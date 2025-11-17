// lib/models/reservation_slot.dart
class ReservationSlot {
  final DateTime startAt;
  final DateTime endAt;
  final bool isAvailable;

  ReservationSlot({
    required this.startAt,
    required this.endAt,
    required this.isAvailable,
  });

  factory ReservationSlot.fromJson(Map<String, dynamic> j) {
    return ReservationSlot(
      startAt: DateTime.parse(j['start_at']),
      endAt: DateTime.parse(j['end_at']),
      isAvailable: j['is_available'] == true,
    );
  }
}
