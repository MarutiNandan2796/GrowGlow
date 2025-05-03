<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        // Insert plant into plants table
        $stmt = $pdo->prepare("INSERT INTO plants (user_id, name, scientific_name, image_path, care_instructions) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $_POST['plant_name'],
            $_POST['scientific_name'],
            $_POST['image_path'],
            $_POST['care_instructions']
        ]);

        $plant_id = $pdo->lastInsertId();

        // Create initial watering reminder
        $stmt = $pdo->prepare("INSERT INTO reminders (plant_id, user_id, reminder_type, reminder_date) 
                              VALUES (?, ?, 'watering', DATE_ADD(CURDATE(), INTERVAL 7 DAY))");
        $stmt->execute([$plant_id, $_SESSION['user_id']]);

        $pdo->commit();
        
        $_SESSION['success'] = "Plant saved successfully!";
        header('Location: my_plants.php');
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error saving plant: " . $e->getMessage();
        header('Location: plant_details.php');
        exit;
    }
} else {
    header('Location: index.php');
    exit;
}
?> 