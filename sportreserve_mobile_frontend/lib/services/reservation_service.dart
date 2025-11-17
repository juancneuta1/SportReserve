import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

import '../models/reservation_slot.dart';
import 'auth_service.dart';

class ReservationService {
  final String baseUrl = 'http://10.0.2.2:8000/api';

  Future<Map<String, String>> _authHeaders() =>
      AuthService.instance.authenticatedJsonHeaders();

  void _log(String message) => debugPrint('ReservationService: $message');

  // ----------------------------------------------------------
  // JSON SANITIZATION & DECODING
  // ----------------------------------------------------------
  String sanitizeJson(String raw) {
    if (raw.isEmpty) return '';

    var sanitized = raw
        .replaceAll('\uFEFF', '')
        .replaceAll('\u200B', '')
        .replaceAll(RegExp(r'[\u0000-\u0008\u000B\u000C\u000E-\u001F]'), '')
        .replaceAll(RegExp(r'\s+'), ' ') // colapsa espacios/saltos
        .trim();

    // corrige fragmentos cortados por MP: "redir rect" -> "redirect"
    sanitized = sanitized.replaceAll('redir rect', 'redirect');

    // repara comillas escapadas comunes
    sanitized = sanitized.replaceAll(r'\"', '"');

    // balancea llaves y corchetes
    sanitized = _balanceBrackets(sanitized);

    return sanitized;
  }

  String _balanceBrackets(String input) {
    int openCurly = _countChar(input, '{');
    int closeCurly = _countChar(input, '}');
    int openSquare = _countChar(input, '[');
    int closeSquare = _countChar(input, ']');

    final buffer = StringBuffer(input);
    if (openCurly > closeCurly) {
      buffer.write(_repeatChar('}', openCurly - closeCurly));
    }
    if (openSquare > closeSquare) {
      buffer.write(_repeatChar(']', openSquare - closeSquare));
    }
    return buffer.toString();
  }

  int _countChar(String source, String char) {
    var count = 0;
    for (var i = 0; i < source.length; i++) {
      if (source[i] == char) count++;
    }
    return count;
  }

  String _repeatChar(String value, int times) {
    if (times <= 0) return '';
    final buffer = StringBuffer();
    for (var i = 0; i < times; i++) {
      buffer.write(value);
    }
    return buffer.toString();
  }

  Map<String, dynamic>? _decodeToMap(String source) {
    try {
      final dynamic decoded = jsonDecode(source);
      if (decoded is Map<String, dynamic>) return decoded;
      if (decoded is Map) {
        return decoded.map((key, value) => MapEntry(key.toString(), value));
      }
      return null;
    } catch (_) {
      return null;
    }
  }

  Map<String, dynamic>? _safeDecodeBody(String rawBody) {
    final sanitized = sanitizeJson(rawBody);
    if (sanitized.isEmpty) return null;

    final decoded = _decodeToMap(sanitized);
    if (decoded != null) return decoded;

    _log('No se pudo decodificar JSON (len=${sanitized.length})');
    return null;
  }

