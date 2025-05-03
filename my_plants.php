<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Handle search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
$params = [$user_id];

if (!empty($search)) {
    $search_condition = "AND name LIKE ?";
    $params[] = "%$search%";
}

// Fetch user's plants with search condition
$stmt = $pdo->prepare("SELECT id, name, last_watered FROM plants WHERE user_id = ? $search_condition ORDER BY name ASC");
$stmt->execute($params);
$plants = $stmt->fetchAll();

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
    <title>My Plants - PlantPal</title>
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
        
        .btn-accent {
            background-color: var(--accent);
            color: white;
        }
        
        .btn-accent:hover {
            background-color: #ff6347;
        }
        
        .plant-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            transition: transform 0.2s ease-in-out;
            border-left: 4px solid var(--primary);
        }
        
        .plant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
        }
        
        .fab {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background-color: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .fab:hover {
            background-color: var(--primary-light);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46,139,87,0.3);
        }
        
        .home-button {
            background-color: var(--primary-light);
            border: none;
            color: white;
            padding: 10px 25px;
            border-radius: 25px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .home-button:hover {
            background-color: var(--primary);
            color: white;
            transform: translateY(-2px);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col hero-gradient">
    <main class="flex-grow py-12">
        <div class="container mx-auto px-4">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-800 mb-2"><i class="fas fa-leaf text-green-600"></i> My Plants</h1>
                <p class="text-gray-600">Manage your plant collection</p>
            </div>

            <!-- Search Box -->
            <div class="max-w-xl mx-auto mb-8">
                <form action="" method="GET" class="flex gap-2">
                    <input type="text" 
                           name="search" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search plants by name..." 
                           class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <button type="submit" class="btn-plant px-6 py-2 rounded-lg">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </form>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <?php if (empty($plants)): ?>
                    <div class="col-12 text-center py-8">
                        <div class="bg-white p-8 rounded-lg shadow-sm max-w-md mx-auto">
                            <i class="fas fa-seedling text-5xl text-green-500 mb-4"></i>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Your garden is empty!</h3>
                            <p class="text-gray-600 mb-4">Add your first plant to get started</p>
                            <a href="add_manual_plant.php" class="btn-plant px-6 py-3 rounded-lg font-semibold inline-block">
                                <i class="fas fa-plus mr-2"></i> Add Plant
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($plants as $plant): ?>
                        <div class="col-md-4 mb-6">
                            <div class="plant-card p-6 h-full">
                                <h3 class="text-xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($plant['name']); ?></h3>
                                <p class="text-gray-600 mb-4"><i class="fas fa-tint text-blue-400 mr-2"></i> Last Watered: <?php echo time_ago($plant['last_watered']); ?></p>
                                <a href="plant_care.php?id=<?php echo $plant['id']; ?>" class="btn-plant px-4 py-2 rounded-lg text-sm font-semibold inline-block">
                                    <i class="fas fa-info-circle mr-1"></i> View Details
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <div class="text-center mt-8">
                <a href="index.php" class="home-button inline-block">
                    <i class="fas fa-home mr-2"></i> Back to Home
                </a>
            </div>
        </div>
    </main>

    <!-- Floating Action Button -->
    <a href="add_manual_plant.php" class="fab" title="Add New Plant">
        <i class="fas fa-plus"></i>
    </a>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>