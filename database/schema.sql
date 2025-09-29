-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 29, 2025 at 12:43 PM
-- Server version: 5.7.42
-- PHP Version: 8.2.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `exp`
--

-- --------------------------------------------------------

--
-- Table structure for table `budgets`
--

CREATE TABLE `budgets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `period` enum('monthly','yearly') COLLATE utf8mb4_unicode_ci DEFAULT 'monthly',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `budgets`
--

INSERT INTO `budgets` (`id`, `user_id`, `category_id`, `amount`, `period`, `start_date`, `end_date`, `created_at`, `updated_at`) VALUES
(1, 1, 4, '300.00', 'monthly', '2025-09-28', '2025-10-28', '2025-09-28 01:48:39', '2025-09-28 01:48:39'),
(2, 1, 1, '10000.00', 'monthly', '2025-08-31', '2025-09-29', '2025-09-28 22:59:29', '2025-09-28 22:59:29'),
(3, 2, 11, '3000.00', 'monthly', '2025-08-31', '2025-09-29', '2025-09-29 00:02:44', '2025-09-29 00:02:44');

-- --------------------------------------------------------

--
-- Stand-in structure for view `budget_vs_actual`
-- (See below for the actual view)
--
CREATE TABLE `budget_vs_actual` (
`budget_id` int(11)
,`user_id` int(11)
,`budget_amount` decimal(10,2)
,`category_name` varchar(100)
,`category_color` varchar(7)
,`category_type` varchar(7)
,`budget_type` varchar(7)
,`actual_amount` decimal(32,2)
,`percentage_used` decimal(41,6)
,`period` varchar(7)
,`start_date` date
,`end_date` date
);

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name_ar` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(7) COLLATE utf8mb4_unicode_ci DEFAULT '#3B82F6',
  `category_type` enum('income','expense','both') COLLATE utf8mb4_unicode_ci DEFAULT 'expense',
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`id`, `name`, `name_ar`, `icon`, `color`, `category_type`, `user_id`, `created_at`) VALUES
(1, 'Rent & Utilities', 'الإيجار والمرافق', 'fas fa-home', '#10B981', 'expense', NULL, '2025-09-28 01:03:42'),
(2, 'Groceries', 'البقالة', 'fas fa-shopping-cart', '#3B82F6', 'expense', NULL, '2025-09-28 01:03:42'),
(3, 'Entertainment', 'الترفيه', 'fas fa-film', '#8B5CF6', 'expense', NULL, '2025-09-28 01:03:42'),
(4, 'Subscriptions', 'الاشتراكات', 'fas fa-credit-card', '#F59E0B', 'expense', NULL, '2025-09-28 01:03:42'),
(5, 'Transportation', 'المواصلات', 'fas fa-car', '#EF4444', 'expense', NULL, '2025-09-28 01:03:42'),
(6, 'Healthcare', 'الرعاية الصحية', 'fas fa-heartbeat', '#EC4899', 'expense', NULL, '2025-09-28 01:03:42'),
(7, 'Education', 'التعليم', 'fas fa-graduation-cap', '#06B6D4', 'expense', NULL, '2025-09-28 01:03:42'),
(8, 'Shopping', 'التسوق', 'fas fa-shopping-bag', '#84CC16', 'expense', NULL, '2025-09-28 01:03:42'),
(9, 'Dining', 'الطعام', 'fas fa-utensils', '#F97316', 'expense', NULL, '2025-09-28 01:03:42'),
(10, 'Travel', 'السفر', 'fas fa-plane', '#6366F1', 'expense', NULL, '2025-09-28 01:03:42'),
(11, 'Salary', 'الراتب', 'fas fa-money-bill-wave', '#10B981', 'income', NULL, '2025-09-28 01:51:06'),
(12, 'Freelance', 'العمل الحر', 'fas fa-laptop-code', '#3B82F6', 'income', NULL, '2025-09-28 01:51:06'),
(13, 'Business Income', 'دخل الأعمال', 'fas fa-briefcase', '#8B5CF6', 'income', NULL, '2025-09-28 01:51:06'),
(14, 'Investments', 'الاستثمارات', 'fas fa-chart-line', '#F59E0B', 'income', NULL, '2025-09-28 01:51:06'),
(15, 'Rental Income', 'دخل الإيجار', 'fas fa-building', '#EF4444', 'income', NULL, '2025-09-28 01:51:06'),
(16, 'Dividends', 'أرباح الأسهم', 'fas fa-percentage', '#EC4899', 'income', NULL, '2025-09-28 01:51:06'),
(17, 'Interest', 'الفوائد', 'fas fa-piggy-bank', '#06B6D4', 'income', NULL, '2025-09-28 01:51:06'),
(18, 'Gifts & Bonuses', 'الهدايا والمكافآت', 'fas fa-gift', '#84CC16', 'income', NULL, '2025-09-28 01:51:06'),
(19, 'Side Hustle', 'الأعمال الجانبية', 'fas fa-tools', '#F97316', 'income', NULL, '2025-09-28 01:51:06'),
(20, 'Other Income', 'دخل آخر', 'fas fa-plus-circle', '#6366F1', 'income', NULL, '2025-09-28 01:51:06');

