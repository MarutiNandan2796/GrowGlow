<?php
session_start();
require_once 'config/database.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';
$plants = [];

if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT * FROM common_plants 
                          WHERE name LIKE :search 
                          OR scientific_name LIKE :search 
                          OR description LIKE :search 
                          LIMIT 10");
    $stmt->execute(['search' => "%$search%"]);
    $plants = $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Plants - Grow-Glow</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
    </style>
</head>
<body class="min-h-screen flex flex-col hero-gradient">
    <?php include 'includes/header.php'; ?>
    
    <main class="flex-grow py-12">
        <div class="container mx-auto px-4">
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-800 mb-2">
                    <i class="fas fa-search text-green-600"></i> Search Results
                </h1>
                <p class="text-gray-600">
                    <?php if (!empty($search)): ?>
                        Showing results for "<?php echo htmlspecialchars($search); ?>"
                    <?php else: ?>
                        Enter a plant name to search
                    <?php endif; ?>
                </p>
            </div>

            <!-- Search Box -->
            <div class="max-w-xl mx-auto mb-8">
                <form action="" method="GET" class="flex gap-2">
                    <input type="text" 
                           name="q" 
                           value="<?php echo htmlspecialchars($search); ?>" 
                           placeholder="Search for plants..." 
                           class="flex-1 px-4 py-2 rounded-lg border border-gray-300 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-search mr-2"></i> Search
                    </button>
                </form>
            </div>

            <!-- Results -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php if (empty($search)): ?>
                    <div class="col-span-full text-center py-8">
                        <div class="bg-white p-8 rounded-lg shadow-sm max-w-md mx-auto">
                            <i class="fas fa-leaf text-5xl text-green-500 mb-4"></i>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">Start Your Search</h3>
                            <p class="text-gray-600">Enter a plant name to learn more about it</p>
                        </div>
                    </div>
                <?php elseif (empty($plants)): ?>
                    <div class="col-span-full text-center py-8">
                        <div class="bg-white p-8 rounded-lg shadow-sm max-w-md mx-auto">
                            <i class="fas fa-search text-5xl text-gray-400 mb-4"></i>
                            <h3 class="text-xl font-bold text-gray-800 mb-2">No Results Found</h3>
                            <p class="text-gray-600">Try searching with a different term</p>
                        </div>
                    </div>
                <?php else: ?>
                    <?php foreach ($plants as $plant): ?>
                        <div class="plant-card p-6">
                            <h3 class="text-xl font-bold text-gray-800 mb-2">
                                <?php echo htmlspecialchars($plant['name']); ?>
                                <?php if (!empty($plant['scientific_name'])): ?>
                                    <span class="text-sm text-gray-500 italic">
                                        (<?php echo htmlspecialchars($plant['scientific_name']); ?>)
                                    </span>
                                <?php endif; ?>
                            </h3>
                            <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($plant['description']); ?></p>
                            
                            <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                                <div>
                                    <p class="font-semibold text-gray-700">Light:</p>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($plant['light_requirements']); ?></p>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-700">Water:</p>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($plant['water_requirements']); ?></p>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-700">Temperature:</p>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($plant['temperature_range']); ?></p>
                                </div>
                                <div>
                                    <p class="font-semibold text-gray-700">Growth Rate:</p>
                                    <p class="text-gray-600"><?php echo htmlspecialchars($plant['growth_rate']); ?></p>
                                </div>
                            </div>
                            
                            <button class="w-full bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition" 
                                    onclick="showPlantDetails(<?php echo htmlspecialchars(json_encode($plant)); ?>)">
                                <i class="fas fa-info-circle mr-2"></i> View Full Details
                            </button>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <!-- Plant Details Modal -->
    <div id="plantModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center">
        <div class="bg-white rounded-lg p-8 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-start mb-4">
                <h2 id="modalTitle" class="text-2xl font-bold text-gray-800"></h2>
                <button onclick="closeModal()" class="text-gray-500 hover:text-gray-700">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div id="modalContent" class="space-y-4"></div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script>
        function showPlantDetails(plant) {
            const modal = document.getElementById('plantModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalContent = document.getElementById('modalContent');
            
            modalTitle.innerHTML = `${plant.name} <span class="text-sm text-gray-500 italic">(${plant.scientific_name})</span>`;
            
            modalContent.innerHTML = `
                <div class="space-y-4">
                    <div>
                        <h3 class="font-semibold text-gray-700">Description</h3>
                        <p class="text-gray-600">${plant.description}</p>
                    </div>
                    <div>
                        <h3 class="font-semibold text-gray-700">Care Instructions</h3>
                        <p class="text-gray-600">${plant.care_instructions}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <h3 class="font-semibold text-gray-700">Light Requirements</h3>
                            <p class="text-gray-600">${plant.light_requirements}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700">Water Requirements</h3>
                            <p class="text-gray-600">${plant.water_requirements}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700">Temperature Range</h3>
                            <p class="text-gray-600">${plant.temperature_range}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700">Humidity Requirements</h3>
                            <p class="text-gray-600">${plant.humidity_requirements}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700">Soil Type</h3>
                            <p class="text-gray-600">${plant.soil_type}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700">Fertilizer Needs</h3>
                            <p class="text-gray-600">${plant.fertilizer_needs}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700">Growth Rate</h3>
                            <p class="text-gray-600">${plant.growth_rate}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700">Mature Height</h3>
                            <p class="text-gray-600">${plant.mature_height}</p>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-700">Toxicity Level</h3>
                            <p class="text-gray-600">${plant.toxicity_level}</p>
                        </div>
                    </div>
                </div>
            `;
            
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        
        function closeModal() {
            const modal = document.getElementById('plantModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        
        // Close modal when clicking outside
        document.getElementById('plantModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html> 