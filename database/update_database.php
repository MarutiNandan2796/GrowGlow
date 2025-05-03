<?php
require_once '../config/database.php';

try {
    // Check if columns exist first
    $table_info = $pdo->query("DESCRIBE plants");
    $columns = $table_info->fetchAll(PDO::FETCH_COLUMN);
    
    // Add last_watered column if it doesn't exist
    if (!in_array('last_watered', $columns)) {
        $pdo->exec("ALTER TABLE plants ADD COLUMN last_watered DATE DEFAULT NULL");
        echo "Added last_watered column.<br>";
    }
    
    // Add watering_frequency column if it doesn't exist
    if (!in_array('watering_frequency', $columns)) {
        $pdo->exec("ALTER TABLE plants ADD COLUMN watering_frequency INT DEFAULT NULL");
        echo "Added watering_frequency column.<br>";
    }
    
    // Set default watering frequency for existing plants
    $pdo->exec("UPDATE plants SET watering_frequency = 7 WHERE watering_frequency IS NULL");
    echo "Updated default watering frequency for plants.<br>";
    
    echo "Database update completed successfully!";
} catch (PDOException $e) {
    echo "Error updating database: " . $e->getMessage();
}
?> 