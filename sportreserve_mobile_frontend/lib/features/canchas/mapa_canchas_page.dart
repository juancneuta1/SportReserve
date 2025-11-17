import 'package:flutter/material.dart';
import 'package:flutter_map/flutter_map.dart';
import 'package:go_router/go_router.dart';
import 'package:latlong2/latlong.dart';

import 'package:sportreserve_mobile_frontend/db/app_database.dart';
import 'package:sportreserve_mobile_frontend/features/canchas/cancha_detail_page.dart';
import 'package:sportreserve_mobile_frontend/models/cancha.dart' as model;
import 'package:sportreserve_mobile_frontend/models/cancha_meta.dart';
import 'package:sportreserve_mobile_frontend/models/user_profile_laravel.dart';
import 'package:sportreserve_mobile_frontend/services/auth_service.dart';
import 'package:sportreserve_mobile_frontend/services/cancha_service.dart';

class MapaCanchasPage extends StatefulWidget {
  const MapaCanchasPage({super.key});

  @override
  State<MapaCanchasPage> createState() => _MapaCanchasPageState();
}

class _MapaCanchasPageState extends State<MapaCanchasPage> {
  final MapController _mapController = MapController();
  final LatLng _neivaCenter = const LatLng(2.9360, -75.2895);

  List<model.Cancha> _canchas = [];
  model.Cancha? _selectedCancha;
  bool _loading = true;
  double _zoom = 14.5;

  static const double sheetHeight = 230;
  UserProfileLaravel? _currentUser;

  @override
  void initState() {
    super.initState();
    _loadCanchas();
    _loadUserProfile();
  }

  Future<void> _loadUserProfile() async {
    try {
      final user = await AuthService.instance.getProfile();
      if (mounted) setState(() => _currentUser = user);
    } catch (e) {
      debugPrint('Error al cargar perfil: $e');
    }
  }

  Future<void> _loadCanchas() async {
    try {
      final db = AppDatabase();
      final data = await CanchaService(db).obtenerCanchas();
      if (mounted) {
        setState(() {
          _canchas = data;
          _loading = false;
        });
      }
    } catch (e) {
      debugPrint('Error al cargar canchas: $e');
      if (mounted) setState(() => _loading = false);
    }
  }

  void _handleMarkerTap(model.Cancha cancha) {
    setState(() => _selectedCancha = cancha);
    _mapController.move(LatLng(cancha.latitud, cancha.longitud), 16);
  }

  void _closeSheet() => setState(() => _selectedCancha = null);

