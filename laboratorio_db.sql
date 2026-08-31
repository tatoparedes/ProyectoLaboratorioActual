-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-08-2026 a las 11:18:08
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
-- Base de datos: `laboratorio_db`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizarPrueba` (IN `p_nPrueba` INT, IN `p_nEspecie` INT, IN `p_cFoto` VARCHAR(255), IN `p_cDescripcion` TEXT, IN `p_cResultado` TEXT, IN `p_cBacteria` VARCHAR(100), IN `p_nUsuario` INT)   BEGIN
    UPDATE prueba
    SET nEspecie = p_nEspecie,
        cFoto = p_cFoto,
        cDescripcion = p_cDescripcion,
        cResultado = p_cResultado,
        cBacteria = p_cBacteria,
        nUsuario = p_nUsuario
    WHERE nPrueba = p_nPrueba;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_calificacion` (IN `p_nCalificacion` INT, IN `p_cCalificacion` DECIMAL(4,2))   BEGIN
    UPDATE calificacion
    SET cCalificacion = p_cCalificacion
    WHERE nCalificacion = p_nCalificacion;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_especie` (IN `p_nEspecie` INT, IN `p_cEspecie` VARCHAR(100), IN `p_nGenero` INT)   BEGIN
    UPDATE especie 
    SET cEspecie = p_cEspecie, nGenero = p_nGenero
    WHERE nEspecie = p_nEspecie;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_examen` (IN `p_nExamen` INT, IN `p_cExamen` VARCHAR(100), IN `p_cCodigoExamen` CHAR(6), IN `p_bEstado` BIT(1))   BEGIN
    UPDATE examen
    SET cExamen = p_cExamen,
        cCodigoExamen = p_cCodigoExamen,
        bEstado = p_bEstado
    WHERE nExamen = p_nExamen;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_familia` (IN `p_nFamilia` INT, IN `p_cFamilia` VARCHAR(100))   BEGIN
    UPDATE familia 
    SET cFamilia = p_cFamilia 
    WHERE nFamilia = p_nFamilia;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_genero` (IN `p_nGenero` INT, IN `p_cGenero` VARCHAR(100), IN `p_nFamilia` INT)   BEGIN
    UPDATE genero 
    SET cGenero = p_cGenero, nFamilia = p_nFamilia
    WHERE nGenero = p_nGenero;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_actualizar_pregunta` (IN `p_nPregunta` INT, IN `p_cPregunta` TEXT, IN `p_nPrueba` INT, IN `p_nExamen` INT)   BEGIN
    UPDATE pregunta
    SET cPregunta = p_cPregunta,
        nPrueba = p_nPrueba,
        nExamen = p_nExamen
    WHERE nPregunta = p_nPregunta;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_calificar_respuesta` (IN `p_nRespuesta` INT, IN `p_cComentario` TEXT)   BEGIN
    UPDATE respuesta
    SET cComentario = p_cComentario
    WHERE nRespuesta = p_nRespuesta;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_crear_especie` (IN `p_cEspecie` VARCHAR(100), IN `p_nGenero` INT, IN `p_nUsuario` INT)   BEGIN
    INSERT INTO especie (cEspecie, nGenero, nUsuario) 
    VALUES (p_cEspecie, p_nGenero, p_nUsuario);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_crear_examen` (IN `p_cExamen` VARCHAR(100), IN `p_cCodigoExamen` CHAR(6), IN `p_nUsuario` INT, IN `p_bEstado` BIT(1))   BEGIN
    INSERT INTO examen (cExamen, cCodigoExamen, nUsuario, bEstado)
    VALUES (p_cExamen, p_cCodigoExamen, p_nUsuario, p_bEstado);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_crear_familia` (IN `p_cFamilia` VARCHAR(100), IN `p_nUsuario` INT)   BEGIN
    INSERT INTO familia (cFamilia, nUsuario) 
    VALUES (p_cFamilia, p_nUsuario);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_crear_genero` (IN `p_cGenero` VARCHAR(100), IN `p_nFamilia` INT, IN `p_nUsuario` INT)   BEGIN
    INSERT INTO genero (cGenero, nFamilia, nUsuario) 
    VALUES (p_cGenero, p_nFamilia, p_nUsuario);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_crear_pregunta` (IN `p_cPregunta` TEXT, IN `p_nPrueba` INT, IN `p_nExamen` INT)   BEGIN
    INSERT INTO pregunta (cPregunta, nPrueba, nExamen)
    VALUES (p_cPregunta, p_nPrueba, p_nExamen);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminarPrueba` (IN `p_nPrueba` INT)   BEGIN
    DELETE FROM prueba 
    WHERE nPrueba = p_nPrueba;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_especie` (IN `p_nEspecie` INT)   BEGIN
    DELETE FROM especie 
    WHERE nEspecie = p_nEspecie;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_examen` (IN `p_nExamen` INT)   BEGIN
    DELETE FROM examen WHERE nExamen = p_nExamen;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_familia` (IN `p_nFamilia` INT)   BEGIN
    DELETE FROM familia 
    WHERE nFamilia = p_nFamilia;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_genero` (IN `p_nGenero` INT)   BEGIN
    DELETE FROM genero 
    WHERE nGenero = p_nGenero;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_eliminar_pregunta` (IN `p_nPregunta` INT)   BEGIN
    DELETE FROM pregunta WHERE nPregunta = p_nPregunta;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_guardar_respuesta` (IN `p_nPregunta` INT, IN `p_cRespuesta` TEXT, IN `p_nCalificacion` INT, IN `p_cComentario` TEXT)   BEGIN
    INSERT INTO respuesta (nPregunta, cRespuesta, nCalificacion, cComentario)
    VALUES (p_nPregunta, p_cRespuesta, p_nCalificacion, p_cComentario);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_insertarPrueba` (IN `p_nEspecie` INT, IN `p_cFoto` VARCHAR(255), IN `p_cDescripcion` TEXT, IN `p_cResultado` TEXT, IN `p_cBacteria` VARCHAR(100), IN `p_nUsuario` INT)   BEGIN
    INSERT INTO prueba (nEspecie, cFoto, cDescripcion, cResultado, cBacteria, nUsuario)
    VALUES (p_nEspecie, p_cFoto, p_cDescripcion, p_cResultado, p_cBacteria, p_nUsuario);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_listarPrueba` ()   BEGIN
    SELECT p.*, e.cEspecie, u.cNombres, u.cApePaterno
    FROM prueba p
    INNER JOIN especie e ON p.nEspecie = e.nEspecie
    INNER JOIN usuario u ON p.nUsuario = u.nUsuario
    ORDER BY p.nPrueba DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_listar_calificaciones_examen` (IN `p_nExamen` INT)   BEGIN
    SELECT c.*, u.cNombres, u.cApePaterno, u.cApeMaterno, u.cDNI
    FROM calificacion c
    INNER JOIN usuario u ON c.nUsuario = u.nUsuario
    WHERE c.nExamen = p_nExamen
    ORDER BY c.cCalificacion DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_listar_especies` ()   BEGIN
    SELECT e.*, g.cGenero, f.cFamilia
    FROM especie e
    INNER JOIN genero g ON e.nGenero = g.nGenero
    INNER JOIN familia f ON g.nFamilia = f.nFamilia
    ORDER BY e.nEspecie DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_listar_examenes` ()   BEGIN
    SELECT e.*, u.cNombres, u.cApePaterno 
    FROM examen e
    INNER JOIN usuario u ON e.nUsuario = u.nUsuario
    ORDER BY e.nExamen DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_listar_familias` ()   BEGIN
    SELECT * FROM familia ORDER BY nFamilia DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_listar_generos` ()   BEGIN
    SELECT g.*, f.cFamilia 
    FROM genero g
    INNER JOIN familia f ON g.nFamilia = f.nFamilia
    ORDER BY g.nGenero DESC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_listar_preguntas_por_examen` (IN `p_nExamen` INT)   BEGIN
    SELECT * FROM pregunta WHERE nExamen = p_nExamen ORDER BY nPregunta ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_listar_respuestas_calificacion` (IN `p_nCalificacion` INT)   BEGIN
    SELECT r.*, p.cPregunta
    FROM respuesta r
    INNER JOIN pregunta p ON r.nPregunta = p.nPregunta
    WHERE r.nCalificacion = p_nCalificacion
    ORDER BY r.nRespuesta ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_listar_roles` ()   BEGIN
    SELECT * FROM rol ORDER BY nRol ASC;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_login_usuario` (IN `pDNI` VARCHAR(8), IN `pPassword` VARCHAR(255))   BEGIN
    SELECT * FROM usuario
    WHERE cDNI = pDNI;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_calificacion` (IN `p_cCalificacion` DECIMAL(4,2), IN `p_nExamen` INT, IN `p_nUsuario` INT)   BEGIN
    INSERT INTO calificacion (cCalificacion, nExamen, nUsuario)
    VALUES (p_cCalificacion, p_nExamen, p_nUsuario);
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_registrar_usuario` (IN `pDNI` VARCHAR(8), IN `pNombres` VARCHAR(100), IN `pApePaterno` VARCHAR(50), IN `pApeMaterno` VARCHAR(50), IN `pCorreo` VARCHAR(100), IN `pContrasena` VARCHAR(255), IN `pRol` INT)   BEGIN
    INSERT INTO usuario (
        cDNI,
        cNombres,
        cApePaterno,
        cApeMaterno,
        cCorreo,
        cContrasena,
        nRol
    ) VALUES (
        pDNI,
        pNombres,
        pApePaterno,
        pApeMaterno,
        pCorreo,
        pContrasena,
        pRol
    );
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_verificar_rol` (IN `_dni` VARCHAR(8))   BEGIN
    SELECT 
        nUsuario,
        cNombres,
        nRol,
        cDNI,
        cCorreo
    FROM usuario
    WHERE cDNI = _dni;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificacion`
--

CREATE TABLE `calificacion` (
  `nCalificacion` int(11) NOT NULL,
  `cCalificacion` decimal(4,1) DEFAULT NULL,
  `nExamen` int(11) NOT NULL,
  `nUsuario` int(11) NOT NULL,
  `fechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `especie`
