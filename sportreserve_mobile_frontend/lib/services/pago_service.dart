import 'dart:convert';

import 'package:http/http.dart' as http;

import 'auth_service.dart';

class PagoService {
  PagoService({http.Client? client}) : _client = client ?? http.Client();

  final http.Client _client;
  final String baseUrl = "http://10.0.2.2:8000/api";

  Future<Map<String, dynamic>> obtenerEstadoPago(int reservaId) async {
    final headers = await AuthService.instance.authenticatedJsonHeaders();
    final response = await _client.get(
      Uri.parse('$baseUrl/reservas/$reservaId/estado-pago'),
      headers: headers,
    );

    if (response.statusCode != 200) {
      throw Exception(
        'Error al consultar el estado del pago (${response.statusCode}).',
      );
    }

    final decoded = jsonDecode(response.body);
    if (decoded is Map<String, dynamic>) {
      return decoded;
    }

    return {'raw': decoded};
  }
}
