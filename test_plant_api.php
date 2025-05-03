<?php
require_once 'config/api_config.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Plant.id API Test</h2>";

// Check if API key is defined
if (!defined('PLANT_ID_API_KEY')) {
    die("❌ API key is not defined in config/api_config.php");
}

echo "✅ API key is configured<br>";
echo "API Key (first 5 chars): " . substr(PLANT_ID_API_KEY, 0, 5) . "...<br>";

// Test API connection
$test_image = __DIR__ . '/assets/images/test_plant.jpg';
if (!file_exists($test_image)) {
    die("❌ Test image not found at: $test_image");
}

echo "✅ Test image found<br>";

// Prepare API request
$image_data = base64_encode(file_get_contents($test_image));
$data = [
    'images' => [$image_data],
    'modifiers' => ["similar_images"],
    'language' => 'en'
];

$ch = curl_init('https://api.plant.id/v2/identify');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Api-Key: ' . PLANT_ID_API_KEY
]);
curl_setopt($ch, CURLOPT_VERBOSE, true);
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

echo "Sending request to Plant.id API...<br>";

$response = curl_exec($ch);
$httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);

// Log verbose output
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
echo "cURL Verbose Log:<br><pre>" . htmlspecialchars($verboseLog) . "</pre>";

curl_close($ch);

if ($curl_error) {
    die("❌ cURL Error: " . $curl_error);
}

echo "Response Code: $httpcode<br>";

if ($httpcode === 200) {
    $result = json_decode($response, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo "✅ API connection successful<br>";
        echo "✅ Response parsed successfully<br>";
        
        if (isset($result['suggestions']) && !empty($result['suggestions'])) {
            echo "✅ Plant identification successful<br>";
            echo "Identified Plant: " . $result['suggestions'][0]['plant_name'] . "<br>";
            echo "Confidence: " . ($result['suggestions'][0]['probability'] * 100) . "%<br>";
        } else {
            echo "❌ No plant suggestions found in response<br>";
            echo "Response: <pre>" . htmlspecialchars($response) . "</pre>";
        }
    } else {
        echo "❌ Failed to parse API response: " . json_last_error_msg() . "<br>";
        echo "Response: <pre>" . htmlspecialchars($response) . "</pre>";
    }
} else {
    echo "❌ API Error: HTTP Code $httpcode<br>";
    echo "Response: <pre>" . htmlspecialchars($response) . "</pre>";
}
?> 