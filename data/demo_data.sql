-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Erstellungszeit: 18. Jul 2018 um 13:01
-- Server-Version: 10.1.26-MariaDB
-- PHP-Version: 7.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Datenbank: `redaxo-test`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `rex_multiglossar`
--

CREATE TABLE `rex_multiglossar` (
  `pid` int(10) UNSIGNED NOT NULL,
  `id` int(10) UNSIGNED NOT NULL,
  `clang_id` int(10) UNSIGNED NOT NULL,
  `active` int(1) DEFAULT NULL,
  `term` varchar(255) DEFAULT NULL,
  `term_alt` text,
  `definition` text,
  `description` text,
  `createuser` varchar(255) NOT NULL,
  `updateuser` varchar(255) NOT NULL,
  `createdate` datetime NOT NULL,
  `updatedate` datetime NOT NULL,
  `revision` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Daten für Tabelle `rex_multiglossar`
--

INSERT INTO `rex_multiglossar` (`pid`, `id`, `clang_id`, `active`, `term`, `term_alt`, `definition`, `description`, `createuser`, `updateuser`, `createdate`, `updatedate`, `revision`) VALUES
(3, 1, 1, NULL, 'CMS', 'Rekaktionssystem, Contentmanagement System', 'Mit einem Inhalts Verwaltung System Kann man Texte, Bilder, sowie Videos verwalten.', 'Es gibt viele Verschiedene CMS Systeme, jedes CMS ist von der Struktur sowie auch vom Code anders aufgebaut.\r\nAber der Sinn dahinter ist immer gleich, fast jede Webseite, die es heutzutage im Internet gibt verwendet ein CMS System, weil es einfacher ist Inhalt zu bearbeiten.', 'admin', 'admin', '2018-07-18 11:42:14', '2018-07-18 11:42:14', 0),
(4, 2, 1, NULL, 'REDAXO CMS', 'REDAXO CMS', 'Redaxo ist ein Flexibles System.', 'Mit REDAXO ist es Möglich einen Internet auftritt sehr einfach zu erstellen. Das gute an REDAXO ist die Flexibilität,\r\ndas bedeutet man kann es für viele internet Auftritte verwenden, zum Beispiel für einen Blog oder auch einfach als Persönliche Webseite.', 'admin', 'admin', '2018-07-18 12:18:46', '2018-07-18 12:18:46', 0);

--
-- Indizes der exportierten Tabellen
--

--
-- Indizes für die Tabelle `rex_multiglossar`
--
ALTER TABLE `rex_multiglossar`
  ADD PRIMARY KEY (`pid`);

--
-- AUTO_INCREMENT für exportierte Tabellen
--

--
-- AUTO_INCREMENT für Tabelle `rex_multiglossar`
--
ALTER TABLE `rex_multiglossar`
  MODIFY `pid` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
