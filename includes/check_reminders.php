<?php
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Check for upcoming reminders
    $stmt = $pdo->prepare("
        SELECT r.*, p.name as plant_name 
        FROM reminders r 
        JOIN plants p ON r.plant_id = p.id 
        WHERE r.user_id = ? 
        AND r.reminder_datetime <= DATE_ADD(NOW(), INTERVAL 1 HOUR)
        AND r.status = 'pending'
        ORDER BY r.reminder_datetime ASC
    ");
    $stmt->execute([$user_id]);
    $upcoming_reminders = $stmt->fetchAll();
    
    if (!empty($upcoming_reminders)) {
        echo '<div class="reminder-notifications">';
        foreach ($upcoming_reminders as $reminder) {
            $time_until = strtotime($reminder['reminder_datetime']) - time();
            $minutes = floor($time_until / 60);
            
            echo '<div class="alert alert-info alert-dismissible fade show" role="alert">';
            echo '<strong>Reminder:</strong> ';
            echo htmlspecialchars($reminder['plant_name']) . ' needs ' . 
                 htmlspecialchars($reminder['reminder_type']) . ' ';
            
            if ($minutes <= 0) {
                echo 'now!';
            } else {
                echo 'in ' . $minutes . ' minutes.';
            }
            
            echo '<div class="mt-2">';
            echo '<form action="mark_reminder.php" method="POST" class="d-inline">';
            echo '<input type="hidden" name="reminder_id" value="' . $reminder['id'] . '">';
            echo '<button type="submit" name="action" value="complete" class="btn btn-success btn-sm">Mark Complete</button>';
            echo '<button type="submit" name="action" value="cancel" class="btn btn-danger btn-sm ms-2">Cancel</button>';
            echo '</form>';
            echo '</div>';
            
            echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
            echo '</div>';
        }
        echo '</div>';
    }
}
?> 