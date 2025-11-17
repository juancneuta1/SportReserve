import 'dart:async';

/// Eventos globales relacionados con reservas y pagos para sincronizar pantallas.
class ReservationEvent {
  ReservationEvent.reservasRefrescadas({required this.payload})
    : type = ReservationEventType.reservasRefrescadas;

  ReservationEvent.pagoCompletado({this.payload})
    : type = ReservationEventType.pagoCompletado;

  ReservationEventType type;
  dynamic payload;
}

enum ReservationEventType { reservasRefrescadas, pagoCompletado }

class ReservationEventsBus {
  ReservationEventsBus._();
  static final ReservationEventsBus instance = ReservationEventsBus._();

  final StreamController<ReservationEvent> _controller =
      StreamController<ReservationEvent>.broadcast();

  Stream<ReservationEvent> get stream => _controller.stream;

  void emit(ReservationEvent event) {
    if (!_controller.isClosed) {
      _controller.add(event);
    }
  }

  void dispose() {
    _controller.close();
  }
}