-- --------------------------------------------------------

--
-- Table structure for table `income_budgets`
--

CREATE TABLE `income_budgets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `target_amount` decimal(10,2) NOT NULL,
  `period` enum('monthly','yearly') COLLATE utf8mb4_unicode_ci DEFAULT 'monthly',
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `income_expense_summary`
-- (See below for the actual view)
--
CREATE TABLE `income_expense_summary` (
`user_id` int(11)
,`user_name` varchar(100)
,`year` int(4)
,`month` int(2)
,`total_income` decimal(32,2)
,`total_expenses` decimal(32,2)
,`net_income` decimal(32,2)
,`income_count` bigint(21)
,`expense_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `monthly_spending`
-- (See below for the actual view)
--
CREATE TABLE `monthly_spending` (
`user_id` int(11)
,`user_name` varchar(100)
,`year` int(4)
,`month` int(2)
,`category_name` varchar(100)
,`category_color` varchar(7)
,`category_type` enum('income','expense','both')
,`transaction_type` enum('income','expense')
,`total_amount` decimal(32,2)
,`transaction_count` bigint(21)
);

-- --------------------------------------------------------

--
-- Table structure for table `savings_goals`
--

CREATE TABLE `savings_goals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_amount` decimal(10,2) NOT NULL,
  `current_amount` decimal(10,2) DEFAULT '0.00',
  `target_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `transaction_type` enum('income','expense') COLLATE utf8mb4_unicode_ci DEFAULT 'expense',
  `merchant` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `status` enum('pending','cleared','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'cleared',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `receipt_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `user_id`, `category_id`, `description`, `amount`, `transaction_type`, `merchant`, `transaction_date`, `status`, `notes`, `receipt_path`, `created_at`, `updated_at`) VALUES
