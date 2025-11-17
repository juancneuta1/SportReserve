import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;

class RegistrarCanchaPage extends StatefulWidget {
  const RegistrarCanchaPage({super.key});

  @override
  State<RegistrarCanchaPage> createState() => _RegistrarCanchaPageState();
}

class _RegistrarCanchaPageState extends State<RegistrarCanchaPage> {
  final _formKey = GlobalKey<FormState>();

  final TextEditingController _nombreController = TextEditingController();
  final TextEditingController _tipoController = TextEditingController();
  final TextEditingController _ubicacionController = TextEditingController();
  final TextEditingController _latController = TextEditingController();
  final TextEditingController _lngController = TextEditingController();
  final TextEditingController _precioController = TextEditingController();

  bool _loading = false;

  @override
  void dispose() {
    _nombreController.dispose();
    _tipoController.dispose();
    _ubicacionController.dispose();
    _latController.dispose();
    _lngController.dispose();
    _precioController.dispose();
    super.dispose();
  }

  Future<void> _registrarCancha() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _loading = true);

    final url = Uri.parse('http://192.168.20.82:8000/api/canchas');
    final body = {
      'nombre': _nombreController.text,
      'tipo': _tipoController.text,
      'ubicacion': _ubicacionController.text,
      'latitud': _latController.text,
      'longitud': _lngController.text,
      'precio_por_hora': _precioController.text,
      'disponibilidad': true,
    };

    try {
      final response = await http.post(
        url,
        headers: const {'Content-Type': 'application/json'},
        body: jsonEncode(body),
      );

      if (!mounted) return;
      final messenger = ScaffoldMessenger.of(context);

      if (response.statusCode == 201 || response.statusCode == 200) {
        messenger.showSnackBar(
          const SnackBar(content: Text('Cancha registrada correctamente')),
        );

        _formKey.currentState!.reset();
        _nombreController.clear();
        _tipoController.clear();
        _ubicacionController.clear();
        _latController.clear();
        _lngController.clear();
        _precioController.clear();

        Future.delayed(const Duration(seconds: 1), () {
          if (!mounted) return;
          Navigator.pop(context, true);
        });
      } else {
        if (kDebugMode) {
          debugPrint('Error registrar cancha: ${response.body}');
        }
        messenger.showSnackBar(
          SnackBar(content: Text('Error: ${response.body}')),
        );
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Error al conectar con el servidor: $e')),
      );
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;

    return Scaffold(
      appBar: AppBar(title: const Text('Registrar Cancha')),
      body: Padding(
        padding: const EdgeInsets.all(20),
        child: Form(
          key: _formKey,
          child: ListView(
            children: [
              TextFormField(
                controller: _nombreController,
                decoration: const InputDecoration(labelText: 'Nombre'),
                validator: (v) => v!.isEmpty ? 'Campo obligatorio' : null,
              ),
              TextFormField(
                controller: _tipoController,
                decoration: const InputDecoration(labelText: 'Tipo'),
                validator: (v) => v!.isEmpty ? 'Campo obligatorio' : null,
              ),
              TextFormField(
                controller: _ubicacionController,
                decoration: const InputDecoration(labelText: 'Ubicación'),
              ),
              Row(
                children: [
                  Expanded(
                    child: TextFormField(
                      controller: _latController,
                      decoration: const InputDecoration(labelText: 'Latitud'),
                      validator: (v) => v!.isEmpty ? 'Campo obligatorio' : null,
                      keyboardType: TextInputType.number,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: TextFormField(
                      controller: _lngController,
                      decoration: const InputDecoration(labelText: 'Longitud'),
                      validator: (v) => v!.isEmpty ? 'Campo obligatorio' : null,
                      keyboardType: TextInputType.number,
                    ),
                  ),
                ],
              ),
              TextFormField(
                controller: _precioController,
                decoration: const InputDecoration(labelText: 'Precio por hora'),
                validator: (v) => v!.isEmpty ? 'Campo obligatorio' : null,
                keyboardType: TextInputType.number,
              ),
              const SizedBox(height: 20),
              ElevatedButton.icon(
                icon: _loading
                    ? const SizedBox(
                        width: 18,
                        height: 18,
                        child: CircularProgressIndicator(strokeWidth: 2),
                      )
                    : const Icon(Icons.save_rounded),
                label: Text(_loading ? 'Guardando...' : 'Guardar cancha'),
                style: ElevatedButton.styleFrom(
                  backgroundColor: scheme.primary,
                  foregroundColor: Colors.white,
                ),
                onPressed: _loading ? null : _registrarCancha,
              ),
            ],
          ),
        ),
      ),
    );
  }
}
