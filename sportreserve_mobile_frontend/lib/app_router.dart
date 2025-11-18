import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

import 'package:sportreserve_mobile_frontend/features/onboarding/onboarding_page.dart';
import 'package:sportreserve_mobile_frontend/features/auth/login_page.dart';
import 'package:sportreserve_mobile_frontend/features/auth/register_page.dart';
import 'package:sportreserve_mobile_frontend/features/auth/forgot_password_page.dart';
import 'package:sportreserve_mobile_frontend/features/auth/reset_password_page.dart';
import 'package:sportreserve_mobile_frontend/features/profile/user_profile_page.dart';

import 'package:sportreserve_mobile_frontend/features/canchas/mapa_canchas_page.dart';
import 'package:sportreserve_mobile_frontend/features/canchas/canchas_page.dart';
import 'package:sportreserve_mobile_frontend/features/canchas/registrar_cancha_page.dart';
import 'package:sportreserve_mobile_frontend/features/canchas/cancha_detail_page.dart';

import 'package:sportreserve_mobile_frontend/models/cancha.dart' as model;
import 'package:sportreserve_mobile_frontend/models/cancha_meta.dart';
import 'package:sportreserve_mobile_frontend/services/cancha_service.dart';
import 'package:sportreserve_mobile_frontend/services/auth_service.dart';

import 'package:sportreserve_mobile_frontend/db/app_database.dart';

import 'features/reservas/mis_reservas_page.dart';
import 'features/reservas/pasarela_pago_page.dart';

GoRouter appRouter(bool hasSeenOnboarding) => GoRouter(
  initialLocation: hasSeenOnboarding ? '/mapa' : '/onboarding',

  routes: [
    GoRoute(
      path: '/onboarding',
      builder: (context, state) => const OnboardingPage(),
    ),

    GoRoute(
      path: '/mapa',
      builder: (context, state) => const MapaCanchasPage(),
    ),

    GoRoute(path: '/canchas', builder: (context, state) => const CanchasPage()),

    GoRoute(
      path: '/registrar-cancha',
      builder: (context, state) => const RegistrarCanchaPage(),
    ),

    GoRoute(path: '/login', builder: (context, state) => const LoginPage()),
    GoRoute(
      path: '/register',
      builder: (context, state) => const RegisterPage(),
    ),
    GoRoute(
      path: '/forgot-password',
      builder: (context, state) => const ForgotPasswordPage(),
    ),
    GoRoute(
      path: '/reset-password',
      builder: (context, state) => ResetPasswordPage(
        initialEmail: state.uri.queryParameters['email'],
        initialToken: state.uri.queryParameters['token'],
      ),
    ),
    GoRoute(
      path: '/password.reset',
      builder: (context, state) => ResetPasswordPage(
        initialEmail: state.uri.queryParameters['email'],
        initialToken: state.uri.queryParameters['token'],
      ),
    ),
    GoRoute(
      path: '/profile',
      builder: (context, state) => const UserProfilePage(),
    ),

    GoRoute(
      path: '/mis-reservas',
      builder: (context, state) => const MisReservasPage(),
    ),

    // Pasarela de pago
    GoRoute(
      path: '/pasarela-pago',
      builder: (context, state) {
        final params = _resolvePasarelaArgs(state.extra, state.uri);
        final paymentLink = params.paymentLink;

        if (paymentLink == null || paymentLink.isEmpty) {
          return const Scaffold(
            body: Center(child: Text('Enlace de pago no disponible.')),
          );
        }

        return PasarelaPagoPage(
          paymentLink: paymentLink,
          titulo: params.titulo ?? 'Pasarela de pago',
          backUrls: params.backUrls,
          reservationId: params.reservationId,
          forceSandboxBanner: params.forceSandbox,
        );
      },
    ),

    // ⚽ Detalle dinámico de cancha
    GoRoute(
      path: '/cancha/:id',
      builder: (context, state) {
        final id = int.tryParse(state.pathParameters['id'] ?? '') ?? 0;

        // ✅ Crear instancia de la base local y servicio de canchas
        final db = AppDatabase();
        final canchaService = CanchaService(db);

        return FutureBuilder<model.Cancha?>(
          future: canchaService.getCanchaById(id),
          builder: (context, snapshot) {
            if (snapshot.connectionState == ConnectionState.waiting) {
              return const Scaffold(
                body: Center(
                  child: CircularProgressIndicator(color: Colors.green),
                ),
              );
            }

            if (snapshot.hasError) {
              return Scaffold(
                body: Center(
                  child: Text(
                    'Error al cargar la cancha: ${snapshot.error}',
                    style: const TextStyle(color: Colors.red),
                  ),
                ),
              );
            }

            final cancha = snapshot.data;
            if (cancha == null) {
              return const Scaffold(
                body: Center(
                  child: Text(
                    'Cancha no encontrada 😢',
                    style: TextStyle(color: Colors.grey),
                  ),
                ),
              );
            }

            // 🟢 Crear metadatos para mostrar la cancha
            final meta = CanchaMeta.buildFromModel(cancha);

            return CanchaDetailPage(cancha: cancha, meta: meta);
          },
        );
      },
    ),
  ],

  redirect: (context, state) {
    final loggedIn = AuthService.instance.currentUser != null;
    final isAuthRoute =
        state.matchedLocation == '/login' ||
        state.matchedLocation == '/register';
    final isProfileRoute = state.matchedLocation == '/profile';

    if (!loggedIn && isProfileRoute) return '/login';
    if (loggedIn && isAuthRoute) return '/mapa';

    return null;
  },
);

