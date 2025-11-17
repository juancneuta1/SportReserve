import 'dart:convert';
import 'package:http/http.dart' as http;

import '../models/.review.dart';
import '../models/.review_summary.dart';
import '../models/user_profile_laravel.dart';
import 'auth_service.dart';

class ReviewService {
  ReviewService._();
  static final ReviewService instance = ReviewService._();

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

  List<Review> _decodeReviews(dynamic payload) {
    dynamic source = payload;
    if (source is String) {
      source = jsonDecode(source);
    }

    if (source is Map<String, dynamic>) {
      final dynamic nested =
          source['data'] ?? source['reviews'] ?? source['items'];
      if (nested is List) {
        source = nested;
      } else {
        return <Review>[];
      }
    }

    if (source is List) {
      return source
          .whereType<Map<String, dynamic>>()
          .map((item) => Review.fromJson(item))
          .toList();
    }

    return <Review>[];
  }

  Future<List<Review>> getAll() async {
    final response = await http.get(
      _uri('/resenas'),
      headers: await _headers(),
    );
    if (response.statusCode != 200) {
      throw Exception('Error al obtener reseñas (${response.statusCode}).');
    }
    return _decodeReviews(response.body);
  }

  Future<Review> getById(int id) async {
    final response = await http.get(
      _uri('/resenas/$id'),
      headers: await _headers(),
    );
    if (response.statusCode != 200) {
      throw Exception('Reseña no encontrada (${response.statusCode}).');
    }
    final Map<String, dynamic> data =
        jsonDecode(response.body) as Map<String, dynamic>;
    return Review.fromJson(data);
  }

  Future<Review> create({
    required int canchaId,
    required double rating,
    required String comment,
  }) async {
    final UserProfileLaravel? profile =
        await AuthService.instance.getProfile();
    if (profile == null) {
      throw StateError('Debes iniciar sesión para enviar una reseña.');
    }

    final response = await http.post(
      _uri('/resenas'),
      headers: await _headers(),
      body: jsonEncode({
        'cancha_id': canchaId,
        'user_id': profile.id,
        'rating': rating,
        'comment': comment,
      }),
    );

    if (response.statusCode != 201) {
      throw Exception('No se pudo crear la reseña (${response.statusCode}).');
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
      if (comment != null) 'comment': comment,
    };

    final response = await http.put(
      _uri('/resenas/$id'),
      headers: await _headers(),
      body: jsonEncode(payload),
    );

    if (response.statusCode != 200) {
      throw Exception(
          'No se pudo actualizar la reseña (${response.statusCode}).');
    }

    final Map<String, dynamic> data =
        jsonDecode(response.body) as Map<String, dynamic>;
    return Review.fromJson(data);
  }

  Future<void> delete(int id) async {
    final response = await http.delete(
      _uri('/resenas/$id'),
      headers: await _headers(),
    );
    if (response.statusCode != 204) {
      throw Exception(
          'No se pudo eliminar la reseña (${response.statusCode}).');
    }
  }

  Stream<ReviewSummary> watchSummary(int canchaId) {
    return Stream.fromFuture(_fetchSummary(canchaId));
  }

  Stream<List<Review>> watchRecentReviews(int canchaId, {int limit = 10}) {
    return Stream.fromFuture(_fetchRecentReviews(canchaId: canchaId, limit: limit));
  }

  Future<ReviewSummary> _fetchSummary(int canchaId) async {
    final response = await http.get(
      _uri('/resenas/canchas/$canchaId/resumen'),
      headers: await _headers(),
    );

    if (response.statusCode == 404) {
      return ReviewSummary(canchaId: canchaId, average: 0, total: 0);
    }

    if (response.statusCode != 200) {
      throw Exception(
          'No se pudo obtener el resumen (${response.statusCode}).');
    }

    final Map<String, dynamic> data =
        jsonDecode(response.body) as Map<String, dynamic>;
    return ReviewSummary.fromJson(data);
  }

  Future<List<Review>> _fetchRecentReviews({
    required int canchaId,
    required int limit,
  }) async {
    final response = await http.get(
      _uri('/resenas/canchas/$canchaId', {'limit': limit}),
      headers: await _headers(),
    );

    if (response.statusCode == 404) return <Review>[];
    if (response.statusCode != 200) {
      throw Exception(
          'No se pudieron obtener reseñas (${response.statusCode}).');
    }

    return _decodeReviews(response.body);
  }

  Future<void> submitReview({
  required int canchaId,
  required double rating,
  required String comment,
}) async {
  await create(canchaId: canchaId, rating: rating, comment: comment);
}

}