  Future<void> _openDetalle(model.Cancha cancha) async {
    final meta = CanchaMeta.buildFromModel(cancha);
    if (!mounted) return;
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (_) => CanchaDetailPage(cancha: cancha, meta: meta),
      ),
    );
  }

  void _changeZoom(double delta) {
    _zoom = (_zoom + delta).clamp(10, 18);
    _mapController.move(_mapController.camera.center, _zoom);
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;

    return Scaffold(
      appBar: AppBar(
        title: const Text('Mapa de canchas'),
        actions: [
          IconButton(
            icon: const Icon(Icons.account_circle_rounded, size: 30),
            onPressed: () {
              final user = _currentUser ?? AuthService.instance.currentUser;
              if (user == null) {
                context.push('/login');
              } else {
                context.push('/profile');
              }
            },
          ),
        ],
      ),
      body: _loading
          ? Center(
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(scheme.primary),
              ),
            )
          : Stack(
              children: [
                FlutterMap(
                  mapController: _mapController,
                  options: MapOptions(
                    initialCenter: _neivaCenter,
                    initialZoom: _zoom,
                    onTap: (_, __) => _closeSheet(),
                  ),
                  children: [
                    TileLayer(
                      urlTemplate:
                          'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
                      subdomains: const ['a', 'b', 'c', 'd'],
                      userAgentPackageName: 'com.sportreserve.mobile',
                    ),
                    MarkerLayer(
                      markers: _canchas
                          .map(
                            (cancha) => Marker(
                              point: LatLng(cancha.latitud, cancha.longitud),
                              width: 60,
                              height: 60,
                              child: GestureDetector(
                                onTap: () => _handleMarkerTap(cancha),
                                child: _FacilityMarker(
                                  meta: CanchaMeta.buildFromModel(cancha),
                                  isSelected: cancha.id == _selectedCancha?.id,
                                ),
                              ),
                            ),
                          )
                          .toList(),
                    ),
                  ],
                ),
                if (_selectedCancha != null)
                  AnimatedPositioned(
                    duration: const Duration(milliseconds: 300),
                    curve: Curves.easeOutCubic,
                    left: 0,
                    right: 0,
                    bottom: 0,
                    child: _SelectedCanchaSheet(
                      cancha: _selectedCancha!,
                      meta: CanchaMeta.buildFromModel(_selectedCancha!),
                      onClose: _closeSheet,
                      onReserve: () => _openDetalle(_selectedCancha!),
                    ),
                  ),
                Positioned(
                  right: 16,
                  bottom: (_selectedCancha != null ? sheetHeight : 0) + 24,
                  child: Column(
                    children: [
                      FloatingActionButton(
                        heroTag: 'zoom_in',
                        mini: true,
                        backgroundColor: Colors.white,
                        onPressed: () => _changeZoom(0.5),
                        child: const Icon(Icons.add, color: Colors.black),
                      ),
                      const SizedBox(height: 8),
                      FloatingActionButton(
                        heroTag: 'zoom_out',
                        mini: true,
                        backgroundColor: Colors.white,
                        onPressed: () => _changeZoom(-0.5),
                        child: const Icon(Icons.remove, color: Colors.black),
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }
}

class _FacilityMarker extends StatelessWidget {
  const _FacilityMarker({required this.meta, required this.isSelected});
  final CanchaMeta meta;
  final bool isSelected;

  @override
  Widget build(BuildContext context) {
    final color = meta.primaryColor;
    return AnimatedContainer(
      duration: const Duration(milliseconds: 200),
      decoration: BoxDecoration(
        color: color.withValues(alpha: isSelected ? 0.95 : 0.75),
        shape: BoxShape.circle,
        boxShadow: [
          BoxShadow(
            color: color.withValues(alpha: 0.4),
            blurRadius: isSelected ? 14 : 8,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      padding: const EdgeInsets.all(10),
      child: Icon(meta.icon, color: Colors.white, size: isSelected ? 32 : 26),
    );
  }
}

class _SelectedCanchaSheet extends StatelessWidget {
  const _SelectedCanchaSheet({
    required this.cancha,
    required this.meta,
    required this.onClose,
    required this.onReserve,
  });

  final model.Cancha cancha;
  final CanchaMeta meta;
  final VoidCallback onClose;
  final VoidCallback onReserve;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final textTheme = Theme.of(context).textTheme;
    final servicios = cancha.servicios.isNotEmpty
        ? cancha.servicios.split(',').map((s) => s.trim()).where((s) => s.isNotEmpty).toList()
        : <String>[];

    return Material(
      elevation: 20,
      shadowColor: Colors.black.withValues(alpha: 0.2),
      borderRadius: const BorderRadius.vertical(top: Radius.circular(28)),
      color: Colors.white.withValues(alpha: 0.96),
      child: Padding(
        padding: EdgeInsets.fromLTRB(24, 18, 24, 16 + MediaQuery.of(context).padding.bottom),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  decoration: BoxDecoration(
                    color: scheme.primary.withValues(alpha: 0.15),
                    shape: BoxShape.circle,
                  ),
                  padding: const EdgeInsets.all(10),
                  child: Icon(meta.icon, color: scheme.primary, size: 28),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Text(
                    cancha.nombre,
                    style: textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: scheme.primary,
                    ),
                  ),
                ),
                IconButton(
                  onPressed: onClose,
                  icon: const Icon(Icons.close_rounded, color: Colors.grey),
                ),
              ],
            ),
            const SizedBox(height: 8),
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Icon(Icons.location_on_outlined, color: Colors.grey, size: 20),
                const SizedBox(width: 6),
                Expanded(
                  child: Text(
                    cancha.ubicacion.isNotEmpty ? cancha.ubicacion : 'Ubicación no disponible',
                    style: textTheme.bodyMedium?.copyWith(color: Colors.grey[700]),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Tarifa por hora', style: textTheme.bodySmall?.copyWith(color: Colors.grey[600])),
                    const SizedBox(height: 2),
                    Text(
                      'COP ${meta.pricePerHour.toStringAsFixed(0)}',
                      style: textTheme.titleLarge?.copyWith(
                        color: Colors.green[800],
                        fontWeight: FontWeight.w700,
                      ),
                    ),
                  ],
                ),
                FilledButton.icon(
                  onPressed: onReserve,
                  style: FilledButton.styleFrom(
                    backgroundColor: Colors.green[700],
                    padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                  ),
                  icon: const Icon(Icons.sports_soccer_rounded, color: Colors.white),
                  label: const Text('Ver detalles', style: TextStyle(color: Colors.white)),
                ),
              ],
            ),
            if (servicios.isNotEmpty) ...[
              const SizedBox(height: 12),
              Text('Servicios', style: textTheme.labelLarge),
              const SizedBox(height: 8),
              Wrap(
                spacing: 10,
                runSpacing: 8,
                children: servicios.map((s) => _ServiceChip(label: s)).toList(),
              ),
            ],
          ],
        ),
      ),
    );
  }
}

class _ServiceChip extends StatelessWidget {
  const _ServiceChip({required this.label});
  final String label;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: scheme.surfaceContainerHighest.withValues(alpha: 0.4),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: scheme.primary.withValues(alpha: 0.2)),
      ),
      child: Text(label, style: Theme.of(context).textTheme.labelLarge),
    );
  }
}

