-- phpMyAdmin SQL Dump
-- version 4.8.3
-- https://www.phpmyadmin.net/
--
-- Anamakine: localhost:3306
-- Üretim Zamanı: 02 Ara 2018, 20:43:01
-- Sunucu sürümü: 5.5.62-0ubuntu0.14.04.1-log
-- PHP Sürümü: 7.1.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--

--

DELIMITER $$
--
-- Yordamlar
--
CREATE DEFINER=`db_kgs`@`%` PROCEDURE `CheckCard` (IN `crd` VARCHAR(40), IN `rou` INT)  BEGIN
	DECLARE rtr INTEGER;
	DECLARE antiPass VARCHAR(50);
  DECLARE lastRoute INTEGER;
	DECLARE logCount INTEGER;
	DECLARE n VARCHAR(200);

	SELECT COUNT(*) INTO rtr FROM Cards WHERE CardId = crd AND Active = 1;
	
	IF rtr > 0 THEN
		SELECT CONCAT(`Name`,' ',`Surname`) INTO n FROM Cards WHERE CardId = crd;

		SELECT `Value` INTO antiPass FROM Settings WHERE Item = 'AntiPass';
			IF AntiPass = '1' THEN
				SET lastRoute = (SELECT `Route` FROM `Logs` WHERE CardId = crd AND `Status` = 1 ORDER BY Id DESC LIMIT 1);
				SET logCount = (SELECT COUNT(*) FROM `Logs` WHERE CardId = crd AND `Status` = 1 ORDER BY Id DESC LIMIT 1);
				IF logCount = 0 THEN
					INSERT INTO Logs (`Name`, CardId, Date, Route, `Status`) VALUES (n, crd, NOW(), rou, rtr);
					SELECT n AS `Name`;
				ELSEIF rou = lastRoute THEN
					INSERT INTO Logs (`Name`, CardId, Date, Route, `Status`) VALUES (CONCAT(n,' - AntiPass'), crd, NOW(), rou, 0);
					SELECT '0' AS `Name`;
				ELSE
					INSERT INTO Logs (`Name`, CardId, Date, Route, `Status`) VALUES (n, crd, NOW(), rou, rtr);
					SELECT n AS `Name`;
				END IF;
			ELSE
					INSERT INTO Logs (`Name`, CardId, Date, Route, `Status`) VALUES (n, crd, NOW(), rou, rtr);
					SELECT n AS `Name`;
			END IF;
	ELSE
		INSERT INTO Logs (`Name`, CardId, Date, Route, `Status`) VALUES ('Tanımsız Kart', crd, NOW(), rou, rtr);
		SELECT '0' AS `Name`;
	END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Cards`
--

CREATE TABLE `Cards` (
  `id` bigint(50) UNSIGNED NOT NULL,
  `CardId` varchar(50) DEFAULT NULL,
  `Name` varchar(50) DEFAULT NULL,
  `Surname` varchar(50) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `Active` int(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Tablo döküm verisi `Cards`
--

INSERT INTO `Cards` (`id`, `CardId`, `Name`, `Surname`, `Phone`, `Active`) VALUES
(1, '19 7d ec d5', 'Adem', 'KIRMIZIYUZ', NULL, 1);

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Logs`
--

CREATE TABLE `Logs` (
  `Id` bigint(20) UNSIGNED NOT NULL,
  `Name` varchar(50) NOT NULL,
  `CardId` varchar(50) NOT NULL,
  `Date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `Route` int(10) NOT NULL DEFAULT '0' COMMENT '0 - Giriş, 1 - Çıkış',
  `Status` int(1) DEFAULT NULL COMMENT '0 - Hatali, 1 - Doğru'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Tablo için tablo yapısı `Settings`
--

CREATE TABLE `Settings` (
  `Item` varchar(50) NOT NULL,
  `Value` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Tablo döküm verisi `Settings`
--

INSERT INTO `Settings` (`Item`, `Value`) VALUES
('AntiPass', '1');

--
-- Dökümü yapılmış tablolar için indeksler
--

--
-- Tablo için indeksler `Cards`
--
ALTER TABLE `Cards`
  ADD PRIMARY KEY (`id`);

--
-- Tablo için indeksler `Logs`
--
ALTER TABLE `Logs`
  ADD PRIMARY KEY (`Id`);

--
-- Tablo için indeksler `Settings`
--
ALTER TABLE `Settings`
  ADD PRIMARY KEY (`Item`);

--
-- Dökümü yapılmış tablolar için AUTO_INCREMENT değeri
--

--
-- Tablo için AUTO_INCREMENT değeri `Cards`
--
ALTER TABLE `Cards`
  MODIFY `id` bigint(50) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Tablo için AUTO_INCREMENT değeri `Logs`
--
ALTER TABLE `Logs`
  MODIFY `Id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
