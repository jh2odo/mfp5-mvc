-- phpMyAdmin SQL Dump
-- version 3.3.2deb1ubuntu1
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 09-05-2015 a las 21:42:30
-- Versión del servidor: 5.1.73
-- Versión de PHP: 5.3.2-1ubuntu4.30

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `demo`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tarea`
--

CREATE TABLE IF NOT EXISTS `tarea` (
  `id_tarea` int(11) NOT NULL AUTO_INCREMENT,
  `titulo_tarea` varchar(100) NOT NULL,
  PRIMARY KEY (`id_tarea`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Volcar la base de datos para la tabla `tarea`
--

INSERT INTO `tarea` (`id_tarea`, `titulo_tarea`) VALUES
(1, 'Instalar software nuevo'),
(2, 'Limpiar la casa'),
(3, 'Hacer la compra'),
(4, 'Preparar el examen');
