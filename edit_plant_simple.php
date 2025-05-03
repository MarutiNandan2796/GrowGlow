<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: my_plants.php');
    exit;
}

$plant_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Fetch plant details
$stmt = $pdo->prepare("SELECT * FROM plants WHERE id = ? AND user_id = ?");
$stmt->execute([$plant_id, $user_id]);
$plant = $stmt->fetch();

if (!$plant) {
    $_SESSION['error'] = "Plant not found.";
    header('Location: my_plants.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo htmlspecialchars($plant['name']); ?> - PlantPal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Edit Plant: <?php echo htmlspecialchars($plant['name']); ?></h1>
        
        <form method="POST" action="update_plant.php">
            <input type="hidden" name="plant_id" value="<?php echo $plant_id; ?>">
            
            <div class="mb-3">
                <label class="form-label">Plant Name</label>
                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($plant['name']); ?>" required>
            </div>
            
            <div class="mb-3">
                <label class="form-label">Last Watered</label>
                <input type="date" name="last_watered" class="form-control" value="<?php echo $plant['last_watered'] ? date('Y-m-d', strtotime($plant['last_watered'])) : ''; ?>">
            </div>
            
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>
</body>
</html> 