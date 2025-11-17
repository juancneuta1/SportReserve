import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';

class AuthTextField extends StatelessWidget {
  final TextEditingController controller;
  final String label;
  final IconData icon;
  final bool obscureText;
  final bool dark;

  const AuthTextField({
    super.key,
    required this.controller,
    required this.label,
    required this.icon,
    this.obscureText = false,
    this.dark = false, // 👈 agregado para soportar modo oscuro/claro
  });

  @override
  Widget build(BuildContext context) {
    final borderColor = dark
        ? Colors.white.withValues(alpha: 0.4)
        : Colors.green.withValues(alpha: 0.4);

    final textColor = dark ? Colors.white : Colors.black87;

    return Container(
      margin: const EdgeInsets.symmetric(vertical: 6),
      child: TextField(
        controller: controller,
        obscureText: obscureText,
        style: GoogleFonts.poppins(
          color: textColor,
          fontWeight: FontWeight.w500,
        ),
        decoration: InputDecoration(
          prefixIcon: Icon(icon, color: dark ? Colors.white70 : Colors.green),
          labelText: label,
          labelStyle: GoogleFonts.poppins(
            color: dark ? Colors.white70 : Colors.green[900],
            fontWeight: FontWeight.w500,
          ),
          enabledBorder: OutlineInputBorder(
            borderSide: BorderSide(color: borderColor, width: 1.4),
            borderRadius: BorderRadius.circular(12),
          ),
          focusedBorder: OutlineInputBorder(
            borderSide: BorderSide(
              color: dark ? Colors.greenAccent : Colors.green[700]!,
              width: 2.0,
            ),
            borderRadius: BorderRadius.circular(12),
          ),
          filled: true,
          fillColor: dark
              ? Colors.white.withValues(alpha: 0.1)
              : Colors.green[50]?.withValues(alpha: 0.5),
          contentPadding: const EdgeInsets.symmetric(
            horizontal: 16,
            vertical: 14,
          ),
        ),
      ),
    );
  }
}
