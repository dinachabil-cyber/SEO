-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Hôte : db:3306
-- Généré le : jeu. 26 mars 2026 à 15:01
-- Version du serveur : 11.8.5-MariaDB-ubu2404-log
-- Version de PHP : 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `db`
--

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

CREATE TABLE `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\\\Version20260303000002', '2026-03-03 13:27:59', 0),
('DoctrineMigrations\\Version20241220000001', '2026-03-13 10:32:40', 265),
('DoctrineMigrations\\Version20260302130122', '2026-03-02 13:57:35', 55),
('DoctrineMigrations\\Version20260302135300', '2026-03-02 13:57:35', 80),
('DoctrineMigrations\\Version20260302142700', '2026-03-02 14:53:13', 153),
('DoctrineMigrations\\Version20260302144400', '2026-03-02 14:56:10', 183),
('DoctrineMigrations\\Version20260303000001', '2026-03-03 11:22:32', 135),
('DoctrineMigrations\\Version20260303000002', '2026-03-11 14:22:56', 215),
('DoctrineMigrations\\Version20260303000003', '2026-03-11 14:24:14', 2),
('DoctrineMigrations\\Version20260306000004', '2026-03-11 14:24:14', 54),
('DoctrineMigrations\\Version20260310000005', '2026-03-10 14:01:11', 65),
('DoctrineMigrations\\Version20260311000006', '2026-03-11 14:24:14', 46),
('DoctrineMigrations\\Version20260312000007', '2026-03-12 15:43:52', 98),
('DoctrineMigrations\\Version20260313000001', '2026-03-13 15:55:04', 303),
('DoctrineMigrations\\Version20260313000010', '2026-03-13 16:21:34', 121),
('DoctrineMigrations\\Version20260313000011', '2026-03-13 16:21:34', 459),
('DoctrineMigrations\\Version20260316000001', '2026-03-16 09:26:50', 78),
('DoctrineMigrations\\Version20260316103000', '2026-03-16 14:00:20', 28),
('DoctrineMigrations\\Version20260316123000', '2026-03-16 14:00:56', 120),
('DoctrineMigrations\\Version20260316151300', '2026-03-16 15:20:42', 65),
('DoctrineMigrations\\Version20260317000000', '2026-03-17 11:59:07', 16),
('DoctrineMigrations\\Version20260317000001', '2026-03-17 11:59:07', 39),
('DoctrineMigrations\\Version20260317000002', '2026-03-17 11:59:07', 13);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

CREATE TABLE `messenger_messages` (
  `id` bigint(20) NOT NULL,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

-- --------------------------------------------------------

--
-- Structure de la table `page`
--

CREATE TABLE `page` (
  `id` int(11) NOT NULL,
  `site_id` int(11) NOT NULL,
  `h1` varchar(255) DEFAULT NULL,
  `meta_description` varchar(255) DEFAULT NULL,
  `slug` varchar(255) NOT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `is_published` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `google_ads` varchar(255) DEFAULT NULL,
  `google_analytics` varchar(255) DEFAULT NULL,
  `google_tag_manager` varchar(255) DEFAULT NULL,
  `google_ads_id` varchar(255) DEFAULT NULL,
  `google_analytics_id` varchar(255) DEFAULT NULL,
  `google_tag_manager_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Déchargement des données de la table `page`
--

