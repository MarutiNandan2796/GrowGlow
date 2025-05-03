<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Enable error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pest Identification - PlantPal</title>
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
            --danger: #dc3545;
        }
        
        body {
            font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5fef5;
            color: #333;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, rgba(46,139,87,0.1) 0%, rgba(255,255,255,1) 100%);
        }
        
        .upload-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        
        .pest-card {
            background: white;
            border-radius: 12px;
            border: 1px solid rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .pest-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }
        
        .btn-danger-custom {
            background-color: var(--danger);
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-danger-custom:hover {
            background-color: #bb2d3b;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.3);
        }
        
        .treatment-tag {
            background-color: #e9ecef;
            color: #495057;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .treatment-tag:hover {
            background-color: var(--primary-light);
            color: white;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <?php include 'includes/header.php'; ?>
    
    <main class="flex-grow hero-gradient py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-6" role="alert">
                        <strong class="font-bold">Error!</strong>
                        <span class="block sm:inline"><?php echo htmlspecialchars($_SESSION['error']); ?></span>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <div class="text-center mb-8">
                    <h1 class="text-4xl font-bold text-gray-800 mb-4">Plant Pest Identification</h1>
                    <p class="text-xl text-gray-600">Upload a photo of the pest or affected plant area to get identification and treatment recommendations</p>
                </div>
                
                <div class="upload-section p-8 mb-8">
                    <form action="process_pest_upload.php" method="POST" enctype="multipart/form-data" class="space-y-6" id="pestForm">
                        <div class="flex items-center justify-center w-full">
                            <label for="pest_image" class="flex flex-col items-center justify-center w-full h-64 border-2 border-red-300 border-dashed rounded-lg cursor-pointer bg-red-50 hover:bg-red-100 transition">
                                <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                    <svg class="w-12 h-12 mb-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <p class="mb-2 text-lg text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                    <p class="text-sm text-gray-400">PNG, JPG or GIF (MAX. 5MB)</p>
                                    <img id="pestPreview" src="" alt="Preview" style="display:none; max-height: 120px; margin-top: 10px; border-radius: 8px;">
                                </div>
                                <input id="pest_image" name="pest_image" type="file" class="hidden" accept="image/*" required />
                            </label>
                        </div>
                        <?php if (isset($_SESSION['last_uploaded_pest_image'])): ?>
                        <div class="mt-6">
                            <h3 class="text-xl font-semibold text-gray-800 mb-3">Last Uploaded Image</h3>
                            <img src="<?php echo htmlspecialchars($_SESSION['last_uploaded_pest_image']); ?>" 
                                 alt="Last uploaded pest" 
                                 class="w-full h-64 object-cover rounded-lg shadow-md">
                        </div>
                        <?php unset($_SESSION['last_uploaded_pest_image']); ?>
                        <?php endif; ?>
                        <div class="text-center">
                            <button type="submit" class="btn-danger-custom px-8 py-3 rounded-lg font-medium inline-block">
                                Identify Pest
                            </button>
                        </div>
                    </form>
                </div>
                
                <div class="bg-white rounded-lg p-6 mb-8">
                    <h2 class="text-2xl font-bold text-gray-800 mb-4">Common Plant Pests</h2>
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="pest-card p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Aphids</h3>
                            <p class="text-gray-600 mb-3">Small sap-sucking insects that cluster on new growth and the undersides of leaves.</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="treatment-tag">Neem Oil</span>
                                <span class="treatment-tag">Insecticidal Soap</span>
                                <span class="treatment-tag">Ladybugs</span>
                            </div>
                        </div>
                        
                        <div class="pest-card p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Spider Mites</h3>
                            <p class="text-gray-600 mb-3">Tiny pests that create fine webbing and cause stippled, discolored leaves.</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="treatment-tag">Neem Oil</span>
                                <span class="treatment-tag">Humidity Increase</span>
                                <span class="treatment-tag">Isolation</span>
                            </div>
                        </div>
                        
                        <div class="pest-card p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Mealybugs</h3>
                            <p class="text-gray-600 mb-3">White, cottony pests that cluster in leaf joints and under leaves.</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="treatment-tag">Alcohol Swab</span>
                                <span class="treatment-tag">Neem Oil</span>
                                <span class="treatment-tag">Pruning</span>
                            </div>
                        </div>
                        
                        <div class="pest-card p-4">
                            <h3 class="text-lg font-semibold text-gray-800 mb-2">Scale Insects</h3>
                            <p class="text-gray-600 mb-3">Small, immobile insects that appear as bumps on stems and leaves.</p>
                            <div class="flex flex-wrap gap-2">
                                <span class="treatment-tag">Horticultural Oil</span>
                                <span class="treatment-tag">Manual Removal</span>
                                <span class="treatment-tag">Systemic Insecticide</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="text-center">
                    <a href="pest_history.php" class="text-primary hover:text-primary-light font-medium">
                        View Your Pest Identification History →
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview uploaded image (show only one preview)
        const input = document.getElementById('pest_image');
        const pestPreview = document.getElementById('pestPreview');
        input.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    pestPreview.src = e.target.result;
                    pestPreview.style.display = 'block';
                }
                reader.readAsDataURL(file);
            } else {
                pestPreview.src = '';
                pestPreview.style.display = 'none';
            }
        });
        // Remove preview after form submission
        document.getElementById('pestForm').addEventListener('submit', function() {
            pestPreview.src = '';
            pestPreview.style.display = 'none';
        });
    </script>
</body>
</html> 