<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['reminders' => []]);
    exit;
}

// Fetch reminders due today or overdue
$stmt = $pdo->prepare("SELECT r.*, p.name as plant_name 
                      FROM reminders r 
                      JOIN plants p ON r.plant_id = p.id 
                      WHERE r.user_id = ? 
                      AND r.is_completed = 0 
                      AND r.reminder_date <= CURDATE() 
                      ORDER BY r.reminder_date ASC");
$stmt->execute([$_SESSION['user_id']]);
$reminders = $stmt->fetchAll();

$formatted_reminders = [];
foreach ($reminders as $reminder) {
    $formatted_reminders[] = [
        'id' => $reminder['id'],
        'message' => "Time to " . $reminder['reminder_type'] . " your " . $reminder['plant_name'],
        'plant_name' => $reminder['plant_name'],
        'type' => $reminder['reminder_type'],
        'date' => $reminder['reminder_date']
    ];
}

echo json_encode(['reminders' => $formatted_reminders]);
?> 