-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 26, 2026 at 10:07 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bookstore_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `books_tbl`
--

CREATE TABLE `books_tbl` (
  `book_id` int(11) NOT NULL,
  `title_fld` varchar(255) NOT NULL,
  `author_fld` varchar(255) NOT NULL,
  `description_fld` text DEFAULT NULL,
  `isbn_fld` varchar(50) DEFAULT NULL,
  `price_fld` decimal(10,2) NOT NULL,
  `stock_qty_fld` int(11) NOT NULL DEFAULT 0,
  `book_cover_image` varchar(255) DEFAULT NULL,
  `original_cover_image` varchar(255) DEFAULT NULL,
  `image_scale` decimal(10,4) DEFAULT 1.0000,
  `image_offset_x` decimal(10,4) DEFAULT 0.0000,
  `image_offset_y` decimal(10,4) DEFAULT 0.0000,
  `book_created_fld` timestamp NOT NULL DEFAULT current_timestamp(),
  `book_updated_fld` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books_tbl`
--

INSERT INTO `books_tbl` (`book_id`, `title_fld`, `author_fld`, `description_fld`, `isbn_fld`, `price_fld`, `stock_qty_fld`, `book_cover_image`, `original_cover_image`, `image_scale`, `image_offset_x`, `image_offset_y`, `book_created_fld`, `book_updated_fld`) VALUES
(3, 'The Hobbit', 'J.R.R. Tolkien', 'A comfortable, stay-at-home hobbit is swept into an epic quest to reclaim a lost dwarf kingdom from a fearsome dragon.', '9780547928227', 650.00, 40, 'book_1777008205_2c16ce23a86189fc.jpeg', NULL, 1.0000, 0.0000, 0.0000, '2026-04-24 05:23:25', '2026-04-24 10:15:56'),
(4, 'Atomic Habits', 'James Clear', 'A practical and proven framework for improving every day by forming good habits and breaking bad ones.', '9780735211292', 1195.00, 84, 'book_1777096179_1a2d15b4195784bd.jpeg', NULL, 1.0000, 0.0000, 0.0000, '2026-04-24 08:21:24', '2026-04-25 05:49:39'),
(5, 'A Brief History of Humankind', 'Yuval Noah Harari', 'An exploration of how biology and history have defined us and enhanced our understanding of what it means to be \"human.\"', '9780062316097', 1149.99, 0, 'book_1777096154_2770b70bd6267c0b.jpeg', NULL, 1.0000, 0.0000, 0.0000, '2026-04-24 08:34:44', '2026-04-25 05:49:14'),
(6, 'Where the Wild Things Are', 'Maurice Sendak', 'A young boy named Max journeys to an island of terrifying but easily tamed beasts after being sent to bed without supper.', '9780060254926', 450.00, 17, 'book_1777096092_a5d80d0c7eb79de2.jpeg', NULL, 1.0000, 0.0000, 0.0000, '2026-04-24 11:19:56', '2026-04-25 05:48:12'),
(8, 'To Kill a Mockingbird', 'Harper Lee', 'A gripping, heart-wrenching, and wholly remarkable tale of coming-of-age in a South poisoned by virulent prejudice.', '9780060935467', 550.00, 54, 'book_1777096240_4b2b409e1a67eb31.jpeg', NULL, 1.0000, 0.0000, 0.0000, '2026-04-25 05:50:40', '2026-04-25 05:50:40'),
(9, 'The Sun and Her Flowers', 'Rupi Kaur', 'A vibrant and transcendent journey about growth and healing, ancestry and honoring one\'s roots.', '9781449486792', 799.00, 22, 'book_1777096311_dc1a089f7e488f76.jpeg', NULL, 1.0000, 0.0000, 0.0000, '2026-04-25 05:51:51', '2026-04-25 05:51:51'),
(10, 'Pride and Prejudice', 'Jane Austen', 'A classic novel following the turbulent relationship between Elizabeth Bennet and the haughty Mr. Darcy.', '9780141439518', 350.00, 59, 'book_1777096372_f5658d351df78d9a.jpeg', NULL, 1.0000, 0.0000, 0.0000, '2026-04-25 05:52:52', '2026-04-25 05:52:52'),
(11, 'A Brief History of Time', 'Stephen Hawking', 'A landmark volume in science writing that explores fundamental questions about the universe, time, and space.', '9780553380163', 850.00, 14, 'book_1777096434_2ae1980d293a17a1.jpeg', NULL, 1.0000, 0.0000, 0.0000, '2026-04-25 05:53:54', '2026-04-25 05:53:54'),
(12, 'Dune', 'Frank Herbert', 'Set on the desert planet Arrakis, this epic tale follows young Paul Atreides as he navigates a complex political and ecological landscape.', '9780441172719', 699.00, 75, 'book_1777183910_1480d2ecdb830111.jpeg', NULL, 1.0000, 0.0000, 0.0000, '2026-04-25 05:55:01', '2026-04-26 06:11:50'),
(13, 'The Girl with the Dragon', 'Stieg Larsson', 'A disgraced journalist and a brilliant but troubled hacker team up to solve a decades-old disappearance.', '9780307949486', 599.00, 24, 'book_1777096617_e033c28ef7f5741e.jpeg', NULL, 1.0000, 0.0000, 0.0000, '2026-04-25 05:56:57', '2026-04-25 06:14:55');

-- --------------------------------------------------------

--
-- Table structure for table `book_categories_tbl`
--

CREATE TABLE `book_categories_tbl` (
  `book_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `book_categories_tbl`
--

INSERT INTO `book_categories_tbl` (`book_id`, `category_id`) VALUES
(3, 1),
(3, 11),
(4, 2),
(4, 8),
(5, 2),
(5, 9),
(5, 10),
(6, 1),
(6, 12),
(8, 1),
(8, 15),
(9, 14),
(10, 1),
(11, 2),
(11, 9),
(12, 1),
(12, 3),
(12, 11),
(13, 1),
(13, 4),
(13, 6);

-- --------------------------------------------------------

--
-- Table structure for table `categories_tbl`
--

CREATE TABLE `categories_tbl` (
  `category_id` int(11) NOT NULL,
  `category_name_fld` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories_tbl`
--

INSERT INTO `categories_tbl` (`category_id`, `category_name_fld`) VALUES
(11, 'Adventure'),
(7, 'Biography'),
(12, 'Children\'s Books'),
(15, 'Drama'),
(1, 'Fiction'),
(10, 'History'),
(4, 'Mystery'),
(2, 'Non-Fiction'),
(14, 'Poetry'),
(5, 'Romance'),
(9, 'Science'),
(3, 'Science Fiction'),
(8, 'Self-Help'),
(6, 'Thriller'),
(13, 'Young Adult');

-- --------------------------------------------------------

--
-- Table structure for table `orders_tbl`
--

CREATE TABLE `orders_tbl` (
  `order_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `total_amount_fld` decimal(10,2) NOT NULL,
  `payment_encrypted` varbinary(255) DEFAULT NULL,
  `payment_iv` varbinary(12) DEFAULT NULL,
  `payment_tag` varbinary(16) DEFAULT NULL,
  `order_status_fld` enum('pending','paid','shipped','completed','cancelled') DEFAULT 'pending',
  `order_created_fld` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_updated_fld` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items_tbl`
--

CREATE TABLE `order_items_tbl` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `quantity_fld` int(11) NOT NULL,
  `price_at_purchase_fld` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reviews_tbl`
--

CREATE TABLE `reviews_tbl` (
  `review_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `book_id` int(11) NOT NULL,
  `rating_fld` tinyint(4) NOT NULL,
  `comment_fld` text DEFAULT NULL,
  `review_created_fld` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_account_tbl`
--

CREATE TABLE `user_account_tbl` (
  `account_id` int(11) NOT NULL,
  `name_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone_encrypted` text DEFAULT NULL,
  `phone_iv` text DEFAULT NULL,
  `phone_tag` text DEFAULT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `account_created_fld` timestamp NOT NULL DEFAULT current_timestamp(),
  `account_updated_fld` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_account_tbl`
--

INSERT INTO `user_account_tbl` (`account_id`, `name_id`, `email`, `password_hash`, `phone_encrypted`, `phone_iv`, `phone_tag`, `role`, `account_created_fld`, `account_updated_fld`) VALUES
(21, 1020, 'JRD@gmail.com', '$2y$12$IZslXVM7nusLXtxnhTCNbuOIwxHfb7eFrcDZU1l4WXz93CF6WLzaq', 'jFtxfgNRlW719FQ=', 'H6rIYi//RMGapX2c', 'E7CB+AEC0pUt3zVQ9cQ8UA==', 'user', '2026-04-16 06:30:01', '2026-04-16 06:30:01'),
(22, 1021, 'jazrencell@gmail.com', '$2y$12$Q8J123finsbAbaeyHFGvtOPt11NJuK30yUCrkBD7Xz7HSvb.2c2rm', 'am9MDdLUxWi1qWg=', 'R3UR9ElUOFb0Rrgq', 'g+HIK2Onc0du/Leo3JVmLg==', 'admin', '2026-04-16 06:53:03', '2026-04-23 04:42:10'),
(23, 1022, 'user1@gmail.com', '$2y$12$7ccCAaiPWnUW9ognnzB6ve/TtI2DaUrOPj.tRnzsJQ4tbGcpubhDG', 'G3b/Sp+v+5jqr8o=', 'lZ/fmBfK47jIILNz', 'w6Izq2GVENQMfPNymQ40ew==', 'user', '2026-04-24 12:03:39', '2026-04-24 12:03:39'),
(24, 1023, 'testuser@example.com', '$2y$12$NMkb4h/RmnsPaSuCFZ39FehUyXXhTzIBA2cjbQFNIMc1Nevw1tq5C', 'YuVWfV2hBaCpaA==', 'Zc9+YhwrnJeeNAFR', 'o0Rt5vYMTEO4Mm7AHbvJxg==', 'user', '2026-04-24 13:25:04', '2026-04-24 13:25:04'),
(25, 1024, 'test@test.com', '$2y$12$h7ygeH5R3QkyCTotvDHvN.YcQuXodBPh8Ws4wveNiHoRk7JJ7xyW6', 'jIP9faDnHvKpqg==', 'MnBCnHIA+U33Humv', '83jLOdESTNx/jGuQLmVU9Q==', 'user', '2026-04-24 17:21:20', '2026-04-24 17:21:20'),
(26, 1025, 'user2@gmail.com', '$2y$12$nse330Ml5/rH7H1Ra6gf8u5duci2fY.Yi40d4BRUjd.L.0137BJzm', '/p+oYUYUQsI4PRZbPQ==', 'nEVV4CJCylTzKDtq', '5aL8x7uSjmkf6HB5fUs1Iw==', 'user', '2026-04-25 03:12:33', '2026-04-25 03:12:33'),
(27, 1026, 'JL@gmail.com', '$2y$12$mVkdsDGpWD6xjsvJ1BPgK.FBXRpf6BiYQtnEamoFsax0yfhXbLa7C', 'gx7+ZGJt2B8kFTvzbQ==', 'rJfRwBvXxM+1g9Lj', 'DliUY+wP3Yrb8w4eTqYDCA==', 'user', '2026-04-25 06:07:07', '2026-04-25 06:07:07');

-- --------------------------------------------------------

--
-- Table structure for table `user_address_tbl`
--

CREATE TABLE `user_address_tbl` (
  `address_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `country_fld` varchar(100) NOT NULL,
  `state_province_fld` varchar(100) NOT NULL,
  `city_town_fld` varchar(100) NOT NULL,
  `barangay_fld` varchar(100) DEFAULT NULL,
  `apartment_unit_fld` varchar(100) DEFAULT NULL,
  `streetnum_fld` varchar(100) NOT NULL,
  `housenum_fld` varchar(100) NOT NULL,
  `address_created_fld` timestamp NOT NULL DEFAULT current_timestamp(),
  `address_updated_fld` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `user_name_tbl`
--

CREATE TABLE `user_name_tbl` (
  `name_id` int(11) NOT NULL,
  `fname_fld` varchar(100) NOT NULL,
  `lname_fld` varchar(100) NOT NULL,
  `name_created_fld` timestamp NOT NULL DEFAULT current_timestamp(),
  `name_updated_fld` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_name_tbl`
--

INSERT INTO `user_name_tbl` (`name_id`, `fname_fld`, `lname_fld`, `name_created_fld`, `name_updated_fld`) VALUES
(1020, 'Jasmin Raine', 'Dela Cruz', '2026-04-16 06:30:01', '2026-04-16 06:30:01'),
(1021, 'James Lawrence', 'Dela Cruz', '2026-04-16 06:53:03', '2026-04-16 06:53:03'),
(1022, 'James Lawrence', 'Dela Cruz', '2026-04-24 12:03:39', '2026-04-24 12:03:39'),
(1023, 'Test', 'User', '2026-04-24 13:25:04', '2026-04-24 13:25:04'),
(1024, 'Test', 'User', '2026-04-24 17:21:20', '2026-04-24 17:21:20'),
(1025, 'Jerome', 'Recalde', '2026-04-25 03:12:33', '2026-04-25 03:12:33'),
(1026, 'James Lawrence', 'Dela Cruz', '2026-04-25 06:07:07', '2026-04-25 06:07:07');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `books_tbl`
--
ALTER TABLE `books_tbl`
  ADD PRIMARY KEY (`book_id`),
  ADD UNIQUE KEY `isbn_fld` (`isbn_fld`);

--
-- Indexes for table `book_categories_tbl`
--
ALTER TABLE `book_categories_tbl`
  ADD PRIMARY KEY (`book_id`,`category_id`),
  ADD KEY `idx_bc_category_id` (`category_id`);

--
-- Indexes for table `categories_tbl`
--
ALTER TABLE `categories_tbl`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name_fld` (`category_name_fld`);

--
-- Indexes for table `orders_tbl`
--
ALTER TABLE `orders_tbl`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_orders_account_id` (`account_id`);

--
-- Indexes for table `order_items_tbl`
--
ALTER TABLE `order_items_tbl`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `idx_items_order_id` (`order_id`),
  ADD KEY `idx_items_book_id` (`book_id`);

--
-- Indexes for table `reviews_tbl`
--
ALTER TABLE `reviews_tbl`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `idx_reviews_account_id` (`account_id`),
  ADD KEY `idx_reviews_book_id` (`book_id`);

--
-- Indexes for table `user_account_tbl`
--
ALTER TABLE `user_account_tbl`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `uq_buyer_email` (`email`),
  ADD KEY `idx_buyer_name_id` (`name_id`);

--
-- Indexes for table `user_address_tbl`
--
ALTER TABLE `user_address_tbl`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `idx_address_account_id` (`account_id`);

--
-- Indexes for table `user_name_tbl`
--
ALTER TABLE `user_name_tbl`
  ADD PRIMARY KEY (`name_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `books_tbl`
--
ALTER TABLE `books_tbl`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `categories_tbl`
--
ALTER TABLE `categories_tbl`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `orders_tbl`
--
ALTER TABLE `orders_tbl`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `order_items_tbl`
--
ALTER TABLE `order_items_tbl`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `reviews_tbl`
--
ALTER TABLE `reviews_tbl`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_account_tbl`
--
ALTER TABLE `user_account_tbl`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `user_address_tbl`
--
ALTER TABLE `user_address_tbl`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_name_tbl`
--
ALTER TABLE `user_name_tbl`
  MODIFY `name_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1027;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `book_categories_tbl`
--
ALTER TABLE `book_categories_tbl`
  ADD CONSTRAINT `fk_bc_book` FOREIGN KEY (`book_id`) REFERENCES `books_tbl` (`book_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_bc_category` FOREIGN KEY (`category_id`) REFERENCES `categories_tbl` (`category_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `orders_tbl`
--
ALTER TABLE `orders_tbl`
  ADD CONSTRAINT `fk_orders_account` FOREIGN KEY (`account_id`) REFERENCES `user_account_tbl` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `order_items_tbl`
--
ALTER TABLE `order_items_tbl`
  ADD CONSTRAINT `fk_items_book` FOREIGN KEY (`book_id`) REFERENCES `books_tbl` (`book_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_items_order` FOREIGN KEY (`order_id`) REFERENCES `orders_tbl` (`order_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `reviews_tbl`
--
ALTER TABLE `reviews_tbl`
  ADD CONSTRAINT `fk_reviews_account` FOREIGN KEY (`account_id`) REFERENCES `user_account_tbl` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_reviews_book` FOREIGN KEY (`book_id`) REFERENCES `books_tbl` (`book_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_account_tbl`
--
ALTER TABLE `user_account_tbl`
  ADD CONSTRAINT `fk_account_name` FOREIGN KEY (`name_id`) REFERENCES `user_name_tbl` (`name_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user_address_tbl`
--
ALTER TABLE `user_address_tbl`
  ADD CONSTRAINT `fk_address_account` FOREIGN KEY (`account_id`) REFERENCES `user_account_tbl` (`account_id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;


CREATE TABLE user_tokens_tbl ( token_id INT AUTO_INCREMENT PRIMARY KEY, 
account_id INT NOT NULL, token VARCHAR(64) NOT NULL UNIQUE, created_at 
TIMESTAMP DEFAULT CURRENT_TIMESTAMP, expires_at DATETIME NOT NULL, 
FOREIGN KEY (account_id) REFERENCES user_account_tbl(account_id) ON 
DELETE CASCADE );  

