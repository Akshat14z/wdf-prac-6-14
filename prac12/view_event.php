<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Event - Event Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-calendar-alt"></i> Event Management System</h1>
            <p>Event Details</p>
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

        // Get event ID from URL
        $eventId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

        if ($eventId <= 0) {
            header('Location: events.php');
            exit();
        }

        $eventManager = new EventManager();
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

        <!-- Event Details -->
        <div class="event-detail">
            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h1 style="color: var(--primary-color); margin-bottom: 0.5rem;">
                        <i class="fas fa-calendar-check"></i> <?php echo htmlspecialchars($event['title']); ?>
                    </h1>
                    <div style="display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                        <span class="status-badge status-<?php echo $event['status']; ?>">
                            <i class="fas fa-<?php echo $event['status'] == 'open' ? 'door-open' : 'door-closed'; ?>"></i>
                            <?php echo ucfirst($event['status']); ?>
                        </span>
                        <span style="color: var(--text-light);">
                            <i class="fas fa-hashtag"></i> ID: <?php echo $event['id']; ?>
                        </span>
                    </div>
                </div>
                <div class="actions">
                    <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit Event
                    </a>
                    <a href="delete_event.php?id=<?php echo $event['id']; ?>" class="btn btn-danger" 
                       onclick="return confirm('Are you sure you want to delete this event?')">
                        <i class="fas fa-trash"></i> Delete Event
                    </a>
                    <a href="events.php" class="btn btn-secondary">
                        <i class="fas fa-list"></i> All Events
                    </a>
                </div>
            </div>

            <!-- Event Meta Information -->
            <div class="event-meta">
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <strong>Date:</strong> <?php echo date('l, F d, Y', strtotime($event['event_date'])); ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-clock"></i>
                    <strong>Time:</strong> <?php echo date('h:i A', strtotime($event['event_time'])); ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <strong>Organizer:</strong> <?php echo htmlspecialchars($event['organizer']); ?>
                </div>
                <div class="meta-item">
                    <i class="fas fa-users"></i>
                    <strong>Capacity:</strong> <?php echo number_format($event['capacity']); ?> people
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-plus"></i>
                    <strong>Created:</strong> <?php echo date('M d, Y h:i A', strtotime($event['created_at'])); ?>
                </div>
            </div>

            <!-- Event Description -->
            <?php if (!empty($event['description'])): ?>
                <div style="margin-top: 2rem;">
                    <h3 style="color: var(--accent-color); margin-bottom: 1rem;">
                        <i class="fas fa-info-circle"></i> Description
                    </h3>
                    <div style="background: var(--secondary-color); padding: 1.5rem; border-radius: 10px; border-left: 5px solid var(--primary-color);">
                        <p style="line-height: 1.8; margin: 0; white-space: pre-wrap;"><?php echo htmlspecialchars($event['description']); ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div style="margin-top: 2rem;">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No description provided for this event.
                    </div>
                </div>
            <?php endif; ?>

            <!-- Event Status Information -->
            <div style="margin-top: 2rem;">
                <h3 style="color: var(--accent-color); margin-bottom: 1rem;">
                    <i class="fas fa-info-circle"></i> Event Status
                </h3>
                <div style="background: var(--secondary-color); padding: 1.5rem; border-radius: 10px;">
                    <?php if ($event['status'] == 'open'): ?>
                        <div style="display: flex; align-items: center; gap: 1rem; color: var(--success);">
                            <i class="fas fa-check-circle" style="font-size: 1.5rem;"></i>
                            <div>
                                <strong>Registration Open</strong>
                                <p style="margin: 0.5rem 0 0 0; color: var(--text-light);">
                                    This event is currently accepting registrations. Attendees can sign up until the event reaches full capacity or registration is closed.
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="display: flex; align-items: center; gap: 1rem; color: var(--danger);">
                            <i class="fas fa-times-circle" style="font-size: 1.5rem;"></i>
                            <div>
                                <strong>Registration Closed</strong>
                                <p style="margin: 0.5rem 0 0 0; color: var(--text-light);">
                                    This event is no longer accepting new registrations. The registration period has ended or the event has reached full capacity.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Quick Actions -->
            <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--tertiary-color);">
                <h3 style="color: var(--accent-color); margin-bottom: 1rem;">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h3>
                <div class="actions">
                    <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit This Event
                    </a>
                    <?php if ($event['status'] == 'open'): ?>
                        <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-danger">
                            <i class="fas fa-door-closed"></i> Close Registration
                        </a>
                    <?php else: ?>
                        <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-success">
                            <i class="fas fa-door-open"></i> Open Registration
                        </a>
                    <?php endif; ?>
                    <a href="create_event.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create Similar Event
                    </a>
                </div>
            </div>
        </div>

        <!-- Related Actions -->
        <div class="card">
            <h3 class="card-subtitle"><i class="fas fa-cogs"></i> Management Options</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div style="text-align: center; padding: 1rem;">
                    <i class="fas fa-edit" style="font-size: 2rem; color: var(--warning); margin-bottom: 1rem;"></i>
                    <h4>Edit Event</h4>
                    <p style="color: var(--text-light); margin: 0.5rem 0;">
                        Modify event details, date, time, location, and other information.
                    </p>
                    <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-warning">
                        <i class="fas fa-edit"></i> Edit
                    </a>
                </div>
                <div style="text-align: center; padding: 1rem;">
                    <i class="fas fa-copy" style="font-size: 2rem; color: var(--accent-color); margin-bottom: 1rem;"></i>
                    <h4>Duplicate Event</h4>
                    <p style="color: var(--text-light); margin: 0.5rem 0;">
                        Create a new event based on this event's details.
                    </p>
                    <a href="create_event.php" class="btn btn-secondary">
                        <i class="fas fa-copy"></i> Duplicate
                    </a>
                </div>
                <div style="text-align: center; padding: 1rem;">
                    <i class="fas fa-trash" style="font-size: 2rem; color: var(--danger); margin-bottom: 1rem;"></i>
                    <h4>Delete Event</h4>
                    <p style="color: var(--text-light); margin: 0.5rem 0;">
                        Permanently remove this event from the system.
                    </p>
                    <a href="delete_event.php?id=<?php echo $event['id']; ?>" class="btn btn-danger"
                       onclick="return confirm('Are you sure you want to delete this event? This action cannot be undone.')">
                        <i class="fas fa-trash"></i> Delete
                    </a>
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