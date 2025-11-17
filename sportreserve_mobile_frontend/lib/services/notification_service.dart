import 'dart:async';
import 'dart:math';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:timezone/data/latest.dart' as tz;
import 'package:timezone/timezone.dart' as tz;

class NotificationService {
  NotificationService._();
  static final NotificationService instance = NotificationService._();

  final FlutterLocalNotificationsPlugin _localNotifications =
      FlutterLocalNotificationsPlugin();
  bool _initialized = false;

  /// 🔹 Inicializa el sistema de notificaciones locales
  Future<void> initialize() async {
    if (_initialized) return;

    await _initializeTimezone();
    await _configureLocalNotifications();
    await _applyPromotionPreference();

    _initialized = true;
  }

  Future<void> _initializeTimezone() async {
    tz.initializeTimeZones();
    try {
      tz.setLocalLocation(tz.getLocation(DateTime.now().timeZoneName));
    } catch (_) {
      tz.setLocalLocation(tz.getLocation('America/Bogota'));
    }
  }

  Future<void> _configureLocalNotifications() async {
    const androidSettings = AndroidInitializationSettings(
      '@mipmap/ic_launcher',
    );
    const darwinSettings = DarwinInitializationSettings();

    await _localNotifications.initialize(
      const InitializationSettings(
        android: androidSettings,
        iOS: darwinSettings,
      ),
      onDidReceiveNotificationResponse: _onNotificationResponse,
    );
  }

  /// 🔹 Mostrar una notificación inmediata
  Future<void> showInstantNotification({
    required String title,
    required String body,
  }) async {
    const androidDetails = AndroidNotificationDetails(
      'sportreserve_now',
      'Notificaciones inmediatas',
      channelDescription: 'Mensajes directos dentro de la app',
      importance: Importance.max,
      priority: Priority.high,
    );
    const iosDetails = DarwinNotificationDetails();

    await _localNotifications.show(
      Random().nextInt(100000),
      title,
      body,
      const NotificationDetails(android: androidDetails, iOS: iosDetails),
    );
  }

  /// 🔹 Programar recordatorio antes de una reserva
  Future<void> scheduleReminder({
    required DateTime eventDateTime,
    required String title,
    required String body,
    Duration advance = const Duration(hours: 1),
  }) async {
    final tz.TZDateTime scheduledDate = tz.TZDateTime.from(
      eventDateTime,
      tz.local,
    ).subtract(advance);

    if (scheduledDate.isBefore(tz.TZDateTime.now(tz.local))) {
      return;
    }

    const androidDetails = AndroidNotificationDetails(
      'sportreserve_reminders',
      'Recordatorios',
      channelDescription: 'Avisos antes del partido o evento',
      importance: Importance.high,
      priority: Priority.high,
    );
    const iosDetails = DarwinNotificationDetails();

    await _localNotifications.zonedSchedule(
      Random().nextInt(100000),
      title,
      body,
      scheduledDate,
      const NotificationDetails(android: androidDetails, iOS: iosDetails),
      androidScheduleMode: AndroidScheduleMode.exactAllowWhileIdle,
      uiLocalNotificationDateInterpretation:
          UILocalNotificationDateInterpretation.absoluteTime,
      matchDateTimeComponents: DateTimeComponents.dateAndTime,
    );
  }

  /// 🔹 Manejar clic en notificación
  Future<void> _onNotificationResponse(NotificationResponse response) async {
    if (kDebugMode) {
      debugPrint('Notificación tocada: ${response.payload}');
    }
  }

  /// 🔹 Guardar preferencia local de promociones
  Future<void> updatePromotionPreference(bool enabled) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('promotionsEnabled', enabled);
  }

  /// 🔹 Aplicar la preferencia guardada (por ejemplo, al iniciar app)
  Future<void> _applyPromotionPreference() async {
    final prefs = await SharedPreferences.getInstance();
    final enabled = prefs.getBool('promotionsEnabled') ?? true;
    if (kDebugMode) {
      debugPrint(
        enabled
            ? '🔔 Promociones activadas localmente.'
            : '🔕 Promociones desactivadas.',
      );
    }
  }
}
