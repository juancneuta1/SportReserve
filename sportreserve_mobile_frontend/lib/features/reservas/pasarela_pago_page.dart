import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:webview_flutter/webview_flutter.dart';

import 'package:sportreserve_mobile_frontend/config/app_environment.dart';
import 'package:sportreserve_mobile_frontend/services/reservation_events_bus.dart';
import 'package:sportreserve_mobile_frontend/services/reservation_service.dart';

import 'models/pasarela_payment_result.dart';

/// Pantalla que abre el checkout de Mercado Pago dentro de un WebView.
class PasarelaPagoPage extends StatefulWidget {
  const PasarelaPagoPage({
    super.key,
    required this.paymentLink,
    this.titulo = 'Pasarela de pago',
    this.backUrls,
    this.reservationId,
    this.forceSandboxBanner = false,
  });

  final String paymentLink;
  final String titulo;
  final Map<String, String>? backUrls;
  final int? reservationId;
  final bool forceSandboxBanner;

  @override
  State<PasarelaPagoPage> createState() => _PasarelaPagoPageState();
}

class _PasarelaPagoPageState extends State<PasarelaPagoPage> {
  WebViewController? _controller;
  bool _isLoading = true;
  String? _errorMessage;
  late final Map<PasarelaPaymentStatus, Uri> _callbacks;

  /// evita múltiples cierres
  bool _handled = false;

  int _loadAttempts = 0;
  static const int _maxAttempts = 3;

  bool get _showSandboxBanner =>
      widget.forceSandboxBanner ||
      AppEnvironment.instance.mercadopagoForceSandbox;

  // ---------------------------------------------------------------
  // SANITIZADOR DE URL
  // ---------------------------------------------------------------
  String? sanitizePaymentLink(String raw) {
    if (raw.isEmpty) return null;

    final clean = raw
        .replaceAll('\n', '')
        .replaceAll('\r', '')
        .replaceAll('\t', '')
        .trim();

    if (!clean.startsWith('http://') && !clean.startsWith('https://')) {
      return null;
    }

    final parsed = Uri.tryParse(clean);
    if (parsed == null || parsed.host.isEmpty) return null;

    return parsed.toString();
  }

  @override
  void initState() {
    super.initState();
    _callbacks = _buildCallbackUris(widget.backUrls);
    _initializeWebView();
  }

