-- Adminer 4.7.7 MySQL dump

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

SET NAMES utf8mb4;

CREATE DATABASE `n2graph` /*!40100 DEFAULT CHARACTER SET utf8mb4 */;
USE `n2graph`;

CREATE TABLE `hmet` (
  `idmser` mediumint(8) unsigned NOT NULL COMMENT 'Id del servicio-metrica',
  `fchmet` int(11) unsigned NOT NULL COMMENT 'Fecha de la metrica',
  `estado` varchar(25) NOT NULL COMMENT 'Estado del check',
  `nroint` tinyint(3) unsigned NOT NULL COMMENT 'Número de intento',
  `valor` float NOT NULL COMMENT 'Valor del check',
  PRIMARY KEY (`idmser`,`fchmet`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='Tabla de historico de métricas';


CREATE TABLE `mser` (
  `idmser` mediumint(8) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Id del registro',
  `host` varchar(100) NOT NULL COMMENT 'Nombre del host',
  `service` varchar(100) NOT NULL COMMENT 'Nombre del servicio',
  `metrica` varchar(100) NOT NULL COMMENT 'Nombre de la métrica',
  PRIMARY KEY (`idmser`),
  UNIQUE KEY `host_service_metrica` (`host`,`service`,`metrica`(50))
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COMMENT='Maestro de Servicios';

CREATE USER 'n2guser'@'localhost' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON n2graph.* TO 'n2guser'@'localhost';
FLUSH PRIVILEGES;

-- 2020-11-27 14:53:29