(1, 1, 5, 'حمار مخطط', '2000.00', 'expense', '', '2025-09-28', 'cleared', NULL, NULL, '2025-09-28 01:47:21', '2025-09-28 01:47:21'),
(2, 1, 11, 'مرتب 6', '2000.00', 'income', '', '2025-09-28', 'cleared', '', NULL, '2025-09-28 05:57:47', '2025-09-28 06:10:51'),
(3, 1, 5, 'جزمة مقاس 65', '1200.00', 'expense', '', '2025-09-29', 'cleared', NULL, NULL, '2025-09-28 21:22:07', '2025-09-28 21:22:07'),
(4, 2, 3, 'قطة رومي', '100.00', 'expense', '', '2025-09-28', 'cleared', NULL, NULL, '2025-09-28 23:44:05', '2025-09-28 23:44:05'),
(5, 2, 11, 'حسنة كل شهر', '3000.00', 'income', '', '2025-09-29', 'cleared', NULL, NULL, '2025-09-28 23:44:44', '2025-09-28 23:44:44'),
(7, 2, 18, 'برفان مضروب', '2500.00', 'expense', '', '2025-09-29', 'cleared', NULL, NULL, '2025-09-29 00:08:51', '2025-09-29 00:08:51'),
(9, 2, 2, 'جوافة', '200.00', 'expense', '', '2025-09-29', 'cleared', NULL, NULL, '2025-09-29 00:10:05', '2025-09-29 00:10:05'),
(11, 2, 8, 'كيلو عنب', '90.00', 'expense', '', '2025-09-29', 'cleared', NULL, NULL, '2025-09-29 00:10:32', '2025-09-29 00:10:32'),
(12, 2, 20, 'بعت معزة', '800.00', 'income', '', '2025-09-04', 'cleared', NULL, NULL, '2025-09-29 00:12:11', '2025-09-29 00:12:11'),
(13, 2, 2, 'بطة صاحية', '500.00', 'expense', '', '2025-09-29', 'cleared', NULL, NULL, '2025-09-29 00:15:09', '2025-09-29 00:15:09'),
(14, 2, 2, 'ربع لانشون', '70.00', 'expense', '', '2025-09-23', 'cleared', NULL, NULL, '2025-09-29 00:15:41', '2025-09-29 00:15:41'),
(15, 2, 20, 'مكافأة', '1800.00', 'income', '', '2025-10-01', 'cleared', NULL, NULL, '2025-09-29 00:16:30', '2025-09-29 00:16:30'),
(16, 2, 7, 'جبنة', '100.00', 'expense', '', '2025-09-29', 'cleared', NULL, NULL, '2025-09-29 00:17:05', '2025-09-29 00:17:05'),
(17, 2, 1, 'asda', '12312.00', 'expense', '', '2025-09-16', 'cleared', NULL, NULL, '2025-09-29 00:18:10', '2025-09-29 00:18:10'),
(18, 2, 1, 'aaaaaaaaaa', '221.00', 'expense', '', '2025-09-23', 'cleared', NULL, NULL, '2025-09-29 00:19:31', '2025-09-29 00:19:31'),
(19, 2, 7, 'ffffffffffffff', '2232.00', 'expense', '', '2025-10-01', 'cleared', NULL, NULL, '2025-09-29 00:20:03', '2025-09-29 00:20:03');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin','super_admin') COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `avatar`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'Super Admin', 'admin@expenses.com', '$2y$10$O8IHh1TjjhiBqf/7mEutd.3AMIRIplef2MgYVoqrOeKSM6RoiC7q.', 'super_admin', NULL, '2025-09-28 01:03:42', '2025-09-28 01:20:51', 1),
(2, 'Mohammed Awad', 'mmm@mmm.com', '$2y$10$Gs1wSzsTiQpy7UpRn4S7FOJx0qcc.uZIunVG.ft5gJ3KcUOtazGyC', 'user', NULL, '2025-09-28 23:18:24', '2025-09-28 23:18:24', 1);

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `session_token`, `expires_at`, `created_at`) VALUES
(1, 1, '79eec881ce7e536553980a1c05f4c97834c00185623b7749da5e99e901d1f4c5', '2025-10-28 01:21:04', '2025-09-28 01:21:04'),
(2, 1, '0b32aec254bc39330006df198e47eab1e163ef1ac9c59ff7216e3fe6c3e754d7', '2025-10-28 01:30:01', '2025-09-28 01:30:01'),
(3, 1, '5bb12373f96a81b4468921ea3a6c04b4bc8022e1ae4d3589a69b4528803c923b', '2025-10-28 05:57:10', '2025-09-28 05:57:10'),
(4, 1, '04857fdd778dd571a2b88ac8592ee900e67fbc67363f17d911f12fd5b9fd2e81', '2025-10-28 21:06:23', '2025-09-28 21:06:23');

-- --------------------------------------------------------

--
-- Structure for view `budget_vs_actual`
--
DROP TABLE IF EXISTS `budget_vs_actual`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `budget_vs_actual`  AS SELECT `b`.`id` AS `budget_id`, `b`.`user_id` AS `user_id`, `b`.`amount` AS `budget_amount`, `c`.`name_ar` AS `category_name`, `c`.`color` AS `category_color`, `c`.`category_type` AS `category_type`, 'expense' AS `budget_type`, coalesce(sum(`t`.`amount`),0) AS `actual_amount`, ((coalesce(sum(`t`.`amount`),0) / `b`.`amount`) * 100) AS `percentage_used`, `b`.`period` AS `period`, `b`.`start_date` AS `start_date`, `b`.`end_date` AS `end_date` FROM ((`budgets` `b` left join `categories` `c` on((`b`.`category_id` = `c`.`id`))) left join `transactions` `t` on(((`b`.`category_id` = `t`.`category_id`) and (`b`.`user_id` = `t`.`user_id`) and (`t`.`transaction_date` between `b`.`start_date` and `b`.`end_date`) and (`t`.`status` = 'cleared') and (`t`.`transaction_type` = 'expense')))) GROUP BY `b`.`id` union all select `ib`.`id` AS `budget_id`,`ib`.`user_id` AS `user_id`,`ib`.`target_amount` AS `budget_amount`,`c`.`name_ar` AS `category_name`,`c`.`color` AS `category_color`,`c`.`category_type` AS `category_type`,'income' AS `budget_type`,coalesce(sum(`t`.`amount`),0) AS `actual_amount`,((coalesce(sum(`t`.`amount`),0) / `ib`.`target_amount`) * 100) AS `percentage_achieved`,`ib`.`period` AS `period`,`ib`.`start_date` AS `start_date`,`ib`.`end_date` AS `end_date` from ((`income_budgets` `ib` left join `categories` `c` on((`ib`.`category_id` = `c`.`id`))) left join `transactions` `t` on(((`ib`.`category_id` = `t`.`category_id`) and (`ib`.`user_id` = `t`.`user_id`) and (`t`.`transaction_date` between `ib`.`start_date` and `ib`.`end_date`) and (`t`.`status` = 'cleared') and (`t`.`transaction_type` = 'income')))) group by `ib`.`id`  ;

