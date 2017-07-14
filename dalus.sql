-- phpMyAdmin SQL Dump
-- version 4.7.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Erstellungszeit: 14. Jul 2017 um 10:58
-- Server-Version: 10.1.22-MariaDB
-- PHP-Version: 7.1.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `dalus`
--
CREATE DATABASE IF NOT EXISTS `dalus` DEFAULT CHARACTER SET latin1 COLLATE latin1_german1_ci;
USE `dalus`;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `objects`
--

CREATE TABLE `objects` (
  `obj_id` int(10) UNSIGNED NOT NULL,
  `obj_typ` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `obj_lon` double NOT NULL,
  `obj_lat` double NOT NULL,
  `obj_nummer` smallint(6) UNSIGNED NOT NULL,
  `obj_hinweis` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `obj_messwert` int(10) UNSIGNED NOT NULL,
  `obj_farbe` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `obj_parameter` varchar(8000) COLLATE utf8_unicode_ci NOT NULL,
  `obj_prj_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `projects`
--

CREATE TABLE `projects` (
  `prj_id` int(10) UNSIGNED NOT NULL,
  `prj_owner` smallint(5) UNSIGNED NOT NULL,
  `prj_shared` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prj_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prj_created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prj_updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `benutzername` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `passwort` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `vorname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nachname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `objects`
--
ALTER TABLE `objects`
  ADD PRIMARY KEY (`obj_nummer`,`obj_prj_id`,`obj_typ`) USING BTREE,
  ADD UNIQUE KEY `obj_id` (`obj_id`);

--
-- Indizes für die Tabelle `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`prj_id`),
  ADD UNIQUE KEY `name` (`prj_name`,`prj_owner`) USING BTREE;

--
-- Indizes für die Tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `benutzername` (`benutzername`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `objects`
--
ALTER TABLE `objects`
  MODIFY `obj_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `projects`
--
ALTER TABLE `projects`
  MODIFY `prj_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT für Tabelle `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;