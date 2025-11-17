import 'dart:convert';

import 'package:http/http.dart' as http;

import '../models/user_profile_laravel.dart';
import 'auth_service.dart';

/// Servicio REST para operaciones relacionadas con usuarios.
class UserService {
  UserService._();

  static final UserService instance = UserService._();

  static const String _baseUrl = 'http://127.0.0.1:8000/api';

  Uri _uri(String path, [Map<String, dynamic>? query]) {
    final uri = Uri.parse('$_baseUrl$path');
    if (query == null) return uri;
    return uri.replace(queryParameters: <String, String>{
      ...uri.queryParameters,
      ...query.map((key, value) => MapEntry(key, value.toString())),
    });
  }

  Future<Map<String, String>> _headers() =>
      AuthService.instance.authenticatedJsonHeaders();

  List<UserProfileLaravel> _decodeUsers(String body) {
    final List<dynamic> data = jsonDecode(body) as List<dynamic>;
    return data
        .whereType<Map<String, dynamic>>()
        .map(UserProfileLaravel.fromJson)
        .toList();
  }

  /// Obtiene todos los usuarios registrados.
  Future<List<UserProfileLaravel>> getAll() async {
    final response = await http.get(
      _uri('/usuarios'),
      headers: await _headers(),
    );
    if (response.statusCode != 200) {
      throw Exception('Error al obtener usuarios (${response.statusCode}).');
    }
    return _decodeUsers(response.body);
  }

  /// Recupera un usuario por su identificador.
  Future<UserProfileLaravel> getById(int id) async {
    final response = await http.get(
      _uri('/usuarios/$id'),
      headers: await _headers(),
    );
    if (response.statusCode != 200) {
      throw Exception('Usuario no encontrado (${response.statusCode}).');
    }
    final Map<String, dynamic> data =
        jsonDecode(response.body) as Map<String, dynamic>;
    return UserProfileLaravel.fromJson(data);
  }

  /// Crea un nuevo usuario (pensado para panel administrativo).
  Future<UserProfileLaravel> create({
    required String name,
    required String email,
    required String password,
  }) async {
    final response = await http.post(
      _uri('/usuarios'),
      headers: await _headers(),
      body: jsonEncode(<String, dynamic>{
        'name': name,
        'email': email,
        'password': password,
      }),
    );

    if (response.statusCode != 201) {
      throw Exception('No se pudo crear el usuario (${response.statusCode}).');
    }

    final Map<String, dynamic> data =
        jsonDecode(response.body) as Map<String, dynamic>;
    return UserProfileLaravel.fromJson(data);
  }

  /// Actualiza la informacion de un usuario existente.
  Future<UserProfileLaravel> update({
    required int id,
    String? name,
    String? email,
    String? password,
    String? photoUrl,
  }) async {
    final Map<String, dynamic> payload = <String, dynamic>{
      if (name != null) 'name': name,
      if (email != null) 'email': email,
      if (password != null) 'password': password,
      if (photoUrl != null) 'photo_url': photoUrl,
    };

    final response = await http.put(
      _uri('/usuarios/$id'),
      headers: await _headers(),
      body: jsonEncode(payload),
    );

    if (response.statusCode != 200) {
      throw Exception('No se pudo actualizar el usuario (${response.statusCode}).');
    }

    final Map<String, dynamic> data =
        jsonDecode(response.body) as Map<String, dynamic>;
    final UserProfileLaravel updated = UserProfileLaravel.fromJson(data);

    final UserProfileLaravel? cached = await AuthService.instance.getProfile();
    if (cached != null && cached.id == updated.id) {
      await AuthService.instance.getProfile(forceRefresh: true);
    }

    return updated;
  }

  /// Elimina un usuario del sistema.
  Future<void> delete(int id) async {
    final response = await http.delete(
      _uri('/usuarios/$id'),
      headers: await _headers(),
    );
    if (response.statusCode != 204) {
      throw Exception('No se pudo eliminar el usuario (${response.statusCode}).');
    }
  }

  /// Obtiene el perfil del usuario autenticado usando el servicio de auth.
  Future<UserProfileLaravel?> getCurrentProfile({bool forceRefresh = false}) {
    return AuthService.instance.getProfile(forceRefresh: forceRefresh);
  }
}
