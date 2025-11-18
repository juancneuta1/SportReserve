import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import 'package:flutter/foundation.dart';
import '../models/user_profile_laravel.dart';

/// Servicio encargado de autenticación y manejo del token persistente.
class AuthService extends ChangeNotifier {
  AuthService._();

  static final AuthService _instance = AuthService._();

  /// Punto de acceso único para el resto de la aplicación.
  static AuthService get instance => _instance;

  static const String _baseUrl = 'http://10.0.2.2:8000/api';
  static const String _tokenKey = 'auth_token';
  static const String _userKey = 'auth_user';

  String? _token;
  UserProfileLaravel? _cachedProfile;

  /// Token actual almacenado en memoria.
  String? get token => _token;

  /// Perfil del usuario autenticado en memoria.
  UserProfileLaravel? get currentUser => _cachedProfile;

  /// Indica si existe una sesión activa en memoria.
  bool get isLoggedIn => _token != null;

  /// Construye una URI completa con la base de la API (sin dobles barras).
  Uri _uri(String path) {
    return Uri.parse('$_baseUrl${path.startsWith('/') ? path : '/$path'}');
  }

  /// Carga token y perfil desde almacenamiento persistente si no están en memoria.
  Future<void> _ensureSessionLoaded() async {
    if (_token != null && _cachedProfile != null) return;

    final prefs = await SharedPreferences.getInstance();
    _token ??= prefs.getString(_tokenKey);

    if (_cachedProfile == null) {
      final jsonString = prefs.getString(_userKey);
      if (jsonString != null && jsonString.isNotEmpty) {
        try {
          final data = jsonDecode(jsonString) as Map<String, dynamic>;
          _cachedProfile = UserProfileLaravel.fromJson(data);
        } catch (_) {
          await prefs.remove(_userKey);
        }
      }
    }
  }

