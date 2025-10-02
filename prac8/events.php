<?php
session_start();
require_once 'config.php';
require_once 'EventManager.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

$eventManager = new EventManager($db);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 8;

// Handle event deletion
if (isset($_GET['delete']) && isset($_GET['id'])) {
    $event_id = (int)$_GET['id'];
    if ($eventManager->deleteEvent($event_id)) {
        $success_message = "Event deleted successfully!";
    } else {
        $error_message = "Failed to delete event.";
    }
}

// Get events for current page
$events = $eventManager->getAllEvents($page, $limit);
$totalEvents = $eventManager->getTotalEventCount();
$totalPages = ceil($totalEvents / $limit);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management Portal - All Events</title>
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
            max-width: 1200px;
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

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
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

        .events-container {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .events-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .events-count {
            color: #666;
            font-size: 1.1rem;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
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

        .btn-danger {
            background: #dc3545;
            color: white;
            font-size: 0.9rem;
            padding: 8px 16px;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .btn-edit {
            background: #28a745;
            color: white;
            font-size: 0.9rem;
            padding: 8px 16px;
            margin-right: 0.5rem;
        }

        .btn-edit:hover {
            background: #218838;
        }

        .events-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .event-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #667eea;
            transition: transform 0.3s;
        }

        .event-card:hover {
            transform: translateY(-5px);
        }

        .event-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .event-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .event-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.5rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #666;
        }

        .event-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .event-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .event-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
        }

        .event-organizer {
            font-weight: 600;
            color: #667eea;
        }

        .event-capacity {
            background: #e7f3ff;
            color: #0066cc;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .event-actions {
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-top: 2rem;
        }

        .pagination a {
            padding: 8px 16px;
            background: white;
            color: #667eea;
            text-decoration: none;
            border-radius: 8px;
            border: 2px solid #667eea;
            transition: all 0.3s;
        }

        .pagination a:hover,
        .pagination .current {
            background: #667eea;
            color: white;
        }

        .pagination .current {
            font-weight: 600;
        }

        .no-events {
            text-align: center;
            padding: 3rem;
            color: #666;
        }

        .no-events i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #ccc;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .events-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .events-grid {
                grid-template-columns: 1fr;
            }

            .event-meta {
                grid-template-columns: 1fr;
            }

            .event-footer {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }

            .event-actions {
                justify-content: center;
                width: 100%;
            }

            .pagination {
                flex-wrap: wrap;
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
            <h1>All Events</h1>
            <p>Complete event management with CRUD operations</p>
        </div>

        <!-- Alerts -->
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Events Container -->
        <div class="events-container">
            <div class="events-header">
                <div class="events-count">
                    Showing <?php echo count($events); ?> of <?php echo $totalEvents; ?> events
                </div>
                <a href="create_event.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Create New Event
                </a>
            </div>

            <?php if ($events && count($events) > 0): ?>
                <div class="events-grid">
                    <?php foreach ($events as $event): ?>
                        <div class="event-card">
                            <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                            
                            <div class="event-status status-<?php echo $event['status']; ?>">
                                <?php echo ucfirst($event['status']); ?>
                            </div>
                            
                            <div class="event-meta">
                                <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                                <span><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                <span><i class="fas fa-user"></i> <?php echo htmlspecialchars($event['creator_name'] ?: 'Unknown'); ?></span>
                            </div>
                            
                            <div class="event-description">
                                <?php echo htmlspecialchars(substr($event['description'], 0, 120)); ?>
                                <?php if (strlen($event['description']) > 120) echo '...'; ?>
                            </div>
                            
                            <div class="event-footer">
                                <div class="event-organizer">
                                    <?php echo htmlspecialchars($event['organizer']); ?>
                                </div>
                                <div class="event-capacity">
                                    <?php echo $event['registered_count']; ?>/<?php echo $event['capacity']; ?> registered
                                </div>
                            </div>
                            
                            <div class="event-actions">
                                <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="btn btn-edit">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="events.php?delete=1&id=<?php echo $event['id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('Are you sure you want to delete this event?')">
                                    <i class="fas fa-trash"></i> Delete
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="events.php?page=<?php echo $page - 1; ?>">
                                <i class="fas fa-chevron-left"></i> Previous
                            </a>
                        <?php endif; ?>

                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <a href="events.php?page=<?php echo $i; ?>" 
                               class="<?php echo $i == $page ? 'current' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="events.php?page=<?php echo $page + 1; ?>">
                                Next <i class="fas fa-chevron-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="no-events">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Events Found</h3>
                    <p>There are currently no events in the system. Create your first event to get started!</p>
                    <a href="create_event.php" class="btn btn-primary" style="margin-top: 1rem;">
                        <i class="fas fa-plus"></i> Create New Event
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 300);
            });
        }, 5000);
    </script>
</body>
</html>