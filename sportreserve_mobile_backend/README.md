# SportReserve Mobile - Backend API (Laravel 12)

API en Laravel 12 para reservas deportivas. Incluye auth con Sanctum, gestion de canchas y reservas con disponibilidad horaria, pagos via Mercado Pago, calificaciones y modulo de seguridad para auditoria de sesiones.

## Stack
- PHP 8.2+, Laravel 12
- Sanctum (Bearer tokens)
- Base de datos: PostgreSQL recomendado (SQLite soportado en dev)
- Mercado Pago SDK, Google2FA (campos listos), colas en BD para mails
- Vite/NPM solo para assets del scaffold

## Estructura rapida
- app/Http/Controllers
  - AuthController: registro/login/logout, reset y cambio de contrasena, perfil, foto.
  - CanchaController: CRUD de canchas + filtros y disponibilidad (CourtAvailabilityService).
  - ReservaController: reservas, validacion de horarios/solapes, precio, comprobantes, estado de pago.
  - CalificacionController: resenas y promedios.
  - MercadoPagoController: webhook de pagos.
- app/Services
  - CourtAvailabilityService: slots entre AVAILABILITY_* evitando solapes.
  - MercadoPagoService: preferencias MP, URLs de retorno, webhook.
- app/Models: User, Cancha, Reserva, Calificacion, AdminNotification, etc.
- config/sportreserve.php: horario de apertura/cierre y step de disponibilidad.
- user-security-module/: modulo de seguridad (logs, sesiones, intentos fallidos, mails).

## Puesta en marcha
1) Clonar e instalar deps
```bash
git clone <repo> sportreserve_mobile_backend
cd sportreserve_mobile_backend
composer install
npm install   # solo si usaras Vite/asset pipeline
```
2) Copiar .env y generar APP_KEY
```bash
cp .env.example .env
php artisan key:generate
```
3) Configurar .env (DB, correo, Mercado Pago, horarios).
4) Migrar base de datos (incluye modulo de seguridad)
```bash
php artisan migrate
```
5) Enlazar storage para fotos/comprobantes
```bash
php artisan storage:link
```
6) Levantar servicios
```bash
php artisan serve
php artisan queue:work   # recomendado para mails/notificaciones
```

## Variables de entorno clave
- Base de datos: DB_CONNECTION=pgsql, DB_HOST/PORT/USERNAME/PASSWORD/DB_DATABASE.
- Mercado Pago: MERCADOPAGO_ACCESS_TOKEN, MERCADOPAGO_PUBLIC_KEY, MERCADOPAGO_NOTIFICATION_URL, MERCADOPAGO_SUCCESS_URL, MERCADOPAGO_PENDING_URL, MERCADOPAGO_FAILURE_URL, MERCADOPAGO_FORCE_SANDBOX=true|false.
- Horarios (config/sportreserve.php): AVAILABILITY_OPENING_HOUR (06:00), AVAILABILITY_CLOSING_HOUR (22:00), AVAILABILITY_STEP_MINUTES (30).
- Correo: MAIL_* para notificaciones.
- Cola: QUEUE_CONNECTION=database (por defecto en .env.example).

## Modelos/datos principales
- User: role, photo_url, must_change_password, last_login_at, campos 2FA y lockout (failed_login_count, locked_until).
- Cancha: nombre, tipo (array de deportes), ubicacion, latitud/longitud, precio_por_hora, servicios, descripcion, disponibilidad.
- Reserva: estado (pendiente, pendiente_verificacion, confirmada, cancelada, etc.), payment_status, payment_link/reference/id/detail, comprobante. Helpers: isPaid/isPending/isRejected.
- Calificacion: estrellas, comentario, relaciones user/cancha.
- user-security-module: tablas user_access_logs, user_mobile_sessions, user_failed_logins; eventos UserLoginSucceeded/Failed/Registered con listeners para loguear y notificar.

## Endpoints principales (API REST)
- Salud: GET /api/ping
- Auth publica: POST /api/register, POST /api/login, POST /api/password/forgot, POST /api/password/reset
- Perfil (Sanctum): POST /api/logout, GET /api/profile, POST /api/change-password, POST /api/update-photo
- Canchas: GET /api/canchas[?tipo=&ubicacion=&disponibilidad=&fecha=], GET /api/canchas/{id}, POST /api/canchas, PUT /api/canchas/{id}, DELETE /api/canchas/{id}
- Reservas (auth): GET /api/reservas, POST /api/reservas, PUT /api/reservas/{id}, DELETE /api/reservas/{id}, GET /api/mis-reservas, PUT /api/reservas/{id}/cancelar, POST /api/reservas/{id}/comprobante, GET /api/canchas/{id}/disponibilidad, GET /api/canchas/{id}/horarios, GET /api/reservas/{id}/estado-pago; admin: GET /api/reservas/pendientes, PUT /api/reservas/{id}/validar
- Calificaciones: GET /api/calificaciones/{cancha_id}/promedio, GET /api/calificaciones/{cancha_id}, GET /api/canchas/{cancha_id}/calificaciones/resumen, POST /api/canchas/{cancha_id}/calificaciones
- Mercado Pago: POST /api/mercadopago/webhook (apunta a MERCADOPAGO_NOTIFICATION_URL)
- Modulo seguridad (user-security-module): POST /api/user/login, POST /api/user/register, GET /api/user/security/logs, GET /api/user/security/sessions, POST /api/user/security/logout-all

## Flujo de reservas y pagos
1. Cliente crea reserva (cancha_id, deporte, fecha, hora, cantidad_horas). Backend calcula precio con precio_por_hora y valida solapes/horarios (6:00 a 22:00 aprox).
2. Si monto < 10.000 COP (hardcoded) se rechaza. Si es gratis se confirma sin pago.
3. Si requiere pago: se crea preferencia MP (payment_link/payment_reference) y se deja estado pendiente_verificacion + payment_status=pendiente_pago.
4. Webhook MP actualiza estado/pago segun approved/rejected/cancelled. Admin puede validar manualmente (PUT /api/reservas/{id}/validar).

## Subida de archivos
- Foto de perfil: POST /api/update-photo (photo image max 2MB) -> storage/app/public/profile_photos, devuelve URL publica.
- Comprobante: POST /api/reservas/{id}/comprobante (jpg/png/pdf 2MB) -> storage/app/public/comprobantes.
- Requiere `php artisan storage:link` y disco `public`.

## Seguridad y auditoria
- Auth con Sanctum (Bearer).
- Notificacion de login por email (SendUserLoginNotification + plantilla HTML).
- Modulo seguridad: logs de IP/device/user-agent, sesiones moviles, bloqueo temporal por intentos fallidos, aviso de login sospechoso si cambia IP.
- Campos 2FA listos en modelo (pendiente de UI/flujo).

## Comandos utiles (composer.json)
- composer setup: instala deps, copia .env si falta, key:generate, migra, npm install, npm run build.
- composer dev: servidor + cola + logs + Vite en paralelo (npx concurrently).
- composer test / php artisan test: corre suite.
- php artisan migrate --force: despliegue.
- php artisan queue:listen --tries=1: desarrollo de colas.

## Tests
Aun no hay pruebas de dominio especifico. Usa php artisan test con .env.testing/SQLite para validar el esqueleto.

## Deploy sugerido
- Definir variables MP y MAIL en el entorno productivo.
- composer install --no-dev, php artisan config:cache && route:cache.
- php artisan migrate --force.
- Worker de colas en segundo plano (systemd/Supervisor).
- HTTPS para callbacks de Mercado Pago y actualizar URLs en .env.
