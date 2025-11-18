import 'package:flutter/material.dart';
import 'package:sportreserve_mobile_frontend/models/cancha.dart';

/// Información visual y de contexto de cada cancha
class CanchaMeta {
  final String type;
  final String surface;
  final IconData icon;
  final Color primaryColor;
  final double pricePerHour;
  final bool isAvailable;

  const CanchaMeta({
    required this.type,
    required this.surface,
    required this.icon,
    required this.primaryColor,
    required this.pricePerHour,
    required this.isAvailable,
  });

  /// 🔹 Crea un meta a partir del modelo `Cancha`
  factory CanchaMeta.buildFromModel(Cancha cancha) {
    late String type;
    late String surface;
    late IconData icon;
    late Color color;
    final deportePrincipal = cancha.deportesDisponibles.isNotEmpty
        ? cancha.deportesDisponibles.first
        : cancha.tipo;

    // Detectar tipo de cancha
    switch (deportePrincipal.toLowerCase()) {
      case 'fútbol':
      case 'futbol':
        type = 'Fútbol';
        surface = 'Césped sintético';
        icon = Icons.sports_soccer;
        color = Colors.green.shade700;
        break;
      case 'baloncesto':
        type = 'Baloncesto';
        surface = 'Parqué';
        icon = Icons.sports_basketball;
        color = Colors.orange.shade800;
        break;
      case 'voleibol':
        type = 'Voleibol';
        surface = 'Arena';
        icon = Icons.sports_volleyball;
        color = Colors.yellow.shade700;
        break;
      case 'tenis':
        type = 'Tenis';
        surface = 'Cemento';
        icon = Icons.sports_tennis;
        color = Colors.blueAccent;
        break;
      default:
        type = 'Cancha genérica';
        surface = 'Mixta';
        icon = Icons.sports;
        color = Colors.blueGrey;
    }

    // ⚙️ Determinar disponibilidad:
    // Si tu backend no tiene ese campo, se marca como disponible por defecto.
    bool disponible = true;

    // Si tu modelo Cancha tiene algún campo similar (por ejemplo 'status' o 'ocupada'),
    // puedes reemplazar la línea anterior por algo como:
    // bool disponible = cancha.status == 'disponible';

    return CanchaMeta(
      type: type,
      surface: surface,
      icon: icon,
      primaryColor: color,
      pricePerHour: cancha.precioPorCancha,
      isAvailable: disponible,
    );
  }

  /// Alias para compatibilidad
  static CanchaMeta fromCancha(Cancha cancha) => CanchaMeta.buildFromModel(cancha);

  /// Estilos auxiliares
  static Color availabilityColor(bool available) =>
      available ? Colors.green : Colors.red;

  static Color availabilityBackground(bool available) =>
      available
          ? Colors.green.withValues(alpha: 0.15)
          : Colors.red.withValues(alpha: 0.15);

  static String availabilityLabel(bool available) =>
      available ? 'Disponible' : 'Ocupada';
}
