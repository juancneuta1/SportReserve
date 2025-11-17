import 'package:flutter/foundation.dart';

/// Centraliza el consumo de banderas proveniente de `.env`, Remote Config
/// o cualquier otra fuente declarativa. Se puede sobreescribir en tiempo
/// de ejecución para pruebas o cuando el backend expone nuevas llaves.
class AppEnvironment {
  AppEnvironment._();
  static final AppEnvironment instance = AppEnvironment._();

  bool? _remoteForceSandbox;
  String? _remoteAvailabilitySocketUrl;

  /// Bandera que obliga a usar las credenciales sandbox de Mercado Pago.
  bool get mercadopagoForceSandbox =>
      _remoteForceSandbox ??
      const bool.fromEnvironment(
        'MERCADOPAGO_FORCE_SANDBOX',
        defaultValue: false,
      );

  /// URL del socket/layer realtime para recibir `availability.updated`.
  String get availabilitySocketUrl =>
      _remoteAvailabilitySocketUrl ??
      const String.fromEnvironment('AVAILABILITY_SOCKET_URL', defaultValue: '');

  /// Aplica valores provenientes de Remote Config o de un endpoint propio.
  void applyRemoteConfig(Map<String, dynamic> remoteConfig) {
    if (remoteConfig.containsKey('MERCADOPAGO_FORCE_SANDBOX')) {
      _remoteForceSandbox = _parseBool(
        remoteConfig['MERCADOPAGO_FORCE_SANDBOX'],
      );
    }

    if (remoteConfig.containsKey('AVAILABILITY_SOCKET_URL')) {
      final socketValue = remoteConfig['AVAILABILITY_SOCKET_URL'];
      if (socketValue is String && socketValue.isNotEmpty) {
        _remoteAvailabilitySocketUrl = socketValue;
      } else {
        _remoteAvailabilitySocketUrl = null;
      }
    }
  }

  /// Permite modificar las banderas manualmente (�til para pruebas).
  void overrideSandboxMode(bool enabled) => _remoteForceSandbox = enabled;

  void overrideSocketUrl(String url) =>
      _remoteAvailabilitySocketUrl = url.trim().isEmpty ? null : url.trim();

  bool? _parseBool(dynamic value) {
    if (value is bool) return value;
    if (value is String) {
      final normalized = value.trim().toLowerCase();
      if (normalized == 'true' || normalized == '1' || normalized == 'yes') {
        return true;
      }
      if (normalized == 'false' || normalized == '0' || normalized == 'no') {
        return false;
      }
    }
    if (value is num) return value != 0;
    return null;
  }
}
