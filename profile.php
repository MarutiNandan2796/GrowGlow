<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT username, email, created_at FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header('Location: login.php?error=invalid_session');
    exit;
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - PlantPal</title>
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
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
        
        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 0 auto;
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
    </style>
</head>
<body class="min-h-screen flex flex-col hero-gradient">
    <?php include 'includes/header.php'; ?>
    
    <main class="flex-grow py-12">
        <div class="container mx-auto px-4">
            <div class="profile-card p-8">
                <div class="text-center mb-6">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-user text-3xl text-green-600"></i>
                    </div>
                    <h1 class="text-3xl font-bold text-gray-800">My Profile</h1>
                    <p class="text-gray-600">Manage your account settings</p>
                </div>
                
                <div class="space-y-4">
                    <div class="border-b pb-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Account Information</h3>
                        <p><span class="font-medium text-gray-700">Username:</span> <?php echo htmlspecialchars($user['username']); ?></p>
                        <p><span class="font-medium text-gray-700">Email:</span> <?php echo htmlspecialchars($user['email']); ?></p>
                        <p><span class="font-medium text-gray-700">Member Since:</span> <?php echo date('F j, Y', strtotime($user['created_at'])); ?></p>
                    </div>
                    
                    <div class="pt-4">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">Account Actions</h3>
                        <div class="flex flex-wrap gap-4">
                            <a href="my_plants.php" class="bg-green-100 text-green-700 px-6 py-2 rounded-lg font-semibold hover:bg-green-200 transition">
                                <i class="fas fa-leaf mr-2"></i> View My Plants
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
</body>
</html>