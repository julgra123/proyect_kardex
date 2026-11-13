-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 13-11-2026 a las 21:52:27
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `kardex_prueba`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id_admin` int(11) NOT NULL,
  `ci` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id_admin`, `ci`, `nombres`, `apellidos`, `usuario`, `password`) VALUES
(2, '1234567', 'Admin', 'Sistema', 'admin', '!migrado'),
(3, 'EMERGENCIA', 'Cuenta', 'de Emergencia', 'emergencia', '!migrado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id` bigint(20) NOT NULL,
  `momento` datetime NOT NULL,
  `id_identidad` int(11) DEFAULT NULL,
  `usuario` varchar(80) DEFAULT NULL,
  `rol` varchar(20) DEFAULT NULL,
  `evento` varchar(50) NOT NULL,
  `resultado` enum('ok','fallo','denegado') NOT NULL,
  `recurso` varchar(120) DEFAULT NULL,
  `detalle` text DEFAULT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `severidad` enum('info','alerta','critica') NOT NULL DEFAULT 'info',
  `hash_prev` char(64) NOT NULL,
  `hash` char(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id`, `momento`, `id_identidad`, `usuario`, `rol`, `evento`, `resultado`, `recurso`, `detalle`, `ip`, `severidad`, `hash_prev`, `hash`) VALUES
(1, '2026-10-25 19:22:07', NULL, 'admin', '', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', '', '0000000000000000000000000000000000000000000000000000000000000000', '727c4ffc7ac4ebf34bdde56a75c67cd286fb0bfcb971abc12fe51864ce53adcb'),
(2, '2026-10-25 19:22:12', NULL, 'admin', '', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', '', '727c4ffc7ac4ebf34bdde56a75c67cd286fb0bfcb971abc12fe51864ce53adcb', 'fc716a8d3bfdb2434e0acbea004eeb1908bef49514eae6ddef06e09262f393f9'),
(3, '2026-10-25 19:28:00', NULL, 'demo-01', '', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', '', 'fc716a8d3bfdb2434e0acbea004eeb1908bef49514eae6ddef06e09262f393f9', '6b55e96a15dd047d676b039eeb66132bc56aa607c0a25769783ba6bda6e0da44'),
(4, '2026-10-25 19:38:14', NULL, 'migracion', 'sistema', 'identidad_migrada', 'ok', 'usuario:admin', '{\"rol\":\"admin\"}', 'cli', '', '6b55e96a15dd047d676b039eeb66132bc56aa607c0a25769783ba6bda6e0da44', '285e6e66ae979f9581606a486f972ec0a575e58d270456cd9856c7a7e9c5ffa6'),
(5, '2026-10-25 19:38:14', NULL, 'migracion', 'sistema', 'identidad_migrada', 'ok', 'usuario:demo.ana', '{\"rol\":\"docente\"}', 'cli', '', '285e6e66ae979f9581606a486f972ec0a575e58d270456cd9856c7a7e9c5ffa6', '0ed3808991ce1e011608b97b1a611cba237559c1174d867ef02314352d45e384'),
(6, '2026-10-25 19:38:14', NULL, 'migracion', 'sistema', 'identidad_migrada', 'ok', 'usuario:demo.carlos', '{\"rol\":\"docente\"}', 'cli', '', '0ed3808991ce1e011608b97b1a611cba237559c1174d867ef02314352d45e384', '80a1dde6bbeb9499821f027300315583238b3ee8d295b95591c60326827d74b2'),
(7, '2026-10-25 19:38:14', NULL, 'migracion', 'sistema', 'identidad_migrada', 'ok', 'usuario:demo.elena', '{\"rol\":\"docente\"}', 'cli', '', '80a1dde6bbeb9499821f027300315583238b3ee8d295b95591c60326827d74b2', '11ef1e6d62293999be5453764645d83a770b6cd5d0defd49f15fd4c92acb49fb'),
(8, '2026-10-25 19:38:14', NULL, 'migracion', 'sistema', 'identidad_migrada', 'ok', 'usuario:DEMO-01', '{\"rol\":\"estudiante\"}', 'cli', '', '11ef1e6d62293999be5453764645d83a770b6cd5d0defd49f15fd4c92acb49fb', 'f9b6531aadab8f333ae379f52c3016e88d568ee7c139cac27985880e05fe1fea'),
(9, '2026-10-25 19:38:14', NULL, 'migracion', 'sistema', 'identidad_migrada', 'ok', 'usuario:DEMO-02', '{\"rol\":\"estudiante\"}', 'cli', '', 'f9b6531aadab8f333ae379f52c3016e88d568ee7c139cac27985880e05fe1fea', '1d43879121e8de79e81e51b752cbc622737ae10f9337fd7db4599a6dfb13b95e'),
(10, '2026-10-25 19:38:14', NULL, 'migracion', 'sistema', 'identidad_migrada', 'ok', 'usuario:DEMO-03', '{\"rol\":\"estudiante\"}', 'cli', '', '1d43879121e8de79e81e51b752cbc622737ae10f9337fd7db4599a6dfb13b95e', 'a825bd18fe7bfccb445052756183eaded407d0876d9f5e43f7528468fb9f48b2'),
(11, '2026-10-25 19:38:15', NULL, 'migracion', 'sistema', 'identidad_migrada', 'ok', 'usuario:DEMO-04', '{\"rol\":\"estudiante\"}', 'cli', '', 'a825bd18fe7bfccb445052756183eaded407d0876d9f5e43f7528468fb9f48b2', '7bba10f576fe1a9804fa56f056e2bbf4a95e4d94527672ec39420177ea3974cc'),
(12, '2026-10-25 19:38:15', NULL, 'migracion', 'sistema', 'identidad_migrada', 'ok', 'usuario:DEMO-05', '{\"rol\":\"estudiante\"}', 'cli', '', '7bba10f576fe1a9804fa56f056e2bbf4a95e4d94527672ec39420177ea3974cc', 'a1d944df0ed46c0087b63b3b267118c84929744ef8ac4a82014450160f08aeb2'),
(13, '2026-10-25 19:38:15', NULL, 'migracion', 'sistema', 'identidad_migrada', 'ok', 'usuario:DEMO-06', '{\"rol\":\"estudiante\"}', 'cli', '', 'a1d944df0ed46c0087b63b3b267118c84929744ef8ac4a82014450160f08aeb2', '19b739fb74dbef8d7ee4e304cc827b6627ad1f3204a0b3e798b35bf080c28a93'),
(14, '2026-10-25 19:38:16', NULL, '', '', 'identidad_creada', 'ok', 'identidad#11', '{\"usuario\":\"emergencia\",\"rol\":\"admin\",\"estado\":\"suspendida\"}', 'cli', '', '19b739fb74dbef8d7ee4e304cc827b6627ad1f3204a0b3e798b35bf080c28a93', 'c1250433f2f331cd9661bc2f5b9a740e57909960dab305b075baac08fd51404d'),
(15, '2026-10-25 19:39:36', 5, 'demo-01', 'estudiante', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', '', 'c1250433f2f331cd9661bc2f5b9a740e57909960dab305b075baac08fd51404d', 'bde3e4d8af813c58bc8b701dea73da24d35c24a144192222d8f48582e7e228fd'),
(16, '2026-10-25 19:39:48', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', '', 'bde3e4d8af813c58bc8b701dea73da24d35c24a144192222d8f48582e7e228fd', '1f03f038d32ae7328ed750e5c878fd9c0e9de24c94d3d8affbf31dfbe5b7e13c'),
(17, '2026-10-25 19:51:18', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', '1f03f038d32ae7328ed750e5c878fd9c0e9de24c94d3d8affbf31dfbe5b7e13c', 'f9b6cf04de774937b15ecb18076cc95f29106902783e109cd9d0649bec4a736e'),
(18, '2026-10-25 19:57:49', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'f9b6cf04de774937b15ecb18076cc95f29106902783e109cd9d0649bec4a736e', 'bce5fc8e493e1664b40ca17be77a9a9a48fd64d44bd202321aff95c6ebab8a72'),
(19, '2026-10-25 19:57:58', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'bce5fc8e493e1664b40ca17be77a9a9a48fd64d44bd202321aff95c6ebab8a72', 'f1d578bf8c44c12e9763779991461bdbfb724ab489c1ac91b4638d225f13072b'),
(20, '2026-10-25 19:58:19', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'f1d578bf8c44c12e9763779991461bdbfb724ab489c1ac91b4638d225f13072b', 'b398c8a0e4f2396ebdf09667dc3650fe30c63fd312b6f464e0029f1d30ced20a'),
(21, '2026-10-25 19:58:25', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'b398c8a0e4f2396ebdf09667dc3650fe30c63fd312b6f464e0029f1d30ced20a', 'c9583f5083d70d8f822dfb73a7745b418d01051d1a07f381acad7db36601f848'),
(22, '2026-10-25 20:00:09', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'c9583f5083d70d8f822dfb73a7745b418d01051d1a07f381acad7db36601f848', 'fe6e22187550a914c54b3bce01fdd5663792d56fabc4c042660fe5e1c35988e0'),
(23, '2026-10-25 20:00:18', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'fe6e22187550a914c54b3bce01fdd5663792d56fabc4c042660fe5e1c35988e0', 'e87856fd7001d963b314b323db26b984d4fc7c7851c41f7860570f657dabd885'),
(24, '2026-10-25 20:00:25', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'e87856fd7001d963b314b323db26b984d4fc7c7851c41f7860570f657dabd885', 'e05a70ead99710e631071ead35398062a358921b0d90c9f711252a04b2af3e74'),
(25, '2026-10-25 20:01:26', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'e05a70ead99710e631071ead35398062a358921b0d90c9f711252a04b2af3e74', 'b239b7144519453ef66077045446a58dc083f1d110f4534581eede0307f864be'),
(26, '2026-10-25 20:01:29', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'b239b7144519453ef66077045446a58dc083f1d110f4534581eede0307f864be', 'ccde3ad036d0a729fa687bb51f59bf26fc49c969d77d39275b204c5908b95f3d'),
(27, '2026-10-25 20:01:36', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'ccde3ad036d0a729fa687bb51f59bf26fc49c969d77d39275b204c5908b95f3d', '3604179d909edbc08e84b103242253c74b472ffa28515629b9b34c998870faf6'),
(28, '2026-10-25 20:02:03', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', '3604179d909edbc08e84b103242253c74b472ffa28515629b9b34c998870faf6', '89ac96913d8e159bb8afca94fe018242568a7105a66d00d29e040f1c2f1713dd'),
(29, '2026-10-25 20:02:49', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', '89ac96913d8e159bb8afca94fe018242568a7105a66d00d29e040f1c2f1713dd', 'd855fff17e5ea87fbc3c97ac70db9e8a47264dc7d60160493ab70ca272eeff66'),
(30, '2026-10-25 20:02:59', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'd855fff17e5ea87fbc3c97ac70db9e8a47264dc7d60160493ab70ca272eeff66', 'c9c4a9698010ee051bea1e0b14c6e1d9c4fcf4458aed3b271a50815d87c91843'),
(31, '2026-10-25 20:03:25', 1, 'admin', 'admin', 'logout', 'ok', '', '', '::1', '', 'c9c4a9698010ee051bea1e0b14c6e1d9c4fcf4458aed3b271a50815d87c91843', '477abe047f89009a56be37ff3b9088b33309af24e3f38f52fc9272cd09059bda'),
(32, '2026-10-25 20:03:33', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', '', '477abe047f89009a56be37ff3b9088b33309af24e3f38f52fc9272cd09059bda', 'b94592b8b259e64e4202de39dbcf3616ddfd18c309a01993581e4b7559d360e4'),
(33, '2026-10-25 20:03:45', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'b94592b8b259e64e4202de39dbcf3616ddfd18c309a01993581e4b7559d360e4', 'beb59e64db9b89a33ee0e3f337833414fac4228142479b2fb7a659952b342bd3'),
(34, '2026-10-25 20:04:35', 1, 'admin', 'admin', 'logout', 'ok', '', '', '::1', '', 'beb59e64db9b89a33ee0e3f337833414fac4228142479b2fb7a659952b342bd3', 'aef43af1aaf5d69ddd0f1eee0e2e9a399cc6ddf1944aee55e48503561fc1e80e'),
(35, '2026-10-25 20:04:41', 5, 'demo-01', 'estudiante', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', '', 'aef43af1aaf5d69ddd0f1eee0e2e9a399cc6ddf1944aee55e48503561fc1e80e', '8d2aaeaa31b3e5cc7e037f28dbbc761cdfb21efb6dde6753649aa38c84b8b252'),
(36, '2026-10-25 20:04:47', 5, 'demo-01', 'estudiante', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', '', '8d2aaeaa31b3e5cc7e037f28dbbc761cdfb21efb6dde6753649aa38c84b8b252', '739b76bfd475d1cdb4cf094ce323dfdd9e50030a9b9e4b1b5c41b590ed7fe385'),
(37, '2026-10-25 20:05:08', 5, 'DEMO-01', 'estudiante', 'login_ok', 'ok', '', '', '::1', '', '739b76bfd475d1cdb4cf094ce323dfdd9e50030a9b9e4b1b5c41b590ed7fe385', 'f84bd66dbe0f19d883ad345d43eadf758db067563974a38d0a8af47055736df6'),
(38, '2026-10-25 20:05:58', 5, 'DEMO-01', 'estudiante', 'logout', 'ok', '', '', '::1', '', 'f84bd66dbe0f19d883ad345d43eadf758db067563974a38d0a8af47055736df6', 'cd9c4f96828c959ba07310bf7c58581d40a1c37ada710cf00794ba7db3912f9a'),
(39, '2026-10-25 20:06:04', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', '', 'cd9c4f96828c959ba07310bf7c58581d40a1c37ada710cf00794ba7db3912f9a', '162c94c2d3da818d71b40fe0b8e8034851ceadcd8f05ca8a28c62a121a2b8c2a'),
(40, '2026-10-25 20:06:44', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', '162c94c2d3da818d71b40fe0b8e8034851ceadcd8f05ca8a28c62a121a2b8c2a', 'c12952aaac95fc6a5cc43b382c4a7684f1959e762bec9453784357984ef74393'),
(41, '2026-10-25 20:06:53', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'c12952aaac95fc6a5cc43b382c4a7684f1959e762bec9453784357984ef74393', 'e439ff0b6e589e6a7ed46368853d7d2140296f4ad4c933e34ed135e9c3f856b3'),
(42, '2026-10-25 20:08:00', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'e439ff0b6e589e6a7ed46368853d7d2140296f4ad4c933e34ed135e9c3f856b3', 'fc376ea85fd26ba8806ad9e9e4769b0c04e948842967d7e887c1e37fec510f94'),
(43, '2026-10-25 20:10:18', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'fc376ea85fd26ba8806ad9e9e4769b0c04e948842967d7e887c1e37fec510f94', '6798da37e03c1d34dcb16a3e1429d24dfd005d84cfdef97d7d53db7d6517f2d4'),
(44, '2026-10-25 20:16:31', 1, 'admin', 'admin', 'logout', 'ok', '', '', '::1', '', '6798da37e03c1d34dcb16a3e1429d24dfd005d84cfdef97d7d53db7d6517f2d4', 'd0d75b2a4d7c3fca89c0a1e407e789d49dce4af0b701324cc2bbc0d4f74866c7'),
(45, '2026-10-25 20:16:36', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', '', 'd0d75b2a4d7c3fca89c0a1e407e789d49dce4af0b701324cc2bbc0d4f74866c7', '77180db0f2b65342127082faf804fdcf6a85e7f8a167cd3db09bb01d5d283950'),
(46, '2026-10-25 20:17:08', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', '77180db0f2b65342127082faf804fdcf6a85e7f8a167cd3db09bb01d5d283950', 'e5d049bd659d3b406b652e72126e0584830b7be4674f2ed539ac35ea3f8db141'),
(47, '2026-10-25 20:19:20', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'activacion', '', '::1', '', 'e5d049bd659d3b406b652e72126e0584830b7be4674f2ed539ac35ea3f8db141', '828989bdeda580e68c8841526ec5b06c7237007556fb22ada6fc7e9a658cad8d'),
(48, '2026-10-25 20:20:57', 1, 'admin', 'admin', 'logout', 'ok', '', '', '::1', '', '828989bdeda580e68c8841526ec5b06c7237007556fb22ada6fc7e9a658cad8d', '8d7bb32d254c600b2f752952c92bd7742bca91bc5d031cbd14519b0b449b6690'),
(49, '2026-10-25 20:21:06', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', '', '8d7bb32d254c600b2f752952c92bd7742bca91bc5d031cbd14519b0b449b6690', '9b30b4f88e5cb5411a7ba11c6efabe5469c0562c8ce2710528c96ccafd96f06c'),
(50, '2026-10-25 20:23:20', 1, 'admin', 'admin', 'mfa_activado', 'ok', 'identidad#1', '', '::1', '', '9b30b4f88e5cb5411a7ba11c6efabe5469c0562c8ce2710528c96ccafd96f06c', '1abe3359cefd91678f7e1f9c531f2310edfafce3aeff03da2b31d3bf18e854e7'),
(51, '2026-10-25 20:25:45', 1, 'admin', 'admin', 'integridad_verificada', 'ok', '', '{\"cadena_ok\":true,\"alteradas\":0,\"eliminadas\":0}', '::1', '', '1abe3359cefd91678f7e1f9c531f2310edfafce3aeff03da2b31d3bf18e854e7', '89d97e2e3a061e304e7188a4456a775162babd196e7582e94262c297165f825b'),
(52, '2026-10-25 20:51:52', 1, 'admin', 'admin', 'sesion_cerrada', 'denegado', '', 'inactividad', '::1', '', '89d97e2e3a061e304e7188a4456a775162babd196e7582e94262c297165f825b', '2113a21dba1e9a16f98c26678c960ee8d3efa8a2704ba5611e6ccacc749d05bd'),
(53, '2026-10-25 20:52:08', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', '', '2113a21dba1e9a16f98c26678c960ee8d3efa8a2704ba5611e6ccacc749d05bd', '5cc59111933caab99dce5822df1ed7e23d30ba7d7aeb85087c3e5aed40787a05'),
(54, '2026-10-25 21:58:16', 1, 'admin', 'admin', 'sesion_cerrada', 'denegado', '', 'inactividad', '::1', '', '5cc59111933caab99dce5822df1ed7e23d30ba7d7aeb85087c3e5aed40787a05', '7c3523ece9db9ef9416a8a152e55c2fe8742ea13e9d3b07bbb3d47151ba5543e'),
(55, '2026-10-25 22:30:11', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', '', '7c3523ece9db9ef9416a8a152e55c2fe8742ea13e9d3b07bbb3d47151ba5543e', 'adf6eefef950718262a412eb8682043474ebdbdc88ab56a0078dcbb88c50d62b'),
(56, '2026-10-25 22:30:17', 1, 'admin', 'admin', 'logout', 'ok', '', '', '::1', '', 'adf6eefef950718262a412eb8682043474ebdbdc88ab56a0078dcbb88c50d62b', '389a0f1fca2a2bb9221eaf3013546dac78e7fd352a4e00cf0da7a32ffb1754d2'),
(57, '2026-10-25 22:30:24', 2, 'demo.ana', 'docente', 'login_ok', 'ok', '', '', '::1', '', '389a0f1fca2a2bb9221eaf3013546dac78e7fd352a4e00cf0da7a32ffb1754d2', '9d3793e851129f63733d9533c5e5569145db91bc4433083ff4778edf8195acd1'),
(58, '2026-10-25 22:31:38', 2, 'demo.ana', 'docente', 'nota_editada', 'ok', 'kardex#10', '{\"estudiante\":\"DEMO-02\",\"materia\":1,\"trimestre\":1,\"nota_antes\":\"45.00\",\"nota_despues\":\"45.00\"}', '::1', '', '9d3793e851129f63733d9533c5e5569145db91bc4433083ff4778edf8195acd1', '8953cf66b7a2935fca72fd558575fd2c3325799185821b2f2110b6405ec75b38'),
(59, '2026-10-25 22:31:45', 2, 'demo.ana', 'docente', 'logout', 'ok', '', '', '::1', '', '8953cf66b7a2935fca72fd558575fd2c3325799185821b2f2110b6405ec75b38', 'd25d377b91202ac2dfc1789e122a4aa5c07ee0a3c7f401c02388a371d241fcc8'),
(60, '2026-10-25 22:31:56', 5, 'demo-01', 'estudiante', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', '', 'd25d377b91202ac2dfc1789e122a4aa5c07ee0a3c7f401c02388a371d241fcc8', 'c6411f851258ebb1f9d31b059e251da4b6452fc0179762ca9494e595d30b1af5'),
(61, '2026-10-25 22:32:28', 5, 'DEMO-01', 'estudiante', 'login_ok', 'ok', '', '', '::1', '', 'c6411f851258ebb1f9d31b059e251da4b6452fc0179762ca9494e595d30b1af5', 'f72c2a3db1885e613b476b4d1cef7ccb73ca9136f6ff7bf43163b68cdaefcf71'),
(62, '2026-10-25 22:52:40', 5, 'DEMO-01', 'estudiante', 'sesion_cerrada', 'denegado', '', 'inactividad', '::1', '', 'f72c2a3db1885e613b476b4d1cef7ccb73ca9136f6ff7bf43163b68cdaefcf71', '3ca0994e8fcc9a9eb2bb5b832d82c1cb406c8693cbe1245cd7b1d9325fa61ecd'),
(63, '2026-10-25 23:00:25', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', 'info', '3ca0994e8fcc9a9eb2bb5b832d82c1cb406c8693cbe1245cd7b1d9325fa61ecd', '909c96e257c42b397e8ff109ed35e5ea9bcc099278e52a8174ed2ff985083ebc'),
(64, '2026-10-25 23:01:33', 1, 'admin', 'admin', 'logout', 'ok', '', '', '::1', 'info', '909c96e257c42b397e8ff109ed35e5ea9bcc099278e52a8174ed2ff985083ebc', 'd78dc8e5cdc17e5265fdf4708416c1dd382b6b70a94ea5c3ad04584404fdd57b'),
(65, '2026-10-25 23:01:39', 2, 'demo.ana', 'docente', 'login_ok', 'ok', '', '', '::1', 'info', 'd78dc8e5cdc17e5265fdf4708416c1dd382b6b70a94ea5c3ad04584404fdd57b', '5e0a9c5f1380227fcb73b698cd80347a6dbf670a7d428be96c25394a788238e9'),
(66, '2026-10-25 23:03:02', 2, 'demo.ana', 'docente', 'acceso_denegado', 'denegado', 'docente.ver', '{\"razon\":\"sin_permiso\",\"recurso\":[],\"pagina\":\"profesores.php\"}', '::1', 'alerta', '5e0a9c5f1380227fcb73b698cd80347a6dbf670a7d428be96c25394a788238e9', 'cfa0004c49ad8bb0240bb43b0150b7466c0da7ebb026797eda8cc59d52d7f934'),
(67, '2026-10-25 23:03:08', 2, 'demo.ana', 'docente', 'acceso_denegado', 'denegado', 'mfa.configurar', '{\"razon\":\"sin_permiso\",\"recurso\":[],\"pagina\":\"mfa_config.php\"}', '::1', 'alerta', 'cfa0004c49ad8bb0240bb43b0150b7466c0da7ebb026797eda8cc59d52d7f934', 'ffb4a1c5ef0b5264f8d802fbdf44ca940431b877982851d11d8a672f73afde18'),
(68, '2026-10-25 23:03:09', 2, 'demo.ana', 'docente', 'acceso_denegado', 'denegado', 'panel.ver', '{\"razon\":\"sin_permiso\",\"recurso\":[],\"pagina\":\"index.php\"}', '::1', 'alerta', 'ffb4a1c5ef0b5264f8d802fbdf44ca940431b877982851d11d8a672f73afde18', '113971008a05a965e137772bf02d0e38f857210db568f8d7d8e2d2c7603f0e3d'),
(69, '2026-10-25 23:03:11', 2, 'demo.ana', 'docente', 'logout', 'ok', '', '', '::1', 'info', '113971008a05a965e137772bf02d0e38f857210db568f8d7d8e2d2c7603f0e3d', '4e0991c43081168922c70a7573d6f09d95927b6f0c5a5d6e54b46783cbece804'),
(70, '2026-10-25 23:03:21', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', 'info', '4e0991c43081168922c70a7573d6f09d95927b6f0c5a5d6e54b46783cbece804', 'e56298c7f7cb1bd0471ab65432a2aa034f324543a0b3ea63dcdba49eb13b2767'),
(71, '2026-10-25 23:03:53', 1, 'admin', 'admin', 'materia_creada', 'ok', 'materia#7', '{\"nombre\":\"PRUEBA\",\"docente\":\"2\"}', '::1', 'info', 'e56298c7f7cb1bd0471ab65432a2aa034f324543a0b3ea63dcdba49eb13b2767', 'aaf2f93042073ca655c96126f270cc38be44cfb000df501c46a480039559f820'),
(72, '2026-10-25 23:04:34', 1, 'admin', 'admin', 'mfa_fallido', 'fallo', 'eliminar.php', '', '::1', 'alerta', 'aaf2f93042073ca655c96126f270cc38be44cfb000df501c46a480039559f820', '3f41e0a38bdc9a3518552348f85041b6ac6c09c6c765cdc070a580b3c39be96e'),
(73, '2026-10-25 23:04:58', 1, 'admin', 'admin', 'mfa_verificado', 'ok', 'eliminar.php', '', '::1', 'info', '3f41e0a38bdc9a3518552348f85041b6ac6c09c6c765cdc070a580b3c39be96e', '95ec4a2c2ea692b03143fb43c2500727b0ba77b81703de57c7e5fc67427befba'),
(74, '2026-10-25 23:04:58', 1, 'admin', 'admin', 'materia_eliminada', 'ok', 'materia#7', '{\"nombre\":\"PRUEBA\"}', '::1', 'alerta', '95ec4a2c2ea692b03143fb43c2500727b0ba77b81703de57c7e5fc67427befba', '579b25b83ab8c6fa2f93608f748e2c07e6091734cfcd087a4a7948a459ac9e4a'),
(75, '2026-10-25 23:32:52', 1, 'admin', 'admin', 'sesion_cerrada', 'denegado', '', 'inactividad', '::1', 'info', '579b25b83ab8c6fa2f93608f748e2c07e6091734cfcd087a4a7948a459ac9e4a', '987005e9b069439f667452fc958e6c595c37ae8776e99331a493e66f8fc003c4'),
(76, '2026-11-02 21:51:28', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', 'info', '987005e9b069439f667452fc958e6c595c37ae8776e99331a493e66f8fc003c4', 'ff733c6f175d9261af33ccd0f2b1afe0e92e2fbc8d3751acdbd379212667cd89'),
(77, '2026-11-03 16:48:23', NULL, 'migracion', 'sistema', 'clave_predeterminada_cambio_obligatorio', 'ok', 'identidades', '{\"usuarios\":[\"demo.carlos\",\"demo.elena\"]}', 'cli', 'alerta', 'ff733c6f175d9261af33ccd0f2b1afe0e92e2fbc8d3751acdbd379212667cd89', '8c3acac59224f0d7d6760d39793ce056c647c896c269398e87844984a5ff7edd'),
(78, '2026-11-03 16:48:59', 1, 'admin', 'admin', 'sesion_cerrada', 'denegado', '', 'sin_registro', '::1', 'info', '8c3acac59224f0d7d6760d39793ce056c647c896c269398e87844984a5ff7edd', 'd5e8baf94242431b33de3618abe448c3b5467f9e43768fd787a1d56a42572b38'),
(79, '2026-11-03 16:49:09', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', 'info', 'd5e8baf94242431b33de3618abe448c3b5467f9e43768fd787a1d56a42572b38', '47574fb230073dc9277ecdc59b64b9eb1432395fbd7d14dc4d27caa09abf0fdf'),
(80, '2026-11-03 16:49:16', 1, 'admin', 'admin', 'logout', 'ok', '', '', '::1', 'info', '47574fb230073dc9277ecdc59b64b9eb1432395fbd7d14dc4d27caa09abf0fdf', '396df5fb2bf336e0993d9b27bd25d2e5670bc6d1674798a88f512799beb69d9c'),
(81, '2026-11-03 16:50:31', 2, 'demo.ana', 'docente', 'login_ok', 'ok', '', '', '::1', 'info', '396df5fb2bf336e0993d9b27bd25d2e5670bc6d1674798a88f512799beb69d9c', 'fe0aff516fd8bc1451007e851ea9a48899ee8fdc768fcb6255c6d46f11ef2db7'),
(82, '2026-11-03 16:53:02', 2, 'demo.ana', 'docente', 'logout', 'ok', '', '', '::1', 'info', 'fe0aff516fd8bc1451007e851ea9a48899ee8fdc768fcb6255c6d46f11ef2db7', '00df604c134f7b9af09fd8c125b6fac4558b1362ad7f98b6f362bfe29d5ff380'),
(83, '2026-11-03 16:53:07', 5, 'demo-01', 'estudiante', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', 'info', '00df604c134f7b9af09fd8c125b6fac4558b1362ad7f98b6f362bfe29d5ff380', 'b2b171365cb23e1576006a1f638d39bcd0521ed5c863c8a804e5db72d5fa3408'),
(84, '2026-11-03 16:53:10', 5, 'demo-01', 'estudiante', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', 'info', 'b2b171365cb23e1576006a1f638d39bcd0521ed5c863c8a804e5db72d5fa3408', '8b2fb69e5cebc4cc1b4065f2986360909370217d6d8991fec85c16e8a681b71c'),
(85, '2026-11-03 16:53:33', 5, 'DEMO-01', 'estudiante', 'login_ok', 'ok', '', '', '::1', 'info', '8b2fb69e5cebc4cc1b4065f2986360909370217d6d8991fec85c16e8a681b71c', '4c20aed989299d4815a048496fd1846886e95e792c5295133047eb8f17165091'),
(86, '2026-11-03 16:58:34', 5, 'DEMO-01', 'estudiante', 'password_cambiada', 'ok', 'identidad#5', '', '::1', 'info', '4c20aed989299d4815a048496fd1846886e95e792c5295133047eb8f17165091', 'a8df4cea20c919140ab7a603c03f79ef2ae17e5ee2496a77a9cc7274fed5f4d1'),
(87, '2026-11-03 16:58:50', 5, 'DEMO-01', 'estudiante', 'logout', 'ok', '', '', '::1', 'info', 'a8df4cea20c919140ab7a603c03f79ef2ae17e5ee2496a77a9cc7274fed5f4d1', '007a001da2f17922d6f8f28a1332861f9884d173a5affbfaf690c760d4d63c01'),
(88, '2026-11-03 16:59:01', 5, 'demo-01', 'estudiante', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', 'info', '007a001da2f17922d6f8f28a1332861f9884d173a5affbfaf690c760d4d63c01', '6d63a36b7b8c1bc77e87223b15ff9076e6aa8e9efdc9a0ea318d90044e501fe9'),
(89, '2026-11-03 16:59:05', 5, 'DEMO-01', 'estudiante', 'login_ok', 'ok', '', '', '::1', 'info', '6d63a36b7b8c1bc77e87223b15ff9076e6aa8e9efdc9a0ea318d90044e501fe9', '6453fb01b7f1ca8fee612ba894a0799edfc53525a002efc06e2ed6944680a4d1'),
(90, '2026-11-03 16:59:10', 5, 'DEMO-01', 'estudiante', 'logout', 'ok', '', '', '::1', 'info', '6453fb01b7f1ca8fee612ba894a0799edfc53525a002efc06e2ed6944680a4d1', '62f93f85981947e80f2c1af94b21ef32961e997a0293b667e6b8c4b4ecdad6f0'),
(91, '2026-11-03 16:59:17', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', 'info', '62f93f85981947e80f2c1af94b21ef32961e997a0293b667e6b8c4b4ecdad6f0', '2b4c681e0fbbc3d89c1583630ce9513ad07bbb39606be192b5123881eb4cc429'),
(92, '2026-11-03 17:00:30', 1, 'admin', 'admin', 'logout', 'ok', '', '', '::1', 'info', '2b4c681e0fbbc3d89c1583630ce9513ad07bbb39606be192b5123881eb4cc429', '9987418288d345624de0a70cc0e1ad12f725e7563d75689a170aaa8ebcc65cae'),
(93, '2026-11-03 17:00:37', 5, 'demo-01', 'estudiante', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', 'info', '9987418288d345624de0a70cc0e1ad12f725e7563d75689a170aaa8ebcc65cae', 'b34fca0a65172a09add3011d077d675e8be3aeb95a72d8a308b2c0e42be74bae'),
(94, '2026-11-03 17:00:47', 5, 'DEMO-01', 'estudiante', 'login_ok', 'ok', '', '', '::1', 'info', 'b34fca0a65172a09add3011d077d675e8be3aeb95a72d8a308b2c0e42be74bae', '0a8f859c8e8d4ff4aa0520ad223b96298533a95bea5609e93b19a9f0900d19b4'),
(95, '2026-11-03 17:03:08', 5, 'DEMO-01', 'estudiante', 'logout', 'ok', '', '', '::1', 'info', '0a8f859c8e8d4ff4aa0520ad223b96298533a95bea5609e93b19a9f0900d19b4', 'def1395b09aeda5c07213b5a5db84e84e6f6375b302b9cd980e09122acb2eef8'),
(96, '2026-11-03 17:03:24', 5, 'demo-01', 'estudiante', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', 'info', 'def1395b09aeda5c07213b5a5db84e84e6f6375b302b9cd980e09122acb2eef8', 'cc45b967e6e66c428677ed878f602720c9641e8521bf2c83e5842be4bc482065'),
(97, '2026-11-03 17:03:43', 5, 'demo-01', 'estudiante', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', 'info', 'cc45b967e6e66c428677ed878f602720c9641e8521bf2c83e5842be4bc482065', 'f156936b675ee5bd5d262cafa4d42ee36e4c3c950d36aeee9fc06e75e7479d94'),
(98, '2026-11-03 17:05:16', 5, 'DEMO-01', 'estudiante', 'login_ok', 'ok', '', '', '::1', 'info', 'f156936b675ee5bd5d262cafa4d42ee36e4c3c950d36aeee9fc06e75e7479d94', '72e5f4be2d1860304b766ac3f584e1433dfb260b943bbad5255df4a4ce5f9f72'),
(99, '2026-11-03 17:10:20', 5, 'DEMO-01', 'estudiante', 'logout', 'ok', '', '', '::1', 'info', '72e5f4be2d1860304b766ac3f584e1433dfb260b943bbad5255df4a4ce5f9f72', '765c04aafdc4f1dbbc676fc2bcd742b82008059a949e125e592f27fbc8bea022'),
(100, '2026-11-03 17:10:26', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', 'info', '765c04aafdc4f1dbbc676fc2bcd742b82008059a949e125e592f27fbc8bea022', '4330090d1c8f192da9e0b70022a116706eddb5422b227007e62f904a525d2434'),
(101, '2026-11-03 17:10:28', 1, 'admin', 'admin', 'integridad_verificada', 'ok', '', '{\"cadena_ok\":false,\"alteradas\":0,\"eliminadas\":0}', '::1', 'critica', '4330090d1c8f192da9e0b70022a116706eddb5422b227007e62f904a525d2434', 'c24a2bad5001476f1fa8dddf885ca4986afcbc4513aa58f1a683afa7fecfd257'),
(102, '2026-11-03 17:11:11', 1, 'admin', 'admin', 'logout', 'ok', '', '', '::1', 'info', 'c24a2bad5001476f1fa8dddf885ca4986afcbc4513aa58f1a683afa7fecfd257', 'e7d300b3985e2b195b67d873607bdc01b4af3995946ad47f013407d8e3e18745'),
(103, '2026-11-03 17:11:27', 6, 'demo-02', 'estudiante', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', 'info', 'e7d300b3985e2b195b67d873607bdc01b4af3995946ad47f013407d8e3e18745', '51756727e2c4ed03c5e423c4d65afedab0974d595e066e828a82ead7bb2a918b'),
(104, '2026-11-03 17:11:28', 6, 'DEMO-02', 'estudiante', 'login_ok', 'ok', '', '', '::1', 'info', '51756727e2c4ed03c5e423c4d65afedab0974d595e066e828a82ead7bb2a918b', '427a68cc96960757cd303cbde7c119e8ad195cfe3d8fe33c37e38c32ac58fead'),
(105, '2026-11-03 17:12:48', 6, 'DEMO-02', 'estudiante', 'password_cambiada', 'ok', 'identidad#6', '', '::1', 'info', '427a68cc96960757cd303cbde7c119e8ad195cfe3d8fe33c37e38c32ac58fead', '307889a618f29b97027558baee750b78c0a3a1bb114f143e6e379b2ed423661a'),
(106, '2026-11-03 17:21:26', 6, 'DEMO-02', 'estudiante', 'acceso_denegado', 'denegado', 'kardex.ver', '{\"razon\":\"kardex_ajeno\",\"re_solicitado\":\"DEMO-03\"}', '::1', 'alerta', '307889a618f29b97027558baee750b78c0a3a1bb114f143e6e379b2ed423661a', '8e1f0919c9828310cd59eadd1ecf1104e936ccd51557d2d92a3479200a3e5503'),
(107, '2026-11-03 17:21:35', 6, 'DEMO-02', 'estudiante', 'acceso_denegado', 'denegado', 'kardex.ver', '{\"razon\":\"kardex_ajeno\",\"re_solicitado\":\"DEMO-01\"}', '::1', 'alerta', '8e1f0919c9828310cd59eadd1ecf1104e936ccd51557d2d92a3479200a3e5503', 'e1ae0bc08180c3831f42d34b1ec9b9689e663b082465cacc96e3b2359f977792'),
(108, '2026-11-03 17:24:35', 6, 'DEMO-02', 'estudiante', 'acceso_denegado', 'denegado', 'kardex.ver', '{\"razon\":\"kardex_ajeno\",\"re_solicitado\":\"DEMO-01\"}', '::1', 'alerta', 'e1ae0bc08180c3831f42d34b1ec9b9689e663b082465cacc96e3b2359f977792', '1c2975ee23a644cc06b36a51afbc8f3150664f87e22661188ccf5ef32d36f8a9'),
(109, '2026-11-03 17:24:38', 6, 'DEMO-02', 'estudiante', 'acceso_denegado', 'denegado', 'kardex.ver', '{\"razon\":\"kardex_ajeno\",\"re_solicitado\":\"DEMO-03\"}', '::1', 'alerta', '1c2975ee23a644cc06b36a51afbc8f3150664f87e22661188ccf5ef32d36f8a9', 'b4c9b7ecacbab1c40512d769552dffadccd514d2a2cc03a94f85217b86d66b50'),
(110, '2026-11-03 17:24:50', 6, 'DEMO-02', 'estudiante', 'logout', 'ok', '', '', '::1', 'info', 'b4c9b7ecacbab1c40512d769552dffadccd514d2a2cc03a94f85217b86d66b50', '152a1c877ea816541463b305df944ebc5a171dfd2ea3dddb57f2faa8ceae2bc4'),
(111, '2026-11-03 17:24:56', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', 'info', '152a1c877ea816541463b305df944ebc5a171dfd2ea3dddb57f2faa8ceae2bc4', '1315ac67e3747c6a6e404107d195e47009576b57042de627b907664740cc2303'),
(112, '2026-11-03 20:14:03', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', 'info', '1315ac67e3747c6a6e404107d195e47009576b57042de627b907664740cc2303', 'c531c978f23fe8fdc4451270dcdc84d74781fc0fecc0c388c4b3d9883956aeb9'),
(113, '2026-11-03 20:14:13', 1, 'admin', 'admin', 'logout', 'ok', '', '', '::1', 'info', 'c531c978f23fe8fdc4451270dcdc84d74781fc0fecc0c388c4b3d9883956aeb9', '05c411f1611073e73f41a8279ed55672d6d18a7bdca205950e2eb54efc33cbc9'),
(114, '2026-11-03 20:14:21', 1, 'admin', 'admin', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', 'info', '05c411f1611073e73f41a8279ed55672d6d18a7bdca205950e2eb54efc33cbc9', '985388d6d259de72a9c43525c1b248a42b1e49844bf0d3dece5619adc9c45e6e'),
(115, '2026-11-03 20:14:27', 1, 'admin', 'admin', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', 'info', '985388d6d259de72a9c43525c1b248a42b1e49844bf0d3dece5619adc9c45e6e', 'e0b06ff32440637e86b9a27e5aa71b9b3299629aff255eae189095e9e357cbc0'),
(116, '2026-11-03 20:14:32', 1, 'admin', 'admin', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', 'info', 'e0b06ff32440637e86b9a27e5aa71b9b3299629aff255eae189095e9e357cbc0', '2d6ab1369f39af70038c8490b73982c150a33abc8e65b178a9dbbae9bd556f2f'),
(117, '2026-11-03 20:14:37', 1, 'admin', 'admin', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', 'info', '2d6ab1369f39af70038c8490b73982c150a33abc8e65b178a9dbbae9bd556f2f', 'daa34d91c93f339f185cc31e115750d3594efad127709d543ab5c9bc701a6abc'),
(118, '2026-11-03 20:14:42', 1, 'admin', 'admin', 'login_fallido', 'fallo', '', 'motivo=credenciales', '::1', 'info', 'daa34d91c93f339f185cc31e115750d3594efad127709d543ab5c9bc701a6abc', '6e38439057763fe2ded42d66b796118542cc76cb355b7ea8906c8e9da50e3ba2'),
(119, '2026-11-03 20:14:50', NULL, 'admin', '', 'login_bloqueado', 'denegado', '', 'Demasiados intentos fallidos', '::1', 'alerta', '6e38439057763fe2ded42d66b796118542cc76cb355b7ea8906c8e9da50e3ba2', '3782d47d58ca5f97c4beeb9d6e16e575ccdb35f9a14e4b73792d46aa9f358297'),
(120, '2026-11-13 16:17:53', 1, 'admin', 'admin', 'login_ok', 'ok', '', '', '::1', 'info', '3782d47d58ca5f97c4beeb9d6e16e575ccdb35f9a14e4b73792d46aa9f358297', 'df2193bb067f322834c190ca1ade29fccc79c8e1d5bae5481446e568539e13ae'),
(121, '2026-11-13 16:18:46', 1, 'admin', 'admin', 'integridad_verificada', 'ok', '', '{\"cadena_ok\":false,\"alteradas\":0,\"eliminadas\":0}', '::1', 'critica', 'df2193bb067f322834c190ca1ade29fccc79c8e1d5bae5481446e568539e13ae', '085bc6da99048ecbc8aabde5d02e295ab17ba823c6214a7795a8999cb04fe2dd');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cambios_rol`
--

CREATE TABLE `cambios_rol` (
  `id` int(11) NOT NULL,
  `id_identidad` int(11) NOT NULL,
  `rol_anterior` varchar(20) NOT NULL,
  `rol_nuevo` varchar(20) NOT NULL,
  `autorizado_por` int(11) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `momento` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion`
--

CREATE TABLE `configuracion` (
  `clave` varchar(40) NOT NULL,
  `valor` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion`
--

INSERT INTO `configuracion` (`clave`, `valor`) VALUES
('bloqueo_minutos', '15'),
('inactividad_minutos', '15'),
('max_intentos', '5'),
('modo_sistema', 'normal'),
('sesion_maxima_horas', '8');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes` (
  `id_docente` int(11) NOT NULL,
  `ci` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `docentes`
--

INSERT INTO `docentes` (`id_docente`, `ci`, `nombres`, `apellidos`, `usuario`, `password`) VALUES
(1, 'DEMO-CI-01', 'Ana', 'Mendoza', 'demo.ana', '!migrado'),
(2, 'DEMO-CI-02', 'Carlos', 'Rivera', 'demo.carlos', '!migrado'),
(3, 'DEMO-CI-03', 'Elena', 'Vargas', 'demo.elena', '!migrado');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `re` varchar(20) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `curso` varchar(50) NOT NULL,
  `fecha_nacimiento` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `estudiantes`
--

INSERT INTO `estudiantes` (`re`, `nombres`, `apellidos`, `curso`, `fecha_nacimiento`) VALUES
('DEMO-01', 'Valeria', 'Mamani Flores', '6to Secundaria A', '2009-03-14'),
('DEMO-02', 'Diego', 'Quispe Vargas', '6to Secundaria A', '2009-08-22'),
('DEMO-03', 'Camila', 'Condori Rojas', '5to Secundaria A', '2010-02-05'),
('DEMO-04', 'Mateo', 'Vargas Choque', '5to Secundaria A', '2010-11-18'),
('DEMO-05', 'Lucía', 'Flores Apaza', '6to Secundaria B', '2009-06-10'),
('DEMO-06', 'Santiago', 'Rojas Lima', '6to Secundaria B', '2009-12-03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `identidades`
--

CREATE TABLE `identidades` (
  `id_identidad` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `nombre_completo` varchar(200) NOT NULL,
  `rol` enum('admin','auditor','docente','coordinador','estudiante') NOT NULL,
  `ref_id` varchar(20) NOT NULL COMMENT 'id_admin / id_docente / re del perfil',
  `password_hash` varchar(255) NOT NULL,
  `estado` enum('activa','suspendida','revocada') NOT NULL DEFAULT 'activa',
  `debe_cambiar_password` tinyint(1) NOT NULL DEFAULT 0,
  `es_emergencia` tinyint(1) NOT NULL DEFAULT 0,
  `vigente_hasta` datetime DEFAULT NULL,
  `mfa_secreto` varchar(255) DEFAULT NULL,
  `mfa_activo` tinyint(1) NOT NULL DEFAULT 0,
  `mfa_ultimo_paso` bigint(20) NOT NULL DEFAULT 0,
  `creada_en` datetime NOT NULL,
  `estado_cambiado_en` datetime DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `identidades`
--

INSERT INTO `identidades` (`id_identidad`, `usuario`, `nombre_completo`, `rol`, `ref_id`, `password_hash`, `estado`, `debe_cambiar_password`, `es_emergencia`, `vigente_hasta`, `mfa_secreto`, `mfa_activo`, `mfa_ultimo_paso`, `creada_en`, `estado_cambiado_en`, `ultimo_login`) VALUES
(1, 'admin', 'Admin Sistema', 'admin', '2', '$argon2id$v=19$m=65536,t=3,p=1$SFg5UlpsYmpYaW16LjdKcw$2MobD39haDPj9dvDuYzMepcjDIGvE2ryyS5Nm2/xZUk', 'activa', 0, 0, NULL, '24IwlgDdXfvhhxrkKGlz0CxDoq3Vn1Eow36aEVx2FGcQtuzYTC/2JWyJztkWE/npGRsSXA9+LphxcY3j', 1, 59766129, '2026-10-25 19:38:14', NULL, '2026-11-13 16:17:53'),
(2, 'demo.ana', 'Ana Mendoza', 'docente', '1', '$argon2id$v=19$m=65536,t=3,p=1$R0d4bGt1c212UlNkWWN2Lg$WRIooFQq40FA6Y6VWH3B0jpEwbRZaH5v4HuoUR/Cn2o', 'activa', 0, 0, NULL, NULL, 0, 0, '2026-10-25 19:38:14', NULL, '2026-11-03 16:50:31'),
(3, 'demo.carlos', 'Carlos Rivera', 'docente', '2', '$2y$12$yQ3IvaYklUG337Ke19pZ/u9cAW8g/cAmBrTREeg63Etf5qDv3sNG2', 'activa', 1, 0, NULL, NULL, 0, 0, '2026-10-25 19:38:14', NULL, NULL),
(4, 'demo.elena', 'Elena Vargas', 'docente', '3', '$2y$12$yQ3IvaYklUG337Ke19pZ/u9cAW8g/cAmBrTREeg63Etf5qDv3sNG2', 'activa', 1, 0, NULL, NULL, 0, 0, '2026-10-25 19:38:14', NULL, NULL),
(5, 'DEMO-01', 'Valeria Mamani Flores', 'estudiante', 'DEMO-01', '$argon2id$v=19$m=65536,t=3,p=1$RGgybXBkN2pKeEQuUHF2TA$7SkCIXJ1QFbetVvtW4dn0bB3lf5jhIyhx50OdlDEccY', 'activa', 0, 0, NULL, NULL, 0, 0, '2026-10-25 19:38:14', NULL, '2026-11-03 17:05:16'),
(6, 'DEMO-02', 'Diego Quispe Vargas', 'estudiante', 'DEMO-02', '$argon2id$v=19$m=65536,t=3,p=1$MklkS2t5YnFYT2FYSjhibA$J0lHow4UV3eN1eLceF6HH+PjzdtN+JLA8Zfs93MdzzY', 'activa', 0, 0, NULL, NULL, 0, 0, '2026-10-25 19:38:14', NULL, '2026-11-03 17:11:28'),
(7, 'DEMO-03', 'Camila Condori Rojas', 'estudiante', 'DEMO-03', '$argon2id$v=19$m=65536,t=3,p=1$cVo5SDViTmZYbG9kMG9Beg$KQVIv4f78Uz8FPmpu6tFMNePQqXJH99xlG6W3SLIuoY', 'activa', 1, 0, NULL, NULL, 0, 0, '2026-10-25 19:38:14', NULL, NULL),
(8, 'DEMO-04', 'Mateo Vargas Choque', 'estudiante', 'DEMO-04', '$argon2id$v=19$m=65536,t=3,p=1$TUk4aVlGQkVDc0RMQW0yag$lTtF9TVEYfbcfCtv/GfMX/YJawH9ESijrgNwJcWWlok', 'activa', 1, 0, NULL, NULL, 0, 0, '2026-10-25 19:38:15', NULL, NULL),
(9, 'DEMO-05', 'Lucía Flores Apaza', 'estudiante', 'DEMO-05', '$argon2id$v=19$m=65536,t=3,p=1$UldBdnNZWS9sSVExMXh0Sg$krtzbGpJYEdAX8QIDq+GMCZPb9im0+WGf9ID97e9qIA', 'activa', 1, 0, NULL, NULL, 0, 0, '2026-10-25 19:38:15', NULL, NULL),
(10, 'DEMO-06', 'Santiago Rojas Lima', 'estudiante', 'DEMO-06', '$argon2id$v=19$m=65536,t=3,p=1$MUpxYXlDNVhSZVhyenVuLg$1pNbWrH/+BfoVexl+cGeA3hIdE9WU+yAC+8Xbtc6qfk', 'activa', 1, 0, NULL, NULL, 0, 0, '2026-10-25 19:38:15', NULL, NULL),
(11, 'emergencia', 'Cuenta de Emergencia', 'admin', '3', '$argon2id$v=19$m=65536,t=3,p=1$MXdVaXR3OEd6R28yTldEVQ$/4utyNx/Reisz2bLZkfgbDfRS8FEIs6dnlMTwjdShdc', 'suspendida', 0, 1, NULL, NULL, 0, 0, '2026-10-25 19:38:16', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `intentos_login`
--

CREATE TABLE `intentos_login` (
  `id` bigint(20) NOT NULL,
  `usuario` varchar(80) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `momento` datetime NOT NULL,
  `exito` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `kardex`
--

CREATE TABLE `kardex` (
  `id_kardex` int(11) NOT NULL,
  `re_estudiante` varchar(20) NOT NULL,
  `id_materia` int(11) NOT NULL,
  `id_docente` int(11) NOT NULL,
  `trimestre` int(11) NOT NULL,
  `nota` decimal(5,2) NOT NULL,
  `observacion` varchar(255) DEFAULT 'Sin observaciones',
  `sello` char(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `kardex`
--

INSERT INTO `kardex` (`id_kardex`, `re_estudiante`, `id_materia`, `id_docente`, `trimestre`, `nota`, `observacion`, `sello`) VALUES
(1, 'DEMO-01', 1, 1, 1, 85.00, 'Evaluación de demostración', '5d6affc81edb975f910a47f14dd9fdd42eb890734a602a74f29f35dfb18a3269'),
(2, 'DEMO-01', 1, 1, 2, 90.00, 'Evaluación de demostración', 'f8926bf873fda0061426811ac2487c40c92bc696065e29ee9c43d7c29fa0f11e'),
(3, 'DEMO-01', 1, 1, 3, 45.00, 'Evaluación de demostración', '76c4a3d003bdfd9b30461fb9707e19c5071e27dfdb2b491db2815a00181eb7e7'),
(4, 'DEMO-01', 3, 2, 1, 90.00, 'Evaluación de demostración', 'dd778a198fe983612c591a29684ffbda46f766be535f85597e7dc8d815963409'),
(5, 'DEMO-01', 3, 2, 2, 45.00, 'Evaluación de demostración', '4d592c04c4559c3039458e6bbcf90ba9ea0b3b44d2131e627e88a8dac7ac82d8'),
(6, 'DEMO-01', 3, 2, 3, 63.00, 'Evaluación de demostración', '96b7a762b50b123e3ff994f68931f3010304e01e0d25c6ff276c48e64bfe2f8f'),
(7, 'DEMO-01', 5, 3, 1, 45.00, 'Evaluación de demostración', '50ac72683584600968b725c48567b0b04487fe765e2d968196553b3f483b6551'),
(8, 'DEMO-01', 5, 3, 2, 63.00, 'Evaluación de demostración', '8ef55875d7527e84fc31d910a587109114b5c174496a80f02413045921d46028'),
(9, 'DEMO-01', 5, 3, 3, 70.00, 'Evaluación de demostración', '36e1117c337a8472556325a34f3bc2c6cf3c5e2324732ceb0952028f6151bb5b'),
(10, 'DEMO-02', 1, 1, 1, 45.00, 'Evaluación de demostración', 'd03f27a64fc84399ec7c8c244b4de59cf2b73956b4f6df27173a17cf31417386'),
(11, 'DEMO-02', 1, 1, 2, 63.00, 'Evaluación de demostración', 'f364b3a608852a8f24b4d986bfe716d9af4a5a3d79f0326dbb3ebfb31c86d7b1'),
(12, 'DEMO-02', 1, 1, 3, 70.00, 'Evaluación de demostración', '8809238e8c6ab1c36645cd5ae963cc3faedb676dc4c75f1632dc81eb6d0ffdc7'),
(13, 'DEMO-02', 3, 2, 1, 63.00, 'Evaluación de demostración', '7777aff8f43a4271f36776ee651a8795d15c853ab8c884a1813dcd57c2751b21'),
(14, 'DEMO-02', 3, 2, 2, 70.00, 'Evaluación de demostración', 'aed33a4fca23a8ce03bf3cd2c3287d8d1eaa4f39f4c3a954d21f5b7adb37037a'),
(15, 'DEMO-02', 3, 2, 3, 92.00, 'Evaluación de demostración', '006cd406cfe53262852c372dbd6e063bbed4852fafbd6758dc6e1407a0f14fa0'),
(16, 'DEMO-02', 5, 3, 1, 70.00, 'Evaluación de demostración', '4f45028bdf4d5ea361a8b62c54e088e1cd04cb95ce115b36b0f3e892ff0a6070'),
(17, 'DEMO-02', 5, 3, 2, 92.00, 'Evaluación de demostración', '202f6a5fe1dd0d1dcfffea169fc9e7f1891baaadcf10d643d5b059e56990f28d'),
(18, 'DEMO-02', 5, 3, 3, 88.00, 'Evaluación de demostración', '0b2a3d2957d847356d44fdc106f09a4015bd58f6818ebe6817aed732f2e978c6'),
(19, 'DEMO-03', 1, 1, 1, 70.00, 'Evaluación de demostración', '86d42163549b38746588b9258fc754c77585f64547575f0f0bc2bf5909a58516'),
(20, 'DEMO-03', 1, 1, 2, 92.00, 'Evaluación de demostración', '589b9980b10f8ef5442b1c9a95689ccfb71421c41ded34d02335fd01c3177e8f'),
(21, 'DEMO-03', 3, 2, 1, 92.00, 'Evaluación de demostración', '5f865f60eab6f93d5c3dfa1369ae11034b3e8ac76640299310bbf8ca1daa985a'),
(22, 'DEMO-03', 3, 2, 2, 88.00, 'Evaluación de demostración', 'a1033a50df76172d2a42b505bae924b826da27a6251f788041eec5434b31f590'),
(23, 'DEMO-03', 5, 3, 1, 88.00, 'Evaluación de demostración', '9929dff779962774eaded7484e0da883d007768f2d7f918f7a3770256ce3abe3'),
(24, 'DEMO-03', 5, 3, 2, 95.00, 'Evaluación de demostración', 'df5e6dea00d67b17351a1a1a4129cf1857f6e4d3e71447d4ba4ddd2bf63ad5a6'),
(25, 'DEMO-04', 1, 1, 1, 88.00, 'Evaluación de demostración', '9e86f61ad9212c3e7a338751be42abba509330d0c75407eb7aac2822dc4e2729'),
(26, 'DEMO-04', 1, 1, 2, 95.00, 'Evaluación de demostración', 'd2845d718e6aa4e3e3229a8de97aa1ac763d10d2062db051af21b22e66c00fa1'),
(27, 'DEMO-04', 3, 2, 1, 95.00, 'Evaluación de demostración', '460bbacb6d461a74b8dec7a9ecfb2e74f6b2d54f9c6e0e8e81f5826847cc56f8'),
(28, 'DEMO-04', 3, 2, 2, 78.00, 'Evaluación de demostración', '2ced96fa53979e2ae1ef9e2729bd8eeca223865766b2d645d0ca9761152b0fa6'),
(29, 'DEMO-04', 5, 3, 1, 78.00, 'Evaluación de demostración', 'b9b5b70eae08e8ba7d54c04d32ba6b6e7346da6c79cc68defb8b7e0b05f6c43b'),
(30, 'DEMO-04', 5, 3, 2, 85.00, 'Evaluación de demostración', 'be5905278f290d1cb7bbdd8ff5c1ab2bae54dad52c7f24a7ad2395685d7c45db'),
(31, 'DEMO-05', 1, 1, 1, 78.00, 'Evaluación de demostración', '502dba1e8fa74ae091636f7b2f1e76970e32dab8212c5c5521ae1adbad79acbc'),
(32, 'DEMO-05', 1, 1, 2, 85.00, 'Evaluación de demostración', '702c068fd85ceb61ff8902aea7c7185d059083e9215ddceccdeb4e29a565f587'),
(33, 'DEMO-05', 3, 2, 1, 85.00, 'Evaluación de demostración', 'c3373ccd8f650ad9d2816dada4adb39eaa37355a00e87417e807ff5037f7b86c'),
(34, 'DEMO-05', 3, 2, 2, 90.00, 'Evaluación de demostración', '2a11e4aa29edd8437bfeee25f411d8d79daf2b4fbba876d5790c069114879937'),
(35, 'DEMO-05', 5, 3, 1, 90.00, 'Evaluación de demostración', 'd5e84ab4e9d5931733457c6fec94a3f05bdaa3329f43ae6420335f2b55fd40d0'),
(36, 'DEMO-05', 5, 3, 2, 45.00, 'Evaluación de demostración', 'b72ec450c473fa3b93893758fd58bc443ac5c5f253a72321f07f59c8f5a08852'),
(37, 'DEMO-06', 1, 1, 1, 90.00, 'Evaluación de demostración', '25d68f84794be69f8d09e998578c86ba45d98b4e5699fc3583d18dc17918525d'),
(38, 'DEMO-06', 1, 1, 2, 45.00, 'Evaluación de demostración', '07ae6b2a36a58a42f1a5229b3e3ddae4d654dac6e679173595350e513d3474b4'),
(39, 'DEMO-06', 3, 2, 1, 45.00, 'Evaluación de demostración', '6bd7a281172778545e51fe0143b86d3153174cba694fdf8a4cc836cec1114829'),
(40, 'DEMO-06', 3, 2, 2, 63.00, 'Evaluación de demostración', '6b59f95dba64a1a6a970dc2988d13dd02b63e741f8de998619e65a79b41c363d'),
(41, 'DEMO-06', 5, 3, 1, 63.00, 'Evaluación de demostración', 'a5a96bf6884dfd943e299869d19c8298e4e6be266b01aea8d372fcdf1f5c2bce'),
(42, 'DEMO-06', 5, 3, 2, 70.00, 'Evaluación de demostración', '30c30448432d193da0561aa703e6e85f95ab7ad0d9a193be60b92d195af66acc');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `kardex_historial`
--

CREATE TABLE `kardex_historial` (
  `id` bigint(20) NOT NULL,
  `id_kardex` int(11) NOT NULL,
  `accion` enum('crear','editar','eliminar','restaurar','migracion') NOT NULL,
  `datos_antes` text DEFAULT NULL,
  `datos_despues` text DEFAULT NULL,
  `id_identidad` int(11) DEFAULT NULL,
  `momento` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `kardex_historial`
--

INSERT INTO `kardex_historial` (`id`, `id_kardex`, `accion`, `datos_antes`, `datos_despues`, `id_identidad`, `momento`) VALUES
(1, 1, 'migracion', NULL, '{\"id_kardex\":1,\"re_estudiante\":\"DEMO-01\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":1,\"nota\":\"85.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(2, 2, 'migracion', NULL, '{\"id_kardex\":2,\"re_estudiante\":\"DEMO-01\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":2,\"nota\":\"90.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(3, 3, 'migracion', NULL, '{\"id_kardex\":3,\"re_estudiante\":\"DEMO-01\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":3,\"nota\":\"45.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(4, 4, 'migracion', NULL, '{\"id_kardex\":4,\"re_estudiante\":\"DEMO-01\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":1,\"nota\":\"90.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(5, 5, 'migracion', NULL, '{\"id_kardex\":5,\"re_estudiante\":\"DEMO-01\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":2,\"nota\":\"45.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(6, 6, 'migracion', NULL, '{\"id_kardex\":6,\"re_estudiante\":\"DEMO-01\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":3,\"nota\":\"63.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(7, 7, 'migracion', NULL, '{\"id_kardex\":7,\"re_estudiante\":\"DEMO-01\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":1,\"nota\":\"45.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(8, 8, 'migracion', NULL, '{\"id_kardex\":8,\"re_estudiante\":\"DEMO-01\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":2,\"nota\":\"63.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(9, 9, 'migracion', NULL, '{\"id_kardex\":9,\"re_estudiante\":\"DEMO-01\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":3,\"nota\":\"70.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(10, 10, 'migracion', NULL, '{\"id_kardex\":10,\"re_estudiante\":\"DEMO-02\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":1,\"nota\":\"45.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(11, 11, 'migracion', NULL, '{\"id_kardex\":11,\"re_estudiante\":\"DEMO-02\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":2,\"nota\":\"63.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(12, 12, 'migracion', NULL, '{\"id_kardex\":12,\"re_estudiante\":\"DEMO-02\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":3,\"nota\":\"70.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(13, 13, 'migracion', NULL, '{\"id_kardex\":13,\"re_estudiante\":\"DEMO-02\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":1,\"nota\":\"63.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(14, 14, 'migracion', NULL, '{\"id_kardex\":14,\"re_estudiante\":\"DEMO-02\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":2,\"nota\":\"70.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(15, 15, 'migracion', NULL, '{\"id_kardex\":15,\"re_estudiante\":\"DEMO-02\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":3,\"nota\":\"92.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(16, 16, 'migracion', NULL, '{\"id_kardex\":16,\"re_estudiante\":\"DEMO-02\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":1,\"nota\":\"70.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(17, 17, 'migracion', NULL, '{\"id_kardex\":17,\"re_estudiante\":\"DEMO-02\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":2,\"nota\":\"92.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(18, 18, 'migracion', NULL, '{\"id_kardex\":18,\"re_estudiante\":\"DEMO-02\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":3,\"nota\":\"88.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(19, 19, 'migracion', NULL, '{\"id_kardex\":19,\"re_estudiante\":\"DEMO-03\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":1,\"nota\":\"70.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(20, 20, 'migracion', NULL, '{\"id_kardex\":20,\"re_estudiante\":\"DEMO-03\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":2,\"nota\":\"92.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(21, 21, 'migracion', NULL, '{\"id_kardex\":21,\"re_estudiante\":\"DEMO-03\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":1,\"nota\":\"92.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(22, 22, 'migracion', NULL, '{\"id_kardex\":22,\"re_estudiante\":\"DEMO-03\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":2,\"nota\":\"88.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(23, 23, 'migracion', NULL, '{\"id_kardex\":23,\"re_estudiante\":\"DEMO-03\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":1,\"nota\":\"88.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(24, 24, 'migracion', NULL, '{\"id_kardex\":24,\"re_estudiante\":\"DEMO-03\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":2,\"nota\":\"95.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(25, 25, 'migracion', NULL, '{\"id_kardex\":25,\"re_estudiante\":\"DEMO-04\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":1,\"nota\":\"88.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(26, 26, 'migracion', NULL, '{\"id_kardex\":26,\"re_estudiante\":\"DEMO-04\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":2,\"nota\":\"95.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(27, 27, 'migracion', NULL, '{\"id_kardex\":27,\"re_estudiante\":\"DEMO-04\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":1,\"nota\":\"95.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(28, 28, 'migracion', NULL, '{\"id_kardex\":28,\"re_estudiante\":\"DEMO-04\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":2,\"nota\":\"78.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(29, 29, 'migracion', NULL, '{\"id_kardex\":29,\"re_estudiante\":\"DEMO-04\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":1,\"nota\":\"78.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(30, 30, 'migracion', NULL, '{\"id_kardex\":30,\"re_estudiante\":\"DEMO-04\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":2,\"nota\":\"85.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(31, 31, 'migracion', NULL, '{\"id_kardex\":31,\"re_estudiante\":\"DEMO-05\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":1,\"nota\":\"78.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(32, 32, 'migracion', NULL, '{\"id_kardex\":32,\"re_estudiante\":\"DEMO-05\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":2,\"nota\":\"85.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(33, 33, 'migracion', NULL, '{\"id_kardex\":33,\"re_estudiante\":\"DEMO-05\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":1,\"nota\":\"85.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(34, 34, 'migracion', NULL, '{\"id_kardex\":34,\"re_estudiante\":\"DEMO-05\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":2,\"nota\":\"90.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(35, 35, 'migracion', NULL, '{\"id_kardex\":35,\"re_estudiante\":\"DEMO-05\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":1,\"nota\":\"90.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(36, 36, 'migracion', NULL, '{\"id_kardex\":36,\"re_estudiante\":\"DEMO-05\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":2,\"nota\":\"45.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(37, 37, 'migracion', NULL, '{\"id_kardex\":37,\"re_estudiante\":\"DEMO-06\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":1,\"nota\":\"90.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(38, 38, 'migracion', NULL, '{\"id_kardex\":38,\"re_estudiante\":\"DEMO-06\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":2,\"nota\":\"45.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(39, 39, 'migracion', NULL, '{\"id_kardex\":39,\"re_estudiante\":\"DEMO-06\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":1,\"nota\":\"45.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(40, 40, 'migracion', NULL, '{\"id_kardex\":40,\"re_estudiante\":\"DEMO-06\",\"id_materia\":3,\"id_docente\":2,\"trimestre\":2,\"nota\":\"63.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(41, 41, 'migracion', NULL, '{\"id_kardex\":41,\"re_estudiante\":\"DEMO-06\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":1,\"nota\":\"63.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(42, 42, 'migracion', NULL, '{\"id_kardex\":42,\"re_estudiante\":\"DEMO-06\",\"id_materia\":5,\"id_docente\":3,\"trimestre\":2,\"nota\":\"70.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', NULL, '2026-10-25 19:38:16'),
(43, 10, 'editar', '{\"id_kardex\":10,\"re_estudiante\":\"DEMO-02\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":1,\"nota\":\"45.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', '{\"id_kardex\":10,\"re_estudiante\":\"DEMO-02\",\"id_materia\":1,\"id_docente\":1,\"trimestre\":1,\"nota\":\"45.00\",\"observacion\":\"Evaluaci\\u00f3n de demostraci\\u00f3n\"}', 2, '2026-10-25 22:31:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materias`
--

CREATE TABLE `materias` (
  `id_materia` int(11) NOT NULL,
  `nombre_materia` varchar(100) NOT NULL,
  `id_docente` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `materias`
--

INSERT INTO `materias` (`id_materia`, `nombre_materia`, `id_docente`) VALUES
(1, 'Matemática', 1),
(2, 'Física', 1),
(3, 'Lenguaje', 2),
(4, 'Ciencias Sociales', 2),
(5, 'Biología', 3),
(6, 'Química', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materia_cursos`
--

CREATE TABLE `materia_cursos` (
  `id_materia` int(11) NOT NULL,
  `curso` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `materia_cursos`
--

INSERT INTO `materia_cursos` (`id_materia`, `curso`) VALUES
(1, '5to Secundaria A'),
(1, '6to Secundaria A'),
(1, '6to Secundaria B'),
(3, '5to Secundaria A'),
(3, '6to Secundaria A'),
(3, '6to Secundaria B'),
(5, '5to Secundaria A'),
(5, '6to Secundaria A'),
(5, '6to Secundaria B');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `rol` varchar(20) NOT NULL,
  `operacion` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`rol`, `operacion`) VALUES
('admin', 'auditoria.ver'),
('admin', 'config.cambiar'),
('admin', 'docente.baja'),
('admin', 'docente.crear'),
('admin', 'docente.editar'),
('admin', 'docente.ver'),
('admin', 'estudiante.baja'),
('admin', 'estudiante.crear'),
('admin', 'estudiante.editar'),
('admin', 'estudiante.ver'),
('admin', 'identidad.cambiar_rol'),
('admin', 'identidad.gestionar'),
('admin', 'integridad.restaurar'),
('admin', 'integridad.verificar'),
('admin', 'kardex.ver'),
('admin', 'materia.crear'),
('admin', 'materia.editar'),
('admin', 'materia.eliminar'),
('admin', 'materia.ver'),
('admin', 'mfa.configurar'),
('admin', 'nota.crear'),
('admin', 'nota.editar'),
('admin', 'nota.eliminar'),
('admin', 'nota.ver'),
('admin', 'panel.ver'),
('auditor', 'auditoria.ver'),
('auditor', 'estudiante.ver'),
('auditor', 'integridad.verificar'),
('auditor', 'kardex.ver'),
('auditor', 'nota.ver'),
('coordinador', 'estudiante.ver'),
('coordinador', 'kardex.ver'),
('coordinador', 'nota.ver'),
('docente', 'estudiante.ver'),
('docente', 'kardex.ver'),
('docente', 'materia.propias'),
('docente', 'mfa.configurar'),
('docente', 'nota.crear'),
('docente', 'nota.editar'),
('docente', 'nota.eliminar'),
('docente', 'nota.ver'),
('estudiante', 'kardex.ver');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `id_sesion` char(64) NOT NULL,
  `id_identidad` int(11) NOT NULL,
  `creada_en` datetime NOT NULL,
  `ultima_actividad` datetime NOT NULL,
  `expira_en` datetime NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `revocada` tinyint(1) NOT NULL DEFAULT 0,
  `motivo_revocacion` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sesiones`
--

INSERT INTO `sesiones` (`id_sesion`, `id_identidad`, `creada_en`, `ultima_actividad`, `expira_en`, `ip`, `user_agent`, `revocada`, `motivo_revocacion`) VALUES
('ce66a54f87602c1060a04c1bbfd7b97ee679f530093139d477b87118e71affcb', 1, '2026-11-13 16:17:53', '2026-11-13 16:19:04', '2026-11-14 00:17:53', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', 0, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `ci` (`ci`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_aud_evento` (`evento`,`momento`),
  ADD KEY `idx_aud_ident` (`id_identidad`);

--
-- Indices de la tabla `cambios_rol`
--
ALTER TABLE `cambios_rol`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_identidad` (`id_identidad`);

--
-- Indices de la tabla `configuracion`
--
ALTER TABLE `configuracion`
  ADD PRIMARY KEY (`clave`);

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD PRIMARY KEY (`id_docente`),
  ADD UNIQUE KEY `ci` (`ci`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`re`);

--
-- Indices de la tabla `identidades`
--
ALTER TABLE `identidades`
  ADD PRIMARY KEY (`id_identidad`),
  ADD UNIQUE KEY `uq_ident_usuario` (`usuario`),
  ADD KEY `idx_ident_ref` (`rol`,`ref_id`);

--
-- Indices de la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_int_usuario` (`usuario`,`momento`),
  ADD KEY `idx_int_ip` (`ip`,`momento`);

--
-- Indices de la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD PRIMARY KEY (`id_kardex`),
  ADD UNIQUE KEY `uq_kardex_est_mat_trim` (`re_estudiante`,`id_materia`,`trimestre`),
  ADD KEY `re_estudiante` (`re_estudiante`),
  ADD KEY `id_materia` (`id_materia`),
  ADD KEY `id_docente` (`id_docente`);

--
-- Indices de la tabla `kardex_historial`
--
ALTER TABLE `kardex_historial`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_kardex` (`id_kardex`);

--
-- Indices de la tabla `materias`
--
ALTER TABLE `materias`
  ADD PRIMARY KEY (`id_materia`),
  ADD UNIQUE KEY `uq_materias_nombre` (`nombre_materia`),
  ADD KEY `id_docente` (`id_docente`);

--
-- Indices de la tabla `materia_cursos`
--
ALTER TABLE `materia_cursos`
  ADD PRIMARY KEY (`id_materia`,`curso`),
  ADD KEY `idx_materia_cursos_curso` (`curso`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`rol`,`operacion`);

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`id_sesion`),
  ADD KEY `id_identidad` (`id_identidad`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT de la tabla `cambios_rol`
--
ALTER TABLE `cambios_rol`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `docentes`
--
ALTER TABLE `docentes`
  MODIFY `id_docente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `identidades`
--
ALTER TABLE `identidades`
  MODIFY `id_identidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `intentos_login`
--
ALTER TABLE `intentos_login`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `kardex`
--
ALTER TABLE `kardex`
  MODIFY `id_kardex` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- AUTO_INCREMENT de la tabla `kardex_historial`
--
ALTER TABLE `kardex_historial`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT de la tabla `materias`
--
ALTER TABLE `materias`
  MODIFY `id_materia` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `kardex`
--
ALTER TABLE `kardex`
  ADD CONSTRAINT `kardex_ibfk_1` FOREIGN KEY (`re_estudiante`) REFERENCES `estudiantes` (`re`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `kardex_ibfk_2` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE,
  ADD CONSTRAINT `kardex_ibfk_3` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON DELETE CASCADE;

--
-- Filtros para la tabla `materias`
--
ALTER TABLE `materias`
  ADD CONSTRAINT `materias_ibfk_1` FOREIGN KEY (`id_docente`) REFERENCES `docentes` (`id_docente`) ON DELETE SET NULL;

--
-- Filtros para la tabla `materia_cursos`
--
ALTER TABLE `materia_cursos`
  ADD CONSTRAINT `fk_materia_cursos_materia` FOREIGN KEY (`id_materia`) REFERENCES `materias` (`id_materia`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD CONSTRAINT `fk_ses_ident` FOREIGN KEY (`id_identidad`) REFERENCES `identidades` (`id_identidad`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
