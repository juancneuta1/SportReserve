import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sportreserve_mobile_frontend/app_router.dart';
import 'services/app_initializer.dart';
import 'services/auth_service.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  try {
    await AppInitializer.initialize();
  } catch (error, stackTrace) {
    debugPrint('App initialization error: $error');
    debugPrintStack(stackTrace: stackTrace);
  }

  final prefs = await SharedPreferences.getInstance();
  final bool hasSeenOnboarding = prefs.getBool('hasSeenOnboarding') ?? false;

  // 🟢 Cargar sesión antes de iniciar la app
  await AuthService.instance.fetchProfile(); // <--- esta línea hace la magia

  runApp(MyApp(hasSeenOnboarding: hasSeenOnboarding));
}

class MyApp extends StatelessWidget {
  final bool hasSeenOnboarding;
  const MyApp({super.key, required this.hasSeenOnboarding});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      debugShowCheckedModeBanner: false,
      title: 'SportReserve',
      theme: _buildLightTheme(),
      darkTheme: _buildDarkTheme(),
      themeMode: ThemeMode.system,
      routerConfig: appRouter(hasSeenOnboarding),
    );
  }
}

// 🎨 Temas visuales
ThemeData _buildLightTheme() {
  const primary = Color(0xFF2E7D32);
  const accent = Color(0xFFF9A825);
  const tertiary = Color(0xFF00796B);

  final ColorScheme scheme =
      ColorScheme.fromSeed(
        seedColor: primary,
        brightness: Brightness.light,
      ).copyWith(
        primary: primary,
        secondary: accent,
        tertiary: tertiary,
        surface: const Color(0xFFF7FAF7),
        primaryContainer: const Color(0xFFA5D6A7),
        secondaryContainer: const Color(0xFFFFECB3),
        onSurfaceVariant: const Color(0xFF4A5D52),
      );

  final TextTheme textTheme = _sportsTypography(brightness: Brightness.light);

  return ThemeData(
    useMaterial3: true,
    colorScheme: scheme,
    scaffoldBackgroundColor: const Color(0xFFF2F5F2),
    textTheme: textTheme,
    appBarTheme: AppBarTheme(
      backgroundColor: Colors.transparent,
      foregroundColor: scheme.onSurface,
      elevation: 0,
      titleTextStyle: textTheme.titleLarge?.copyWith(
        fontWeight: FontWeight.w700,
      ),
    ),
    // ✅ Cambiado a CardThemeData
    cardTheme: CardThemeData(
      color: scheme.surface,
      elevation: 6,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: primary,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
        textStyle: textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
      ),
    ),
    chipTheme: ChipThemeData(
      backgroundColor: scheme.surfaceContainerHighest,
      selectedColor: primary.withValues(alpha: 0.85),
      labelStyle: textTheme.labelMedium?.copyWith(fontWeight: FontWeight.w600),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
    ),
  );
}

ThemeData _buildDarkTheme() {
  const primary = Color(0xFF2E7D32);
  const accent = Color(0xFFF9A825);
  const tertiary = Color(0xFF26A69A);

  final ColorScheme scheme =
      ColorScheme.fromSeed(
        seedColor: primary,
        brightness: Brightness.dark,
      ).copyWith(
        primary: primary,
        secondary: accent,
        tertiary: tertiary,
        surface: const Color(0xFF101C10),
        primaryContainer: const Color(0xFF145925),
        secondaryContainer: const Color(0xFF5C4100),
        onSurfaceVariant: const Color(0xFFB3C7B8),
      );

  final TextTheme textTheme = _sportsTypography(brightness: Brightness.dark);

  return ThemeData(
    useMaterial3: true,
    colorScheme: scheme,
    scaffoldBackgroundColor: const Color(0xFF0B140C),
    textTheme: textTheme,
    appBarTheme: AppBarTheme(
      backgroundColor: Colors.transparent,
      foregroundColor: scheme.onSurface,
      elevation: 0,
      titleTextStyle: textTheme.titleLarge?.copyWith(
        fontWeight: FontWeight.w700,
      ),
    ),
    // ✅ También corregido aquí
    cardTheme: CardThemeData(
      color: scheme.surface,
      elevation: 6,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
    ),
    filledButtonTheme: FilledButtonThemeData(
      style: FilledButton.styleFrom(
        backgroundColor: primary,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
        textStyle: textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
      ),
    ),
    chipTheme: ChipThemeData(
      backgroundColor: scheme.surfaceContainerHighest,
      selectedColor: primary.withValues(alpha: 0.75),
      labelStyle: textTheme.labelMedium?.copyWith(fontWeight: FontWeight.w600),
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
    ),
  );
}

// 🏟️ Tipografía general
TextTheme _sportsTypography({required Brightness brightness}) {
  final Color baseColor = brightness == Brightness.dark
      ? Colors.white
      : const Color(0xFF1F2C21);

  final TextTheme poppins = GoogleFonts.poppinsTextTheme().apply(
    bodyColor: baseColor,
    displayColor: baseColor,
  );

  TextStyle? montserrat(TextStyle? style) {
    if (style == null) return null;
    return GoogleFonts.montserrat(
      textStyle: style,
      fontWeight: FontWeight.w600,
    );
  }

  return poppins.copyWith(
    headlineLarge: montserrat(poppins.headlineLarge),
    headlineMedium: montserrat(poppins.headlineMedium),
    headlineSmall: montserrat(poppins.headlineSmall),
    titleLarge: poppins.titleLarge?.copyWith(fontWeight: FontWeight.w700),
    titleMedium: poppins.titleMedium?.copyWith(fontWeight: FontWeight.w600),
    labelLarge: poppins.labelLarge?.copyWith(fontWeight: FontWeight.w600),
    bodyLarge: poppins.bodyLarge?.copyWith(height: 1.4),
    bodyMedium: poppins.bodyMedium?.copyWith(height: 1.4),
  );
}