  // ---------------------------------------------------------------
  // CONFIGURAR WEBVIEW - VERSIÓN CORREGIDA COMPLETA
  // ---------------------------------------------------------------
  void _initializeWebView() {
    final sanitizedLink = sanitizePaymentLink(widget.paymentLink);
    if (sanitizedLink == null) {
      setState(() {
        _errorMessage = 'El enlace de pago es inválido o incompleto.';
        _isLoading = false;
      });
      return;
    }

    final uri = Uri.parse(sanitizedLink);

    final controller = WebViewController()
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(Colors.transparent)
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (_) {
            if (!mounted) return;
            setState(() {
              _isLoading = true;
              _errorMessage = null;
            });
          },
          onPageFinished: (_) {
            if (!mounted) return;
            setState(() => _isLoading = false);
          },
          onWebResourceError: (error) {
            if (!mounted) return;

            _loadAttempts++;
            final reachedLimit = _loadAttempts >= _maxAttempts;

            setState(() {
              _isLoading = false;
              _errorMessage = reachedLimit
                  ? 'No pudimos cargar la pasarela después de $_maxAttempts intentos.'
                  : 'No pudimos cargar la pasarela (${error.errorCode}).';
            });

            if (!reachedLimit) {
              _reload();
            }
          },
          onNavigationRequest: (request) {
            // Detectar callbacks success/failure/pending
            final callback = _matchCallback(request.url);
            if (callback != null) {
              _handleCheckoutStatus(callback, request.url);
              return NavigationDecision.prevent;
            }

            // Bloqueo de salidas no deseadas
            if (_isLeavingMercadoPago(request.url)) {
              return NavigationDecision.prevent;
            }

            return NavigationDecision.navigate;
          },
          onUrlChange: (change) {
            final url = change.url;
            if (url == null || _handled) return;

            final callback = _matchCallback(url);
            if (callback != null) {
              _handleCheckoutStatus(callback, url);
            }
          },
        ),
      )
      ..loadRequest(uri);

    _controller = controller;
  }

  // ---------------------------------------------------------------
  // MANEJO DE URLs de Mercado Pago
  // ---------------------------------------------------------------
  bool _isLeavingMercadoPago(String url) {
    final uri = Uri.tryParse(url);
    if (uri == null) return true;

    final host = uri.host.toLowerCase();

    // dominios permitidos
    const allowedHosts = [
      'mercadopago.com',
      'mercadopago.com.co',
      'sandbox.mercadopago.com',
      'sandbox.mercadopago.com.co',
      'mercadopago.com.ar',
      'mercadopago.com.br',
      'mlstatic.com', // assets de checkout
    ];

    for (final allowed in allowedHosts) {
      if (host.endsWith(allowed)) return false;
    }

    // dominios externos prohibidos
    const blocked = [
      'mercadolibre.com',
      'google.com',
      'facebook.com',
      'apple.com',
      'youtube.com',
      'instagram.com',
    ];

    for (final b in blocked) {
      if (host.contains(b)) return true;
    }

    return true;
  }

  // ---------------------------------------------------------------
  // CALLBACKS (success, failure, pending)
  // ---------------------------------------------------------------
  Map<PasarelaPaymentStatus, Uri> _buildCallbackUris(
    Map<String, String>? rawBackUrls,
  ) {
    final out = <PasarelaPaymentStatus, Uri>{};

    rawBackUrls?.forEach((key, value) {
      if (key.isEmpty || value.isEmpty) return;
      final uri = Uri.tryParse(value);
      if (uri == null) return;

      switch (key.toLowerCase()) {
        case 'success':
          out[PasarelaPaymentStatus.success] = uri;
          break;
        case 'failure':
          out[PasarelaPaymentStatus.failure] = uri;
          break;
        case 'pending':
          out[PasarelaPaymentStatus.pending] = uri;
          break;
      }
    });

    return out;
  }

  PasarelaPaymentStatus? _matchCallback(String url) {
    final incoming = Uri.tryParse(url);
    if (incoming == null) return null;

    for (final entry in _callbacks.entries) {
      final expected = entry.value;

      if (incoming.scheme == expected.scheme &&
          incoming.host == expected.host &&
          incoming.path.startsWith(expected.path)) {
        return entry.key;
      }

      if (incoming.toString().startsWith(expected.toString())) {
        return entry.key;
      }
    }

    return null;
  }

  // ---------------------------------------------------------------
  // CERRAR LA PASARELA
  // ---------------------------------------------------------------
  Future<void> _handleCheckoutStatus(
    PasarelaPaymentStatus status,
    String redirectUrl,
  ) async {
    if (_handled) return;
    _handled = true;

    if (status == PasarelaPaymentStatus.success) {
      await _refrescarReservas();
    }

    if (!mounted) return;

    Navigator.pop(
      context,
      PasarelaPaymentResult(status, redirectUrl: redirectUrl),
    );
  }

  Future<void> _refrescarReservas() async {
    try {
      final data = await ReservationService().obtenerMisReservas();

      ReservationEventsBus.instance.emit(
        ReservationEvent.reservasRefrescadas(payload: data),
      );

      ReservationEventsBus.instance.emit(
        ReservationEvent.pagoCompletado(
          payload: {'reservationId': widget.reservationId},
        ),
      );
    } catch (_) {}
  }

  // ---------------------------------------------------------------
  // RELOAD
  // ---------------------------------------------------------------
  void _reload() {
    if (_handled) return;
    final controller = _controller;
    final sanitizedLink = sanitizePaymentLink(widget.paymentLink);
    if (controller == null || sanitizedLink == null) return;

    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    controller.loadRequest(Uri.parse(sanitizedLink));
  }

  // ---------------------------------------------------------------
  // UI
  // ---------------------------------------------------------------
  @override
  Widget build(BuildContext context) {
    final controller = _controller;

    return Scaffold(
      appBar: AppBar(
        title: Text(widget.titulo),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: controller == null ? null : _reload,
          ),
        ],
      ),
      body: Column(
        children: [
          if (_showSandboxBanner)
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
              color: Colors.orange.withOpacity(0.15),
              child: const Text(
                'Usando cuenta de prueba de Mercado Pago',
                style: TextStyle(
                  fontWeight: FontWeight.w600,
                  color: Colors.orange,
                ),
                textAlign: TextAlign.center,
              ),
            ),
          Expanded(
            child: _errorMessage != null
                ? _buildErrorState()
                : controller == null
                ? const Center(
                    child: Text('No fue posible inicializar el checkout.'),
                  )
                : Stack(
                    children: [
                      WebViewWidget(controller: controller),
                      if (_isLoading)
                        const Center(child: CircularProgressIndicator()),
                    ],
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(
              Icons.warning_amber_rounded,
              size: 64,
              color: Colors.red,
            ),
            const SizedBox(height: 16),
            Text(
              _errorMessage ?? 'Ocurrió un error inesperado.',
              textAlign: TextAlign.center,
              style: Theme.of(context).textTheme.titleMedium,
            ),
            const SizedBox(height: 24),
            FilledButton.icon(
              onPressed: _reload,
              icon: const Icon(Icons.refresh),
              label: const Text('Reintentar'),
            ),
          ],
        ),
      ),
    );
  }
}
