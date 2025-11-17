class ReviewSummary {
  final int canchaId;
  final double average;
  final int total;

  ReviewSummary({
    required this.canchaId,
    required this.average,
    required this.total,
  });

  factory ReviewSummary.fromJson(Map<String, dynamic> json) {
    return ReviewSummary(
      canchaId: json['cancha_id'] ?? 0,
      average: (json['average'] ?? json['average_rating'] ?? 0).toDouble(),
      total: json['total'] ?? json['total_reviews'] ?? 0,
    );
  }

  Map<String, dynamic> toJson() => {
        'cancha_id': canchaId,
        'average': average,
        'total': total,
      };
}