-- --------------------------------------------------------

--
-- Structure for view `income_expense_summary`
--
DROP TABLE IF EXISTS `income_expense_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `income_expense_summary`  AS SELECT `u`.`id` AS `user_id`, `u`.`name` AS `user_name`, year(`t`.`transaction_date`) AS `year`, month(`t`.`transaction_date`) AS `month`, sum((case when (`t`.`transaction_type` = 'income') then `t`.`amount` else 0 end)) AS `total_income`, sum((case when (`t`.`transaction_type` = 'expense') then `t`.`amount` else 0 end)) AS `total_expenses`, sum((case when (`t`.`transaction_type` = 'income') then `t`.`amount` else -(`t`.`amount`) end)) AS `net_income`, count((case when (`t`.`transaction_type` = 'income') then 1 end)) AS `income_count`, count((case when (`t`.`transaction_type` = 'expense') then 1 end)) AS `expense_count` FROM (`users` `u` left join `transactions` `t` on((`u`.`id` = `t`.`user_id`))) WHERE (`t`.`status` = 'cleared') GROUP BY `u`.`id`, year(`t`.`transaction_date`), month(`t`.`transaction_date`)  ;

-- --------------------------------------------------------

--
-- Structure for view `monthly_spending`
--
DROP TABLE IF EXISTS `monthly_spending`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `monthly_spending`  AS SELECT `u`.`id` AS `user_id`, `u`.`name` AS `user_name`, year(`t`.`transaction_date`) AS `year`, month(`t`.`transaction_date`) AS `month`, `c`.`name_ar` AS `category_name`, `c`.`color` AS `category_color`, `c`.`category_type` AS `category_type`, `t`.`transaction_type` AS `transaction_type`, sum(`t`.`amount`) AS `total_amount`, count(`t`.`id`) AS `transaction_count` FROM ((`users` `u` left join `transactions` `t` on((`u`.`id` = `t`.`user_id`))) left join `categories` `c` on((`t`.`category_id` = `c`.`id`))) WHERE (`t`.`status` = 'cleared') GROUP BY `u`.`id`, year(`t`.`transaction_date`), month(`t`.`transaction_date`), `c`.`id`, `t`.`transaction_type``transaction_type`  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `budgets`
--
ALTER TABLE `budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_budgets_user_period` (`user_id`,`period`,`start_date`,`end_date`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_categories_user` (`user_id`),
  ADD KEY `idx_categories_type` (`category_type`);

--
-- Indexes for table `income_budgets`
--
ALTER TABLE `income_budgets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `idx_income_budgets_user_period` (`user_id`,`period`,`start_date`,`end_date`);

--
-- Indexes for table `savings_goals`
--
ALTER TABLE `savings_goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_date` (`user_id`,`transaction_date`),
  ADD KEY `idx_category` (`category_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_transactions_user_date` (`user_id`,`transaction_date`),
  ADD KEY `idx_transactions_type_date` (`transaction_type`,`transaction_date`),
  ADD KEY `idx_transactions_user_type` (`user_id`,`transaction_type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `idx_token` (`session_token`),
  ADD KEY `idx_expires` (`expires_at`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `budgets`
--
ALTER TABLE `budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `income_budgets`
--
ALTER TABLE `income_budgets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `savings_goals`
--
ALTER TABLE `savings_goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `budgets`
--
ALTER TABLE `budgets`
  ADD CONSTRAINT `budgets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `budgets_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `categories`
--
ALTER TABLE `categories`
  ADD CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `income_budgets`
--
ALTER TABLE `income_budgets`
  ADD CONSTRAINT `income_budgets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `income_budgets_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `savings_goals`
--
ALTER TABLE `savings_goals`
  ADD CONSTRAINT `savings_goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
