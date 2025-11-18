import 'dart:async';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:sportreserve_mobile_frontend/services/pago_service.dart';
import 'package:sportreserve_mobile_frontend/services/reservation_events_bus.dart';
import 'package:sportreserve_mobile_frontend/services/reservation_service.dart';

class MisReservasPage extends StatefulWidget {
  const MisReservasPage({super.key});

  @override
  State<MisReservasPage> createState() => _MisReservasPageState();
}

class _MisReservasPageState extends State<MisReservasPage> {
  final _reservationService = ReservationService();
  final _pagoService = PagoService();

  final Duration _refreshInterval = const Duration(seconds: 25);
  Timer? _autoRefreshTimer;
  StreamSubscription<ReservationEvent>? _eventsSubscription;

  List<Map<String, dynamic>> _reservas = <Map<String, dynamic>>[];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadReservas();
    _startAutoRefresh();
    _eventsSubscription = ReservationEventsBus.instance.stream.listen((event) {
      if (!mounted) return;
      if (event.type == ReservationEventType.reservasRefrescadas ||
          event.type == ReservationEventType.pagoCompletado) {
        _loadReservas(silent: true);
      }
    });
  }

  void _startAutoRefresh() {
    _autoRefreshTimer?.cancel();
    _autoRefreshTimer = Timer.periodic(_refreshInterval, (_) {
      _loadReservas(silent: true);
    });
  }

  Future<void> _loadReservas({bool silent = false}) async {
    if (!silent) {
      setState(() {
        _loading = true;
        _error = null;
      });
    }

    try {
      final dynamic result = await _reservationService.obtenerMisReservas();
      var parsed = _normalizeReservas(result);
      parsed = await _sincronizarEstados(parsed);

      if (!mounted) return;
      setState(() {
        _reservas = parsed;
        _loading = false;
      });

      // ✅ Mostrar alerta si hay pagos aprobados
      final messenger = ScaffoldMessenger.of(context);
      for (var reserva in parsed) {
        if (reserva['payment_status'] == 'approved' ||
            reserva['estado_pago'] == 'approved' ||
            reserva['estado_pago'] == 'aprobado') {
          Future.microtask(() {
            if (!mounted) return;
            messenger.showSnackBar(
              SnackBar(
                content: Text(
                  '✅ Tu pago de la reserva en ${reserva['cancha']?['nombre'] ?? 'una cancha'} ha sido autorizado.',
                  style: const TextStyle(fontWeight: FontWeight.bold),
                ),
                backgroundColor: Colors.green.shade700,
                behavior: SnackBarBehavior.floating,
                duration: const Duration(seconds: 4),
              ),
            );
          });
        }
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _error = e.toString();
        _loading = false;
      });
    }
  }

  List<Map<String, dynamic>> _normalizeReservas(dynamic source) {
    final List<Map<String, dynamic>> list = [];

    void addList(dynamic value) {
      if (value is List) {
        list.addAll(
          value.whereType<Map>().map((e) => Map<String, dynamic>.from(e)),
        );
      }
    }

    if (source is List) {
      addList(source);
      return list;
    }

    if (source is Map<String, dynamic>) {
      if (source['success'] == false) {
        throw Exception(source['message'] ?? 'Error al cargar reservas');
      }
      if (source['reservas'] is List) {
        addList(source['reservas']);
      } else if (source['data'] is List) {
        addList(source['data']);
      } else if (source['items'] is List) {
        addList(source['items']);
      } else if (source['reserva'] is Map<String, dynamic>) {
        list.add(Map<String, dynamic>.from(source['reserva']));
      }
    }

    return list;
  }

  Future<List<Map<String, dynamic>>> _sincronizarEstados(
    List<Map<String, dynamic>> reservas,
  ) async {
    return Future.wait(
      reservas.map((reserva) async {
        final id = (reserva['id'] ?? reserva['reserva_id']) as int?;
        if (id == null) return reserva;
        try {
          final estado = await _pagoService.obtenerEstadoPago(id);
          return {
            ...reserva,
            'estado_pago':
                estado['estado_pago'] ??
                estado['status'] ??
                reserva['estado_pago'],
            'estado':
                estado['estado_reserva'] ??
                estado['estado'] ??
                reserva['estado'],
            'ultimo_evento_pago':
                estado['updated_at'] ??
                estado['actualizado_en'] ??
                reserva['ultimo_evento_pago'],
          };
        } catch (_) {
          return reserva;
        }
      }),
    );
  }

  Future<void> _actualizarPagoIndividual(int reservaId) async {
    try {
      final estado = await _pagoService.obtenerEstadoPago(reservaId);
      if (!mounted) return;
      setState(() {
        _reservas = _reservas.map((reserva) {
          final id = (reserva['id'] ?? reserva['reserva_id']) as int?;
          if (id == reservaId) {
            return {
              ...reserva,
              'estado_pago':
                  estado['estado_pago'] ??
                  estado['status'] ??
                  reserva['estado_pago'],
              'estado':
                  estado['estado_reserva'] ??
                  estado['estado'] ??
                  reserva['estado'],
            };
          }
          return reserva;
        }).toList();
      });
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Estado de pago actualizado.')),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            'No se pudo actualizar el pago: '
            '${e.toString().replaceFirst('Exception: ', '')}',
          ),
        ),
      );
    }
  }

  @override
  void dispose() {
    _autoRefreshTimer?.cancel();
    _eventsSubscription?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final textTheme = Theme.of(context).textTheme;

    return Scaffold(
      appBar: AppBar(title: const Text('Mis Reservas'), centerTitle: true),
      body: _buildBody(scheme, textTheme),
    );
  }

  Widget _buildBody(ColorScheme scheme, TextTheme textTheme) {
    if (_loading) {
      return const Center(child: CircularProgressIndicator());
    }

    if (_error != null) {
      return Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text('Error: $_error', style: const TextStyle(color: Colors.red)),
            const SizedBox(height: 12),
            ElevatedButton(
              onPressed: () => _loadReservas(),
              child: const Text('Reintentar'),
            ),
          ],
        ),
      );
    }

    if (_reservas.isEmpty) {
      return RefreshIndicator(
        onRefresh: () => _loadReservas(),
        child: ListView(
          physics: const AlwaysScrollableScrollPhysics(),
          children: const [
            SizedBox(height: 200),
            Center(child: Text('No tienes reservas aún.')),
          ],
        ),
      );
    }

    return RefreshIndicator(
      onRefresh: () => _loadReservas(),
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: _reservas.length,
        itemBuilder: (context, i) {
          final r = _reservas[i];
          final reservaId = (r['id'] ?? r['reserva_id']) as int?;
          final cancha = r['cancha']?['nombre'] ?? 'Cancha desconocida';
          final fecha = r['fecha'] != null
              ? DateFormat('dd/MM/yyyy').format(DateTime.parse(r['fecha']))
              : '--/--/----';
          final hora = r['hora'] ?? '--:--';
          final horaFin = r['hora_fin'] ?? '--:--';
          final estado = (r['estado'] ?? 'pendiente').toString();
          final estadoPago = (r['estado_pago'] ?? 'pendiente').toString();
          final precio = r['precio_por_cancha']?.toString() ?? '0';
          final deporte =
              (r['deporte'] ?? r['tipo'] ?? r['sport'] ?? '').toString();

          return Card(
            elevation: 3,
            margin: const EdgeInsets.only(bottom: 14),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(16),
            ),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    cancha,
                    style: textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.bold,
                      color: scheme.primary,
                  ),
                ),
                const SizedBox(height: 6),
                if (deporte.isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 2),
                    child: Text(
                      'Deporte: $deporte',
                      style: textTheme.bodyMedium?.copyWith(
                        color: scheme.onSurfaceVariant,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                Text('📅 $fecha   ⏰ $hora - $horaFin'),
                Text('💰 $precio COP'),
                  const SizedBox(height: 8),
                  Wrap(
                    spacing: 8,
                    children: [
                      _EstadoChip(
                        label: estado.toUpperCase(),
                        color: _estadoColor(estado),
                      ),
                      _EstadoChip(
                        label: 'Pago: ${estadoPago.toUpperCase()}',
                        color: _estadoPagoColor(estadoPago),
                      ),
                    ],
                  ),
                  if (reservaId != null) ...[
                    const SizedBox(height: 12),
                    Align(
                      alignment: Alignment.centerRight,
                      child: TextButton.icon(
                        onPressed: () => _actualizarPagoIndividual(reservaId),
                        icon: const Icon(Icons.sync),
                        label: const Text('Actualizar pago'),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  Color _estadoColor(String estado) {
    switch (estado.toLowerCase()) {
      case 'confirmada':
      case 'confirmado':
        return Colors.green;
      case 'cancelada':
      case 'cancelado':
        return Colors.red;
      default:
        return Colors.orange;
    }
  }

  Color _estadoPagoColor(String estadoPago) {
    switch (estadoPago.toLowerCase()) {
      case 'approved':
      case 'aprobado':
      case 'confirmado':
      case 'pagado':
      case 'free':
        return Colors.green;
      case 'rechazado':
      case 'cancelado':
      case 'rejected':
        return Colors.red;
      default:
        return Colors.orange;
    }
  }
}

class _EstadoChip extends StatelessWidget {
  const _EstadoChip({required this.label, required this.color});

  final String label;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Chip(
      label: Text(label, style: const TextStyle(color: Colors.white)),
      backgroundColor: color,
    );
  }
}