--

CREATE TABLE `especie` (
  `nEspecie` int(11) NOT NULL,
  `cEspecie` varchar(100) NOT NULL,
  `nGenero` int(11) NOT NULL,
  `nUsuario` int(11) NOT NULL,
  `dtFechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `examen`
--

CREATE TABLE `examen` (
  `nExamen` int(11) NOT NULL,
  `cExamen` varchar(100) NOT NULL,
  `cCodigoExamen` char(6) NOT NULL,
  `nUsuario` int(11) NOT NULL,
  `fechaRegistro` timestamp NOT NULL DEFAULT current_timestamp(),
  `bEstado` bit(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `familia`
--

CREATE TABLE `familia` (
  `nFamilia` int(11) NOT NULL,
  `cFamilia` varchar(100) NOT NULL,
  `nUsuario` int(11) NOT NULL,
  `dtFechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `genero`
--

CREATE TABLE `genero` (
  `nGenero` int(11) NOT NULL,
  `cGenero` varchar(100) NOT NULL,
  `nFamilia` int(11) NOT NULL,
  `nUsuario` int(11) NOT NULL,
  `dtFechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pregunta`
--

CREATE TABLE `pregunta` (
  `nPregunta` int(11) NOT NULL,
  `cPregunta` text NOT NULL,
  `nPrueba` int(11) DEFAULT NULL,
  `nExamen` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prueba`
--

CREATE TABLE `prueba` (
  `nPrueba` int(11) NOT NULL,
  `nEspecie` int(11) NOT NULL,
  `cFoto` varchar(255) DEFAULT NULL,
  `cDescripcion` text DEFAULT NULL,
  `cResultado` text DEFAULT NULL,
  `cBacteria` varchar(100) DEFAULT NULL,
  `nUsuario` int(11) NOT NULL,
  `dtFechaRegistro` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `respuesta`
--

CREATE TABLE `respuesta` (
  `nRespuesta` int(11) NOT NULL,
  `nPregunta` int(11) NOT NULL,
  `cRespuesta` text NOT NULL,
  `nCalificacion` int(11) DEFAULT NULL,
  `cComentario` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `nRol` int(11) NOT NULL,
  `cRol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`nRol`, `cRol`) VALUES
(1, 'Alumno'),
(2, 'Docente'),
(3, 'Admin');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `nUsuario` int(11) NOT NULL,
  `cContrasena` varchar(255) NOT NULL,
  `nRol` int(11) DEFAULT NULL,
  `cDNI` char(8) NOT NULL,
  `cApePaterno` varchar(50) DEFAULT NULL,
  `cApeMaterno` varchar(50) DEFAULT NULL,
  `cNombres` varchar(100) DEFAULT NULL,
  `cCorreo` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`nUsuario`, `cContrasena`, `nRol`, `cDNI`, `cApePaterno`, `cApeMaterno`, `cNombres`, `cCorreo`) VALUES
(13, '$2y$10$NC1e4k3KQBVB5hX8e28WuOXfE3ZjlItq4iQZvM5VG9319pcuYIBgi', 2, '75109606', 'VASQUEZ', 'MILLER', 'CRISTIAN SEBASTIAN', 'cristiansvm17@gmail.com');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `calificacion`
--
ALTER TABLE `calificacion`
  ADD PRIMARY KEY (`nCalificacion`),
  ADD KEY `nExamen` (`nExamen`),
  ADD KEY `nUsuario` (`nUsuario`);

--
-- Indices de la tabla `especie`
--
ALTER TABLE `especie`
  ADD PRIMARY KEY (`nEspecie`),
  ADD KEY `nGenero` (`nGenero`),
  ADD KEY `nUsuario` (`nUsuario`);

--
-- Indices de la tabla `examen`
--
ALTER TABLE `examen`
  ADD PRIMARY KEY (`nExamen`),
  ADD UNIQUE KEY `cCodigoExamen` (`cCodigoExamen`),
  ADD KEY `nUsuario` (`nUsuario`);

--
-- Indices de la tabla `familia`
--
ALTER TABLE `familia`
  ADD PRIMARY KEY (`nFamilia`),
  ADD KEY `nUsuario` (`nUsuario`);

--
-- Indices de la tabla `genero`
--
ALTER TABLE `genero`
  ADD PRIMARY KEY (`nGenero`),
  ADD KEY `nFamilia` (`nFamilia`),
  ADD KEY `nUsuario` (`nUsuario`);

--
-- Indices de la tabla `pregunta`
--
ALTER TABLE `pregunta`
  ADD PRIMARY KEY (`nPregunta`),
  ADD KEY `nExamen` (`nExamen`),
  ADD KEY `nPrueba` (`nPrueba`);

--
-- Indices de la tabla `prueba`
--
ALTER TABLE `prueba`
  ADD PRIMARY KEY (`nPrueba`),
  ADD KEY `nEspecie` (`nEspecie`),
  ADD KEY `nUsuario` (`nUsuario`);

--
-- Indices de la tabla `respuesta`
--
ALTER TABLE `respuesta`
  ADD PRIMARY KEY (`nRespuesta`),
  ADD KEY `nPregunta` (`nPregunta`),
  ADD KEY `nCalificacion` (`nCalificacion`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`nRol`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`nUsuario`),
  ADD KEY `nRol` (`nRol`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `calificacion`
--
ALTER TABLE `calificacion`
  MODIFY `nCalificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `especie`
--
ALTER TABLE `especie`
  MODIFY `nEspecie` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `examen`
--
ALTER TABLE `examen`
  MODIFY `nExamen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `familia`
--
ALTER TABLE `familia`
  MODIFY `nFamilia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `genero`
--
ALTER TABLE `genero`
  MODIFY `nGenero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pregunta`
--
ALTER TABLE `pregunta`
  MODIFY `nPregunta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT de la tabla `prueba`
--
ALTER TABLE `prueba`
  MODIFY `nPrueba` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `respuesta`
--
ALTER TABLE `respuesta`
  MODIFY `nRespuesta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `nRol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `nUsuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `calificacion`
--
ALTER TABLE `calificacion`
  ADD CONSTRAINT `calificacion_ibfk_1` FOREIGN KEY (`nExamen`) REFERENCES `examen` (`nExamen`),
  ADD CONSTRAINT `calificacion_ibfk_2` FOREIGN KEY (`nUsuario`) REFERENCES `usuario` (`nUsuario`);

--
-- Filtros para la tabla `especie`
--
ALTER TABLE `especie`
  ADD CONSTRAINT `especie_ibfk_1` FOREIGN KEY (`nGenero`) REFERENCES `genero` (`nGenero`),
  ADD CONSTRAINT `especie_ibfk_2` FOREIGN KEY (`nUsuario`) REFERENCES `usuario` (`nUsuario`);

--
-- Filtros para la tabla `examen`
--
ALTER TABLE `examen`
  ADD CONSTRAINT `examen_ibfk_1` FOREIGN KEY (`nUsuario`) REFERENCES `usuario` (`nUsuario`);

--
-- Filtros para la tabla `familia`
--
ALTER TABLE `familia`
  ADD CONSTRAINT `familia_ibfk_1` FOREIGN KEY (`nUsuario`) REFERENCES `usuario` (`nUsuario`);

--
-- Filtros para la tabla `genero`
--
ALTER TABLE `genero`
  ADD CONSTRAINT `genero_ibfk_1` FOREIGN KEY (`nFamilia`) REFERENCES `familia` (`nFamilia`),
  ADD CONSTRAINT `genero_ibfk_2` FOREIGN KEY (`nUsuario`) REFERENCES `usuario` (`nUsuario`);

--
-- Filtros para la tabla `pregunta`
--
ALTER TABLE `pregunta`
  ADD CONSTRAINT `pregunta_ibfk_1` FOREIGN KEY (`nExamen`) REFERENCES `examen` (`nExamen`),
  ADD CONSTRAINT `pregunta_ibfk_2` FOREIGN KEY (`nPrueba`) REFERENCES `prueba` (`nPrueba`);

--
-- Filtros para la tabla `prueba`
--
ALTER TABLE `prueba`
  ADD CONSTRAINT `prueba_ibfk_1` FOREIGN KEY (`nEspecie`) REFERENCES `especie` (`nEspecie`),
  ADD CONSTRAINT `prueba_ibfk_2` FOREIGN KEY (`nUsuario`) REFERENCES `usuario` (`nUsuario`);

--
-- Filtros para la tabla `respuesta`
--
ALTER TABLE `respuesta`
  ADD CONSTRAINT `respuesta_ibfk_1` FOREIGN KEY (`nPregunta`) REFERENCES `pregunta` (`nPregunta`),
  ADD CONSTRAINT `respuesta_ibfk_2` FOREIGN KEY (`nCalificacion`) REFERENCES `calificacion` (`nCalificacion`);

--
-- Filtros para la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `usuario_ibfk_1` FOREIGN KEY (`nRol`) REFERENCES `rol` (`nRol`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
