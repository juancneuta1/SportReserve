# SportReserve Mobile (Flutter)

App móvil para descubrir canchas deportivas, reservar, pagar por WebView (Mercado Pago) y dejar calificaciones. Usa Flutter + Drift (SQLite) para cache local, `go_router` para navegación y WebSockets para disponibilidad en tiempo real.

## Requisitos de entorno
- Flutter SDK con Dart ≥ 3.9.2 (versión estable que lo incluya). Instala desde https://flutter.dev/docs/get-started/install y agrega `flutter/bin` al `PATH`.
- Android Studio (SDK 34+, emulador o dispositivo con depuración USB) y Java 17+.
- Xcode 15+ si compilas en iOS (en macOS).
- Chrome si corres `flutter run -d chrome`.
- Backend Laravel accesible en red: la app apunta a `http://10.0.2.2:8000/api` (emulador Android). Para dispositivos reales cambia las URLs a la IP de tu backend.

## Puesta en marcha (PC recién formateado)
1) `flutter doctor` y corrige lo que falte (SDKs, licencias).  
2) Clona el repo y entra en la carpeta.  
3) Instala dependencias: `flutter pub get`.  
4) Si modificas Drift (`lib/db/app_database.dart`), regenera código:  
   `dart run build_runner build --delete-conflicting-outputs`.  
5) Corre en emulador/dispositivo: `flutter run -d emulator-5554` (Android) o `flutter run -d chrome` (web).  
6) Producción: `flutter build apk --release` o `flutter build appbundle` (Android), `flutter build ipa` (iOS, en macOS).

## Configuración de backend y entornos
- Base de API:  
  - Auth/Reservas/Calificaciones usan `http://10.0.2.2:8000/api` en `lib/services/auth_service.dart`, `reservation_service.dart`, `cancha_service.dart`, `calificacion_service.dart`, `pago_service.dart`.  
  - `lib/services/user_service.dart` usa `http://127.0.0.1:8000/api`. Ajusta estas bases si usas IP LAN o dominio.
- WebSocket de disponibilidad: define `AVAILABILITY_SOCKET_URL` (ws/wss) via `--dart-define=AVAILABILITY_SOCKET_URL=wss://tu-socket` para `AvailabilityRealtimeService`.
- Mercado Pago sandbox: `--dart-define=MERCADOPAGO_FORCE_SANDBOX=true` o usa `AppEnvironment.overrideSandboxMode`.
- Notificaciones locales: usan `flutter_local_notifications` + zona horaria; en iOS pide permisos de notificación.

## Arquitectura y carpetas clave
- `lib/main.dart`: inicializa servicios, tema (light/dark) y lee si se mostró onboarding.  
- `lib/app_router.dart`: rutas con `go_router` (onboarding, mapa, auth, perfil, reservas, pasarela).  
- `lib/config/app_environment.dart`: banderas de runtime (sandbox, socket).  
- `lib/db/app_database.dart`: Drift + SQLite para cachear canchas (`app_database.g.dart` generado).  
- Modelos: `lib/models/*` (cancha, reserva, usuario, calificación, etc.).  
- Servicios REST/WS:  
  - `auth_service.dart` (login/registro/profile/token/contraseña/foto).  
  - `cancha_service.dart` (listado/detalle + cache local).  
  - `reservation_service.dart` (crear reserva, horarios, mis reservas, cancelar).  
  - `pago_service.dart` (estado de pago), `availability_realtime_service.dart` (WebSocket), `calificacion_service.dart` y `review_service.dart`.  
  - `notification_service.dart` (notificaciones inmediatas y recordatorios).  
- UI:  
  - Onboarding: `features/onboarding/onboarding_page.dart`.  
  - Auth: `features/auth/*` (login, registro, recuperar/reset password, campos comunes).  
  - Canchas: mapa (`mapa_canchas_page.dart` con `flutter_map`), listado (`canchas_page.dart`), detalle/reserva (`cancha_detail_page.dart`), registro de cancha y widgets de deporte/calificación.  
  - Reservas: `mis_reservas_page.dart` (refresco automático + chips de estado), `pasarela_pago_page.dart` (WebView Mercado Pago), `reservation_events_bus.dart`.  
  - Perfil: `user_profile_page.dart` (cambiar contraseña, foto, ver reservas, logout).  
  - Otros: splash en `lib/splash/splash_screen.dart`, onboarding assets en `assets/`.

## Flujo principal
1) Onboarding → guarda preferencia en SharedPreferences y redirige a mapa.  
2) Mapa/lista de canchas → datos desde Laravel con fallback a cache local (Drift).  
3) Detalle de cancha → selecciona fecha/hora/deporte, crea reserva vía `ReservationService`, recibe link de pago y abre `PasarelaPagoPage` con WebView; eventos se propagan por `ReservationEventsBus`.  
4) Mis reservas → lista, refresco automático de estado de pago (`PagoService`).  
5) Calificaciones → carga promedio/resenas y envía nuevas (autenticado).  
6) Perfil → muestra/actualiza datos, cambio de contraseña, foto, logout.

## Assets y branding
- Splash/Iconos configurados en `pubspec.yaml` (`flutter_native_splash`, `flutter_launcher_icons`).  
- Fondos y onboarding en `assets/images/*`, animación Lottie en `assets/lottie/loading.json`. Ejecuta `dart run flutter_native_splash:create` o `dart run flutter_launcher_icons` si cambias imágenes.

## Variables y notas importantes
- Emulador Android usa `10.0.2.2`; en dispositivo real actualiza las constantes baseUrl a la IP del backend.  
- Para pagos, la pantalla espera `back_urls` (success/failure/pending) y `payment_link`/`init_point`.  
- La clave de onboarding se guarda como `hasSeenboarding` en `OnboardingPage`, pero `main.dart` lee `hasSeenOnboarding`; ajusta ambas si quieres persistencia correcta.  
- `AppEnvironment` permite forzar sandbox/URL de socket sin modificar código.

## Comandos útiles
- Formato/lints: `flutter analyze`.  
- Tests (solo viene el de ejemplo en `test/widget_test.dart`): `flutter test`.  
- Regenerar Drift: `dart run build_runner build --delete-conflicting-outputs`.

## Checklist rápido post-instalación
- [ ] `flutter doctor` sin errores.  
- [ ] SDK Android instalado y emulador creado.  
- [ ] `flutter pub get` ejecutado.  
- [ ] URLs de backend ajustadas a tu entorno.  
- [ ] Opcional: `--dart-define` para sandbox/socket si aplica.  
- [ ] Prueba `flutter run` en emulador/dispositivo.

