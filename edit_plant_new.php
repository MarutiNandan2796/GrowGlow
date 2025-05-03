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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Update plant details
        $stmt = $pdo->prepare("UPDATE plants SET 
            name = ?,
            scientific_name = ?,
            description = ?,
            care_instructions = ?,
            last_watered = ?,
            last_fertilized = ?,
            last_repotted = ?
            WHERE id = ? AND user_id = ?");
            
        $stmt->execute([
            $_POST['name'],
            $_POST['scientific_name'],
            $_POST['description'],
            $_POST['care_instructions'],
            $_POST['last_watered'] ?: null,
            $_POST['last_fertilized'] ?: null,
            $_POST['last_repotted'] ?: null,
            $plant_id,
            $user_id
        ]);

        // Handle reminder creation
        if (!empty($_POST['reminder_type']) && !empty($_POST['reminder_date']) && !empty($_POST['reminder_time'])) {
            $reminder_datetime = $_POST['reminder_date'] . ' ' . $_POST['reminder_time'];
            
            $stmt = $pdo->prepare("INSERT INTO reminders (plant_id, user_id, reminder_type, reminder_datetime, status) 
                                VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([
                $plant_id,
                $user_id,
                $_POST['reminder_type'],
                $reminder_datetime
            ]);
        }

        $_SESSION['success'] = "Plant details updated successfully!";
        header('Location: plant_care.php?id=' . $plant_id);
        exit;
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error updating plant: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit <?php echo htmlspecialchars($plant['name']); ?> - PlantPal</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        :root {
            --primary: #2e8b57;
            --primary-light: #3cb371;
            --secondary: #f8f9fa;
            --accent: #ff7f50;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5fef5;
            color: #333;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, rgba(46,139,87,0.1) 0%, rgba(255,255,255,1) 100%);
        }
        
        .btn-plant {
            background-color: var(--primary);
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-plant:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46,139,87,0.3);
        }
        
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary);
        }
        
        .form-label {
            font-weight: 600;
            color: #2d3748;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.2rem rgba(46,139,87,0.25);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col hero-gradient">
    <?php include 'includes/header.php'; ?>
    
    <main class="flex-grow py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="form-card p-6 mb-8">
                    <div class="flex justify-between items-start mb-6">
                        <h1 class="text-3xl font-bold text-gray-800">Edit Plant Details</h1>
                        <a href="plant_care.php?id=<?php echo $plant_id; ?>" class="btn-plant px-4 py-2 rounded-lg text-sm font-semibold">
                            <i class="fas fa-arrow-left mr-2"></i> Back to Plant Details
                        </a>
                    </div>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="form-label">Plant Name</label>
                                <input type="text" name="name" class="form-control" value="<?php echo htmlspecialchars($plant['name']); ?>" required>
                            </div>
                            <div>
                                <label class="form-label">Scientific Name</label>
                                <input type="text" name="scientific_name" class="form-control" value="<?php echo htmlspecialchars($plant['scientific_name'] ?? ''); ?>">
                            </div>
                        </div>

                        <div>
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"><?php echo htmlspecialchars($plant['description'] ?? ''); ?></textarea>
                        </div>

                        <div>
                            <label class="form-label">Care Instructions</label>
                            <textarea name="care_instructions" class="form-control" rows="4"><?php echo htmlspecialchars($plant['care_instructions'] ?? ''); ?></textarea>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="form-label">Last Watered</label>
                                <input type="date" name="last_watered" class="form-control" value="<?php echo $plant['last_watered'] ? date('Y-m-d', strtotime($plant['last_watered'])) : ''; ?>">
                            </div>
                            <div>
                                <label class="form-label">Last Fertilized</label>
                                <input type="date" name="last_fertilized" class="form-control" value="<?php echo $plant['last_fertilized'] ? date('Y-m-d', strtotime($plant['last_fertilized'])) : ''; ?>">
                            </div>
                            <div>
                                <label class="form-label">Last Repotted</label>
                                <input type="date" name="last_repotted" class="form-control" value="<?php echo $plant['last_repotted'] ? date('Y-m-d', strtotime($plant['last_repotted'])) : ''; ?>">
                            </div>
                        </div>

                        <div class="border-t pt-6 mt-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-4">Add Reminder</h2>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label class="form-label">Reminder Type</label>
                                    <select name="reminder_type" class="form-control">
                                        <option value="watering">Watering</option>
                                        <option value="fertilizing">Fertilizing</option>
                                        <option value="repotting">Repotting</option>
                                        <option value="pruning">Pruning</option>
                                        <option value="other">Other</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Date</label>
                                    <input type="date" name="reminder_date" class="form-control" min="<?php echo date('Y-m-d'); ?>">
                                </div>
                                <div>
                                    <label class="form-label">Time</label>
                                    <input type="time" name="reminder_time" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end gap-4 mt-6">
                            <button type="submit" class="btn-plant px-6 py-2 rounded-lg font-semibold">
                                <i class="fas fa-save mr-2"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 