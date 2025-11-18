import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';
import 'package:sportreserve_mobile_frontend/models/cancha.dart' as model;
import 'package:sportreserve_mobile_frontend/models/cancha_meta.dart';
import 'package:sportreserve_mobile_frontend/features/canchas/widgets/calificacion_widget.dart';
import 'package:sportreserve_mobile_frontend/features/canchas/widgets/deporte_selector.dart';
import 'package:sportreserve_mobile_frontend/services/reservation_service.dart';
import 'package:sportreserve_mobile_frontend/services/notification_service.dart';
import 'package:sportreserve_mobile_frontend/services/auth_service.dart';
import 'package:sportreserve_mobile_frontend/features/reservas/models/pasarela_payment_result.dart';

class CanchaDetailPage extends StatefulWidget {
  const CanchaDetailPage({super.key, required this.cancha, required this.meta});

  final model.Cancha cancha;
  final CanchaMeta meta;

  @override
  State<CanchaDetailPage> createState() => _CanchaDetailPageState();
}

class _CanchaDetailPageState extends State<CanchaDetailPage> {
  bool _reserving = false;
  DateTime _selectedDate = DateTime.now();
  TimeOfDay? _selectedTime;
  int _cantidadHoras = 1;
  String? _selectedDeporte;
  late final List<String> _deportesDisponibles;

  @override
  void initState() {
    super.initState();
    _deportesDisponibles = widget.cancha.deportesDisponibles;
    if (_deportesDisponibles.length == 1) {
      _selectedDeporte = _deportesDisponibles.first;
    }
  }

