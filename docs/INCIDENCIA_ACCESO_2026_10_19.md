# Incidencia de acceso · 19 de octubre de 2026

Referencia comunicada: `47b868c56e4056d2`.

## Causa comprobada

El acceso fallaba en `UserModel::findActiveByEmail`, antes de verificar la contraseña. La cuenta SQL de la aplicación no tenía lectura de `tbpersonalpermisos` (SQLSTATE 42000, código 1142). No era una restricción por antigüedad de la cuenta.

Al restaurar los permisos del Plan 7 se detectó además corrupción en `mysql.columns_priv`, la tabla interna de permisos por columna de MariaDB (códigos 144 y 1194). `CHECK TABLE` confirmó índices y una página con checksum incorrecto. No se determinó la causa original de esa corrupción.

## Reparación y comprobación

- [x] Restaurar los permisos específicos de `tbpersonalpermisos` previamente autorizados.
- [x] Obtener autorización explícita para reparar la tabla interna de MariaDB.
- [x] Conservar copia previa de sus tres archivos en `storage/backups/mysql-permissions-*`, excluida de Git.
- [x] Reparar `mysql.columns_priv`. La reparación rápida no resolvió la escritura; se completó la reparación normal y se comprobó la integridad.
- [x] Restaurar los permisos por columna autorizados de inventario y los permisos del Plan 7 mediante sus herramientas de activación/verificación.
- [x] Confirmar `CHECK TABLE` correcto para `mysql.columns_priv` y `mysql.tables_priv`.
- [x] Verificar los planes 4, 6 y 7 con `enable-inventory.php --verify`, `enable-orders.php --verify` y `enable-sales-accounts.php --verify`.
- [x] Consultar por ID y correo las tres cuentas activas mediante el modelo real, sin publicar sus datos: tres hashes de contraseña compatibles.
- [x] Probar el formulario HTTP con CSRF y credenciales inexistentes: rechazo normal de credenciales, HTTP 200, sin error 503.

La herramienta del Plan 7 comprobó que cuentas, hashes, roles, ventas y saldos permanecían iguales durante la activación. No se cambiaron contraseñas ni se enviaron correos. No se inició sesión con contraseñas reales del usuario durante esta comprobación.

El activador del Plan 7 muestra ahora SQLSTATE y código numérico del controlador al fallar; continúa ocultando mensajes SQL y credenciales. El script temporal de diagnóstico y reparación se retiró después de verificar el resultado.
