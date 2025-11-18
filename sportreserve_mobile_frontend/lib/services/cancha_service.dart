import 'dart:convert';
import 'dart:typed_data';

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:sportreserve_mobile_frontend/models/cancha.dart';
import 'package:sportreserve_mobile_frontend/mappers/cancha_mapper.dart';
import 'package:sportreserve_mobile_frontend/db/app_database.dart';

class CanchaService {
  // URL base del backend Laravel (usa 10.0.2.2 si ejecutas en emulador Android)
  final String baseUrl = "http://10.0.2.2:8000/api";
  final AppDatabase db;

  CanchaService(this.db);

  dynamic _decodeBody(Uint8List bytes) {
    // Limpia BOM y soporta codificaciones mal formadas.
    String text = utf8.decode(bytes, allowMalformed: true);
    text = text.replaceFirst('\uFEFF', '').trim();
    return jsonDecode(text);
  }

  List<dynamic> _extractList(dynamic body) {
    if (body is List) return body;
    if (body is Map<String, dynamic>) {
      final candidates = [
        body['canchas'],
        body['data'],
        body['items'],
        body['results'],
      ];
      for (final candidate in candidates) {
        if (candidate is List) return candidate;
        if (candidate is Map<String, dynamic> && candidate['data'] is List) {
          return candidate['data'] as List;
        }
      }
    }
    return <dynamic>[];
  }

  Map<String, dynamic> _normalizeCanchaJson(Map<String, dynamic> json) {
    final map = Map<String, dynamic>.from(json);

    dynamic tipo = map['tipo'];
    if (tipo is List) {
      map['tipo'] = tipo.map((e) => e.toString()).join(', ');
    } else if (tipo is Map) {
      map['tipo'] = tipo.values.map((e) => e.toString()).join(', ');
    } else if (tipo != null) {
      map['tipo'] = tipo.toString();
    }

    dynamic servicios = map['servicios'];
    if (servicios is List) {
      map['servicios'] = servicios.map((e) => e.toString()).join(', ');
    } else if (servicios is Map) {
      map['servicios'] = servicios.values.map((e) => e.toString()).join(', ');
    } else if (servicios != null) {
      map['servicios'] = servicios.toString();
    }

    if (map['ubicacion'] != null) {
      map['ubicacion'] = map['ubicacion'].toString();
    }
    return map;
  }

  /// Obtiene todas las canchas desde la API de Laravel o desde cache si no hay conexión
  Future<List<Cancha>> obtenerCanchas() async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/canchas'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode != 200) {
        throw Exception(
          'Error ${response.statusCode}: ${response.reasonPhrase}',
        );
      }

      final body = _decodeBody(response.bodyBytes);

      // Soporta respuestas tipo lista o con claves "data" / "canchas" / "items"
      final List<dynamic> rawList = _extractList(body);
      final normalizedList = rawList
          .whereType<Map<String, dynamic>>()
          .map(_normalizeCanchaJson)
          .toList();

      // Convierte JSON a modelo de dominio
      final canchas =
          normalizedList.map((json) => CanchaMapper.fromJson(json)).toList();

      // Guarda en base de datos local (cache)
      await db.cachearCanchas(
        normalizedList,
      );

      debugPrint('Canchas cargadas desde Laravel: ${canchas.length}');
      return canchas;
    } catch (e) {
      debugPrint('Error o sin conexión. Cargando cache local: $e');

      // Si falla la API, recupera desde base local
      final locales = await db.obtenerCanchas();

      debugPrint('Canchas desde cache local: ${locales.length}');

      return locales
          .map(
            (c) => Cancha(
              id: c.id,
              nombre: c.nombre,
              tipo: c.tipo ?? 'Desconocido',
              tipos: c.tipo != null ? <String>[c.tipo!] : <String>[],
              ubicacion: c.ubicacion ?? 'Sin ubicación',
              latitud: c.latitud ?? 0.0,
              longitud: c.longitud ?? 0.0,
              descripcion: c.descripcion ?? '',
              servicios: c.servicios ?? '',
              disponibilidad: c.disponibilidad,
              capacidad: 0,
              precioPorCancha: 0,
              imagen: '',
            ),
          )
          .toList();
    }
  }

  /// Obtiene una cancha específica por ID (con fallback a cache local)
  Future<Cancha?> getCanchaById(int id) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/canchas/$id'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = _decodeBody(response.bodyBytes);
        final canchaJson = data is Map<String, dynamic>
            ? (data['cancha'] ?? data)
            : null;

        if (canchaJson != null && canchaJson is Map<String, dynamic>) {
          final normalized = _normalizeCanchaJson(canchaJson);
          final cancha = CanchaMapper.fromJson(normalized);
          // Actualiza el cache local con la cancha individual
          await db.cachearCanchas([normalized]);
          return cancha;
        }
      } else if (response.statusCode == 404) {
        debugPrint('Cancha no encontrada (ID: $id)');
        return null;
      } else {
        debugPrint('Error ${response.statusCode}: ${response.reasonPhrase}');
      }
    } catch (e) {
      debugPrint('Sin conexión, buscando cancha $id en cache local');

      final locales = await db.obtenerCanchas();

      // Buscar manualmente la cancha en la cache local
      CanchaLocal? canchaLocal;
      for (final c in locales) {
        if (c.id == id) {
          canchaLocal = c;
          break;
        }
      }

      if (canchaLocal == null) {
        debugPrint('Cancha no encontrada en cache local');
        return null;
      }

      return Cancha(
        id: canchaLocal.id,
        nombre: canchaLocal.nombre,
        tipo: canchaLocal.tipo ?? 'Desconocido',
        tipos:
            canchaLocal.tipo != null ? <String>[canchaLocal.tipo!] : <String>[],
        ubicacion: canchaLocal.ubicacion ?? 'Sin ubicación',
        latitud: canchaLocal.latitud ?? 0.0,
        longitud: canchaLocal.longitud ?? 0.0,
        descripcion: canchaLocal.descripcion ?? '',
        servicios: canchaLocal.servicios ?? '',
        disponibilidad: canchaLocal.disponibilidad,
        capacidad: 0,
        precioPorCancha: 0,
        imagen: '',
      );
    }
    return null;
  }
}
