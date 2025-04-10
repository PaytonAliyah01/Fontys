-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 08, 2025 at 11:16 AM
-- Server version: 8.0.31
-- PHP Version: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `sportconnect`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

DROP TABLE IF EXISTS `activity_log`;
CREATE TABLE IF NOT EXISTS `activity_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chat_groups`
--

DROP TABLE IF EXISTS `chat_groups`;
CREATE TABLE IF NOT EXISTS `chat_groups` (
  `group_id` int NOT NULL AUTO_INCREMENT,
  `group_name` varchar(255) NOT NULL,
  `created_by` int NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_id`),
  KEY `created_by` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `chat_groups`
--

INSERT INTO `chat_groups` (`group_id`, `group_name`, `created_by`, `created_at`) VALUES
(1, 'gg', 6, '2025-04-04 13:31:41'),
(6, 'up', 4, '2025-04-07 11:47:32');

-- --------------------------------------------------------

--
-- Table structure for table `conversations`
--

DROP TABLE IF EXISTS `conversations`;
CREATE TABLE IF NOT EXISTS `conversations` (
  `conversation_id` int NOT NULL AUTO_INCREMENT,
  `user_1_id` int NOT NULL,
  `user_2_id` int NOT NULL,
  PRIMARY KEY (`conversation_id`),
  KEY `user_1_id` (`user_1_id`),
  KEY `user_2_id` (`user_2_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `conversations`
--

INSERT INTO `conversations` (`conversation_id`, `user_1_id`, `user_2_id`) VALUES
(1, 4, 6);

-- --------------------------------------------------------

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
CREATE TABLE IF NOT EXISTS `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text,
  `event_date` date NOT NULL,
  `event_time` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `location` varchar(255) NOT NULL,
  `sport_type` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `events`
--

INSERT INTO `events` (`id`, `user_id`, `title`, `description`, `event_date`, `event_time`, `location`, `sport_type`, `created_at`, `updated_at`) VALUES
(6, 4, 'kinsdaay hockey', NULL, '2025-04-27', '2025-04-04 11:15:10', '\'S Gravesandestraat 34', 'Hockey', '2025-04-04 11:15:10', '2025-04-04 11:15:10'),
(5, 4, 'Kingsday game', NULL, '2025-04-27', '2025-04-04 09:53:47', '\'S Gravesandestraat 34', 'Basketball', '2025-04-04 09:53:47', '2025-04-04 09:53:47'),
(7, 4, 'Kingsday Dodgeball', NULL, '2025-04-27', '2025-04-04 11:20:45', '\'S Gravesandestraat 34', 'Dodgeball', '2025-04-04 11:20:45', '2025-04-04 11:20:45'),
(8, 4, 'hgvfhjsedgv', NULL, '2025-04-23', '2025-04-04 12:51:34', '\'S Gravesandestraat 34', 'Dodgeball', '2025-04-04 12:51:34', '2025-04-04 12:51:34');

-- --------------------------------------------------------

--
-- Table structure for table `event_reviews`
--

DROP TABLE IF EXISTS `event_reviews`;
CREATE TABLE IF NOT EXISTS `event_reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` int NOT NULL,
  `review_text` text NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`)
) ;

--
-- Dumping data for table `event_reviews`
--

INSERT INTO `event_reviews` (`review_id`, `event_id`, `user_id`, `rating`, `review_text`, `created_at`) VALUES
(1, 6, 4, 3, 'vhjk', '2025-04-04 11:55:37');

-- --------------------------------------------------------

--
-- Table structure for table `friends`
--

DROP TABLE IF EXISTS `friends`;
CREATE TABLE IF NOT EXISTS `friends` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_1_id` int NOT NULL,
  `user_2_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_1_id` (`user_1_id`),
  KEY `user_2_id` (`user_2_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `friends`
--

INSERT INTO `friends` (`id`, `user_1_id`, `user_2_id`) VALUES
(1, 4, 6);

-- --------------------------------------------------------

--
-- Table structure for table `friend_requests`
--

