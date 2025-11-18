class Review {
  final int id;
  final int userId;
  final int canchaId;
  final String userName;
  final double rating;
  final String comment;
  final DateTime createdAt;

  Review({
    required this.id,
    required this.userId,
    required this.canchaId,
    required this.userName,
    required this.rating,
    required this.comment,
    required this.createdAt,
  });

  factory Review.fromJson(Map<String, dynamic> json) {
    double _parseDouble(dynamic value) {
      if (value is num) return value.toDouble();
      if (value is String) return double.tryParse(value) ?? 0;
      return 0;
    }

    int _parseInt(dynamic value) {
      if (value is int) return value;
      if (value is num) return value.toInt();
      if (value is String) return int.tryParse(value) ?? 0;
      return 0;
    }

    final user = (json['user'] is Map<String, dynamic>)
        ? json['user'] as Map<String, dynamic>
        : null;

    return Review(
      id: _parseInt(json['id']),
      userId: _parseInt(json['user_id'] ?? user?['id']),
      canchaId: _parseInt(json['cancha_id']),
      userName: (json['user_name'] ??
              user?['name'] ??
              user?['nombre'] ??
              'Anonimo')
          .toString(),
      rating: _parseDouble(json['rating'] ?? json['estrellas']),
      comment: (json['comment'] ?? json['comentario'] ?? '').toString(),
      createdAt: DateTime.tryParse(
            json['created_at'] ??
                json['updated_at'] ??
                json['fecha']?.toString() ??
                '',
          ) ??
          DateTime.now(),
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'user_id': userId,
        'cancha_id': canchaId,
        'user_name': userName,
        'rating': rating,
        'comment': comment,
        'created_at': createdAt.toIso8601String(),
      };
}
