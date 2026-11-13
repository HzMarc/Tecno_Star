-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3308
-- Tiempo de generación: 06-11-2026 a las 03:25:58
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
-- Base de datos: `tecno_star`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `google_module_migrations`
--

CREATE TABLE `google_module_migrations` (
  `version` varchar(150) NOT NULL,
  `checksum` char(64) NOT NULL,
  `status` varchar(20) NOT NULL,
  `applied_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `google_module_migrations`
--

INSERT INTO `google_module_migrations` (`version`, `checksum`, `status`, `applied_at`) VALUES


('009_google_login.sql', '46d93b3f7d3bb1309f13d92225ed3b9e7ff05850783ee8a16edfe2c09e648184', 'applied', '2026-10-22 07:11:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inventory_module_migrations`
--

CREATE TABLE `inventory_module_migrations` (
  `version` varchar(150) NOT NULL,
  `checksum` char(64) NOT NULL,
  `status` varchar(20) NOT NULL,
  `applied_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `inventory_module_migrations`
--

INSERT INTO `inventory_module_migrations` (`version`, `checksum`, `status`, `applied_at`) VALUES


('003_inventory_movements.sql', 'c4ed09e765319461bd594b32ce192d0dd745e33358afd66e75399b38ee1447c1', 'applied', '2026-10-17 11:31:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `orders_module_migrations`
--

CREATE TABLE `orders_module_migrations` (
  `version` varchar(150) NOT NULL,
  `checksum` char(64) NOT NULL,
  `status` varchar(20) NOT NULL,
  `applied_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `orders_module_migrations`
--

INSERT INTO `orders_module_migrations` (`version`, `checksum`, `status`, `applied_at`) VALUES


('005_pickup_orders.sql', '3580064432ada2739e1ce79d76f1b5488801c9e10bafb964831f8f8b24e3df2d', 'applied', '2026-10-18 23:21:42');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ready_mail_module_migrations`
--

CREATE TABLE `ready_mail_module_migrations` (
  `version` varchar(150) NOT NULL,
  `checksum` char(64) NOT NULL,
  `status` varchar(20) NOT NULL,
  `applied_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ready_mail_module_migrations`
--

INSERT INTO `ready_mail_module_migrations` (`version`, `checksum`, `status`, `applied_at`) VALUES


('008_ready_notifications.sql', 'b608f02ce5d3a986de5c3cb4b0817b12d2148b8b27d906d199d5b731dee175d3', 'applied', '2026-10-20 04:39:51');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reinvestment_module_migrations`
--

CREATE TABLE `reinvestment_module_migrations` (
  `version` varchar(150) NOT NULL,
  `checksum` char(64) NOT NULL,
  `status` varchar(20) NOT NULL,
  `applied_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reinvestment_module_migrations`
--

INSERT INTO `reinvestment_module_migrations` (`version`, `checksum`, `status`, `applied_at`) VALUES


('007_reinvestment.sql', 'fdff3c20bdf568b986b08bc4c1a0f73c581b50425e9d576294426186617fb559', 'applied', '2026-10-20 03:56:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sales_accounts_module_migrations`
--

CREATE TABLE `sales_accounts_module_migrations` (
  `version` varchar(150) NOT NULL,
  `checksum` char(64) NOT NULL,
  `status` varchar(20) NOT NULL,
  `applied_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `sales_accounts_module_migrations`
--

INSERT INTO `sales_accounts_module_migrations` (`version`, `checksum`, `status`, `applied_at`) VALUES


('006_sales_accounts.sql', '67cdc0ba8b24e558b1b0d73824af3edcff102a0c6989678164194ca3bbddf818', 'applied', '2026-10-19 00:13:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `service_module_migrations`
--

CREATE TABLE `service_module_migrations` (
  `version` varchar(150) NOT NULL,
  `checksum` char(64) NOT NULL,
  `status` varchar(20) NOT NULL,
  `applied_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `service_module_migrations`
--

INSERT INTO `service_module_migrations` (`version`, `checksum`, `status`, `applied_at`) VALUES


('010_services.sql', 'd5b72f61a19c742499315b00fbc79255398deb87179cfbdf54dcdc3ee68ca712', 'applied', '2026-10-31 17:14:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `shopping_module_migrations`
--

CREATE TABLE `shopping_module_migrations` (
  `version` varchar(150) NOT NULL,
  `checksum` char(64) NOT NULL,
  `status` varchar(20) NOT NULL,
  `applied_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `shopping_module_migrations`
--

INSERT INTO `shopping_module_migrations` (`version`, `checksum`, `status`, `applied_at`) VALUES


('004_shopping_consistency.sql', '401e9a43edd5e4ec6911deb32dda73958a6440053f8885a1c8c3f636147f5b39', 'applied', '2026-10-18 22:52:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcarritos`
--

CREATE TABLE `tbcarritos` (
  `id_carrito` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `estado` enum('activo','convertido','cancelado') NOT NULL DEFAULT 'activo',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `revision` bigint(20) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbcarritos`
--

INSERT INTO `tbcarritos` (`id_carrito`, `id_usuario`, `estado`, `created_at`, `updated_at`, `revision`) VALUES


(45, 3, 'activo', '2026-10-15 09:49:52', '2026-10-21 22:50:12', 21),
(47, 2, 'activo', '2026-10-19 00:26:09', '2026-10-19 00:26:09', 1),
(48, 4, 'activo', '2026-10-19 00:34:19', '2026-10-19 00:35:19', 5),
(66, 6, 'activo', '2026-10-22 10:05:21', '2026-10-22 10:16:20', 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcategorias`
--

CREATE TABLE `tbcategorias` (
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `nombre_categoria` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbcategorias`
--

INSERT INTO `tbcategorias` (`id_categoria`, `nombre_categoria`, `descripcion`, `estado`, `created_at`) VALUES


(1, 'Covers y protección para celular', 'Covers de silicón, magnéticos, acrílicos, OtterBox, Adventurer, dobles y de diferentes diseños.', 'activo', '2026-10-07 23:20:05'),
(2, 'Vidrios e hidrogel', 'Vidrios templados, oscuros, de cámara e hidrogel para celulares y tablets.', 'activo', '2026-10-07 23:20:05'),
(3, 'Cargadores y energía', 'Cargadores para iPhone, Samsung, cargadores inalámbricos, para carro y baterías portátiles.', 'activo', '2026-10-07 23:20:05'),
(4, 'Cables', 'Cables USB, USB a Tipo C, C a C, C a iPhone, HDMI y cables de poder.', 'activo', '2026-10-07 23:20:05'),
(5, 'Audio', 'Audífonos Bluetooth, gamer, con cable, para iPhone, tipo C, parlantes Bluetooth y bocinas.', 'activo', '2026-10-07 23:20:05'),
(6, 'Computación y oficina', 'Mouse, mouse inalámbrico, teclados, teclados inalámbricos, mousepad y tinta.', 'activo', '2026-10-07 23:20:05'),
(7, 'Gaming', 'Mouse gamer, teclados gamer, mousepad gamer, combos gamer y mandos para PC o PlayStation.', 'activo', '2026-10-07 23:20:05'),
(8, 'Adaptadores y conectividad', 'Adaptadores, adaptadores USB-C, Lightning y accesorios OTG.', 'activo', '2026-10-07 23:20:05'),
(9, 'Almacenamiento', 'Memorias USB y memorias SD para guardar y transferir información.', 'activo', '2026-10-07 23:20:05'),
(10, 'Seguridad y video', 'Cámaras de seguridad y webcams para monitoreo y videollamadas.', 'activo', '2026-10-07 23:20:05'),
(11, 'Limpieza y mantenimiento', 'Productos para limpieza y cuidado de pantallas y dispositivos.', 'activo', '2026-10-07 23:20:05'),
(12, 'Accesorios para celular', 'Accesorios generales para celular que no pertenecen a otra categoría específica.', 'activo', '2026-10-07 23:20:05'),
(13, 'prueba', NULL, 'activo', '2026-10-17 10:15:03'),
(14, 'Tecnologia', NULL, 'activo', '2026-10-22 10:11:15');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbclaverecuperacion`
--

CREATE TABLE `tbclaverecuperacion` (
  `id_recuperacion` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `token_hash` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `session_version` int(10) UNSIGNED NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;-- Los tokens de recuperación se omitieron en este volcado de demostración.


-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbclientes`
--

CREATE TABLE `tbclientes` (
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `correo_contacto` varchar(150) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbclientes`
--

INSERT INTO `tbclientes` (`id_cliente`, `id_usuario`, `nombres`, `apellidos`, `telefono`, `correo_contacto`, `estado`, `created_at`) VALUES


(2, 3, 'Cliente', 'Demo', '70000003', 'cliente@clinete.com', 'activo', '2026-10-08 00:35:33'),
(3, 4, 'Cuenta', 'Demo', '70000004', 'demo-user-4@tecno-star.test', 'activo', '2026-10-19 00:33:49'),
(4, 5, 'Cuenta', 'Demo', '70000005', 'demo-user-5@tecno-star.test', 'activo', '2026-10-22 01:18:33'),
(5, 6, 'Cuenta', 'Demo', '70000006', 'demo-user-6@tecno-star.test', 'activo', '2026-10-22 10:05:20');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbcuentashistorial`
--

CREATE TABLE `tbcuentashistorial` (
  `id_evento` bigint(20) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_actor` int(10) UNSIGNED DEFAULT NULL,
  `accion` varchar(40) NOT NULL,
  `detalle` varchar(500) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbdetallecarritos`
--

CREATE TABLE `tbdetallecarritos` (
  `id_detalle` int(10) UNSIGNED NOT NULL,
  `id_carrito` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `cantidad` decimal(12,3) NOT NULL DEFAULT 1.000,
  `precio_visto` decimal(12,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbdetallecarritos`
--

INSERT INTO `tbdetallecarritos` (`id_detalle`, `id_carrito`, `id_producto`, `cantidad`, `precio_visto`) VALUES


(104, 47, 1, 1.000, 18.99),
(119, 45, 8, 1.000, 200.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbdetallelistasdeseos`
--

CREATE TABLE `tbdetallelistasdeseos` (
  `id_lista` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tbdetallelistasdeseos`
--

INSERT INTO `tbdetallelistasdeseos` (`id_lista`, `id_producto`, `created_at`) VALUES


(45, 1, '2026-10-17 08:56:47'),
(45, 2, '2026-10-21 22:50:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbdetallepedido`
--

CREATE TABLE `tbdetallepedido` (
  `id_detalle_pedido` int(10) UNSIGNED NOT NULL,
  `id_pedido` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbdetalleventa`
--

CREATE TABLE `tbdetalleventa` (
  `id_detalle_venta` int(10) UNSIGNED NOT NULL,
  `id_venta` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `costo_unitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbdetalleventaservicio`
--

CREATE TABLE `tbdetalleventaservicio` (
  `id_detalle_servicio` int(10) UNSIGNED NOT NULL,
  `id_venta` int(10) UNSIGNED NOT NULL,
  `id_servicio` int(10) UNSIGNED NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `costo_unitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) GENERATED ALWAYS AS (`cantidad` * `precio_unitario`) STORED
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbentregaspedidos`
--

CREATE TABLE `tbentregaspedidos` (
  `id_entrega` int(10) UNSIGNED NOT NULL,
  `id_pedido` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `fecha_entrega` datetime NOT NULL DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbgoogleidentidades`
--

CREATE TABLE `tbgoogleidentidades` (
  `google_subject` varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbgoogleidentidades`
--

INSERT INTO `tbgoogleidentidades` (`google_subject`, `id_usuario`, `created_at`) VALUES


('demo-google-subject-6', 6, '2026-10-22 10:05:20'),
('demo-google-subject-5', 5, '2026-10-22 01:18:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbinventario`
--

CREATE TABLE `tbinventario` (
  `id_inventario` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `stock_actual` decimal(12,3) NOT NULL DEFAULT 0.000,
  `stock_reservado` decimal(12,3) NOT NULL DEFAULT 0.000,
  `stock_minimo` decimal(12,3) NOT NULL DEFAULT 0.000,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ;

--
-- Volcado de datos para la tabla `tbinventario`
--

INSERT INTO `tbinventario` (`id_inventario`, `id_producto`, `stock_actual`, `stock_reservado`, `stock_minimo`, `updated_at`) VALUES


(1, 3, 15.990, 0.000, 5.000, '2026-10-21 22:37:54'),
(2, 6, 20.000, 0.000, 5.000, '2026-10-21 22:37:54'),
(3, 5, 11.000, 0.000, 5.000, '2026-10-22 10:08:29'),
(4, 4, 49.000, 0.000, 5.000, '2026-10-21 22:37:54'),
(5, 1, 23.000, 0.000, 5.000, '2026-10-22 10:08:29'),
(6, 2, 38.000, 0.000, 5.000, '2026-10-22 10:08:29'),
(7, 7, 20.000, 0.000, 0.000, '2026-10-21 22:41:39'),
(8, 8, 9.998, 0.000, 0.000, '2026-10-21 22:48:38'),
(9, 9, 10.000, 0.000, 0.000, '2026-10-26 14:30:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tblistasdeseos`
--

CREATE TABLE `tblistasdeseos` (
  `id_lista` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `tblistasdeseos`
--

INSERT INTO `tblistasdeseos` (`id_lista`, `id_usuario`, `created_at`) VALUES


(45, 3, '2026-10-17 08:56:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbmarcas`
--

CREATE TABLE `tbmarcas` (
  `id_marca` int(10) UNSIGNED NOT NULL,
  `nombre_marca` varchar(100) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbmarcas`
--

INSERT INTO `tbmarcas` (`id_marca`, `nombre_marca`, `estado`) VALUES


(1, 'Tecno Star', 'activo'),
(2, 'Apple', 'activo'),
(3, 'MODOFVY', 'activo'),
(4, 'Gamer', 'activo'),
(5, 'Bluetooth', 'activo'),
(6, 'ACER', 'activo'),
(7, 'Asus', 'activo'),
(8, 'Xiaomi', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbmodeloscompatibles`
--

CREATE TABLE `tbmodeloscompatibles` (
  `id_modelo` int(10) UNSIGNED NOT NULL,
  `id_marca` int(10) UNSIGNED NOT NULL,
  `nombre_modelo` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbmovimientosinventario`
--

CREATE TABLE `tbmovimientosinventario` (
  `id_movimiento` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_pedido` int(10) UNSIGNED DEFAULT NULL,
  `id_venta` int(10) UNSIGNED DEFAULT NULL,
  `id_detalle_servicio` int(10) UNSIGNED DEFAULT NULL,
  `tipo` enum('entrada','salida','ajuste','reserva','liberacion','consumo') NOT NULL,
  `cantidad` decimal(12,3) NOT NULL,
  `motivo` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbpedidos`
--

CREATE TABLE `tbpedidos` (
  `id_pedido` int(10) UNSIGNED NOT NULL,
  `codigo_pedido` varchar(30) NOT NULL,
  `id_cliente` int(10) UNSIGNED NOT NULL,
  `id_usuario_confirmacion` int(10) UNSIGNED DEFAULT NULL,
  `fecha_confirmacion` datetime DEFAULT NULL,
  `fecha_vencimiento` datetime DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` enum('pendiente','confirmado','rechazado','cancelado','vencido','pagado','entregado','en_preparacion','listo') NOT NULL DEFAULT 'pendiente',
  `observaciones` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `retiro_solicitud` char(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `retiro_huella` char(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `contacto_nombre` varchar(201) DEFAULT NULL,
  `contacto_telefono` varchar(30) DEFAULT NULL,
  `contacto_correo` varchar(254) DEFAULT NULL,
  `nota_retiro` varchar(500) DEFAULT NULL,
  `reserva_activa` tinyint(1) NOT NULL DEFAULT 0,
  `updated_at` datetime DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `tbpedidos`
--

INSERT INTO `tbpedidos` (`id_pedido`, `codigo_pedido`, `id_cliente`, `id_usuario_confirmacion`, `fecha_confirmacion`, `fecha_vencimiento`, `total`, `estado`, `observaciones`, `created_at`, `id_usuario`, `retiro_solicitud`, `retiro_huella`, `contacto_nombre`, `contacto_telefono`, `contacto_correo`, `nota_retiro`, `reserva_activa`, `updated_at`) VALUES


(1, 'TS-261019-10A71776ED85', 3, NULL, '2026-10-19 06:35:19', '2026-10-20 06:35:19', 68.98, 'cancelado', NULL, '2026-10-19 06:35:19', 4, 'eb82b04958371d17c4f437e4a3c5015e9db95d15d2139dac3fdd7fbc842026f7', '8723d4ab993b3fac50ba21933643ea07e256baf3cdd0a8361c1bd930990c9739', 'Cliente de demostración', '70000004', 'demo-user-4@tecno-star.test', NULL, 0, '2026-10-20 15:50:02'),
(2, 'TS-261019-83495A8D4E51', 2, NULL, '2026-10-19 16:46:33', '2026-10-20 16:46:33', 18.99, 'cancelado', NULL, '2026-10-19 16:46:33', 3, '622360cf402aada9172b3ea66681c1d395bbefe669b752c5571a22b60ca68d13', 'a91ac583b16d46b5199eafb04a5863a6ad2f6cbb48cf6d60b9f45e9469fd5f81', 'Cliente de demostración', '70000003', 'cliente@clinete.com', NULL, 0, '2026-10-21 03:10:47'),
(3, 'TS-261019-DCF6DDC1CE09', 2, NULL, '2026-10-19 19:06:53', '2026-10-20 19:06:53', 18.99, 'cancelado', NULL, '2026-10-19 19:06:53', 3, '84590d319ecfa36380f5b921404c80fb7666aa1197372b5f5cd3787ce54561a5', 'cad76fb496ecdaa166b84bc8efd46cfd090b99cdb35e3852ae0d5a8408fa2a0a', 'Cliente de demostración', '70000003', 'cliente@clinete.com', NULL, 0, '2026-10-21 03:10:47'),
(4, 'TS-261019-EC15B8353AB6', 2, NULL, '2026-10-20 04:02:27', '2026-10-21 04:02:27', 131.45, 'cancelado', NULL, '2026-10-20 04:02:27', 3, 'b4c4d05770f5a8c155c5f30fbad84a8c8b50a9343eb555c163d33bac5134b134', '2bae1c4533c4dc5c6075501f322ad284d314a845b47671796fd07f3ded20d68c', 'Cliente de demostración', '70000003', 'cliente@clinete.com', NULL, 0, '2026-10-22 04:37:54'),
(5, 'TS-261019-B1008746AE7F', 2, NULL, '2026-10-20 04:09:23', '2026-10-21 04:09:23', 9.99, 'entregado', NULL, '2026-10-20 04:09:23', 3, 'aff71faea6cc5527f6f9f84bc07c12be7c45f55277dfb62377ac35437aac7b4f', '53b3bb93b1946aa25c279a9cd09c929797af21c61c197e4e6433b1045b778f39', 'Cliente de demostración', '70000003', 'cliente@clinete.com', NULL, 0, '2026-10-20 04:28:09'),
(6, 'TS-261019-A54BCC43D92D', 2, NULL, '2026-10-20 04:18:13', '2026-10-21 04:18:13', 12.50, 'cancelado', NULL, '2026-10-20 04:18:13', 3, '645fe634e36d0c79c3edb4f005d527889a7be09cdddb8d51d664f989191a6dc6', '761e8ef299dd4597121c571567b92bcb174b4002b838064f61bc2daaeeaf815d', 'Cliente de demostración', '70000003', 'cliente@clinete.com', NULL, 0, '2026-10-22 04:37:54'),
(7, 'TS-261019-9C30DDBF7DEA', 2, NULL, '2026-10-20 04:21:36', '2026-10-21 04:21:36', 34.99, 'cancelado', NULL, '2026-10-20 04:21:36', 3, '57f61f3660b8bb578660a4b3f688368c8a1cbab88858fde8b794e91ce8c7f08a', '763aaebb6884f4f50ec3842d5fa7bf288084325bff6867387a138ac7568482c3', 'Cliente de demostración', '70000003', 'cliente@clinete.com', NULL, 0, '2026-10-22 04:37:54'),
(8, 'TS-261020-E1DA441229FF', 2, NULL, '2026-10-20 15:51:03', '2026-10-21 15:51:03', 18.99, 'entregado', NULL, '2026-10-20 15:51:03', 3, '20e93de959d18c0e2315f5e9dce720ef3966e2329375451f65c824b596ed42bb', '3d624c85ccd7e6d1369197b0bceac3a4859da425a296f339e789688c4f5bc862', 'Cliente de demostración', '70000003', 'cliente@clinete.com', NULL, 0, '2026-10-20 15:53:22'),
(9, 'TS-261022-C6A9909F3357', 5, NULL, '2026-10-22 16:06:02', '2026-10-23 16:06:02', 78.98, 'entregado', NULL, '2026-10-22 16:06:02', 6, '8b24652ddbf8bffbc8d6c040035ddd524278350771f8cb7b1d6f65017ae5baf2', '9c6fdc182ca15f8ec4037aff71e50b88533a2c527af0d94c69d973970f4ef8b0', 'Cliente de demostración', '70000006', 'demo-user-6@tecno-star.test', NULL, 0, '2026-10-22 16:08:29'),
(10, 'TS-261022-65F713DB9E82', 5, NULL, '2026-10-22 16:16:20', '2026-10-23 16:16:20', 200.50, 'cancelado', NULL, '2026-10-22 16:16:20', 6, '2d2e96d521506547cde1fb50f90c5c67a8e113e0a388b8e0cd1e47d6196ad36f', 'a25e59b9b0dac4965239abba79c48489d2db1c288c4e74323905cd07c0bb2ce1', 'Cliente de demostración', '70000006', 'demo-user-6@tecno-star.test', NULL, 0, '2026-10-26 20:30:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbpersonalpermisos`
--

CREATE TABLE `tbpersonalpermisos` (
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `pedidos` tinyint(1) NOT NULL DEFAULT 0,
  `id_actor` int(10) UNSIGNED NOT NULL,
  `updated_at` datetime NOT NULL,
  `servicios` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproductocompatibilidad`
--

CREATE TABLE `tbproductocompatibilidad` (
  `id_compatibilidad` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `id_modelo` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbproductos`
--

CREATE TABLE `tbproductos` (
  `id_producto` int(10) UNSIGNED NOT NULL,
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `id_marca` int(10) UNSIGNED DEFAULT NULL,
  `codigo_producto` varchar(50) NOT NULL,
  `nombre_producto` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `tipo` enum('producto','insumo') NOT NULL DEFAULT 'producto',
  `unidad_medida` varchar(20) NOT NULL DEFAULT 'unidad',
  `precio_compra` decimal(12,2) NOT NULL,
  `precio_venta` decimal(12,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `tbproductos`
--

INSERT INTO `tbproductos` (`id_producto`, `id_categoria`, `id_marca`, `codigo_producto`, `nombre_producto`, `descripcion`, `tipo`, `unidad_medida`, `precio_compra`, `precio_venta`, `imagen`, `estado`, `created_at`) VALUES


(1, 3, 1, 'TS-CARG-001', 'Cargador rápido USB-C', 'Carga tus dispositivos de forma práctica y segura.', 'producto', 'unidad', 10.00, 18.99, '/Tecno_Star/public/assets/images/products/cargador-real.jpg', 'activo', '2026-10-08 00:14:13'),
(2, 12, 1, 'TS-FUND-002', 'Funda protectora para celular', 'Protección y estilo para diferentes modelos.', 'producto', 'unidad', 6.00, 12.50, '/Tecno_Star/public/assets/images/products/funda-real.jpg', 'activo', '2026-10-08 00:14:13'),
(3, 5, 2, 'AP-AUDI-003', 'Audífonos inalámbricos', 'Disfruta tu música, clases y llamadas.', 'producto', 'unidad', 15.00, 24.99, '/Tecno_Star/public/assets/images/products/audifonos-real.jpg', 'activo', '2026-10-08 00:14:13'),
(4, 4, 3, 'MD-CABL-004', 'Cable USB de carga rápida', 'Conecta, carga y transfiere tus archivos.', 'producto', 'unidad', 4.00, 9.99, '/Tecno_Star/public/assets/images/products/cable-real.jpg', 'activo', '2026-10-08 00:14:13'),
(5, 7, 4, 'GM-TECL-005', 'Teclado gamer RGB', 'Accesorio para estudiar, trabajar y jugar.', 'producto', 'unidad', 24.00, 34.99, '/Tecno_Star/public/assets/images/products/teclado-real.webp', 'activo', '2026-10-08 00:14:13'),
(6, 5, 3, 'BT-BOCI-006', 'Bocina Bluetooth portátil', 'Lleva tu sonido a donde quieras.', 'producto', 'unidad', 19.00, 29.99, '/Tecno_Star/public/assets/images/products/bocina-real.jpg', 'activo', '2026-10-08 00:14:13'),
(7, 7, 6, 'CAL002', 'Monitor ACER nitro', '', 'producto', 'unidad', 200.00, 300.00, '/uploads/products/449ca3538b4946d117af78a37c30c2c59732ed4f17dfee8b.jpg', 'activo', '2026-10-21 22:39:31'),
(8, 7, 7, 'ANDIOANODIAS', 'asus zephyrus', 'La línea ASUS ROG Zephyrus es la familia de portátiles gaming premium y ultrafinos de ASUS, diseñada para ofrecer el máximo rendimiento en un chasis de aluminio ligero, elegante y fácil de transportar. Se posiciona como la opción ideal tanto para jugadores entusiastas como para creadores de contenido profesionales (editores de video, diseñadores y programadores) que buscan potencia de nivel de escritorio sin cargar con un equipo pesado', 'producto', 'unidad', 100.00, 200.00, '/uploads/products/9b3769df06cdd51f77aadb499121776bc626e1beecaf8b6e.jpg', 'activo', '2026-10-21 22:47:50'),
(9, 14, 8, 'ACC-002', 'Cargador Inalámbrico Rápido', 'Base de carga rápida inalámbrica de 15W para dispositivos móviles', 'producto', 'unidad', 7.95, 20.05, 'https://m.media-amazon.com/images/I/71tt2JHuwjL._AC_UF894,1000_QL80_.jpg', 'activo', '2026-10-22 10:12:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbreinversiones`
--

CREATE TABLE `tbreinversiones` (
  `id_reinversion` int(10) UNSIGNED NOT NULL,
  `id_venta` int(10) UNSIGNED NOT NULL,
  `monto_venta` decimal(12,2) NOT NULL,
  `porcentaje_reposicion` decimal(5,2) NOT NULL DEFAULT 50.00,
  `porcentaje_crecimiento` decimal(5,2) NOT NULL DEFAULT 5.00,
  `monto_reposicion` decimal(12,2) GENERATED ALWAYS AS (round(`monto_venta` * `porcentaje_reposicion` / 100,2)) STORED,
  `monto_crecimiento` decimal(12,2) GENERATED ALWAYS AS (round(`monto_venta` * (`porcentaje_reposicion` + `porcentaje_crecimiento`) / 100,2) - round(`monto_venta` * `porcentaje_reposicion` / 100,2)) STORED,
  `total_reinversion` decimal(12,2) GENERATED ALWAYS AS (round(`monto_venta` * (`porcentaje_reposicion` + `porcentaje_crecimiento`) / 100,2)) STORED,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

--
-- Volcado de datos para la tabla `tbreinversiones`
--

INSERT INTO `tbreinversiones` (`id_reinversion`, `id_venta`, `monto_venta`, `porcentaje_reposicion`, `porcentaje_crecimiento`, `created_at`) VALUES


(1, 1, 9.99, 50.00, 5.00, '2026-10-20 04:28:09'),
(2, 2, 18.99, 50.00, 5.00, '2026-10-20 15:53:22'),
(3, 3, 78.98, 50.00, 5.00, '2026-10-22 16:08:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbretiocorreos`
--

CREATE TABLE `tbretiocorreos` (
  `id_correo` bigint(20) UNSIGNED NOT NULL,
  `id_pedido` int(10) UNSIGNED NOT NULL,
  `tipo` enum('confirmado','listo') NOT NULL DEFAULT 'confirmado',
  `estado` enum('pendiente','procesando','enviado','omitido') NOT NULL DEFAULT 'pendiente',
  `intentos` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `bloqueo` char(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `proximo_intento` datetime NOT NULL,
  `enviado_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbretiocorreos`
--

INSERT INTO `tbretiocorreos` (`id_correo`, `id_pedido`, `tipo`, `estado`, `intentos`, `bloqueo`, `proximo_intento`, `enviado_at`, `created_at`) VALUES


(1, 1, 'confirmado', 'enviado', 2, NULL, '2026-10-19 06:45:41', '2026-10-19 06:40:45', '2026-10-19 06:36:43'),
(2, 2, 'confirmado', 'enviado', 1, NULL, '2026-10-19 16:53:08', '2026-10-19 16:48:11', '2026-10-19 16:48:08'),
(3, 3, 'confirmado', 'enviado', 1, NULL, '2026-10-19 19:13:21', '2026-10-19 19:08:25', '2026-10-19 19:08:21'),
(4, 4, 'confirmado', 'enviado', 1, NULL, '2026-10-20 04:09:11', '2026-10-20 04:04:17', '2026-10-20 04:04:11'),
(5, 5, 'confirmado', 'enviado', 1, NULL, '2026-10-20 04:20:55', '2026-10-20 04:16:00', '2026-10-20 04:15:55'),
(6, 6, 'confirmado', 'enviado', 1, NULL, '2026-10-20 04:23:27', '2026-10-20 04:18:30', '2026-10-20 04:18:27'),
(7, 7, 'confirmado', 'enviado', 1, NULL, '2026-10-20 04:26:48', '2026-10-20 04:21:51', '2026-10-20 04:21:48'),
(8, 8, 'confirmado', 'enviado', 1, NULL, '2026-10-20 15:56:22', '2026-10-20 15:51:27', '2026-10-20 15:51:22'),
(9, 8, 'listo', 'enviado', 1, NULL, '2026-10-20 15:57:14', '2026-10-20 15:52:17', '2026-10-20 15:52:14'),
(10, 9, 'confirmado', 'enviado', 1, NULL, '2026-10-22 16:11:39', '2026-10-22 16:06:46', '2026-10-22 16:06:39'),
(11, 9, 'listo', 'enviado', 1, NULL, '2026-10-22 16:12:22', '2026-10-22 16:07:29', '2026-10-22 16:07:22');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbretirodetalles`
--

CREATE TABLE `tbretirodetalles` (
  `id_detalle` bigint(20) UNSIGNED NOT NULL,
  `id_pedido` int(10) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `codigo_producto` varchar(50) NOT NULL,
  `nombre_producto` varchar(150) NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL
) ;

--
-- Volcado de datos para la tabla `tbretirodetalles`
--

INSERT INTO `tbretirodetalles` (`id_detalle`, `id_pedido`, `id_producto`, `codigo_producto`, `nombre_producto`, `cantidad`, `precio_unitario`, `subtotal`) VALUES


(1, 1, 1, 'TS-CARG-001', 'Cargador rápido USB-C', 1, 18.99, 18.99),
(2, 1, 2, 'TS-FUND-002', 'Funda protectora para celular', 2, 12.50, 25.00),
(3, 1, 3, 'AP-AUDI-003', 'Audífonos inalámbricos', 1, 24.99, 24.99),
(4, 2, 1, 'TS-CARG-001', 'Cargador rápido USB-C', 1, 18.99, 18.99),
(5, 3, 1, 'TS-CARG-001', 'Cargador rápido USB-C', 1, 18.99, 18.99),
(6, 4, 1, 'TS-CARG-001', 'Cargador rápido USB-C', 1, 18.99, 18.99),
(7, 4, 2, 'TS-FUND-002', 'Funda protectora para celular', 1, 12.50, 12.50),
(8, 4, 3, 'AP-AUDI-003', 'Audífonos inalámbricos', 1, 24.99, 24.99),
(9, 4, 4, 'MD-CABL-004', 'Cable USB de carga rápida', 1, 9.99, 9.99),
(10, 4, 5, 'GM-TECL-005', 'Teclado gamer RGB', 1, 34.99, 34.99),
(11, 4, 6, 'BT-BOCI-006', 'Bocina Bluetooth portátil', 1, 29.99, 29.99),
(12, 5, 4, 'MD-CABL-004', 'Cable USB de carga rápida', 1, 9.99, 9.99),
(13, 6, 2, 'TS-FUND-002', 'Funda protectora para celular', 1, 12.50, 12.50),
(14, 7, 5, 'GM-TECL-005', 'Teclado gamer RGB', 1, 34.99, 34.99),
(15, 8, 1, 'TS-CARG-001', 'Cargador rápido USB-C', 1, 18.99, 18.99),
(16, 9, 1, 'TS-CARG-001', 'Cargador rápido USB-C', 1, 18.99, 18.99),
(17, 9, 2, 'TS-FUND-002', 'Funda protectora para celular', 2, 12.50, 25.00),
(18, 9, 5, 'GM-TECL-005', 'Teclado gamer RGB', 1, 34.99, 34.99),
(19, 10, 9, 'ACC-002', 'Cargador Inalámbrico Rápido', 10, 20.05, 200.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbretirohistorial`
--

CREATE TABLE `tbretirohistorial` (
  `id_historial` bigint(20) UNSIGNED NOT NULL,
  `id_pedido` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `responsable` varchar(201) NOT NULL,
  `estado_anterior` varchar(30) DEFAULT NULL,
  `estado_nuevo` varchar(30) NOT NULL,
  `motivo` varchar(500) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbretirohistorial`
--

INSERT INTO `tbretirohistorial` (`id_historial`, `id_pedido`, `id_usuario`, `responsable`, `estado_anterior`, `estado_nuevo`, `motivo`, `created_at`) VALUES


(1, 1, 4, 'Personal de demostración', NULL, 'pendiente', 'Movimiento de demostración.', '2026-10-19 06:35:19'),
(2, 1, 2, 'Personal de demostración', 'pendiente', 'confirmado', 'Movimiento de demostración.', '2026-10-19 06:36:43'),
(3, 2, 3, 'Personal de demostración', NULL, 'pendiente', 'Movimiento de demostración.', '2026-10-19 16:46:33'),
(4, 2, 2, 'Personal de demostración', 'pendiente', 'confirmado', 'Movimiento de demostración.', '2026-10-19 16:48:08'),
(5, 3, 3, 'Personal de demostración', NULL, 'pendiente', 'Movimiento de demostración.', '2026-10-19 19:06:53'),
(6, 3, 2, 'Personal de demostración', 'pendiente', 'confirmado', 'Movimiento de demostración.', '2026-10-19 19:08:21'),
(7, 4, 3, 'Personal de demostración', NULL, 'pendiente', 'Movimiento de demostración.', '2026-10-20 04:02:27'),
(8, 4, 2, 'Personal de demostración', 'pendiente', 'confirmado', 'Movimiento de demostración.', '2026-10-20 04:04:11'),
(9, 5, 3, 'Personal de demostración', NULL, 'pendiente', 'Movimiento de demostración.', '2026-10-20 04:09:23'),
(10, 5, 2, 'Personal de demostración', 'pendiente', 'confirmado', 'Movimiento de demostración.', '2026-10-20 04:15:55'),
(11, 5, 2, 'Personal de demostración', 'confirmado', 'en_preparacion', 'Movimiento de demostración.', '2026-10-20 04:16:26'),
(12, 5, 2, 'Personal de demostración', 'en_preparacion', 'listo', 'Movimiento de demostración.', '2026-10-20 04:16:51'),
(13, 6, 3, 'Personal de demostración', NULL, 'pendiente', 'Movimiento de demostración.', '2026-10-20 04:18:13'),
(14, 6, 2, 'Personal de demostración', 'pendiente', 'confirmado', 'Movimiento de demostración.', '2026-10-20 04:18:27'),
(15, 6, 2, 'Personal de demostración', 'confirmado', 'en_preparacion', 'Movimiento de demostración.', '2026-10-20 04:20:39'),
(16, 6, 2, 'Personal de demostración', 'en_preparacion', 'listo', 'Movimiento de demostración.', '2026-10-20 04:20:43'),
(17, 7, 3, 'Personal de demostración', NULL, 'pendiente', 'Movimiento de demostración.', '2026-10-20 04:21:36'),
(18, 7, 2, 'Personal de demostración', 'pendiente', 'confirmado', 'Movimiento de demostración.', '2026-10-20 04:21:48'),
(19, 7, 2, 'Personal de demostración', 'confirmado', 'en_preparacion', 'Movimiento de demostración.', '2026-10-20 04:22:20'),
(20, 5, 2, 'Personal de demostración', 'listo', 'entregado', 'Movimiento de demostración.', '2026-10-20 04:28:09'),
(21, 1, NULL, 'Personal de demostración', 'confirmado', 'cancelado', 'Movimiento de demostración.', '2026-10-20 15:50:02'),
(22, 8, 3, 'Personal de demostración', NULL, 'pendiente', 'Movimiento de demostración.', '2026-10-20 15:51:03'),
(23, 8, 2, 'Personal de demostración', 'pendiente', 'confirmado', 'Movimiento de demostración.', '2026-10-20 15:51:22'),
(24, 8, 2, 'Personal de demostración', 'confirmado', 'en_preparacion', 'Movimiento de demostración.', '2026-10-20 15:52:08'),
(25, 8, 2, 'Personal de demostración', 'en_preparacion', 'listo', 'Movimiento de demostración.', '2026-10-20 15:52:14'),
(26, 8, 2, 'Personal de demostración', 'listo', 'entregado', 'Movimiento de demostración.', '2026-10-20 15:53:22'),
(27, 2, NULL, 'Personal de demostración', 'confirmado', 'cancelado', 'Movimiento de demostración.', '2026-10-21 03:10:47'),
(28, 3, NULL, 'Personal de demostración', 'confirmado', 'cancelado', 'Movimiento de demostración.', '2026-10-21 03:10:47'),
(29, 4, NULL, 'Personal de demostración', 'confirmado', 'cancelado', 'Movimiento de demostración.', '2026-10-22 04:37:54'),
(30, 6, NULL, 'Personal de demostración', 'listo', 'cancelado', 'Movimiento de demostración.', '2026-10-22 04:37:54'),
(31, 7, NULL, 'Personal de demostración', 'en_preparacion', 'cancelado', 'Movimiento de demostración.', '2026-10-22 04:37:54'),
(32, 9, 6, 'Personal de demostración', NULL, 'pendiente', 'Movimiento de demostración.', '2026-10-22 16:06:02'),
(33, 9, 2, 'Personal de demostración', 'pendiente', 'confirmado', 'Movimiento de demostración.', '2026-10-22 16:06:39'),
(34, 9, 2, 'Personal de demostración', 'confirmado', 'en_preparacion', 'Movimiento de demostración.', '2026-10-22 16:07:00'),
(35, 9, 2, 'Personal de demostración', 'en_preparacion', 'listo', 'Movimiento de demostración.', '2026-10-22 16:07:22'),
(36, 9, 2, 'Personal de demostración', 'listo', 'entregado', 'Movimiento de demostración.', '2026-10-22 16:08:29'),
(37, 10, 6, 'Personal de demostración', NULL, 'pendiente', 'Movimiento de demostración.', '2026-10-22 16:16:20'),
(38, 10, NULL, 'Personal de demostración', 'pendiente', 'cancelado', 'Movimiento de demostración.', '2026-10-26 20:30:14');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbroles`
--

CREATE TABLE `tbroles` (
  `id_rol` int(10) UNSIGNED NOT NULL,
  `nombre_rol` varchar(30) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbroles`
--

INSERT INTO `tbroles` (`id_rol`, `nombre_rol`, `descripcion`) VALUES


(1, 'Administrador', 'Administra usuarios, catalogos, inventario y reportes.'),
(2, 'Empleado', 'Atiende clientes y registra reservas, ventas, servicios y entregas.'),
(3, 'Cliente', 'Consulta el catalogo y gestiona sus propias reservas.');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbservicioarchivos`
--

CREATE TABLE `tbservicioarchivos` (
  `id_archivo` bigint(20) UNSIGNED NOT NULL,
  `id_solicitud` int(10) UNSIGNED NOT NULL,
  `id_actor` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `ruta` varchar(80) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `mime` varchar(100) NOT NULL,
  `bytes` int(10) UNSIGNED NOT NULL,
  `sha256` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `tipo` enum('original','resultado') NOT NULL,
  `eliminado_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbservicioarchivos`
--

INSERT INTO `tbservicioarchivos` (`id_archivo`, `id_solicitud`, `id_actor`, `nombre`, `ruta`, `mime`, `bytes`, `sha256`, `tipo`, `eliminado_at`, `created_at`) VALUES


(1, 1, 5, 'archivo-demo.pdf', 'demo/archivo-demo.pdf', 'image/png', 17922, '0000000000000000000000000000000000000000000000000000000000000000', 'original', NULL, '2026-10-31 17:56:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbserviciocatalogo`
--

CREATE TABLE `tbserviciocatalogo` (
  `id_servicio` int(10) UNSIGNED NOT NULL,
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `descripcion` varchar(2000) NOT NULL,
  `unidad` varchar(60) NOT NULL,
  `precio_base` decimal(12,2) DEFAULT NULL,
  `tiempo` varchar(150) NOT NULL DEFAULT 'A confirmar al cotizar',
  `material_fisico` tinyint(1) NOT NULL DEFAULT 0,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `revision` int(10) UNSIGNED NOT NULL DEFAULT 0
) ;

--
-- Volcado de datos para la tabla `tbserviciocatalogo`
--

INSERT INTO `tbserviciocatalogo` (`id_servicio`, `id_categoria`, `nombre`, `descripcion`, `unidad`, `precio_base`, `tiempo`, `material_fisico`, `estado`, `revision`) VALUES


(1, 1, 'Copias', 'Copias de tus documentos. Lleva los originales al local.', 'página', 20.00, 'A confirmar al cotizar', 1, 'activo', 1),
(2, 1, 'Impresiones', 'Impresión de documentos en blanco y negro o color.', 'página', NULL, 'A confirmar al cotizar', 0, 'activo', 0),
(3, 2, 'Fotografías', 'Impresión de fotografías según las medidas solicitadas.', 'fotografía', 0.68, 'A confirmar al cotizar', 0, 'activo', 1),
(4, 2, 'Stickers', 'Impresión de stickers. Indica medidas, cantidad y preparación necesaria.', 'trabajo', NULL, 'A confirmar al cotizar', 0, 'activo', 0),
(5, 1, 'Escaneos', 'Digitalización de documentos originales entregados en tienda.', 'página', NULL, 'A confirmar al cotizar', 1, 'activo', 0),
(6, 1, 'Digitación', 'Preparación de documentos a partir de tus instrucciones y referencias.', 'trabajo', NULL, 'A confirmar al cotizar', 0, 'activo', 0),
(7, 3, 'Anillado', 'Encuadernación de documentos entregados en tienda.', 'documento', NULL, 'A confirmar al cotizar', 1, 'activo', 0),
(8, 3, 'Plastificado', 'Protección de documentos originales entregados en tienda.', 'documento', NULL, 'A confirmar al cotizar', 1, 'activo', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbserviciocategorias`
--

CREATE TABLE `tbserviciocategorias` (
  `id_categoria` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbserviciocategorias`
--

INSERT INTO `tbserviciocategorias` (`id_categoria`, `nombre`, `estado`) VALUES


(1, 'Documentos', 'activo'),
(2, 'Fotografías y stickers', 'activo'),
(3, 'Acabados', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbserviciocorreos`
--

CREATE TABLE `tbserviciocorreos` (
  `id_correo` bigint(20) UNSIGNED NOT NULL,
  `id_solicitud` int(10) UNSIGNED NOT NULL,
  `revision` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(40) NOT NULL,
  `estado` enum('pendiente','procesando','enviado','omitido') NOT NULL DEFAULT 'pendiente',
  `intentos` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `bloqueo` char(64) DEFAULT NULL,
  `proximo_intento` datetime NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbserviciocorreos`
--

INSERT INTO `tbserviciocorreos` (`id_correo`, `id_solicitud`, `revision`, `tipo`, `estado`, `intentos`, `bloqueo`, `proximo_intento`, `created_at`) VALUES


(1, 1, 0, 'recibida', 'enviado', 1, NULL, '2026-10-31 18:01:58', '2026-10-31 17:56:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbserviciocotizaciones`
--

CREATE TABLE `tbserviciocotizaciones` (
  `id_cotizacion` int(10) UNSIGNED NOT NULL,
  `id_solicitud` int(10) UNSIGNED NOT NULL,
  `version` int(10) UNSIGNED NOT NULL,
  `conceptos` text NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `condiciones` varchar(2000) NOT NULL,
  `fecha_entrega` datetime NOT NULL,
  `vence_at` datetime NOT NULL,
  `decision` enum('pendiente','aceptada','rechazada','sustituida') NOT NULL DEFAULT 'pendiente',
  `id_actor` int(10) UNSIGNED NOT NULL,
  `decidida_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbserviciohistorial`
--

CREATE TABLE `tbserviciohistorial` (
  `id_evento` bigint(20) UNSIGNED NOT NULL,
  `id_solicitud` int(10) UNSIGNED NOT NULL,
  `id_actor` int(10) UNSIGNED NOT NULL,
  `accion` varchar(40) NOT NULL,
  `detalle` varchar(2000) NOT NULL,
  `operacion` char(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `huella` char(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbserviciohistorial`
--

INSERT INTO `tbserviciohistorial` (`id_evento`, `id_solicitud`, `id_actor`, `accion`, `detalle`, `operacion`, `huella`, `created_at`) VALUES


(1, 1, 5, 'nueva', 'Solicitud recibida.', NULL, NULL, '2026-10-31 17:56:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbservicios`
--

CREATE TABLE `tbservicios` (
  `id_servicio` int(10) UNSIGNED NOT NULL,
  `nombre_servicio` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `precio` decimal(12,2) NOT NULL,
  `costo_estimado` decimal(12,2) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbserviciosolicitudes`
--

CREATE TABLE `tbserviciosolicitudes` (
  `id_solicitud` int(10) UNSIGNED NOT NULL,
  `codigo` varchar(30) NOT NULL,
  `id_servicio` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `nombre_servicio` varchar(120) NOT NULL,
  `unidad` varchar(60) NOT NULL,
  `cantidad` int(10) UNSIGNED NOT NULL,
  `descripcion` varchar(4000) NOT NULL,
  `opciones` text NOT NULL,
  `contacto_nombre` varchar(201) NOT NULL,
  `contacto_correo` varchar(150) NOT NULL,
  `contacto_telefono` varchar(20) NOT NULL,
  `fecha_preferida` date DEFAULT NULL,
  `material_fisico` tinyint(1) NOT NULL DEFAULT 0,
  `material_recibido` tinyint(1) NOT NULL DEFAULT 0,
  `estado` enum('nueva','en_cotizacion','cotizada','aceptada','en_proceso','lista','entregada','cancelada') NOT NULL DEFAULT 'nueva',
  `revision` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `total` decimal(12,2) DEFAULT NULL,
  `fecha_entrega` datetime DEFAULT NULL,
  `operacion` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `huella` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL
) ;

--
-- Volcado de datos para la tabla `tbserviciosolicitudes`
--

INSERT INTO `tbserviciosolicitudes` (`id_solicitud`, `codigo`, `id_servicio`, `id_usuario`, `nombre_servicio`, `unidad`, `cantidad`, `descripcion`, `opciones`, `contacto_nombre`, `contacto_correo`, `contacto_telefono`, `fecha_preferida`, `material_fisico`, `material_recibido`, `estado`, `revision`, `total`, `fecha_entrega`, `operacion`, `huella`, `created_at`, `updated_at`) VALUES


(1, 'SV-261031-8989784DBDB6', 7, 5, 'Anillado', 'documento', 1, 'Solicitud de demostración.', '{}', 'Cliente de demostración', 'demo-user-5@tecno-star.test', '70000005', '2026-11-01', 1, 0, 'nueva', 0, NULL, NULL, '5865e16d5d106e1a097070827872ae026dcc951415a8d215139af742d74d9c99', '2d51c3502bc6020e6591c84d71753513661515a467d8601a4942cc31c95752a9', '2026-10-31 17:56:58', '2026-10-31 17:56:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbshoppingoperaciones`
--

CREATE TABLE `tbshoppingoperaciones` (
  `solicitud` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `huella` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `avisos` text NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbshoppingoperaciones`
--

INSERT INTO `tbshoppingoperaciones` (`solicitud`, `id_usuario`, `huella`, `avisos`, `created_at`) VALUES


('933fc7c84ee0914b0a4222cfaa2d64212807129f7b68eedb55715a9a6c654bd3', 6, '4fc855a62c610b9e98e33df5869d98b9a90675343c14c202eee9fbc427476beb', '[]', '2026-10-22 10:16:12'),
('6113395484932af5cb7933c22a3adac5c9d9f6c62314c3fb44c046ea198e9f09', 3, '3fc43abe2192b0f8149912b4f48f492b55c4852a91a6ff2700300240db9990d3', '[]', '2026-10-19 22:02:02'),
('6629e0711c281a3b695b6c19a5701b5654409e2f8a1fb951830b5a1ae87a1159', 3, 'bc41e35396c247ecd82cfa21d1f47035f129d05f9b2f0fc7fc4f39708bc69bfd', '[]', '2026-10-19 13:05:39'),
('a700ebfaea0af0143578f9f96a402792c6a0bd080dd7b45cbdec91a6a6244353', 3, '35eebd373e4c226d5eb383ff876f957c24a35d98e5334caa43976d6dfd49df8a', '[]', '2026-10-19 22:09:03'),
('20e53584a65cf4558bf302ced22f403f4136cda8147bb0b6a5ea945c68198cc4', 2, 'a4e2078bef7ead2f45ed131dbeeab92d5a9c746ca7a053924aea9021add4e409', '[]', '2026-10-19 00:26:09'),
('610c5ebd37fc84539076e174358d89aac7b5d4cc794fbdaecec6d57bb10b5a21', 3, '52392c3e63025e564236bf90dc20f1622742e651408d230316b8815ce4ec7a84', '[]', '2026-10-19 22:02:01'),
('31adb3a3e20abf286e2993690d4bb2b0c84ad41e8779b73a6f22a0d3852aa059', 6, '8c0130c928c12bd8bdf17bc8b2de651dd133460e01fb86e581ee938bff0e99b5', '[]', '2026-10-22 10:05:31'),
('9e5ddf1c243258d02fb22e64bade69374ef3d3d188250d9e12411f801b3030b0', 3, 'cc7e44804901a7a310d99a688b0df74ea69f5086196ac817b06b3716d9cf6c3a', '[]', '2026-10-19 22:21:26'),
('789c0ac1cc32d9be2f3d9c29b6f607c2bdbc2f06de71526073b81e965e2e27ca', 3, '913d9f8f706e2a93346937cdbb9f7df3247020f0c4b5e43629ab7cb854f53d9f', '[]', '2026-10-19 22:17:56'),
('70360d1de1c1f553258f306d59ab101dd3cb95ed31c9d7b04cf6070b3a6ec1df', 3, '01e74f2d3f42750c7faa95c79f438309350e86761b886c05b8ee03a1aebb9c8e', '[]', '2026-10-19 22:02:00'),
('c29b6abab193dfeae7c6376458d512382e0f4ad93abf322ec078d7c903350d30', 6, 'b814db5c6c8bc75b36ee373138fb836b41e6d1d76b410ad6481ab62f42603528', '[]', '2026-10-22 10:05:21'),
('d5add62bd2237d2de8afee1744ffcb5ede4bd153e5001c0cb4deb29c49f08538', 4, '4c29255a10765add54fe9c491f9470cfcfab1b375dbae5a3c447fe10e5a52ff3', '[]', '2026-10-19 00:34:21'),
('65058e6237c89d59c6eb025a62495a506d1a4a10c32e2768ebbeb4b82515cd89', 3, '6fb877af488ceaa305220ba0fbae8d5774773102ffabe28551e932cf7851a51b', '[]', '2026-10-19 22:01:58'),
('e7c57379ec563e7b0902393e602e4ace11347c5b6cc9c91fdcbf0423ea398e70', 4, 'c947651cf860d5136e7460690f10a990555c4bdf9b7cca45bf038c0c2371987b', '[]', '2026-10-19 00:34:19'),
('3a0f13ee1a99ce00a2ee4aef65cfb064ba8aaa025ca4e4d71c90bf4018aee023', 6, 'fe3ae95f31450df2afa75c4b50bd9aa341a5a2d21673b6437c0e0be7adc14f61', '[]', '2026-10-22 10:05:33'),
('aa6d4084ff5cdce6733379ad2e41ee1b6de04d549a33b79dd532751baaab4a65', 4, '0d67dc2ceda073ffc47ccfc1ce993f831a23f1ff3c2c60fc5a91932eb8e127ff', '[]', '2026-10-19 00:34:23'),
('92d735df51d053b877816a910a7baa8cc841a15146df40714c8bb51e945c9dc6', 3, '0de0743d5a9ae6178bcf8575747052cd94b95aabb3904107bbf8e64cc4c6c243', '[]', '2026-10-21 22:49:50'),
('6c4011ee3f56f60cf36ecdc3e448063e0a92201d3adc2ec076f8a2395aaf16a7', 3, '605a7adbb1c7a0466430ce795e2088fdbda4f50815db8393123e0670bca85d2c', '[]', '2026-10-19 22:01:56'),
('57898ea99b4c47d0f724a9f40d664f44c3342249be55c2b74e93f3f850f11076', 3, 'ee953c0230725afe3bb57121b60374c8df7c00227b309594786187b0b216cfb6', '[]', '2026-10-19 10:46:16'),
('a237fce9cbb723d4354d3c29bba933ba80a738a52eaac1354d522688dbfc43fc', 6, '6455e00c167b58035b7565c0a0d07d91f523dac1d137d0b57a55345ce51c9a51', '[]', '2026-10-22 10:05:30'),
('1ddd0c176b2bea9b110e2926d3416038e56996d673378617540f31aea3077e75', 3, '4a41113817b6ae4909510befc40c1ed8c20ea92303b689859aba4e1c09e316b8', '[]', '2026-10-19 22:01:54'),
('8b14aa1449cd5936f73c6b19aa87797ef488dc725e8518b5c869a660251fd366', 3, '5535e04cbde79f1f2ecf7bf2e379d41c58a069e4108de11b24f298501f5fb6a0', '[]', '2026-10-21 22:50:12'),
('72f7738b0e27b3158df59e292446791a34f295ab1511f97e15d1b7ded3f7bb11', 4, '9a5e22ca789d84d6646cefd14746fe430a2ed99f1f17c3907f022049f9cb5497', '[]', '2026-10-19 00:34:27'),
('a1205360c8eddabe2cd402451effbb5f70c3a2d434f66d3a48e4b2b87d5f5444', 3, '18be38e0f6e95164e2a0ae58767d5c0e2a18249839c6279063e54165bba9ce9a', '[]', '2026-10-20 09:50:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbstockmovimientos`
--

CREATE TABLE `tbstockmovimientos` (
  `id_movimiento` bigint(20) UNSIGNED NOT NULL,
  `id_producto` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED DEFAULT NULL,
  `responsable` varchar(201) NOT NULL,
  `tipo` enum('inicial','entrada','salida','ajuste','minimo','reserva','liberacion','entrega','devolucion') NOT NULL,
  `cantidad` decimal(12,3) NOT NULL DEFAULT 0.000,
  `stock_antes` decimal(12,3) UNSIGNED NOT NULL,
  `stock_despues` decimal(12,3) UNSIGNED NOT NULL,
  `reservado` decimal(12,3) UNSIGNED NOT NULL,
  `minimo_antes` decimal(12,3) UNSIGNED NOT NULL,
  `minimo_despues` decimal(12,3) UNSIGNED NOT NULL,
  `motivo` varchar(500) NOT NULL,
  `solicitud` char(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `huella` char(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `reservado_antes` decimal(12,3) UNSIGNED DEFAULT NULL,
  `id_pedido` int(10) UNSIGNED DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `tbstockmovimientos`
--

INSERT INTO `tbstockmovimientos` (`id_movimiento`, `id_producto`, `id_usuario`, `responsable`, `tipo`, `cantidad`, `stock_antes`, `stock_despues`, `reservado`, `minimo_antes`, `minimo_despues`, `motivo`, `solicitud`, `huella`, `created_at`, `reservado_antes`, `id_pedido`) VALUES


(1, 3, NULL, 'Personal de demostración', 'inicial', 0.000, 18.000, 18.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-17 11:31:17', NULL, NULL),
(2, 6, NULL, 'Personal de demostración', 'inicial', 0.000, 20.000, 20.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-17 11:31:17', NULL, NULL),
(3, 5, NULL, 'Personal de demostración', 'inicial', 0.000, 12.000, 12.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-17 11:31:17', NULL, NULL),
(4, 4, NULL, 'Personal de demostración', 'inicial', 0.000, 50.000, 50.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-17 11:31:17', NULL, NULL),
(5, 1, NULL, 'Personal de demostración', 'inicial', 0.000, 25.000, 25.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-17 11:31:17', NULL, NULL),
(6, 2, NULL, 'Personal de demostración', 'inicial', 0.000, 40.000, 40.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-17 11:31:17', NULL, NULL),
(7, 1, 4, 'Personal de demostración', 'reserva', 0.000, 25.000, 25.000, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 00:35:19', 0.000, 1),
(8, 2, 4, 'Personal de demostración', 'reserva', 0.000, 40.000, 40.000, 2.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 00:35:19', 0.000, 1),
(9, 3, 4, 'Personal de demostración', 'reserva', 0.000, 1.000, 1.000, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 00:35:19', 0.000, 1),
(10, 1, 3, 'Personal de demostración', 'reserva', 0.000, 25.000, 25.000, 2.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 10:46:33', 1.000, 2),
(11, 3, 2, 'Personal de demostración', 'entrada', 4.990, 1.000, 5.990, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', 'd8fe81f9c781d7df3464c8bb8b117a9611c6ca84dff503127254347a4c351386', 'e5dbaf9599dd94a6e3e2d10bad06b5da2a80d7bbec239bff562faf75edfaa3e4', '2026-10-19 10:53:10', NULL, NULL),
(12, 3, 2, 'Personal de demostración', 'entrada', 10.000, 5.990, 15.990, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', 'c60e12d4b1ac56f416faecb877684b99d9865d248a4be346311b5f05df098647', 'c6908feb29d5bcc89217bac724d6f5ff3dc4cbae6217f42acfbf36737be345e6', '2026-10-19 10:53:26', NULL, NULL),
(13, 1, 3, 'Personal de demostración', 'reserva', 0.000, 25.000, 25.000, 3.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 13:06:53', 2.000, 3),
(14, 1, 3, 'Personal de demostración', 'reserva', 0.000, 25.000, 25.000, 4.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 22:02:27', 3.000, 4),
(15, 2, 3, 'Personal de demostración', 'reserva', 0.000, 40.000, 40.000, 3.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 22:02:27', 2.000, 4),
(16, 3, 3, 'Personal de demostración', 'reserva', 0.000, 15.990, 15.990, 2.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 22:02:27', 1.000, 4),
(17, 4, 3, 'Personal de demostración', 'reserva', 0.000, 50.000, 50.000, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 22:02:27', 0.000, 4),
(18, 5, 3, 'Personal de demostración', 'reserva', 0.000, 12.000, 12.000, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 22:02:27', 0.000, 4),
(19, 6, 3, 'Personal de demostración', 'reserva', 0.000, 20.000, 20.000, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 22:02:27', 0.000, 4),
(20, 4, 3, 'Personal de demostración', 'reserva', 0.000, 50.000, 50.000, 2.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 22:09:23', 1.000, 5),
(21, 2, 3, 'Personal de demostración', 'reserva', 0.000, 40.000, 40.000, 4.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 22:18:13', 3.000, 6),
(22, 5, 3, 'Personal de demostración', 'reserva', 0.000, 12.000, 12.000, 2.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 22:21:36', 1.000, 7),
(23, 4, 2, 'Personal de demostración', 'entrega', -1.000, 50.000, 49.000, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-19 22:28:09', 2.000, 5),
(24, 1, NULL, 'Personal de demostración', 'liberacion', 0.000, 25.000, 25.000, 3.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-20 09:50:02', 4.000, 1),
(25, 2, NULL, 'Personal de demostración', 'liberacion', 0.000, 40.000, 40.000, 2.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-20 09:50:02', 4.000, 1),
(26, 3, NULL, 'Personal de demostración', 'liberacion', 0.000, 15.990, 15.990, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-20 09:50:02', 2.000, 1),
(27, 1, 3, 'Personal de demostración', 'reserva', 0.000, 25.000, 25.000, 4.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-20 09:51:03', 3.000, 8),
(28, 1, 2, 'Personal de demostración', 'entrega', -1.000, 25.000, 24.000, 3.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-20 09:53:22', 4.000, 8),
(29, 1, NULL, 'Personal de demostración', 'liberacion', 0.000, 24.000, 24.000, 2.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-20 21:10:47', 3.000, 2),
(30, 1, NULL, 'Personal de demostración', 'liberacion', 0.000, 24.000, 24.000, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-20 21:10:47', 2.000, 3),
(37, 1, NULL, 'Personal de demostración', 'liberacion', 0.000, 24.000, 24.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-21 22:37:54', 1.000, 4),
(38, 2, NULL, 'Personal de demostración', 'liberacion', 0.000, 40.000, 40.000, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-21 22:37:54', 2.000, 4),
(39, 3, NULL, 'Personal de demostración', 'liberacion', 0.000, 15.990, 15.990, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-21 22:37:54', 1.000, 4),
(40, 4, NULL, 'Personal de demostración', 'liberacion', 0.000, 49.000, 49.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-21 22:37:54', 1.000, 4),
(41, 5, NULL, 'Personal de demostración', 'liberacion', 0.000, 12.000, 12.000, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-21 22:37:54', 2.000, 4),
(42, 6, NULL, 'Personal de demostración', 'liberacion', 0.000, 20.000, 20.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-21 22:37:54', 1.000, 4),
(43, 2, NULL, 'Personal de demostración', 'liberacion', 0.000, 40.000, 40.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-21 22:37:54', 1.000, 6),
(44, 5, NULL, 'Personal de demostración', 'liberacion', 0.000, 12.000, 12.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-21 22:37:54', 1.000, 7),
(45, 7, 2, 'Personal de demostración', 'entrada', 20.000, 0.000, 20.000, 0.000, 0.000, 0.000, 'Movimiento de inventario de demostración.', '65a5f5d2743cc8e76996d3ec5a1b83ab6fcef8174faea2467d606243a4f25bb6', 'c6a3039057a760a0a3b0abc34469000c2eba46891825aec0efeaa4a47c3b1a17', '2026-10-21 22:41:39', NULL, NULL),
(46, 8, 2, 'Personal de demostración', 'entrada', 9.998, 0.000, 9.998, 0.000, 0.000, 0.000, 'Movimiento de inventario de demostración.', 'b2101c1536016c1253c3d6548762e19c970426cc9b4d54f5955c8b8c88b8a7a6', '8eab95e17fe70924bbd95ff8d5949d69dccd7e5a1b7d59d48d48046e80314cff', '2026-10-21 22:48:38', NULL, NULL),
(47, 1, 6, 'Personal de demostración', 'reserva', 0.000, 24.000, 24.000, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-22 10:06:02', 0.000, 9),
(48, 2, 6, 'Personal de demostración', 'reserva', 0.000, 40.000, 40.000, 2.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-22 10:06:02', 0.000, 9),
(49, 5, 6, 'Personal de demostración', 'reserva', 0.000, 12.000, 12.000, 1.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-22 10:06:02', 0.000, 9),
(50, 1, 2, 'Personal de demostración', 'entrega', -1.000, 24.000, 23.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-22 10:08:29', 1.000, 9),
(51, 2, 2, 'Personal de demostración', 'entrega', -2.000, 40.000, 38.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-22 10:08:29', 2.000, 9),
(52, 5, 2, 'Personal de demostración', 'entrega', -1.000, 12.000, 11.000, 0.000, 5.000, 5.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-22 10:08:29', 1.000, 9),
(53, 9, 2, 'Personal de demostración', 'entrada', 10.000, 0.000, 10.000, 0.000, 0.000, 0.000, 'Movimiento de inventario de demostración.', 'f6228700cbc2d1c67bc8c9caeea9e363e23ec1556885014a329495410a187521', '25dfa551bf127c1e9fac36cea77d1d8e405c778ba48acd8cca6283de30499431', '2026-10-22 10:13:29', NULL, NULL),
(54, 9, 6, 'Personal de demostración', 'reserva', 0.000, 10.000, 10.000, 10.000, 0.000, 0.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-22 10:16:20', 0.000, 10),
(55, 9, NULL, 'Personal de demostración', 'liberacion', 0.000, 10.000, 10.000, 0.000, 0.000, 0.000, 'Movimiento de inventario de demostración.', NULL, NULL, '2026-10-26 14:30:14', 10.000, 10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbusuarios`
--

CREATE TABLE `tbusuarios` (
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `id_rol` int(10) UNSIGNED NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `nombres` varchar(100) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `correo` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `session_version` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `cuenta_revision` int(10) UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbusuarios`
--

INSERT INTO `tbusuarios` (`id_usuario`, `id_rol`, `nombre_usuario`, `nombres`, `apellidos`, `correo`, `password_hash`, `estado`, `created_at`, `session_version`, `cuenta_revision`) VALUES


(2, 1, 'admin-demo', 'Administrador', 'Demo', 'admin@admin.com', '$2y$10$rKwZOOTyzQBh5HRcQB2mhO.hjPTS59hfxXEAdAXGEs9PnuMesCbxa', 'activo', '2026-10-07 22:46:25', 0, 0),
(3, 3, 'cliente-demo', 'Cliente', 'Demo', 'cliente@clinete.com', '$2y$10$.OIyGc3PUECLsoRkv.eVgu0T4iXQ/A.pPxuJncp/Mib1sCZgfnchy', 'activo', '2026-10-08 00:35:33', 0, 0),
(4, 3, 'demo-user-4', 'Cuenta', 'Demo', 'demo-user-4@tecno-star.test', '!DEMO-ACCOUNT-LOCKED!', 'inactivo', '2026-10-19 00:33:49', 0, 0),
(5, 3, 'demo-user-5', 'Cuenta', 'Demo', 'demo-user-5@tecno-star.test', '!DEMO-ACCOUNT-LOCKED!', 'inactivo', '2026-10-22 01:18:33', 0, 0),
(6, 3, 'demo-user-6', 'Cuenta', 'Demo', 'demo-user-6@tecno-star.test', '!DEMO-ACCOUNT-LOCKED!', 'inactivo', '2026-10-22 10:05:20', 0, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbventas`
--

CREATE TABLE `tbventas` (
  `id_venta` int(10) UNSIGNED NOT NULL,
  `codigo_venta` varchar(30) NOT NULL,
  `id_pedido` int(10) UNSIGNED DEFAULT NULL,
  `id_cliente` int(10) UNSIGNED DEFAULT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `fecha_pago` datetime DEFAULT NULL,
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `metodo_pago` varchar(30) DEFAULT NULL,
  `estado` enum('borrador','pagada','anulada') NOT NULL DEFAULT 'borrador',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `recibido` decimal(12,2) DEFAULT NULL,
  `cambio` decimal(12,2) DEFAULT NULL,
  `responsable_cobro` varchar(201) DEFAULT NULL,
  `cobro_solicitud` char(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `cobro_huella` char(64) CHARACTER SET ascii COLLATE ascii_bin DEFAULT NULL,
  `pedido_cobrado` int(10) UNSIGNED GENERATED ALWAYS AS (case when `cobro_solicitud` is not null then `id_pedido` else NULL end) STORED,
  `id_solicitud_servicio` int(10) UNSIGNED DEFAULT NULL
) ;

--
-- Volcado de datos para la tabla `tbventas`
--

INSERT INTO `tbventas` (`id_venta`, `codigo_venta`, `id_pedido`, `id_cliente`, `id_usuario`, `fecha_pago`, `total`, `metodo_pago`, `estado`, `created_at`, `recibido`, `cambio`, `responsable_cobro`, `cobro_solicitud`, `cobro_huella`, `id_solicitud_servicio`) VALUES


(1, 'VT-261019-4231F74B0EF7', 5, 2, 2, '2026-10-20 04:28:09', 9.99, 'efectivo', 'pagada', '2026-10-20 04:28:09', 10.00, 0.01, 'Personal de demostración', 'b46052e916fbf7333631e056792c34a56f0cbbe64bac5da7c5ad83e71a83462c', '707da7c2926ae9a87860163037da1d33dbd23711eff44e36a5e911dd36c39502', NULL),
(2, 'VT-261020-4F7BE801CBC6', 8, 2, 2, '2026-10-20 15:53:22', 18.99, 'efectivo', 'pagada', '2026-10-20 15:53:22', 20.00, 1.01, 'Personal de demostración', '9e415bb6924666464b4cdffe08d3bb62f1a65677e97b56101aa54cee5cfda8f5', '58f4e6284868bbd0b3a11f72dabe1a031e6bb2ce21013f04e57b2b22b7491523', NULL),
(3, 'VT-261022-681BA8FF2E38', 9, 5, 2, '2026-10-22 16:08:29', 78.98, 'efectivo', 'pagada', '2026-10-22 16:08:29', 79.13, 0.15, 'Personal de demostración', 'f0fba58bbbc921783846f70bd5501194330e424ee259535953c729cf26df0dae', 'acb790433bd0c5a19883c4315b4cea30e7ab87034b717090699a19e7fba6ea75', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tbventasanulaciones`
--

CREATE TABLE `tbventasanulaciones` (
  `id_anulacion` bigint(20) UNSIGNED NOT NULL,
  `id_venta` int(10) UNSIGNED NOT NULL,
  `id_usuario` int(10) UNSIGNED NOT NULL,
  `responsable` varchar(201) NOT NULL,
  `reembolso` decimal(12,2) NOT NULL,
  `stock_devuelto` tinyint(1) NOT NULL,
  `motivo` varchar(500) NOT NULL,
  `solicitud` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `huella` char(64) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  `created_at` datetime NOT NULL
) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `google_module_migrations`
--
ALTER TABLE `google_module_migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indices de la tabla `inventory_module_migrations`
--
ALTER TABLE `inventory_module_migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indices de la tabla `orders_module_migrations`
--
ALTER TABLE `orders_module_migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indices de la tabla `ready_mail_module_migrations`
--
ALTER TABLE `ready_mail_module_migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indices de la tabla `reinvestment_module_migrations`
--
ALTER TABLE `reinvestment_module_migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indices de la tabla `sales_accounts_module_migrations`
--
ALTER TABLE `sales_accounts_module_migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indices de la tabla `service_module_migrations`
--
ALTER TABLE `service_module_migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indices de la tabla `shopping_module_migrations`
--
ALTER TABLE `shopping_module_migrations`
  ADD PRIMARY KEY (`version`);

--
-- Indices de la tabla `tbcarritos`
--
ALTER TABLE `tbcarritos`
  ADD PRIMARY KEY (`id_carrito`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `tbcategorias`
--
ALTER TABLE `tbcategorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `uq_categorias_nombre` (`nombre_categoria`);

--
-- Indices de la tabla `tbclaverecuperacion`
--
ALTER TABLE `tbclaverecuperacion`
  ADD PRIMARY KEY (`id_recuperacion`),
  ADD UNIQUE KEY `token_hash` (`token_hash`),
  ADD KEY `idx_recuperacion_usuario` (`id_usuario`,`created_at`);

--
-- Indices de la tabla `tbclientes`
--
ALTER TABLE `tbclientes`
  ADD PRIMARY KEY (`id_cliente`),
  ADD UNIQUE KEY `uq_clientes_usuario` (`id_usuario`),
  ADD KEY `ix_clientes_nombre` (`apellidos`,`nombres`),
  ADD KEY `ix_clientes_telefono` (`telefono`);

--
-- Indices de la tabla `tbcuentashistorial`
--
ALTER TABLE `tbcuentashistorial`
  ADD PRIMARY KEY (`id_evento`),
  ADD KEY `fk_cuentahistorial_actor` (`id_actor`),
  ADD KEY `idx_cuentahistorial_usuario` (`id_usuario`,`id_evento`);

--
-- Indices de la tabla `tbdetallecarritos`
--
ALTER TABLE `tbdetallecarritos`
  ADD PRIMARY KEY (`id_detalle`),
  ADD UNIQUE KEY `uq_detalle_carrito_producto` (`id_carrito`,`id_producto`),
  ADD KEY `fk_detallecarrito_producto` (`id_producto`);

--
-- Indices de la tabla `tbdetallelistasdeseos`
--
ALTER TABLE `tbdetallelistasdeseos`
  ADD PRIMARY KEY (`id_lista`,`id_producto`),
  ADD KEY `fk_detallelista_producto` (`id_producto`);

--
-- Indices de la tabla `tbdetallepedido`
--
ALTER TABLE `tbdetallepedido`
  ADD PRIMARY KEY (`id_detalle_pedido`),
  ADD UNIQUE KEY `uq_detalle_pedido_producto` (`id_pedido`,`id_producto`),
  ADD KEY `fk_detalle_pedido_producto` (`id_producto`);

--
-- Indices de la tabla `tbdetalleventa`
--
ALTER TABLE `tbdetalleventa`
  ADD PRIMARY KEY (`id_detalle_venta`),
  ADD UNIQUE KEY `uq_detalle_venta_producto` (`id_venta`,`id_producto`),
  ADD KEY `fk_detalle_venta_producto` (`id_producto`);

--
-- Indices de la tabla `tbdetalleventaservicio`
--
ALTER TABLE `tbdetalleventaservicio`
  ADD PRIMARY KEY (`id_detalle_servicio`),
  ADD UNIQUE KEY `uq_detalle_venta_servicio` (`id_venta`,`id_servicio`),
  ADD KEY `fk_detalle_servicio_catalogo` (`id_servicio`);

--
-- Indices de la tabla `tbentregaspedidos`
--
ALTER TABLE `tbentregaspedidos`
  ADD PRIMARY KEY (`id_entrega`),
  ADD UNIQUE KEY `uq_entregas_pedido` (`id_pedido`),
  ADD KEY `fk_entregas_usuario` (`id_usuario`);

--
-- Indices de la tabla `tbgoogleidentidades`
--
ALTER TABLE `tbgoogleidentidades`
  ADD PRIMARY KEY (`google_subject`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `tbinventario`
--
ALTER TABLE `tbinventario`
  ADD PRIMARY KEY (`id_inventario`),
  ADD UNIQUE KEY `uq_inventario_producto` (`id_producto`);

--
-- Indices de la tabla `tblistasdeseos`
--
ALTER TABLE `tblistasdeseos`
  ADD PRIMARY KEY (`id_lista`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `tbmarcas`
--
ALTER TABLE `tbmarcas`
  ADD PRIMARY KEY (`id_marca`),
  ADD UNIQUE KEY `uq_marcas_nombre` (`nombre_marca`);

--
-- Indices de la tabla `tbmodeloscompatibles`
--
ALTER TABLE `tbmodeloscompatibles`
  ADD PRIMARY KEY (`id_modelo`),
  ADD UNIQUE KEY `uq_modelos_marca_nombre` (`id_marca`,`nombre_modelo`);

--
-- Indices de la tabla `tbmovimientosinventario`
--
ALTER TABLE `tbmovimientosinventario`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD KEY `ix_movimientos_producto_fecha` (`id_producto`,`created_at`),
  ADD KEY `fk_movimientos_usuario` (`id_usuario`),
  ADD KEY `fk_movimientos_pedido` (`id_pedido`),
  ADD KEY `fk_movimientos_venta` (`id_venta`),
  ADD KEY `fk_movimientos_servicio` (`id_detalle_servicio`);

--
-- Indices de la tabla `tbpedidos`
--
ALTER TABLE `tbpedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD UNIQUE KEY `uq_pedidos_codigo` (`codigo_pedido`),
  ADD UNIQUE KEY `uq_retiro_codigo` (`codigo_pedido`),
  ADD UNIQUE KEY `uq_retiro_solicitud` (`retiro_solicitud`),
  ADD KEY `ix_pedidos_estado_vencimiento` (`estado`,`fecha_vencimiento`),
  ADD KEY `ix_pedidos_cliente_fecha` (`id_cliente`,`created_at`),
  ADD KEY `fk_pedidos_confirmador` (`id_usuario_confirmacion`),
  ADD KEY `idx_retiro_owner` (`id_usuario`,`created_at`),
  ADD KEY `idx_retiro_expira` (`reserva_activa`,`fecha_vencimiento`);

--
-- Indices de la tabla `tbpersonalpermisos`
--
ALTER TABLE `tbpersonalpermisos`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `fk_personalpermiso_actor` (`id_actor`);

--
-- Indices de la tabla `tbproductocompatibilidad`
--
ALTER TABLE `tbproductocompatibilidad`
  ADD PRIMARY KEY (`id_compatibilidad`),
  ADD UNIQUE KEY `uq_compatibilidad_producto_modelo` (`id_producto`,`id_modelo`),
  ADD KEY `fk_compatibilidad_modelo` (`id_modelo`);

--
-- Indices de la tabla `tbproductos`
--
ALTER TABLE `tbproductos`
  ADD PRIMARY KEY (`id_producto`),
  ADD UNIQUE KEY `uq_productos_codigo` (`codigo_producto`),
  ADD KEY `ix_productos_nombre` (`nombre_producto`),
  ADD KEY `fk_productos_categoria` (`id_categoria`),
  ADD KEY `fk_productos_marca` (`id_marca`);

--
-- Indices de la tabla `tbreinversiones`
--
ALTER TABLE `tbreinversiones`
  ADD PRIMARY KEY (`id_reinversion`),
  ADD UNIQUE KEY `uq_reinversiones_venta` (`id_venta`);

--
-- Indices de la tabla `tbretiocorreos`
--
ALTER TABLE `tbretiocorreos`
  ADD PRIMARY KEY (`id_correo`),
  ADD UNIQUE KEY `uq_retiocorreo_evento` (`id_pedido`,`tipo`),
  ADD KEY `idx_retiocorreo_pendiente` (`estado`,`proximo_intento`),
  ADD KEY `idx_retiocorreo_pedido` (`id_pedido`);

--
-- Indices de la tabla `tbretirodetalles`
--
ALTER TABLE `tbretirodetalles`
  ADD PRIMARY KEY (`id_detalle`),
  ADD UNIQUE KEY `uq_retiro_producto` (`id_pedido`,`id_producto`),
  ADD KEY `fk_retirodetalle_producto` (`id_producto`);

--
-- Indices de la tabla `tbretirohistorial`
--
ALTER TABLE `tbretirohistorial`
  ADD PRIMARY KEY (`id_historial`),
  ADD KEY `fk_retirohistorial_usuario` (`id_usuario`),
  ADD KEY `idx_retiro_historial` (`id_pedido`,`id_historial`);

--
-- Indices de la tabla `tbroles`
--
ALTER TABLE `tbroles`
  ADD PRIMARY KEY (`id_rol`),
  ADD UNIQUE KEY `uq_roles_nombre` (`nombre_rol`);

--
-- Indices de la tabla `tbservicioarchivos`
--
ALTER TABLE `tbservicioarchivos`
  ADD PRIMARY KEY (`id_archivo`),
  ADD UNIQUE KEY `ruta` (`ruta`),
  ADD KEY `fk_servicioarchivo_solicitud` (`id_solicitud`),
  ADD KEY `fk_servicioarchivo_actor` (`id_actor`);

--
-- Indices de la tabla `tbserviciocatalogo`
--
ALTER TABLE `tbserviciocatalogo`
  ADD PRIMARY KEY (`id_servicio`),
  ADD KEY `fk_servicio_categoria` (`id_categoria`);

--
-- Indices de la tabla `tbserviciocategorias`
--
ALTER TABLE `tbserviciocategorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `tbserviciocorreos`
--
ALTER TABLE `tbserviciocorreos`
  ADD PRIMARY KEY (`id_correo`),
  ADD UNIQUE KEY `uq_serviciocorreo_evento` (`id_solicitud`,`revision`,`tipo`);

--
-- Indices de la tabla `tbserviciocotizaciones`
--
ALTER TABLE `tbserviciocotizaciones`
  ADD PRIMARY KEY (`id_cotizacion`),
  ADD UNIQUE KEY `uq_serviciocotizacion_version` (`id_solicitud`,`version`),
  ADD KEY `fk_serviciocotizacion_actor` (`id_actor`);

--
-- Indices de la tabla `tbserviciohistorial`
--
ALTER TABLE `tbserviciohistorial`
  ADD PRIMARY KEY (`id_evento`),
  ADD UNIQUE KEY `operacion` (`operacion`),
  ADD KEY `fk_serviciohistorial_solicitud` (`id_solicitud`),
  ADD KEY `fk_serviciohistorial_actor` (`id_actor`);

--
-- Indices de la tabla `tbservicios`
--
ALTER TABLE `tbservicios`
  ADD PRIMARY KEY (`id_servicio`),
  ADD UNIQUE KEY `uq_servicios_nombre` (`nombre_servicio`);

--
-- Indices de la tabla `tbserviciosolicitudes`
--
ALTER TABLE `tbserviciosolicitudes`
  ADD PRIMARY KEY (`id_solicitud`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD UNIQUE KEY `operacion` (`operacion`),
  ADD KEY `fk_serviciosolicitud_servicio` (`id_servicio`),
  ADD KEY `idx_serviciosolicitud_usuario` (`id_usuario`,`id_solicitud`),
  ADD KEY `idx_serviciosolicitud_estado` (`estado`,`id_solicitud`);

--
-- Indices de la tabla `tbshoppingoperaciones`
--
ALTER TABLE `tbshoppingoperaciones`
  ADD PRIMARY KEY (`solicitud`),
  ADD KEY `idx_shoppingoperation_usuario` (`id_usuario`,`created_at`);

--
-- Indices de la tabla `tbstockmovimientos`
--
ALTER TABLE `tbstockmovimientos`
  ADD PRIMARY KEY (`id_movimiento`),
  ADD UNIQUE KEY `solicitud` (`solicitud`),
  ADD KEY `idx_movimiento_producto_fecha` (`id_producto`,`created_at`,`id_movimiento`),
  ADD KEY `idx_movimiento_fecha` (`created_at`,`id_movimiento`),
  ADD KEY `idx_movimiento_usuario` (`id_usuario`),
  ADD KEY `fk_stock_retiro` (`id_pedido`);

--
-- Indices de la tabla `tbusuarios`
--
ALTER TABLE `tbusuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `uq_usuarios_nombre` (`nombre_usuario`),
  ADD UNIQUE KEY `uq_usuarios_correo` (`correo`),
  ADD KEY `fk_usuarios_rol` (`id_rol`);

--
-- Indices de la tabla `tbventas`
--
ALTER TABLE `tbventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD UNIQUE KEY `uq_ventas_codigo` (`codigo_venta`),
  ADD UNIQUE KEY `uq_ventas_pedido` (`id_pedido`),
  ADD UNIQUE KEY `uq_cobro_solicitud` (`cobro_solicitud`),
  ADD UNIQUE KEY `uq_cobro_pedido` (`pedido_cobrado`),
  ADD UNIQUE KEY `id_solicitud_servicio` (`id_solicitud_servicio`),
  ADD KEY `ix_ventas_estado_pago` (`estado`,`fecha_pago`),
  ADD KEY `ix_ventas_cliente_pago` (`id_cliente`,`fecha_pago`),
  ADD KEY `fk_ventas_usuario` (`id_usuario`),
  ADD KEY `idx_cobro_fecha` (`fecha_pago`,`estado`);

--
-- Indices de la tabla `tbventasanulaciones`
--
ALTER TABLE `tbventasanulaciones`
  ADD PRIMARY KEY (`id_anulacion`),
  ADD UNIQUE KEY `id_venta` (`id_venta`),
  ADD UNIQUE KEY `solicitud` (`solicitud`),
  ADD KEY `fk_anulacion_usuario` (`id_usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `tbcarritos`
--
ALTER TABLE `tbcarritos`
  MODIFY `id_carrito` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT de la tabla `tbcategorias`
--
ALTER TABLE `tbcategorias`
  MODIFY `id_categoria` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `tbclaverecuperacion`
--
ALTER TABLE `tbclaverecuperacion`
  MODIFY `id_recuperacion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tbclientes`
--
ALTER TABLE `tbclientes`
  MODIFY `id_cliente` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `tbcuentashistorial`
--
ALTER TABLE `tbcuentashistorial`
  MODIFY `id_evento` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbdetallecarritos`
--
ALTER TABLE `tbdetallecarritos`
  MODIFY `id_detalle` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=124;

--
-- AUTO_INCREMENT de la tabla `tbdetallepedido`
--
ALTER TABLE `tbdetallepedido`
  MODIFY `id_detalle_pedido` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbdetalleventa`
--
ALTER TABLE `tbdetalleventa`
  MODIFY `id_detalle_venta` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbdetalleventaservicio`
--
ALTER TABLE `tbdetalleventaservicio`
  MODIFY `id_detalle_servicio` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbentregaspedidos`
--
ALTER TABLE `tbentregaspedidos`
  MODIFY `id_entrega` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbinventario`
--
ALTER TABLE `tbinventario`
  MODIFY `id_inventario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tblistasdeseos`
--
ALTER TABLE `tblistasdeseos`
  MODIFY `id_lista` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=47;

--
-- AUTO_INCREMENT de la tabla `tbmarcas`
--
ALTER TABLE `tbmarcas`
  MODIFY `id_marca` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `tbmodeloscompatibles`
--
ALTER TABLE `tbmodeloscompatibles`
  MODIFY `id_modelo` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbmovimientosinventario`
--
ALTER TABLE `tbmovimientosinventario`
  MODIFY `id_movimiento` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbpedidos`
--
ALTER TABLE `tbpedidos`
  MODIFY `id_pedido` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbproductocompatibilidad`
--
ALTER TABLE `tbproductocompatibilidad`
  MODIFY `id_compatibilidad` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbproductos`
--
ALTER TABLE `tbproductos`
  MODIFY `id_producto` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbreinversiones`
--
ALTER TABLE `tbreinversiones`
  MODIFY `id_reinversion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbretiocorreos`
--
ALTER TABLE `tbretiocorreos`
  MODIFY `id_correo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `tbretirodetalles`
--
ALTER TABLE `tbretirodetalles`
  MODIFY `id_detalle` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbretirohistorial`
--
ALTER TABLE `tbretirohistorial`
  MODIFY `id_historial` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `tbroles`
--
ALTER TABLE `tbroles`
  MODIFY `id_rol` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tbservicioarchivos`
--
ALTER TABLE `tbservicioarchivos`
  MODIFY `id_archivo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tbserviciocatalogo`
--
ALTER TABLE `tbserviciocatalogo`
  MODIFY `id_servicio` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbserviciocategorias`
--
ALTER TABLE `tbserviciocategorias`
  MODIFY `id_categoria` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tbserviciocorreos`
--
ALTER TABLE `tbserviciocorreos`
  MODIFY `id_correo` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tbserviciocotizaciones`
--
ALTER TABLE `tbserviciocotizaciones`
  MODIFY `id_cotizacion` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbserviciohistorial`
--
ALTER TABLE `tbserviciohistorial`
  MODIFY `id_evento` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tbservicios`
--
ALTER TABLE `tbservicios`
  MODIFY `id_servicio` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbserviciosolicitudes`
--
ALTER TABLE `tbserviciosolicitudes`
  MODIFY `id_solicitud` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbstockmovimientos`
--
ALTER TABLE `tbstockmovimientos`
  MODIFY `id_movimiento` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbusuarios`
--
ALTER TABLE `tbusuarios`
  MODIFY `id_usuario` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `tbventas`
--
ALTER TABLE `tbventas`
  MODIFY `id_venta` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tbventasanulaciones`
--
ALTER TABLE `tbventasanulaciones`
  MODIFY `id_anulacion` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tbcarritos`
--
ALTER TABLE `tbcarritos`
  ADD CONSTRAINT `fk_tbcarritos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tbclaverecuperacion`
--
ALTER TABLE `tbclaverecuperacion`
  ADD CONSTRAINT `fk_claverecuperacion_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`);

--
-- Filtros para la tabla `tbclientes`
--
ALTER TABLE `tbclientes`
  ADD CONSTRAINT `fk_clientes_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`);

--
-- Filtros para la tabla `tbcuentashistorial`
--
ALTER TABLE `tbcuentashistorial`
  ADD CONSTRAINT `fk_cuentahistorial_actor` FOREIGN KEY (`id_actor`) REFERENCES `tbusuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_cuentahistorial_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`);

--
-- Filtros para la tabla `tbdetallecarritos`
--
ALTER TABLE `tbdetallecarritos`
  ADD CONSTRAINT `fk_detallecarrito_carrito` FOREIGN KEY (`id_carrito`) REFERENCES `tbcarritos` (`id_carrito`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detallecarrito_producto` FOREIGN KEY (`id_producto`) REFERENCES `tbproductos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tbdetallelistasdeseos`
--
ALTER TABLE `tbdetallelistasdeseos`
  ADD CONSTRAINT `fk_detallelista_lista` FOREIGN KEY (`id_lista`) REFERENCES `tblistasdeseos` (`id_lista`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detallelista_producto` FOREIGN KEY (`id_producto`) REFERENCES `tbproductos` (`id_producto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tbdetallepedido`
--
ALTER TABLE `tbdetallepedido`
  ADD CONSTRAINT `fk_detalle_pedido_cabecera` FOREIGN KEY (`id_pedido`) REFERENCES `tbpedidos` (`id_pedido`),
  ADD CONSTRAINT `fk_detalle_pedido_producto` FOREIGN KEY (`id_producto`) REFERENCES `tbproductos` (`id_producto`);

--
-- Filtros para la tabla `tbdetalleventa`
--
ALTER TABLE `tbdetalleventa`
  ADD CONSTRAINT `fk_detalle_venta_cabecera` FOREIGN KEY (`id_venta`) REFERENCES `tbventas` (`id_venta`),
  ADD CONSTRAINT `fk_detalle_venta_producto` FOREIGN KEY (`id_producto`) REFERENCES `tbproductos` (`id_producto`);

--
-- Filtros para la tabla `tbdetalleventaservicio`
--
ALTER TABLE `tbdetalleventaservicio`
  ADD CONSTRAINT `fk_detalle_servicio_catalogo` FOREIGN KEY (`id_servicio`) REFERENCES `tbservicios` (`id_servicio`),
  ADD CONSTRAINT `fk_detalle_servicio_venta` FOREIGN KEY (`id_venta`) REFERENCES `tbventas` (`id_venta`);

--
-- Filtros para la tabla `tbentregaspedidos`
--
ALTER TABLE `tbentregaspedidos`
  ADD CONSTRAINT `fk_entregas_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `tbpedidos` (`id_pedido`),
  ADD CONSTRAINT `fk_entregas_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`);

--
-- Filtros para la tabla `tbgoogleidentidades`
--
ALTER TABLE `tbgoogleidentidades`
  ADD CONSTRAINT `fk_google_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`);

--
-- Filtros para la tabla `tbinventario`
--
ALTER TABLE `tbinventario`
  ADD CONSTRAINT `fk_inventario_producto` FOREIGN KEY (`id_producto`) REFERENCES `tbproductos` (`id_producto`);

--
-- Filtros para la tabla `tblistasdeseos`
--
ALTER TABLE `tblistasdeseos`
  ADD CONSTRAINT `fk_tblistas_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `tbmodeloscompatibles`
--
ALTER TABLE `tbmodeloscompatibles`
  ADD CONSTRAINT `fk_modelos_marca` FOREIGN KEY (`id_marca`) REFERENCES `tbmarcas` (`id_marca`);

--
-- Filtros para la tabla `tbmovimientosinventario`
--
ALTER TABLE `tbmovimientosinventario`
  ADD CONSTRAINT `fk_movimientos_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `tbpedidos` (`id_pedido`),
  ADD CONSTRAINT `fk_movimientos_producto` FOREIGN KEY (`id_producto`) REFERENCES `tbproductos` (`id_producto`),
  ADD CONSTRAINT `fk_movimientos_servicio` FOREIGN KEY (`id_detalle_servicio`) REFERENCES `tbdetalleventaservicio` (`id_detalle_servicio`),
  ADD CONSTRAINT `fk_movimientos_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_movimientos_venta` FOREIGN KEY (`id_venta`) REFERENCES `tbventas` (`id_venta`);

--
-- Filtros para la tabla `tbpedidos`
--
ALTER TABLE `tbpedidos`
  ADD CONSTRAINT `fk_pedidos_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbclientes` (`id_cliente`),
  ADD CONSTRAINT `fk_pedidos_confirmador` FOREIGN KEY (`id_usuario_confirmacion`) REFERENCES `tbusuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_retiro_owner` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`);

--
-- Filtros para la tabla `tbpersonalpermisos`
--
ALTER TABLE `tbpersonalpermisos`
  ADD CONSTRAINT `fk_personalpermiso_actor` FOREIGN KEY (`id_actor`) REFERENCES `tbusuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_personalpermiso_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`);

--
-- Filtros para la tabla `tbproductocompatibilidad`
--
ALTER TABLE `tbproductocompatibilidad`
  ADD CONSTRAINT `fk_compatibilidad_modelo` FOREIGN KEY (`id_modelo`) REFERENCES `tbmodeloscompatibles` (`id_modelo`),
  ADD CONSTRAINT `fk_compatibilidad_producto` FOREIGN KEY (`id_producto`) REFERENCES `tbproductos` (`id_producto`);

--
-- Filtros para la tabla `tbproductos`
--
ALTER TABLE `tbproductos`
  ADD CONSTRAINT `fk_productos_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `tbcategorias` (`id_categoria`),
  ADD CONSTRAINT `fk_productos_marca` FOREIGN KEY (`id_marca`) REFERENCES `tbmarcas` (`id_marca`);

--
-- Filtros para la tabla `tbreinversiones`
--
ALTER TABLE `tbreinversiones`
  ADD CONSTRAINT `fk_reinversiones_venta` FOREIGN KEY (`id_venta`) REFERENCES `tbventas` (`id_venta`);

--
-- Filtros para la tabla `tbretiocorreos`
--
ALTER TABLE `tbretiocorreos`
  ADD CONSTRAINT `fk_retiocorreo_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `tbpedidos` (`id_pedido`);

--
-- Filtros para la tabla `tbretirodetalles`
--
ALTER TABLE `tbretirodetalles`
  ADD CONSTRAINT `fk_retirodetalle_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `tbpedidos` (`id_pedido`),
  ADD CONSTRAINT `fk_retirodetalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `tbproductos` (`id_producto`);

--
-- Filtros para la tabla `tbretirohistorial`
--
ALTER TABLE `tbretirohistorial`
  ADD CONSTRAINT `fk_retirohistorial_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `tbpedidos` (`id_pedido`),
  ADD CONSTRAINT `fk_retirohistorial_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`);

--
-- Filtros para la tabla `tbservicioarchivos`
--
ALTER TABLE `tbservicioarchivos`
  ADD CONSTRAINT `fk_servicioarchivo_actor` FOREIGN KEY (`id_actor`) REFERENCES `tbusuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_servicioarchivo_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `tbserviciosolicitudes` (`id_solicitud`);

--
-- Filtros para la tabla `tbserviciocatalogo`
--
ALTER TABLE `tbserviciocatalogo`
  ADD CONSTRAINT `fk_servicio_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `tbserviciocategorias` (`id_categoria`);

--
-- Filtros para la tabla `tbserviciocorreos`
--
ALTER TABLE `tbserviciocorreos`
  ADD CONSTRAINT `fk_serviciocorreo_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `tbserviciosolicitudes` (`id_solicitud`);

--
-- Filtros para la tabla `tbserviciocotizaciones`
--
ALTER TABLE `tbserviciocotizaciones`
  ADD CONSTRAINT `fk_serviciocotizacion_actor` FOREIGN KEY (`id_actor`) REFERENCES `tbusuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_serviciocotizacion_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `tbserviciosolicitudes` (`id_solicitud`);

--
-- Filtros para la tabla `tbserviciohistorial`
--
ALTER TABLE `tbserviciohistorial`
  ADD CONSTRAINT `fk_serviciohistorial_actor` FOREIGN KEY (`id_actor`) REFERENCES `tbusuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_serviciohistorial_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `tbserviciosolicitudes` (`id_solicitud`);

--
-- Filtros para la tabla `tbserviciosolicitudes`
--
ALTER TABLE `tbserviciosolicitudes`
  ADD CONSTRAINT `fk_serviciosolicitud_servicio` FOREIGN KEY (`id_servicio`) REFERENCES `tbserviciocatalogo` (`id_servicio`),
  ADD CONSTRAINT `fk_serviciosolicitud_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`);

--
-- Filtros para la tabla `tbshoppingoperaciones`
--
ALTER TABLE `tbshoppingoperaciones`
  ADD CONSTRAINT `fk_shoppingoperation_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`);

--
-- Filtros para la tabla `tbstockmovimientos`
--
ALTER TABLE `tbstockmovimientos`
  ADD CONSTRAINT `fk_stock_retiro` FOREIGN KEY (`id_pedido`) REFERENCES `tbpedidos` (`id_pedido`),
  ADD CONSTRAINT `fk_stockaudit_producto` FOREIGN KEY (`id_producto`) REFERENCES `tbproductos` (`id_producto`),
  ADD CONSTRAINT `fk_stockaudit_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`);

--
-- Filtros para la tabla `tbusuarios`
--
ALTER TABLE `tbusuarios`
  ADD CONSTRAINT `fk_usuarios_rol` FOREIGN KEY (`id_rol`) REFERENCES `tbroles` (`id_rol`);

--
-- Filtros para la tabla `tbventas`
--
ALTER TABLE `tbventas`
  ADD CONSTRAINT `fk_venta_servicio` FOREIGN KEY (`id_solicitud_servicio`) REFERENCES `tbserviciosolicitudes` (`id_solicitud`),
  ADD CONSTRAINT `fk_ventas_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `tbclientes` (`id_cliente`),
  ADD CONSTRAINT `fk_ventas_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `tbpedidos` (`id_pedido`),
  ADD CONSTRAINT `fk_ventas_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`);

--
-- Filtros para la tabla `tbventasanulaciones`
--
ALTER TABLE `tbventasanulaciones`
  ADD CONSTRAINT `fk_anulacion_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `tbusuarios` (`id_usuario`),
  ADD CONSTRAINT `fk_anulacion_venta` FOREIGN KEY (`id_venta`) REFERENCES `tbventas` (`id_venta`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
