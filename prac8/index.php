<?php
session_start();
require_once 'config.php';
require_once 'EventManager.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

$eventManager = new EventManager($db);

// Get latest 5 events for dashboard
$latestEvents = $eventManager->getLatestEvents(5);
$stats = $eventManager->getEventStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Management Portal - Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #E9E3DF 0%, #465C88 100%);
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
            color: #FF7A30;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            list-style: none;
        }

        .nav-links a {
            text-decoration: none;
            color: #000000;
            font-weight: 500;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .nav-links a:hover {
            color: #FF7A30;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .dashboard-header {
            text-align: center;
            color: white;
            margin-bottom: 3rem;
        }

        .dashboard-header h1 {
            font-size: 3rem;
            margin-bottom: 0.5rem;
        }

        .dashboard-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #FF7A30 0%, #465C88 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .stat-icon i {
            font-size: 2rem;
            color: white;
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #465C88;
            font-weight: 500;
        }

        .events-section {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 2rem;
            color: #000000;
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
            background: linear-gradient(135deg, #FF7A30 0%, #465C88 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(255, 122, 48, 0.3);
        }

        .event-grid {
            display: grid;
            gap: 1.5rem;
        }

        .event-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #FF7A30;
            transition: transform 0.3s;
        }

        .event-card:hover {
            transform: translateX(5px);
        }

        .event-header {
            display: flex;
            justify-content: between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .event-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: #000000;
            margin-bottom: 0.5rem;
        }

        .event-meta {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            flex-wrap: wrap;
        }

        .event-meta span {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #465C88;
            font-size: 0.9rem;
        }

        .event-description {
            color: #465C88;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .event-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .event-organizer {
            font-weight: 600;
            color: #FF7A30;
        }

        .event-capacity {
            background: #E9E3DF;
            color: #465C88;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .no-events {
            text-align: center;
            padding: 3rem;
            color: #465C88;
        }

        .no-events i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #E9E3DF;
        }

        .action-buttons {
            margin-top: 2rem;
            text-align: center;
        }

        .btn-secondary {
            background: #E9E3DF;
            color: #000000;
            border: 2px solid #FF7A30;
            margin: 0 0.5rem;
        }

        .btn-secondary:hover {
            background: #FF7A30;
            color: white;
        }

        @media (max-width: 768px) {
            .container {
                padding: 1rem;
            }

            .dashboard-header h1 {
                font-size: 2rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .section-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .event-meta {
                flex-direction: column;
                gap: 0.5rem;
            }

            .event-footer {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <i class="fas fa-calendar-alt"></i>
                Event Portal
            </div>
            <ul class="nav-links">
                <li><a href="index.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li><a href="events.php"><i class="fas fa-list"></i> All Events</a></li>
                <li><a href="create_event.php"><i class="fas fa-plus"></i> Create Event</a></li>
                <li><a href="login.php"><i class="fas fa-user"></i> Login</a></li>
            </ul>
        </div>
    </nav>

    <div class="container">
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <h1 style=" color: black">Event Management Portal</h1>
            <p>Connect with MySQL to store and retrieve user/event data</p>
        </div>

        <!-- Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-number"><?php echo $stats['total_events']; ?></div>
                <div class="stat-label">Total Events</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-play-circle"></i>
                </div>
                <div class="stat-number"><?php echo $stats['active_events']; ?></div>
                <div class="stat-label">Active Events</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $stats['total_registrations']; ?></div>
                <div class="stat-label">Total Registrations</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-calendar-week"></i>
                </div>
                <div class="stat-number"><?php echo $stats['events_this_month']; ?></div>
                <div class="stat-label">Events This Month</div>
            </div>
        </div>

        <!-- Latest Events Section -->
        <div class="events-section">
            <div class="section-header">
                <h2 class="section-title">Latest 5 Events</h2>
                <a href="events.php" class="btn btn-primary">
                    <i class="fas fa-arrow-right"></i> View All Events
                </a>
            </div>

            <?php if ($latestEvents && count($latestEvents) > 0): ?>
                <div class="event-grid">
                    <?php foreach ($latestEvents as $event): ?>
                        <div class="event-card">
                            <div class="event-header">
                                <div>
                                    <div class="event-title"><?php echo htmlspecialchars($event['title']); ?></div>
                                    <div class="event-meta">
                                        <span><i class="fas fa-calendar"></i> <?php echo date('M j, Y', strtotime($event['event_date'])); ?></span>
                                        <span><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($event['event_time'])); ?></span>
                                        <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="event-description">
                                <?php echo htmlspecialchars(substr($event['description'], 0, 150)); ?>
                                <?php if (strlen($event['description']) > 150) echo '...'; ?>
                            </div>
                            
                            <div class="event-footer">
                                <div class="event-organizer">
                                    <i class="fas fa-user"></i> <?php echo htmlspecialchars($event['organizer']); ?>
                                </div>
                                <div class="event-capacity">
                                    <?php echo $event['registered_count']; ?>/<?php echo $event['capacity']; ?> registered
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-events">
                    <i class="fas fa-calendar-times"></i>
                    <h3>No Events Available</h3>
                    <p>There are currently no events in the system. Create your first event to get started!</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="create_event.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Event
            </a>
            <a href="events.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Manage All Events
            </a>
            <a href="database_test.php" class="btn btn-secondary">
                <i class="fas fa-database"></i> Test Database Connection
            </a>
        </div>
    </div>
</body>
</html>