-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 26, 2026 at 10:46 AM
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
-- Database: `productdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `productname` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `quantity` int(11) NOT NULL,
  `image` varchar(255) NOT NULL,
  `category` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `productname`, `description`, `price`, `quantity`, `image`, `category`) VALUES
(1, 'Hype Text T-Shirt', 'Black cotton T-shirt with repeated HYPE logo design.', 750.00, 200, 'images/001.png', 'clothes'),
(2, 'Gradient Hype Tee', 'Minimal streetwear tee with gradient HYPE print.', 650.00, 200, 'images/002.png', 'clothes'),
(3, 'Ace Graphic Shirt', 'Premium graphic tee with artistic card design.', 720.00, 200, 'images/003.png', 'clothes'),
(4, 'Vertical Logo Shirt', 'Modern street style shirt with vertical HYPE logo.', 680.00, 200, 'images/004.png', 'clothes'),
(5, 'Titan Graphic Tee', 'Anime inspired graphic streetwear design.', 710.00, 200, 'images/005.png', 'clothes'),
(6, 'Color Art Street Tee', 'Colorful urban fashion with bold artwork print.', 620.00, 200, 'images/006.png', 'clothes'),
(7, 'Black Street Cargo Pants', 'Comfortable black cargo pants with a relaxed fit and multiple pockets. Perfect for casual streetwear and everyday use.', 199.00, 200, 'images/1772841574_007.png', 'pants'),
(8, 'Vintage Grey Denim Jeans', 'Stylish vintage grey denim jeans made with durable fabric. Designed for a classic casual look and long-lasting comfort.', 249.00, 11, 'images/1772841860_008.png', 'pants'),
(9, 'Khaki Utility Cargo Pants', 'Trendy khaki cargo pants with a modern utility design. Lightweight, breathable and ideal for daily wear.', 299.00, 8, 'images/1772841914_009.png', 'pants'),
(10, 'Classic Black Oversized Hoodie', 'A classic black oversized hoodie made from soft cotton fabric. Warm, comfortable and perfect for casual street style.', 550.00, 0, 'images/1772841958_010.png', 'hoodie'),
(11, 'Minimal Beige Pullover Hoodie', 'Minimalist beige hoodie with a clean and modern design. Soft material provides comfort for everyday wear.', 600.00, 10, 'images/1772842021_011.png', 'hoodie'),
(12, 'Purple Graphic Street Hoodie', 'Eye-catching purple hoodie with a stylish graphic print. Perfect for adding bold street fashion to your wardrobe.', 1300.00, 50, 'images/1772842107_012.png', 'hoodie');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
