import 'package:flutter/material.dart';

import '../../../models/calificacion.dart';
import '../../../services/auth_service.dart';
import '../../../services/calificacion_service.dart';

class CalificacionWidget extends StatefulWidget {
  const CalificacionWidget({super.key, required this.canchaId});

  final int canchaId;

  @override
  State<CalificacionWidget> createState() => _CalificacionWidgetState();
}

class _CalificacionWidgetState extends State<CalificacionWidget> {
  final _service = CalificacionService();
  final _comentarioController = TextEditingController();

  double _promedio = 0;
  int _total = 0;
  int _selectedEstrellas = 0;
  bool _loading = true;
  bool _sending = false;
  List<Calificacion> _calificaciones = <Calificacion>[];
  String? _errorMessage;

  bool get _isAuthenticated =>
      AuthService.instance.currentUser != null || AuthService.instance.isLoggedIn;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() {
      _loading = true;
      _errorMessage = null;
    });
    try {
      final results = await Future.wait([
        _service.obtenerPromedio(widget.canchaId),
        _service.listarCalificaciones(widget.canchaId),
      ]);

      if (!mounted) return;

      final promedioData = results[0] as Map<String, dynamic>;
      final calificaciones = results[1] as List<Calificacion>;

      setState(() {
        _promedio =
            (promedioData['promedio'] as num?)?.toDouble() ?? 0.0;
        _total = (promedioData['total'] as num?)?.toInt() ?? calificaciones.length;
        _calificaciones = calificaciones;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMessage = 'No se pudieron cargar las calificaciones. Intenta más tarde.';
      });
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  void _selectEstrellas(int value) {
    if (_sending || !_isAuthenticated) return;
    setState(() => _selectedEstrellas = value);
  }

  Future<void> _enviar() async {
    if (!_isAuthenticated) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Inicia sesión para calificar la cancha.')),
      );
      return;
    }

    if (_selectedEstrellas == 0) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Selecciona la cantidad de estrellas.')),
      );
      return;
    }

    setState(() => _sending = true);
    try {
      await _service.enviarCalificacion(
        canchaId: widget.canchaId,
        estrellas: _selectedEstrellas,
        comentario: _comentarioController.text.trim().isEmpty
            ? null
            : _comentarioController.text.trim(),
      );

      if (!mounted) return;

      _comentarioController.clear();
      _selectedEstrellas = 0;
      FocusScope.of(context).unfocus();

      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Calificación enviada. ¡Gracias!')),
      );

      await _loadData();
    } catch (e) {
      if (!mounted) return;
      final friendly = e.toString().replaceFirst('Exception: ', '');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            friendly.isEmpty
                ? 'No se pudo enviar la calificación. Intenta más tarde.'
                : friendly,
          ),
        ),
      );
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  @override
  void dispose() {
    _comentarioController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Card(
      margin: const EdgeInsets.only(top: 32),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
      elevation: 3,
      child: Padding(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      _promedio.toStringAsFixed(1),
                      style: Theme.of(context).textTheme.headlineMedium?.copyWith(
                            fontWeight: FontWeight.bold,
                          ),
                    ),
                    const SizedBox(height: 4),
                    Text('Promedio de $_total reseñas'),
                    const SizedBox(height: 8),
                    _buildAverageStars(_promedio),
                  ],
                ),
                IconButton(
                  onPressed: _loading ? null : _loadData,
                  icon: _loading
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(strokeWidth: 2),
                        )
                      : const Icon(Icons.refresh_rounded),
                  tooltip: 'Actualizar',
                ),
              ],
            ),
            const Divider(height: 32),
            Align(
              alignment: Alignment.centerLeft,
              child: Text(
                'Califica esta cancha',
                style: Theme.of(context).textTheme.titleMedium?.copyWith(
                      fontWeight: FontWeight.w600,
                    ),
              ),
            ),
            const SizedBox(height: 12),
            _buildSelectableStars(),
            const SizedBox(height: 12),
            if (_isAuthenticated)
              TextField(
                controller: _comentarioController,
                minLines: 2,
                maxLines: 4,
                decoration: const InputDecoration(
                  labelText: 'Comentario (opcional)',
                  border: OutlineInputBorder(),
                ),
              )
            else
              Container(
                width: double.infinity,
                padding:
                    const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                decoration: BoxDecoration(
                  color: scheme.surfaceContainerHighest.withValues(alpha: 0.4),
                  borderRadius: BorderRadius.circular(12),
                ),
                child: Text(
                  'Inicia sesión para dejar un comentario.',
                  style: TextStyle(color: scheme.onSurfaceVariant),
                ),
              ),
            const SizedBox(height: 12),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: _sending ? null : _enviar,
                icon: _sending
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Colors.white,
                        ),
                      )
                    : const Icon(Icons.star_rate_rounded),
                label: Text(_sending ? 'Enviando...' : 'Enviar calificación'),
              ),
            ),
            const SizedBox(height: 20),
            Text(
              'Experiencias recientes',
              style: Theme.of(context).textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
            ),
            const SizedBox(height: 12),
            if (_errorMessage != null)
              Text(
                _errorMessage!,
                style: TextStyle(color: scheme.error),
              )
            else if (_calificaciones.isEmpty)
              Text(
                _loading
                    ? 'Cargando calificaciones...'
                    : 'Aún no hay calificaciones para esta cancha.',
                style: TextStyle(color: scheme.onSurfaceVariant),
              )
            else
              ListView.separated(
                shrinkWrap: true,
                physics: const NeverScrollableScrollPhysics(),
                itemCount: _calificaciones.length,
                separatorBuilder: (_, __) => const Divider(height: 20),
                itemBuilder: (context, index) {
                  final calificacion = _calificaciones[index];
                  return _CalificacionTile(calificacion: calificacion);
                },
              ),
          ],
        ),
      ),
    );
  }

  Widget _buildAverageStars(double rating) {
    return Row(
      children: List.generate(5, (index) {
        final position = index + 1;
        IconData icon;
        if (rating >= position) {
          icon = Icons.star_rounded;
        } else if (rating + 0.5 >= position) {
          icon = Icons.star_half_rounded;
        } else {
          icon = Icons.star_border_rounded;
        }
        return Icon(icon, color: Colors.amber, size: 24);
      }),
    );
  }

  Widget _buildSelectableStars() {
    final canTap = _isAuthenticated && !_sending;
    return Row(
      children: List.generate(5, (index) {
        final value = index + 1;
        final isActive = value <= _selectedEstrellas;
        return IconButton(
          onPressed: canTap ? () => _selectEstrellas(value) : null,
          icon: Icon(
            isActive ? Icons.star_rounded : Icons.star_border_rounded,
            color: isActive ? Colors.amber : Colors.grey,
            size: 32,
          ),
        );
      }),
    );
  }
}

