SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

CREATE DATABASE IF NOT EXISTS `dalus` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `dalus`;

CREATE TABLE `gps` (
  `gps_car_id` varchar(10) NOT NULL,
  `gps_lat` double NOT NULL,
  `gps_lon` double NOT NULL,
  `gps_speed` float NOT NULL,
  `gps_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `gps_id` int(11) NOT NULL,
  `gps_aid` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `objects` (
  `obj_id` int(10) UNSIGNED NOT NULL,
  `obj_typ` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `obj_lon` double NOT NULL,
  `obj_lat` double NOT NULL,
  `obj_nummer` smallint(6) NOT NULL,
  `obj_hinweis` varchar(8000) COLLATE utf8_unicode_ci NOT NULL,
  `obj_messwert` int(10) UNSIGNED NOT NULL,
  `obj_farbe` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `obj_parameter` varchar(8000) COLLATE utf8_unicode_ci NOT NULL,
  `obj_label` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `obj_messtrupp` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `obj_prj_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `options` (
  `opt_UID` int(10) UNSIGNED NOT NULL,
  `opt_cars` longtext NOT NULL,
  `opt_kataster` longtext NOT NULL,
  `opt_OWMAPI` varchar(255) NOT NULL,
  `opt_googleAPI` text NOT NULL,
  `opt_city` varchar(255) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE `projects` (
  `prj_id` int(10) UNSIGNED NOT NULL,
  `prj_owner` smallint(5) UNSIGNED NOT NULL,
  `prj_shared` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prj_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `prj_created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `prj_updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `benutzername` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `passwort` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `level` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `vorname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `nachname` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


ALTER TABLE `gps`
  ADD UNIQUE KEY `gps_id_2` (`gps_id`),
  ADD KEY `gps_id` (`gps_id`);

ALTER TABLE `objects`
  ADD PRIMARY KEY (`obj_typ`,`obj_nummer`,`obj_prj_id`),
  ADD UNIQUE KEY `obj_id` (`obj_id`);

ALTER TABLE `options`
  ADD UNIQUE KEY `userid` (`opt_UID`);

ALTER TABLE `projects`
  ADD PRIMARY KEY (`prj_id`),
  ADD UNIQUE KEY `name` (`prj_name`),
  ADD KEY `prj_owner` (`prj_owner`,`prj_name`);

ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `benutzername` (`benutzername`);


ALTER TABLE `gps`
  MODIFY `gps_id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `objects`
  MODIFY `obj_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `projects`
  MODIFY `prj_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
