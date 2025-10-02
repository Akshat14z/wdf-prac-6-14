<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Events - Event Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <h1><i class="fas fa-calendar-alt"></i> Event Management System</h1>
            <p>Search Events</p>
        </div>
    </header>

    <!-- Navigation -->
    <nav class="nav">
        <div class="nav-container">
            <ul>
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="events.php"><i class="fas fa-list"></i> All Events</a></li>
                <li><a href="create_event.php"><i class="fas fa-plus"></i> Create Event</a></li>
                <li><a href="search.php" class="active"><i class="fas fa-search"></i> Search Events</a></li>
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container">
        <?php
        require_once 'EventManager.php';
        
        $eventManager = new EventManager();
        $searchResults = [];
        $searchTerm = '';
        $searchPerformed = false;
        
        // Handle search
        if (isset($_GET['search']) && !empty(trim($_GET['search']))) {
            $searchTerm = trim($_GET['search']);
            $searchResults = $eventManager->searchEvents($searchTerm);
            $searchPerformed = true;
        }
        
        // Get filter data
        $allEvents = $eventManager->getAllEvents();
        $upcomingEvents = $eventManager->getUpcomingEvents();
        $openEvents = $eventManager->getEventsByStatus('open');
        $closedEvents = $eventManager->getEventsByStatus('closed');
        ?>

        <!-- Search Form -->
        <div class="search-container">
            <h2 class="card-title"><i class="fas fa-search"></i> Search Events</h2>
            <form method="GET" action="" class="search-form">
                <div class="search-input">
                    <label for="search" class="form-label">
                        <i class="fas fa-search"></i> Search Term
                    </label>
                    <input type="text" id="search" name="search" class="form-control" 
                           value="<?php echo htmlspecialchars($searchTerm); ?>"
                           placeholder="Enter title, description, location, or organizer...">
                </div>
                <div>
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Search
                    </button>
                    <?php if ($searchPerformed): ?>
                        <a href="search.php" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Clear
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Quick Filters -->
        <div class="card">
            <h3 class="card-subtitle"><i class="fas fa-filter"></i> Quick Filters</h3>
            <div class="actions">
                <a href="search.php" class="btn <?php echo !$searchPerformed ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class="fas fa-list"></i> All Events (<?php echo count($allEvents); ?>)
                </a>
                <a href="?filter=upcoming" class="btn <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'upcoming') ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class="fas fa-clock"></i> Upcoming (<?php echo count($upcomingEvents); ?>)
                </a>
                <a href="?filter=open" class="btn <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'open') ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class="fas fa-door-open"></i> Open (<?php echo count($openEvents); ?>)
                </a>
                <a href="?filter=closed" class="btn <?php echo (isset($_GET['filter']) && $_GET['filter'] == 'closed') ? 'btn-primary' : 'btn-secondary'; ?>">
                    <i class="fas fa-door-closed"></i> Closed (<?php echo count($closedEvents); ?>)
                </a>
            </div>
        </div>

        <!-- Handle Filters -->
        <?php
        if (isset($_GET['filter'])) {
            switch ($_GET['filter']) {
                case 'upcoming':
                    $searchResults = $upcomingEvents;
                    $searchPerformed = true;
                    $filterTitle = 'Upcoming Events';
                    $filterIcon = 'fa-clock';
                    break;
                case 'open':
                    $searchResults = $openEvents;
                    $searchPerformed = true;
                    $filterTitle = 'Open Events';
                    $filterIcon = 'fa-door-open';
                    break;
                case 'closed':
                    $searchResults = $closedEvents;
                    $searchPerformed = true;
                    $filterTitle = 'Closed Events';
                    $filterIcon = 'fa-door-closed';
                    break;
            }
        }
        
        // If no search or filter, show all events
        if (!$searchPerformed && !isset($_GET['filter'])) {
            $searchResults = $allEvents;
            $searchPerformed = true;
            $filterTitle = 'All Events';
            $filterIcon = 'fa-list';
        }
        ?>

        <!-- Search Results -->
        <div class="card">
            <h2 class="card-title">
                <i class="fas <?php echo isset($filterIcon) ? $filterIcon : 'fa-search'; ?>"></i> 
                <?php 
                if ($searchTerm) {
                    echo 'Search Results for "' . htmlspecialchars($searchTerm) . '"';
                } elseif (isset($filterTitle)) {
                    echo $filterTitle;
                } else {
                    echo 'Search Results';
                }
                ?>
                (<?php echo count($searchResults); ?> found)
            </h2>

            <?php if ($searchTerm && empty($searchResults)): ?>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i> 
                    No events found matching "<strong><?php echo htmlspecialchars($searchTerm); ?></strong>".
                    <br><br>
                    <strong>Try:</strong>
                    <ul style="margin: 1rem 0 0 2rem;">
                        <li>Using different keywords</li>
                        <li>Checking your spelling</li>
                        <li>Using broader search terms</li>
                        <li>Searching by organizer name or location</li>
                    </ul>
                </div>
            <?php elseif (empty($searchResults)): ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No events available.
                    <br><br>
                    <a href="create_event.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Create First Event
                    </a>
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
                            <?php foreach ($searchResults as $event): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($event['id']); ?></td>
                                    <td>
                                        <strong>
                                            <?php 
                                            $title = htmlspecialchars($event['title']);
                                            if ($searchTerm) {
                                                $title = preg_replace('/(' . preg_quote($searchTerm, '/') . ')/i', '<mark>$1</mark>', $title);
                                            }
                                            echo $title;
                                            ?>
                                        </strong>
                                    </td>
                                    <td>
                                        <?php 
                                        $description = htmlspecialchars($event['description']);
                                        if ($searchTerm) {
                                            $description = preg_replace('/(' . preg_quote($searchTerm, '/') . ')/i', '<mark>$1</mark>', $description);
                                        }
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
                                        <?php 
                                        $location = htmlspecialchars($event['location']);
                                        if ($searchTerm) {
                                            $location = preg_replace('/(' . preg_quote($searchTerm, '/') . ')/i', '<mark>$1</mark>', $location);
                                        }
                                        echo '<i class="fas fa-map-marker-alt"></i> ' . $location;
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        $organizer = htmlspecialchars($event['organizer']);
                                        if ($searchTerm) {
                                            $organizer = preg_replace('/(' . preg_quote($searchTerm, '/') . ')/i', '<mark>$1</mark>', $organizer);
                                        }
                                        echo '<i class="fas fa-user"></i> ' . $organizer;
                                        ?>
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

        <!-- Search Tips -->
        <div class="card">
            <h3 class="card-subtitle"><i class="fas fa-lightbulb"></i> Search Tips</h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <div>
                    <h4><i class="fas fa-tag"></i> Event Title</h4>
                    <p style="color: var(--text-light);">Search by event name or title keywords</p>
                </div>
                <div>
                    <h4><i class="fas fa-align-left"></i> Description</h4>
                    <p style="color: var(--text-light);">Find events by description content</p>
                </div>
                <div>
                    <h4><i class="fas fa-map-marker-alt"></i> Location</h4>
                    <p style="color: var(--text-light);">Search by venue or location name</p>
                </div>
                <div>
                    <h4><i class="fas fa-user"></i> Organizer</h4>
                    <p style="color: var(--text-light);">Find events by organizer name</p>
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
    
    <style>
        mark {
            background-color: var(--warning);
            color: var(--text-dark);
            padding: 2px 4px;
            border-radius: 3px;
            font-weight: bold;
        }
    </style>
</body>
</html>