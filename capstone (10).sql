-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 17, 2025 at 10:55 AM
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
-- Database: `capstone`
--

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `cart_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `added_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000001_create_cache_table', 1),
(2, '0001_01_01_000002_create_jobs_table', 1),
(3, '2024_11_08_111653_create_sessions_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `type` enum('Gadget','Accessory','Spare Part') NOT NULL,
  `brand` varchar(100) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `name`, `description`, `base_price`, `type`, `brand`, `is_active`, `image_url`, `created_at`, `updated_at`) VALUES
(1, 'iPhone 16', 'Latest model of iPhone with advanced features', 999.00, 'Gadget', 'Apple', 1, 'images/products/iphone-16.png', '2024-11-03 10:07:24', '2025-01-27 04:04:12'),
(6, 'Samsung S24', 'New Samsung Phonesssweqeqweq', 40001.00, 'Gadget', 'Samsung', 1, 'images/products/erWBw9HJkRo2CwKKWE3cL9yszmmU0XUe89r3S0bO.png', '2024-12-03 00:15:47', '2025-02-14 10:01:24');

-- --------------------------------------------------------

--
-- Table structure for table `product_orders`
--

CREATE TABLE `product_orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `address_id` int(11) DEFAULT NULL,
  `product_id` int(11) NOT NULL DEFAULT 0,
  `variant_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_status` enum('Pending','Processed','Shipped','Delivered','Cancelled') NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_method` enum('walk-in','gcash') NOT NULL DEFAULT 'walk-in',
  `gcash_ref_number` varchar(50) DEFAULT NULL,
  `gcash_screenshot` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_orders`
--

INSERT INTO `product_orders` (`order_id`, `user_id`, `address_id`, `product_id`, `variant_id`, `quantity`, `total_price`, `order_status`, `order_date`, `updated_at`, `payment_method`, `gcash_ref_number`, `gcash_screenshot`) VALUES
(2, 1, 6, 1, 1, 1, 1049.00, 'Pending', '2025-02-17 09:54:33', '2025-02-17 09:54:33', 'walk-in', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `repair_requests`
--

CREATE TABLE `repair_requests` (
  `request_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `error_description` text NOT NULL,
  `media` varchar(255) DEFAULT NULL,
  `delivery_option` enum('Deliver','Drop-off') NOT NULL,
  `status` enum('Pending','Approved','Declined') NOT NULL DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `repair_requests`
--

INSERT INTO `repair_requests` (`request_id`, `user_id`, `product_name`, `error_description`, `media`, `delivery_option`, `status`, `created_at`, `updated_at`) VALUES
(11, 1, 'Iphone ix', 'Guba ang screen ', '1738920368_1000097345.mp4, 1738920368_1000097344.jpg, 1738920368_1000097343.jpg', 'Drop-off', 'Pending', '2025-02-07 09:26:08', '2025-02-08 09:37:51');

-- --------------------------------------------------------

--
-- Table structure for table `shipping_addresses`
--

CREATE TABLE `shipping_addresses` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `recipient_name` varchar(255) NOT NULL,
  `street_address` varchar(255) NOT NULL,
  `barangay` varchar(100) NOT NULL,
  `city` varchar(100) NOT NULL,
  `postal_code` varchar(4) DEFAULT NULL,
  `mobile_number` varchar(11) DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping_addresses`
--

INSERT INTO `shipping_addresses` (`address_id`, `user_id`, `recipient_name`, `street_address`, `barangay`, `city`, `postal_code`, `mobile_number`, `is_default`, `created_at`, `updated_at`) VALUES
(2, 1, 'John Doe', '1234 Main St', 'Barangay 1', 'Citytown', '1234', '9171234567', 0, '2025-02-01 13:17:45', '2025-02-01 13:18:37'),
(4, 1, 'John Doe', '1234 Main St', 'Barangay 1', 'Citytown', '1234', '9171234567', 0, '2025-02-01 13:17:59', '2025-02-01 13:18:37'),
(5, 1, 'John Doe', '1234 Main St', 'Barangay 1', 'Citytown', '1234', '9171234567', 1, '2025-02-01 13:18:37', '2025-02-01 13:18:37'),
(6, 1, 'Test name', 'Test street', 'Test brgy', 'Test city', '1234', '9123456789', 0, '2025-02-01 13:18:43', '2025-02-01 13:18:43');

-- --------------------------------------------------------

--
-- Table structure for table `staffs`
--

CREATE TABLE `staffs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `type` enum('Staff','Owner','Manager','Admin','Repairman') DEFAULT 'Staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staffs`
--

INSERT INTO `staffs` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `type`) VALUES
(1, 'test', 'test@acc.com', NULL, '$2y$12$ty02epUdD4occ10vTMG3Z.bLLXrkXwxM2RVvIx41YONhYyp5QaPfq', NULL, '2024-11-08 03:47:13', '2024-11-08 03:47:13', 'Staff'),
(4, 'testrepairman', 'repairtest@gmail.com', NULL, '$2y$12$RKfjdCiY6SrjOVnwCoUqrew9M5/ckDX3LyMasIGymBb3WyQA03VD2', NULL, '2025-02-17 00:47:29', '2025-02-17 00:47:29', 'Repairman');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Staff','Technician','Customer') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `created_at`, `updated_at`) VALUES
(1, 'Jancine', 'jancineroque018@gmail.com', '$2y$10$Af7ibhmu8Q3HjZug52UYs.XTCJT6KOn.necxa1/EC5Ez/0oG3EvBK', 'Customer', '2024-10-21 14:52:38', '2024-10-21 14:52:38'),
(2, 'Testing', 'test@gmail.com', '$2y$10$k/dHZe9uRDxGHKoC731QCOkkN0rfmXLzhT48QBa5260z.6dtptxkC', 'Customer', '2024-10-21 14:53:31', '2024-10-21 14:53:31'),
(3, 'Testing1', 'test11@gmail.com', '$2y$10$Z4Unl98lUL5RSr0torihXOmkyvK6HXUk2zxV/k0OmABhvmGKsZrl6', 'Customer', '2024-10-21 14:56:17', '2024-10-21 14:56:17');

