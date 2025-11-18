class ReviewSummary {
  final int canchaId;
  final double average;
  final int total;

  ReviewSummary({
    required this.canchaId,
    required this.average,
    required this.total,
  });

  factory ReviewSummary.fromJson(Map<String, dynamic> json, [int canchaId = 0]) {
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

    final reviews = json['reviews'];
    final reviewsCount =
        (reviews is List) ? reviews.length : _parseInt(json['reviews_count']);
    final totalFromJson =
        _parseInt(json['count'] ?? json['total'] ?? json['total_reviews']);

    return ReviewSummary(
      canchaId: _parseInt(json['cancha_id'] ?? canchaId),
      average: _parseDouble(
        json['average'] ?? json['promedio'] ?? json['average_rating'],
      ),
      total: totalFromJson > 0 ? totalFromJson : reviewsCount,
    );
  }

  Map<String, dynamic> toJson() => {
        'cancha_id': canchaId,
        'average': average,
        'total': total,
      };
}