  // -------------------------------------------------------------------
  // 🔹 RESERVAR CANCHA
  // -------------------------------------------------------------------
  Future<void> _handleReserve() async {
    if (_reserving) return;

    final messenger = ScaffoldMessenger.of(context);
    final user =
        AuthService.instance.currentUser ??
        await AuthService.instance.fetchProfile();

    if (user == null) {
      if (!mounted) return;
      _showLoginRegisterModal(context, expired: true);
      return;
    }

    if (_selectedTime == null) {
      messenger.showSnackBar(
        const SnackBar(
          content: Text('Selecciona una hora antes de reservar.'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    if (_selectedDeporte == null || _selectedDeporte!.isEmpty) {
      messenger.showSnackBar(
        const SnackBar(
          content: Text('Selecciona el deporte que deseas reservar.'),
          backgroundColor: Colors.orange,
        ),
      );
      return;
    }

    setState(() => _reserving = true);

    try {
      final fecha = DateFormat('yyyy-MM-dd').format(_selectedDate);
      final h = _selectedTime!.hour.toString().padLeft(2, '0');
      final m = _selectedTime!.minute.toString().padLeft(2, '0');
      final parsedHora = '$h:$m';

      final response = await ReservationService().crearReserva(
        canchaId: widget.cancha.id,
        deporte: _selectedDeporte!,
        fecha: fecha,
        hora: parsedHora,
        cantidadHoras: _cantidadHoras,
        precioPorCancha: widget.cancha.precioPorCancha,
      );

      if (!mounted) return;

      // ================================================================
      // 🔥 FIX DEL ERROR 422 — monto mínimo pasarela MercadoPago
      // ================================================================
      if (response['statusCode'] == 422) {
        messenger.showSnackBar(
          SnackBar(
            content: Text(response['message'] ?? 'El pago no puede procesarse'),
            backgroundColor: Colors.red,
            duration: const Duration(seconds: 4),
          ),
        );
        return; // ⛔ detener flujo y NO abrir pasarela
      }

      // ================================================================
      // 🔥 FLUJO NORMAL DE RESERVA
      // ================================================================
      if (response['success'] == true) {
        messenger.showSnackBar(
          const SnackBar(
            content: Text('Reserva creada exitosamente.'),
            backgroundColor: Colors.green,
          ),
        );

        await NotificationService.instance.showInstantNotification(
          title: 'Reserva confirmada',
          body:
              "Tu cancha ${widget.cancha.nombre} está reservada para $fecha a las $parsedHora.",
        );

        final reserva = response['reserva'] as Map<String, dynamic>?;
        final paymentLink = _resolvePaymentLink(response, reserva);
        final backUrls =
            _normalizeBackUrls(response['back_urls']) ??
            _normalizeBackUrls(reserva?['back_urls']);
        final reservaId = _parseReservationId(reserva);
        final backendSandbox =
            (response['environment'] ?? reserva?['environment'])
                ?.toString()
                .toLowerCase() ==
            'sandbox';

        if (paymentLink != null && paymentLink.isNotEmpty) {
          await _openPasarelaPago(
            paymentLink: paymentLink,
            backUrls: backUrls,
            reservaId: reservaId,
            forceSandboxBanner: backendSandbox,
          );
        } else {
          messenger.showSnackBar(
            const SnackBar(
              content: Text(
                'No recibimos un enlace de pago. Revisa Mis Reservas para validar el estado.',
              ),
              duration: Duration(seconds: 5),
            ),
          );
        }
      } else {
        messenger.showSnackBar(
          SnackBar(
            content: Text(response['message'] ?? 'Error desconocido'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      messenger.showSnackBar(SnackBar(content: Text('Error: $e')));
    } finally {
      if (mounted) setState(() => _reserving = false);
    }
  }

  // -------------------------------------------------------------------
  // 🔹 UTILIDADES DE RESERVA Y PAGO
  // -------------------------------------------------------------------

  String? _extractDeporteError(dynamic rawErrors) {
    if (rawErrors is Map) {
      final value =
          rawErrors['deporte'] ?? rawErrors['sport'] ?? rawErrors['tipo'];
      if (value is List && value.isNotEmpty) return value.first.toString();
      if (value is String && value.trim().isNotEmpty) return value.trim();
    }
    return null;
  }

  String? _resolvePaymentLink(
    Map<String, dynamic> response,
    Map<String, dynamic>? reserva,
  ) {
    final candidates = <dynamic>[
      response['payment_link'],
      reserva?['payment_link'],
      reserva?['init_point'],
      response['init_point'],
    ];

    for (var c in candidates) {
      if (c is String && c.trim().isNotEmpty) return c.trim();
    }
    return null;
  }

  Map<String, String>? _normalizeBackUrls(dynamic source) {
    if (source is Map) {
      final result = <String, String>{};
      source.forEach((key, value) {
        if (key != null && value != null) {
          result[key.toString()] = value.toString();
        }
      });
      return result;
    }
    return null;
  }

  int? _parseReservationId(Map<String, dynamic>? reserva) {
    final raw = reserva?['id'] ?? reserva?['reserva_id'];
    if (raw is int) return raw;
    if (raw is String) return int.tryParse(raw);
    return null;
  }

  Future<void> _openPasarelaPago({
    required String paymentLink,
    Map<String, String>? backUrls,
    int? reservaId,
    bool forceSandboxBanner = false,
  }) async {
    final result = await context.push<PasarelaPaymentResult>(
      '/pasarela-pago',
      extra: {
        'paymentLink': paymentLink,
        'backUrls': backUrls,
        'reservationId': reservaId,
        'forceSandbox': forceSandboxBanner,
        'titulo': 'Pasarela de pago',
      },
    );

    if (result != null) _handleCheckoutResult(result);
  }

  void _handleCheckoutResult(PasarelaPaymentResult result) {
    final messenger = ScaffoldMessenger.of(context);
    switch (result.status) {
      case PasarelaPaymentStatus.success:
        messenger.showSnackBar(
          const SnackBar(
            content: Text(
              'Pago confirmado. Actualiza Mis Reservas para ver detalles.',
            ),
            backgroundColor: Colors.green,
          ),
        );
        break;

      case PasarelaPaymentStatus.pending:
        messenger.showSnackBar(
          const SnackBar(
            content: Text('Pago pendiente de confirmación.'),
            backgroundColor: Colors.orange,
          ),
        );
        break;

      case PasarelaPaymentStatus.failure:
        messenger.showSnackBar(
          const SnackBar(
            content: Text(
              'Pago rechazado o cancelado. Puedes intentar nuevamente desde Mis Reservas.',
            ),
            backgroundColor: Colors.red,
          ),
        );
        break;
    }
  }

  // -------------------------------------------------------------------
  // 🔹 MODAL LOGIN/REGISTRO
  // -------------------------------------------------------------------
  void _showLoginRegisterModal(
    BuildContext parentContext, {
    bool expired = false,
  }) {
    showModalBottomSheet(
      context: parentContext,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (ctx) {
        return DraggableScrollableSheet(
          initialChildSize: 0.35,
          minChildSize: 0.3,
          maxChildSize: 0.6,
          builder: (ctx, scrollController) {
            return Container(
              decoration: const BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
              ),
              padding: const EdgeInsets.fromLTRB(20, 16, 20, 24),
              child: ListView(
                controller: scrollController,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      const Icon(
                        Icons.lock_outline,
                        color: Colors.redAccent,
                        size: 28,
                      ),
                      const SizedBox(width: 8),
                      Text(
                        expired
                            ? 'Tu sesión ha expirado'
                            : 'Inicia sesión para reservar',
                        style: Theme.of(context).textTheme.titleMedium
                            ?.copyWith(
                              fontWeight: FontWeight.bold,
                              color: Colors.black87,
                            ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  Text(
                    expired
                        ? 'Inicia sesión nuevamente para continuar con tu reserva.'
                        : 'Para continuar con tu reserva, inicia sesión o crea una cuenta.',
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 20),
                  FilledButton.icon(
                    onPressed: () {
                      Navigator.of(ctx).pop();
                      GoRouter.of(parentContext).push('/login');
                    },
                    icon: const Icon(Icons.login),
                    label: const Text('Iniciar sesión'),
                  ),
                  const SizedBox(height: 10),
                  OutlinedButton.icon(
                    onPressed: () {
                      Navigator.of(ctx).pop();
                      GoRouter.of(parentContext).push('/register');
                    },
                    icon: const Icon(Icons.person_add_alt_1),
                    label: const Text('Crear cuenta'),
                  ),
                ],
              ),
            );
          },
        );
      },
    );
  }

  // -------------------------------------------------------------------
  // 🔹 PICKERS
  // -------------------------------------------------------------------
  Future<void> _selectDate() async {
    final picked = await showDatePicker(
      context: context,
      initialDate: _selectedDate,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 30)),
    );
    if (picked != null) setState(() => _selectedDate = picked);
  }

  Future<void> _selectTime() async {
    final picked = await showTimePicker(
      context: context,
      initialTime: const TimeOfDay(hour: 6, minute: 0),
    );
    if (picked != null) setState(() => _selectedTime = picked);
  }

  // -------------------------------------------------------------------
  // 🔹 UI PRINCIPAL
  // -------------------------------------------------------------------
  @override
  Widget build(BuildContext context) {
    final cancha = widget.cancha;
    final meta = widget.meta;
    final scheme = Theme.of(context).colorScheme;
    final textTheme = Theme.of(context).textTheme;
    final user = AuthService.instance.currentUser;

    final servicios = cancha.servicios.isNotEmpty
        ? cancha.servicios.split(',').map((e) => e.trim()).toList()
        : ['Sin servicios registrados'];

    return Scaffold(
      appBar: AppBar(
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new_rounded),
          onPressed: () => Navigator.of(context).maybePop(),
        ),
        title: Text(cancha.nombre),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(24, 24, 24, 40),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Wrap(
              spacing: 12,
              runSpacing: 8,
              children: [
                _AvailabilityChip(meta: meta),
                _InfoChip(icon: meta.icon, label: meta.type),
                _InfoChip(icon: Icons.grass, label: meta.surface),
              ],
            ),

            const SizedBox(height: 24),

            if (user != null) ...[
              Text(
                'Selecciona fecha y hora',
                style: textTheme.titleLarge?.copyWith(
                  fontWeight: FontWeight.w600,
                ),
              ),
              const SizedBox(height: 16),

              Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _selectDate,
                      icon: const Icon(Icons.calendar_month),
                      label: Text(
                        DateFormat('dd/MM/yyyy').format(_selectedDate),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _selectTime,
                      icon: const Icon(Icons.access_time),
                      label: Text(
                        _selectedTime != null
                            ? _selectedTime!.format(context)
                            : 'Hora',
                      ),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 12),

              if (_deportesDisponibles.isNotEmpty) ...[
                DeporteSelector(
                  deportes: _deportesDisponibles,
                  selected: _selectedDeporte,
                  onSelect: (value) => setState(() => _selectedDeporte = value),
                ),
                const SizedBox(height: 16),
              ],

              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Duración (horas):'),
                  DropdownButton<int>(
                    value: _cantidadHoras,
                    onChanged: (v) {
                      if (v != null) setState(() => _cantidadHoras = v);
                    },
                    items: List.generate(5, (i) => i + 1)
                        .map(
                          (h) => DropdownMenuItem(value: h, child: Text('$h')),
                        )
                        .toList(),
                  ),
                ],
              ),

              const SizedBox(height: 20),

              SizedBox(
                width: double.infinity,
                child: ElevatedButton.icon(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.green,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 14),
                    shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12),
                    ),
                  ),
                  onPressed: _reserving || _selectedDeporte == null
                      ? null
                      : _handleReserve,
                  icon: _reserving
                      ? const SizedBox(
                          width: 18,
                          height: 18,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2,
                          ),
                        )
                      : const Icon(Icons.sports_soccer),
                  label: Text(
                    _reserving ? 'Reservando...' : 'Reservar',
                    style: const TextStyle(fontSize: 16),
                  ),
                ),
              ),

              const SizedBox(height: 28),
            ] else ...[
              const SizedBox(height: 16),
              Center(
                child: Column(
                  children: [
                    const Icon(
                      Icons.lock_outline,
                      size: 50,
                      color: Colors.grey,
                    ),
                    const SizedBox(height: 12),
                    Text(
                      'Inicia sesión para poder reservar una cancha',
                      style: textTheme.bodyMedium,
                      textAlign: TextAlign.center,
                    ),
                    const SizedBox(height: 10),
                    ElevatedButton.icon(
                      icon: const Icon(Icons.login),
                      onPressed: () => context.push('/login'),
                      label: const Text('Iniciar sesión'),
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 28),
            ],

            // -------------------------------------------------------------------
            // 🔹 RESUMEN
            // -------------------------------------------------------------------
            Text(
              'Resumen de la cancha',
              style: textTheme.titleLarge?.copyWith(
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 12),

            Text(
              cancha.descripcion.isNotEmpty
                  ? cancha.descripcion
                  : 'Sin descripción registrada.',
              style: textTheme.bodyMedium?.copyWith(
                color: scheme.onSurfaceVariant,
                height: 1.4,
              ),
            ),

            const SizedBox(height: 24),

            _DetailTile(
              icon: Icons.location_on_outlined,
              title: 'Ubicación',
              subtitle: cancha.ubicacion.isNotEmpty
                  ? cancha.ubicacion
                  : 'Sin dirección registrada.',
            ),

            _DetailTile(
              icon: Icons.monetization_on,
              title: 'Precio por hora',
              subtitle: '\$${cancha.precioPorCancha.toStringAsFixed(0)} COP',
            ),

            const SizedBox(height: 24),

            Text(
              'Servicios incluidos',
              style: textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.w600,
              ),
            ),
            const SizedBox(height: 12),

            Wrap(
              spacing: 12,
              runSpacing: 12,
              children: servicios.map((s) => _ServiceChip(label: s)).toList(),
            ),

            const SizedBox(height: 32),

            CalificacionWidget(canchaId: cancha.id),
          ],
        ),
      ),
    );
  }
}

