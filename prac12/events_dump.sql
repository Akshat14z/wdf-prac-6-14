-- Event Management CRUD System - Practice 12
-- Database Schema and Sample Data
-- Generated: September 27, 2025

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

-- Create Database
CREATE DATABASE IF NOT EXISTS `event_management_prac12` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `event_management_prac12`;

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
  `status` enum('open','closed') DEFAULT 'open',
  `organizer` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_event_date` (`event_date`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample event data
INSERT INTO `events` (`id`, `title`, `description`, `event_date`, `event_time`, `location`, `capacity`, `status`, `organizer`, `created_at`, `updated_at`) VALUES
(1, 'Tech Conference 2025', 'Annual technology conference featuring the latest innovations in AI, Machine Learning, Web Development, and Mobile Technologies. Join industry experts, developers, and tech enthusiasts for a day of learning, networking, and exploring cutting-edge technologies that are shaping the future.', '2025-10-15', '09:00:00', 'Convention Center Hall A', 500, 'open', 'Tech Society', NOW(), NOW()),
(2, 'Web Development Workshop', 'Comprehensive hands-on workshop covering modern web development techniques including React, Node.js, MongoDB, and deployment strategies. Perfect for beginners and intermediate developers looking to enhance their skills with practical projects and real-world examples.', '2025-10-22', '14:00:00', 'Computer Lab 101', 50, 'open', 'Dev Community', NOW(), NOW()),
(3, 'Database Design Seminar', 'In-depth seminar on advanced database design principles, normalization techniques, performance optimization, and best practices. Learn about relational database design, NoSQL alternatives, indexing strategies, and query optimization from industry experts.', '2025-11-05', '10:30:00', 'Lecture Hall B', 100, 'closed', 'Database Guild', NOW(), NOW()),
(4, 'Mobile App Development Bootcamp', 'Intensive 3-day bootcamp covering mobile app development for both iOS and Android platforms. Learn React Native, Flutter, native development approaches, app store deployment, and monetization strategies. Includes hands-on projects and portfolio development.', '2025-11-20', '09:00:00', 'Training Center', 30, 'open', 'Mobile Dev Academy', NOW(), NOW()),
(5, 'UI/UX Design Masterclass', 'Comprehensive masterclass on user interface and user experience design principles. Cover design thinking, wireframing, prototyping, user research, accessibility, and modern design tools like Figma, Adobe XD, and Sketch. Perfect for designers and developers.', '2025-12-01', '13:00:00', 'Design Studio A', 75, 'open', 'Design Collective', NOW(), NOW()),
(6, 'Cybersecurity Awareness Workshop', 'Essential cybersecurity workshop covering threat detection, prevention strategies, secure coding practices, network security, and incident response. Learn about common vulnerabilities, penetration testing basics, and how to build secure applications.', '2025-12-10', '10:00:00', 'Security Lab', 40, 'open', 'CyberSec Pro', NOW(), NOW()),
(7, 'Data Science & Analytics Summit', 'Full-day summit on data science, machine learning, and business analytics. Explore data visualization, statistical analysis, predictive modeling, big data technologies, and practical applications across various industries. Network with data professionals.', '2025-12-18', '08:30:00', 'Analytics Center', 200, 'open', 'Data Scientists United', NOW(), NOW()),
(8, 'Cloud Computing Conference', 'Comprehensive conference on cloud technologies including AWS, Azure, Google Cloud, serverless architectures, containerization with Docker and Kubernetes, and cloud migration strategies. Perfect for developers and system administrators.', '2026-01-15', '09:30:00', 'Cloud Campus', 300, 'open', 'Cloud Engineers Society', NOW(), NOW()),
(9, 'DevOps and Automation Seminar', 'Learn about DevOps practices, CI/CD pipelines, infrastructure as code, monitoring and logging, automated testing, and deployment strategies. Hands-on sessions with popular tools like Jenkins, GitLab, Terraform, and monitoring solutions.', '2026-01-25', '11:00:00', 'DevOps Hub', 60, 'open', 'Automation Experts', NOW(), NOW()),
(10, 'Blockchain and Cryptocurrency Workshop', 'Explore blockchain technology, smart contracts, DeFi applications, NFTs, and cryptocurrency development. Learn about different blockchain platforms, development tools, and real-world applications beyond digital currencies.', '2026-02-08', '14:30:00', 'Blockchain Lab', 45, 'open', 'Crypto Developers', NOW(), NOW()),
(11, 'Gaming Development Bootcamp', 'Intensive bootcamp on game development using Unity, Unreal Engine, and web-based game technologies. Cover game design principles, 2D/3D graphics, physics, animation, sound integration, and publishing strategies for various platforms.', '2025-09-30', '10:00:00', 'Game Dev Studio', 25, 'closed', 'Game Makers Guild', NOW(), NOW()),
(12, 'Digital Marketing & SEO Workshop', 'Comprehensive workshop on digital marketing strategies, search engine optimization, social media marketing, content marketing, email campaigns, and analytics. Learn tools and techniques to grow online presence and engagement.', '2025-10-05', '15:00:00', 'Marketing Center', 80, 'closed', 'Digital Marketers', NOW(), NOW());

-- Create indexes for better performance
CREATE INDEX idx_title_search ON events(title);
CREATE INDEX idx_location_search ON events(location);
CREATE INDEX idx_organizer_search ON events(organizer);
CREATE INDEX idx_upcoming_events ON events(event_date, event_time) WHERE event_date >= CURDATE();

-- Views for common queries
CREATE VIEW upcoming_events AS
SELECT * FROM events 
WHERE event_date >= CURDATE() 
ORDER BY event_date ASC, event_time ASC;

CREATE VIEW open_events AS
SELECT * FROM events 
WHERE status = 'open' 
ORDER BY event_date ASC;

CREATE VIEW closed_events AS
SELECT * FROM events 
WHERE status = 'closed' 
ORDER BY event_date DESC;

-- Event statistics view
CREATE VIEW event_stats AS
SELECT 
    COUNT(*) as total_events,
    COUNT(CASE WHEN status = 'open' THEN 1 END) as open_events,
    COUNT(CASE WHEN status = 'closed' THEN 1 END) as closed_events,
    COUNT(CASE WHEN event_date >= CURDATE() THEN 1 END) as upcoming_events,
    COUNT(CASE WHEN event_date < CURDATE() THEN 1 END) as past_events,
    AVG(capacity) as avg_capacity,
    SUM(capacity) as total_capacity
FROM events;

COMMIT;