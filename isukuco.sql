-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : ven. 25 juil. 2025 à 13:28
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `isukuco`
--

-- --------------------------------------------------------

--
-- Structure de la table `annonce_collecte`
--

CREATE TABLE `annonce_collecte` (
  `id_annonce` int(11) NOT NULL,
  `id_tournee` int(11) NOT NULL,
  `date_annonce` datetime DEFAULT current_timestamp(),
  `message` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `annonce_collecte`
--

INSERT INTO `annonce_collecte` (`id_annonce`, `id_tournee`, `date_annonce`, `message`) VALUES
(4, 5, '2025-06-14 01:43:24', 'la collecte est prevue le 25-06-2025.');

-- --------------------------------------------------------

--
-- Structure de la table `assignation_vehicule`
--

CREATE TABLE `assignation_vehicule` (
  `id_assignation_vehicule` int(11) NOT NULL,
  `id_chauffeur` varchar(100) NOT NULL,
  `immatriculation` varchar(50) NOT NULL,
  `date_assigned` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `statut` enum('actif','terminé') DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `assignation_vehicule`
--

INSERT INTO `assignation_vehicule` (`id_assignation_vehicule`, `id_chauffeur`, `immatriculation`, `date_assigned`, `date_fin`, `statut`) VALUES
(1, '122/132321/123.509373', 'C6789A', '2025-07-07', '2025-07-15', 'actif'),
(2, '122/132321/123.509373', 'C6789A', '2025-06-14', '2025-06-21', 'terminé'),
(3, '122/132321/123.5092343', 'B6789A', '2025-06-21', '2025-06-28', 'terminé');

-- --------------------------------------------------------

--
-- Structure de la table `attribution_zone`
--

CREATE TABLE `attribution_zone` (
  `id_attribution` int(11) NOT NULL,
  `id_utilisateur` varchar(100) NOT NULL,
  `id_zone` int(11) NOT NULL,
  `date_attribution` date NOT NULL,
  `date_fin` date DEFAULT NULL,
  `statut` enum('actif','terminé') DEFAULT 'actif'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `attribution_zone`
--

INSERT INTO `attribution_zone` (`id_attribution`, `id_utilisateur`, `id_zone`, `date_attribution`, `date_fin`, `statut`) VALUES
(1, '122/132321/123.509373', 6, '2025-06-07', '2025-07-10', 'terminé'),
(2, '122/132321/123.5023589', 4, '2025-06-28', '2025-07-26', 'actif'),
(3, '122/134567/9989', 2, '2025-06-21', '2025-06-28', 'terminé');

-- --------------------------------------------------------

--
-- Structure de la table `collecte`
--

CREATE TABLE `collecte` (
  `id_collecte` int(11) NOT NULL,
  `id_tournee` int(11) NOT NULL,
  `id_agent` varchar(100) NOT NULL,
  `id_client` varchar(100) NOT NULL,
  `date_collecte` datetime DEFAULT current_timestamp(),
  `statut` enum('prévue','effectuée','annulée') DEFAULT 'prévue',
  `signature_client` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `collecte`
--

INSERT INTO `collecte` (`id_collecte`, `id_tournee`, `id_agent`, `id_client`, `date_collecte`, `statut`, `signature_client`) VALUES
(14, 3, '122/132321/123.5023589', '122/132321/123.50989', '2025-06-19 11:45:12', 'effectuée', 'signatures/sig_1750326312_3999.png'),
(15, 4, '122/132321/123.5023589', '122/132321/123.509000', '2025-06-19 11:45:34', 'effectuée', 'signatures/sig_1750326334_1196.png'),
(16, 4, '122/132321/123.5023589', '122/132321/123.50989', '2025-06-19 11:46:04', 'effectuée', 'signatures/sig_1750326364_6988.png');

-- --------------------------------------------------------

--
-- Structure de la table `commentaire`
--

CREATE TABLE `commentaire` (
  `id_commentaire` int(11) NOT NULL,
  `nom_complet` varchar(50) NOT NULL,
  `objet` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `date_commentaire` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('valide','invalide') DEFAULT 'invalide'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `commentaire`
--

INSERT INTO `commentaire` (`id_commentaire`, `nom_complet`, `objet`, `message`, `date_commentaire`, `statut`) VALUES
(1, 'Audry Ndikumana', 'collecte', 'Ravis des services!', '2025-06-07 00:44:28', 'valide'),
(3, 'Miki Jarde', 'recours', 'Apres chaque collecte ils visent le meilleur.', '2025-06-07 00:45:31', 'invalide'),
(4, 'Miki Jarde', 'recours', 'Apres chaque, service ils le font soigneusement!', '2025-06-07 00:57:58', 'valide'),
(5, 'Yann Dushime', 'service', 'Service fiable!', '2025-06-07 10:46:07', 'valide'),
(6, 'Louna NDIKUMANA', 'service', 'Le service rendu elle est de bonne qualite!', '2025-06-20 14:20:28', 'invalide'),
(7, 'MUSIKAMI Frank', 'service', 'Les services sont rapides!', '2025-06-23 06:10:04', 'valide'),
(8, 'Kenny Kelvin', 'Remerciement', 'Merci pour vos services!', '2025-06-30 08:18:09', 'valide'),
(9, 'Nshima', 'Din', 'merci!', '2025-07-12 14:23:34', 'valide');

-- --------------------------------------------------------

--
-- Structure de la table `contrat`
--

CREATE TABLE `contrat` (
  `id_contrat` int(11) NOT NULL,
  `id_utilisateur` varchar(100) NOT NULL,
  `id_zone` int(11) NOT NULL,
  `type_contrat` enum('mensuel','trimestriel','annuel') NOT NULL,
  `statut` enum('attente','actif','expiré') DEFAULT 'attente',
  `justificatif` varchar(255) DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `contrat`
--

INSERT INTO `contrat` (`id_contrat`, `id_utilisateur`, `id_zone`, `type_contrat`, `statut`, `justificatif`, `date_creation`, `date_debut`, `date_fin`) VALUES
(1, '122/132321/123.50989', 3, 'trimestriel', 'actif', NULL, '2025-06-14 04:27:23', '2025-06-28', '2025-09-28'),
(2, '122/132321/123.509000', 3, 'mensuel', 'actif', NULL, '2025-06-19 11:43:00', '2025-06-19', '2025-07-19');

-- --------------------------------------------------------

--
-- Structure de la table `facturation`
--

CREATE TABLE `facturation` (
  `id_facture` int(11) NOT NULL,
  `id_contrat` int(11) NOT NULL,
  `id_annonce` int(11) NOT NULL,
  `id_moyen` int(11) DEFAULT NULL,
  `date_facture` datetime DEFAULT current_timestamp(),
  `montant` decimal(10,2) NOT NULL,
  `justificatif` varchar(255) DEFAULT NULL,
  `statut` enum('en attente','payée','annulée') DEFAULT 'en attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `facturation`
--

INSERT INTO `facturation` (`id_facture`, `id_contrat`, `id_annonce`, `id_moyen`, `date_facture`, `montant`, `justificatif`, `statut`) VALUES
(1, 1, 4, 3, '2025-06-14 00:00:00', 3000.00, 'justif_68555fbc53260.png', 'en attente'),
(3, 2, 4, 2, '2025-06-20 14:40:32', 5000.00, 'facture_68555c51ee09c.pdf', 'payée'),
(5, 2, 4, 3, '2025-06-22 19:52:38', 6000.00, NULL, 'en attente');

-- --------------------------------------------------------

--
-- Structure de la table `journal_connexion`
--

CREATE TABLE `journal_connexion` (
  `id_connexion` int(11) NOT NULL,
  `id_utilisateur` varchar(100) DEFAULT NULL,
  `role` varchar(20) DEFAULT NULL,
  `date_connexion` datetime DEFAULT current_timestamp(),
  `adresse_ip` varchar(45) DEFAULT NULL,
  `navigateur` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `journal_connexion`
--

INSERT INTO `journal_connexion` (`id_connexion`, `id_utilisateur`, `role`, `date_connexion`, `adresse_ip`, `navigateur`) VALUES
(1, '122/132321/989', 'client', '2025-06-19 22:00:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(2, '122/132321/989', 'client', '2025-06-19 22:10:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(3, '122/132321/989', 'client', '2025-06-19 22:13:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(4, '122/132321/989', 'client', '2025-06-19 22:15:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(5, '122/132321/989', 'client', '2025-06-19 22:16:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(6, '122/132321/989', 'client', '2025-06-19 22:17:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(7, '122/132321/989', 'client', '2025-06-19 22:31:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(8, '122/132321/989', 'client', '2025-06-19 22:36:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(9, '122/132321/989', 'client', '2025-06-19 22:36:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(10, '122/132321/989', 'client', '2025-06-19 22:38:07', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(11, '122/132321/989', 'client', '2025-06-19 22:42:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(12, '122/132321/989', 'client', '2025-06-19 22:43:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(13, '122/132321/989', 'client', '2025-06-19 22:49:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(14, '122/132321/989', 'client', '2025-06-19 22:50:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(15, '122/132321/989', 'client', '2025-06-19 22:50:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(16, '122/132321/989', 'client', '2025-06-19 22:51:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(17, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:11:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(18, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:12:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(19, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:15:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(20, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:15:35', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(21, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:15:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(22, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:16:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(23, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:16:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(24, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:16:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(25, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:17:30', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(26, '122/132321/123.5023589', 'agent', '2025-06-19 23:19:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(27, '122/132321/123.50989', 'client', '2025-06-19 23:20:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(28, '122/132321/123.509373', 'chauffeur', '2025-06-19 23:21:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(29, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:21:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(30, '122/132321/123.50989', 'client', '2025-06-19 23:22:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(31, '122/132321/123.50989', 'client', '2025-06-19 23:23:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(32, '122/132321/123.50989', 'client', '2025-06-19 23:23:28', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(33, '122/132321/123.50989', 'client', '2025-06-19 23:23:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(34, '122/132321/123.50989', 'client', '2025-06-19 23:24:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(35, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:25:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(36, '122/132321/123.509373', 'chauffeur', '2025-06-19 23:25:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(37, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:25:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(38, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:44:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(39, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:44:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(40, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:59:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(41, '122/132321/123.509389', 'coordinateur', '2025-06-19 23:59:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(42, '122/132321/123.50989', 'client', '2025-06-20 00:31:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(43, '122/132321/123.50989', 'client', '2025-06-20 00:31:12', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(44, '122/132321/123.509389', 'coordinateur', '2025-06-20 00:33:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(45, '122/132321/123.509389', 'coordinateur', '2025-06-20 00:33:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(46, '122/132321/123.509389', 'coordinateur', '2025-06-20 00:41:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(47, '122/132321/123.509389', 'coordinateur', '2025-06-20 00:41:13', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(48, '122/132321/123.509389', 'coordinateur', '2025-06-20 00:42:56', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(49, '122/132321/989', 'client', '2025-06-20 00:45:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(50, '122/132321/123.509389', 'coordinateur', '2025-06-20 09:15:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(51, '122/132321/123.50989', 'client', '2025-06-20 09:21:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(52, '122/132321/123.509373', 'chauffeur', '2025-06-20 09:22:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(53, '122/132321/123.5023589', 'agent', '2025-06-20 09:30:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(54, '122/132321/123.5023589', 'agent', '2025-06-20 09:34:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(55, '122/132321/123.509373', 'chauffeur', '2025-06-20 09:35:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(56, '122/132321/123.50989', 'client', '2025-06-20 09:35:42', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(57, '122/132321/123.50989', 'client', '2025-06-20 10:12:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(58, '122/132321/123.50989', 'client', '2025-06-20 10:12:26', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(59, '122/132321/123.509389', 'coordinateur', '2025-06-20 10:14:25', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(60, '122/132321/123.509389', 'coordinateur', '2025-06-20 10:33:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(61, '122/132321/123.5023589', 'agent', '2025-06-20 10:40:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(62, '122/132321/123.509389', 'coordinateur', '2025-06-20 10:44:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(63, '122/132321/123.509389', 'coordinateur', '2025-06-20 11:58:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(64, '122/132321/123.509389', 'coordinateur', '2025-06-20 13:56:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(65, '122/132321/123.509389', 'coordinateur', '2025-06-20 13:56:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(66, '122/132321/123.509389', 'coordinateur', '2025-06-20 14:03:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(67, '122/132321/123.509389', 'coordinateur', '2025-06-20 14:03:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(68, '122/132321/123.509389', 'coordinateur', '2025-06-20 14:14:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(69, '122/132321/123.509389', 'coordinateur', '2025-06-20 14:30:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(70, '122/132321/123.509389', 'coordinateur', '2025-06-20 14:30:29', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(71, '122/132321/123.509389', 'coordinateur', '2025-06-20 14:52:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(72, '122/132321/123.509389', 'coordinateur', '2025-06-20 14:52:09', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(73, '122/132321/123.50989', 'client', '2025-06-20 15:04:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(74, '122/132321/123.50989', 'client', '2025-06-20 15:17:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(75, '122/132321/123.50989', 'client', '2025-06-20 15:17:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(76, '122/132321/123.50989', 'client', '2025-06-20 15:21:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(77, '122/132321/123.5023589', 'agent', '2025-06-20 15:49:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(78, '122/132321/123.5023589', 'agent', '2025-06-20 15:50:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(79, '122/132321/123.5023589', 'agent', '2025-06-20 15:52:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(80, '122/132321/123.5023589', 'agent', '2025-06-20 15:52:17', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(81, '122/132321/123.5023589', 'agent', '2025-06-20 16:04:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(82, '122/132321/123.5023589', 'agent', '2025-06-20 16:05:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(83, '122/132321/123.509389', 'coordinateur', '2025-06-20 16:20:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(84, '122/132321/123.509389', 'coordinateur', '2025-06-20 16:21:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(85, '122/134567/9989', 'chauffeur', '2025-06-20 16:24:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(86, '122/132321/123.509389', 'coordinateur', '2025-06-20 16:24:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(87, '122/132321/123.509389', 'coordinateur', '2025-06-22 19:47:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(88, '122/132321/123.509373', 'chauffeur', '2025-06-22 19:53:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(89, '122/132321/123.5023589', 'agent', '2025-06-22 19:55:54', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(90, '122/132321/123.50989', 'client', '2025-06-22 19:57:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(91, '122/132321/123.509389', 'coordinateur', '2025-06-23 08:10:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(92, '122/132321/123.509389', 'coordinateur', '2025-06-23 08:10:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(93, '122/132321/123.509389', 'coordinateur', '2025-06-23 08:11:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(94, '122/132321/123.509389', 'coordinateur', '2025-06-23 08:16:32', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(95, '122/132321/123.509389', 'coordinateur', '2025-06-23 08:18:51', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(96, '122/132321/123.50989', 'client', '2025-06-23 08:23:02', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(97, '122/132321/123.5023589', 'agent', '2025-06-23 08:25:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(98, '122/132321/123.509373', 'chauffeur', '2025-06-23 08:27:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(99, '122/132321/123.509389', 'coordinateur', '2025-06-23 08:27:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(100, '122/132321/123.509373', 'chauffeur', '2025-06-23 08:28:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(101, '122/132321/123.509389', 'coordinateur', '2025-06-27 17:50:19', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(102, '122/132321/123.5023589', 'agent', '2025-06-27 17:55:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(103, '122/132321/123.509389', 'coordinateur', '2025-06-27 17:59:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(104, '122/132321/123.509389', 'coordinateur', '2025-06-30 10:18:23', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(105, '122/132321/123.509389', 'coordinateur', '2025-07-07 11:07:21', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(106, '122/132321/123.509389', 'coordinateur', '2025-07-07 11:08:03', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(107, '122/132321/989.867956', 'client', '2025-07-07 11:11:49', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(108, '122/132321/123.509389', 'coordinateur', '2025-07-07 11:13:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(109, '122/132321/123.509389', 'coordinateur', '2025-07-07 11:52:38', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(110, '122/132321/123.5023589', 'agent', '2025-07-07 11:58:45', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(111, '122/132321/123.509373', 'chauffeur', '2025-07-07 11:59:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(112, '122/132321/123.509389', 'coordinateur', '2025-07-07 11:59:48', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(113, '122/132321/123.509373', 'chauffeur', '2025-07-07 12:00:58', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(114, '122/132321/123.509389', 'coordinateur', '2025-07-09 13:51:57', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(115, '122/132321/123.509389', 'coordinateur', '2025-07-12 16:23:44', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(116, '122/132321/123.509389', 'coordinateur', '2025-07-12 16:23:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(117, '122/132321/123.509389', 'coordinateur', '2025-07-12 16:25:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(118, '122/132321/123.509389', 'coordinateur', '2025-07-12 16:26:43', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(119, '122/132321/123.5023589', 'agent', '2025-07-12 16:34:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(120, '122/132321/123.509373', 'chauffeur', '2025-07-12 16:36:08', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(121, '122/132321/123.509389', 'coordinateur', '2025-07-12 16:36:40', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(122, '122/132321/123.509373', 'chauffeur', '2025-07-12 16:37:05', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36'),
(123, '122/132321/123.509389', 'coordinateur', '2025-07-12 16:38:00', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Structure de la table `moyen_paiement`
--

CREATE TABLE `moyen_paiement` (
  `id_moyen` int(11) NOT NULL,
  `type` enum('espèces','mobile_money','banque','autre') NOT NULL,
  `nom_moyen` varchar(100) NOT NULL,
  `nom_compte` varchar(100) DEFAULT NULL,
  `nom_banque` varchar(100) DEFAULT NULL,
  `numero_compte` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `actif` tinyint(1) DEFAULT 1,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `moyen_paiement`
--

INSERT INTO `moyen_paiement` (`id_moyen`, `type`, `nom_moyen`, `nom_compte`, `nom_banque`, `numero_compte`, `description`, `actif`, `date_creation`) VALUES
(2, 'mobile_money', 'Lumicash', 'NAHIMANA John', '', '234267', '', 1, '2025-06-15 13:32:04'),
(3, 'banque', 'CRDB', 'BIU-EED', 'CRDB INYENYERI', '3839-28393-292', '', 1, '2025-06-15 14:06:30');

-- --------------------------------------------------------

--
-- Structure de la table `publication`
--

CREATE TABLE `publication` (
  `id_publication` int(11) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `date_publication` datetime DEFAULT current_timestamp(),
  `auteur` varchar(100) DEFAULT 'coordinateur'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `publication`
--

INSERT INTO `publication` (`id_publication`, `titre`, `contenu`, `date_publication`, `auteur`) VALUES
(2, 'Collecte', '♻️ Nouveau type de tri sélectif mis en place.', '2025-07-07 11:07:40', 'Chris Alick'),
(3, 'Collecte', '♻️ Nouveau type de tri sélectif mis en place.', '2025-07-07 11:08:12', 'Chris Alick'),
(4, 'Collecte', '♻️ Nouveau type de tri sélectif mis en place.', '2025-07-07 11:08:18', 'Chris Alick');

-- --------------------------------------------------------

--
-- Structure de la table `rapport`
--

CREATE TABLE `rapport` (
  `id_rapport` int(11) NOT NULL,
  `id_coordinateur` varchar(100) NOT NULL,
  `titre` varchar(255) NOT NULL,
  `contenu` text NOT NULL,
  `date_rapport` date NOT NULL DEFAULT curdate(),
  `fichier` varchar(255) DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `rapport`
--

INSERT INTO `rapport` (`id_rapport`, `id_coordinateur`, `titre`, `contenu`, `date_rapport`, `fichier`, `date_creation`) VALUES
(2, '122/132321/123.509389', 'Collecte', 'Ravis des services de l\'equipe du Nord!', '2025-06-28', NULL, '2025-06-07 23:16:22');

-- --------------------------------------------------------

--
-- Structure de la table `reclamation`
--

CREATE TABLE `reclamation` (
  `id_reclamation` int(11) NOT NULL,
  `id_client` varchar(100) NOT NULL,
  `objet` varchar(150) DEFAULT NULL,
  `message` text NOT NULL,
  `date_reclamation` timestamp NOT NULL DEFAULT current_timestamp(),
  `statut` enum('ouverte','traitement','résolue') DEFAULT 'traitement'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `reclamation`
--

INSERT INTO `reclamation` (`id_reclamation`, `id_client`, `objet`, `message`, `date_reclamation`, `statut`) VALUES
(1, '122/132321/123.50989', 'INSTALLATION APPROPRIES', 'INSTALLATION APPROPRIES des poubelles dans chaque menages', '2025-06-08 00:12:08', 'ouverte'),
(2, '122/132321/123.50989', 'collecte', 'toujours il y a des gens qui me derange lors de la collecte.', '2025-06-18 19:29:05', 'traitement'),
(3, '122/132321/123.50989', 'collecte', 'j\'aimerais que le tri se fasse en trois etapes.', '2025-06-18 20:47:13', 'résolue');

-- --------------------------------------------------------

--
-- Structure de la table `tournee`
--

CREATE TABLE `tournee` (
  `id_tournee` int(11) NOT NULL,
  `date_tournee` date NOT NULL,
  `id_chauffeur` varchar(100) NOT NULL,
  `id_zone` int(11) NOT NULL,
  `statut` enum('planifiée','annulée','terminée') NOT NULL DEFAULT 'planifiée',
  `commentaire` text DEFAULT NULL,
  `date_creation` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tournee`
--

INSERT INTO `tournee` (`id_tournee`, `date_tournee`, `id_chauffeur`, `id_zone`, `statut`, `commentaire`, `date_creation`) VALUES
(2, '2025-06-14', '122/132321/123.5092343', 2, 'terminée', '', '2025-06-08 04:24:18'),
(3, '2025-06-21', '122/132321/123.509373', 3, 'terminée', 'Tu dois etre a l\'heure!', '2025-06-14 00:52:51'),
(4, '2025-06-28', '122/132321/123.5092343', 2, 'terminée', 'Veuillez a etre au complet!\r\n', '2025-06-15 17:54:10'),
(5, '2025-06-28', '122/134567/9989', 6, 'planifiée', 'soit a l\'heure!', '2025-06-20 16:23:50');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id_utilisateur` varchar(100) NOT NULL,
  `nom` varchar(50) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `genre` varchar(4) NOT NULL,
  `pays` varchar(100) NOT NULL,
  `province` varchar(50) NOT NULL,
  `commune` varchar(50) NOT NULL,
  `quartier` varchar(50) NOT NULL,
  `avenue` varchar(60) NOT NULL,
  `telephone` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `role` enum('coordinateur','agent','chauffeur','client') NOT NULL,
  `password` varchar(255) DEFAULT NULL,
  `date_inscription` date NOT NULL DEFAULT current_timestamp(),
  `actif` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id_utilisateur`, `nom`, `prenom`, `genre`, `pays`, `province`, `commune`, `quartier`, `avenue`, `telephone`, `email`, `role`, `password`, `date_inscription`, `actif`) VALUES
('122/121/123.50989', 'chris', 'alick', 'M', 'Burundi', 'Bujumbura Mairie', 'Ntahangwa', 'carama', '13Av', '6613234', 'kiki@gmail.com', 'client', '$2y$10$Rf7e6.cyq1R2ePzQdNSJBeRY7BDeigJesuNObYqBdRnh5x5BexEXi', '2025-06-20', 1),
('122/132321/123.5023589', 'Kimber', 'Marquise', 'F', 'Burundi', 'Bujumbura Mairie', 'ntahangwa', 'Ngagara Q6', '13Av', '+257662892373', 'mquise@gmail.com', 'agent', '$2y$10$h3.3M73IDgryOoWUP8kXHuVnbMsF5afvnu.QoNc/Oa0hBy.EsFSNm', '2025-06-08', 1),
('122/132321/123.509000', 'roland', 'kwaay', 'M', 'Kinshasa', 'Bujumbura Mairie', 'Bujumbura', 'Ngagara', '12AV', '+25766289219', 'nshima@gmail.com', 'client', '12', '2025-05-23', 1),
('122/132321/123.509008', 'roland', 'kwaay', 'F', 'Burundi', 'Bujumbura Mairie', 'ntahangwa', 'Cibitoke', '13Av', '+25766289219', 'roland@gmail.com', 'client', '12345', '2025-05-19', 1),
('122/132321/123.5092343', 'roland', 'kwaay', 'F', 'Burundi', 'Bujumbura Mairie', 'Bujumbura', 'carama', '12AV', '78382923', 'nshimalick1@gmail.com', 'chauffeur', '$2y$10$3NkrsZ8DADTbf2PBLtcYEOB3jYjTFJTMtIe0vhVMM.LL1SYQE2IVi', '2025-06-07', 1),
('122/132321/123.509373', 'MINANI', 'Jean', 'M', 'Burundi', 'Bujumbura Mairie', 'Muha', 'Kinanira', '13av', '+25766289219', 'mjean@gmail.com', 'chauffeur', '$2y$10$rWNuYOzokfCzcI68xr..ZOdGYmBRCMsfkSSeX8zTtLShn5xesihNe', '2025-06-07', 1),
('122/132321/123.509389', 'Alick', 'Chris', 'M', 'Burundi', 'Bujumbura Mairie', 'Mutimbuzi', 'Maramvya', '13av', '+25675783490', 'cralz@gmail.com', 'coordinateur', '$2y$10$nUtby1mredtlji.XnkaNBOSci7MVhHEvN04/C2wuXRxEBaGt64Aj6', '2025-06-04', 1),
('122/132321/123.50989', 'chris', 'alick', 'M', 'Burundi', 'Bujumbura Mairie', 'Ntahangwa', 'Ngagara Q6', '...', '+25766289217', 'nshimalick@gmail.com', 'client', '$2y$10$YENiaNQ3ZOq8d7DRXuS9ou0KES5BVSvmDEsEVohPoKOlOZmCSlMjS', '2025-06-03', 1),
('122/132321/989', 'chris', 'alick', 'M', 'Burundi', 'Bujumbura Mairie', 'ntahangwa', 'carama', '12AV', '661362327', 'nshimy@gmail.com', 'client', '$2y$10$gTT2t3GE0vaN.DI00FGqO.oe3iyyMG4E5pKz4werBi3.6WP4ccpau', '2025-06-19', 1),
('122/132321/989.867956', 'Dani', 'UWA', 'F', 'Burundi', 'Bujumbura Mairie', 'Bujumbura', 'Ngagara Q6', '...', '3563728903', 'danie@gmail.com', 'client', '$2y$10$JBFxfgX9Mhoz/3fMVqQHG.rTHMSi3uqU0G.hHGuJCBYFvUyeOxkgC', '2025-07-07', 1),
('122/13232192343', 'chris', 'alick', 'M', 'Burundi', 'Bujumbura Mairie', 'ntahangwa', 'gahahe', '...', '66136218', 'nshika@gmail.com', 'client', '$2y$10$Qefiz1iE/oVmKkHJlshQseNhSosf4V6aLR7NU6lvEKi8SBtAzIora', '2025-06-20', 1),
('122/134567/9989', 'Kathia', 'Louange', 'F', 'Burundi', 'Bujumbura Mairie', 'Ntahangwa', 'Ngagara', '...', '67890123', 'kathy@gmail.com', 'chauffeur', '$2y$10$FH00ohmuSxTM2RrQAN1KxOinEDRgBStCCmartGMVmHf8ybEiH/bmG', '2025-06-20', 1),
('123/332/132/3.4335', 'chris', 'mwaba', 'M', 'Tanzanie', 'Bujumbura Mairie', 'Bujumbura', 'Ngagara', '12AV', '+256757834567', 'nshim@gmail.com', 'client', '12345', '2025-05-19', 1);

-- --------------------------------------------------------

--
-- Structure de la table `vehicule`
--

CREATE TABLE `vehicule` (
  `immatriculation` varchar(50) NOT NULL,
  `marque` varchar(100) NOT NULL,
  `modele` varchar(100) NOT NULL,
  `type` varchar(50) NOT NULL,
  `statut` enum('en service','en maintenance','hors service') DEFAULT 'hors service'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `vehicule`
--

INSERT INTO `vehicule` (`immatriculation`, `marque`, `modele`, `type`, `statut`) VALUES
('B6789A', 'toyota', 'Allion', 'Plaine', 'hors service'),
('C6789A', 'toyota', 'probox', 'tout terrain', 'en service');

-- --------------------------------------------------------

--
-- Structure de la table `zone`
--

CREATE TABLE `zone` (
  `id_zone` int(11) NOT NULL,
  `nom_quartier` varchar(100) NOT NULL,
  `commune` varchar(100) DEFAULT NULL,
  `province` varchar(100) DEFAULT NULL,
  `tarif_mensuel` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `zone`
--

INSERT INTO `zone` (`id_zone`, `nom_quartier`, `commune`, `province`, `tarif_mensuel`) VALUES
(2, 'ngagara', 'ntahagwa', 'bujumbura', 8000.00),
(3, 'Kinindo', 'Muha', 'Bujumbura Mairie', 10000.00),
(4, 'Kiriri', 'Muha', 'Bujumbura Mairie', 15000.00),
(6, 'Kinanira', 'Muha', 'Bujumbura Mairie', 9000.00);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `annonce_collecte`
--
ALTER TABLE `annonce_collecte`
  ADD PRIMARY KEY (`id_annonce`),
  ADD KEY `id_tournee` (`id_tournee`);

--
-- Index pour la table `assignation_vehicule`
--
ALTER TABLE `assignation_vehicule`
  ADD PRIMARY KEY (`id_assignation_vehicule`),
  ADD KEY `id_chauffeur` (`id_chauffeur`),
  ADD KEY `immatriculation` (`immatriculation`);

--
-- Index pour la table `attribution_zone`
--
ALTER TABLE `attribution_zone`
  ADD PRIMARY KEY (`id_attribution`),
  ADD KEY `fk_utilisateur_zone` (`id_utilisateur`),
  ADD KEY `fk_zone_utilisateur` (`id_zone`);

--
-- Index pour la table `collecte`
--
ALTER TABLE `collecte`
  ADD PRIMARY KEY (`id_collecte`),
  ADD KEY `id_tournee` (`id_tournee`),
  ADD KEY `id_agent` (`id_agent`),
  ADD KEY `id_client` (`id_client`);

--
-- Index pour la table `commentaire`
--
ALTER TABLE `commentaire`
  ADD PRIMARY KEY (`id_commentaire`);

--
-- Index pour la table `contrat`
--
ALTER TABLE `contrat`
  ADD PRIMARY KEY (`id_contrat`),
  ADD KEY `id_utilisateur` (`id_utilisateur`),
  ADD KEY `id_zone` (`id_zone`);

--
-- Index pour la table `facturation`
--
ALTER TABLE `facturation`
  ADD PRIMARY KEY (`id_facture`),
  ADD KEY `id_contrat` (`id_contrat`),
  ADD KEY `id_moyen` (`id_moyen`),
  ADD KEY `id_annonce` (`id_annonce`);

--
-- Index pour la table `journal_connexion`
--
ALTER TABLE `journal_connexion`
  ADD PRIMARY KEY (`id_connexion`);

--
-- Index pour la table `moyen_paiement`
--
ALTER TABLE `moyen_paiement`
  ADD PRIMARY KEY (`id_moyen`);

--
-- Index pour la table `publication`
--
ALTER TABLE `publication`
  ADD PRIMARY KEY (`id_publication`);

--
-- Index pour la table `rapport`
--
ALTER TABLE `rapport`
  ADD PRIMARY KEY (`id_rapport`),
  ADD KEY `id_coordinateur` (`id_coordinateur`);

--
-- Index pour la table `reclamation`
--
ALTER TABLE `reclamation`
  ADD PRIMARY KEY (`id_reclamation`),
  ADD KEY `id_client` (`id_client`);

--
-- Index pour la table `tournee`
--
ALTER TABLE `tournee`
  ADD PRIMARY KEY (`id_tournee`),
  ADD KEY `id_chauffeur` (`id_chauffeur`),
  ADD KEY `id_zone` (`id_zone`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id_utilisateur`),
  ADD UNIQUE KEY `gmail` (`email`);

--
-- Index pour la table `vehicule`
--
ALTER TABLE `vehicule`
  ADD PRIMARY KEY (`immatriculation`);

--
-- Index pour la table `zone`
--
ALTER TABLE `zone`
  ADD PRIMARY KEY (`id_zone`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `annonce_collecte`
--
ALTER TABLE `annonce_collecte`
  MODIFY `id_annonce` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `assignation_vehicule`
--
ALTER TABLE `assignation_vehicule`
  MODIFY `id_assignation_vehicule` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `attribution_zone`
--
ALTER TABLE `attribution_zone`
  MODIFY `id_attribution` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `collecte`
--
ALTER TABLE `collecte`
  MODIFY `id_collecte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT pour la table `commentaire`
--
ALTER TABLE `commentaire`
  MODIFY `id_commentaire` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `contrat`
--
ALTER TABLE `contrat`
  MODIFY `id_contrat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `facturation`
--
ALTER TABLE `facturation`
  MODIFY `id_facture` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `journal_connexion`
--
ALTER TABLE `journal_connexion`
  MODIFY `id_connexion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT pour la table `moyen_paiement`
--
ALTER TABLE `moyen_paiement`
  MODIFY `id_moyen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `publication`
--
ALTER TABLE `publication`
  MODIFY `id_publication` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `rapport`
--
ALTER TABLE `rapport`
  MODIFY `id_rapport` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `reclamation`
--
ALTER TABLE `reclamation`
  MODIFY `id_reclamation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `tournee`
--
ALTER TABLE `tournee`
  MODIFY `id_tournee` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `annonce_collecte`
--
ALTER TABLE `annonce_collecte`
  ADD CONSTRAINT `annonce_collecte_ibfk_1` FOREIGN KEY (`id_tournee`) REFERENCES `tournee` (`id_tournee`);

--
-- Contraintes pour la table `assignation_vehicule`
--
ALTER TABLE `assignation_vehicule`
  ADD CONSTRAINT `assignation_vehicule_ibfk_1` FOREIGN KEY (`id_chauffeur`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `assignation_vehicule_ibfk_2` FOREIGN KEY (`immatriculation`) REFERENCES `vehicule` (`immatriculation`);

--
-- Contraintes pour la table `attribution_zone`
--
ALTER TABLE `attribution_zone`
  ADD CONSTRAINT `fk_utilisateur_zone` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_zone_utilisateur` FOREIGN KEY (`id_zone`) REFERENCES `zone` (`id_zone`) ON DELETE CASCADE;

--
-- Contraintes pour la table `collecte`
--
ALTER TABLE `collecte`
  ADD CONSTRAINT `collecte_ibfk_1` FOREIGN KEY (`id_tournee`) REFERENCES `tournee` (`id_tournee`),
  ADD CONSTRAINT `collecte_ibfk_2` FOREIGN KEY (`id_agent`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `collecte_ibfk_3` FOREIGN KEY (`id_client`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `contrat`
--
ALTER TABLE `contrat`
  ADD CONSTRAINT `contrat_ibfk_1` FOREIGN KEY (`id_utilisateur`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `contrat_ibfk_2` FOREIGN KEY (`id_zone`) REFERENCES `zone` (`id_zone`);

--
-- Contraintes pour la table `facturation`
--
ALTER TABLE `facturation`
  ADD CONSTRAINT `facturation_ibfk_1` FOREIGN KEY (`id_contrat`) REFERENCES `contrat` (`id_contrat`) ON DELETE CASCADE,
  ADD CONSTRAINT `facturation_ibfk_2` FOREIGN KEY (`id_moyen`) REFERENCES `moyen_paiement` (`id_moyen`) ON DELETE SET NULL,
  ADD CONSTRAINT `facturation_ibfk_3` FOREIGN KEY (`id_annonce`) REFERENCES `annonce_collecte` (`id_annonce`) ON DELETE CASCADE;

--
-- Contraintes pour la table `rapport`
--
ALTER TABLE `rapport`
  ADD CONSTRAINT `rapport_ibfk_1` FOREIGN KEY (`id_coordinateur`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `reclamation`
--
ALTER TABLE `reclamation`
  ADD CONSTRAINT `reclamation_ibfk_1` FOREIGN KEY (`id_client`) REFERENCES `utilisateur` (`id_utilisateur`);

--
-- Contraintes pour la table `tournee`
--
ALTER TABLE `tournee`
  ADD CONSTRAINT `tournee_ibfk_1` FOREIGN KEY (`id_chauffeur`) REFERENCES `utilisateur` (`id_utilisateur`),
  ADD CONSTRAINT `tournee_ibfk_2` FOREIGN KEY (`id_zone`) REFERENCES `zone` (`id_zone`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
