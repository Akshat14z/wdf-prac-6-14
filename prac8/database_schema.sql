-- Event Management Portal Database Schema
-- Practice 8 - Database Dump
-- Generated: 2024

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Create Database
CREATE DATABASE IF NOT EXISTS `event_management` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `event_management`;

-- --------------------------------------------------------

-- Table structure for table `users`
DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL UNIQUE,
  `email` varchar(100) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample user data
INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `created_at`) VALUES
(1, 'admin', 'admin@eventportal.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', NOW()),
(2, 'john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', NOW()),
(3, 'jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', NOW());

-- --------------------------------------------------------

-- Table structure for table `events`
DROP TABLE IF EXISTS `events`;
CREATE TABLE `events` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(200) NOT NULL,
  `description` text,
  `event_date` date NOT NULL,
  `event_time` time NOT NULL,
  `location` varchar(200) NOT NULL,
  `capacity` int(11) DEFAULT 0,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_events_created_by` (`created_by`),
  CONSTRAINT `fk_events_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample event data
INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `location`, `capacity`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'PHP Workshop 2024', 'Learn modern PHP development techniques and best practices in this comprehensive workshop.', '2024-12-15', '10:00:00', 'Tech Conference Center, Main Hall', 50, 1, NOW(), NOW()),
(2, 'MySQL Database Optimization', 'Deep dive into MySQL performance tuning and query optimization strategies.', '2024-12-20', '14:00:00', 'Database Training Room B', 30, 1, NOW(), NOW()),
(3, 'Web Development Bootcamp', 'Intensive bootcamp covering HTML, CSS, JavaScript, PHP, and MySQL.', '2025-01-05', '09:00:00', 'Coding Academy, Room 201', 25, 2, NOW(), NOW()),
(4, 'React.js Fundamentals', 'Introduction to React.js for beginners with hands-on projects.', '2025-01-10', '15:30:00', 'Innovation Hub, Lab 3', 40, 2, NOW(), NOW()),
(5, 'API Development with PHP', 'Building RESTful APIs using PHP and modern frameworks.', '2025-01-15', '11:00:00', 'Developer Center, Workshop Room', 35, 3, NOW(), NOW()),
(6, 'Database Design Principles', 'Learn the fundamentals of database design and normalization.', '2025-01-20', '13:00:00', 'Training Center, Room A', 20, 1, NOW(), NOW()),
(7, 'JavaScript ES6+ Features', 'Explore modern JavaScript features and best practices.', '2025-01-25', '16:00:00', 'Code Academy, Main Lab', 45, 2, NOW(), NOW());

-- --------------------------------------------------------

-- Table structure for table `event_registrations` (Optional - for future enhancement)
DROP TABLE IF EXISTS `event_registrations`;
CREATE TABLE `event_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `event_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `registration_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_registration` (`event_id`, `user_id`),
  KEY `fk_registrations_event` (`event_id`),
  KEY `fk_registrations_user` (`user_id`),
  CONSTRAINT `fk_registrations_event` FOREIGN KEY (`event_id`) REFERENCES `events` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_registrations_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample registration data
INSERT INTO `event_registrations` (`id`, `event_id`, `user_id`, `registration_date`, `status`) VALUES
(1, 1, 2, NOW(), 'confirmed'),
(2, 1, 3, NOW(), 'confirmed'),
(3, 2, 2, NOW(), 'pending'),
(4, 3, 3, NOW(), 'confirmed'),
(5, 4, 1, NOW(), 'confirmed');

-- --------------------------------------------------------

-- Views for reporting
CREATE VIEW `event_summary` AS
SELECT 
    e.id,
    e.title,
    e.event_date,
    e.event_time,
    e.location,
    e.capacity,
    u.full_name as created_by_name,
    COUNT(r.id) as registration_count,
    e.created_at
FROM events e
LEFT JOIN users u ON e.created_by = u.id
LEFT JOIN event_registrations r ON e.id = r.event_id AND r.status = 'confirmed'
GROUP BY e.id, e.title, e.event_date, e.event_time, e.location, e.capacity, u.full_name, e.created_at
ORDER BY e.event_date ASC;

-- --------------------------------------------------------

-- Indexes for performance
CREATE INDEX idx_events_date ON events(event_date);
CREATE INDEX idx_events_created_by ON events(created_by);
CREATE INDEX idx_users_username ON users(username);
CREATE INDEX idx_users_email ON users(email);

-- --------------------------------------------------------

-- Sample queries demonstrating functionality

-- 1. SELECT Operation: Get latest 5 events
-- SELECT * FROM events ORDER BY created_at DESC LIMIT 5;

-- 2. INSERT Operation: Add new event
-- INSERT INTO events (title, description, event_date, event_time, location, capacity, created_by) 
-- VALUES ('New Event', 'Event description', '2025-02-01', '10:00:00', 'Venue Name', 50, 1);

-- 3. UPDATE Operation: Modify event details
-- UPDATE events SET title = 'Updated Title', description = 'Updated description' WHERE id = 1;

-- 4. DELETE Operation: Remove event
-- DELETE FROM events WHERE id = 1;

-- 5. Complex query: Events with creator names
-- SELECT e.*, u.full_name as creator_name 
-- FROM events e 
-- JOIN users u ON e.created_by = u.id 
-- ORDER BY e.event_date ASC;

-- --------------------------------------------------------

-- Database Statistics
SELECT 
    'Database Statistics' as info,
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM events) as total_events,
    (SELECT COUNT(*) FROM event_registrations) as total_registrations;

COMMIT;

-- End of dump
-- This schema demonstrates:
-- 1. Primary keys and foreign key relationships
-- 2. INSERT, SELECT, UPDATE, DELETE operations
-- 3. Data validation through constraints
-- 4. Indexing for performance
-- 5. Sample data for testing
-- 6. Views for complex reporting
-- 7. Error handling through constraints and relationships