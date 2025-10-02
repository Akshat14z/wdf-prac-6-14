<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management CRUD System - Practice 12</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-calendar-alt"></i> Event Management System</h1>
            <p>Complete CRUD Operations for Event Management</p>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-container">
            <ul>
                <li><a href="index.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
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
        
        $eventManager = new EventManager();
        $allEvents = $eventManager->getAllEvents();
        $upcomingEvents = $eventManager->getUpcomingEvents();
        $openEvents = $eventManager->getEventsByStatus('open');
        $closedEvents = $eventManager->getEventsByStatus('closed');
        
        // Calculate stats
        $totalEvents = count($allEvents);
        $upcomingCount = count($upcomingEvents);
        $openCount = count($openEvents);
        $closedCount = count($closedEvents);
        ?>

        <!-- Statistics Dashboard -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3><?php echo $totalEvents; ?></h3>
                <p><i class="fas fa-calendar-alt"></i> Total Events</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $upcomingCount; ?></h3>
                <p><i class="fas fa-clock"></i> Upcoming Events</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $openCount; ?></h3>
                <p><i class="fas fa-door-open"></i> Open Events</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $closedCount; ?></h3>
                <p><i class="fas fa-door-closed"></i> Closed Events</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="card">
            <h2 class="card-title"><i class="fas fa-bolt"></i> Quick Actions</h2>
            <div class="actions">
                <a href="create_event.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Event
                </a>
                <a href="events.php" class="btn btn-secondary">
                    <i class="fas fa-list"></i> View All Events
                </a>
                <a href="search.php" class="btn btn-warning">
                    <i class="fas fa-search"></i> Search Events
                </a>
            </div>
        </div>

        <!-- Recent Events -->
        <div class="card">
            <h2 class="card-title"><i class="fas fa-history"></i> Recent Events</h2>
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
                                <th><i class="fas fa-calendar"></i> Date & Time</th>
                                <th><i class="fas fa-map-marker-alt"></i> Location</th>
                                <th><i class="fas fa-user"></i> Organizer</th>
                                <th><i class="fas fa-info-circle"></i> Status</th>
                                <th><i class="fas fa-users"></i> Capacity</th>
                                <th><i class="fas fa-cogs"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Show only latest 5 events on dashboard
                            $recentEvents = array_slice($allEvents, 0, 5);
                            foreach ($recentEvents as $event): 
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['id']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($event['event_date'])); ?><br>
                                        <i class="fas fa-clock"></i> <?php echo date('h:i A', strtotime($event['event_time'])); ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-user"></i> <?php echo htmlspecialchars($event['organizer']); ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo $event['status']; ?>">
                                            <?php echo ucfirst($event['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="fas fa-users"></i> <?php echo htmlspecialchars($event['capacity']); ?>
                                    </td>
                                    <td>
                                        <div class="actions">
                                            <a href="view_event.php?id=<?php echo $event['id']; ?>" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-warning btn-sm">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="delete_event.php?id=<?php echo $event['id']; ?>" class="btn btn-danger btn-sm" 
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
                
                <?php if (count($allEvents) > 5): ?>
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="events.php" class="btn btn-primary">
                            <i class="fas fa-list"></i> View All <?php echo $totalEvents; ?> Events
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>

        <!-- Upcoming Events Preview -->
        <?php if (!empty($upcomingEvents)): ?>
        <div class="card">
            <h2 class="card-title"><i class="fas fa-calendar-check"></i> Upcoming Events</h2>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th><i class="fas fa-tag"></i> Event</th>
                            <th><i class="fas fa-calendar"></i> Date</th>
                            <th><i class="fas fa-clock"></i> Time</th>
                            <th><i class="fas fa-map-marker-alt"></i> Location</th>
                            <th><i class="fas fa-info-circle"></i> Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        // Show only next 3 upcoming events
                        $nextEvents = array_slice($upcomingEvents, 0, 3);
                        foreach ($nextEvents as $event): 
                        ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
                                <td><?php echo date('h:i A', strtotime($event['event_time'])); ?></td>
                                <td><?php echo htmlspecialchars($event['location']); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $event['status']; ?>">
                                        <?php echo ucfirst($event['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
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