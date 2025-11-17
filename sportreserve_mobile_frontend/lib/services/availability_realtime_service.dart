import 'dart:async';
import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:web_socket_channel/web_socket_channel.dart';

import '../config/app_environment.dart';
import 'auth_service.dart';

class AvailabilityRealtimeService {
  AvailabilityRealtimeService._();
  static final AvailabilityRealtimeService instance =
      AvailabilityRealtimeService._();

  WebSocketChannel? _channel;
  StreamSubscription<dynamic>? _channelSubscription;
  Timer? _reconnectTimer;
  bool _connecting = false;

  final StreamController<AvailabilityUpdate> _controller =
      StreamController<AvailabilityUpdate>.broadcast();

  Stream<AvailabilityUpdate> get updates => _controller.stream;

  /// Inicia la escucha del socket si existe una URL y la plataforma lo permite.
  Future<void> ensureConnected() async {
    if (_connecting || _channel != null) return;

    final socketUrl = AppEnvironment.instance.availabilitySocketUrl;
    if (socketUrl.isEmpty) {
      if (kDebugMode) {
        debugPrint(
          'AvailabilityRealtimeService: sin URL de socket configurada.',
        );
      }
      return;
    }

    final uri = Uri.tryParse(socketUrl);
    if (uri == null) {
      if (kDebugMode) {
        debugPrint(
          'AvailabilityRealtimeService: URL de socket invalida: $socketUrl',
        );
      }
      return;
    }

    _connecting = true;
    try {
      final headers = await _authHeaders();
      _channel = WebSocketChannel.connect(uri);


      _channelSubscription = _channel!.stream.listen(
        _handleMessage,
        onError: (Object error, StackTrace stackTrace) {
          if (kDebugMode) {
            debugPrint('AvailabilityRealtimeService error: $error');
          }
          _scheduleReconnect();
        },
        onDone: _scheduleReconnect,
      );
    } catch (error) {
      if (kDebugMode) {
        debugPrint('AvailabilityRealtimeService no pudo conectar: $error');
      }
      _scheduleReconnect();
    } finally {
      _connecting = false;
    }
  }

  void pushLocalUpdate(AvailabilityUpdate update) {
    if (!_controller.isClosed) {
      _controller.add(update);
    }
  }

  Future<Map<String, dynamic>> _authHeaders() async {
    await AuthService.instance.isAuthenticated();
    final token = AuthService.instance.token;
    if (token == null || token.isEmpty) return const {};
    return {'Authorization': 'Bearer $token'};
  }

  void _handleMessage(dynamic message) {
    final payload = _normalizePayload(message);
    if (payload == null) return;

    final eventName = payload['event']?.toString();
    if (eventName != 'availability.updated') return;

    final dynamic data = payload['payload'] ?? payload['data'];
    if (data is Map<String, dynamic>) {
      final update = AvailabilityUpdate.fromJson(data);
      pushLocalUpdate(update);
    }
  }

  Map<String, dynamic>? _normalizePayload(dynamic message) {
    if (message is Map<String, dynamic>) return message;
    if (message is String && message.trim().isNotEmpty) {
      try {
        final decoded = jsonDecode(message);
        if (decoded is Map<String, dynamic>) return decoded;
      } catch (_) {
        // Ignorar mensajes no JSON (p.e. pings)
      }
    }
    return null;
  }

  void _scheduleReconnect() {
    _channelSubscription?.cancel();
    _channelSubscription = null;
    _channel?.sink.close();
    _channel = null;

    _reconnectTimer?.cancel();
    _reconnectTimer = Timer(const Duration(seconds: 5), () {
      ensureConnected();
    });
  }

  Future<void> dispose() async {
    await _channelSubscription?.cancel();
    await _channel?.sink.close();
    _reconnectTimer?.cancel();
    await _controller.close();
  }
}

class AvailabilityUpdate {
  const AvailabilityUpdate({required this.canchaId, required this.available});

  final int canchaId;
  final bool available;

  factory AvailabilityUpdate.fromJson(Map<String, dynamic> json) {
    final dynamic canchaValue =
        json['cancha_id'] ?? json['canchaId'] ?? json['id'];
    final canchaId = canchaValue is int
        ? canchaValue
        : int.tryParse(canchaValue?.toString() ?? '') ?? 0;

    final dynamic availabilityValue =
        json['available'] ?? json['disponible'] ?? json['status'];
    final available =
        availabilityValue == true ||
        availabilityValue == 'available' ||
        availabilityValue == 'disponible' ||
        availabilityValue == 1 ||
        availabilityValue == '1';

    return AvailabilityUpdate(canchaId: canchaId, available: available);
  }
}
