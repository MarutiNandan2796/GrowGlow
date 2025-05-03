<?php 
session_start();
// Enable error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Debug output
error_log("Session data in index.php: " . print_r($_SESSION, true));
if (isset($_SESSION['last_uploaded_image'])) {
    error_log("Last uploaded image path: " . $_SESSION['last_uploaded_image']);
    error_log("File exists: " . (file_exists($_SESSION['last_uploaded_image']) ? 'Yes' : 'No'));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grow-Glow - Your Plant Care Companion</title>
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
        
        .hero-section {
            background: linear-gradient(135deg, rgba(46,139,87,0.05) 0%, rgba(255,255,255,1) 100%);
            min-height: 75vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
            margin-top: -1rem;
        }
        
        .hero-image-container {
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 45%;
            height: 90%;
            border-radius: 30px 0 0 30px;
            overflow: hidden;
            box-shadow: -10px 10px 40px rgba(0,0,0,0.1);
        }
        
        .hero-image {
            width: 100%;
            height: 100%;
            object-fit: contain;
            object-position: center;
            transition: transform 0.3s ease;
        }
        
        .hero-image:hover {
            transform: scale(1.05);
        }
        
        .hero-content {
            width: 55%;
            padding-left: 5%;
            position: relative;
            z-index: 1;
        }
        
        .hero-title {
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 1rem;
            color: #1a1a1a;
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
            color: #666;
            margin-bottom: 1.5rem;
            max-width: 500px;
        }
        
        .hero-buttons {
            display: flex;
            gap: 1rem;
        }
        
        .btn-hero {
            padding: 0.75rem 2rem;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary-hero {
            background: var(--primary);
            color: white;
        }
        
        .btn-primary-hero:hover {
            background: var(--primary-light);
            transform: translateY(-2px);
        }
        
        .btn-secondary-hero {
            background: white;
            color: var(--primary);
            border: 2px solid var(--primary);
        }
        
        .btn-secondary-hero:hover {
            background: var(--primary);
            color: white;
        }
        
        @media (max-width: 1024px) {
            .hero-section {
                min-height: auto;
                padding: 1rem 0;
                margin-top: 0;
            }
            
            .hero-image-container {
                width: 100%;
                height: 45vh;
                position: relative;
                right: 0;
                top: 0;
                transform: none;
                border-radius: 0;
                margin-top: 1rem;
            }
            
            .hero-content {
                width: 100%;
                padding: 1rem;
                text-align: center;
            }
            
            .hero-title {
                font-size: 2.5rem;
            }
            
            .hero-buttons {
                justify-content: center;
            }
        }
        
        .animate-glow {
            animation: glow 2s ease-in-out infinite alternate;
        }
        
        @keyframes glow {
            from {
                filter: drop-shadow(0 0 2px rgba(46, 204, 113, 0.6));
                transform: scale(1);
            }
            to {
                filter: drop-shadow(0 0 12px rgba(46, 204, 113, 0.8));
                transform: scale(1.05);
            }
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5fef5;
            color: #333;
        }
        
        .hero-gradient {
            background: linear-gradient(135deg, rgba(46,139,87,0.1) 0%, rgba(255,255,255,1) 100%);
            padding: 0; /* Remove default padding for full-width image */
        }
        
        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, rgba(0,0,0,0.2), rgba(0,0,0,0));
        }

        .content-section {
            padding-top: 2rem;
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
        
        .reminder-link {
            display: inline-block;
            margin-top: 10px;
            color: var(--primary);
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .reminder-link:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <?php include 'includes/header.php'; ?>
    
    <main class="flex-grow">
        <section class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">
                    Welcome to <span class="text-green-600">Grow-Glow</span>
                </h1>
                <p class="hero-subtitle">
                    Your ultimate plant care companion for identifying plants, tracking their care, and getting personalized reminders.
                </p>

                <!-- Plant Search Box -->
                <div class="max-w-xl mx-auto my-8">
                    <form action="search_plants.php" method="GET" class="flex gap-2">
                        <input type="text" 
                               name="q" 
                               placeholder="Search for plants..." 
                               class="flex-1 px-4 py-3 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent text-lg">
                        <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 transition text-lg">
                            <i class="fas fa-search mr-2"></i> Search
                        </button>
                    </form>
                </div>

                <div class="hero-buttons">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <a href="register.php" class="btn-hero btn-primary-hero">Get Started</a>
                        <a href="login.php" class="btn-hero btn-secondary-hero">Sign In</a>
                    <?php else: ?>
                        <button onclick="scrollToUpload()" class="btn-hero btn-primary-hero">Identify Plants</button>
                        <a href="my_plants.php" class="btn-hero btn-secondary-hero">My Plants</a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="hero-image-container">
                <img src="\grow glow\plantpal\Plantpal\assets\images\Screenshot 2025-04-22 023508.png" 
                     alt="Grow-Glow Hero" 
                     class="hero-image">
            </div>
        </section>

        <div class="content-section">
            <div class="container mx-auto px-4">
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                        <div class="flex">
                            <div class="py-1">
                                <svg class="h-6 w-6 text-red-500 mr-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <div>
                                <p class="font-bold">Error</p>
                                <p><?php echo htmlspecialchars($_SESSION['error']); ?></p>
                            </div>
                        </div>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>
                <div class="flex flex-col lg:flex-row gap-8 items-center">
                    <!-- Left Column -->
                    <div class="lg:w-1/2 space-y-8">
                        <div class="space-y-6">
                            <div class="feature-item p-5 rounded-lg bg-white shadow-sm">
                                <h3 class="text-xl font-bold text-green-700 mb-2">Plant Identification</h3>
                                <p class="text-gray-600">Upload a photo to identify any plant instantly with our advanced AI technology.</p>
                            </div>
                            <div class="feature-item p-5 rounded-lg bg-white shadow-sm">
                                <h3 class="text-xl font-bold text-green-700 mb-2">Care Tracking</h3>
                                <p class="text-gray-600">Never forget to water your plants again with our personalized care schedules.</p>
                            </div>
                            <div class="feature-item p-5 rounded-lg bg-white shadow-sm">
                                <h3 class="text-xl font-bold text-green-700 mb-2">Plant Reminders</h3>
                                <p class="text-gray-600">Track when your plants were last watered and get notified when it's time to water them again.</p>
                                <a href="reminders.php" class="reminder-link">View my plant reminders →</a>
                            </div>
                            <div class="feature-item p-5 rounded-lg bg-white shadow-sm">
                                <h3 class="text-xl font-bold text-green-700 mb-2">Pest Control</h3>
                                <p class="text-gray-600">Get expert advice on dealing with common plant pests and diseases.</p>
                                <a href="pest_identification.php" class="reminder-link">Identify plant pests →</a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column -->
                    <div class="lg:w-1/2">
                        <div id="upload-section" class="upload-section p-8">
                            <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Identify Your Plant</h2>
                            <form action="process_upload.php" method="POST" enctype="multipart/form-data" id="plantForm">
                                <div class="mb-6">
                                    <div class="flex items-center justify-center w-full">
                                        <label for="plant_image" class="flex flex-col items-center justify-center w-full h-64 border-2 border-green-300 border-dashed rounded-lg cursor-pointer bg-green-50 hover:bg-green-100 transition">
                                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                                <svg class="w-12 h-12 mb-4 text-green-500" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                                                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                                                </svg>
                                                <p class="mb-2 text-lg text-gray-500"><span class="font-semibold">Click to upload</span> or drag and drop</p>
                                                <p class="text-sm text-gray-400">SVG, PNG, JPG or GIF (MAX. 5MB)</p>
                                            </div>
                                            <input id="plant_image" type="file" class="hidden" name="plant_image" accept="image/*" required />
                                        </label>
                                    </div>
                                    <!-- Image Preview Container -->
                                    <div id="imagePreviewContainer" class="mt-4 hidden">
                                        <h3 class="text-lg font-semibold text-gray-700 mb-2">Selected Image:</h3>
                                        <img id="imagePreview" src="" alt="Selected image preview" class="w-full h-64 object-cover rounded-lg shadow-md">
                                    </div>
                                </div>
                                <button type="submit" class="w-full btn-plant py-3 rounded-lg font-semibold text-lg shadow-md" id="identifyBtn">
                                    Identify Plant
                                </button>
                            </form>

                            <?php if (isset($_SESSION['last_uploaded_image']) && file_exists($_SESSION['last_uploaded_image'])): ?>
                                <div class="mt-6">
                                    <h3 class="text-xl font-semibold text-gray-800 mb-3">Last Uploaded Image</h3>
                                    <img src="<?php echo htmlspecialchars($_SESSION['last_uploaded_image']); ?>" 
                                         alt="Last uploaded plant" 
                                         class="w-full h-64 object-cover rounded-lg shadow-md">
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="mt-8 bg-white p-6 rounded-lg shadow-sm">
                            <h3 class="text-xl font-bold text-green-700 mb-4">New to Grow-Glow?</h3>
                            <p class="text-gray-600 mb-4">Join our community of plant lovers and get access to all features!</p>
                            <?php if (!isset($_SESSION['user_id'])): ?>
                                <a href="register.php" class="btn-accent px-6 py-3 rounded-lg font-semibold inline-block text-center">
                                    Create Free Account
                                </a>
                            <?php else: ?>
                                <a href="my_plants.php" class="btn-accent px-6 py-3 rounded-lg font-semibold inline-block text-center">
                                    View My Plants
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Image preview functionality
        document.getElementById('plant_image').addEventListener('change', function(e) {
            const file = e.target.files[0];
            const previewContainer = document.getElementById('imagePreviewContainer');
            const preview = document.getElementById('imagePreview');

            // Remove any previous preview image
            preview.src = '';
            previewContainer.classList.add('hidden');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                }
                reader.readAsDataURL(file);
            }
        });

        // Add loading state to Identify Plant button on form submit
        document.getElementById('plantForm').addEventListener('submit', function() {
            const btn = document.getElementById('identifyBtn');
            btn.disabled = true;
            btn.textContent = 'Uploading...';
        });

        // Function to scroll to upload section
        function scrollToUpload() {
            const uploadSection = document.getElementById('upload-section');
            if (uploadSection) {
                uploadSection.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    </script>
</body>
</html>