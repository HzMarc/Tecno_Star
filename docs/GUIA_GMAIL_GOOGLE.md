# Gmail, QR y credenciales de Google · Tecno Star

Proyecto de Google Cloud: **TectoStar**, ID **tectostar**, verificado en la consola el 22 de octubre de 2026. Remitente elegido: **henriquezquinterosmarcosjusue@gmail.com** (Gmail personal).

Hay dos configuraciones independientes: Gmail SMTP envía los pedidos; OAuth permite iniciar sesión con Google. El secreto OAuth no sirve como contraseña SMTP. No pegues contraseñas, secretos ni archivos JSON de credenciales en el chat o en Git.

**Comprobación local del 19 de octubre de 2026:** Gmail está habilitado (`MAIL_ENABLED=1`). Composer verificó PHPMailer y las dependencias QR. Se detectó GD habilitado en PHP de consola pero sin cargar en Apache; reiniciar Apache lo corrigió. Se comprobaron GD, generación PNG del QR y conexión SMTP desde Apache. El correo real del pedido 1 confirmado fue aceptado por Gmail y registrado como enviado; queda comprobar su recepción en la bandeja del destinatario. La dirección pública sigue siendo localhost y debe adaptarse para abrir enlaces desde otro dispositivo.

## 1. Obtener la clave para enviar desde Gmail

