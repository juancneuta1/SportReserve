class Calificacion {
  const Calificacion({
    required this.id,
    required this.canchaId,
    required this.userId,
    required this.estrellas,
    this.comentario,
    this.usuarioNombre,
    this.fecha,
  });

  final int id;
  final int canchaId;
  final int userId;
  final int estrellas;
  final String? comentario;
  final String? usuarioNombre;
  final String? fecha;

  factory Calificacion.fromJson(Map<String, dynamic> json) {
    int parseIntValue(dynamic value) {
      if (value is int) return value;
      if (value is String) return int.tryParse(value) ?? 0;
      if (value is double) return value.round();
      return 0;
    }

    String? parseStringValue(dynamic value) {
      if (value == null) return null;
      return value.toString();
    }

    Map<String, dynamic>? asMap(dynamic value) {
      if (value is Map<String, dynamic>) return value;
      return null;
    }

    final userMap = asMap(json['user']);

    return Calificacion(
      id: parseIntValue(json['id']),
      canchaId: parseIntValue(json['cancha_id'] ?? json['canchaId']),
      userId: parseIntValue(
        json['user_id'] ?? json['userId'] ?? userMap?['id'],
      ),
      estrellas: parseIntValue(json['estrellas'] ?? json['rating']),
      comentario: parseStringValue(json['comentario'] ?? json['comment']),
      usuarioNombre: parseStringValue(
        json['usuario_nombre'] ??
            json['usuarioNombre'] ??
            json['user_name'] ??
            userMap?['name'] ??
            userMap?['nombre'],
      ),
      fecha: parseStringValue(
        json['fecha'] ??
            json['created_at'] ??
            json['updated_at'] ??
            userMap?['created_at'],
      ),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'cancha_id': canchaId,
      'user_id': userId,
      'estrellas': estrellas,
      if (comentario != null) 'comentario': comentario,
      if (usuarioNombre != null) 'usuario_nombre': usuarioNombre,
      if (fecha != null) 'fecha': fecha,
    };
  }
}
