import '../models/cancha.dart' as model;

/// 🔹 Mapper encargado de convertir los datos JSON del backend a modelo de dominio.
class CanchaMapper {
  /// Convierte el JSON recibido del backend en un objeto `Cancha`
  static model.Cancha fromJson(Map<String, dynamic> json) {
    return model.Cancha(
      id: json['id'] is String
          ? int.tryParse(json['id']) ?? 0
          : json['id'] ?? 0,
      nombre: json['nombre'] ?? 'Cancha sin nombre',
      tipo: json['tipo'] ?? 'Desconocido',
      ubicacion: json['ubicacion'] ?? 'Ubicación no registrada',
      capacidad: json['capacidad'] ?? 0,
      precioPorCancha:
          (json['precio_por_hora'] ??
                  json['precio'] ??
                  json['precioPorCancha'] ??
                  0)
              .toDouble(),
      latitud: (json['latitud'] is String)
          ? double.tryParse(json['latitud']) ?? 0.0
          : (json['latitud'] ?? 0.0).toDouble(),
      longitud: (json['longitud'] is String)
          ? double.tryParse(json['longitud']) ?? 0.0
          : (json['longitud'] ?? 0.0).toDouble(),
      disponibilidad:
          json['disponibilidad'] == true ||
          json['disponibilidad'] == 1 ||
          json['disponibilidad'] == '1',
      imagen: json['imagen'] ?? '',
      descripcion: json['descripcion'] ?? '',
      servicios: json['servicios'] ?? '',
    );
  }

  /// Convierte una lista de JSON a una lista de objetos `Cancha`
  static List<model.Cancha> fromJsonList(List<dynamic> list) {
    return list.map((json) => fromJson(json)).toList();
  }
}
