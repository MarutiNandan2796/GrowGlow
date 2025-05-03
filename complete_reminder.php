<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reminder_id'])) {
    $reminder_id = $_POST['reminder_id'];
    
    // Verify reminder belongs to user
    $stmt = $pdo->prepare("SELECT * FROM reminders WHERE id = ? AND user_id = ?");
    $stmt->execute([$reminder_id, $_SESSION['user_id']]);
    $reminder = $stmt->fetch();
    
    if ($reminder) {
        try {
            $stmt = $pdo->prepare("UPDATE reminders SET is_completed = 1 WHERE id = ?");
            $stmt->execute([$reminder_id]);
            
            // If it's a watering reminder, update the plant's last_watered date
            if ($reminder['reminder_type'] === 'watering') {
                $stmt = $pdo->prepare("UPDATE plants SET last_watered = CURDATE() WHERE id = ?");
                $stmt->execute([$reminder['plant_id']]);
            }
            
            $_SESSION['success'] = "Reminder marked as complete!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error updating reminder. Please try again.";
        }
    }
}

header('Location: my_plants.php');
exit;
?> 