1. Abre [Seguridad de tu cuenta Google](https://myaccount.google.com/security) con la cuenta remitente indicada arriba.
2. Activa **Verificación en dos pasos** si aún no está activa.
3. Abre [Contraseñas de aplicaciones](https://myaccount.google.com/apppasswords).
4. Crea una contraseña con el nombre **Tecno Star correo**.
5. Google muestra una contraseña de aplicación de 16 caracteres. Cópiala una sola vez a `MAIL_PASSWORD` en el archivo privado `.env`, sin los espacios de presentación. **No uses la contraseña normal de Gmail.**
6. Si no aparece esta opción, revisa [las condiciones de Google](https://support.google.com/accounts/answer/185833?hl=es): la protección avanzada y ciertas configuraciones de verificación pueden impedirla. En ese caso se necesita SMTP con OAuth; no desactives la verificación en dos pasos.

Configura en `C:\xampp\htdocs\Tecno_Star\.env` (actualiza las claves si ya existen; no las dupliques):

```dotenv
MAIL_ENABLED=1
MAIL_USERNAME=henriquezquinterosmarcosjusue@gmail.com
MAIL_PASSWORD="PEGA_AQUI_LA_CONTRASENA_DE_APLICACION"
MAIL_FROM_NAME="Tecno Star"
ORDER_PUBLIC_URL=http://localhost:8012/Tecno_Star/public
ORDER_STAFF_USER_IDS=""
```

El sistema usa **smtp.gmail.com, puerto 587, STARTTLS**, con PHPMailer instalado mediante Composer. Mantiene la validación TLS; no desactives la comprobación de certificados para resolver errores de conexión. No requiere activar Gmail API ni crear una API key para esta modalidad SMTP. Consulta el [ejemplo oficial de PHPMailer para Gmail](https://github.com/PHPMailer/PHPMailer/blob/master/examples/gmail.phps).

Si el correo autentica pero falla al crear el QR, comprueba que GD esté cargado también en Apache, no solo en `php -m`. Después de cambiar `extension=gd` en el PHP de XAMPP hay que reiniciar Apache. `composer install` usa las versiones fijadas en `composer.lock`; no requiere cambiar las credenciales de Gmail.

El correo se registra en la misma transacción que cambia el pedido a **Confirmado**. Luego se intenta enviarlo. Si Gmail falla o falta configuración, el pedido queda confirmado y el correo pendiente; la interfaz no anuncia un envío que no ocurrió. Los reintentos normales no generan otra notificación. Como en cualquier SMTP, si se pierde la conexión justo después de que el proveedor acepte el mensaje, un reintento podría repetirlo; se reutiliza el mismo Message-ID para identificarlo.

Prueba con un pedido propio: confirma desde administración, revisa la etiqueta de correo en el detalle y comprueba la bandeja de entrada y spam. El envío real necesita tu clave privada y conexión a Gmail; las pruebas automáticas usan un transporte simulado y no mandan correos a clientes.

Procesar correos pendientes desde la carpeta del proyecto:

```powershell
C:\xampp\php\php.exe database/send-order-mails.php --run
```

Se respetan cinco minutos entre reintentos fallidos. El botón del detalle permite procesar un envío pendiente; no reenvía uno ya enviado. Para operación continua, configura este comando cada minuto en el Programador de tareas de Windows, con la cuenta de ejecución y permisos de tu instalación. Esta guía no crea una tarea programada automáticamente.

## 2. QR con USB, laptop y dispositivos de la misma red

El QR contiene un **enlace del sistema con el código del pedido** (`.../orders/scan?codigo=TS-...`). No contiene claves de acceso, datos bancarios ni una autorización de pago. Al abrirlo se exige iniciar sesión; el empleado debe tener permiso. El cliente también puede consultar su propio pedido.

- **Lector USB:** abre **Pedidos** desde administración, enfoca el campo de código y escanea. Configura el lector como teclado con sufijo Enter. Se aceptan el código o el enlace completo del QR.
- **Cámara de laptop:** abre **Pedidos → Usar cámara**, permite el acceso y selecciona la cámara. Se detiene al salir o cerrar la lectura. Funciona con `localhost` o HTTPS.
- **Cámara del celular:** la aplicación de cámara puede abrir el enlace. El celular necesita llegar al servidor y el empleado debe iniciar sesión allí.
- El escaneo muestra productos, cantidades, precios guardados, contacto y total; no cobra ni entrega automáticamente.
- El Plan 7 está activo: cuando el pedido está **Listo**, personal autorizado puede abrir **Cobrar y entregar**, introducir el efectivo y confirmar. Escanear por sí solo no ejecuta esa operación.

Para misma Wi-Fi, ejecuta `ipconfig` en la PC y busca la IPv4 del adaptador conectado, por ejemplo `192.168.1.50`. Configura:

```dotenv
ORDER_PUBLIC_URL=http://192.168.1.50:8012/Tecno_Star/public
```

Sustituye esa IP por la real. Apache debe escuchar el puerto 8012 en la red; permite el acceso únicamente en la red privada si el firewall lo solicita. Prueba la dirección desde el celular. `localhost` en el celular apunta al celular, no a la PC. Usa una IP fija o reserva DHCP. Los correos ya enviados conservan su enlace anterior.

La lectura USB funciona en HTTP dentro de la red. Para **abrir la cámara dentro del navegador desde la IP de la PC**, configura HTTPS con un certificado confiable en los dispositivos; una IP por HTTP no es un contexto seguro para `getUserMedia`. Documentación: [contextos seguros](https://developer.mozilla.org/en-US/docs/Web/Security/Secure_Contexts). No expongas XAMPP a Internet para resolver una prueba local.

### Permiso de empleado

El administrador tiene acceso. Con el Plan 7, abre **Usuarios**, crea o edita al empleado y habilita su **permiso de pedidos/caja**. Necesita cuenta activa y rol Empleado; el rol por sí solo no concede acceso. Los cambios se comprueban en cada solicitud. El permiso habilita pedidos y ventas, sin habilitar catálogo, inventario, gestión de usuarios ni anulaciones administrativas.

Consulta los IDs locales sin modificar cuentas:

```powershell
C:\xampp\php\php.exe database/order-staff.php --list
```

`ORDER_STAFF_USER_IDS` se conserva como alternativa para empleados sin fila de permiso registrada. Por ejemplo, `ORDER_STAFF_USER_IDS="5,8"` autoriza esos IDs existentes; no uses los números de ejemplo como permisos reales. Una selección guardada en Usuarios prevalece sobre esta lista, incluso cuando se desactiva el permiso. La activación no asignó permisos ni cambió roles de cuentas existentes.

### Recuperación de contraseña

Los mensajes de confirmación y retiro usan el logo incrustado como imagen CID, junto con QR, productos, total y vencimiento; el logo no depende de que el destinatario pueda abrir una imagen en localhost. El aviso de retiro se genera al pasar a «Listo para retirar», separado de la confirmación inicial. La migración 008 de la cola ya está activada y verificada en la instalación local con copia previa y autorización expresa; no se reenviaron mensajes históricos. Revisa visualización y recepción del nuevo diseño en el buzón real.

El mismo Gmail envía el enlace de **Olvidé mi contraseña**. El enlace dura 20 minutos y se puede utilizar una sola vez; solicitar otro invalida el anterior. Su forma es `.../reset-password#token=...`, con el token en el fragmento, retirado de la barra al abrir el formulario. No compartas esos enlaces. El servidor conserva únicamente su hash, limita intentos y devuelve el mismo aviso exista o no la cuenta. Si falla el envío, invalida el enlace; estos mensajes no usan la cola de pedidos.

## 3. Obtener Client ID y Client Secret de Google para el login

**Estado al 22 de octubre de 2026:** cliente web **Tecno Star local** creado en **TectoStar (`tectostar`)**, con Client ID y Client Secret guardados exclusivamente en `.env`. Se instaló `google/apiclient` 2.20.1. El botón aparece en login y registro cuando las credenciales y el callback son válidos. La prueba real en Chrome completó el retorno de Google, la verificación del correo y el acceso a Mi perfil con sesión autenticada. La aplicación continúa en modo **Prueba**, para uso local.

El callback valida firma del ID token, emisor, audiencia, vencimiento, correo verificado y nonce. Cada intento usa state de un solo uso, PKCE S256 y un plazo de diez minutos. Solo solicita `openid email profile`, sin acceso a Gmail/Drive ni tokens de actualización. Los códigos salen de la URL antes de mostrar páginas y los tokens no se guardan en sesión ni en registros.

La migración `009_google_login.sql` añade `tbgoogleidentidades`, con subject de Google y usuario únicos. El identificador estable para entrar es `sub`, no el correo. Si el correo ya existe, se debe confirmar la contraseña de Tecno Star para vincular; se mantienen la contraseña y el rol del servidor. Una cuenta nueva completa nombres, apellidos y teléfono salvadoreño, y se crea exclusivamente con rol Cliente. Las cuentas inactivas no pueden acceder.

Activación local: `php database/enable-google.php --apply`; crea un respaldo antes de actualizar y concede únicamente SELECT/INSERT sobre la tabla nueva a la cuenta SQL actual. Comprobación: `php database/enable-google.php --verify`. Las instalaciones nuevas incluyen la migración 009 y sus permisos en `RestrictedUser`.

Pruebas: `php tests/google-oauth.php` (JWT firmado con claves de prueba, respuestas Google simuladas, base temporal aislada y rollback de vínculos), `php tests/google-http.php` (servidor local, botones, redirecciones, cancelación, callback ajeno y CSRF). No se crean cuentas reales en estas pruebas. El correo SMTP y el QR siguen funcionando independientemente.

1. Entra en [Google Cloud Console](https://console.cloud.google.com/) y selecciona tu proyecto **TectoStar (`tectostar`)** en el selector superior. No hace falta crear otro proyecto.
2. Abre **Google Auth Platform**. Si ves **Comenzar**, completa la configuración inicial.
3. En **Branding / Información de la marca**, coloca el nombre **Tecno Star**, tu correo de soporte y el contacto de desarrollador.
4. En **Audience / Público**, selecciona **Externo**, porque el remitente es Gmail personal. Durante pruebas, añade los correos que usarás en **Usuarios de prueba**, cuando la consola muestre esa sección.
5. En **Data Access / Acceso a datos**, usa únicamente `openid`, `email` y `profile` para el login. No solicites acceso a Gmail o Drive para iniciar sesión.
6. En **Clients / Clientes**, pulsa **Crear cliente → Aplicación web**. Nombre sugerido: **Tecno Star local**.
7. Para un flujo de redirección del servidor, registra esta **URI de redirección autorizada** exactamente:

   ```text
   http://localhost:8012/Tecno_Star/public/auth/google/callback
   ```

   El puerto, mayúsculas, ruta y barra final deben coincidir con el callback implementado. No pongas `/login` ni `/auth/google` como callback. `localhost` admite pruebas locales por HTTP; el acceso desde IP privada de la LAN requiere una configuración HTTPS/dominio compatible con las reglas de Google. Usa `localhost` para las pruebas OAuth de esta PC.
8. Si también configuras **Orígenes autorizados de JavaScript**, el origen local es `http://localhost:8012`, sin rutas. Ese campo es necesario para una integración JavaScript de Google Identity Services; no sustituye la URI de redirección de un flujo del servidor.
9. Pulsa **Crear**. Guarda **ID de cliente** y **Secreto del cliente** cuando se muestren. Descarga el JSON si lo necesitas y consérvalo fuera de las carpetas públicas y del repositorio. Si un secreto anterior ya no se puede visualizar, crea uno nuevo desde la consola y reemplázalo en la configuración privada.
10. En `.env` reserva estas variables para la integración:

```dotenv
GOOGLE_CLIENT_ID="TU_CLIENT_ID.apps.googleusercontent.com"
GOOGLE_CLIENT_SECRET="TU_CLIENT_SECRET"
GOOGLE_REDIRECT_URI=http://localhost:8012/Tecno_Star/public/auth/google/callback
```

**Las claves que necesitas:** para correo, `MAIL_PASSWORD` de aplicación; para login, `GOOGLE_CLIENT_ID` y `GOOGLE_CLIENT_SECRET`. No necesitas una API key ni un archivo de cuenta de servicio para el login de usuarios.

Para publicar después, crea un cliente OAuth de producción, usa el dominio HTTPS real y registra su callback exacto. Actualiza `APP_URL` y `GOOGLE_REDIRECT_URI`, completa los datos de marca y privacidad y revisa los requisitos de publicación de Google. La configuración actual es local y no se publicó la aplicación.

Fuentes oficiales: [crear credenciales](https://developers.google.com/workspace/guides/create-credentials), [OpenID Connect](https://developers.google.com/identity/openid-connect/openid-connect), [flujo OAuth para servidor](https://developers.google.com/identity/protocols/oauth2/web-server), [gestión de clientes](https://support.google.com/cloud/answer/15549257), [gestión y protección de secretos](https://developers.googleblog.com/en/usability-and-safety-updates-to-google-auth-platform/).