DROP TABLE IF EXISTS `friend_requests`;
CREATE TABLE IF NOT EXISTS `friend_requests` (
  `id` int NOT NULL AUTO_INCREMENT,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `friend_requests`
--

INSERT INTO `friend_requests` (`id`, `sender_id`, `receiver_id`, `status`, `created_at`) VALUES
(1, 4, 6, 'accepted', '2025-04-04 12:01:34');

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

DROP TABLE IF EXISTS `goals`;
CREATE TABLE IF NOT EXISTS `goals` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `goal_title` varchar(255) NOT NULL,
  `target_value` int NOT NULL,
  `current_value` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `goal_progress`
--

DROP TABLE IF EXISTS `goal_progress`;
CREATE TABLE IF NOT EXISTS `goal_progress` (
  `id` int NOT NULL AUTO_INCREMENT,
  `goal_id` int NOT NULL,
  `value` int NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `goal_id` (`goal_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `group_members`
--

DROP TABLE IF EXISTS `group_members`;
CREATE TABLE IF NOT EXISTS `group_members` (
  `group_id` int NOT NULL,
  `user_id` int NOT NULL,
  `joined_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `group_members`
--

INSERT INTO `group_members` (`group_id`, `user_id`, `joined_at`) VALUES
(1, 6, '2025-04-04 13:31:41'),
(1, 4, '2025-04-04 13:31:41'),
(2, 6, '2025-04-04 13:41:24'),
(2, 4, '2025-04-04 13:41:24'),
(3, 6, '2025-04-04 13:44:30'),
(3, 4, '2025-04-04 13:44:30'),
(4, 4, '2025-04-07 11:43:16'),
(5, 4, '2025-04-07 11:43:55'),
(6, 4, '2025-04-07 11:47:32');

-- --------------------------------------------------------

--
-- Table structure for table `group_messages`
--

DROP TABLE IF EXISTS `group_messages`;
CREATE TABLE IF NOT EXISTS `group_messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `group_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `group_id` (`group_id`),
  KEY `sender_id` (`sender_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `group_messages`
--

INSERT INTO `group_messages` (`message_id`, `group_id`, `sender_id`, `message`, `timestamp`) VALUES
(1, 3, 6, 'hwlo', '2025-04-04 13:52:34'),
(2, 1, 4, 'hello', '2025-04-07 11:48:21'),
(3, 6, 4, 'hello', '2025-04-07 20:55:36');

-- --------------------------------------------------------

--
-- Table structure for table `invitations`
--

DROP TABLE IF EXISTS `invitations`;
CREATE TABLE IF NOT EXISTS `invitations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `inviter_id` int NOT NULL,
  `invitee_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `invitations`
--

INSERT INTO `invitations` (`id`, `event_id`, `user_id`, `status`, `created_at`, `inviter_id`, `invitee_id`) VALUES
(10, 7, 0, 'accepted', '2025-04-04 11:20:45', 0, 4),
(9, 5, 4, 'accepted', '2025-04-04 09:54:05', 4, 6),
(8, 2, 4, 'accepted', '2025-04-04 09:37:37', 4, 6),
(7, 1, 4, '', '2025-04-04 09:28:48', 4, 6),
(11, 8, 0, 'accepted', '2025-04-04 12:51:34', 0, 4),
(12, 6, 4, 'declined', '2025-04-04 12:52:25', 4, 6);

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

DROP TABLE IF EXISTS `messages`;
CREATE TABLE IF NOT EXISTS `messages` (
  `message_id` int NOT NULL AUTO_INCREMENT,
  `conversation_id` int NOT NULL,
  `sender_id` int NOT NULL,
  `receiver_id` int NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`),
  KEY `sender_id` (`sender_id`),
  KEY `receiver_id` (`receiver_id`),
  KEY `conversation_id` (`conversation_id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `conversation_id`, `sender_id`, `receiver_id`, `message`, `timestamp`) VALUES
(1, 1, 6, 0, 'hello', '2025-04-04 12:38:44'),
(2, 1, 4, 0, 'how are you\r\n', '2025-04-04 12:40:02'),
(3, 1, 4, 0, 'tast', '2025-04-04 12:53:00'),
(4, 1, 4, 0, 'tast', '2025-04-04 12:53:01'),
(5, 1, 4, 0, 'hello', '2025-04-04 12:53:09'),
(6, 1, 4, 0, 'hello', '2025-04-04 12:53:10'),
(7, 1, 4, 0, 'hello', '2025-04-04 12:53:10'),
(8, 1, 4, 0, 'hello', '2025-04-04 12:53:20'),
(9, 1, 4, 0, 'hell', '2025-04-04 12:53:35'),
(10, 1, 6, 0, 'i good\r\n', '2025-04-04 12:54:58'),
(11, 1, 6, 0, 'heelo', '2025-04-04 13:13:14'),
(12, 1, 4, 0, 'hello', '2025-04-07 20:31:33');

-- --------------------------------------------------------

--
-- Table structure for table `player_ratings`
--

DROP TABLE IF EXISTS `player_ratings`;
CREATE TABLE IF NOT EXISTS `player_ratings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `rated_user_id` int NOT NULL,
  `rater_user_id` int NOT NULL,
  `sport` enum('Basketball','Soccer','Tennis','Dodgeball','Hockey') NOT NULL,
  `rating` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `review` text,
  PRIMARY KEY (`id`),
  KEY `rated_user_id` (`rated_user_id`),
  KEY `rater_user_id` (`rater_user_id`)
) ;

--
-- Dumping data for table `player_ratings`
--

INSERT INTO `player_ratings` (`id`, `rated_user_id`, `rater_user_id`, `sport`, `rating`, `created_at`, `review`) VALUES
(1, 6, 4, 'Basketball', 3, '2025-04-04 12:50:37', 'can do better');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `rating` int DEFAULT NULL,
  `review` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`)
) ;

-- --------------------------------------------------------

--
-- Table structure for table `rsvps`
--

DROP TABLE IF EXISTS `rsvps`;
CREATE TABLE IF NOT EXISTS `rsvps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `event_id` int NOT NULL,
  `user_id` int NOT NULL,
  `status` enum('Going','Not Going','Maybe') DEFAULT 'Going',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `event_id` (`event_id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `rsvps`
--

INSERT INTO `rsvps` (`id`, `event_id`, `user_id`, `status`, `created_at`) VALUES
(1, 7, 6, '', '2025-04-04 09:31:32'),
(2, 8, 6, '', '2025-04-04 09:37:49'),
(3, 9, 6, '', '2025-04-04 10:47:54'),
(4, 6, 4, '', '2025-04-04 11:15:10'),
(5, 7, 4, '', '2025-04-04 11:20:45'),
(6, 8, 4, '', '2025-04-04 12:51:34'),
(7, 12, 6, '', '2025-04-04 12:54:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(255) NOT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `overall_ranking` float DEFAULT '0',
  `reset_token` varchar(255) DEFAULT NULL,
  `token_expiration` datetime DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `bio` text,
  `sports_interests` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `email`, `password`, `profile_picture`, `created_at`, `updated_at`, `overall_ranking`, `reset_token`, `token_expiration`, `location`, `bio`, `sports_interests`) VALUES
(4, 'billie b', 'elienne.phelipa@gmail.com', '$2y$10$AXbVYYNFSNt.2/VVvGrFMuXDLBSQJWk..pWlY11xw4RnQBi9OZaqu', 'uploads/astronaut-space-artwork-1660745.jpg', '2025-03-27 10:41:57', '2025-04-07 19:12:48', 0, NULL, NULL, '', NULL, ''),
(6, 'bob g', 'eliennephelipa06@gmail.com', '$2y$10$o0x8gHdU0T4Zg5/LpSgYhe.ksITIzRcLRSrsHror4ri1/uq4NC/L2', NULL, '2025-04-01 11:41:53', '2025-04-04 12:56:37', 0, NULL, NULL, '', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_sports_rankings`
--

DROP TABLE IF EXISTS `user_sports_rankings`;
CREATE TABLE IF NOT EXISTS `user_sports_rankings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `sport` enum('Basketball','Soccer','Tennis','Dodgeball','Hockey') NOT NULL,
  `ranking` float DEFAULT '0',
  `rating_count` int DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user_sports_rankings`
--

INSERT INTO `user_sports_rankings` (`id`, `user_id`, `sport`, `ranking`, `rating_count`, `updated_at`) VALUES
(1, 6, 'Basketball', 3, 1, '2025-04-04 12:50:37');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
