<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['reminder_id']) || !isset($_POST['action'])) {
    header('Location: index.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$reminder_id = $_POST['reminder_id'];
$action = $_POST['action'];

try {
    // Verify the reminder belongs to the user
    $stmt = $pdo->prepare("SELECT * FROM reminders WHERE id = ? AND user_id = ?");
    $stmt->execute([$reminder_id, $user_id]);
    $reminder = $stmt->fetch();

    if (!$reminder) {
        $_SESSION['error'] = "Reminder not found.";
        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    // Update the reminder status
    $status = ($action === 'complete') ? 'completed' : 'cancelled';
    $stmt = $pdo->prepare("UPDATE reminders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $reminder_id]);

    // If marking as complete, update the plant's last care date
    if ($action === 'complete') {
        $column = 'last_' . $reminder['reminder_type'];
        if (in_array($column, ['last_watered', 'last_fertilized', 'last_repotted'])) {
            $stmt = $pdo->prepare("UPDATE plants SET $column = NOW() WHERE id = ?");
            $stmt->execute([$reminder['plant_id']]);
        }
    }

    $_SESSION['success'] = "Reminder marked as " . $status . " successfully!";
} catch (PDOException $e) {
    $_SESSION['error'] = "Error updating reminder: " . $e->getMessage();
}

header('Location: ' . $_SERVER['HTTP_REFERER']);
exit; 