PasarelaRouteArgs _resolvePasarelaArgs(dynamic extra, Uri uri) {
  String? paymentLink;
  Map<String, String>? backUrls;
  int? reservationId;
  bool forceSandbox = false;
  String? titulo;

  Map<String, String>? mapFromDynamic(dynamic source) {
    if (source is Map) {
      final result = <String, String>{};
      source.forEach((key, value) {
        if (key == null || value == null) return;
        final keyString = key.toString();
        final valueString = value.toString();
        if (keyString.isEmpty || valueString.isEmpty) return;
        result[keyString] = valueString;
      });
      return result.isEmpty ? null : result;
    }
    return null;
  }

  int? parseId(dynamic value) {
    if (value is int) return value;
    if (value is String) return int.tryParse(value);
    return null;
  }

  bool parseBool(dynamic value) {
    if (value is bool) return value;
    if (value is String) {
      final normalized = value.trim().toLowerCase();
      return normalized == 'true' || normalized == '1' || normalized == 'yes';
    }
    if (value is num) return value != 0;
    return false;
  }

  void readMap(Map<dynamic, dynamic> map) {
    paymentLink ??= map['paymentLink']?.toString();
    paymentLink ??= map['payment_link']?.toString();
    paymentLink ??= map['url']?.toString();
    backUrls ??= mapFromDynamic(map['backUrls'] ?? map['back_urls']);
    reservationId ??= parseId(map['reservationId'] ?? map['reservaId']);
    reservationId ??= parseId(map['reserva_id'] ?? map['id']);
    forceSandbox =
        forceSandbox || parseBool(map['forceSandbox'] ?? map['sandbox']);
    titulo ??= map['titulo']?.toString();
  }

  if (extra is PasarelaRouteArgs) {
    return PasarelaRouteArgs(
      paymentLink: extra.paymentLink,
      backUrls: extra.backUrls,
      reservationId: extra.reservationId,
      forceSandbox: extra.forceSandbox,
      titulo: extra.titulo,
    );
  } else if (extra is Map) {
    readMap(extra);
  } else if (extra is String) {
    paymentLink = extra;
  }

  paymentLink ??= uri.queryParameters['url'];
  titulo ??= uri.queryParameters['title'] ?? uri.queryParameters['titulo'];
  if (parseBool(uri.queryParameters['sandbox'])) {
    forceSandbox = true;
  }

  return PasarelaRouteArgs(
    paymentLink: paymentLink,
    backUrls: backUrls,
    reservationId: reservationId,
    forceSandbox: forceSandbox,
    titulo: titulo,
  );
}

class PasarelaRouteArgs {
  const PasarelaRouteArgs({
    required this.paymentLink,
    this.backUrls,
    this.reservationId,
    this.forceSandbox = false,
    this.titulo,
  });

  final String? paymentLink;
  final Map<String, String>? backUrls;
  final int? reservationId;
  final bool forceSandbox;
  final String? titulo;
}