  // ----------------------------------------------------------
  // HELPERS
  // ----------------------------------------------------------
  Map<String, dynamic> _extractReservation(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) {
      return raw.map((k, v) => MapEntry(k.toString(), v));
    }
    return <String, dynamic>{};
  }

  Map<String, dynamic> _extractErrors(dynamic raw) {
    if (raw is Map<String, dynamic>) return raw;
    if (raw is Map) {
      return raw.map((k, v) => MapEntry(k.toString(), v));
    }
    return <String, dynamic>{};
  }

  String? _cleanPaymentLink(dynamic rawLink) {
    if (rawLink is! String) return null;
    var link = rawLink
        .replaceAll('\n', ' ')
        .replaceAll('\r', ' ')
        .replaceAll('redir rect', 'redirect')
        .trim();
    link = link.replaceAll(RegExp(r'\s+'), '');
    if (link.isEmpty) return null;
    if (!(link.startsWith('http://') || link.startsWith('https://'))) {
      return null;
    }
    return link;
  }

  Map<String, String>? _readBackUrls(Map<String, dynamic> reserva) {
    final dynamic rawBackUrls = reserva['back_urls'];
    if (rawBackUrls is Map) {
      final mapped = <String, String>{};
      rawBackUrls.forEach((key, value) {
        if (key == null || value == null) return;
        final k = key.toString();
        final v = value.toString();
        if (k.isEmpty || v.isEmpty) return;
        mapped[k] = v;
      });
      return mapped.isEmpty ? null : mapped;
    }
    return null;
  }

  Map<String, dynamic> _errorResponse(String message,
          {Map<String, dynamic>? errors}) =>
      {
        'success': false,
        'message': message,
        if (errors != null) 'errors': errors,
      };

  // ======================================================
  //               CREAR RESERVA
  // ======================================================
  Future<Map<String, dynamic>> crearReserva({
    required int canchaId,
    required String fecha,
    required String hora,
    required int cantidadHoras,
    required double precioPorCancha,
  }) async {
    try {
      final headers = await _authHeaders();
      final body = {
        'cancha_id': canchaId,
        'fecha': fecha,
        'hora': hora,
        'cantidad_horas': cantidadHoras,
        'precio_por_cancha': precioPorCancha,
      };

      final response = await http.post(
        Uri.parse('$baseUrl/reservas'),
        headers: headers,
        body: jsonEncode(body),
      );

      final rawBody = response.body;
      if (rawBody.isEmpty) {
        return _errorResponse(
            'El servidor no devolvio datos (codigo ${response.statusCode}).');
      }

      final data = _safeDecodeBody(rawBody);
      if (data == null) {
        return _errorResponse('Respuesta invalida del servidor.');
      }

      if (response.statusCode == 401) {
        return _errorResponse('Sesion expirada');
      }

      if (response.statusCode >= 400 && response.statusCode < 500) {
        return _errorResponse(
          data['message']?.toString() ?? 'No se pudo crear la reserva.',
          errors: _extractErrors(data['errors']),
        );
      }

      if (response.statusCode == 201 && data['success'] == true) {
        final reserva = _extractReservation(data['reserva']);
        final paymentLink =
            _cleanPaymentLink(reserva['payment_link']) ??
                _cleanPaymentLink(reserva['init_point']);
        final backUrls = _readBackUrls(reserva) ?? <String, String>{};
        final environment = reserva['environment']?.toString();

        return {
          'success': true,
          'message':
              data['message']?.toString() ?? 'Reserva creada correctamente.',
          'reserva': reserva,
          'payment_link': paymentLink,
          'init_point': reserva['init_point']?.toString(),
          'back_urls': backUrls,
          'environment': environment,
        };
      }

      return _errorResponse(
        data['message']?.toString() ??
            'Error desconocido al crear la reserva.',
        errors: _extractErrors(data['errors']),
      );
    } catch (e) {
      _log('Error al crear la reserva: $e');
      return _errorResponse('Error interno al crear la reserva.');
    }
  }

  // ======================================================
  //                DISPONIBILIDAD
  // ======================================================
  Future<Map<String, dynamic>> obtenerDisponibilidad({
    required int canchaId,
    required String fecha,
  }) async {
    final headers = await _authHeaders();
    final response = await http.get(
      Uri.parse('$baseUrl/canchas/$canchaId/disponibilidad?fecha=$fecha'),
      headers: headers,
    );

    if (response.statusCode == 401) {
      throw Exception('Unauthenticated');
    }

    return jsonDecode(response.body);
  }

  // ======================================================
  //                MIS RESERVAS
  // ======================================================
  Future<dynamic> obtenerMisReservas() async {
    final headers = await _authHeaders();
    final response = await http.get(
      Uri.parse('$baseUrl/mis-reservas'),
      headers: headers,
    );

    if (response.statusCode == 401) {
      throw Exception('Unauthenticated');
    }

    return jsonDecode(response.body);
  }

  // ======================================================
  //              CANCELAR RESERVA
  // ======================================================
  Future<Map<String, dynamic>> cancelarReserva(int reservaId) async {
    final headers = await _authHeaders();

    final response = await http.put(
      Uri.parse('$baseUrl/reservas/$reservaId/cancelar'),
      headers: headers,
    );

    if (response.statusCode == 401) {
      throw Exception('Unauthenticated');
    }

    return jsonDecode(response.body);
  }

  // ======================================================
  //            HORARIOS DIARIOS
  // ======================================================
  Future<List<ReservationSlot>> fetchDailySlots({
    required int canchaId,
    required DateTime day,
  }) async {
    final headers = await _authHeaders();
    final fecha =
        '${day.year}-${day.month.toString().padLeft(2, '0')}-${day.day.toString().padLeft(2, '0')}';

    final response = await http.get(
      Uri.parse('$baseUrl/canchas/$canchaId/horarios?fecha=$fecha'),
      headers: headers,
    );

    if (response.statusCode == 401) {
      throw Exception('Unauthenticated');
    }

    if (response.statusCode != 200) {
      throw Exception(
        'Error al obtener horarios (${response.statusCode}): ${response.body}',
      );
    }

    final data = jsonDecode(response.body);

    final List<dynamic> rawList = data is List
        ? data
        : (data is Map<String, dynamic> && data['data'] is List
            ? data['data']
            : <dynamic>[]);

    return rawList
        .whereType<Map<String, dynamic>>()
        .map(ReservationSlot.fromJson)
        .toList();
  }
}
