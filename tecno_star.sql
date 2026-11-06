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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tbclaverecuperacion`
--

INSERT INTO `tbclaverecuperacion` (`id_recuperacion`, `id_usuario`, `token_hash`, `session_version`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 5, '8e947ba7a8e9925310bfc26496e472ba85ef46422a269d0493a8d9815c25d664', 0, '2026-10-22 16:15:04', NULL, '2026-10-22 15:55:04'),
(2, 6, '2cde633856d8822f381c07a4c73f358c7643befabe9eee454070c5d3478af039', 0, '2026-10-22 16:39:15', NULL, '2026-10-22 16:19:15');

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
(2, 3, 'Cliente', 'Tecno Star', '00000000', 'cliente@cliente.com', 'activo', '2026-10-08 00:35:33'),
(3, 4, 'client', 'client', '7650-9900', 'henriquezquinterosmarcosjusue@gmail.com', 'activo', '2026-10-19 00:33:49'),
(4, 5, 'Marcos', 'Henriquez', '+50378089999', 'henriquezquinterosmarcosjusue@gmail.com', 'activo', '2026-10-22 01:18:33'),
(5, 6, 'MARCOS JOSUE', 'HENRIQUEZ QUINTEROS', '76509900', 'marco.henriquez@catolica.edu.sv', 'activo', '2026-10-22 10:05:20');

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
('114917447596150731628', 6, '2026-10-22 10:05:20'),
('116169328763784463385', 5, '2026-10-22 01:18:33');

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
(1, 'TS-261019-10A71776ED85', 3, NULL, '2026-10-19 06:35:19', '2026-10-20 06:35:19', 68.98, 'cancelado', NULL, '2026-10-19 06:35:19', 4, '961f993a48f71241f7c3ebf672abfb040aa1937f9b083d16263acd1577b17263', 'a895e7f4c236bb37792bc56911b6b2355178c6e0083eeffb389dafcf81dec9de', 'client client', '7650-9900', 'henriquezquinterosmarcosjusue@gmail.com', '', 0, '2026-10-20 15:50:02'),
(2, 'TS-261019-83495A8D4E51', 2, NULL, '2026-10-19 16:46:33', '2026-10-20 16:46:33', 18.99, 'cancelado', NULL, '2026-10-19 16:46:33', 3, 'f1620e783b88aaba429aff122a1b7a4514e9ffcb5ebdf064fe426840863bda47', '5a47c5a7dcbb74e123cf1687b41025a11d3ff9b22a04a91b0639435a90ffda8a', 'Cliente Tecno Star', '7000-0000', 'henriquezquinterosmarcosjusue@gmail.com', '', 0, '2026-10-21 03:10:47'),
(3, 'TS-261019-DCF6DDC1CE09', 2, NULL, '2026-10-19 19:06:53', '2026-10-20 19:06:53', 18.99, 'cancelado', NULL, '2026-10-19 19:06:53', 3, '8a01b85e2271126b6095ab01baeb8d8c81e99dabde71d9a70a0cda95a26173de', '5651c560b6342a0fc5866d57fb2aa2212647e229b41e64281bbd56693f43b416', 'Cliente Tecno Star', '7373-7373', 'elbindanielrivas@icloud.com', 'gokk', 0, '2026-10-21 03:10:47'),
(4, 'TS-261019-EC15B8353AB6', 2, NULL, '2026-10-20 04:02:27', '2026-10-21 04:02:27', 131.45, 'cancelado', NULL, '2026-10-20 04:02:27', 3, 'ef955e39aa4930c837737e59c2a81e326578f6be077dbf872d23719c773f3cc7', '0d41ba846c26b7c9eadb92c1f9efadb972125ff20d1aa524983665a1846506b0', 'Cliente Tecno Star', '7000-0000', 'henriquezquinterosmarcosjusue@gmail.com', '', 0, '2026-10-22 04:37:54'),
(5, 'TS-261019-B1008746AE7F', 2, NULL, '2026-10-20 04:09:23', '2026-10-21 04:09:23', 9.99, 'entregado', NULL, '2026-10-20 04:09:23', 3, '0a7e59f1261db6c77c7cdb65a954610ae6b98c679033d57863f5e57767c9a53e', '84ba7f8bf6a38e2ebd06384eca4232c8e1ad24d63e6cf669256f686c4fc8f56f', 'Cliente Tecno Star', '7000-0000', 'henriquezquinterosmarcosjusue@gmail.com', '', 0, '2026-10-20 04:28:09'),
(6, 'TS-261019-A54BCC43D92D', 2, NULL, '2026-10-20 04:18:13', '2026-10-21 04:18:13', 12.50, 'cancelado', NULL, '2026-10-20 04:18:13', 3, '6096a3bbbc7a640645c66ec24e4590a4d3b50f9897a6c6b0ec171f5b729bb1c2', 'a4d1f0cee659ed9fbd742d3f682ad2d8124d2541c68237378573db37536d733e', 'Cliente Tecno Star', '7000-0000', 'henriquezquinterosmarcosjusue@gmail.com', '', 0, '2026-10-22 04:37:54'),
(7, 'TS-261019-9C30DDBF7DEA', 2, NULL, '2026-10-20 04:21:36', '2026-10-21 04:21:36', 34.99, 'cancelado', NULL, '2026-10-20 04:21:36', 3, '468330cc9c0ed164d59d6957901022c66d3f06a9a4c308fb114f640133602d58', 'babdbac105b46aabf816bf97c817581f7a31130074c2163d86e10f544a63f0f9', 'Cliente Tecno Star', '70000000', 'cliente@cliente.com', '', 0, '2026-10-22 04:37:54'),
(8, 'TS-261020-E1DA441229FF', 2, NULL, '2026-10-20 15:51:03', '2026-10-21 15:51:03', 18.99, 'entregado', NULL, '2026-10-20 15:51:03', 3, '4852144d77d2c16eaf1a87d518ee4f7769d73354a4042f4b56183307734cd1e6', '3aa2d25dd25a355e4941d698f0bda73d7f67f4890beb71b51edf72d77d7c3bc8', 'Cliente Tecno Star', '70000000', 'henriquezquinterosmarcosjusue@gmail.com', '', 0, '2026-10-20 15:53:22'),
(9, 'TS-261022-C6A9909F3357', 5, NULL, '2026-10-22 16:06:02', '2026-10-23 16:06:02', 78.98, 'entregado', NULL, '2026-10-22 16:06:02', 6, '88b32ef373a136f9fd07fcb91d4493a3bbd8ed77bd8005ccfd0c8f5d8d07411b', '7c869e9c92be6ddfe5f40ced3239b4f32b0d09ef856165b8fa8a81c60cdc6a2f', 'MARCOS JOSUE HENRIQUEZ QUINTEROS', '76509900', 'marco.henriquez@catolica.edu.sv', '', 0, '2026-10-22 16:08:29'),
(10, 'TS-261022-65F713DB9E82', 5, NULL, '2026-10-22 16:16:20', '2026-10-23 16:16:20', 200.50, 'cancelado', NULL, '2026-10-22 16:16:20', 6, 'a78901301c66ba45f35817765cd8e5de0ff2cbf014803109dc12aa5f0a03c406', '36a10ef8c0549af99c6192ef0a63fcce8a3755bb7ac6db6e9809e544d1c493aa', 'MARCOS JOSUE HENRIQUEZ QUINTEROS', '76509900', 'marco.henriquez@catolica.edu.sv', '', 0, '2026-10-26 20:30:14');

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
(1, 1, 4, 'client client', NULL, 'pendiente', 'Pedido confirmado por el cliente; pendiente de aceptación de la tienda.', '2026-10-19 06:35:19'),
(2, 1, 2, 'Administrador Tecno Star', 'pendiente', 'confirmado', 'confirmado', '2026-10-19 06:36:43'),
(3, 2, 3, 'Cliente Tecno Star', NULL, 'pendiente', 'Pedido enviado a la tienda; pendiente de aceptación.', '2026-10-19 16:46:33'),
(4, 2, 2, 'Administrador Tecno Star', 'pendiente', 'confirmado', 'confimado tenemos todos sus productos', '2026-10-19 16:48:08'),
(5, 3, 3, 'Cliente Tecno Star', NULL, 'pendiente', 'Pedido enviado a la tienda; pendiente de aceptación.', '2026-10-19 19:06:53'),
(6, 3, 2, 'Administrador Tecno Star', 'pendiente', 'confirmado', 'hioa', '2026-10-19 19:08:21'),
(7, 4, 3, 'Cliente Tecno Star', NULL, 'pendiente', 'Pedido enviado a la tienda; pendiente de aceptación.', '2026-10-20 04:02:27'),
(8, 4, 2, 'Administrador Tecno Star', 'pendiente', 'confirmado', 'Aceptar pedido', '2026-10-20 04:04:11'),
(9, 5, 3, 'Cliente Tecno Star', NULL, 'pendiente', 'Pedido enviado a la tienda; pendiente de aceptación.', '2026-10-20 04:09:23'),
(10, 5, 2, 'Administrador Tecno Star', 'pendiente', 'confirmado', 'Aceptar pedido', '2026-10-20 04:15:55'),
(11, 5, 2, 'Administrador Tecno Star', 'confirmado', 'en_preparacion', 'Iniciar preparación', '2026-10-20 04:16:26'),
(12, 5, 2, 'Administrador Tecno Star', 'en_preparacion', 'listo', 'Marcar listo para retirar', '2026-10-20 04:16:51'),
(13, 6, 3, 'Cliente Tecno Star', NULL, 'pendiente', 'Pedido enviado a la tienda; pendiente de aceptación.', '2026-10-20 04:18:13'),
(14, 6, 2, 'Administrador Tecno Star', 'pendiente', 'confirmado', 'Aceptar pedido', '2026-10-20 04:18:27'),
(15, 6, 2, 'Administrador Tecno Star', 'confirmado', 'en_preparacion', 'Iniciar preparación', '2026-10-20 04:20:39'),
(16, 6, 2, 'Administrador Tecno Star', 'en_preparacion', 'listo', 'Marcar listo para retirar', '2026-10-20 04:20:43'),
(17, 7, 3, 'Cliente Tecno Star', NULL, 'pendiente', 'Pedido enviado a la tienda; pendiente de aceptación.', '2026-10-20 04:21:36'),
(18, 7, 2, 'Administrador Tecno Star', 'pendiente', 'confirmado', 'Aceptar pedido', '2026-10-20 04:21:48'),
(19, 7, 2, 'Administrador Tecno Star', 'confirmado', 'en_preparacion', 'Iniciar preparación', '2026-10-20 04:22:20'),
(20, 5, 2, 'Administrador Tecno Star', 'listo', 'entregado', 'Cobro en efectivo y entrega en caja.', '2026-10-20 04:28:09'),
(21, 1, NULL, 'Sistema', 'confirmado', 'cancelado', 'Reserva vencida (24 horas).', '2026-10-20 15:50:02'),
(22, 8, 3, 'Cliente Tecno Star', NULL, 'pendiente', 'Pedido enviado a la tienda; pendiente de aceptación.', '2026-10-20 15:51:03'),
(23, 8, 2, 'Administrador Tecno Star', 'pendiente', 'confirmado', 'Aceptar pedido', '2026-10-20 15:51:22'),
(24, 8, 2, 'Administrador Tecno Star', 'confirmado', 'en_preparacion', 'Iniciar preparación', '2026-10-20 15:52:08'),
(25, 8, 2, 'Administrador Tecno Star', 'en_preparacion', 'listo', 'Marcar listo para retirar', '2026-10-20 15:52:14'),
(26, 8, 2, 'Administrador Tecno Star', 'listo', 'entregado', 'Cobro en efectivo y entrega en caja.', '2026-10-20 15:53:22'),
(27, 2, NULL, 'Sistema', 'confirmado', 'cancelado', 'Reserva vencida (24 horas).', '2026-10-21 03:10:47'),
(28, 3, NULL, 'Sistema', 'confirmado', 'cancelado', 'Reserva vencida (24 horas).', '2026-10-21 03:10:47'),
(29, 4, NULL, 'Sistema', 'confirmado', 'cancelado', 'Reserva vencida (24 horas).', '2026-10-22 04:37:54'),
(30, 6, NULL, 'Sistema', 'listo', 'cancelado', 'Reserva vencida (24 horas).', '2026-10-22 04:37:54'),
(31, 7, NULL, 'Sistema', 'en_preparacion', 'cancelado', 'Reserva vencida (24 horas).', '2026-10-22 04:37:54'),
(32, 9, 6, 'MARCOS JOSUE HENRIQUEZ QUINTEROS', NULL, 'pendiente', 'Pedido enviado a la tienda; pendiente de aceptación.', '2026-10-22 16:06:02'),
(33, 9, 2, 'Administrador Tecno Star', 'pendiente', 'confirmado', 'Aceptar pedido', '2026-10-22 16:06:39'),
(34, 9, 2, 'Administrador Tecno Star', 'confirmado', 'en_preparacion', 'Iniciar preparación', '2026-10-22 16:07:00'),
(35, 9, 2, 'Administrador Tecno Star', 'en_preparacion', 'listo', 'Marcar listo para retirar', '2026-10-22 16:07:22'),
(36, 9, 2, 'Administrador Tecno Star', 'listo', 'entregado', 'Cobro en efectivo y entrega en caja.', '2026-10-22 16:08:29'),
(37, 10, 6, 'MARCOS JOSUE HENRIQUEZ QUINTEROS', NULL, 'pendiente', 'Pedido enviado a la tienda; pendiente de aceptación.', '2026-10-22 16:16:20'),
(38, 10, NULL, 'Sistema', 'pendiente', 'cancelado', 'Reserva vencida (24 horas).', '2026-10-26 20:30:14');

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
(1, 1, 5, 'qr-demo-privada.png', '844237b21ea406c3764b2e00c026e3d057d13fef37fce690.png', 'image/png', 17922, '002f200b89c45c5279c6fc2083184b6d38cd2cc7ec2079434c0f586bf5e7586c', 'original', NULL, '2026-10-31 17:56:58');

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
(1, 'SV-261031-8989784DBDB6', 7, 5, 'Anillado', 'documento', 1, 'hola', '{\"papel\":\"A4\",\"color\":\"Color\",\"caras\":\"Una cara\",\"medidas\":\"11 x 12\"}', 'Marcos Henriquez', 'henriquezquinterosmarcosjusue@gmail.com', '+50378089999', '2026-11-01', 1, 0, 'nueva', 0, NULL, NULL, '727cfa593edb20bfab455b76d728b19c04be19eebbad4da1ef1688c78b95995c', 'f79711eeb565426e7fc30a81289c5590d260a365d828abd30fb0553e6fae7b3c', '2026-10-31 17:56:58', '2026-10-31 17:56:58');

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
('12153b2e7b7f4143675378d50699787a128fa3e7b8bbb535f9bc1f65e76d3b40', 6, '8911c550a83d12f152a5337159c80aca415407d042c1557ee74f3db8edfce093', '[]', '2026-10-22 10:16:12'),
('125283eb036ebe67b1e7a6c70fe63a3da289a98851a508545a794ed4e7a9843c', 3, '587029f7c519d0ea2b2910f29b80e45526b315eb9335be09ca219a6905ec696a', '[]', '2026-10-19 22:02:02'),
('1a2635debca26731c207d7f325020a202ee9e8ab08f41ea2be425840d19655c9', 3, '516d78d28d91d5256cc8fd147f70bea88960fb800f3441f0cbb69ebb09ad5ddf', '[]', '2026-10-19 13:05:39'),
('2c1f8e11c636c9c03ba0e719813f893a61013ff447c73948723b8d5a9d1a123c', 3, 'd8dbff5e481cae023cc71192be61ecd45a0c2fd083862a6c31dce290717839df', '[]', '2026-10-19 22:09:03'),
('2f6d1a52fca35e5f7a9d90e93a7d077922b9c091a65982ff282cbc89bfcd25b3', 2, 'f08e96ec627c37d3eccd3aa080ff29d201e38e4a927ee0cf36931a2a41245a55', '[]', '2026-10-19 00:26:09'),
('458fb2d3218283ed74bca52a2b43912b91a38220e40694dc0935d4851b093c7f', 3, '058ba491307b61a622baacfdaaa295f9d2fc690fa1f61f8d0e609f6d5d74abe2', '[]', '2026-10-19 22:02:01'),
('46ee77b45a89755c91943611b79f30d652a9669ab57ddabcba750d45760e5d80', 6, 'bb73b7eb7447e41447f3ff126c04083a9fac7fc6d24334a56b25be93fcf6036e', '[]', '2026-10-22 10:05:31'),
('48a0e0104ffab4b02c9080786423f2c9897de48793de3a5d96b69a7f41f1b305', 3, '7ae1960c1dab44647717214ec9b151e1789e1c1c69c19d77b10a502986de5292', '[]', '2026-10-19 22:21:26'),
('49b77196acd14b0b8bbfad922641b702b27ac0bca8992b1e4bea0403852e07e7', 3, '0701cf8fe56928ba5544427f5b80b3c0ce94fb3ca702dd4000fa9647a122ff5c', '[]', '2026-10-19 22:17:56'),
('4ce360b7e7bac6f8208caa740d16c0d0ca57071b782ca05805c1bcd5dd9f2978', 3, '285eb0b19ea866524c22027159612e29eaac39616ef0cbc0d9ea6f7056732cf0', '[]', '2026-10-19 22:02:00'),
('66990d915e8225e49bb5d2d2d1d21ac059a69038cf5dc3e0f646495dcb1c7382', 6, 'a420d16aebafe0d3b10d035d99368ff74ae09adc5800651b071054d6ad17c4b9', '[]', '2026-10-22 10:05:21'),
('702f29d43e1c1fc4146747cd76b07050e45e4d8f330fb8c25a078e38c31d072c', 4, '6ac4c5df9319d3beb1f60ff43bdaa2a842b970680c5b1755056b4c43ebfb95e5', '[]', '2026-10-19 00:34:21'),
('7483488b3d7621401c359039c4fc10a458ab7c010ac0305bce00431db596995f', 3, '5460f14742ced47f21f8baab922e92835e8497aeaebb526bc9fecbe6598fc08b', '[]', '2026-10-19 22:01:58'),
('788c3244daf92bf9c4186890173e9332604fc045b52100b67612b453ade12c6d', 4, '42f54b064cd9912a6424d3a63d936318d94b823cdc243a859a7bcc5129471b4a', '[]', '2026-10-19 00:34:19'),
('7b7ce1916208fd670b9ec57b285a9f7ffe62e533488870193b5b3a18067db0fb', 6, '945b1fbfc57b486fc9c9acadd99191f73d1fc18f4236d2813b2204742a7ab2d3', '[]', '2026-10-22 10:05:33'),
('7eea995d13b00bec15882d75a2b463294cd08e43a3133084f66a7ea87d2b7335', 4, 'fea375e4aa09f2d0fa98038a8d517cb608e71e0a1163709a6daced35749aaa17', '[]', '2026-10-19 00:34:23'),
('80154e4245a6564d57630db17f7bbcfab59f54a3a6e2d1b1cbe5666a6e2a3bad', 3, 'de5338954e2f867ce73e39adfae1fb20ddd2d5062f5698b03e4bd278f726c61d', '[]', '2026-10-21 22:49:50'),
('88cec0ca4bbf41645ce99e2f4ce454f651ebbb1d45a94e82017aa8c29399a9fd', 3, 'ccffee40379f564614740457080c6273f6b1655502b11f10786ec0fca13371af', '[]', '2026-10-19 22:01:56'),
('93dc161c60c6524c466295aaab031757cca44739ad32e4defb0cdde6c47bf2d0', 3, 'c56284e4a0299a8b2c6a4e950da7d0877f716aefc51fe91591d70379695b2425', '[]', '2026-10-19 10:46:16'),
('a19cc58027d391fb25fad7048f3d4eb379a21e01c533adddd076020cf71d5432', 6, 'aa8b56db0090b2e02b5fcf917868fca08207eb9d037b5f2573e2d0573b0c8f5e', '[]', '2026-10-22 10:05:30'),
('b53052a378d8af78d7222f0bcea02cb8c14938a35b4147a64491151bd0435554', 3, 'b25506248e1dd25e8036dfab12025fc2634a3049c50541866d1ce5f1a591eeec', '[]', '2026-10-19 22:01:54'),
('c32feb99f57fb267d95a2e97b58233c469c80c1fb80a99c5b17ffdd568191212', 3, 'b494d686f7b45d3661303e3a514214878a0241257d62dd7787dd516d9e8dcfcf', '[]', '2026-10-21 22:50:12'),
('dc3639e2e58daec32fc2d69570949e52d099d48e72716af0982b3e7eb072b9fd', 4, '159ecbe989046a564107d78c47eb1d062d4045f85d162fdbecbfd781e49eff12', '[]', '2026-10-19 00:34:27'),
('e41787f112fb84ac421daf6e5f1b90767fdb5fe26d3e3d40280d761718561e83', 3, 'a75f1307ed35e98c5718c716c72a1dc76ff09863cb59316be9ac815f8ebee1e7', '[]', '2026-10-20 09:50:29');

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
(1, 3, NULL, 'Sistema (saldo inicial)', 'inicial', 0.000, 18.000, 18.000, 0.000, 5.000, 5.000, 'Saldo conservado al habilitar el historial. No reconstruye movimientos anteriores.', NULL, NULL, '2026-10-17 11:31:17', NULL, NULL),
(2, 6, NULL, 'Sistema (saldo inicial)', 'inicial', 0.000, 20.000, 20.000, 0.000, 5.000, 5.000, 'Saldo conservado al habilitar el historial. No reconstruye movimientos anteriores.', NULL, NULL, '2026-10-17 11:31:17', NULL, NULL),
(3, 5, NULL, 'Sistema (saldo inicial)', 'inicial', 0.000, 12.000, 12.000, 0.000, 5.000, 5.000, 'Saldo conservado al habilitar el historial. No reconstruye movimientos anteriores.', NULL, NULL, '2026-10-17 11:31:17', NULL, NULL),
(4, 4, NULL, 'Sistema (saldo inicial)', 'inicial', 0.000, 50.000, 50.000, 0.000, 5.000, 5.000, 'Saldo conservado al habilitar el historial. No reconstruye movimientos anteriores.', NULL, NULL, '2026-10-17 11:31:17', NULL, NULL),
(5, 1, NULL, 'Sistema (saldo inicial)', 'inicial', 0.000, 25.000, 25.000, 0.000, 5.000, 5.000, 'Saldo conservado al habilitar el historial. No reconstruye movimientos anteriores.', NULL, NULL, '2026-10-17 11:31:17', NULL, NULL),
(6, 2, NULL, 'Sistema (saldo inicial)', 'inicial', 0.000, 40.000, 40.000, 0.000, 5.000, 5.000, 'Saldo conservado al habilitar el historial. No reconstruye movimientos anteriores.', NULL, NULL, '2026-10-17 11:31:17', NULL, NULL),
(7, 1, 4, 'client client', 'reserva', 0.000, 25.000, 25.000, 1.000, 5.000, 5.000, 'Reserva del pedido TS-261019-10A71776ED85', NULL, NULL, '2026-10-19 00:35:19', 0.000, 1),
(8, 2, 4, 'client client', 'reserva', 0.000, 40.000, 40.000, 2.000, 5.000, 5.000, 'Reserva del pedido TS-261019-10A71776ED85', NULL, NULL, '2026-10-19 00:35:19', 0.000, 1),
(9, 3, 4, 'client client', 'reserva', 0.000, 1.000, 1.000, 1.000, 5.000, 5.000, 'Reserva del pedido TS-261019-10A71776ED85', NULL, NULL, '2026-10-19 00:35:19', 0.000, 1),
(10, 1, 3, 'Cliente Tecno Star', 'reserva', 0.000, 25.000, 25.000, 2.000, 5.000, 5.000, 'Reserva del pedido TS-261019-83495A8D4E51', NULL, NULL, '2026-10-19 10:46:33', 1.000, 2),
(11, 3, 2, 'Administrador Tecno Star', 'entrada', 4.990, 1.000, 5.990, 1.000, 5.000, 5.000, 'Recepcion de mercancia', 'bbd413743c69f84a2b5c3a2034382f698822210a37b376e0a3fe30234788ce20', 'cc4e49b55d4711ca6494e1ae24baac57ed5acaaeb862f6a173dbb7a001e67d63', '2026-10-19 10:53:10', NULL, NULL),
(12, 3, 2, 'Administrador Tecno Star', 'entrada', 10.000, 5.990, 15.990, 1.000, 5.000, 5.000, 'nuevo stock', '45a7a6dc60e60ec13667cd4814a7a0e0e4dd4484637febd95171e4c1861660d1', '521c44b0f58ed782f4c1d7df3055e812e65553be364c29077bede2fc5a0d3552', '2026-10-19 10:53:26', NULL, NULL),
(13, 1, 3, 'Cliente Tecno Star', 'reserva', 0.000, 25.000, 25.000, 3.000, 5.000, 5.000, 'Reserva del pedido TS-261019-DCF6DDC1CE09', NULL, NULL, '2026-10-19 13:06:53', 2.000, 3),
(14, 1, 3, 'Cliente Tecno Star', 'reserva', 0.000, 25.000, 25.000, 4.000, 5.000, 5.000, 'Reserva del pedido TS-261019-EC15B8353AB6', NULL, NULL, '2026-10-19 22:02:27', 3.000, 4),
(15, 2, 3, 'Cliente Tecno Star', 'reserva', 0.000, 40.000, 40.000, 3.000, 5.000, 5.000, 'Reserva del pedido TS-261019-EC15B8353AB6', NULL, NULL, '2026-10-19 22:02:27', 2.000, 4),
(16, 3, 3, 'Cliente Tecno Star', 'reserva', 0.000, 15.990, 15.990, 2.000, 5.000, 5.000, 'Reserva del pedido TS-261019-EC15B8353AB6', NULL, NULL, '2026-10-19 22:02:27', 1.000, 4),
(17, 4, 3, 'Cliente Tecno Star', 'reserva', 0.000, 50.000, 50.000, 1.000, 5.000, 5.000, 'Reserva del pedido TS-261019-EC15B8353AB6', NULL, NULL, '2026-10-19 22:02:27', 0.000, 4),
(18, 5, 3, 'Cliente Tecno Star', 'reserva', 0.000, 12.000, 12.000, 1.000, 5.000, 5.000, 'Reserva del pedido TS-261019-EC15B8353AB6', NULL, NULL, '2026-10-19 22:02:27', 0.000, 4),
(19, 6, 3, 'Cliente Tecno Star', 'reserva', 0.000, 20.000, 20.000, 1.000, 5.000, 5.000, 'Reserva del pedido TS-261019-EC15B8353AB6', NULL, NULL, '2026-10-19 22:02:27', 0.000, 4),
(20, 4, 3, 'Cliente Tecno Star', 'reserva', 0.000, 50.000, 50.000, 2.000, 5.000, 5.000, 'Reserva del pedido TS-261019-B1008746AE7F', NULL, NULL, '2026-10-19 22:09:23', 1.000, 5),
(21, 2, 3, 'Cliente Tecno Star', 'reserva', 0.000, 40.000, 40.000, 4.000, 5.000, 5.000, 'Reserva del pedido TS-261019-A54BCC43D92D', NULL, NULL, '2026-10-19 22:18:13', 3.000, 6),
(22, 5, 3, 'Cliente Tecno Star', 'reserva', 0.000, 12.000, 12.000, 2.000, 5.000, 5.000, 'Reserva del pedido TS-261019-9C30DDBF7DEA', NULL, NULL, '2026-10-19 22:21:36', 1.000, 7),
(23, 4, 2, 'Administrador Tecno Star', 'entrega', -1.000, 50.000, 49.000, 1.000, 5.000, 5.000, 'Cobro en efectivo y entrega en caja.', NULL, NULL, '2026-10-19 22:28:09', 2.000, 5),
(24, 1, NULL, 'Sistema', 'liberacion', 0.000, 25.000, 25.000, 3.000, 5.000, 5.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-20 09:50:02', 4.000, 1),
(25, 2, NULL, 'Sistema', 'liberacion', 0.000, 40.000, 40.000, 2.000, 5.000, 5.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-20 09:50:02', 4.000, 1),
(26, 3, NULL, 'Sistema', 'liberacion', 0.000, 15.990, 15.990, 1.000, 5.000, 5.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-20 09:50:02', 2.000, 1),
(27, 1, 3, 'Cliente Tecno Star', 'reserva', 0.000, 25.000, 25.000, 4.000, 5.000, 5.000, 'Reserva del pedido TS-261020-E1DA441229FF', NULL, NULL, '2026-10-20 09:51:03', 3.000, 8),
(28, 1, 2, 'Administrador Tecno Star', 'entrega', -1.000, 25.000, 24.000, 3.000, 5.000, 5.000, 'Cobro en efectivo y entrega en caja.', NULL, NULL, '2026-10-20 09:53:22', 4.000, 8),
(29, 1, NULL, 'Sistema', 'liberacion', 0.000, 24.000, 24.000, 2.000, 5.000, 5.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-20 21:10:47', 3.000, 2),
(30, 1, NULL, 'Sistema', 'liberacion', 0.000, 24.000, 24.000, 1.000, 5.000, 5.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-20 21:10:47', 2.000, 3),
(37, 1, NULL, 'Sistema', 'liberacion', 0.000, 24.000, 24.000, 0.000, 5.000, 5.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-21 22:37:54', 1.000, 4),
(38, 2, NULL, 'Sistema', 'liberacion', 0.000, 40.000, 40.000, 1.000, 5.000, 5.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-21 22:37:54', 2.000, 4),
(39, 3, NULL, 'Sistema', 'liberacion', 0.000, 15.990, 15.990, 0.000, 5.000, 5.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-21 22:37:54', 1.000, 4),
(40, 4, NULL, 'Sistema', 'liberacion', 0.000, 49.000, 49.000, 0.000, 5.000, 5.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-21 22:37:54', 1.000, 4),
(41, 5, NULL, 'Sistema', 'liberacion', 0.000, 12.000, 12.000, 1.000, 5.000, 5.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-21 22:37:54', 2.000, 4),
(42, 6, NULL, 'Sistema', 'liberacion', 0.000, 20.000, 20.000, 0.000, 5.000, 5.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-21 22:37:54', 1.000, 4),
(43, 2, NULL, 'Sistema', 'liberacion', 0.000, 40.000, 40.000, 0.000, 5.000, 5.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-21 22:37:54', 1.000, 6),
(44, 5, NULL, 'Sistema', 'liberacion', 0.000, 12.000, 12.000, 0.000, 5.000, 5.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-21 22:37:54', 1.000, 7),
(45, 7, 2, 'Administrador Tecno Star', 'entrada', 20.000, 0.000, 20.000, 0.000, 0.000, 0.000, 'recepcion', 'a933c5fdb4ef079747a77cacc25bfeba65b6cbfec7f7449ade80d13127219314', '1722bab0f3a6092f58b5520da1ce428a92c9d798a0a5689c1e049c555e3044ec', '2026-10-21 22:41:39', NULL, NULL),
(46, 8, 2, 'Administrador Tecno Star', 'entrada', 9.998, 0.000, 9.998, 0.000, 0.000, 0.000, 'recepcion', '06d02614323124767811cf7831ce14c4dd8a74eae7817f9c7ae88d4792e100d7', 'ae4ac2d76c2ebdcf09742887b9bb497dfaf3505ed4927b50e314cbe60eb16d0d', '2026-10-21 22:48:38', NULL, NULL),
(47, 1, 6, 'MARCOS JOSUE HENRIQUEZ QUINTEROS', 'reserva', 0.000, 24.000, 24.000, 1.000, 5.000, 5.000, 'Reserva del pedido TS-261022-C6A9909F3357', NULL, NULL, '2026-10-22 10:06:02', 0.000, 9),
(48, 2, 6, 'MARCOS JOSUE HENRIQUEZ QUINTEROS', 'reserva', 0.000, 40.000, 40.000, 2.000, 5.000, 5.000, 'Reserva del pedido TS-261022-C6A9909F3357', NULL, NULL, '2026-10-22 10:06:02', 0.000, 9),
(49, 5, 6, 'MARCOS JOSUE HENRIQUEZ QUINTEROS', 'reserva', 0.000, 12.000, 12.000, 1.000, 5.000, 5.000, 'Reserva del pedido TS-261022-C6A9909F3357', NULL, NULL, '2026-10-22 10:06:02', 0.000, 9),
(50, 1, 2, 'Administrador Tecno Star', 'entrega', -1.000, 24.000, 23.000, 0.000, 5.000, 5.000, 'Cobro en efectivo y entrega en caja.', NULL, NULL, '2026-10-22 10:08:29', 1.000, 9),
(51, 2, 2, 'Administrador Tecno Star', 'entrega', -2.000, 40.000, 38.000, 0.000, 5.000, 5.000, 'Cobro en efectivo y entrega en caja.', NULL, NULL, '2026-10-22 10:08:29', 2.000, 9),
(52, 5, 2, 'Administrador Tecno Star', 'entrega', -1.000, 12.000, 11.000, 0.000, 5.000, 5.000, 'Cobro en efectivo y entrega en caja.', NULL, NULL, '2026-10-22 10:08:29', 1.000, 9),
(53, 9, 2, 'Administrador Tecno Star', 'entrada', 10.000, 0.000, 10.000, 0.000, 0.000, 0.000, 'jhhh', '048a102c7f5affaa36637fd7894805570e437ad760a744e5f7febb9f57fc976a', '9c072d0a31b7246cf15c7eb75a730b9aef3b2d95bf0999c6c6333ed5a5e35907', '2026-10-22 10:13:29', NULL, NULL),
(54, 9, 6, 'MARCOS JOSUE HENRIQUEZ QUINTEROS', 'reserva', 0.000, 10.000, 10.000, 10.000, 0.000, 0.000, 'Reserva del pedido TS-261022-65F713DB9E82', NULL, NULL, '2026-10-22 10:16:20', 0.000, 10),
(55, 9, NULL, 'Sistema', 'liberacion', 0.000, 10.000, 10.000, 0.000, 0.000, 0.000, 'Reserva vencida (24 horas).', NULL, NULL, '2026-10-26 14:30:14', 10.000, 10);

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
(2, 1, 'admin', 'Administrador', 'Tecno Star', 'admin@admin.com', '$2y$10$lXRU8UvBU55yK4XIRGtM4O2L23BME5u1kUvfMVpbhz2Frsx/t2IPO', 'activo', '2026-10-07 22:46:25', 0, 0),
(3, 3, 'cliente-af8c801a', 'Cliente', 'Tecno Star', 'cliente@cliente.com', '$2y$10$m3WX0NXfmgohhYgZmRAD7uJmA3rmF4Mh9TE2nQde.PbBhPo//jfK.', 'activo', '2026-10-08 00:35:33', 0, 0),
(4, 3, 'henriquezquinterosmarcosjusue-ebae38a3', 'client', 'client', 'cliente@cliente3.com', '$2y$10$WiJccNlCXIAupDppmgYpTOLeir/M/ud1R7pfBtU0C2Ndaxej0GTRS', 'activo', '2026-10-19 00:33:49', 0, 0),
(5, 3, 'google-573e69807e78da8a84c0caa2ac9382d2b283dcbf', 'Marcos', 'Henriquez', 'henriquezquinterosmarcosjusue@gmail.com', '$2y$10$JxG7iU7DusARRe8/qQyum.W6pUvQK34dhPyKUj3AtwssDnfZ9ZCT2', 'activo', '2026-10-22 01:18:33', 0, 0),
(6, 3, 'google-69c6e5adb8c4a415402af626ee08325adb88b401', 'MARCOS JOSUE', 'HENRIQUEZ QUINTEROS', 'marco.henriquez@catolica.edu.sv', '$2y$10$aj8RDdJ/MnMTdWcQU9LLUOsf/HnIKsUy0sHbUTs3.lKqbZ/F.fXhO', 'activo', '2026-10-22 10:05:20', 0, 0);

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
(1, 'VT-261019-4231F74B0EF7', 5, 2, 2, '2026-10-20 04:28:09', 9.99, 'efectivo', 'pagada', '2026-10-20 04:28:09', 10.00, 0.01, 'Administrador Tecno Star', 'a02760fdf3aa8e23c0f1faf91a4fb40295de408496c8631c11c075aceb872398', '01a890dfa445c213773739eca9b80d5f3fb1458dc89ffa847112e3e152f6d45a', NULL),
(2, 'VT-261020-4F7BE801CBC6', 8, 2, 2, '2026-10-20 15:53:22', 18.99, 'efectivo', 'pagada', '2026-10-20 15:53:22', 20.00, 1.01, 'Administrador Tecno Star', '0414d66d959b7adf58fdc0bb566cd44239d9cb0b79f4aee4ddf440e078eab20c', '49b6266a05b442e2391762b33727d60046eb186fb346d0abdcaf0aaa8998c3bd', NULL),
(3, 'VT-261022-681BA8FF2E38', 9, 5, 2, '2026-10-22 16:08:29', 78.98, 'efectivo', 'pagada', '2026-10-22 16:08:29', 79.13, 0.15, 'Administrador Tecno Star', 'a16125e3c040b3713d99c2deea48437d2eb90b03c1a6d04c0a07e904df353609', 'f7b53d936bcba04676745341e3276804b9293678505f05e6e3a2eb0add1cc9e0', NULL);

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
