<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// First, let's try to add the columns if they don't exist
try {
    $pdo->exec("ALTER TABLE plants ADD COLUMN IF NOT EXISTS last_watered DATE DEFAULT NULL");
    $pdo->exec("ALTER TABLE plants ADD COLUMN IF NOT EXISTS watering_frequency INT DEFAULT NULL");
} catch (PDOException $e) {
    // If there's an error, we'll continue anyway as the columns might already exist
}

// Fetch all reminders for the user, ordered by date
$stmt = $pdo->prepare("SELECT r.*, p.name as plant_name, 
                             p.last_watered, p.watering_frequency 
                      FROM reminders r 
                      JOIN plants p ON r.plant_id = p.id 
                      WHERE r.user_id = ? 
                      ORDER BY r.reminder_date ASC, r.is_completed ASC");
$stmt->execute([$user_id]);
$reminders = $stmt->fetchAll();

$today = date('Y-m-d');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reminders - PlantPal</title>
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
        
        .overdue {
            background-color: #fef2f2;
            border-left-color: #ef4444;
        }
        
        .upcoming {
            background-color: #fff8e1;
            border-left-color: #f59e0b;
        }
        
        .completed {
            background-color: #d1fae5;
            border-left-color: #10b981;
            opacity: 0.8;
        }
        
        .reminder-card {
            border: 1px solid rgba(0,0,0,0.05);
            border-left-width: 5px;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .reminder-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
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
        
        .date-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .date-badge.last-watered {
            background-color: #e0f2fe;
            color: #0284c7;
        }
        
        .date-badge.next-watering {
            background-color: #fef3c7;
            color: #d97706;
        }
        
        .date-badge.overdue {
            background-color: #fee2e2;
            color: #dc2626;
        }
        
        .plant-name {
            color: var(--primary);
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .plant-name:hover {
            color: var(--primary-light);
            text-decoration: underline;
        }
        
        .reminder-type {
            font-weight: 600;
            color: #4b5563;
        }
        
        .reminder-notes {
            color: #6b7280;
            font-style: italic;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <?php include 'includes/header.php'; ?>
    
    <main class="flex-grow hero-gradient py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-4xl font-bold text-gray-800 mb-6">My Plant Reminders</h1>
                
                <?php if (isset($_SESSION['success'])): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                <?php endif; ?>

                <?php if (empty($reminders)): ?>
                    <div class="bg-blue-100 border-l-4 border-blue-500 text-blue-700 p-4 mb-6 rounded">
                        <p class="font-medium">You have no reminders set up yet.</p>
                        <p class="mt-2">Add reminders from the <a href="my_plants.php" class="text-blue-600 underline">My Plants</a> page.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php foreach ($reminders as $reminder): 
                            $status_class = 'upcoming';
                            $status_text = 'Upcoming';
                            
                            if ($reminder['is_completed']) {
                                $status_class = 'completed';
                                $status_text = 'Completed';
                            } elseif ($reminder['reminder_date'] < $today) {
                                $status_class = 'overdue';
                                $status_text = 'Overdue';
                            }
                            
                            // Calculate next watering date if this is a watering reminder
                            $next_watering_date = null;
                            if ($reminder['reminder_type'] === 'watering' && $reminder['last_watered'] && $reminder['watering_frequency']) {
                                $last_watered = new DateTime($reminder['last_watered']);
                                $interval = new DateInterval('P' . $reminder['watering_frequency'] . 'D');
                                $next_watering = $last_watered->add($interval);
                                $next_watering_date = $next_watering->format('Y-m-d');
                            }
                        ?>
                            <div class="reminder-card <?php echo $status_class; ?>">
                                <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-3">
                                    <div>
                                        <h3 class="text-xl font-semibold mb-1">
                                            <span class="reminder-type"><?php echo ucfirst(htmlspecialchars($reminder['reminder_type'])); ?></span> - 
                                            <a href="plant_details.php?id=<?php echo $reminder['plant_id']; ?>" class="plant-name">
                                                <?php echo htmlspecialchars($reminder['plant_name']); ?>
                                            </a>
                                        </h3>
                                        
                                        <div class="flex flex-wrap gap-2 mt-2">
                                            <span class="date-badge <?php echo $status_class; ?>">
                                                <?php echo $status_text; ?>: <?php echo date('F j, Y', strtotime($reminder['reminder_date'])); ?>
                                            </span>
                                            
                                            <?php if ($reminder['reminder_type'] === 'watering' && $reminder['last_watered']): ?>
                                                <span class="date-badge last-watered">
                                                    Last Watered: <?php echo date('F j, Y', strtotime($reminder['last_watered'])); ?>
                                                </span>
                                            <?php endif; ?>
                                            
                                            <?php if ($next_watering_date): ?>
                                                <span class="date-badge next-watering">
                                                    Next Watering: <?php echo date('F j, Y', strtotime($next_watering_date)); ?>
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($reminder['notes'])): ?>
                                    <p class="reminder-notes mb-3"><?php echo nl2br(htmlspecialchars($reminder['notes'])); ?></p>
                                <?php endif; ?>
                                
                                <div class="flex flex-wrap gap-2 mt-3">
                                    <?php if (!$reminder['is_completed']): ?>
                                        <form action="complete_reminder.php" method="POST" class="inline">
                                            <input type="hidden" name="reminder_id" value="<?php echo $reminder['id']; ?>">
                                            <button type="submit" class="btn-plant px-4 py-2 rounded-lg text-sm font-medium">
                                                Mark Complete
                                            </button>
                                        </form>
                                        <form action="delete_reminder.php" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this reminder?');">
                                            <input type="hidden" name="reminder_id" value="<?php echo $reminder['id']; ?>">
                                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium transition-all duration-300">
                                                Delete
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-sm text-gray-500 italic">Completed on <?php echo date('F j, Y'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <div class="mt-8 text-center">
                    <a href="my_plants.php" class="btn-plant px-6 py-3 rounded-lg font-medium inline-block">
                        Back to My Plants
                    </a>
                </div>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
</body>
</html> 