import 'package:flutter/material.dart';

class DeporteSelector extends StatelessWidget {
  const DeporteSelector({
    super.key,
    required this.deportes,
    required this.selected,
    required this.onSelect,
  });

  final List<String> deportes;
  final String? selected;
  final ValueChanged<String> onSelect;

  @override
  Widget build(BuildContext context) {
    final opciones = deportes
        .map((e) => e.trim())
        .where((e) => e.isNotEmpty)
        .toList();

    if (opciones.isEmpty) return const SizedBox.shrink();

    final scheme = Theme.of(context).colorScheme;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          'Selecciona el deporte',
          style: Theme.of(context).textTheme.titleMedium?.copyWith(
                fontWeight: FontWeight.w600,
              ),
        ),
        const SizedBox(height: 8),
        Wrap(
          spacing: 8,
          runSpacing: 8,
          children: opciones
              .map(
                (deporte) => ChoiceChip(
                  label: Text(deporte),
                  selected: deporte == selected,
                  selectedColor: scheme.primary.withOpacity(0.15),
                  labelStyle: TextStyle(
                    color:
                        deporte == selected ? scheme.primary : scheme.onSurface,
                    fontWeight:
                        deporte == selected ? FontWeight.w600 : FontWeight.w500,
                  ),
                  side: BorderSide(
                    color: deporte == selected
                        ? scheme.primary
                        : scheme.outline.withOpacity(0.5),
                  ),
                  onSelected: (_) => onSelect(deporte),
                ),
              )
              .toList(),
        ),
      ],
    );
  }
}
