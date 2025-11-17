import 'package:flutter/material.dart';
import 'package:sportreserve_mobile_frontend/models/cancha.dart';


/// Additional presentation data for a [Cancha].
class CanchaMeta {
  const CanchaMeta({
    required this.type,
    required this.pricePerHour,
    required this.isAvailable,
    required this.primaryColor,
    required this.surface,
    required this.icon,
  });

  final String type;
  final double pricePerHour;
  final bool isAvailable;
  final Color primaryColor;
  final String surface;
  final IconData icon;

  String get availabilityLabel => isAvailable ? 'Disponible' : 'Ocupada';

  Color availabilityColor(BuildContext context) {
    if (isAvailable) {
      return const Color(0xFF43A047);
    }
    final ColorScheme scheme = Theme.of(context).colorScheme;
    return scheme.error;
  }

  Color availabilityBackground(BuildContext context) {
    final ColorScheme scheme = Theme.of(context).colorScheme;
    return isAvailable
        ? const Color(0x3343A047)
        : scheme.errorContainer.withValues(alpha: 0.35);
  }
}

final Map<String, CanchaMeta> _metadataByName = <String, CanchaMeta>{
  'Tercer Tiempo - Complejo Deportivo': const CanchaMeta(
    type: 'Fútbol 5',
    pricePerHour: 85000,
    isAvailable: true,
    primaryColor: Color(0xFF2E7D32),
    surface: 'Cesped sintetico',
    icon: Icons.sports_soccer,
  ),
  'Cancha Sintética Utrahuilca': const CanchaMeta(
    type: 'Fútbol 7',
    pricePerHour: 78000,
    isAvailable: false,
    primaryColor: Color(0xFF1565C0),
    surface: 'Cesped sintetico',
    icon: Icons.sports_soccer,
  ),
  'Club Los Lagos': const CanchaMeta(
    type: 'Tenis',
    pricePerHour: 62000,
    isAvailable: true,
    primaryColor: Color(0xFF00897B),
    surface: 'Arcilla',
    icon: Icons.sports_tennis,
  ),
};

CanchaMeta buildMetaForCancha(Cancha cancha) {
  return _metadataByName[cancha.nombre] ??
      const CanchaMeta(
        type: 'Polideportivo',
        pricePerHour: 65000,
        isAvailable: true,
        primaryColor: Color(0xFF2E7D32),
        surface: 'Mixto',
        icon: Icons.sports,
      );
}
