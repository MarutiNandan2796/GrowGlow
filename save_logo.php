<?php
// Create the assets/images directory if it doesn't exist
$dir = 'assets/images';
if (!file_exists($dir)) {
    mkdir($dir, 0777, true);
}

// The image URL
$imageUrl = 'https://raw.githubusercontent.com/yourusername/plantpal/main/assets/images/glowing-leaf-logo.png';

// The local path where we want to save the image
$savePath = $dir . '/glowing-leaf-logo.png';

// Download and save the image
if (file_put_contents($savePath, file_get_contents($imageUrl)) !== false) {
    echo "Logo saved successfully!";
} else {
    echo "Error saving logo.";
}
?> 