import '../models/cancha.dart' as model;

/// Mapper encargado de convertir los datos JSON del backend a modelo de dominio.
class CanchaMapper {
  /// Convierte el JSON recibido del backend en un objeto `Cancha`.
  static model.Cancha fromJson(Map<String, dynamic> json) {
    // Reutilizamos la lógica robusta del modelo para soportar todas las variantes de nombres.
    return model.Cancha.fromJson(json);
  }

  /// Convierte una lista de JSON a una lista de objetos `Cancha`.
  static List<model.Cancha> fromJsonList(List<dynamic> list) {
    return list
        .whereType<Map<String, dynamic>>()
        .map(fromJson)
        .toList();
  }
}