INSERT INTO `page` (`id`, `site_id`, `h1`, `meta_description`, `slug`, `meta_title`, `is_published`, `created_at`, `updated_at`, `meta_keywords`, `google_ads`, `google_analytics`, `google_tag_manager`, `google_ads_id`, `google_analytics_id`, `google_tag_manager_id`) VALUES
(4, 9, 'Mentions légales', 'Consultez les mentions légales du site Assurance pour Taxi : informations sur l’éditeur, l’hébergeur, la protection des données et les conditions d’utilisation du site.', 'mentions-legales', 'Mentions légales | Assurance pour Taxi – Informations juridiques', 1, '2026-03-05 10:58:54.000000', '2026-03-09 09:23:23.000000', 'DIJ?A', 'AW-433555', 'G-4564565', 'GTM-W69S4T83', NULL, NULL, NULL),
(5, 9, 'Mentions légales', 'Consultez les mentions légales de notre site : informations sur l’éditeur, l’hébergement, la protection des données et les conditions d’utilisation conformément à la légi', 'mentions-legales-site', 'Mentions légales | Informations légales du site et éditeur', 1, '2026-03-09 15:52:00.000000', '2026-03-09 15:52:00.000000', 'mentions légales, informations légales, éditeur du site, hébergement web, protection des données, conditions d\'utilisation', NULL, NULL, NULL, NULL, NULL, NULL),
(6, 17, 'Mentions légales', 'qtConsultez les mentions légales du site : informations sur l’éditeur, l’hébergeur, la propriété intellectuelle, les données personnelles et les conditions d’utilisation.', 'mentions-lgales', 'Mentions légales | Informations éditeur, hébergeur et conditions du si', 1, '2026-03-24 13:14:47.000000', '2026-03-24 13:14:47.000000', 'Meta Keywords mentions légales, éditeur du site, hébergeur, propriété intellectuelle, données personnelles, conditions d’utilisation, informations légales', NULL, NULL, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `page_section`
--

CREATE TABLE `page_section` (
  `id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `type` varchar(20) NOT NULL,
  `position` int(11) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `reference_section_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `page_section`
--

INSERT INTO `page_section` (`id`, `page_id`, `type`, `position`, `data`, `created_at`, `updated_at`, `reference_section_id`, `name`) VALUES
(19, 4, 'header', 1, '{\"brandText\":\"Quelque soient vos ant\\u00e9c\\u00e9dents obtenez un devis d\'assurance pour Taxi en quelques clics\",\"logoUrl\":null,\"menuItems\":\"dukyuk\",\"ctaText\":\"zeaft\",\"ctaUrl\":\"rde\",\"backgroundColor\":\"#0008ff\",\"textColor\":\"#121212\",\"paddingTop\":\"100\",\"paddingBottom\":\"100\",\"marginTop\":\"10\",\"marginBottom\":\"10\",\"buttonBackgroundColor\":\"#000000\",\"buttonTextColor\":\"#201d1d\",\"buttonBorderColor\":\"#473e3e\",\"buttonBorderRadius\":\"1000\",\"buttonStyle\":\"primary\"}', '2026-03-06 09:23:22', '2026-03-26 10:05:40', NULL, ''),
(23, 4, 'footer', 6, '{\"company_name\":null,\"company_description\":null,\"useful_links\":null,\"address\":\"10 rue de Penthi\\u00e8vre\\n75008 Paris\",\"phone\":\"01.82.83.48.00\",\"email\":\"contact@aksam-assurances.fr\",\"copyright_text\":null,\"ctaText\":null,\"ctaUrl\":null,\"background_color\":\"#ff4dcf\",\"text_color\":\"#ffffff\",\"heading_color\":\"#ffffff\",\"link_color\":\"#b8b8b8\",\"link_hover_color\":\"#4a90e2\",\"border_top_color\":\"#333333\",\"padding_top\":\"60\",\"padding_bottom\":\"40\",\"margin_top\":\"0\",\"margin_bottom\":\"0\",\"title_font_size\":\"18\",\"text_font_size\":\"14\",\"link_font_size\":\"14\",\"text_alignment\":\"left\",\"column_gap\":\"30\",\"columns_count\":3,\"border_radius\":\"8\",\"box_shadow\":\"none\",\"container_width\":\"1140px\",\"layout_type\":\"classic\",\"stack_on_mobile\":true,\"show_divider\":true}', '2026-03-06 09:24:07', '2026-03-26 11:04:27', NULL, ''),
(28, 4, 'cards_premium', 4, '{\"sectionTitle\":\"Assurance Taxi \\u2013 Devis rapide et sans engagement\",\"cards\":[{\"title\":\"Artisans & BTP\",\"description\":\"R\\u00e9sili\\u00e9 ou maluss\\u00e9.\\nSinistres r\\u00e9cents.\\nNouveau chauffeur ou reprise d\'activit\\u00e9.\\nPermis suspendu ou ant\\u00e9c\\u00e9dents d\'assurance.\",\"imageUrl\":null,\"linkUrl\":null},{\"title\":\"Commerciaux & Prof. Lib\\u00e9rales\",\"description\":\"Utilitaires , fourgons, camionnettes bennes... Vos outils de chantier sont entre de bonnes mains.\",\"imageUrl\":null,\"linkUrl\":null},{\"title\":\"Transport & Logistique\",\"description\":\"Poids lourds, remorques, v\\u00e9hicules frigorifiques... Assurez la continuit\\u00e9 de votre cha\\u00eene logistique.\",\"imageUrl\":null,\"linkUrl\":null},{\"title\":\"Service \\u00e0 la Personne & Sant\\u00e9\",\"description\":\"V\\u00e9hicules l\\u00e9gers pour les visites \\u00e0 domicile, ambulances... Votre mobilit\\u00e9 est notre priorit\\u00e9.\",\"imageUrl\":null,\"linkUrl\":null}],\"cardLayout\":\"grid-4\",\"cardStyle\":\"shadowed\",\"textAlignment\":\"center\",\"backgroundColor\":\"#ffffff\",\"textColor\":\"#000000\",\"titleColor\":\"#fbff00\",\"subtitleColor\":\"#ee7777\",\"cardBackgroundColor\":\"#ffffff\",\"cardTitleColor\":\"#fbff0a\",\"cardTextColor\":\"#030303\",\"cardBorderColor\":\"#000000\",\"cardBorderRadius\":null,\"paddingTop\":null,\"paddingBottom\":null,\"marginTop\":null,\"marginBottom\":null,\"buttonBackgroundColor\":\"#ffea00\",\"buttonTextColor\":\"#000000\",\"buttonBorderColor\":\"#000000\",\"buttonBorderRadius\":null,\"buttonStyle\":\"primary\",\"cardShadow\":false}', '2026-03-09 15:22:50', '2026-03-18 11:11:05', NULL, ''),
(30, 4, 'cta', 5, '{\"title\":\"Devis assurances taxi\",\"text\":\"Pour obtenir un devis d\'assurance taxi, il vous suffit de compl\\u00e9ter le formulaire en haut de page, c\'est simple et rapide. Cela vous permettra d\'effectuer en ligne un comparatif de diff\\u00e9rentes compagnies d\'assurance proposant des garanties sur mesure pour les chauffeurs taxi.\",\"buttonText\":\"Demandez votre devis gratuit en ligne\",\"buttonUrl\":\"https:\\/\\/assurance-pour-taxi.fr\\/#contactForm\",\"backgroundColor\":\"#f5c000\",\"textColor\":\"#000000\",\"titleColor\":\"#000000\",\"textAlignment\":\"center\",\"paddingTop\":null,\"paddingBottom\":null,\"marginTop\":null,\"marginBottom\":null,\"buttonBackgroundColor\":\"#000000\",\"buttonTextColor\":\"#ffdd00\",\"buttonBorderColor\":\"#000000\",\"buttonBorderRadius\":null,\"buttonStyle\":\"primary\"}', '2026-03-10 09:18:50', '2026-03-18 11:10:51', NULL, ''),
(34, 5, 'header', 1, '{\"brandText\":\"Quelque soient vos ant\\u00e9c\\u00e9dents obtenez un devis d\'assurance pour Taxi en quelques clics\",\"logoUrl\":null,\"menuItems\":null,\"ctaText\":\"Conseil personnalis\\u00e9 01 82 83 48 00\",\"ctaUrl\":\"rde\",\"backgroundColor\":\"#faf200\",\"textColor\":\"#000000\",\"paddingTop\":null,\"paddingBottom\":null,\"marginTop\":null,\"marginBottom\":null,\"buttonBackgroundColor\":\"#000000\",\"buttonTextColor\":\"#000000\",\"buttonBorderColor\":\"#000000\",\"buttonBorderRadius\":null,\"buttonStyle\":\"primary\"}', '2026-03-11 09:33:36', '2026-03-18 10:32:38', NULL, 'Section Header (Reference)'),
(35, 5, 'footer', 6, '{\"text\":null,\"links\":null,\"phone\":\"01.82.83.48.00\",\"email\":\"contact@aksam-assurances.fr\",\"style\":{\"sticky\":false,\"variant\":\"dark\",\"background\":\"#f3e012\",\"textColor\":\"#ffffff\",\"backgroundVariant\":\"surface\",\"layout\":\"left\",\"columns\":3,\"cardVariant\":\"solid\",\"accentColor\":\"primary\",\"buttonColor\":\"primary\",\"buttonStyle\":\"primary\",\"borderColor\":\"\",\"shadow\":false,\"rounded\":false,\"maxWidth\":\"normal\",\"textAlign\":\"left\",\"align\":\"center\",\"accordionVariant\":\"default\"},\"brandName\":\"Aksam Assurance\",\"description\":\"Entreprise soumise au contr\\u00f4le de l\'ACPR\",\"usefulLinks\":[],\"address\":\"10 rue de Penthi\\u00e8vre\\n75008 Paris\",\"copyright\":null}', '2026-03-11 09:33:43', '2026-03-24 16:07:49', NULL, 'Section Footer (Reference)'),
(41, 4, 'hero', 3, '{\"hero_title\":\"Compl\\u00e9tez ce formulaire pour obtenir un tarif\",\"badge_text\":null,\"hero_subtitle\":null,\"description\":null,\"primary_button_text\":null,\"primary_button_url\":null,\"secondary_button_text\":null,\"secondary_button_url\":null,\"show_image\":true,\"hero_image_url\":\"https:\\/\\/assurance-pour-taxi.fr\\/public\\/images\\/assurance-taxi.png\",\"mobile_image_url\":\"<https:\\/\\/assurance-pour-taxi.fr\\/public\\/images\\/assurance-taxi.png\",\"image_alt_text\":\"https:\\/\\/assurance-pour-taxi.fr\\/public\\/images\\/assurance-taxi.png\",\"layout_type\":\"text_left_image_right\",\"text_alignment\":\"center\",\"content_width\":\"medium\",\"section_height\":\"medium\",\"vertical_alignment\":\"center\",\"column_gap\":\"30\",\"background_color\":\"#f5f5f5\",\"background_gradient\":null,\"hero_text_color\":\"#000000\",\"title_color\":\"#000000\",\"subtitle_color\":\"#000000\",\"description_color\":\"#000000\",\"card_background_color\":\"#000000\",\"border_radius\":null,\"padding_top\":\"60\",\"padding_bottom\":\"60\",\"margin_top\":null,\"margin_bottom\":null,\"primary_button_background_color\":\"#000000\",\"primary_button_text_color\":\"#000000\",\"primary_button_border_color\":\"#000000\",\"primary_button_border_radius\":null,\"primary_button_style\":\"primary\",\"secondary_button_background_color\":\"#000000\",\"secondary_button_text_color\":\"#000000\",\"secondary_button_border_color\":\"#000000\",\"secondary_button_border_radius\":null,\"secondary_button_style\":\"outline\",\"title\":\"\",\"subtitle\":\"\",\"ctaText\":\"\",\"ctaUrl\":\"\",\"imageUrl\":\"\",\"showForm\":\"\",\"box_shadow\":false,\"hero_fields\":[]}', '2026-03-18 11:10:27', '2026-03-24 16:13:56', NULL, ''),
(42, 5, 'form', 4, '{\"section_title\":null,\"section_subtitle\":null,\"form_title\":\"7k;p\",\"form_description\":null,\"submit_button_text\":\"Submit\",\"success_message\":\"Thank you! Your message has been sent.\",\"form_type\":\"contact\",\"form_key\":null,\"redirect_url_after_submit\":null,\"form_layout\":\"centered\",\"form_width\":\"medium\",\"form_alignment\":\"center\",\"side_image_url\":null,\"image_alt_text\":null,\"section_background_color\":\"#000000\",\"form_card_background_color\":\"#000000\",\"title_color\":\"#000000\",\"subtitle_color\":\"#000000\",\"label_color\":\"#000000\",\"input_background_color\":\"#000000\",\"input_text_color\":\"#000000\",\"input_border_color\":\"#000000\",\"input_border_radius\":null,\"button_background_color\":\"#000000\",\"button_text_color\":\"#000000\",\"button_border_color\":\"#000000\",\"button_border_radius\":null,\"padding_top\":\"60\",\"padding_bottom\":\"60\",\"margin_top\":null,\"margin_bottom\":null,\"title\":\"\",\"submitText\":\"\",\"successMessage\":\"\",\"fields\":\"\",\"form_fields\":[],\"show_name_field\":false,\"show_email_field\":false,\"show_phone_field\":false,\"show_message_field\":false,\"show_company_field\":false,\"show_checkbox_consent\":false,\"store_submissions\":false,\"show_icon_above_title\":false,\"show_image\":false,\"box_shadow\":false}', '2026-03-19 09:42:32', '2026-03-26 08:44:43', NULL, ''),
(43, 5, 'cards_premium', 5, '{\"sectionTitle\":\"Assurance Taxi \\u2013 Devis rapide et sans engagement\",\"cardLayout\":\"grid-3\",\"cardStyle\":\"standard\",\"textAlignment\":\"center\",\"backgroundColor\":\"#000000\",\"textColor\":\"#000000\",\"titleColor\":\"#000000\",\"subtitleColor\":\"#000000\",\"cardBackgroundColor\":\"#000000\",\"cardTitleColor\":\"#000000\",\"cardTextColor\":\"#000000\",\"cardBorderColor\":\"#000000\",\"cardBorderRadius\":null,\"paddingTop\":null,\"paddingBottom\":null,\"marginTop\":null,\"marginBottom\":null,\"buttonBackgroundColor\":\"#000000\",\"buttonTextColor\":\"#000000\",\"buttonBorderColor\":\"#000000\",\"buttonBorderRadius\":null,\"buttonStyle\":\"primary\",\"cards\":[],\"cardShadow\":false}', '2026-03-19 15:05:48', '2026-03-24 16:07:49', NULL, ''),
(44, 6, 'hero', 0, '{\"hero_title\":null,\"hero_subtitle\":null,\"badge_text\":null,\"description\":null,\"primary_button_text\":null,\"primary_button_url\":null,\"secondary_button_text\":null,\"secondary_button_url\":null,\"hero_image_url\":null,\"mobile_image_url\":null,\"image_alt_text\":null,\"show_image\":false,\"layout_type\":null,\"text_alignment\":null,\"content_width\":null,\"section_height\":null,\"vertical_alignment\":null,\"column_gap\":null,\"background_color\":null,\"background_gradient\":null,\"hero_text_color\":null,\"title_color\":null,\"subtitle_color\":null,\"description_color\":null,\"card_background_color\":null,\"border_radius\":null,\"box_shadow\":false,\"padding_top\":null,\"padding_bottom\":null,\"margin_top\":null,\"margin_bottom\":null,\"primary_button_background_color\":null,\"primary_button_text_color\":null,\"primary_button_border_color\":null,\"primary_button_border_radius\":null,\"primary_button_style\":null,\"secondary_button_background_color\":null,\"secondary_button_text_color\":null,\"secondary_button_border_color\":null,\"secondary_button_border_radius\":null,\"secondary_button_style\":null}', '2026-03-24 13:15:20', '2026-03-24 13:15:20', NULL, ''),
(48, 4, 'form', 8, '{\"section_title\":null,\"section_subtitle\":null,\"form_title\":null,\"form_description\":null,\"submit_button_text\":null,\"success_message\":null,\"form_type\":null,\"form_key\":null,\"show_name_field\":false,\"show_email_field\":false,\"show_phone_field\":false,\"show_message_field\":false,\"show_company_field\":false,\"show_checkbox_consent\":false,\"redirect_url_after_submit\":null,\"store_submissions\":false,\"form_layout\":null,\"form_width\":null,\"form_alignment\":null,\"show_icon_above_title\":false,\"side_image_url\":null,\"image_alt_text\":null,\"show_image\":false,\"section_background_color\":null,\"form_card_background_color\":null,\"title_color\":null,\"subtitle_color\":null,\"label_color\":null,\"input_background_color\":null,\"input_text_color\":null,\"input_border_color\":null,\"input_border_radius\":null,\"button_background_color\":null,\"button_text_color\":null,\"button_border_color\":null,\"button_border_radius\":null,\"padding_top\":null,\"padding_bottom\":null,\"margin_top\":null,\"margin_bottom\":null,\"box_shadow\":false}', '2026-03-26 09:52:52', '2026-03-26 09:52:52', NULL, ''),
(49, 4, 'cards', 9, '{\"sectionTitle\":\"qrbhfg\",\"cardLayout\":\"grid-3\",\"cardStyle\":\"standard\",\"textAlignment\":\"center\",\"backgroundColor\":\"#000000\",\"textColor\":\"#000000\",\"titleColor\":\"#000000\",\"subtitleColor\":\"#000000\",\"cardBackgroundColor\":\"#000000\",\"cardTitleColor\":\"#000000\",\"cardTextColor\":\"#000000\",\"cardBorderColor\":\"#000000\",\"cardBorderRadius\":null,\"paddingTop\":null,\"paddingBottom\":null,\"marginTop\":null,\"marginBottom\":null,\"buttonBackgroundColor\":\"#000000\",\"buttonTextColor\":\"#000000\",\"buttonBorderColor\":\"#000000\",\"buttonBorderRadius\":null,\"buttonStyle\":\"primary\",\"cards\":[],\"cardShadow\":false}', '2026-03-26 10:08:10', '2026-03-26 10:08:21', NULL, '');

-- --------------------------------------------------------

--
-- Structure de la table `password_reset_request`
--

CREATE TABLE `password_reset_request` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `processed_by_id` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL,
  `requested_at` datetime NOT NULL,
  `processed_at` datetime DEFAULT NULL,
  `admin_note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `password_reset_request`
--

INSERT INTO `password_reset_request` (`id`, `user_id`, `processed_by_id`, `status`, `requested_at`, `processed_at`, `admin_note`) VALUES
(1, 14, NULL, 'pending', '2026-03-19 11:05:45', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `reference_section`
--

CREATE TABLE `reference_section` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` varchar(255) NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`)),
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reference_section`
--

INSERT INTO `reference_section` (`id`, `name`, `type`, `data`, `created_at`, `updated_at`) VALUES
(1, 'Test Reference Section', 'body', '{\"content\":\"This is a test reference section\"}', '2026-03-03 14:29:26', '2026-03-03 14:29:26'),
(3, 'Section Header (Reference)', 'header', '{\"brandText\":\"Quelque soient vos ant\\u00e9c\\u00e9dents obtenez un devis d\'assurance pour Taxi en quelques clics\",\"logoUrl\":null,\"menuItems\":[],\"ctaText\":\"Conseil personnalis\\u00e9 01 82 83 48 00\",\"ctaUrl\":\"rde\",\"style\":{\"variant\":\"light\",\"sticky\":true,\"background\":\"#f5ed0a\"}}', '2026-03-10 14:29:22', '2026-03-10 14:29:22'),
(4, 'Section Hero_split (Reference)', 'hero_split', '{\"title\":\"Compl\\u00e9tez ce formulaire pour obtenir un tarif\",\"subtitle\":\"sf\",\"imageUrl\":\"https:\\/\\/assurance-pour-taxi.fr\\/public\\/images\\/assurance-taxi.png\",\"imageAlt\":\"image\",\"form\":{\"title\":\"Compl\\u00e9tez ce formulaire pour obtenir un tarif\",\"submitText\":\"Comparer maintenant\",\"consentText\":\"\",\"successMessage\":\"\",\"fields\":[{\"label\":\"tdfh\",\"name\":\"gsxeg\",\"type\":\"text\",\"required\":true,\"placeholder\":\"rfgbsh\",\"options\":\"shbgcn\",\"width\":\"full\"},{\"label\":\"trgjh\",\"name\":\"strynjhs\",\"type\":\"email\",\"required\":true,\"placeholder\":\"dryh\",\"options\":\"rydj,\",\"width\":\"full\"}]},\"style\":{\"backgroundVariant\":\"light\",\"layout\":\"image-left-form-right\"}}', '2026-03-10 14:36:44', '2026-03-10 14:36:44'),
(5, 'Section Footer (Reference)', 'footer', '{\"text\":null,\"links\":null,\"phone\":\"01.82.83.48.00\",\"email\":\"contact@aksam-assurances.fr\",\"style\":{\"variant\":\"dark\",\"background\":\"#f3e012\",\"textColor\":\"#ffffff\"},\"brandName\":\"Aksam Assurance\",\"description\":\"Entreprise soumise au contr\\u00f4le de l\'ACPR\",\"usefulLinks\":[],\"address\":\"10 rue de Penthi\\u00e8vre\\n75008 Paris\",\"copyright\":null}', '2026-03-10 15:02:24', '2026-03-10 15:02:24'),
(6, 'Section Cards (Reference)', 'cards', '{\"sectionTitle\":\"Assurance Taxi \\u2013 Devis rapide et sans engagement\",\"cards\":[{\"title\":\"Chauffeurs taxi avec ant\\u00e9c\\u00e9dents :\",\"description\":\"R\\u00e9sili\\u00e9 ou maluss\\u00e9.\\nSinistres r\\u00e9cents.\\nNouveau chauffeur ou reprise d\'activit\\u00e9.\\nPermis suspendu ou ant\\u00e9c\\u00e9dents d\'assurance.\",\"icon\":\"fd\",\"iconEmoji\":null,\"imageUrl\":\"sfdq\",\"linkUrl\":\"qsfqfd\",\"buttonText\":\"sdf\",\"badge\":\"qdgv\"},{\"title\":\"frgvq\",\"description\":\"gv\",\"icon\":\"fr\",\"iconEmoji\":null,\"imageUrl\":\"f\",\"linkUrl\":\"vgq\",\"buttonText\":\"fr\",\"badge\":\"efg\"},{\"title\":\"gf\",\"description\":\"rfde\",\"icon\":null,\"iconEmoji\":null,\"imageUrl\":\"refv\",\"linkUrl\":\"fv\",\"buttonText\":\"qre\",\"badge\":\"qgf\"},{\"title\":\"zefc\",\"description\":\"erfvgd\",\"icon\":\"qervg\",\"iconEmoji\":null,\"imageUrl\":null,\"linkUrl\":null,\"buttonText\":null,\"badge\":null}],\"style\":{\"background\":\"#ffffff\",\"textColor\":\"#0b0b09\",\"columns\":4,\"cardVariant\":\"glass\",\"buttonColor\":\"primary\",\"buttonCustomColor\":\"#fff700\",\"borderColor\":\"#000000\",\"shadow\":true,\"rounded\":true}}', '2026-03-10 15:30:23', '2026-03-10 15:30:23'),
(7, 'Section Cta (Reference)', 'cta', '{\"title\":\"Devis assurances taxi\",\"text\":\"Pour obtenir un devis d\'assurance taxi, il vous suffit de compl\\u00e9ter le formulaire en haut de page, c\'est simple et rapide. Cela vous permettra d\'effectuer en ligne un comparatif de diff\\u00e9rentes compagnies d\'assurance proposant des garanties sur mesure pour les chauffeurs taxi.\",\"icon\":null,\"iconEmoji\":null,\"buttonText\":\"Demandez votre devis gratuit en ligne\",\"buttonUrl\":\"https:\\/\\/assurance-pour-taxi.fr\\/#contactForm\",\"style\":{\"background\":\"#fbff00\",\"textColor\":\"#000000\",\"backgroundVariant\":\"surface\",\"buttonColor\":\"danger\",\"buttonCustomColor\":\"#292424\"}}', '2026-03-10 15:47:10', '2026-03-10 15:47:10'),
(8, 'Section Header (Reference)', 'header', '{\"brandText\":\"Quelque soient vos ant\\u00e9c\\u00e9dents obtenez un devis d\'assurance pour Taxi en quelques clics\",\"logoUrl\":null,\"menuItems\":[],\"ctaText\":\"Conseil personnalis\\u00e9 01 82 83 48 00\",\"ctaUrl\":\"rde\",\"style\":{\"variant\":\"light\",\"sticky\":true,\"background\":\"#f5ed0a\"}}', '2026-03-11 09:32:59', '2026-03-11 09:32:59');

-- --------------------------------------------------------

--
-- Structure de la table `site`
--

CREATE TABLE `site` (
  `id` int(11) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `default_locale` varchar(5) NOT NULL DEFAULT 'fr',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(6) NOT NULL,
  `updated_at` datetime(6) NOT NULL,
  `hosting` varchar(255) DEFAULT 'NULL',
  `database_name` varchar(255) DEFAULT NULL,
  `database_password` varchar(255) DEFAULT NULL,
  `technology` varchar(255) DEFAULT NULL,
  `published_at` datetime DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `status` varchar(255) DEFAULT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `address` varchar(500) DEFAULT NULL,
  `phone` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `legal_representative` varchar(255) DEFAULT NULL,
  `registration_number` varchar(255) DEFAULT NULL,
  `page_count` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_520_ci;

--
-- Déchargement des données de la table `site`
--

INSERT INTO `site` (`id`, `domain`, `default_locale`, `is_active`, `created_at`, `updated_at`, `hosting`, `database_name`, `database_password`, `technology`, `published_at`, `owner_id`, `status`, `company_name`, `address`, `phone`, `email`, `legal_representative`, `registration_number`, `page_count`) VALUES
(9, 'https://assurance-pour-taxi.fr', 'fr', 1, '2026-03-05 10:17:32.000000', '2026-03-17 11:03:19.000000', 'TEST', 'test', 'qOgBesMAcQWrPn47UkvabU1BMjVVQXo1Qmc2Wlh1cXpTWGVrL0E9PQ==', 'symfony', '2018-06-22 17:52:00', NULL, 'Published', 'AKSAM', 'rabat', '4535633', 'dina.chabil@aksam-assurances.ma', 'YGJUYG', '45634563', 2),
(10, 'https://assurance-vehicule-pro.fr', 'fr', 1, '2026-03-05 15:52:18.000000', '2026-03-05 15:59:25.000000', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(11, 'test.com', 'fr', 1, '2026-03-12 11:09:08.000000', '2026-03-12 11:09:08.000000', 'adz', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(12, 'test-site.com', 'fr', 1, '2026-03-13 10:09:47.000000', '2026-03-13 10:09:47.000000', 'VPS-01', 'db-testing', '$2y$12$HwD1moOa31LwT.TrL2kaCOC6bO9b7CbENsE.GpPWKxbTeMBn3TFwq', 'WordPress', '2026-03-13 10:00:00', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0),
(16, 'TESTTTTTTTTTT.com', 'fr', 1, '2026-03-17 12:00:04.000000', '2026-03-17 12:00:04.000000', 'fdbg', 'dfhtg', 'nhIGtrFsVMB+nbhHnW0O1HNuZ2Y2S2JjUEhEQWVQY3VjMmw0REE9PQ==', 'dfhb', NULL, 6, 'Draft', 'fgh', 'rabat', '123456789', 'dina.chabil@aksam-assurances.ma', 'YGJUYG', '123456789', 0),
(17, 'kia.com', 'fr', 1, '2026-03-17 14:33:30.000000', '2026-03-24 13:14:47.000000', 'kia', 'kia', 'Et4+RNu5ZcYq9xybzw4XSi9nYmVJRmc4UXgzNjVrVFhFS2xBY1E9PQ==', 'symfony', '2025-07-23 14:33:00', 14, 'Draft', 'AKSAM', 'rabat', '4535633', 'dina.chabil@aksam-assurances.ma', 'gh', '45634563', 1);

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `name` varchar(180) NOT NULL,
  `roles` longtext NOT NULL COMMENT '(DC2Type:json)',
  `password` varchar(255) NOT NULL,
  `created_at` datetime(6) DEFAULT NULL,
  `updated_at` datetime(6) DEFAULT NULL,
  `is_enabled` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `name`, `roles`, `password`, `created_at`, `updated_at`, `is_enabled`) VALUES
(13, 'dina chabil', '[\"ROLE_ADMIN\"]', '$2y$13$ic07IWgnpk24NXOgyRNAze6u0k36LmUwwgsXXAWoCJM0MbiW4fxly', '2026-03-17 13:49:57.000000', '2026-03-17 13:49:58.000000', 1),
(14, 'kia', '[\"ROLE_USER\"]', '$2y$13$9rLayo39p8JE4kd95P6OGu1IjJWUO8GkxM8B67yU2gnJy6WU0Om7y', '2026-03-17 14:11:59.000000', '2026-03-17 14:11:59.000000', 1);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `doctrine_migration_versions`
--
ALTER TABLE `doctrine_migration_versions`
  ADD PRIMARY KEY (`version`);

--
-- Index pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`);

--
-- Index pour la table `page`
--
ALTER TABLE `page`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_140AB620F6BD1646989D9B62` (`site_id`,`slug`),
  ADD KEY `IDX_140AB620F6BD1646` (`site_id`),
  ADD KEY `IDX_140AB6205DA37D0D` (`slug`),
  ADD KEY `IDX_page_is_published` (`is_published`);

--
-- Index pour la table `page_section`
--
ALTER TABLE `page_section`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_98A2C6F4C4663E4` (`page_id`),
  ADD KEY `FK_59766770F73A70AB` (`reference_section_id`);

--
-- Index pour la table `password_reset_request`
--
ALTER TABLE `password_reset_request`
  ADD PRIMARY KEY (`id`),
  ADD KEY `IDX_BCC6B33DA76ED395` (`user_id`),
  ADD KEY `IDX_BCC6B33DFAE5492F` (`processed_by_id`);

--
-- Index pour la table `reference_section`
--
ALTER TABLE `reference_section`
  ADD PRIMARY KEY (`id`);

--
-- Index pour la table `site`
--
ALTER TABLE `site`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_694309E4115F0EE5` (`domain`),
  ADD KEY `IDX_694309E4A76ED395` (`owner_id`),
  ADD KEY `IDX_site_domain` (`domain`),
  ADD KEY `IDX_site_technology` (`technology`),
  ADD KEY `IDX_site_hosting` (`hosting`),
  ADD KEY `IDX_site_status` (`status`),
  ADD KEY `IDX_site_is_active` (`is_active`);

--
-- Index pour la table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `UNIQ_8D93D6495E237E06` (`name`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `messenger_messages`
--
ALTER TABLE `messenger_messages`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `page`
--
ALTER TABLE `page`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `page_section`
--
ALTER TABLE `page_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT pour la table `password_reset_request`
--
ALTER TABLE `password_reset_request`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `reference_section`
--
ALTER TABLE `reference_section`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `site`
--
ALTER TABLE `site`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT pour la table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `page`
--
ALTER TABLE `page`
  ADD CONSTRAINT `FK_140AB620F6BD1646` FOREIGN KEY (`site_id`) REFERENCES `site` (`id`);

--
-- Contraintes pour la table `page_section`
--
ALTER TABLE `page_section`
  ADD CONSTRAINT `FK_59766770C4663E4` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_59766770F73A70AB` FOREIGN KEY (`reference_section_id`) REFERENCES `reference_section` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `FK_98A2C6F4C4663E4` FOREIGN KEY (`page_id`) REFERENCES `page` (`id`);

--
-- Contraintes pour la table `password_reset_request`
--
ALTER TABLE `password_reset_request`
  ADD CONSTRAINT `FK_BCC6B33DA76ED395` FOREIGN KEY (`user_id`) REFERENCES `user` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `FK_BCC6B33DFAE5492F` FOREIGN KEY (`processed_by_id`) REFERENCES `user` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
