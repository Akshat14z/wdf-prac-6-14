<?php
session_start();
require_once 'config.php';
require_once 'EventManager.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

$eventManager = new EventManager($db);

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'title' => trim($_POST['title']),
        'description' => trim($_POST['description']),
        'event_date' => $_POST['event_date'],
        'event_time' => $_POST['event_time'],
        'location' => trim($_POST['location']),
        'organizer' => trim($_POST['organizer']),
        'capacity' => (int)$_POST['capacity'],
        'created_by' => 1 // Default to admin user for now
    ];
    
    // Basic validation
    if (empty($data['title']) || empty($data['description']) || empty($data['event_date']) || 
        empty($data['event_time']) || empty($data['location']) || empty($data['organizer'])) {
        $error_message = "All fields are required!";
    } elseif ($data['capacity'] < 1) {
        $error_message = "Capacity must be at least 1!";
    } elseif (strtotime($data['event_date']) < strtotime('today')) {
        $error_message = "Event date cannot be in the past!";
    } else {
        $result = $eventManager->createEvent($data);
        if ($result) {
            $success_message = "Event created successfully! Event ID: " . $result;
            // Clear form data
            $data = [];
        } else {
            $error_message = "Failed to create event. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management Portal - Create Event</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }

        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .nav-brand {
            font-size: 1.5rem;
            font-weight: 700;
            color: #667eea;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links a:hover {
            color: #667eea;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 2rem;
        }

        .page-header {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }

        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }

        .form-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 3rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .form-group {
            margin-bottom: 2rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .required {
            color: #dc3545;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            transition: border-color 0.3s;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .btn {
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            margin-right: 1rem;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .form-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .input-icon {
            position: relative;
        }

        .input-icon i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
        }

        .input-icon input {
            padding-left: 40px;
        }

        .form-help {
            font-size: 0.9rem;
            color: #666;
            margin-top: 0.5rem;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .form-container {
                padding: 2rem 1.5rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .page-header h1 {
                font-size: 2rem;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                justify-content: center;
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <a href="index.php" class="nav-brand">
                <i class="fas fa-calendar-alt"></i>
                Event Portal
            </a>
            <ul class="nav-links">
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="events.php"><i class="fas fa-list"></i> All Events</a></li>
                <li><a href="create_event.php"><i class="fas fa-plus"></i> Create Event</a></li>
                <li><a href="login.php"><i class="fas fa-user"></i> Login</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>Create New Event</h1>
            <p>Add a new event to the system</p>
        </div>

        <div class="form-container">
            <!-- Alerts -->
            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <!-- Event Title -->
                <div class="form-group">
                    <label for="title">Event Title <span class="required">*</span></label>
                    <div class="input-icon">
                        <i class="fas fa-calendar-check"></i>
                        <input type="text" id="title" name="title" 
                               value="<?php echo isset($data['title']) ? htmlspecialchars($data['title']) : ''; ?>" 
                               required>
                    </div>
                </div>

                <!-- Event Description -->
                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea id="description" name="description" required 
                              placeholder="Provide a detailed description of the event..."><?php echo isset($data['description']) ? htmlspecialchars($data['description']) : ''; ?></textarea>
                    <div class="form-help">Describe the event, its objectives, and what participants can expect.</div>
                </div>

                <!-- Date and Time -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="event_date">Event Date <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-calendar"></i>
                            <input type="date" id="event_date" name="event_date" 
                                   value="<?php echo isset($data['event_date']) ? $data['event_date'] : ''; ?>" 
                                   min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="event_time">Event Time <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-clock"></i>
                            <input type="time" id="event_time" name="event_time" 
                                   value="<?php echo isset($data['event_time']) ? $data['event_time'] : ''; ?>" 
                                   required>
                        </div>
                    </div>
                </div>

                <!-- Location -->
                <div class="form-group">
                    <label for="location">Location <span class="required">*</span></label>
                    <div class="input-icon">
                        <i class="fas fa-map-marker-alt"></i>
                        <input type="text" id="location" name="location" 
                               value="<?php echo isset($data['location']) ? htmlspecialchars($data['location']) : ''; ?>" 
                               placeholder="e.g., Conference Room A, Auditorium, Online" required>
                    </div>
                </div>

                <!-- Organizer and Capacity -->
                <div class="form-row">
                    <div class="form-group">
                        <label for="organizer">Organizer <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="organizer" name="organizer" 
                                   value="<?php echo isset($data['organizer']) ? htmlspecialchars($data['organizer']) : ''; ?>" 
                                   placeholder="Organization or person name" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="capacity">Capacity <span class="required">*</span></label>
                        <div class="input-icon">
                            <i class="fas fa-users"></i>
                            <input type="number" id="capacity" name="capacity" 
                                   value="<?php echo isset($data['capacity']) ? $data['capacity'] : '50'; ?>" 
                                   min="1" max="1000" required>
                        </div>
                        <div class="form-help">Maximum number of participants</div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="events.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Event
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Auto-hide success alerts after 5 seconds
        setTimeout(function() {
            const successAlerts = document.querySelectorAll('.alert-success');
            successAlerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const description = document.getElementById('description').value.trim();
            const eventDate = document.getElementById('event_date').value;
            const eventTime = document.getElementById('event_time').value;
            const location = document.getElementById('location').value.trim();
            const organizer = document.getElementById('organizer').value.trim();
            const capacity = document.getElementById('capacity').value;

            if (!title || !description || !eventDate || !eventTime || !location || !organizer) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return false;
            }

            if (capacity < 1) {
                e.preventDefault();
                alert('Capacity must be at least 1.');
                return false;
            }

            const selectedDate = new Date(eventDate);
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            if (selectedDate < today) {
                e.preventDefault();
                alert('Event date cannot be in the past.');
                return false;
            }
        });
    </script>
</body>
</html>