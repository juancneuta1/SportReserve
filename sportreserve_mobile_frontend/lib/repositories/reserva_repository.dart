import 'package:sportreserve_mobile_frontend/db/app_database.dart';

class ReservaRepository {
  final AppDatabase db;
  ReservaRepository(this.db);

  /// 🔹 Obtiene la lista de canchas guardadas localmente (cache)
  Future<List<CanchaLocal>> listarCanchas() => db.obtenerCanchas();
}
