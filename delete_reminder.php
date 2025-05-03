<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    // If not logged in, redirect to login
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reminder_id'])) {
    $reminder_id = $_POST['reminder_id'];
    $user_id = $_SESSION['user_id'];

    try {
        // Verify reminder belongs to the logged-in user before deleting
        $stmt = $pdo->prepare("DELETE FROM reminders WHERE id = ? AND user_id = ?");
        $stmt->execute([$reminder_id, $user_id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = "Reminder deleted successfully.";
        } else {
            // Reminder not found or doesn't belong to the user
            $_SESSION['error'] = "Could not delete reminder. It may not exist or belong to you.";
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = "Database error occurred while deleting the reminder.";
        // Log the error for debugging
        error_log("Error deleting reminder: " . $e->getMessage());
    }
} else {
    // If accessed directly or without required POST data
    $_SESSION['error'] = "Invalid request to delete reminder.";
}

// Redirect back to the reminders page
header('Location: reminders.php');
exit;
?> 