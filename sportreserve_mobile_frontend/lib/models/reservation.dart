/// Modelo que representa una reserva proveniente del backend Laravel.
class Reservation {
  Reservation({
    required this.id,
    required this.canchaId,
    required this.userId,
    required this.startAt,
    required this.endAt,
    required this.status,
    required this.createdAt,
  });

  final int id;
  final int canchaId;
  final int userId;
  final DateTime startAt;
  final DateTime endAt;
  final String status;
  final DateTime createdAt;

  /// Calcula la duracion de la reserva para facilitar validaciones en UI.
  Duration get duration => endAt.difference(startAt);

  /// Crea una instancia a partir de la respuesta JSON del backend.
  factory Reservation.fromJson(Map<String, dynamic> json) {
    DateTime parseDate(dynamic value) {
      if (value is DateTime) return value;
      if (value is String && value.isNotEmpty) {
        return DateTime.parse(value).toLocal();
      }
      throw const FormatException('Fecha invalida en la reserva');
    }

    int parseInt(dynamic value) {
      if (value is int) return value;
      if (value is String && value.isNotEmpty) {
        return int.tryParse(value) ??
            (throw const FormatException('Identificador invalido'));
      }
      throw const FormatException('Identificador invalido');
    }

    return Reservation(
      id: parseInt(json['id']),
      canchaId: parseInt(json['cancha_id'] ?? json['canchaId']),
      userId: parseInt(json['user_id'] ?? json['userId']),
      startAt: parseDate(json['start_at'] ?? json['startAt']),
      endAt: parseDate(json['end_at'] ?? json['endAt']),
      status: (json['status'] as String?)?.trim() ?? 'pending',
      createdAt: parseDate(json['created_at'] ?? json['createdAt']),
    );
  }

  /// Serializa la reserva para enviarla al backend.
  Map<String, dynamic> toJson() {
    String encodeDate(DateTime value) => value.toUtc().toIso8601String();
    return <String, dynamic>{
      'id': id,
      'cancha_id': canchaId,
      'user_id': userId,
      'start_at': encodeDate(startAt),
      'end_at': encodeDate(endAt),
      'status': status,
      'created_at': encodeDate(createdAt),
    };
  }
}
