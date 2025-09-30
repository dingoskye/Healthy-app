-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 29, 2025 at 08:36 AM
-- Server version: 8.4.2
-- PHP Version: 8.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `prototype_nutribot`
--

-- --------------------------------------------------------

--
-- Table structure for table `shop_products`
--

CREATE TABLE `shop_products` (
  `id` int NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `gram` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `shop_products`
--

INSERT INTO `shop_products` (`id`, `name`, `description`, `price`, `image`, `created_at`, `gram`) VALUES
(5, 'aa', 'aa', 11.00, 'prod_68d3be41a0e5e3.01717558.png', '2025-09-24 09:47:45', NULL),
(6, 'coffee', 'cofee', 120.00, 'prod_68d3be55766bf8.26379324.png', '2025-09-24 09:48:05', NULL),
(7, 'coffee', 'aa', 180.00, 'prod_68d3bec6f030c2.56313928.png', '2025-09-24 09:49:58', NULL),
(8, 'coffee', 'aa', 180.00, 'prod_68d3befaae5d63.30774422.png', '2025-09-24 09:50:50', NULL),
(9, 'coffee', 'aa', 180.00, 'prod_68d3bff0bc3e52.30248255.png', '2025-09-24 09:54:56', NULL),
(10, 'Koffiebonen Arabica', 'Premium medium roast koffiebonen, 500g zak.', 7.99, 'coffee.jpg', '2025-09-24 09:57:24', NULL),
(11, 'Groene Thee Matcha', 'Japanse biologische matcha, 30g blikje.', 12.50, 'matcha.jpg', '2025-09-24 09:57:24', NULL),
(12, 'Chocoladecroissant', 'Verse Franse croissant gevuld met pure chocolade.', 2.25, 'croissant.jpg', '2025-09-24 09:57:24', NULL),
(13, 'Aardbeien Smoothie', 'Verse smoothie met aardbei en banaan, 330ml.', 3.75, 'smoothie.jpg', '2025-09-24 09:57:24', NULL),
(14, 'BBQ Saus Smokey', 'Rokerige barbecuesaus, licht pittig, 250ml fles.', 4.10, 'bbq_saus.jpg', '2025-09-24 09:57:24', NULL),
(15, 'Olijfolie Extra Vergine', 'Koude persing uit Italië, 500ml fles.', 8.90, 'olijfolie.jpg', '2025-09-24 09:57:24', NULL),
(16, 'Pizza Margherita', 'Handgemaakte pizza met tomaat, mozzarella en basilicum.', 6.50, 'pizza.jpg', '2025-09-24 09:57:24', NULL),
(17, 'Spaghetti Pasta', 'Traditionele Italiaanse spaghetti, 1kg pak.', 1.99, 'spaghetti.jpg', '2025-09-24 09:57:24', NULL),
(18, 'Parmezaanse Kaas', 'Geraspte Parmigiano Reggiano, 200g.', 5.40, 'parmezaan.jpg', '2025-09-24 09:57:24', NULL),
(19, 'Avocado Dip Guacamole', 'Romige guacamole met limoensap, 200g.', 3.20, 'guacamole.jpg', '2025-09-24 09:57:24', NULL),
(20, 'Cola Zero', 'Frisdrank zonder suiker, 330ml blik.', 1.20, 'cola_zero.jpg', '2025-09-24 09:57:24', NULL),
(21, 'Minerale Water', 'Natuurlijk bronwater, 1 liter fles.', 0.90, 'water.jpg', '2025-09-24 09:57:24', NULL),
(22, 'Honing Biologisch', 'Pure biologische bloemenhoning, 250g pot.', 4.50, 'honing.jpg', '2025-09-24 09:57:24', NULL),
(23, 'Hazelnootpasta', 'Chocolade-hazelnootpasta, 400g pot.', 3.95, 'hazelnootpasta.jpg', '2025-09-24 09:57:24', NULL),
(24, 'Zongedroogde Tomaten', 'Zongedroogde tomaten op olie, 300g pot.', 4.70, 'zongedroogd.jpg', '2025-09-24 09:57:24', NULL),
(25, 'Rijst Basmati', 'Aromatische witte basmati rijst, 1kg zak.', 2.60, 'basmati.jpg', '2025-09-24 09:57:24', NULL),
(26, 'Chili Saus Zoet', 'Thaise zoete chilisaus, 250ml fles.', 2.30, 'chili_zoet.jpg', '2025-09-24 09:57:24', NULL),
(27, 'Kaasplank Deluxe', 'Mix van brie, camembert en blauwe kaas, 350g.', 9.99, 'kaasplank.jpg', '2025-09-24 09:57:24', NULL),
(28, 'Bananen', 'Biologische bananen, per tros van ~1kg.', 2.10, 'bananen.jpg', '2025-09-24 09:57:24', NULL),
(29, 'Sinaasappelsap Vers', 'Versgeperst sinaasappelsap, 500ml fles.', 3.40, 'sinaasappelsap.jpg', '2025-09-24 09:57:24', NULL),
(30, 'ss', 'dd', 11.00, 'https://images.openfoodfacts.org/images/products/322/885/700/1323/front_fr.1746.400.jpg', '2025-09-24 11:33:23', '26 g (1 tranche)'),
(31, 'ss', 'aa', 22.00, 'https://images.openfoodfacts.org/images/products/322/885/700/1323/front_fr.1746.400.jpg', '2025-09-24 11:33:29', '26 g (1 tranche)'),
(32, 'banaan', '', 1.00, 'https://images.openfoodfacts.org/images/products/871/798/200/5337/front_fr.3.400.jpg', '2025-09-24 11:33:46', '400 gram'),
(33, 'basmati rijst', '', 2.00, 'https://images.openfoodfacts.org/images/products/433/725/660/0132/front_de.15.400.jpg', '2025-09-24 11:35:59', NULL),
(34, 'banana', '', 0.01, 'https://images.openfoodfacts.org/images/products/611/124/210/2941/front_en.27.400.jpg', '2025-09-24 11:36:17', NULL),
(35, 'banana', '', 0.01, 'https://images.openfoodfacts.org/images/products/611/124/210/2941/front_en.27.400.jpg', '2025-09-24 11:36:18', NULL),
(36, 'chocolate', '', 11.00, 'https://images.openfoodfacts.org/images/products/304/692/002/9759/front_en.396.400.jpg', '2025-09-24 11:36:44', '1 portion (100 g)'),
(37, 'aa', 'aa', 11.00, 'https://images.openfoodfacts.org/images/products/009/661/942/7383/front_en.45.400.jpg', '2025-09-24 11:37:27', '1 large egg (50 g)'),
(38, 'chocolate', '', 11.00, 'https://images.openfoodfacts.org/images/products/304/692/002/9759/front_en.396.400.jpg', '2025-09-24 11:39:38', '1 portion (100 g)'),
(39, 'bananen', '', 1.00, 'https://images.openfoodfacts.org/images/products/425/129/111/3221/front_de.52.400.jpg', '2025-09-24 11:39:58', NULL),
(40, 'banaan', '', 1.00, 'https://images.openfoodfacts.org/images/products/871/798/200/5337/front_fr.3.400.jpg', '2025-09-24 11:41:11', '400 gram'),
(41, 'trosbanen', '', 1.00, 'uploads/default.jpg', '2025-09-24 11:41:28', NULL),
(42, 'apple', '', 1.00, 'https://images.openfoodfacts.org/images/products/506/048/284/0179/front_en.25.400.jpg', '2025-09-24 11:41:51', '1 bar (50 g)'),
(43, '1kg apple', '', 1.00, 'https://images.openfoodfacts.org/images/products/542/000/150/9602/front_fr.4.400.jpg', '2025-09-24 11:42:15', NULL),
(44, 'apples', '', 1.00, 'https://images.openfoodfacts.org/images/products/506/048/284/0179/front_en.25.400.jpg', '2025-09-24 11:42:30', '1 bar (50 g)'),
(45, 'ss', '', 11.00, 'uploads/prod_68d52bce079af9.44812103.png', '2025-09-25 11:47:26', NULL),
(46, 'ss', '', 11.00, 'uploads/prod_68d52bcf9216f0.97970942.png', '2025-09-25 11:47:27', NULL),
(47, 'ss', '', 11.00, 'uploads/prod_68d52bd04bb9d8.31739501.png', '2025-09-25 11:47:28', NULL),
(48, 'coffee', '', 11.00, 'https://images.openfoodfacts.org/images/products/322/885/700/1323/front_fr.1746.400.jpg', '2025-09-25 11:47:41', '26 g (1 tranche)'),
(49, 'coffee', '', 11.00, 'uploads/prod_68d52bed315cc8.82802704.png', '2025-09-25 11:47:57', NULL),
(50, 'coffee', '', 11.00, 'uploads/prod_68d52bf632e921.53558317.png', '2025-09-25 11:48:06', NULL),
(51, 'coffee', '', 11.00, 'uploads/prod_68d52c10a90b54.97442286.png', '2025-09-25 11:48:32', NULL),
(52, 'coffee', '', 11.00, 'uploads/prod_68d52c7e1a8399.34861894.png', '2025-09-25 11:50:22', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `shop_products`
--
ALTER TABLE `shop_products`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `shop_products`
--
ALTER TABLE `shop_products`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
