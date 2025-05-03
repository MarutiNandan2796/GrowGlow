<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if plant ID is provided
if (!isset($_POST['plant_id'])) {
    $_SESSION['error'] = "No plant specified for deletion.";
    header('Location: my_plants.php');
    exit;
}

$plant_id = $_POST['plant_id'];
$user_id = $_SESSION['user_id'];

try {
    // First verify that the plant belongs to the user
    $stmt = $pdo->prepare("SELECT id FROM plants WHERE id = ? AND user_id = ?");
    $stmt->execute([$plant_id, $user_id]);
    
    if (!$stmt->fetch()) {
        $_SESSION['error'] = "You don't have permission to delete this plant.";
        header('Location: my_plants.php');
        exit;
    }

    // Delete associated reminders first (foreign key constraint)
    $stmt = $pdo->prepare("DELETE FROM reminders WHERE plant_id = ?");
    $stmt->execute([$plant_id]);

    // Delete the plant
    $stmt = $pdo->prepare("DELETE FROM plants WHERE id = ? AND user_id = ?");
    $stmt->execute([$plant_id, $user_id]);

    $_SESSION['success'] = "Plant deleted successfully.";
    header('Location: my_plants.php');
    exit;

} catch (PDOException $e) {
    $_SESSION['error'] = "Error deleting plant: " . $e->getMessage();
    header('Location: edit_plant.php?id=' . $plant_id);
    exit;
}
?> 