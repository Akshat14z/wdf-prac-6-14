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
    header('Location: events.php?error=Event not found');
    exit();
}

// Delete the event
$result = $eventManager->deleteEvent($eventId);

if ($result['success']) {
    header('Location: events.php?success=' . urlencode($result['message']));
} else {
    header('Location: events.php?error=' . urlencode($result['message']));
}
exit();
?>