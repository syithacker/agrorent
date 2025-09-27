CREATE DATABASE IF NOT EXISTS `agrorent`;
USE `agrorent`;

-- Table structure for table `users`
CREATE TABLE `users` (`user_id` INT(11) NOT NULL AUTO_INCREMENT, `name` VARCHAR(255) NOT NULL, `email` VARCHAR(255) NOT NULL, `password` VARCHAR(255) NOT NULL, `phone` VARCHAR(20) DEFAULT NULL, `address` VARCHAR(255) DEFAULT NULL, PRIMARY KEY (`user_id`), UNIQUE KEY `email` (`email`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `roles`
CREATE TABLE `roles` (`role_id` INT(11) NOT NULL AUTO_INCREMENT, `role_name` VARCHAR(50) NOT NULL, PRIMARY KEY (`role_id`), UNIQUE KEY `role_name` (`role_name`)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserting predefined roles
INSERT INTO `roles` (`role_id`, `role_name`) VALUES (1, 'farmer'), (2, 'owner'), (3, 'admin');

-- Table structure for table `user_roles`
CREATE TABLE `user_roles` (`id` INT(11) NOT NULL AUTO_INCREMENT, `user_id` INT(11) NOT NULL, `role_id` INT(11) NOT NULL, PRIMARY KEY (`id`), FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE, FOREIGN KEY (`role_id`) REFERENCES `roles`(`role_id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table structure for table `lands`
CREATE TABLE `lands` (`land_id` INT(11) NOT NULL AUTO_INCREMENT, `owner_id` INT(11) NOT NULL, `title` VARCHAR(255) NOT NULL, `location` VARCHAR(255) NOT NULL, `size` VARCHAR(100) NOT NULL, `price_per_month` DECIMAL(10,2) NOT NULL, `description` TEXT, `image_url` VARCHAR(255) DEFAULT 'https://images.unsplash.com/photo-1599849581337-33b00f685c2b', `status` ENUM('available','booked') NOT NULL DEFAULT 'available', PRIMARY KEY (`land_id`), FOREIGN KEY (`owner_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- =======================================================
-- DUMMY DATA AND ADMIN SETUP
-- =======================================================
--

-- 1. Create a dummy user who will be an Owner and a Farmer.
-- The password for this user is 'password123'
INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `phone`, `address`) VALUES
(1, 'John Doe', 'johndoe@example.com', '$2y$10$GLjY5y./h8418L1s72osN.2zE7s9V8y34qY94fbsIICGzF.9.sQeC', '1234567890', '123 Green Valley, Farmville');

-- 2. Assign the 'owner' and 'farmer' roles to John Doe (user_id = 1)
INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES (1, 1), (1, 2);

-- 3. TO MAKE A USER AN ADMIN (IMPORTANT!):
-- Run this query manually, replacing '1' with the user_id of the person you want to make an admin.
-- INSERT INTO `user_roles` (`user_id`, `role_id`) VALUES (1, 3);
-- For now, John Doe is NOT an admin.

-- 4. Add some dummy land listings owned by John Doe (owner_id = 1).
INSERT INTO `lands` (`owner_id`, `title`, `location`, `size`, `price_per_month`, `description`, `image_url`) VALUES
(1, 'Sunny Meadow Fields', 'Willow Creek, Punjab', '10 Acres', '15000.00', 'Fertile land perfect for wheat and corn cultivation. Comes with a pre-installed irrigation system.', 'https://images.unsplash.com/photo-1470058315132-0b23f03b664d'),
(1, 'Golden Harvest Patch', 'Riverbend, Haryana', '5 Acres', '9500.00', 'Rich soil ideal for vegetables and seasonal crops. Located near the main river for easy water access.', 'https://images.unsplash.com/photo-1500382017468-9049fed747ef'),
(1, 'Oakwood Farmland', 'Greenwood, Uttar Pradesh', '20 Acres', '22000.00', 'Expansive plot suitable for large-scale farming. Includes a small barn for storage. Soil tested and approved.', 'https://images.unsplash.com/photo-1586771107445-d3ca888129ff');