import 'dart:async';

import 'package:flutter/material.dart';
import 'package:sportreserve_mobile_frontend/db/app_database.dart';
import 'package:sportreserve_mobile_frontend/features/canchas/cancha_detail_page.dart';
import 'package:sportreserve_mobile_frontend/features/canchas/mapa_canchas_page.dart';
import 'package:sportreserve_mobile_frontend/features/profile/user_profile_page.dart';
import 'package:sportreserve_mobile_frontend/models/.review_summary.dart';
import 'package:sportreserve_mobile_frontend/models/cancha.dart';
import 'package:sportreserve_mobile_frontend/models/cancha_meta.dart';
import 'package:sportreserve_mobile_frontend/services/availability_realtime_service.dart';
import 'package:sportreserve_mobile_frontend/services/cancha_service.dart';
import 'package:sportreserve_mobile_frontend/services/review_service.dart';

class CanchasPage extends StatefulWidget {
  const CanchasPage({super.key});

  @override
  State<CanchasPage> createState() => _CanchasPageState();
}

class _CanchasPageState extends State<CanchasPage> {
  late Future<List<Cancha>> _futureCanchas;
  List<Cancha>? _canchas;
  StreamSubscription<AvailabilityUpdate>? _availabilitySubscription;

  @override
  void initState() {
    super.initState();

    final db = AppDatabase();
    final service = CanchaService(db);
    _futureCanchas = service.obtenerCanchas();
    _futureCanchas.then((value) {
      if (!mounted) return;
      setState(() => _canchas = value);
    });

    final realtime = AvailabilityRealtimeService.instance;
    realtime.ensureConnected();
    _availabilitySubscription = realtime.updates.listen(_applyAvailability);
  }

  @override
  void dispose() {
    _availabilitySubscription?.cancel();
    super.dispose();
  }

  void _applyAvailability(AvailabilityUpdate update) {
    final current = _canchas;
    if (current == null || current.isEmpty) return;

    final index = current.indexWhere((c) => c.id == update.canchaId);
    if (index == -1) return;

    final mutable = List<Cancha>.from(current);
    mutable[index] = mutable[index].copyWith(disponibilidad: update.available);

    if (!mounted) return;
    setState(() => _canchas = mutable);
  }

