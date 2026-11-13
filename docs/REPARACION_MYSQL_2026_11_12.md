# Reparación de MySQL/XAMPP · 12 de noviembre de 2026

## Diagnóstico

MariaDB 10.4.32 se cerraba al leer `tecno_star.tbclientes`. El registro identificó corrupción en la página 0 de su espacio InnoDB (`space=63`); la lectura repetida terminó derribando el servidor.

## Respaldo y recuperación

- Con MySQL detenido se copió `C:\xampp\mysql\data` a `storage/backups/mysql-crash-20261112-before-repair/`.
- Se verificaron los 525 archivos del respaldo con SHA-256. El manifiesto está en `storage/backups/mysql-crash-20261112-before-repair/manifest.json`.
- La recuperación se ensayó sobre una copia independiente con `innodb_force_recovery=1`. Se exportaron los 4 registros legibles de `tbclientes`, se reconstruyó solo esa tabla y `CHECK TABLE` devolvió `OK`.
- Se aplicó el mismo procedimiento a la base original. Ninguna otra tabla fue recreada.
- El primer inicio normal encontró referencias antiguas en `ib_buffer_pool`. Se omitió su carga una sola vez, se hizo un cierre normal para regenerarla y luego MySQL arrancó con la configuración predeterminada.

La copia de trabajo y el volcado lógico recuperado están en `storage/recovery/mysql-crash-20261112-working-copy/`. Contienen copias de datos privados; deben mantenerse fuera del directorio público.

## Verificación final

- MySQL responde en el puerto `3308`.
- `innodb_force_recovery` quedó en `0`; no se dejó modo de recuperación activado en la configuración.
- `tecno_star.tbclientes` conserva 4 registros y `CHECK TABLE` devuelve `OK`.
- La cuenta normal de la aplicación pudo ejecutar la consulta de usuarios/clientes: 5 usuarios activos y 4 clientes vinculados.
