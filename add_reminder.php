<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['plant_id'])) {
    header('Location: my_plants.php');
    exit;
}

$plant_id = $_GET['plant_id'];

$stmt = $pdo->prepare("SELECT * FROM plants WHERE id = ? AND user_id = ?");
$stmt->execute([$plant_id, $_SESSION['user_id']]);
$plant = $stmt->fetch();

if (!$plant) {
    header('Location: my_plants.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $reminder_type = $_POST['reminder_type'];
    $reminder_date = $_POST['reminder_date'];
    $notes = $_POST['notes'] ?? '';
    $watering_frequency = null;
    
    if ($reminder_type === 'watering' && isset($_POST['watering_frequency'])) {
        $watering_frequency = $_POST['watering_frequency'];
    }

    try {
        // Start a transaction
        $pdo->beginTransaction();
        
        // Insert the reminder
        $stmt = $pdo->prepare("INSERT INTO reminders (plant_id, user_id, reminder_type, reminder_date, notes) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$plant_id, $_SESSION['user_id'], $reminder_type, $reminder_date, $notes]);
        
        // If it's a watering reminder and frequency is set, update the plant's watering frequency
        if ($reminder_type === 'watering' && $watering_frequency) {
            $stmt = $pdo->prepare("UPDATE plants SET watering_frequency = ? WHERE id = ?");
            $stmt->execute([$watering_frequency, $plant_id]);
        }
        
        // Commit the transaction
        $pdo->commit();
        
        $_SESSION['success'] = "Reminder added successfully!";
        header('Location: reminders.php');
        exit;
    } catch (PDOException $e) {
        // Rollback the transaction on error
        $pdo->rollBack();
        $error = "Error adding reminder. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Reminder - PlantPal</title>
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
        
        .card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
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
        
        .btn-secondary {
            background-color: #6c757d;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background-color: #5a6268;
            transform: translateY(-2px);
        }
        
        .form-control, .form-select {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(46,139,87,0.2);
            outline: none;
        }
        
        .form-label {
            font-weight: 500;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }
        
        .watering-frequency {
            display: none;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <?php include 'includes/header.php'; ?>
    
    <main class="flex-grow hero-gradient py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-2xl mx-auto">
                <div class="card p-8">
                    <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Add Reminder for <span class="text-green-700"><?php echo htmlspecialchars($plant['name']); ?></span></h2>
                    
                    <?php if (isset($error)): ?>
                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="add_reminder.php?plant_id=<?php echo $plant_id; ?>" class="space-y-6">
                        <div>
                            <label for="reminder_type" class="form-label">Reminder Type</label>
                            <select class="form-select w-full" id="reminder_type" name="reminder_type" required>
                                <option value="watering">Watering</option>
                                <option value="fertilizing">Fertilizing</option>
                                <option value="repotting">Repotting</option>
                                <option value="pruning">Pruning</option>
                                <option value="other">Other</option>
                            </select>
                        </div>

                        <div id="watering_frequency_container" class="watering-frequency">
                            <label for="watering_frequency" class="form-label">Watering Frequency (days)</label>
                            <input type="number" class="form-control w-full" id="watering_frequency" name="watering_frequency" min="1" value="<?php echo $plant['watering_frequency'] ?? 7; ?>">
                            <p class="text-sm text-gray-500 mt-1">This will be used to calculate the next watering date.</p>
                        </div>

                        <div>
                            <label for="reminder_date" class="form-label">Reminder Date</label>
                            <input type="date" class="form-control w-full" id="reminder_date" name="reminder_date" required 
                                   min="<?php echo date('Y-m-d'); ?>" 
                                   value="<?php echo date('Y-m-d'); ?>">
                        </div>

                        <div>
                            <label for="notes" class="form-label">Notes (Optional)</label>
                            <textarea class="form-control w-full" id="notes" name="notes" rows="3" 
                                      placeholder="Add any specific instructions or notes about this reminder"></textarea>
                        </div>

                        <div class="flex flex-col sm:flex-row gap-4 mt-6">
                            <button type="submit" class="btn-plant px-6 py-3 rounded-lg font-medium flex-grow">
                                Add Reminder
                            </button>
                            <a href="my_plants.php" class="btn-secondary px-6 py-3 rounded-lg font-medium text-center">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Show/hide watering frequency field based on reminder type
        document.getElementById('reminder_type').addEventListener('change', function() {
            const wateringFrequencyContainer = document.getElementById('watering_frequency_container');
            if (this.value === 'watering') {
                wateringFrequencyContainer.style.display = 'block';
            } else {
                wateringFrequencyContainer.style.display = 'none';
            }
        });
        
        // Trigger the change event on page load
        document.addEventListener('DOMContentLoaded', function() {
            const reminderTypeSelect = document.getElementById('reminder_type');
            const event = new Event('change');
            reminderTypeSelect.dispatchEvent(event);
        });
    </script>
</body>
</html> 