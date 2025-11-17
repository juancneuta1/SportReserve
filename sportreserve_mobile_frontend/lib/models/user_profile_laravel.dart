class UserProfileLaravel {
  final int id;
  final String name;
  final String email;
  final String? photoUrl;
  final String? role; // ✅ Campo nuevo

  UserProfileLaravel({
    required this.id,
    required this.name,
    required this.email,
    this.photoUrl,
    this.role,
  });

  /// 🔹 Crea una instancia de UserProfileLaravel a partir del JSON recibido del backend Laravel
  factory UserProfileLaravel.fromJson(Map<String, dynamic> json) {
    return UserProfileLaravel(
      // Acepta 'id' como int o String
      id: json['id'] is String
          ? int.tryParse(json['id']) ?? 0
          : (json['id'] ?? 0),

      name: json['name'] ?? '',
      email: json['email'] ?? '',

      // Acepta tanto 'photo_url' (Laravel) como 'photoUrl' (camelCase)
      photoUrl: json['photo_url'] ?? json['photoUrl'] ?? '',

      // Acepta 'role' como String o como objeto { "id": 1, "name": "admin" }
      role: json['role'] is Map<String, dynamic>
          ? json['role']['name'] ?? 'usuario'
          : (json['role'] ?? 'usuario'),
    );
  }

  /// 🔹 Convierte la instancia en un mapa JSON (para guardar en cache local o enviar al backend)
  Map<String, dynamic> toJson() => {
    'id': id,
    'name': name,
    'email': email,
    'photo_url': photoUrl,
    'role': role,
  };

  /// 🔹 Retorna una copia modificada del perfil
  UserProfileLaravel copyWith({
    int? id,
    String? name,
    String? email,
    String? photoUrl,
    String? role,
  }) {
    return UserProfileLaravel(
      id: id ?? this.id,
      name: name ?? this.name,
      email: email ?? this.email,
      photoUrl: photoUrl ?? this.photoUrl,
      role: role ?? this.role,
    );
  }
}
