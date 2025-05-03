<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['identified_plant'])) {
    header('Location: index.php');
    exit;
}

$plant = $_SESSION['identified_plant'];
$uploaded_image = $_SESSION['plant_image'];
$api_image = $plant['api_image'] ?? $uploaded_image;
$confidence_percent = round(($plant['confidence'] ?? 0) * 100);

include 'includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Plant Identification Result</h1>
            <p class="text-gray-600">Identified on <?php echo date('F j, Y'); ?></p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Uploaded Image Section -->
            <div class="bg-gray-50 rounded-lg p-4">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">Your Uploaded Image</h2>
                <div class="relative aspect-square rounded-lg overflow-hidden">
                    <img src="<?php echo htmlspecialchars($uploaded_image); ?>" 
                         alt="Uploaded Plant" 
                         class="absolute inset-0 w-full h-full object-cover">
                </div>
            </div>
            <!-- AI Suggested Image Section (if different) -->
            <?php if ($api_image && $api_image !== $uploaded_image): ?>
            <div class="bg-gray-50 rounded-lg p-4">
                <h2 class="text-xl font-semibold text-gray-800 mb-4">AI Suggested Image</h2>
                <div class="relative aspect-square rounded-lg overflow-hidden">
                    <img src="<?php echo htmlspecialchars($api_image); ?>" 
                         alt="AI Suggested Plant" 
                         class="absolute inset-0 w-full h-full object-cover">
                </div>
            </div>
            <?php endif; ?>

            <!-- Results Section -->
            <div class="space-y-6">
                <div class="bg-green-50 rounded-lg p-6">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($plant['name']); ?></h2>
                    <?php if (!empty($plant['scientific_name'])): ?>
                        <p class="text-gray-600 italic mb-4"><?php echo htmlspecialchars($plant['scientific_name']); ?></p>
                    <?php endif; ?>
                    <div class="mb-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-gray-600">Confidence</span>
                            <span class="text-green-600 font-semibold"><?php echo $confidence_percent; ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2.5">
                            <div class="bg-green-600 h-2.5 rounded-full" style="width: <?php echo $confidence_percent; ?>%"></div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Description</h3>
                    <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($plant['wiki_description'])); ?></p>
                </div>

                <div class="bg-white rounded-lg p-6 border border-gray-200">
                    <h3 class="text-xl font-semibold text-gray-800 mb-4">Care Instructions</h3>
                    <div class="text-gray-600">
                        <?php echo nl2br(htmlspecialchars($plant['care_instructions'])); ?>
                    </div>
                </div>

                <?php if (!empty($plant['common_names'])): ?>
                    <div class="bg-white rounded-lg p-6 border border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-800 mb-4">Common Names</h3>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($plant['common_names'] as $name): ?>
                                <span class="bg-gray-100 text-gray-800 px-3 py-1 rounded-full text-sm">
                                    <?php echo htmlspecialchars($name); ?>
                                </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="mt-8 flex justify-between items-center">
            <a href="index.php" class="inline-flex items-center text-green-600 hover:text-green-700">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Back to Home
            </a>
            <a href="my_plants.php" class="inline-flex items-center text-green-600 hover:text-green-700">
                View My Plants
                <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                </svg>
            </a>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 