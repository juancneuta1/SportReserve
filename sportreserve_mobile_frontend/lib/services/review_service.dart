import 'dart:convert';
import 'dart:typed_data';

import 'package:http/http.dart' as http;

import '../models/.review.dart';
import '../models/.review_summary.dart';
import '../models/user_profile_laravel.dart';
import 'auth_service.dart';

class ReviewService {
  ReviewService._();
  static final ReviewService instance = ReviewService._();

  static const String _baseUrl = 'http://10.0.2.2:8000/api';

  dynamic _decodeBody(Uint8List bytes) {
    String text = utf8.decode(bytes, allowMalformed: true);
    text = text.replaceFirst('\uFEFF', '').trim();
    return jsonDecode(text);
  }

  Uri _uri(String path, [Map<String, dynamic>? query]) {
    final uri = Uri.parse('$_baseUrl$path');
    if (query == null) return uri;
    return uri.replace(
      queryParameters: <String, String>{
        ...uri.queryParameters,
        ...query.map((k, v) => MapEntry(k, v.toString())),
      },
    );
  }

  Future<Map<String, String>> _headers() =>
      AuthService.instance.authenticatedJsonHeaders();

  Future<Map<String, String>> _headersOrPublic() async {
    try {
      return await _headers();
    } catch (_) {
      return const {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      };
    }
  }

  List<Review> _decodeReviews(dynamic payload) {
    dynamic source = payload;
    if (source is String) {
      source = jsonDecode(source);
    }

    if (source is Map<String, dynamic>) {
      final dynamic nested = source['reviews'] ??
          source['data'] ??
          source['calificaciones'] ??
          source['items'];
      if (nested is List) {
        source = nested;
      } else {
        return <Review>[];
      }
    }

    if (source is List) {
      return source
          .whereType<Map<String, dynamic>>()
          .map(Review.fromJson)
          .toList();
    }

    return <Review>[];
  }

  Future<Map<String, dynamic>> _fetchResumenPayload(
    int canchaId, {
    int? limit,
  }) async {
    final response = await http.get(
      _uri(
        '/canchas/$canchaId/calificaciones/resumen',
        limit != null ? {'limit': limit} : null,
      ),
      headers: await _headersOrPublic(),
    );

    if (response.statusCode == 404) {
      return {'average': 0, 'count': 0, 'reviews': <dynamic>[]};
    }

    if (response.statusCode != 200) {
      throw Exception(
        'No se pudo obtener el resumen (${response.statusCode}).',
      );
    }

    final dynamic decoded = _decodeBody(response.bodyBytes);
    return decoded is Map<String, dynamic> ? decoded : <String, dynamic>{};
  }

  Stream<ReviewSummary> watchSummary(int canchaId) {
    return Stream.fromFuture(_fetchSummary(canchaId));
  }

  Stream<List<Review>> watchRecentReviews(int canchaId, {int limit = 10}) {
    return Stream.fromFuture(
      _fetchRecentReviews(canchaId: canchaId, limit: limit),
    );
  }

  Future<ReviewSummary> _fetchSummary(int canchaId) async {
    final data = await _fetchResumenPayload(canchaId);
    final Map<String, dynamic> normalized =
        data is Map<String, dynamic> ? Map<String, dynamic>.from(data) : {};
    normalized.putIfAbsent('cancha_id', () => canchaId);
    return ReviewSummary.fromJson(normalized, canchaId);
  }

  Future<List<Review>> _fetchRecentReviews({
    required int canchaId,
    required int limit,
  }) async {
    final data = await _fetchResumenPayload(canchaId, limit: limit);
    return _decodeReviews(data);
  }

  Future<Review> create({
    required int canchaId,
    required double rating,
    required String comment,
  }) async {
    final UserProfileLaravel? profile =
        await AuthService.instance.getProfile();
    if (profile == null) {
      throw StateError('Debes iniciar sesion para enviar una resena.');
    }

    final response = await http.post(
      _uri('/canchas/$canchaId/calificaciones'),
      headers: await _headersOrPublic(),
      body: jsonEncode({
        'rating': rating,
        'estrellas': rating,
        'comentario': comment,
        'comment': comment,
        'user_id': profile.id,
      }),
    );

    if (response.statusCode != 201 && response.statusCode != 200) {
      throw Exception('No se pudo crear la resena (${response.statusCode}).');
    }

    final Map<String, dynamic> data =
        jsonDecode(response.body) as Map<String, dynamic>;
    return Review.fromJson(data);
  }

  Future<Review> update({
    required int id,
    double? rating,
    String? comment,
  }) async {
    final payload = <String, dynamic>{
      if (rating != null) 'rating': rating,
      if (rating != null) 'estrellas': rating,
      if (comment != null) 'comment': comment,
      if (comment != null) 'comentario': comment,
    };

    final response = await http.put(
      _uri('/calificaciones/$id'),
      headers: await _headersOrPublic(),
      body: jsonEncode(payload),
    );

    if (response.statusCode != 200) {
      throw Exception(
        'No se pudo actualizar la resena (${response.statusCode}).',
      );
    }

    final Map<String, dynamic> data =
        jsonDecode(response.body) as Map<String, dynamic>;
    return Review.fromJson(data);
  }

  Future<void> delete(int id) async {
    final response = await http.delete(
      _uri('/calificaciones/$id'),
      headers: await _headersOrPublic(),
    );
    if (response.statusCode != 204 && response.statusCode != 200) {
      throw Exception(
        'No se pudo eliminar la resena (${response.statusCode}).',
      );
    }
  }

  Future<void> submitReview({
    required int canchaId,
    required double rating,
    required String comment,
  }) async {
    await create(
      canchaId: canchaId,
      rating: rating,
      comment: comment,
    );
  }
}
