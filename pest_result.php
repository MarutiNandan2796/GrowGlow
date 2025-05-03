<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: pest_identification.php');
    exit;
}

$identification_id = $_GET['id'];

// Fetch the pest identification result
$stmt = $pdo->prepare("SELECT * FROM pest_identifications WHERE id = ? AND user_id = ?");
$stmt->execute([$identification_id, $_SESSION['user_id']]);
$result = $stmt->fetch();

if (!$result) {
    header('Location: pest_identification.php');
    exit;
}

include 'includes/header.php';
?>

<div class="max-w-4xl mx-auto bg-white rounded-lg shadow-lg p-6">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800 mb-2">Pest Identification Result</h1>
        <p class="text-gray-600">Identified on <?php echo date('F j, Y', strtotime($result['created_at'])); ?></p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <!-- Image Section -->
        <div class="bg-gray-50 rounded-lg p-4">
            <h2 class="text-xl font-semibold text-gray-800 mb-4">Uploaded Image</h2>
            <div class="relative aspect-square rounded-lg overflow-hidden">
                <img src="<?php echo htmlspecialchars($result['image_path']); ?>" 
                     alt="Pest Image" 
                     class="absolute inset-0 w-full h-full object-cover">
            </div>
        </div>

        <!-- Results Section -->
        <div class="space-y-6">
            <div class="bg-green-50 rounded-lg p-6">
                <h2 class="text-2xl font-bold text-gray-800 mb-2"><?php echo htmlspecialchars($result['pest_name']); ?></h2>
                <div class="mb-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-gray-600">Confidence</span>
                        <span class="text-green-600 font-semibold"><?php echo round($result['confidence'] * 100); ?>%</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2.5">
                        <div class="bg-green-600 h-2.5 rounded-full" style="width: <?php echo $result['confidence'] * 100; ?>%"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg p-6 border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Description</h3>
                <p class="text-gray-600"><?php echo nl2br(htmlspecialchars($result['description'])); ?></p>
            </div>

            <div class="bg-white rounded-lg p-6 border border-gray-200">
                <h3 class="text-xl font-semibold text-gray-800 mb-4">Recommended Treatments</h3>
                <ul class="list-disc list-inside space-y-2 text-gray-600">
                    <?php 
                    $treatments = json_decode($result['treatments'], true);
                    if (is_array($treatments)) {
                        foreach ($treatments as $treatment) {
                            echo '<li>' . htmlspecialchars($treatment) . '</li>';
                        }
                    }
                    ?>
                </ul>
            </div>
        </div>
    </div>

    <div class="mt-8 flex justify-between items-center">
        <a href="pest_identification.php" class="inline-flex items-center text-green-600 hover:text-green-700">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Back to Identification
        </a>
        <a href="my_plants.php" class="inline-flex items-center text-green-600 hover:text-green-700">
            View My Plants
            <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3" />
            </svg>
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 