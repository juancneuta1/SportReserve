import 'dart:async';

import 'package:flutter/foundation.dart';

import 'auth_service.dart';
import 'notification_service.dart';

class AppInitializer {
  const AppInitializer._();

  static bool _initialized = false;

  static Future<void> initialize() async {
    if (_initialized) return;

    try {
      await _initializeBackend();
      await _initializeNotifications();
      _initialized = true;
    } catch (error) {
      if (kDebugMode) {
        debugPrint('Error al inicializar la app: $error');
      }
      rethrow;
    }
  }

  static Future<void> _initializeBackend() async {
    // Aqui puedes validar si hay token guardado o limpiar sesiones expiradas.
    final auth = AuthService.instance;
    final user = await auth.getProfile();
    if (kDebugMode) {
      debugPrint(
        user != null
            ? 'Usuario autenticado: ${user.name}'
            : 'Sin sesion activa',
      );
    }
  }

  static Future<void> _initializeNotifications() async {
    try {
      await NotificationService.instance.initialize();
    } catch (error) {
      if (kDebugMode) {
        debugPrint('Error al inicializar notificaciones: $error');
      }
    }
  }
}
