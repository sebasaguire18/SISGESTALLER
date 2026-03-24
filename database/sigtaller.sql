-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 24-03-2026 a las 22:53:39
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sigiij`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_detalles`
--

CREATE TABLE `orden_detalles` (
  `id` varchar(36) NOT NULL,
  `ot_id` varchar(100) NOT NULL,
  `observaciones` text DEFAULT NULL,
  `CREATED_AT` timestamp NOT NULL DEFAULT current_timestamp(),
  `UPDATED_AT` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_items`
--

CREATE TABLE `orden_items` (
  `id` varchar(36) NOT NULL,
  `ot_id` varchar(100) NOT NULL,
  `ps_id` varchar(36) DEFAULT NULL,
  `nombre` varchar(255) DEFAULT NULL,
  `referencia_bodega` varchar(100) DEFAULT NULL,
  `tipo` enum('producto','servicio') NOT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `cantidad` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orden_trabajos`
--

CREATE TABLE `orden_trabajos` (
  `ot_id` varchar(200) NOT NULL COMMENT 'identificador único',
  `ot_placa` varchar(6) NOT NULL COMMENT 'placa del vehiculo',
  `ot_empresa` varchar(200) NOT NULL COMMENT 'nombre de la empresa o persona',
  `ot_fecha_ingreso` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'fecha de creación del registro',
  `ot_estado` int(11) NOT NULL DEFAULT 1 COMMENT '1:activo 0:inactivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos_servicios`
--

CREATE TABLE `productos_servicios` (
  `id` varchar(36) NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `tipo` enum('producto','servicio') NOT NULL,
  `referencia_bodega` varchar(100) DEFAULT NULL,
  `precio` decimal(10,2) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `usuario_id` varchar(200) NOT NULL COMMENT 'identificador único',
  `usuario_nombre` varchar(200) NOT NULL COMMENT 'nombre del usuario',
  `usuario_email` varchar(200) NOT NULL COMMENT 'correo',
  `usuario_clave` varchar(200) NOT NULL COMMENT 'contraseña',
  `usuario_estado` int(11) NOT NULL DEFAULT 1 COMMENT '1:activo 0:inactivo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`usuario_id`, `usuario_nombre`, `usuario_email`, `usuario_clave`, `usuario_estado`) VALUES
('SAV00118032026', 'SEBASTIAN AGUIRRE', 'taller@importadoraisuzujapon.com', '2708', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `orden_detalles`
--
ALTER TABLE `orden_detalles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `orden_items`
--
ALTER TABLE `orden_items`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos_servicios`
--
ALTER TABLE `productos_servicios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `referencia_bodega` (`referencia_bodega`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
