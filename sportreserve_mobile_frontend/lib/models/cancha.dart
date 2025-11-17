class Cancha {
  final int id;
  final String nombre;
  final String tipo;
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

  /// 🔹 Crea una instancia de [Cancha] a partir del JSON del backend Laravel
  factory Cancha.fromJson(Map<String, dynamic> json) {
    return Cancha(
      id: json['id'] is String
          ? int.tryParse(json['id']) ?? 0
          : (json['id'] ?? 0),
      nombre: json['nombre'] ?? 'Cancha sin nombre',
      tipo: json['tipo'] ?? 'Desconocido',
      ubicacion: json['ubicacion'] ?? 'Ubicación no registrada',
      latitud: (json['latitud'] is String)
          ? double.tryParse(json['latitud']) ?? 0.0
          : (json['latitud'] is num
                ? (json['latitud'] as num).toDouble()
                : 0.0),
      longitud: (json['longitud'] is String)
          ? double.tryParse(json['longitud']) ?? 0.0
          : (json['longitud'] is num
                ? (json['longitud'] as num).toDouble()
                : 0.0),
      precioPorCancha:
          (json['precio_por_hora'] ??
                  json['precio_por_cancha'] ??
                  json['precio'] ??
                  0)
              is String
          ? double.tryParse(
                  json['precio_por_hora'] ??
                      json['precio_por_cancha'] ??
                      json['precio'],
                )?.toDouble() ??
                0.0
          : (json['precio_por_hora'] ??
                    json['precio_por_cancha'] ??
                    json['precio'] ??
                    0)
                .toDouble(),
      disponibilidad:
          json['disponibilidad'] == true ||
          json['disponibilidad'] == 1 ||
          json['disponibilidad'] == '1',
      imagen: json['imagen'] ?? '',
      descripcion: json['descripcion'] ?? '',
      servicios: json['servicios'] ?? '',
      capacidad: json['capacidad'] is String
          ? int.tryParse(json['capacidad']) ?? 0
          : (json['capacidad'] ?? 0),
    );
  }

  /// 🔹 Convierte la cancha a un JSON (para enviar o guardar)
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'nombre': nombre,
      'tipo': tipo,
      'ubicacion': ubicacion,
      'latitud': latitud,
      'longitud': longitud,
      'precio_por_cancha': precioPorCancha,
      'disponibilidad': disponibilidad,
      'imagen': imagen,
      'descripcion': descripcion,
      'servicios': servicios,
      'capacidad': capacidad,
    };
  }

  Cancha copyWith({
    int? id,
    String? nombre,
    String? tipo,
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
}
