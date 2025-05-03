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

// Helper function to calculate time ago
function time_ago($date) {
    if (empty($date)) {
        return 'Not recorded';
    }
    $timestamp = strtotime($date);
    $current_time = time();
    $time_difference = $current_time - $timestamp;
    $seconds = $time_difference;
    $minutes = round($seconds / 60);
    $hours = round($seconds / 3600);
    $days = round($seconds / 86400);
    $weeks = round($seconds / 604800);
    $months = round($seconds / 2629440);
    $years = round($seconds / 31553280);

    if ($seconds <= 60) {
        return "Just now";
    } elseif ($minutes <= 60) {
        return ($minutes == 1) ? "1 minute ago" : "$minutes minutes ago";
    } elseif ($hours <= 24) {
        return ($hours == 1) ? "1 hour ago" : "$hours hours ago";
    } elseif ($days <= 7) {
        return ($days == 1) ? "Yesterday" : "$days days ago";
    } elseif ($weeks <= 4.3) {
        return ($weeks == 1) ? "1 week ago" : "$weeks weeks ago";
    } elseif ($months <= 12) {
        return ($months == 1) ? "1 month ago" : "$months months ago";
    } else {
        return ($years == 1) ? "1 year ago" : "$years years ago";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($plant['name']); ?> - Plant Care - PlantPal</title>
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
        
        .plant-details-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border-left: 4px solid var(--primary);
        }
        
        .care-info-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s ease-in-out;
        }
        
        .care-info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col hero-gradient">
    <?php include 'includes/header.php'; ?>
    
    <main class="flex-grow py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="plant-details-card p-6 mb-8">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($plant['name']); ?></h1>
                            <?php if (!empty($plant['scientific_name'])): ?>
                                <p class="text-gray-600 italic"><?php echo htmlspecialchars($plant['scientific_name']); ?></p>
                            <?php endif; ?>
                        </div>
                        <a href="my_plants.php" class="btn-plant px-4 py-2 rounded-lg text-sm font-semibold">
                            <i class="fas fa-arrow-left mr-2"></i> Back to My Plants
                        </a>
                    </div>

                    <?php if (!empty($plant['image_path'])): ?>
                        <div class="mb-6">
                            <img src="<?php echo htmlspecialchars($plant['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($plant['name']); ?>" 
                                 class="rounded-lg shadow-md max-h-96 mx-auto">
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($plant['description'])): ?>
                        <div class="mb-6">
                            <h2 class="text-xl font-bold text-gray-800 mb-3">Description</h2>
                            <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($plant['description'])); ?></p>
                        </div>
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="care-info-card p-4">
                            <h3 class="text-lg font-bold text-gray-800 mb-3">Care Instructions</h3>
                            <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($plant['care_instructions'])); ?></p>
                        </div>

                        <div class="care-info-card p-4">
                            <h3 class="text-lg font-bold text-gray-800 mb-3">Plant Status</h3>
                            <p class="text-gray-600 mb-2">
                                <i class="fas fa-tint text-blue-400 mr-2"></i> 
                                Last Watered: <?php echo time_ago($plant['last_watered']); ?>
                            </p>
                            <?php if (!empty($plant['last_fertilized'])): ?>
                                <p class="text-gray-600 mb-2">
                                    <i class="fas fa-seedling text-green-400 mr-2"></i>
                                    Last Fertilized: <?php echo time_ago($plant['last_fertilized']); ?>
                                </p>
                            <?php endif; ?>
                            <?php if (!empty($plant['last_repotted'])): ?>
                                <p class="text-gray-600">
                                    <i class="fas fa-flask text-purple-400 mr-2"></i>
                                    Last Repotted: <?php echo time_ago($plant['last_repotted']); ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end gap-4">
                        <a href="edit_plant.php?id=<?php echo $plant['id']; ?>" class="btn-plant px-4 py-2 rounded-lg text-sm font-semibold">
                            <i class="fas fa-edit mr-2"></i> Edit Plant
                        </a>
                        <form action="delete_plant.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this plant?');">
                            <input type="hidden" name="plant_id" value="<?php echo $plant['id']; ?>">
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                                <i class="fas fa-trash-alt mr-2"></i> Delete Plant
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 