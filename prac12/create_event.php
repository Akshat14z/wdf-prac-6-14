<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Event Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-calendar-alt"></i> Event Management System</h1>
            <p>Create New Event</p>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-container">
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="events.php"><i class="fas fa-list"></i> All Events</a></li>
                <li><a href="create_event.php" class="active"><i class="fas fa-plus"></i> Create Event</a></li>
                <li><a href="search.php"><i class="fas fa-search"></i> Search Events</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <?php
        require_once 'EventManager.php';

        $message = '';
        $messageType = '';

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $eventManager = new EventManager();
            
            // Prepare event data
            $eventData = [
                'title' => $_POST['title'],
                'description' => $_POST['description'],
                'event_date' => $_POST['event_date'],
                'event_time' => $_POST['event_time'],
                'location' => $_POST['location'],
                'organizer' => $_POST['organizer'],
                'capacity' => (int)$_POST['capacity'],
                'status' => $_POST['status']
            ];
            
            $result = $eventManager->createEvent($eventData);
            
            if ($result['success']) {
                $message = $result['message'];
                $messageType = 'success';
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        }
        ?>

        <div class="card">
            <h2 class="card-title"><i class="fas fa-plus-circle"></i> Create New Event</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                    <?php if ($messageType == 'success'): ?>
                        <br><br>
                        <a href="events.php" class="btn btn-secondary">
                            <i class="fas fa-list"></i> View All Events
                        </a>
                        <a href="create_event.php" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Create Another Event
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="title" class="form-label">
                        <i class="fas fa-tag"></i> Event Title *
                    </label>
                    <input type="text" id="title" name="title" class="form-control" required
                           placeholder="Enter event title">
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <textarea id="description" name="description" class="form-control" rows="4"
                              placeholder="Describe your event"></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="event_date" class="form-label">
                            <i class="fas fa-calendar"></i> Event Date *
                        </label>
                        <input type="date" id="event_date" name="event_date" class="form-control" required
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="event_time" class="form-label">
                            <i class="fas fa-clock"></i> Event Time *
                        </label>
                        <input type="time" id="event_time" name="event_time" class="form-control" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="location" class="form-label">
                        <i class="fas fa-map-marker-alt"></i> Location *
                    </label>
                    <input type="text" id="location" name="location" class="form-control" required
                           placeholder="Enter event location">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="organizer" class="form-label">
                            <i class="fas fa-user"></i> Organizer *
                        </label>
                        <input type="text" id="organizer" name="organizer" class="form-control" required
                               placeholder="Enter organizer name">
                    </div>

                    <div class="form-group">
                        <label for="capacity" class="form-label">
                            <i class="fas fa-users"></i> Capacity *
                        </label>
                        <input type="number" id="capacity" name="capacity" class="form-control" required
                               min="1" placeholder="Maximum attendees">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">
                        <i class="fas fa-info-circle"></i> Event Status *
                    </label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="">Select Status</option>
                        <option value="open">Open - Accepting registrations</option>
                        <option value="closed">Closed - Registration closed</option>
                    </select>
                </div>

                <div class="actions" style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Create Event
                    </button>
                    <a href="events.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="reset" class="btn btn-warning">
                        <i class="fas fa-undo"></i> Reset Form
                    </button>
                </div>
            </form>
        </div>

        <!-- Help Section -->
        <div class="card">
            <h3 class="card-subtitle"><i class="fas fa-question-circle"></i> Help & Guidelines</h3>
            <ul style="list-style: none; padding-left: 0;">
                <li style="margin-bottom: 0.5rem;">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <strong>Title:</strong> Enter a clear, descriptive title for your event
                </li>
                <li style="margin-bottom: 0.5rem;">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <strong>Description:</strong> Provide detailed information about what the event entails
                </li>
                <li style="margin-bottom: 0.5rem;">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <strong>Date & Time:</strong> Choose appropriate date and time for your event
                </li>
                <li style="margin-bottom: 0.5rem;">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <strong>Location:</strong> Specify the exact venue where the event will take place
                </li>
                <li style="margin-bottom: 0.5rem;">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <strong>Capacity:</strong> Set a realistic number based on venue capacity
                </li>
                <li style="margin-bottom: 0.5rem;">
                    <i class="fas fa-check-circle" style="color: var(--success);"></i>
                    <strong>Status:</strong> Set to "Open" if accepting registrations, "Closed" if not
                </li>
            </ul>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Event Management CRUD System - Practice 12. Built with PHP & MySQL.</p>
        </div>
    </footer>

    <script src="js/script.js"></script>
</body>
</html>