-- Create database, table and user for n2graph
-- Version 1.0.0 and above

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

SET NAMES utf8mb4;

CREATE DATABASE IF NOT EXISTS `n2graph` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `n2graph`;

CREATE TABLE IF NOT EXISTS `hmet` (
  `idmser` mediumint(8) unsigned NOT NULL COMMENT 'Id del servicio-metrica',
  `fchmet` int(11) unsigned NOT NULL COMMENT 'Fecha de la metrica',
  `estado` varchar(25) NOT NULL COMMENT 'Estado del check',
  `nroint` tinyint(3) unsigned NOT NULL COMMENT 'Número de intento',
  `valor` float NOT NULL COMMENT 'Valor del check',
  PRIMARY KEY (`idmser`,`fchmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='Tabla de historico de métricas';


CREATE TABLE IF NOT EXISTS `mser` (
  `idmser` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id del registro',
  `host` varchar(100) NOT NULL COMMENT 'Nombre del host',
  `service` varchar(100) NOT NULL COMMENT 'Nombre del servicio',
  `metrica` varchar(100) NOT NULL COMMENT 'Nombre de la métrica',
  `metalias` varchar(100) NOT NULL DEFAULT '' COMMENT 'Alias de la métrica',
  `unidad` varchar(25) NOT NULL DEFAULT '' COMMENT 'Unidades de medición',
  PRIMARY KEY (`idmser`),
  KEY `host_service_metalias` (`host`,`service`,`metalias`(50))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='Maestro de Servicios';

-- This in case upgrading from version 0.9.2 and below
ALTER TABLE `mser` 
ADD COLUMN IF NOT EXISTS `metalias` varchar(100) NOT NULL DEFAULT '' COMMENT 'Alias de la métrica',
ADD COLUMN IF NOT EXISTS `unidad` varchar(25) NOT NULL DEFAULT '' COMMENT 'Unidades de medición';

UPDATE `mser` SET `metalias`=`metrica` WHERE `metalias`='';

ALTER TABLE `mser` 
DROP INDEX IF EXISTS `host_service_metrica`;

ALTER TABLE `mser` 
ADD INDEX IF NOT EXISTS `host_service_metalias` (`host`,`service`,`metalias`(50));

-- Create user and give privileges

CREATE USER IF NOT EXISTS 'n2guser'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON n2graph.* TO 'n2guser'@'localhost';
FLUSH PRIVILEGES;


-- 2020-12-04