  /// Guarda el token emitido por el backend.
  Future<void> _persistToken(String tokenValue) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_tokenKey, tokenValue);
    _token = tokenValue;
  }

  /// Guarda el perfil autenticado para acceso rápido.
  Future<void> _persistUser(UserProfileLaravel user) async {
    final prefs = await SharedPreferences.getInstance();
    final serialized = jsonEncode(user.toJson());
    await prefs.setString(_userKey, serialized);
    _cachedProfile = user;
  }

  /// Limpia cualquier dato de sesión persistido.
  Future<void> clearSession() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_userKey);

    _token = null;
    _cachedProfile = null;

    notifyListeners();
  }

  /// Headers genéricos para peticiones JSON.
  Map<String, String> _jsonHeaders() => const {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  };

  String _sanitizeJson(String raw) {
    return raw
        .replaceAll('\uFEFF', '')
        .replaceAll('\u200B', '')
        .replaceAll(RegExp(r'[\u0000-\u0008\u000B\u000C\u000E-\u001F]'), '')
        .trim();
  }

  /// Headers autenticados con el token.
  Future<Map<String, String>> _authHeaders() async {
    await _ensureSessionLoaded();
    final currentToken = _token;
    if (currentToken == null || currentToken.isEmpty) {
      throw StateError('No hay token disponible. Inicia sesión nuevamente.');
    }
    return {
      'Accept': 'application/json',
      'Content-Type': 'application/json',
      'Authorization': 'Bearer $currentToken',
    };
  }

  /// 🔹 Registro: devuelve el perfil creado.
  Future<UserProfileLaravel> register({
    required String name,
    required String email,
    required String password,
    required String confirmPassword,
  }) async {
    final url = _uri('/register');
    debugPrint('📡 Intentando registrar en: $url');

    final response = await http.post(
      url,
      headers: _jsonHeaders(),
      body: jsonEncode({
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': confirmPassword,
      }),
    );

    debugPrint('📩 Código de respuesta: ${response.statusCode}');
    debugPrint('📦 Respuesta: ${response.body}');

    if (response.statusCode == 201) {
      final sanitized = _sanitizeJson(response.body);
      final data = jsonDecode(sanitized) as Map<String, dynamic>;
      return UserProfileLaravel.fromJson(
        (data['user'] ?? data) as Map<String, dynamic>,
      );
    }

    if (response.statusCode == 422) {
      final data = jsonDecode(response.body);
      if (data is Map && data['errors'] != null) {
        final errors = data['errors'] as Map<String, dynamic>;
        final String firstError = (errors.values.first as List).first
            .toString();
        throw Exception(firstError);
      }
      throw Exception(data['message'] ?? 'Error de validación.');
    }

    throw Exception(
      'Error inesperado (${response.statusCode}): ${response.body}',
    );
  }

  /// 🔹 Login: guarda token y perfil.
  Future<bool> login({required String email, required String password}) async {
    debugPrint('📡 Intentando iniciar sesión en $_baseUrl/login');

    final response = await http.post(
      _uri('/login'),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
      body: jsonEncode({'email': email, 'password': password}),
    );

    debugPrint('📩 Código de respuesta: ${response.statusCode}');
    debugPrint('📦 Respuesta: ${response.body}');

    if (response.statusCode != 200) return false;

    final data = jsonDecode(response.body) as Map<String, dynamic>;
    final issuedToken = data['access_token'] as String?;
    if (issuedToken == null || issuedToken.isEmpty) return false;

    await _persistToken(issuedToken);

    final userJson = data['user'];
    if (userJson is Map<String, dynamic>) {
      await _persistUser(UserProfileLaravel.fromJson(userJson));
    } else {
      _cachedProfile = null;
    }

    notifyListeners();
    return true;
  }

  /// 🔹 Obtiene el perfil autenticado (de caché o desde backend).
  Future<UserProfileLaravel?> getProfile({bool forceRefresh = false}) async {
    await _ensureSessionLoaded();
    if (_token == null) return null;

    if (!forceRefresh && _cachedProfile != null) {
      return _cachedProfile;
    }

    final response = await http.get(
      _uri('/profile'),
      headers: await _authHeaders(),
    );

    if (response.statusCode == 200) {
      final data = jsonDecode(response.body);
      final bodyUser = (data is Map && data['user'] != null)
          ? data['user']
          : data;
      final profile = UserProfileLaravel.fromJson(
        bodyUser as Map<String, dynamic>,
      );
      await _persistUser(profile);
      return profile;
    }

    if (response.statusCode == 401) {
      await clearSession();
      return null;
    }

    throw Exception('No se pudo obtener el perfil (${response.statusCode}).');
  }

  /// Alias de getProfile (usado por otras partes del código).
  Future<UserProfileLaravel?> fetchProfile({bool forceRefresh = false}) {
    return getProfile(forceRefresh: forceRefresh);
  }

  /// 🔹 Actualiza datos del perfil autenticado.
  Future<UserProfileLaravel> updateProfile({
    String? name,
    String? email,
    String? password,
    String? photoUrl,
  }) async {
    final payload = {
      if (name != null) 'name': name,
      if (email != null) 'email': email,
      if (password != null && password.isNotEmpty) 'password': password,
      if (photoUrl != null) 'photo_url': photoUrl,
    };

    if (payload.isEmpty) {
      final profile = await getProfile();
      if (profile == null) {
        throw StateError('No hay perfil disponible para actualizar.');
      }
      return profile;
    }

    final response = await http.put(
      _uri('/profile'),
      headers: await _authHeaders(),
      body: jsonEncode(payload),
    );

    if (response.statusCode != 200) {
      throw Exception(
        'No se pudo actualizar el perfil (${response.statusCode}).',
      );
    }

    final data = jsonDecode(response.body) as Map<String, dynamic>;
    final bodyUser = data['user'] ?? data;
    final updated = UserProfileLaravel.fromJson(
      bodyUser as Map<String, dynamic>,
    );
    await _persistUser(updated);
    notifyListeners();
    return updated;
  }

  /// 🔹 Verifica si existe una sesión autenticada válida.
  Future<bool> isAuthenticated() async {
    await _ensureSessionLoaded();
    return _token != null && _token!.isNotEmpty;
  }

  /// 🔹 Cierra sesión en el backend y limpia los datos locales.
  Future<void> logout() async {
    await _ensureSessionLoaded();

    // 🧹 Si no hay token, solo limpia localmente
    if (_token == null || _token!.isEmpty) {
      await clearSession();
      debugPrint('🧽 No había token, se limpió la sesión local.');
      return;
    }

    try {
      final url = _uri('/logout');
      final headers = await _authHeaders();
      final response = await http.post(url, headers: headers);

      debugPrint('🔚 Logout backend → ${response.statusCode}');
      debugPrint('📦 Respuesta: ${response.body}');

      // 🔒 Manejo de estado
      if (response.statusCode == 200) {
        debugPrint('✅ Sesión cerrada correctamente en el backend.');
      } else if (response.statusCode == 401) {
        debugPrint('⚠️ Token expirado o inválido, limpiando sesión local.');
      } else {
        debugPrint(
          '⚠️ Logout con respuesta inesperada (${response.statusCode}).',
        );
      }
    } catch (e) {
      debugPrint('⚠️ Excepción durante logout: $e');
    } finally {
      await clearSession();
    }
  }

  /// 🔹 Expone headers autenticados para consumo de otros servicios.
  Future<Map<String, String>> authenticatedJsonHeaders() => _authHeaders();

  /// 🔹 Cambia la contraseña del usuario autenticado.
  Future<void> changePassword({
    required String currentPassword,
    required String newPassword,
    required String confirmPassword,
  }) async {
    final url = _uri('/change-password');
    final headers = await _authHeaders();

    final body = jsonEncode({
      'current_password': currentPassword,
      'new_password': newPassword,
      'new_password_confirmation': confirmPassword,
    });

    final response = await http.post(url, headers: headers, body: body);
    debugPrint('📡 Cambiar contraseña: ${response.statusCode}');
    debugPrint('📦 Respuesta: ${response.body}');

    if (response.statusCode != 200) {
      throw Exception('Error al cambiar la contraseña.');
    }
  }

  /// 🔹 Solicita correo de recuperación de contraseña.
  Future<String> sendPasswordResetEmail(String email) async {
    final url = _uri('/password/forgot');
    final response = await http.post(
      url,
      headers: _jsonHeaders(),
      body: jsonEncode({'email': email}),
    );

    final sanitized = _sanitizeJson(response.body);
    final data = sanitized.isNotEmpty
        ? jsonDecode(sanitized) as Map<String, dynamic>
        : <String, dynamic>{};

    if (response.statusCode == 200) {
      return data['message']?.toString() ??
          'Hemos enviado un enlace de recuperación si el correo existe.';
    }

    if (response.statusCode == 422) {
      final msg =
          data['message']?.toString() ?? 'No pudimos procesar tu solicitud.';
      throw Exception(msg);
    }

    debugPrint('🔴 Forgot password error ${response.statusCode}: $sanitized');
    throw Exception(
      'No pudimos enviar el enlace en este momento. Intenta más tarde o contacta soporte.',
    );
  }

  /// 🔹 Restablece la contraseña usando el token enviado por correo.
  Future<String> resetPassword({
    required String email,
    required String token,
    required String password,
    required String confirmPassword,
  }) async {
    final url = _uri('/password/reset');
    final response = await http.post(
      url,
      headers: _jsonHeaders(),
      body: jsonEncode({
        'email': email,
        'token': token,
        'password': password,
        'password_confirmation': confirmPassword,
      }),
    );

    final sanitized = _sanitizeJson(response.body);
    final data = sanitized.isNotEmpty
        ? jsonDecode(sanitized) as Map<String, dynamic>
        : <String, dynamic>{};

    if (response.statusCode == 200) {
      return data['message']?.toString() ??
          'Contraseña restablecida correctamente.';
    }

    if (response.statusCode == 422) {
      final msg =
          data['message']?.toString() ??
          'No pudimos restablecer la contraseña.';
      throw Exception(msg);
    }

    throw Exception(
      data['message']?.toString() ??
          'Error inesperado al restablecer contraseña (${response.statusCode}).',
    );
  }

  /// 🔹 Sube una nueva foto de perfil y actualiza el perfil.
  Future<String> uploadProfilePhoto(String filePath) async {
    final url = _uri('/update-photo');
    final headers = await _authHeaders();

    final request = http.MultipartRequest('POST', url);
    request.headers.addAll(headers);
    request.files.add(await http.MultipartFile.fromPath('photo', filePath));

    final streamed = await request.send();
    final response = await http.Response.fromStream(streamed);

    debugPrint('📡 Subir foto: ${response.statusCode}');
    debugPrint('📦 Respuesta: ${response.body}');

    if (response.statusCode == 200) {
      final sanitized = _sanitizeJson(response.body);
      final data = jsonDecode(sanitized) as Map<String, dynamic>;
      final photoUrl = data['photo_url'] as String;

      if (_cachedProfile != null) {
        _cachedProfile = _cachedProfile!.copyWith(photoUrl: photoUrl);
        await _persistUser(_cachedProfile!);
      }

      notifyListeners();
      return photoUrl;
    }

    throw Exception('Error al subir la foto (${response.statusCode}).');
  }

  /// 🔹 Actualiza el perfil local en memoria y almacenamiento persistente
  Future<void> updateLocalProfile(UserProfileLaravel updatedUser) async {
    _cachedProfile = updatedUser;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_userKey, jsonEncode(updatedUser.toJson()));
    notifyListeners();
  }
}
