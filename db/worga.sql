-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mer. 01 mai 2024 à 14:29
-- Version du serveur : 8.2.0
-- Version de PHP : 8.2.13

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `worga`
--
CREATE DATABASE IF NOT EXISTS `worga` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `worga`;

-- --------------------------------------------------------

--
-- Structure de la table `accounts`
--

DROP TABLE IF EXISTS `accounts`;
CREATE TABLE IF NOT EXISTS `accounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `estimates_total` decimal(10,2) NOT NULL,
  `invoices_total` decimal(10,2) NOT NULL,
  `receipts_total` decimal(10,2) NOT NULL,
  `rest_to_invoice` decimal(10,2) NOT NULL,
  `rest_to_cash` decimal(10,2) NOT NULL,
  `client_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `client_id` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `accounts`
--

INSERT INTO `accounts` (`id`, `estimates_total`, `invoices_total`, `receipts_total`, `rest_to_invoice`, `rest_to_cash`, `client_id`) VALUES
(1, 78000.00, 36000.00, 12000.00, 42000.00, 24000.00, 1);

-- --------------------------------------------------------

--
-- Structure de la table `clients`
--

DROP TABLE IF EXISTS `clients`;
CREATE TABLE IF NOT EXISTS `clients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `last_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` int NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `other` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `inserted_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_client_id_user` (`user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `clients`
--

INSERT INTO `clients` (`id`, `last_name`, `first_name`, `address`, `phone`, `email`, `other`, `inserted_at`, `updated_at`, `user_id`) VALUES
(1, 'Dupond', 'Jean', '1 rue Rivoli 99000 Ville', 623456789, 'jean.dupond@test.com', '', '2024-04-22 00:00:00', '2024-04-22 00:00:00', 1);

-- --------------------------------------------------------

--
-- Structure de la table `documents`
--

DROP TABLE IF EXISTS `documents`;
CREATE TABLE IF NOT EXISTS `documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `inserted_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `fin_trans_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_doc_id_user` (`user_id`),
  KEY `idx_doc_id_fin_trans` (`fin_trans_id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `documents`
--

INSERT INTO `documents` (`id`, `name`, `path`, `inserted_at`, `updated_at`, `fin_trans_id`, `user_id`) VALUES
(5, 'Fact acpt 1', 'financial-documents/act1/94e4cfbe8811df2d9bf1.pdf', '2024-04-30 17:41:28', '2024-04-30 17:41:28', 2, 1),
(8, 'Fact acpt 2', 'financial-documents/act1/df8a11540b56977c56aa.pdf', '2024-05-01 11:47:23', '2024-05-01 11:47:23', 9, 1);

-- --------------------------------------------------------

--
-- Structure de la table `financial_transactions`
--

DROP TABLE IF EXISTS `financial_transactions`;
CREATE TABLE IF NOT EXISTS `financial_transactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` enum('to_be_debited','debit','credit') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount_ex_vat` decimal(10,2) NOT NULL,
  `vat_rate` decimal(5,2) NOT NULL,
  `fin_trans_date` date NOT NULL,
  `inserted_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `account_id` int NOT NULL,
  `user_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_ft_id_user` (`user_id`),
  KEY `idx_ft_id_account` (`account_id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `financial_transactions`
--

INSERT INTO `financial_transactions` (`id`, `title`, `description`, `category`, `amount_ex_vat`, `vat_rate`, `fin_trans_date`, `inserted_at`, `updated_at`, `account_id`, `user_id`) VALUES
(1, 'Devis #1', 'Contruction terrasse', 'to_be_debited', 60000.00, 20.00, '2024-04-16', '2024-04-25 16:35:48', '2024-04-25 16:35:48', 1, 1),
(2, 'Fact acpt #1', 'Construction terrasse', 'debit', 10000.00, 20.00, '2024-04-21', '2024-04-25 16:35:48', '2024-04-25 16:35:48', 1, 1),
(3, 'Paiement', 'Solde pour fact #1', 'credit', 10000.00, 20.00, '2024-04-25', '2024-04-29 14:06:03', '2024-04-29 14:06:03', 1, 1),
(7, 'Devis #2', 'Travaux supplementaires', 'to_be_debited', 5000.00, 20.00, '2024-04-26', '2024-04-29 14:18:10', '2024-04-29 14:18:10', 1, 1),
(9, 'Fact acpt #2', 'Construction terrasse', 'debit', 15000.00, 20.00, '2024-04-27', '2024-04-29 14:24:08', '2024-04-29 14:24:08', 1, 1),
(15, 'Fact acpt #3', 'Construction terrasse', 'debit', 5000.00, 20.00, '2024-04-30', '2024-05-01 15:51:22', '2024-05-01 15:51:22', 1, 1);

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `login` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','editor','visitor') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `is_active` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `login` (`login`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `role`, `created_at`, `updated_at`, `is_active`) VALUES
(1, 'admin', '$argon2id$v=19$m=65536,t=2,p=1$ReP2U85vtpuVglT22qKSKg$jE9ExXAsUXsF+QCyzckM4P6Is/6zoUZgkFu0bxzuKbc', 'admin', '2024-03-24 00:00:00', '2024-04-16 00:00:00', 1),
(2, 'editor', '$argon2id$v=19$m=65536,t=2,p=1$IIHtPeTazpcLAmCL8DE8Ag$xxw9qahxXMYgykISk09MpfFDPh3hmr8bRagUFeDRkgo', 'editor', '2024-04-04 00:00:00', '2024-04-16 00:00:00', 1),
(3, 'visitor', '$argon2id$v=19$m=65536,t=2,p=1$MyMgsNzRP390IKbZ2C63gg$D2gbsnppFlFyjX19kCOzt7sp57g6XyCh97/Qrhq1NgI', 'visitor', '2024-04-16 00:00:00', '2024-04-16 00:00:00', 0);

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `accounts`
--
ALTER TABLE `accounts`
  ADD CONSTRAINT `idx_account_id_client` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `clients`
--
ALTER TABLE `clients`
  ADD CONSTRAINT `idx_client_id_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `documents`
--
ALTER TABLE `documents`
  ADD CONSTRAINT `idx_doc_id_fin_trans` FOREIGN KEY (`fin_trans_id`) REFERENCES `financial_transactions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `idx_doc_id_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Contraintes pour la table `financial_transactions`
--
ALTER TABLE `financial_transactions`
  ADD CONSTRAINT `idx_ft_id_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `idx_ft_id_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
