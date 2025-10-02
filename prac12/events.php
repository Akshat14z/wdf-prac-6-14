<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Events - Event Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-calendar-alt"></i> Event Management System</h1>
            <p>All Events - Complete List</p>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-container">
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="events.php" class="active"><i class="fas fa-list"></i> All Events</a></li>
                <li><a href="create_event.php"><i class="fas fa-plus"></i> Create Event</a></li>
                <li><a href="search.php"><i class="fas fa-search"></i> Search Events</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <?php
        require_once 'EventManager.php';
        
        $eventManager = new EventManager();
        $allEvents = $eventManager->getAllEvents();
        
        // Handle success/error messages
        $message = '';
        $messageType = '';
        
        if (isset($_GET['success'])) {
            $message = $_GET['success'];
            $messageType = 'success';
        } elseif (isset($_GET['error'])) {
            $message = $_GET['error'];
            $messageType = 'error';
        }
        ?>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType == 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                <h2 class="card-title"><i class="fas fa-list"></i> All Events (<?php echo count($allEvents); ?>)</h2>
                <a href="create_event.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Event
                </a>
            </div>

            <?php if (empty($allEvents)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No events found. <a href="create_event.php">Create your first event</a>!
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-hashtag"></i> ID</th>
                                <th><i class="fas fa-tag"></i> Title</th>
                                <th><i class="fas fa-align-left"></i> Description</th>
                                <th><i class="fas fa-calendar"></i> Date & Time</th>
                                <th><i class="fas fa-map-marker-alt"></i> Location</th>
                                <th><i class="fas fa-user"></i> Organizer</th>
                                <th><i class="fas fa-users"></i> Capacity</th>
                                <th><i class="fas fa-info-circle"></i> Status</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($allEvents as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                    </td>
                                    <td>
                                        <?php 
                                        $description = htmlspecialchars($event['description']);
                                        echo strlen($description) > 50 ? substr($description, 0, 50) . '...' : $description;
                                        ?>
                                    </td>
                                    <td>
                                        <div>
                                            <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($event['event_date'])); ?>
                                        </div>
                                        <div>
                                            <i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($event['event_time'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($event['organizer']); ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-users"></i> <?php echo htmlspecialchars($event['capacity']); ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $event['status']; ?>">
                                            <i class="fas fa-<?php echo $event['status'] == 'open' ? 'door-open' : 'door-closed'; ?>"></i>
                                            <?php echo ucfirst($event['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="view_event.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary btn-sm" title="View Event">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-warning btn-sm" title="Edit Event">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="delete_event.php?id=<?php echo $event['id']; ?>" class="btn btn-danger btn-sm" 
                                               title="Delete Event"
                                               onclick="return confirm('Are you sure you want to delete this event?')">
                                                <i class="fas fa-trash"></i> Delete
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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