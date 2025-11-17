import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/calificacion.dart';
import 'auth_service.dart';

class CalificacionService {
  CalificacionService({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;
  static const String _baseUrl = 'http://10.0.2.2:8000/api';

  Uri _uri(String path) {
    if (path.startsWith('http')) return Uri.parse(path);
    return Uri.parse('$_baseUrl$path');
  }

  Future<Map<String, String>> _headers() =>
      AuthService.instance.authenticatedJsonHeaders();

  Future<void> enviarCalificacion({
    required int canchaId,
    required int estrellas,
    String? comentario,
  }) async {
    final payload = <String, dynamic>{
      'cancha_id': canchaId,
      'estrellas': estrellas,
      if (comentario != null && comentario.isNotEmpty) 'comentario': comentario,
    };

    final response = await _client.post(
      _uri('/calificaciones'),
      headers: await _headers(),
      body: jsonEncode(payload),
    );

    if (response.statusCode == 201) return;

    if (response.statusCode >= 500) {
      throw Exception(
        'Las calificaciones todavía no están disponibles. Inténtalo más tarde.',
      );
    }

    if (response.statusCode != 201) {
      final message = _extractMessage(response.body) ??
          'No se pudo enviar la calificación (${response.statusCode}).';
      throw Exception(message);
    }
  }

  Future<Map<String, dynamic>> obtenerPromedio(int canchaId) async {
    final response = await _client.get(
      _uri('/calificaciones/$canchaId/promedio'),
      headers: await _headers(),
    );

    if (response.statusCode == 404) {
      return {'promedio': 0.0, 'total': 0};
    }

    if (response.statusCode >= 500) {
      // Backend aún no tiene la tabla/listado, devolvemos valores neutros.
      return {'promedio': 0.0, 'total': 0};
    }

    if (response.statusCode != 200) {
      final message = _extractMessage(response.body) ??
          'No se pudo obtener el promedio (${response.statusCode}).';
      throw Exception(message);
    }

    final dynamic data = jsonDecode(response.body);
    final promedio = (data is Map<String, dynamic> ? data['promedio'] : null) ??
        (data is List && data.isNotEmpty ? data.first['promedio'] : null);
    final total = (data is Map<String, dynamic> ? data['total'] : null) ??
        (data is List && data.isNotEmpty ? data.first['total'] : null);

    return {
      'promedio': (promedio is num) ? promedio.toDouble() : 0.0,
      'total': (total is num) ? total.toInt() : 0,
    };
  }

  Future<List<Calificacion>> listarCalificaciones(int canchaId) async {
    final response = await _client.get(
      _uri('/calificaciones/$canchaId'),
      headers: await _headers(),
    );

    if (response.statusCode == 404) return <Calificacion>[];

    if (response.statusCode >= 500) {
      return <Calificacion>[];
    }

    if (response.statusCode != 200) {
      final message = _extractMessage(response.body) ??
          'No se pudieron obtener las calificaciones (${response.statusCode}).';
      throw Exception(message);
    }

    final dynamic decoded = jsonDecode(response.body);
    List<dynamic> rawList;

    if (decoded is List) {
      rawList = decoded;
    } else if (decoded is Map<String, dynamic>) {
      final dynamic nested =
          decoded['data'] ?? decoded['calificaciones'] ?? decoded['items'];
      rawList = nested is List ? nested : <dynamic>[];
    } else {
      rawList = <dynamic>[];
    }

    return rawList
        .whereType<Map<String, dynamic>>()
        .map(Calificacion.fromJson)
        .toList();
  }

  String? _extractMessage(String body) {
    try {
      final dynamic decoded = jsonDecode(body);
      if (decoded is Map<String, dynamic>) {
        if (decoded['message'] is String) return decoded['message'] as String;
        if (decoded['error'] is String) return decoded['error'] as String;

        if (decoded['errors'] is Map<String, dynamic>) {
          final errors = decoded['errors'] as Map<String, dynamic>;
          final first = errors.values.first;
          if (first is List && first.isNotEmpty) {
            return first.first.toString();
          }
        }
      }
    } catch (_) {
      // Ignorar parseos fallidos
    }
    return null;
  }
}
