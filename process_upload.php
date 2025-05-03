<?php
session_start();
require_once 'config/database.php';
require_once 'config/api_config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start output buffering to prevent any output before redirects
ob_start();

// Debug output
error_log("Starting process_upload.php");
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

// Function to call Gemini API for plant identification
function identifyPlant($imagePath, $apiKey) {
    // Read and encode the image
    $image_data = base64_encode(file_get_contents($imagePath));
    error_log("Image data length: " . strlen($image_data));

    // Prepare the API request
    $base_url = 'https://generativelanguage.googleapis.com';
    $endpoint = '/v1/models/gemini-1.5-flash:generateContent';
    $url = $base_url . $endpoint . '?key=' . urlencode($apiKey);

    $prompt = "You are a plant identification expert. Analyze this image and identify the plant. Provide the following information in valid JSON format:
    {
        \"plant_name\": \"[common name of the plant]\",
        \"scientific_name\": \"[scientific name if identifiable]\",
        \"probability\": [confidence score between 0 and 1],
        \"plant_details\": {
            \"common_names\": [\"list\", \"of\", \"common names\"],
            \"wiki_description\": {
                \"value\": \"[detailed description of the plant]\"
            },
            \"watering\": \"[watering instructions]\",
            \"light\": \"[light requirements]\",
            \"soil\": \"[soil requirements]\",
            \"growth\": \"[growth characteristics]\"
        }
    }";

    $data = [
        "contents" => [
            "parts" => [
                [
                    "text" => $prompt
                ],
                [
                    "inline_data" => [
                        "mime_type" => "image/jpeg",
                        "data" => $image_data
                    ]
                ]
            ]
        ],
        "generationConfig" => [
            "temperature" => 0.4,
            "topK" => 32,
            "topP" => 1,
            "maxOutputTokens" => 2048
        ]
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

    $response = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    
    curl_close($ch);

    error_log("API Response Code: " . $httpcode);
    error_log("API Response: " . $response);
    error_log("cURL Error: " . $curl_error);

    if ($curl_error) {
        return ['error' => 'API request failed (cURL error): ' . $curl_error];
    }

    if ($httpcode != 200) {
        return ['error' => 'Plant identification failed. API returned status ' . $httpcode . ': ' . $response];
    }

    // Parse the response
    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Failed to parse API response: ' . json_last_error_msg()];
    }

    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        return ['error' => 'Invalid API response structure'];
    }

    // Extract the text response
    $text_response = $result['candidates'][0]['content']['parts'][0]['text'];
    
    // Try to extract JSON from the response text
    preg_match('/\{.*\}/s', $text_response, $matches);
    if (empty($matches)) {
        return ['error' => 'No plant data found in response'];
    }

    // Parse the JSON response
    $plant_data = json_decode($matches[0], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        return ['error' => 'Failed to parse plant data JSON: ' . json_last_error_msg()];
    }

    return $plant_data;
}

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    if (!isset($_FILES['plant_image']) || $_FILES['plant_image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }

    $file = $_FILES['plant_image'];
    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    
    // Validate file type
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Invalid file type. Please upload a JPEG, PNG, or GIF image.');
    }
    
    // Validate file size (5MB max)
    if ($file_size > 5 * 1024 * 1024) {
        throw new Exception('File too large. Maximum size is 5MB.');
    }

    // Create upload directory if it doesn't exist
    $upload_dir = __DIR__ . '/uploads/plants/';
    if (!file_exists($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            throw new Exception('Failed to create upload directory');
        }
    }

    // Generate unique filename
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $unique_filename = uniqid('plant_') . '.' . $extension;
    $upload_path = $upload_dir . $unique_filename;
    $relative_upload_path = 'uploads/plants/' . $unique_filename;
    
    if (!move_uploaded_file($file_tmp, $upload_path)) {
        throw new Exception('Failed to save uploaded file');
    }

    error_log("File saved successfully at: " . $upload_path);

    // Store the plant identification request in database
    $stmt = $pdo->prepare("INSERT INTO plant_identifications (user_id, image_path, status, created_at) 
                          VALUES (?, ?, 'pending', NOW())");
    $stmt->execute([$_SESSION['user_id'], $relative_upload_path]);
    $identification_id = $pdo->lastInsertId();

    error_log("Database record created with ID: " . $identification_id);

    // Call the Plant Identification API using Gemini
    $identification_result = identifyPlant($upload_path, GOOGLE_API_KEY);

    if (isset($identification_result['error'])) {
        throw new Exception($identification_result['error']);
    }

    error_log("Plant identification successful: " . print_r($identification_result, true));

    // Prepare plant details
    $plant_details = $identification_result['plant_details'];
    
    // Build comprehensive care instructions
    $care_instructions = [];
    
    if (!empty($plant_details['watering'])) {
        $care_instructions[] = "Watering: " . $plant_details['watering'];
    }
    if (!empty($plant_details['light'])) {
        $care_instructions[] = "Light: " . $plant_details['light'];
    }
    if (!empty($plant_details['soil'])) {
        $care_instructions[] = "Soil: " . $plant_details['soil'];
    }
    if (!empty($plant_details['growth'])) {
        $care_instructions[] = "Growth: " . $plant_details['growth'];
    }
    
    if (empty($care_instructions) && !empty($plant_details['wiki_description']['value'])) {
        $care_instructions[] = $plant_details['wiki_description']['value'];
    }
    
    if (empty($care_instructions)) {
        $care_instructions[] = "No specific care instructions available for this plant.";
    }

    $identified_plant = [
        'id' => $identification_id,
        'name' => $identification_result['plant_name'] ?? 'Unknown Plant',
        'scientific_name' => $identification_result['scientific_name'] ?? null,
        'common_names' => $plant_details['common_names'] ?? [],
        'confidence' => $identification_result['probability'] ?? 0,
        'wiki_description' => $plant_details['wiki_description']['value'] ?? 'No description available.',
        'care_instructions' => implode("\n\n", $care_instructions)
    ];
    
    error_log("Prepared plant data: " . print_r($identified_plant, true));
    
    // Store results in session
    $_SESSION['identified_plant'] = $identified_plant;
    $_SESSION['plant_image'] = $relative_upload_path;
    
    // Update database with results
    $stmt = $pdo->prepare("UPDATE plant_identifications 
                          SET plant_name = ?, confidence = ?, description = ?, 
                              care_instructions = ?, status = 'completed', 
                              completed_at = NOW() 
                          WHERE id = ?");
    $stmt->execute([
        $identified_plant['name'],
        $identified_plant['confidence'],
        $identified_plant['wiki_description'],
        $identified_plant['care_instructions'],
        $identification_id
    ]);
    
    error_log("Database updated successfully");
    
    // Redirect to results page
    header("Location: plant_details.php");
    exit;
    
} catch (Exception $e) {
    error_log("Error in process_upload.php: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    $_SESSION['error'] = "Error during plant identification: " . $e->getMessage();
    header('Location: index.php');
    exit;
}

// End output buffering and discard any output
ob_end_clean();
?> 