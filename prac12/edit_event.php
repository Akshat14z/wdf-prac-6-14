<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event - Event Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-calendar-alt"></i> Event Management System</h1>
            <p>Edit Event</p>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-container">
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="events.php"><i class="fas fa-list"></i> All Events</a></li>
                <li><a href="create_event.php"><i class="fas fa-plus"></i> Create Event</a></li>
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
        $event = null;

        // Get event ID from URL
        $eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($eventId <= 0) {
            header('Location: events.php');
            exit();
        }

        $eventManager = new EventManager();

        // Handle form submission
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
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
            
            $result = $eventManager->updateEvent($eventId, $eventData);
            
            if ($result['success']) {
                $message = $result['message'];
                $messageType = 'success';
            } else {
                $message = $result['message'];
                $messageType = 'error';
            }
        }

        // Get event data
        $event = $eventManager->getEventById($eventId);

        if (!$event) {
            ?>
            <div class="card">
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> Event not found!
                    <br><br>
                    <a href="events.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Events
                    </a>
                </div>
            </div>
            <?php
            include 'footer.php';
            exit();
        }
        ?>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                <h2 class="card-title"><i class="fas fa-edit"></i> Edit Event</h2>
                <a href="events.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Events
                </a>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                    <?php if ($messageType == 'success'): ?>
                        <br><br>
                        <a href="view_event.php?id=<?php echo $eventId; ?>" class="btn btn-secondary">
                            <i class="fas fa-eye"></i> View Event
                        </a>
                        <a href="events.php" class="btn btn-primary">
                            <i class="fas fa-list"></i> All Events
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
                           value="<?php echo htmlspecialchars($event['title']); ?>"
                           placeholder="Enter event title">
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">
                        <i class="fas fa-align-left"></i> Description
                    </label>
                    <textarea id="description" name="description" class="form-control" rows="4"
                              placeholder="Describe your event"><?php echo htmlspecialchars($event['description']); ?></textarea>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="event_date" class="form-label">
                            <i class="fas fa-calendar"></i> Event Date *
                        </label>
                        <input type="date" id="event_date" name="event_date" class="form-control" required
                               value="<?php echo $event['event_date']; ?>">
                    </div>

                    <div class="form-group">
                        <label for="event_time" class="form-label">
                            <i class="fas fa-clock"></i> Event Time *
                        </label>
                        <input type="time" id="event_time" name="event_time" class="form-control" required
                               value="<?php echo $event['event_time']; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="location" class="form-label">
                        <i class="fas fa-map-marker-alt"></i> Location *
                    </label>
                    <input type="text" id="location" name="location" class="form-control" required
                           value="<?php echo htmlspecialchars($event['location']); ?>"
                           placeholder="Enter event location">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                    <div class="form-group">
                        <label for="organizer" class="form-label">
                            <i class="fas fa-user"></i> Organizer *
                        </label>
                        <input type="text" id="organizer" name="organizer" class="form-control" required
                               value="<?php echo htmlspecialchars($event['organizer']); ?>"
                               placeholder="Enter organizer name">
                    </div>

                    <div class="form-group">
                        <label for="capacity" class="form-label">
                            <i class="fas fa-users"></i> Capacity *
                        </label>
                        <input type="number" id="capacity" name="capacity" class="form-control" required
                               value="<?php echo $event['capacity']; ?>"
                               min="1" placeholder="Maximum attendees">
                    </div>
                </div>

                <div class="form-group">
                    <label for="status" class="form-label">
                        <i class="fas fa-info-circle"></i> Event Status *
                    </label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="">Select Status</option>
                        <option value="open" <?php echo ($event['status'] == 'open') ? 'selected' : ''; ?>>
                            Open - Accepting registrations
                        </option>
                        <option value="closed" <?php echo ($event['status'] == 'closed') ? 'selected' : ''; ?>>
                            Closed - Registration closed
                        </option>
                    </select>
                </div>

                <div class="actions" style="margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Event
                    </button>
                    <a href="view_event.php?id=<?php echo $eventId; ?>" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> View Event
                    </a>
                    <a href="events.php" class="btn btn-warning">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                    <button type="reset" class="btn btn-danger">
                        <i class="fas fa-undo"></i> Reset Changes
                    </button>
                </div>
            </form>
        </div>

        <!-- Event Info -->
        <div class="card">
            <h3 class="card-subtitle"><i class="fas fa-info-circle"></i> Event Information</h3>
            <div class="event-meta">
                <div class="meta-item">
                    <i class="fas fa-hashtag"></i>
                    <strong>ID:</strong> <?php echo $event['id']; ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-plus"></i>
                    <strong>Created:</strong> <?php echo date('M d, Y h:i A', strtotime($event['created_at'])); ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-clock"></i>
                    <strong>Updated:</strong> <?php echo date('M d, Y h:i A', strtotime($event['updated_at'])); ?>
                </div>
            </div>
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