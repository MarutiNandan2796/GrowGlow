<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = null;
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION['user_id'];
    $plant_name = trim($_POST['plant_name'] ?? '');
    $scientific_name = trim($_POST['scientific_name'] ?? '');
    $last_watered = $_POST['last_watered'] ?? null;
    $care_instructions = trim($_POST['care_instructions'] ?? '');
    $image_path = 'assets/images/default_plant.png'; 

    if (empty($plant_name)) {
        $error = "Plant name is required.";
    } else {
        if (isset($_FILES['plant_image']) && $_FILES['plant_image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['plant_image']['type'];

            if (in_array($file_type, $allowed_types)) {
                $upload_dir = 'uploads/plants/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                $file_extension = pathinfo($_FILES['plant_image']['name'], PATHINFO_EXTENSION);
                $file_name = uniqid('manual_', true) . '.' . $file_extension;
                $target_path = $upload_dir . $file_name;

                if (move_uploaded_file($_FILES['plant_image']['tmp_name'], $target_path)) {
                    $image_path = $target_path; 
                } else {
                    $error = "Failed to move uploaded file.";
                }
            } else {
                $error = "Invalid image file type. Allowed types: JPG, PNG, GIF.";
            }
        }
        
        if ($error === null) {
             try {
                $stmt = $pdo->prepare("INSERT INTO plants (user_id, name, scientific_name, image_path, care_instructions, last_watered) 
                                      VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([
                    $user_id,
                    $plant_name,
                    empty($scientific_name) ? null : $scientific_name,
                    $image_path,
                    empty($care_instructions) ? null : $care_instructions,
                    empty($last_watered) ? null : $last_watered
                ]);

                $_SESSION['success'] = "Plant '" . htmlspecialchars($plant_name) . "' added successfully!";
                header('Location: my_plants.php'); 
                exit;
            } catch (PDOException $e) {
                error_log("Error adding plant: " . $e->getMessage());
                $error = "Database error occurred while adding the plant.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Plant - PlantPal</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
         body {
            background-color: #f8f9fa; 
        }
    </style>
</head>
<body>
    <?php /* include 'includes/header.php'; */ ?> 

    <main class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow-sm">
                    <div class="card-body p-4">
                        <h1 class="card-title text-center mb-4">Add a New Plant</h1>

                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <?php if ($success): ?>
                             <div class="alert alert-success"><?php echo $success; ?></div>
                         <?php endif; ?>

                        <form action="add_manual_plant.php" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="plant_name" class="form-label">Plant Name *</label>
                                <input type="text" class="form-control" id="plant_name" name="plant_name" required value="<?php echo isset($_POST['plant_name']) ? htmlspecialchars($_POST['plant_name']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="scientific_name" class="form-label">Scientific Name (Optional)</label>
                                <input type="text" class="form-control" id="scientific_name" name="scientific_name" value="<?php echo isset($_POST['scientific_name']) ? htmlspecialchars($_POST['scientific_name']) : ''; ?>">
                            </div>

                            <div class="mb-3">
                                <label for="last_watered" class="form-label">Last Watered Date (Optional)</label>
                                <input type="date" class="form-control" id="last_watered" name="last_watered" value="<?php echo isset($_POST['last_watered']) ? htmlspecialchars($_POST['last_watered']) : ''; ?>">
                            </div>

                             <div class="mb-3">
                                <label for="plant_image" class="form-label">Plant Image (Optional)</label>
                                <input type="file" class="form-control" id="plant_image" name="plant_image" accept="image/*">
                                <small class="form-text text-muted">If not provided, a default image will be used.</small>
                            </div>

                            <div class="mb-4">
                                <label for="care_instructions" class="form-label">Care Instructions (Optional)</label>
                                <textarea class="form-control" id="care_instructions" name="care_instructions" rows="4"><?php echo isset($_POST['care_instructions']) ? htmlspecialchars($_POST['care_instructions']) : ''; ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">Add Plant</button>
                                <a href="my_plants.php" class="btn btn-outline-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php /* include 'includes/footer.php'; */ ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 