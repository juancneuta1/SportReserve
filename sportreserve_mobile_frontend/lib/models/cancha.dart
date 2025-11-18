import 'dart:convert';

class Cancha {
  final int id;
  final String nombre;
  final String tipo;
  final List<String> tipos;
  final String ubicacion;
  final double latitud;
  final double longitud;
  final double precioPorCancha;
  final bool disponibilidad;
  final String imagen;
  final String descripcion;
  final String servicios;
  final int capacidad;

  Cancha({
    required this.id,
    required this.nombre,
    required this.tipo,
    required this.tipos,
    required this.ubicacion,
    required this.latitud,
    required this.longitud,
    required this.precioPorCancha,
    required this.disponibilidad,
    required this.imagen,
    required this.descripcion,
    required this.servicios,
    required this.capacidad,
  });

  static int _parseInt(dynamic value) {
    if (value is int) return value;
    if (value is num) return value.toInt();
    if (value is String) return int.tryParse(value) ?? 0;
    return 0;
  }

  static double _parseDouble(dynamic value) {
    if (value is num) return value.toDouble();
    if (value is String) {
      String cleaned = value.replaceAll(RegExp(r'[^\d,.\-]'), '');
      final hasComma = cleaned.contains(',');
      // Si viene con separador de miles como "50.000" y sin coma decimal, elimino puntos.
      if (!hasComma &&
          cleaned.contains('.') &&
          cleaned.split('.').last.length == 3) {
        cleaned = cleaned.replaceAll('.', '');
      }
      cleaned = cleaned.replaceAll(',', '.');
      return double.tryParse(cleaned) ?? 0.0;
    }
    return 0.0;
  }

  factory Cancha.fromJson(Map<String, dynamic> json) {
    final dynamic precioRaw = json['precio_por_hora'] ??
        json['precio_hora'] ??
        json['precio_por_cancha'] ??
        json['precioPorHora'] ??
        json['precio'] ??
        json['tarifa'] ??
        json['price'];

    final dynamic rawTipo = json['tipo'] ?? json['tipos'];
    final parsedTipos = _parseTipos(rawTipo);
    final tipo = parsedTipos.isNotEmpty
        ? parsedTipos.first
        : (rawTipo?.toString() ?? 'Desconocido');

    return Cancha(
      id: _parseInt(json['id']),
      nombre: json['nombre']?.toString() ?? 'Cancha sin nombre',
      tipo: tipo,
      tipos: parsedTipos,
      ubicacion: json['ubicacion']?.toString() ?? 'Ubicacion no registrada',
      latitud: _parseDouble(json['latitud']),
      longitud: _parseDouble(json['longitud']),
      precioPorCancha: _parseDouble(precioRaw),
      disponibilidad: json['disponibilidad'] == true ||
          json['disponibilidad'] == 1 ||
          json['disponibilidad'] == '1' ||
          json['available'] == true,
      imagen: json['imagen']?.toString() ?? '',
      descripcion: json['descripcion']?.toString() ?? '',
      servicios: json['servicios']?.toString() ?? '',
      capacidad: _parseInt(json['capacidad']),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nombre': nombre,
      'tipo': tipo,
      'tipos': tipos,
      'ubicacion': ubicacion,
      'latitud': latitud,
      'longitud': longitud,
      'precio_por_hora': precioPorCancha,
      'precio_por_cancha': precioPorCancha,
      'precio': precioPorCancha,
      'disponibilidad': disponibilidad,
      'imagen': imagen,
      'descripcion': descripcion,
      'servicios': servicios,
      'capacidad': capacidad,
    };
  }

  List<String> get deportesDisponibles {
    if (tipos.isNotEmpty) return tipos;
    return tipo.trim().isNotEmpty ? [tipo.trim()] : <String>[];
  }

  Cancha copyWith({
    int? id,
    String? nombre,
    String? tipo,
    List<String>? tipos,
    String? ubicacion,
    double? latitud,
    double? longitud,
    double? precioPorCancha,
    bool? disponibilidad,
    String? imagen,
    String? descripcion,
    String? servicios,
    int? capacidad,
  }) {
    return Cancha(
      id: id ?? this.id,
      nombre: nombre ?? this.nombre,
      tipo: tipo ?? this.tipo,
      tipos: tipos ?? this.tipos,
      ubicacion: ubicacion ?? this.ubicacion,
      latitud: latitud ?? this.latitud,
      longitud: longitud ?? this.longitud,
      precioPorCancha: precioPorCancha ?? this.precioPorCancha,
      disponibilidad: disponibilidad ?? this.disponibilidad,
      imagen: imagen ?? this.imagen,
      descripcion: descripcion ?? this.descripcion,
      servicios: servicios ?? this.servicios,
      capacidad: capacidad ?? this.capacidad,
    );
  }

  static List<String> _parseTipos(dynamic value) {
    List<String> normalizeList(dynamic raw) => (raw as List)
        .map((e) => e?.toString().trim() ?? '')
        .where((e) => e.isNotEmpty)
        .toList();

    if (value is List) return normalizeList(value);

    if (value is String) {
      final trimmed = value.trim();
      if (trimmed.isEmpty) return <String>[];

      // Si viene como string JSON, intentar decodificarlo.
      if (trimmed.startsWith('[') && trimmed.endsWith(']')) {
        try {
          final decoded = jsonDecode(trimmed);
          if (decoded is List) return normalizeList(decoded);
        } catch (_) {
          // fallback below
        }
      }

      if (trimmed.contains(',')) {
        return trimmed
            .split(',')
            .map((e) => e.trim())
            .where((e) => e.isNotEmpty)
            .toList();
      }

      return <String>[trimmed];
    }

    return <String>[];
  }
}
