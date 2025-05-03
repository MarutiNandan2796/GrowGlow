<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grow-Glow - Your Plant Care Companion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        .navbar-brand {
            font-size: 2rem;
            font-weight: bold;
            color: #2ecc71 !important;
            display: flex;
            align-items: center;
            margin: 0 auto;
        }
        .nav-link {
            color: #34495e !important;
            font-weight: 500;
        }
        .nav-link:hover {
            color: #2ecc71 !important;
        }
        .active {
            color: #2ecc71 !important;
        }
        .logo-img {
            width: 60px;
            height: auto;
            margin-right: 15px;
            animation: glow 2s ease-in-out infinite alternate;
        }
        @keyframes glow {
            from {
                filter: drop-shadow(0 0 2px rgba(46, 204, 113, 0.6));
            }
            to {
                filter: drop-shadow(0 0 8px rgba(46, 204, 113, 0.8));
            }
        }
        .navbar {
            padding: 0.5rem 0;
        }
        .navbar .container {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .navbar-brand-container {
            display: flex;
            justify-content: center;
            width: 100%;
            margin-bottom: 0.5rem;
        }
        .navbar-nav-container {
            width: 100%;
            display: flex;
            justify-content: center;
        }
        @media (min-width: 992px) {
            .navbar .container {
                flex-direction: row;
                justify-content: space-between;
            }
            .navbar-brand-container {
                width: auto;
                margin-bottom: 0;
            }
            .navbar-nav-container {
                width: auto;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <div class="navbar-brand-container">
                <a class="navbar-brand" href="index.php">
                    <img src="\grow glow\plantpal\Plantpal\assets\images\glowing-leaf-logo.png" alt="Grow-Glow" class="logo-img">
                    Grow-Glow
                </a>
            </div>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse navbar-nav-container" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Plant ID</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pest_identification.php">Pest ID</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_plants.php">My Plants</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">Profile</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">Login</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link btn btn-outline-success ms-2" href="register.php">Register</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
    <div class="container mt-2">
        <header class="bg-white shadow-sm">
            <div class="container mx-auto px-4 py-2 flex justify-between items-center">
                <!-- Removing the duplicate logo and name -->
            </div>
        </header>
    </div>
</body>
</html>