-- --------------------------------------------------------
-- Host:                         192.168.10.115
-- Wersja serwera:               5.5.5-10.1.10-MariaDB-log - MariaDB Server
-- Serwer OS:                    Linux
-- HeidiSQL Wersja:              8.3.0.4694
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Zrzut struktury bazy danych bookstore
DROP DATABASE IF EXISTS `bookstore`;
CREATE DATABASE IF NOT EXISTS `bookstore` /*!40100 DEFAULT CHARACTER SET utf8 COLLATE utf8_polish_ci */;
USE `bookstore`;


-- Zrzut struktury tabela bookstore.books
DROP TABLE IF EXISTS `books`;
CREATE TABLE IF NOT EXISTS `books` (
  `idBook` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(250) COLLATE utf8_polish_ci NOT NULL DEFAULT 'no name',
  `idCategory` int(10) unsigned DEFAULT NULL,
  `releaseDate` date DEFAULT NULL,
  `releaseDatetime` datetime DEFAULT NULL,
  `price` float NOT NULL,
  PRIMARY KEY (`idBook`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- Zrzucanie danych dla tabeli bookstore.books: ~10 rows (około)
DELETE FROM `books`;
/*!40000 ALTER TABLE `books` DISABLE KEYS */;
INSERT INTO `books` (`idBook`, `name`, `idCategory`, `releaseDate`, `releaseDatetime`, `price`) VALUES
	(1, 'name - 0', NULL, '2000-02-15', '1988-08-18 02:46:14', 95.76),
	(2, 'name - 1', 2, '1919-08-11', '1992-10-17 13:50:08', 57.07),
	(3, 'name - 2', 4, '1936-10-24', '1968-01-06 17:37:17', 99.19),
	(4, 'name - 3', 11, '1909-09-05', '1920-12-21 21:01:46', 86.57),
	(5, 'name - 4', NULL, '1913-01-09', '1990-11-16 18:35:03', 30.55),
	(6, 'name - 5', NULL, '1936-04-07', '1928-04-04 23:52:09', 14.32),
	(7, 'name - 6', 8, '1907-02-01', '2004-12-14 07:40:56', 17.96),
	(8, 'name - 7', 2, '1947-05-09', '1935-05-10 17:13:24', 43.43),
	(9, 'name - 8', 10, '1949-09-12', '1902-09-10 23:27:50', 39.56),
	(10, 'name - 9', 8, '1945-05-20', '1918-05-10 12:18:11', 27.04);
/*!40000 ALTER TABLE `books` ENABLE KEYS */;


-- Zrzut struktury tabela bookstore.books Categories Dic
DROP TABLE IF EXISTS `books Categories Dic`;
CREATE TABLE IF NOT EXISTS `books Categories Dic` (
  `idCategory` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  PRIMARY KEY (`idCategory`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- Zrzucanie danych dla tabeli bookstore.books Categories Dic: ~2 rows (około)
DELETE FROM `books Categories Dic`;
/*!40000 ALTER TABLE `books Categories Dic` DISABLE KEYS */;
INSERT INTO `books Categories Dic` (`idCategory`, `name`) VALUES
	(1, 'IT'),
	(2, 'Science fiction'),
	(3, 'Satire'),
	(4, 'Drama'),
	(5, 'Action and Adventure'),
	(6, 'Romance'),
	(7, 'Mystery'),
	(8, 'Horror'),
	(9, 'Self help'),
	(10, 'Religion, Spirituality & New Age'),
	(11, 'Journals');
/*!40000 ALTER TABLE `books Categories Dic` ENABLE KEYS */;


-- Zrzut struktury tabela bookstore.booksTags
DROP TABLE IF EXISTS `booksTags`;
CREATE TABLE IF NOT EXISTS `booksTags` (
  `idBook` int(10) unsigned NOT NULL,
  `idTag` int(10) unsigned NOT NULL,
  `dateOfAdded` datetime NOT NULL,
  PRIMARY KEY (`idBook`,`idTag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- Zrzucanie danych dla tabeli bookstore.booksTags: ~0 rows (około)
DELETE FROM `booksTags`;
/*!40000 ALTER TABLE `booksTags` DISABLE KEYS */;
/*!40000 ALTER TABLE `booksTags` ENABLE KEYS */;


-- Zrzut struktury tabela bookstore.booksTagsDic
DROP TABLE IF EXISTS `booksTagsDic`;
CREATE TABLE IF NOT EXISTS `booksTagsDic` (
  `idTag` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `tag` varchar(50) COLLATE utf8_polish_ci NOT NULL,
  PRIMARY KEY (`idTag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_polish_ci;

-- Zrzucanie danych dla tabeli bookstore.booksTagsDic: ~0 rows (około)
DELETE FROM `booksTagsDic`;
/*!40000 ALTER TABLE `booksTagsDic` DISABLE KEYS */;
/*!40000 ALTER TABLE `booksTagsDic` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;

DROP PROCEDURE IF EXISTS createBooks;

-- delimiter //
CREATE PROCEDURE createBooks(IN num INT)
    label1: LOOP
        SET num = num - 1;

        IF num >= 0 THEN
            INSERT INTO `books`
                (`idBook`, `name`, `idCategory`, `releaseDate`, `releaseDatetime`, `price`) VALUES
                (null, CONCAT('name', num), NULL, '2000-02-15', '1988-08-18 02:46:14', 95.76)
            ;
            ITERATE label1;
        END IF;

        LEAVE label1;

    END LOOP label1;
BEGIN
END;
-- delimiter ;
