import 'package:flutter/material.dart';
import 'package:lottie/lottie.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:sportreserve_mobile_frontend/features/canchas/mapa_canchas_page.dart';
import 'package:sportreserve_mobile_frontend/features/onboarding/onboarding_page.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controller;
  late final Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 1200),
    );

    _scaleAnimation = CurvedAnimation(
      parent: _controller,
      curve: Curves.easeOutBack,
    );

    _controller.forward();
    _checkFirstRun();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _checkFirstRun() async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    final bool hasSeenOnboarding = prefs.getBool('hasSeenOnboarding') ?? false;

    await Future<void>.delayed(const Duration(seconds: 3));

    if (!mounted) return;

    final Widget destination = hasSeenOnboarding
        ? const MapaCanchasPage()
        : const OnboardingPage();

    Navigator.of(
      context,
    ).pushReplacement(MaterialPageRoute<void>(builder: (_) => destination));
  }

  @override
  Widget build(BuildContext context) {
    final Color background = Theme.of(context).colorScheme.primary;

    return Scaffold(
      backgroundColor: background,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            ScaleTransition(
              scale: _scaleAnimation,
              child: Image.asset(
                'assets/images/ICON_MOBILE_SPORTRESERVE.png',
                width: 280,
                height: 280,
              ),
            ),
            const SizedBox(height: 96),
            Lottie.asset(
              'assets/lottie/loading.json',
              width: 120,
              repeat: true,
            ),
          ],
        ),
      ),
    );
  }
}
