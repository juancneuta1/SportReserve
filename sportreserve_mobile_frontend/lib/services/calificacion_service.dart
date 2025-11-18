import 'dart:convert';
import 'dart:typed_data';

import 'package:http/http.dart' as http;

import '../models/calificacion.dart';
import 'auth_service.dart';

class CalificacionResumen {
  CalificacionResumen({
    required this.promedio,
    required this.total,
    required this.calificaciones,
  });

  final double promedio;
  final int total;
  final List<Calificacion> calificaciones;
}

class CalificacionService {
  CalificacionService({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;
  static const String _baseUrl = 'http://10.0.2.2:8000/api';

  dynamic _decodeBody(Uint8List bytes) {
    // Decodifica tolerando BOM o caracteres malformados.
    String text = utf8.decode(bytes, allowMalformed: true);
    text = text.replaceFirst('\uFEFF', '').trim();
    return jsonDecode(text);
  }

  Uri _uri(String path, [Map<String, dynamic>? query]) {
    final uri = path.startsWith('http')
        ? Uri.parse(path)
        : Uri.parse('$_baseUrl$path');
    if (query == null) return uri;
    return uri.replace(
      queryParameters: <String, String>{
        ...uri.queryParameters,
        ...query.map((k, v) => MapEntry(k, v.toString())),
      },
    );
  }

  Future<Map<String, String>> _headers() =>
      AuthService.instance.authenticatedJsonHeaders();

  Future<Map<String, String>> _headersOrPublic() async {
    try {
      return await _headers();
    } catch (_) {
      return const {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      };
    }
  }

  Future<void> enviarCalificacion({
    required int canchaId,
    required int estrellas,
    String? comentario,
  }) async {
    final payload = <String, dynamic>{
      'estrellas': estrellas,
      'rating': estrellas,
      if (comentario != null && comentario.isNotEmpty)
        'comentario': comentario,
      if (comentario != null && comentario.isNotEmpty) 'comment': comentario,
    };

    final response = await _client.post(
      _uri('/canchas/$canchaId/calificaciones'),
      headers: await _headersOrPublic(),
      body: jsonEncode(payload),
    );

    if (response.statusCode == 201 || response.statusCode == 200) return;

    if (response.statusCode >= 500) {
      throw Exception(
        'Las calificaciones todavia no estan disponibles. Intentalo mas tarde.',
      );
    }

    final message = _extractMessage(response.body) ??
          'No se pudo enviar la calificacion (${response.statusCode}).';
    throw Exception(message);
  }

  Future<CalificacionResumen> obtenerResumen(int canchaId) async {
    final response = await _client.get(
      _uri('/canchas/$canchaId/calificaciones/resumen'),
      headers: await _headersOrPublic(),
    );

    if (response.statusCode == 404) {
      return CalificacionResumen(
        promedio: 0.0,
        total: 0,
        calificaciones: <Calificacion>[],
      );
    }

    if (response.statusCode >= 500) {
      return CalificacionResumen(
        promedio: 0.0,
        total: 0,
        calificaciones: <Calificacion>[],
      );
    }

    if (response.statusCode != 200) {
      final message = _extractMessage(response.body) ??
          'No se pudo obtener el promedio (${response.statusCode}).';
      throw Exception(message);
    }

    final dynamic data = _decodeBody(response.bodyBytes);
    final Map<String, dynamic> map =
        data is Map<String, dynamic> ? data : <String, dynamic>{};

    final reviewsRaw = map['reviews'] ??
        map['calificaciones'] ??
        map['data'] ??
        <dynamic>[];
    final List<Calificacion> calificaciones = (reviewsRaw is List)
        ? reviewsRaw
            .whereType<Map<String, dynamic>>()
            .map(Calificacion.fromJson)
            .toList()
        : <Calificacion>[];

    double _parseDouble(dynamic value) {
      if (value is num) return value.toDouble();
      if (value is String) return double.tryParse(value) ?? 0;
      return 0;
    }

    int _parseInt(dynamic value) {
      if (value is int) return value;
      if (value is num) return value.toInt();
      if (value is String) return int.tryParse(value) ?? 0;
      return 0;
    }

    final promedio = _parseDouble(map['average'] ?? map['promedio']);
    final total = _parseInt(
      map['count'] ?? map['total'] ?? map['reviews_count'],
    );

    return CalificacionResumen(
      promedio: promedio,
      total: total > 0 ? total : calificaciones.length,
      calificaciones: calificaciones,
    );
  }

  Future<Map<String, dynamic>> obtenerPromedio(int canchaId) async {
    final resumen = await obtenerResumen(canchaId);
    return {'promedio': resumen.promedio, 'total': resumen.total};
  }

  Future<List<Calificacion>> listarCalificaciones(int canchaId) async {
    final resumen = await obtenerResumen(canchaId);
    return resumen.calificaciones;
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
