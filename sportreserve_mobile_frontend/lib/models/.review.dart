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
    return Review(
      id: json['id'] ?? 0,
      userId: json['user_id'] ?? 0,
      canchaId: json['cancha_id'] ?? 0,
      userName: json['user_name'] ?? json['user']?['name'] ?? 'Anónimo',
      rating: (json['rating'] ?? 0).toDouble(),
      comment: json['comment'] ?? '',
      createdAt: DateTime.tryParse(json['created_at'] ?? '') ?? DateTime.now(),
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
