/// Resultado del flujo de pago luego de cerrar la pasarela.
class PasarelaPaymentResult {
  const PasarelaPaymentResult(this.status, {this.redirectUrl});

  final PasarelaPaymentStatus status;
  final String? redirectUrl;
}

enum PasarelaPaymentStatus { success, failure, pending }