class _CalificacionTile extends StatelessWidget {
  const _CalificacionTile({required this.calificacion});

  final Calificacion calificacion;

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final date = _formatDate(calificacion.fecha);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            CircleAvatar(
              radius: 18,
              backgroundColor: scheme.primary.withValues(alpha: 0.15),
              child: Text(
                (calificacion.usuarioNombre?.substring(0, 1) ?? '?').toUpperCase(),
                style: TextStyle(
                  color: scheme.primary,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    calificacion.usuarioNombre ?? 'Usuario anónimo',
                    style: Theme.of(context).textTheme.bodyMedium?.copyWith(
                          fontWeight: FontWeight.w600,
                        ),
                  ),
                  if (date != null)
                    Text(
                      date,
                      style: Theme.of(context)
                          .textTheme
                          .bodySmall
                          ?.copyWith(color: scheme.onSurfaceVariant),
                    ),
                ],
              ),
            ),
            Row(
              children: List.generate(5, (i) {
                final filled = i < calificacion.estrellas;
                return Icon(
                  filled ? Icons.star_rounded : Icons.star_border_rounded,
                  color: Colors.amber,
                  size: 18,
                );
              }),
            ),
          ],
        ),
        if (calificacion.comentario != null &&
            calificacion.comentario!.trim().isNotEmpty) ...[
          const SizedBox(height: 8),
          Text(
            calificacion.comentario!,
            style: Theme.of(context).textTheme.bodyMedium,
          ),
        ],
      ],
    );
  }

  String? _formatDate(String? raw) {
    if (raw == null || raw.isEmpty) return null;
    try {
      final parsed = DateTime.parse(raw);
      return '${parsed.day.toString().padLeft(2, '0')}/'
          '${parsed.month.toString().padLeft(2, '0')}/'
          '${parsed.year}';
    } catch (_) {
      return raw;
    }
  }
}
