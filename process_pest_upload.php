<?php
// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start output buffering
ob_start();

session_start();
require_once 'config/database.php';

// Debug information
echo "Script started<br>";
echo "POST data: " . print_r($_POST, true) . "<br>";
echo "FILES data: " . print_r($_FILES, true) . "<br>";

// Enable error logging
ini_set('display_errors', 1);
error_reporting(E_ALL);
$log_file = __DIR__ . '/pest_identification_log.txt';

function logMessage($message, $type = 'INFO') {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($log_file, "[$timestamp] [$type] $message\n", FILE_APPEND);
}

try {
    if (!isset($_SESSION['user_id'])) {
        throw new Exception('User not logged in');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method');
    }

    // Load environment variables
    $env_file = __DIR__ . '/.env';
    if (!file_exists($env_file)) {
        throw new Exception('.env file not found');
    }

    $env = parse_ini_file($env_file);
    if (!$env || !isset($env['GOOGLE_API_KEY'])) {
        throw new Exception('API key not found in .env file');
    }

    $api_key = trim($env['GOOGLE_API_KEY']); // Remove any whitespace
    logMessage("API Key loaded successfully");

    // Create uploads directory if it doesn't exist
    $upload_dir = __DIR__ . '/uploads/pests/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (!isset($_FILES['pest_image']) || $_FILES['pest_image']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('No file uploaded or upload error occurred');
    }

    $file = $_FILES['pest_image'];
    logMessage("File upload received: " . json_encode($file));

    $file_name = $file['name'];
    $file_tmp = $file['tmp_name'];
    $file_size = $file['size'];
    
    // Generate unique filename
    $extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $unique_filename = uniqid('pest_') . '.' . $extension;
    $upload_path = $upload_dir . $unique_filename;
    $relative_upload_path = 'uploads/pests/' . $unique_filename;
    
    // Validate file type
    $allowed_types = array('jpg', 'jpeg', 'png', 'gif', 'webp');
    if (!in_array($extension, $allowed_types)) {
        throw new Exception("Invalid file type: $extension");
    }
    
    // Validate file size (5MB max)
    if ($file_size > 5 * 1024 * 1024) {
        throw new Exception("File too large: $file_size bytes");
    }
    
    if ($extension === 'webp') {
        if (!function_exists('imagecreatefromwebp')) {
            throw new Exception('WebP images are not supported on this server. Please upload JPG, PNG, or GIF.');
        }
        $image = imagecreatefromwebp($file_tmp);
        if ($image === false) {
            throw new Exception('Failed to read WebP image');
        }
        $converted_path = $upload_dir . uniqid('pest_') . '.jpg';
        $relative_converted_path = 'uploads/pests/' . basename($converted_path);
        if (!imagejpeg($image, $converted_path, 90)) {
            imagedestroy($image);
            throw new Exception('Failed to convert WebP to JPG');
        }
        imagedestroy($image);
        $upload_path = $converted_path;
        $relative_upload_path = $relative_converted_path;
        $extension = 'jpg';
    } else {
        if (!move_uploaded_file($file_tmp, $upload_path)) {
            throw new Exception("Failed to move uploaded file to $upload_path");
        }
    }

    logMessage("File saved successfully: $upload_path");

    // Store the pest identification request in database
    $stmt = $pdo->prepare("INSERT INTO pest_identifications (user_id, image_path, status, created_at) 
                          VALUES (?, ?, 'pending', NOW())");
    $stmt->execute([$_SESSION['user_id'], $relative_upload_path]);
    $identification_id = $pdo->lastInsertId();
    logMessage("Database record created with ID: $identification_id");

    // Store the last uploaded pest image in session for display on the upload page
    $_SESSION['last_uploaded_pest_image'] = $relative_upload_path;

    // Read and encode the image
    $image_data = base64_encode(file_get_contents($upload_path));
    logMessage("Image encoded successfully");

    // Prepare the API request
    $base_url = 'https://generativelanguage.googleapis.com';
    $endpoint = '/v1/models/gemini-1.5-flash:generateContent';
    $url = $base_url . $endpoint . '?key=' . urlencode($api_key);

    $prompt = "You are a plant pest identification expert. Analyze this image and identify any plant pests or diseases. Provide the following information in valid JSON format:
    {
        \"name\": \"[pest/disease name]\",
        \"confidence\": [confidence score between 0 and 1],
        \"description\": \"[detailed description]\",
        \"treatments\": [\"treatment step 1\", \"treatment step 2\", etc.]
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
        ],
        "safetySettings" => [
            [
                "category" => "HARM_CATEGORY_HARASSMENT",
                "threshold" => "BLOCK_NONE"
            ],
            [
                "category" => "HARM_CATEGORY_HATE_SPEECH",
                "threshold" => "BLOCK_NONE"
            ],
            [
                "category" => "HARM_CATEGORY_SEXUALLY_EXPLICIT",
                "threshold" => "BLOCK_NONE"
            ],
            [
                "category" => "HARM_CATEGORY_DANGEROUS_CONTENT",
                "threshold" => "BLOCK_NONE"
            ]
        ]
    ];

    logMessage("Sending request to Gemini API");

    // Initialize cURL
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2
    ]);

    // Execute the request
    $response = curl_exec($ch);
    
    if ($response === false) {
        throw new Exception("cURL Error: " . curl_error($ch));
    }
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($http_code !== 200) {
        throw new Exception("API returned error code: $http_code, Response: $response");
    }
    
    curl_close($ch);

    // Parse the response
    $result = json_decode($response, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("JSON decode error: " . json_last_error_msg());
    }
    
    if (!isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        throw new Exception("Invalid API response structure: " . json_encode($result));
    }

    // Extract the text response
    $text_response = $result['candidates'][0]['content']['parts'][0]['text'];
    logMessage("API Text Response: $text_response");
    
    // Try to extract JSON from the response text
    preg_match('/\{.*\}/s', $text_response, $matches);
    if (empty($matches)) {
        throw new Exception("No JSON found in response");
    }

    // Parse the JSON response
    $identified_pest = json_decode($matches[0], true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Failed to parse pest data JSON: " . json_last_error_msg());
    }

    if (!$identified_pest || !isset($identified_pest['name'])) {
        throw new Exception("Invalid pest data structure");
    }

    logMessage("Pest identified: " . json_encode($identified_pest));

    // Ensure confidence is a number between 0 and 1
    $confidence = floatval($identified_pest['confidence']);
    $confidence = max(0, min(1, $confidence));

    // Store the identification results
    $stmt = $pdo->prepare("UPDATE pest_identifications 
                          SET pest_name = ?, confidence = ?, description = ?, 
                              treatments = ?, status = 'completed', 
                              completed_at = NOW() 
                          WHERE id = ?");
    $stmt->execute([
        $identified_pest['name'],
        $confidence,
        $identified_pest['description'],
        json_encode($identified_pest['treatments']),
        $identification_id
    ]);
    
    logMessage("Results saved to database successfully");
    
    // Redirect to results page
    header("Location: pest_result.php?id=" . $identification_id);
    exit;
    
} catch (Exception $e) {
    // Get the buffered content
    $debug_output = ob_get_clean();
    
    // Log both the error and debug output
    error_log("Debug output: " . $debug_output);
    error_log("Error: " . $e->getMessage());
    error_log("Stack trace: " . $e->getTraceAsString());
    
    $_SESSION['error'] = "Error during pest identification: " . $e->getMessage();
    header('Location: pest_identification.php');
    exit;
}

// End output buffering and discard any output
ob_end_clean();
?> 