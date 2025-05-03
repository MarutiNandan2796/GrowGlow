<?php
require_once 'config/database.php';
require_once 'config/api_config.php';

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Database Connection Test</h2>";
try {
    // Test database connection
    $pdo->query("SELECT 1");
    echo "✅ Database connection successful<br>";
    
    // Check if tables exist
    $tables = ['users', 'plant_identifications', 'pest_identifications'];
    foreach ($tables as $table) {
        $result = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "✅ Table '$table' exists<br>";
        } else {
            echo "❌ Table '$table' does not exist<br>";
        }
    }
} catch (PDOException $e) {
    echo "❌ Database Error: " . $e->getMessage() . "<br>";
}

echo "<h2>API Key Test</h2>";
// Test Plant.id API key
if (defined('PLANT_ID_API_KEY') && PLANT_ID_API_KEY !== 'YOUR_PLANT_ID_API_KEY') {
    echo "✅ Plant.id API key is configured<br>";
    
    // Test API connection
    $test_image = __DIR__ . '/assets/images/test_plant.jpg';
    if (file_exists($test_image)) {
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
        
        $response = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpcode === 200) {
            echo "✅ Plant.id API connection successful<br>";
        } else {
            echo "❌ Plant.id API Error: HTTP Code $httpcode<br>";
            echo "Response: " . $response . "<br>";
        }
    } else {
        echo "❌ Test image not found at: $test_image<br>";
    }
} else {
    echo "❌ Plant.id API key is not configured properly<br>";
}

echo "<h2>Directory Permissions Test</h2>";
// Test upload directories
$directories = [
    'uploads/plants',
    'uploads/pests'
];

foreach ($directories as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (!file_exists($path)) {
        if (mkdir($path, 0777, true)) {
            echo "✅ Created directory: $dir<br>";
        } else {
            echo "❌ Failed to create directory: $dir<br>";
        }
    } else {
        if (is_writable($path)) {
            echo "✅ Directory is writable: $dir<br>";
        } else {
            echo "❌ Directory is not writable: $dir<br>";
        }
    }
}

echo "<h2>PHP Configuration</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Upload Max Filesize: " . ini_get('upload_max_filesize') . "<br>";
echo "Post Max Size: " . ini_get('post_max_size') . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
?> 