import 'dart:convert';
import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:sportreserve_mobile_frontend/models/cancha.dart';
import 'package:sportreserve_mobile_frontend/mappers/cancha_mapper.dart';
import 'package:sportreserve_mobile_frontend/db/app_database.dart';

class CanchaService {
  // ✅ URL base del backend Laravel (usa 10.0.2.2 si ejecutas en emulador Android)
  final String baseUrl = "http://10.0.2.2:8000/api";
  final AppDatabase db;

  CanchaService(this.db);

  /// 🔹 Obtiene todas las canchas desde la API de Laravel o desde cache si no hay conexión
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

      final body = jsonDecode(response.body);

      // ✅ Soporta respuestas tipo lista o con claves "data" o "canchas"
      final List<dynamic> rawList = body is List
          ? body
          : (body['canchas'] ?? body['data'] ?? []);

      // ✅ Convierte JSON a modelo de dominio
      final canchas = rawList
          .whereType<Map<String, dynamic>>()
          .map((json) => CanchaMapper.fromJson(json))
          .toList();

      // ✅ Guarda en base de datos local (cache)
      await db.cachearCanchas(
        rawList.whereType<Map<String, dynamic>>().toList(),
      );

      debugPrint('✅ Canchas cargadas desde Laravel: ${canchas.length}');
      return canchas;
    } catch (e) {
      debugPrint('⚠️ Error o sin conexión. Cargando cache local: $e');

      // 🔸 Si falla la API, recupera desde base local
      final locales = await db.obtenerCanchas();

      debugPrint('📦 Canchas desde cache local: ${locales.length}');

      return locales
          .map(
            (c) => Cancha(
              id: c.id,
              nombre: c.nombre,
              tipo: c.tipo ?? 'Desconocido',
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

  /// 🔹 Obtiene una cancha específica por ID (con fallback a cache local)
  Future<Cancha?> getCanchaById(int id) async {
    try {
      final response = await http.get(
        Uri.parse('$baseUrl/canchas/$id'),
        headers: {'Accept': 'application/json'},
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final canchaJson = data is Map<String, dynamic>
            ? (data['cancha'] ?? data)
            : null;

        if (canchaJson != null && canchaJson is Map<String, dynamic>) {
          final cancha = CanchaMapper.fromJson(canchaJson);
          // 🔹 Actualiza el cache local con la cancha individual
          await db.cachearCanchas([canchaJson]);
          return cancha;
        }
      } else if (response.statusCode == 404) {
        debugPrint('⚠️ Cancha no encontrada (ID: $id)');
        return null;
      } else {
        debugPrint('❌ Error ${response.statusCode}: ${response.reasonPhrase}');
      }
    } catch (e) {
      debugPrint('⚠️ Sin conexión, buscando cancha $id en cache local');

      final locales = await db.obtenerCanchas();

      // ✅ Buscar manualmente la cancha en la cache local
      CanchaLocal? canchaLocal;
      for (final c in locales) {
        if (c.id == id) {
          canchaLocal = c;
          break;
        }
      }

      if (canchaLocal == null) {
        debugPrint('⚠️ Cancha no encontrada en cache local');
        return null;
      }

      return Cancha(
        id: canchaLocal.id,
        nombre: canchaLocal.nombre,
        tipo: canchaLocal.tipo ?? 'Desconocido',
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