-- --------------------------------------------------------

--
-- Table structure for table `variants`
--

CREATE TABLE `variants` (
  `variant_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `variant_name` varchar(100) NOT NULL,
  `additional_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `stock_quantity` int(11) DEFAULT 0,
  `image_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `variants`
--

INSERT INTO `variants` (`variant_id`, `product_id`, `variant_name`, `additional_price`, `stock_quantity`, `image_url`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 1, 'Black', 50.00, 100, 'images/products/variants/iphone-16-black.png', '2024-11-03 10:07:24', '2025-01-27 04:18:02', 1),
(2, 1, 'Blue', 100.00, 50, 'images/products/variants/iphone-16-blue.png', '2024-11-03 10:07:24', '2025-01-31 07:59:42', 0),
(3, 6, 'Gold', 0.00, 50, 'images/products/variants/s24-gold.png', '2024-12-13 15:06:11', '2025-01-27 04:18:07', 1),
(4, 6, 'test', 0.00, 100, 'variants/xBsZ9LHkBQ7v7cesMexHkdUupx8rT2muroaEXbfs.png', '2025-01-23 07:54:03', '2025-01-23 07:54:03', 1),
(5, 6, 'testing', 0.00, 10, 'variants/BDn4A8xisUWe1o6teYomODPWVYK0j5KalwLXYQ0V.png', '2025-01-23 07:55:42', '2025-01-23 07:55:42', 1),
(6, 6, 'test1', 0.00, 10, 'variants/mBs2MMfcEHD7KnGli4r2OxCVLddl47lXa7W0QE5s.png', '2025-01-23 07:56:37', '2025-01-23 07:56:37', 1),
(7, 6, 'testing 11', 0.00, 10, 'variants/KExN3TcGlTKTDABo8igjDIj4OQEt5chh73l3dNIJ.png', '2025-01-23 07:57:22', '2025-01-23 07:57:22', 1),
(8, 1, 'testing', 0.00, 8, 'images/3JFKgLvJOmC3qYGtAjbweaMvx87lBgPvydAbaagW.png', '2025-01-24 06:36:32', '2025-01-24 06:36:32', 1),
(9, 1, 'testing 11', 0.00, 9, 'images/ZtJjm2iaiFzxyvh90BrS1IIdqAMK37BuwIornz3w.png', '2025-01-24 06:40:14', '2025-01-24 06:40:14', 1),
(10, 1, 'qwerty', 0.00, 8, 'images/r4T9fy1ijaniPiCBciKSmv1igNDPi3bMo5WMCbDX.png', '2025-01-26 23:53:01', '2025-01-26 23:53:01', 1),
(11, 6, 'asd', 0.00, 8, 'images/M3UGDokxhLmQSd8UqxYCpPO7bwBMo59AyfZJ3EKw.png', '2025-01-27 00:21:51', '2025-01-27 00:21:51', 1),
(12, 1, 'waw112', 0.00, 90, 'images/EGtnFFY9Ln2tvBw2dPWkiFMUE9c71LO7Gsje71gr.png', '2025-01-27 00:22:21', '2025-01-27 03:44:56', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`cart_id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`,`variant_id`),
  ADD KEY `cart_ibfk_2` (`product_id`),
  ADD KEY `cart_ibfk_3` (`variant_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Indexes for table `product_orders`
--
ALTER TABLE `product_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `variant_id` (`variant_id`),
  ADD KEY `fk_product_orders_address` (`address_id`),
  ADD KEY `fk_user_id` (`user_id`),
  ADD KEY `product_orders_ibfk_2` (`product_id`);

--
-- Indexes for table `repair_requests`
--
ALTER TABLE `repair_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `staffs`
--
ALTER TABLE `staffs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `staffs_email_unique` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `variants`
--
ALTER TABLE `variants`
  ADD PRIMARY KEY (`variant_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `cart_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `product_orders`
--
ALTER TABLE `product_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `repair_requests`
--
ALTER TABLE `repair_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `staffs`
--
ALTER TABLE `staffs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `variants`
--
ALTER TABLE `variants`
  MODIFY `variant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `cart_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`variant_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `product_orders`
--
ALTER TABLE `product_orders`
  ADD CONSTRAINT `fk_product_orders_address` FOREIGN KEY (`address_id`) REFERENCES `shipping_addresses` (`address_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `product_orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `product_orders_ibfk_3` FOREIGN KEY (`variant_id`) REFERENCES `variants` (`variant_id`);

--
-- Constraints for table `repair_requests`
--
ALTER TABLE `repair_requests`
  ADD CONSTRAINT `repair_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `shipping_addresses`
--
ALTER TABLE `shipping_addresses`
  ADD CONSTRAINT `shipping_addresses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
