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

  /// evita multiples cierres
  bool _handled = false;

  int _loadAttempts = 0;
  static const int _maxAttempts = 3;

  bool get _showSandboxBanner =>
      widget.forceSandboxBanner ||
      AppEnvironment.instance.mercadopagoForceSandbox;

  String? sanitizePaymentLink(String raw) {
    var link = raw
        .replaceAll('\n', ' ')
        .replaceAll('\r', ' ')
        .replaceAll('\t', ' ')
        .replaceAll('redir rect', 'redirect')
        .trim();
    link = link.replaceAll(RegExp(r'\s+'), '');

    if (link.isEmpty) return null;
    if (!link.startsWith('http://') && !link.startsWith('https://')) {
      return null;
    }

    final parsed = Uri.tryParse(link);
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
  // CONFIGURAR WEBVIEW
  // ---------------------------------------------------------------
  void _initializeWebView() {
    final sanitizedLink = sanitizePaymentLink(widget.paymentLink);
    if (sanitizedLink == null) {
      setState(() {
        _errorMessage = 'El enlace de pago es invalido o incompleto.';
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
                  ? 'No pudimos cargar la pasarela despues de $_maxAttempts intentos.'
                  : 'No pudimos cargar la pasarela (${error.errorCode}).';
            });
            if (!reachedLimit) {
              _reload();
            }
          },
          onNavigationRequest: (request) {
            final callback = _matchCallback(request.url);
            if (callback != null) {
              _handleCheckoutStatus(callback, request.url);
              return NavigationDecision.prevent;
            }

            if (_isLeavingMercadoPago(request.url)) {
              return NavigationDecision.prevent;
            }

            return NavigationDecision.navigate;
          },
          // Android extra callback: filtrado con _handled y matching
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
  // DETECTAR CALLBACKS SUCCESS / FAILURE / PENDING
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
      final pathsMatch = _comparePaths(incoming.path, expected.path);

      final basicMatch = incoming.scheme == expected.scheme &&
          incoming.host == expected.host &&
          pathsMatch;

      final startsWithMatch =
          incoming.toString().startsWith(expected.toString());

      if (basicMatch ||
          startsWithMatch ||
          _matchesWithSegments(incoming, expected)) {
        return entry.key;
      }
    }

    return null;
  }

  bool _comparePaths(String a, String b) {
    final normA = a.endsWith('/') ? a.substring(0, a.length - 1) : a;
    final normB = b.endsWith('/') ? b.substring(0, b.length - 1) : b;
    if (normA == normB) return true;
    // permite "/success/#/approved" o "/success?status=approved"
    return normA.startsWith(normB) || normB.startsWith(normA);
  }

  bool _matchesWithSegments(Uri incoming, Uri expected) {
    if (incoming.scheme != expected.scheme || incoming.host != expected.host) {
      return false;
    }
    final incSegments =
        incoming.pathSegments.where((s) => s.isNotEmpty).toList();
    final expSegments =
        expected.pathSegments.where((s) => s.isNotEmpty).toList();
    if (incSegments.isEmpty || expSegments.isEmpty) return false;

    if (incSegments.length < expSegments.length) return false;
    for (var i = 0; i < expSegments.length; i++) {
      if (incSegments[i] != expSegments[i]) return false;
    }
    return true;
  }

  bool _isLeavingMercadoPago(String url) {
    return url.contains('mercadolibre.com') ||
        url.contains('google.com') ||
        url.contains('facebook.com');
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
                        child: Text('No fue posible inicializar el checkout.'))
                    : Stack(
                        children: [
                          WebViewWidget(controller: controller),
                          if (_isLoading)
                            const Center(
                                child: CircularProgressIndicator()),
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
            const Icon(Icons.warning_amber_rounded,
                size: 64, color: Colors.red),
            const SizedBox(height: 16),
            Text(
              _errorMessage ?? 'Ocurrio un error inesperado.',
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
