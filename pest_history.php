<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch pest identification history
$stmt = $pdo->prepare("SELECT * FROM pest_identifications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$identifications = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pest Identification History - PlantPal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2e8b57;
            --primary-light: #3cb371;
            --secondary: #f8f9fa;
            --accent: #ff7f50;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5fef5;
            color: #333;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, rgba(46,139,87,0.1) 0%, rgba(255,255,255,1) 100%);
        }
        
        .history-card {
            background: white;
            border-radius: 15px;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .history-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        .thumbnail {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
        }
        
        .confidence-pill {
            background-color: var(--primary);
            color: white;
            font-size: 0.875rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
        }
        
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <?php include 'includes/header.php'; ?>
    
    <main class="flex-grow hero-gradient py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <div class="flex justify-between items-center mb-8">
                    <h1 class="text-3xl font-bold text-gray-800">Pest Identification History</h1>
                    <a href="pest_identification.php" class="bg-primary hover:bg-primary-light text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                        New Identification
                    </a>
                </div>
                
                <?php if (empty($identifications)): ?>
                    <div class="empty-state">
                        <h2 class="text-xl font-semibold text-gray-800 mb-4">No Identifications Yet</h2>
                        <p class="text-gray-600 mb-6">Start by uploading a photo of a pest you'd like to identify.</p>
                        <a href="pest_identification.php" class="bg-primary hover:bg-primary-light text-white px-6 py-3 rounded-lg font-medium transition duration-300">
                            Identify Your First Pest
                        </a>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($identifications as $identification): ?>
                            <a href="pest_result.php?id=<?php echo htmlspecialchars($identification['id']); ?>" 
                               class="history-card block p-4 no-underline">
                                <div class="flex items-center gap-4">
                                    <img src="<?php echo htmlspecialchars($identification['image_path']); ?>" 
                                         alt="Pest thumbnail" 
                                         class="thumbnail">
                                    <div class="flex-grow">
                                        <div class="flex justify-between items-start mb-2">
                                            <h2 class="text-xl font-semibold text-gray-800">
                                                <?php echo htmlspecialchars($identification['pest_name']); ?>
                                            </h2>
                                            <span class="confidence-pill">
                                                <?php echo round($identification['confidence'] * 100); ?>% confidence
                                            </span>
                                        </div>
                                        <p class="text-gray-600 line-clamp-2">
                                            <?php echo htmlspecialchars($identification['description']); ?>
                                        </p>
                                        <div class="text-sm text-gray-500 mt-2">
                                            <?php echo date('F j, Y g:i A', strtotime($identification['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 