<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['plant_id'])) {
    header('Location: my_plants.php');
    exit;
}

$plant_id = $_POST['plant_id'];
$user_id = $_SESSION['user_id'];

try {
    // Update plant details
    $stmt = $pdo->prepare("UPDATE plants SET 
        name = ?,
        last_watered = ?
        WHERE id = ? AND user_id = ?");
        
    $stmt->execute([
        $_POST['name'],
        $_POST['last_watered'] ?: null,
        $plant_id,
        $user_id
    ]);

    $_SESSION['success'] = "Plant details updated successfully!";
    header('Location: plant_care.php?id=' . $plant_id);
    exit;
} catch (PDOException $e) {
    $_SESSION['error'] = "Error updating plant: " . $e->getMessage();
    header('Location: edit_plant_simple.php?id=' . $plant_id);
    exit;
}
?> 