  void _openDetalle(Cancha cancha) {
    final meta = CanchaMeta.fromCancha(cancha);
    Navigator.of(context).push(
      PageRouteBuilder<void>(
        transitionDuration: const Duration(milliseconds: 420),
        pageBuilder: (_, animation, __) => FadeTransition(
          opacity: animation,
          child: CanchaDetailPage(cancha: cancha, meta: meta),
        ),
        transitionsBuilder: (_, animation, __, child) {
          return FadeTransition(
            opacity: CurvedAnimation(
              parent: animation,
              curve: Curves.easeOutCubic,
              reverseCurve: Curves.easeInBack,
            ),
            child: child,
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final ColorScheme scheme = Theme.of(context).colorScheme;
    return Scaffold(
      appBar: AppBar(
        title: const Text('Reserva tu cancha'),
        actions: [
          IconButton(
            icon: const Icon(Icons.person_outline),
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute<void>(
                  builder: (_) => const UserProfilePage(),
                ),
              );
            },
          ),
          IconButton(
            icon: const Icon(Icons.map_outlined),
            onPressed: () {
              Navigator.of(context).push(
                MaterialPageRoute<void>(
                  builder: (_) => const MapaCanchasPage(),
                ),
              );
            },
          ),
        ],
      ),
      body: FutureBuilder<List<Cancha>>(
        future: _futureCanchas,
        builder: (context, snapshot) {
          if (snapshot.connectionState != ConnectionState.done) {
            return Center(
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(scheme.primary),
              ),
            );
          }
          if (snapshot.hasError) {
            return Center(
              child: Text(
                'Error: ${snapshot.error}',
                style: Theme.of(context).textTheme.titleMedium,
              ),
            );
          }
          final canchas = _canchas ?? snapshot.data ?? [];
          if (canchas.isEmpty) {
            return Center(
              child: Text(
                'No hay canchas registradas por ahora.',
                style: Theme.of(context).textTheme.titleMedium,
              ),
            );
          }
          return Padding(
            padding: const EdgeInsets.fromLTRB(20, 20, 20, 0),
            child: ListView.separated(
              padding: const EdgeInsets.only(bottom: 36),
              itemCount: canchas.length,
              separatorBuilder: (_, __) => const SizedBox(height: 18),
              itemBuilder: (context, index) {
                final cancha = canchas[index];
                final meta = CanchaMeta.fromCancha(cancha);
                return _CanchaCard(
                  cancha: cancha,
                  meta: meta,
                  onReserve: () => _openDetalle(cancha),
                  onTap: () => _openDetalle(cancha),
                );
              },
            ),
          );
        },
      ),
    );
  }
}

class _CanchaCard extends StatelessWidget {
  const _CanchaCard({
    required this.cancha,
    required this.meta,
    required this.onReserve,
    required this.onTap,
  });
  final Cancha cancha;
  final CanchaMeta meta;
  final VoidCallback onReserve;
  final VoidCallback onTap;
  @override
  Widget build(BuildContext context) {
    final ColorScheme scheme = Theme.of(context).colorScheme;
    final TextTheme textTheme = Theme.of(context).textTheme;
    return GestureDetector(
      onTap: onTap,
      child: Card(
        clipBehavior: Clip.antiAlias,
        elevation: 10,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(26)),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Stack(
              children: [
                Hero(
                  tag: 'cancha_image_${cancha.id}',
                  child: Container(
                    height: 180,
                    decoration: BoxDecoration(
                      gradient: LinearGradient(
                        colors: [
                          meta.primaryColor.withValues(alpha: 0.95),
                          meta.primaryColor.withValues(alpha: 0.55),
                        ],
                        begin: Alignment.topLeft,
                        end: Alignment.bottomRight,
                      ),
                    ),
                    child: Align(
                      alignment: Alignment.center,
                      child: Icon(
                        meta.icon,
                        color: Colors.white.withValues(alpha: 0.2),
                        size: 140,
                      ),
                    ),
                  ),
                ),
                Positioned(
                  left: 16,
                  top: 16,
                  child: _AvailabilityChip(meta: meta),
                ),
                Positioned(
                  right: 16,
                  top: 16,
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 8,
                    ),
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(18),
                      color: Colors.white.withValues(alpha: 0.85),
                    ),
                    child: Row(
                      children: [
                        Icon(Icons.sports, color: scheme.primary, size: 16),
                        const SizedBox(width: 6),
                        Text(
                          meta.type,
                          style: textTheme.labelLarge?.copyWith(
                            fontWeight: FontWeight.w600,
                            color: scheme.primary,
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
            Padding(
              padding: const EdgeInsets.fromLTRB(20, 18, 20, 20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    cancha.nombre,
                    style: textTheme.titleLarge?.copyWith(
                      fontWeight: FontWeight.w700,
                    ),
                  ),
                  const SizedBox(height: 6),
                  StreamBuilder<ReviewSummary>(
                    stream: ReviewService.instance.watchSummary(cancha.id),
                    builder: (context, snapshot) {
                      final summary =
                          snapshot.data ??
                          ReviewSummary(
                            canchaId: cancha.id,
                            average: 0,
                            total: 0,
                          );
                      return _RatingSummary(summary: summary);
                    },
                  ),
                  const SizedBox(height: 6),
                  Row(
                    children: [
                      Icon(
                        Icons.place_outlined,
                        size: 18,
                        color: scheme.onSurfaceVariant,
                      ),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          cancha.ubicacion,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: textTheme.bodySmall?.copyWith(
                            color: scheme.onSurfaceVariant,
                          ),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 12,
                          vertical: 6,
                        ),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(16),
                          color: scheme.primaryContainer.withValues(alpha: 0.5),
                        ),
                        child: Row(
                          children: [
                            const Icon(Icons.timelapse, size: 16),
                            const SizedBox(width: 6),
                            Text(
                              '1h',
                              style: textTheme.labelLarge?.copyWith(
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(width: 10),
                      Text(
                        '\$${meta.pricePerHour.toStringAsFixed(0)} COP',
                        style: textTheme.titleMedium?.copyWith(
                          fontWeight: FontWeight.bold,
                          color: scheme.primary,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 18),
                  Row(
                    children: [
                      Expanded(
                        child: FilledButton(
                          onPressed: onReserve,
                          style: FilledButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(18),
                            ),
                          ),
                          child: Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              const Icon(Icons.calendar_today_rounded),
                              const SizedBox(width: 10),
                              Text(
                                'Reservar',
                                style: textTheme.titleMedium?.copyWith(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      IconButton.filledTonal(
                        onPressed: onTap,
                        icon: const Icon(Icons.info_outline),
                        style: IconButton.styleFrom(
                          padding: const EdgeInsets.all(16),
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _AvailabilityChip extends StatelessWidget {
  const _AvailabilityChip({required this.meta});
  final CanchaMeta meta;
  @override
  Widget build(BuildContext context) {
    final Color color = meta.primaryColor;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: meta.primaryColor.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(18),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.fiber_manual_record, size: 12, color: color),
          const SizedBox(width: 6),
          Text(
            'Disponible',
            style: Theme.of(context).textTheme.labelMedium?.copyWith(
              color: color,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

class _RatingSummary extends StatelessWidget {
  const _RatingSummary({required this.summary});
  final ReviewSummary summary;
  @override
  Widget build(BuildContext context) {
    final ColorScheme scheme = Theme.of(context).colorScheme;
    final TextStyle? textStyle = Theme.of(
      context,
    ).textTheme.bodySmall?.copyWith(color: scheme.onSurfaceVariant);
    return Row(
      children: [
        Icon(Icons.star_rounded, color: Colors.amber.shade600, size: 18),
        const SizedBox(width: 4),
        Text(
          summary.average.toStringAsFixed(1),
          style: textStyle?.copyWith(
            fontWeight: FontWeight.w700,
            color: scheme.onSurface,
          ),
        ),
        const SizedBox(width: 6),
        Text('(${summary.total})', style: textStyle),
      ],
    );
  }
}
