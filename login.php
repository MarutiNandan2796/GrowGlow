<?php
session_start();
require_once 'config/database.php';

if (isset($_SESSION['user_id'])) {
    header('Location: my_plants.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        header('Location: my_plants.php');
        exit;
    } else {
        $error = "Invalid email or password";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PlantPal</title>
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
        
        .feature-item {
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
        }
        
        .feature-item:hover {
            transform: translateY(-5px);
            background-color: rgba(46,139,87,0.05);
        }
        
        .upload-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
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
        
        .login-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            max-width: 500px;
            margin: 0 auto;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col hero-gradient">
    <?php include 'includes/header.php'; ?>
    
    <main class="flex-grow py-12">
        <div class="container mx-auto px-4">
            <div class="login-card p-8">
                <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Login to PlantPal</h2>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger mb-4">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" class="space-y-6">
                    <div>
                        <label for="email" class="block text-gray-700 mb-2">Email</label>
                        <input type="email" id="email" name="email" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    </div>

                    <div>
                        <label for="password" class="block text-gray-700 mb-2">Password</label>
                        <input type="password" id="password" name="password" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500" required>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="w-full btn-plant py-3 rounded-lg font-semibold text-lg shadow-md">
                            Login
                        </button>
                        <a href="forgot_password.php" class="text-center text-decoration-none">
                            <small>Forgot your password?</small>
                        </a>
                    </div>
                </form>

                <div class="text-center mt-6">
                    <p class="text-gray-600">Don't have an account? <a href="register.php" class="text-green-600 hover:text-green-800 font-semibold">Register here</a></p>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>