////////////////////////////////////////////////////////////////////////////////
// 🔹 WIDGETS AUXILIARES
////////////////////////////////////////////////////////////////////////////////

class _ServiceChip extends StatelessWidget {
  const _ServiceChip({required this.label});
  final String label;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: scheme.surfaceContainerHighest.withValues(alpha: 0.4),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: scheme.primary.withValues(alpha: 0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.check_circle, size: 16, color: scheme.primary),
          const SizedBox(width: 6),
          Text(
            label,
            style: Theme.of(context).textTheme.labelLarge?.copyWith(
              color: scheme.primary,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}

class _DetailTile extends StatelessWidget {
  const _DetailTile({
    required this.icon,
    required this.title,
    required this.subtitle,
  });

  final IconData icon;
  final String title;
  final String subtitle;

  @override
  Widget build(BuildContext context) {
    final textTheme = Theme.of(context).textTheme;
    final scheme = Theme.of(context).colorScheme;

    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: scheme.primary, size: 20),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: textTheme.bodyMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                    color: scheme.onSurfaceVariant,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: textTheme.bodyMedium?.copyWith(
                    color: scheme.onSurface,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _InfoChip extends StatelessWidget {
  const _InfoChip({required this.icon, required this.label});

  final IconData icon;
  final String label;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        color: scheme.surfaceContainerHighest.withValues(alpha: 0.4),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: scheme.primary.withValues(alpha: 0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: scheme.primary),
          const SizedBox(width: 6),
          Flexible(
            child: Text(
              label,
              overflow: TextOverflow.ellipsis,
              style: Theme.of(
                context,
              ).textTheme.labelLarge?.copyWith(color: scheme.onSurface),
            ),
          ),
        ],
      ),
    );
  }
}

class _AvailabilityChip extends StatelessWidget {
  const _AvailabilityChip({required this.meta});
  final CanchaMeta meta;

  @override
  Widget build(BuildContext context) {
    final color = CanchaMeta.availabilityColor(true);

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(18),
        color: CanchaMeta.availabilityBackground(true),
        border: Border.all(color: color.withValues(alpha: 0.3)),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(Icons.check_circle, size: 16, color: color),
          const SizedBox(width: 6),
          Text(
            CanchaMeta.availabilityLabel(true),
            style: Theme.of(context).textTheme.labelLarge?.copyWith(
              color: color,
              fontWeight: FontWeight.w600,
            ),
          ),
        ],
      ),
    );
  }
}
