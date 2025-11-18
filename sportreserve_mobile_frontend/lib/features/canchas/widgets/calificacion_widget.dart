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
      final resumen = await _service.obtenerResumen(widget.canchaId);

      if (!mounted) return;

      setState(() {
        _promedio = resumen.promedio;
        _total = resumen.total;
        _calificaciones = resumen.calificaciones;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() {
        _errorMessage =
            'No se pudieron cargar las calificaciones. Intenta mas tarde.';
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
        const SnackBar(
          content: Text('Inicia sesion para calificar la cancha.'),
        ),
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
        const SnackBar(
          content: Text('Calificacion enviada. Gracias!'),
        ),
      );

      await _loadData();
    } catch (e) {
      if (!mounted) return;
      final friendly = e.toString().replaceFirst('Exception: ', '');
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            friendly.isEmpty
                ? 'No se pudo enviar la calificacion. Intenta mas tarde.'
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
    final textTheme = Theme.of(context).textTheme;

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: scheme.surface,
        borderRadius: BorderRadius.circular(18),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withValues(alpha: 0.05),
            blurRadius: 12,
            offset: const Offset(0, 6),
          ),
        ],
      ),
      child: _loading
          ? Center(
              child: CircularProgressIndicator(
                valueColor: AlwaysStoppedAnimation<Color>(scheme.primary),
              ),
            )
          : Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Text(
                      _promedio.toStringAsFixed(1),
                      style: textTheme.displaySmall?.copyWith(
                        fontWeight: FontWeight.bold,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            'Promedio de $_total resenas',
                            style: textTheme.bodyMedium?.copyWith(
                              color: scheme.onSurfaceVariant,
                            ),
                          ),
                          const SizedBox(height: 4),
                          _buildAverageStars(_promedio),
                        ],
                      ),
                    ),
                    IconButton(
                      onPressed: _loadData,
                      icon: const Icon(Icons.refresh_rounded),
                      tooltip: 'Recargar resenas',
                    ),
                  ],
                ),
                const SizedBox(height: 16),
                Text(
                  'Califica esta cancha',
                  style: textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 8),
                _buildSelectableStars(),
                const SizedBox(height: 8),
                TextField(
                  controller: _comentarioController,
                  maxLines: 3,
                  decoration: const InputDecoration(
                    labelText: 'Comentario (opcional)',
                    border: OutlineInputBorder(),
                  ),
                ),
                const SizedBox(height: 12),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _sending ? null : _enviar,
                    style: ElevatedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12),
                      ),
                    ),
                    child: _sending
                        ? const SizedBox(
                            height: 18,
                            width: 18,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          )
                        : const Text('Enviar calificacion'),
                  ),
                ),
                const SizedBox(height: 16),
                Text(
                  'Experiencias recientes',
                  style: textTheme.titleMedium?.copyWith(
                    fontWeight: FontWeight.w600,
                  ),
                ),
                const SizedBox(height: 10),
                if (_errorMessage != null)
                  Text(
                    _errorMessage!,
                    style: textTheme.bodyMedium?.copyWith(color: Colors.red),
                  )
                else if (_calificaciones.isEmpty)
                  Text(
                    'Aun no hay calificaciones. Se el primero en opinar!',
                    style: textTheme.bodyMedium,
                  )
                else
                  Column(
                    children: List.generate(
                      _calificaciones.length,
                      (index) => Padding(
                        padding: EdgeInsets.only(
                          bottom: index == _calificaciones.length - 1 ? 0 : 12,
                        ),
                        child: _CalificacionTile(
                          calificacion: _calificaciones[index],
                        ),
                      ),
                    ),
                  ),
              ],
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
        return Icon(icon, color: Colors.amber, size: 22);
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
                (calificacion.usuarioNombre?.substring(0, 1) ?? '?')
                    .toUpperCase(),
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
                    calificacion.usuarioNombre ?? 'Usuario anonimo',
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
