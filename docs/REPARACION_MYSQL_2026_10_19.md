# Recuperación de MySQL · 19 de octubre de 2026

## Estado

La reparación autorizada ya fue aplicada al servidor original y verificada con MariaDB iniciado normalmente.

## Diagnóstico

- El evento de MariaDB registra cierre por `mysql.global_priv` marcada como dañada y reparación automática fallida.
- `mysql.transaction_registry` tiene una página corrupta; intentar leerla hizo caer únicamente la instancia de recuperación. No hay tablas con versionado de sistema en los esquemas inspeccionados.
- La comprobación completa encontró también corrupción en `mysql.proxies_priv`.

## Copia previa

`storage/backups/mysql-crash-20261019-230057/` contiene los archivos originales y `my.ini`: 225 archivos, 102 781 552 bytes, verificados contra el origen por SHA256. `manifest.json` registra las huellas. El respaldo se obtuvo con MariaDB detenido y no se modificó durante las pruebas.

La copia de trabajo independiente está en `storage/recovery/mysql-20261019-230057/`. No sustituye al respaldo original.

Antes de aplicar el cambio se creó el respaldo autorizado `storage/backups/mysql-repair-approved-20261019-231001/` (225 archivos, verificado por SHA256).

## Procedimiento probado

1. Reconstruir la tabla Aria `global_priv` y normalizar sus metadatos, manteniendo las cinco cuentas y sus valores de autenticación.
2. Reparar `proxies_priv` con memoria de ordenación suficiente. **Su cabecera indicaba una entrada, pero ni la recuperación normal ni la extendida pudieron recuperarla: la tabla reparada queda vacía.** No se ha identificado a quién correspondía esa delegación SQL.
3. Reconstruir `transaction_registry` con la definición de `C:/xampp/mysql/share/mysql_system_tables.sql`. Sus registros anteriores no fueron legibles y permanecen en el respaldo físico; no existen tablas versionadas que dependan de ellos en la instalación inspeccionada.
4. Arrancar normalmente, sin `innodb_force_recovery`, comprobar tablas y credenciales y apagar de forma normal la copia.
5. Aplicar el mismo procedimiento al servidor original detenido y volver a comprobarlo en arranque normal.

## Resultado de las pruebas

- [x] Arranque normal sin modo de recuperación.
- [x] 91 tablas comprobadas sin errores, incluidas las del sistema.
- [x] Conteos de las 61 tablas de aplicación conservados.
- [x] Huella de las cinco cuentas y autenticación conservada después de reparar la copia.
- [x] Conexión válida con las credenciales SQL actuales de Tecno Star.
- [x] Copias de recuperación apagadas al finalizar.
- [x] Autorizar y aplicar al servidor original, incluyendo la pérdida de la entrada de delegación no recuperable y la reconstrucción del registro interno.
- [x] Verificar arranque normal, credenciales, tablas y conexión PHP de Tecno Star después de aplicar.

La comprobación final conserva 5 cuentas, 6 productos y 3 usuarios de aplicación. `mysql.global_priv`, `mysql.proxies_priv` y `mysql.transaction_registry` devuelven estado `OK`; `proxies_priv` queda con 0 registros porque su única entrada no pudo recuperarse. La comprobación de mantenimiento de Tecno Star terminó correctamente (`0 registros fuera de retención`).

No se crearon pedidos, ventas ni clientes de prueba en la instalación original. No se reemplazó su carpeta de datos ni se activó acceso sin contraseña.

## Riesgo que requiere aprobación

La reparación afecta a tablas compartidas por todos los proyectos de este MariaDB. Las cuentas y contraseñas se conservaron; la entrada de `proxies_priv` no recuperable se perdió y podría haber afectado a quien usara esa delegación. No se puede afirmar conservación del contenido ilegible de `transaction_registry`; su archivo original permanece respaldado.

Referencia técnica: [herramienta aria_chk de MariaDB](https://mariadb.com/docs/server/clients-and-utilities/aria-clients-and-utilities/aria_chk).
