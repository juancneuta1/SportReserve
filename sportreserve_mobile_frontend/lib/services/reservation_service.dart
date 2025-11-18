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
  // JSON SANITIZATION SEGURO (NO ROMPE COMILLAS NI ESPACIOS)
  // ----------------------------------------------------------
  String sanitizeJson(String raw) {
    if (raw.isEmpty) return raw;

    return raw
        .replaceAll('\uFEFF', '')
        .replaceAll('\u200B', '')
        .replaceAll(RegExp(r'[\u0000-\u0008\u000B\u000C\u000E-\u001F]'), '');
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

    final link = rawLink
        .replaceAll('\n', ' ')
        .replaceAll('\r', ' ')
        .replaceAll('redir rect', 'redirect')
        .trim();

    if (link.isEmpty) return null;
    if (!link.startsWith('http://') && !link.startsWith('https://')) {
      return null;
    }
    return link;
  }

  Map<String, String>? _readBackUrls(Map<String, dynamic> reserva) {
    final dynamic rawBackUrls = reserva['back_urls'];
    if (rawBackUrls is Map) {
      final mapped = <String, String>{};
      rawBackUrls.forEach((k, v) {
        if (k != null && v != null) {
          mapped[k.toString()] = v.toString();
        }
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
    required String deporte,
    required String fecha,
    required String hora,
    required int cantidadHoras,
    required double precioPorCancha,
  }) async {
    try {
      final headers = await _authHeaders();
      final body = {
        'cancha_id': canchaId,
        'deporte': deporte,
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
          'El servidor no devolvió datos (codigo ${response.statusCode}).',
        );
      }

      final sanitized = sanitizeJson(rawBody);
      final data = jsonDecode(sanitized);

      if (response.statusCode == 401) {
        return _errorResponse('Sesión expirada');
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

        return {
          'success': true,
          'message': data['message']?.toString(),
          'reserva': reserva,
          'payment_link': paymentLink,
          'init_point': reserva['init_point']?.toString(),
          'back_urls': backUrls,
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
  //                MIS RESERVAS (CORREGIDO)
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

    final raw = response.body;
    if (raw.isEmpty) {
      throw Exception("Respuesta vacía del servidor");
    }

    final sanitized = sanitizeJson(raw);

    try {
      final decoded = json.decode(sanitized);
      return decoded;
    } catch (e) {
      debugPrint("❌ ERROR JSON MIS RESERVAS: $e");
      debugPrint("❌ RAW SANITIZED: $sanitized");
      throw Exception("Formato JSON inválido en mis reservas");
    }
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
