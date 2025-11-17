import 'dart:io';
import 'package:drift/drift.dart';
import 'package:drift/native.dart';
import 'package:path/path.dart' as p;
import 'package:path_provider/path_provider.dart';

part 'app_database.g.dart';

/// 🟢 Definición de la tabla local de canchas
@DataClassName('CanchaLocal')
class Canchas extends Table {
  IntColumn get id => integer()(); // clave primaria obligatoria
  TextColumn get nombre => text()();
  TextColumn get tipo => text().nullable()();
  TextColumn get ubicacion => text().nullable()();
  RealColumn get latitud => real().nullable()();
  RealColumn get longitud => real().nullable()();
  TextColumn get descripcion => text().nullable()();
  BoolColumn get disponibilidad =>
      boolean().withDefault(const Constant(true))();
  TextColumn get servicios => text().nullable()();

  @override
  Set<Column> get primaryKey => {id};
}

/// 🗂 Base de datos local (Drift)
@DriftDatabase(tables: [Canchas])
class AppDatabase extends _$AppDatabase {
  AppDatabase._internal() : super(_openConnection());

  static final AppDatabase instance = AppDatabase._internal();

  factory AppDatabase() => instance;

  @override
  int get schemaVersion => 1;

  /// 💾 Guarda la lista de canchas en la base local
  Future<void> cachearCanchas(List<Map<String, dynamic>> data) async {
    await batch((batch) {
      batch.insertAllOnConflictUpdate(
        canchas,
        data.map((json) {
          final int idParsed = json['id'] is String
              ? int.tryParse(json['id']) ?? 0
              : (json['id'] ?? 0);

          // ⚙️ Construcción correcta del Companion:
          return CanchasCompanion(
            id: Value(idParsed),
            nombre: Value(json['nombre'] ?? 'Cancha sin nombre'),
            tipo: Value(json['tipo']),
            ubicacion: Value(json['ubicacion']),
            latitud: Value(
              (json['latitud'] is String)
                  ? double.tryParse(json['latitud']) ?? 0.0
                  : (json['latitud'] ?? 0.0).toDouble(),
            ),
            longitud: Value(
              (json['longitud'] is String)
                  ? double.tryParse(json['longitud']) ?? 0.0
                  : (json['longitud'] ?? 0.0).toDouble(),
            ),
            descripcion: Value(json['descripcion']),
            disponibilidad: Value(
              json['disponibilidad'] == true ||
                  json['disponibilidad'] == 1 ||
                  json['disponibilidad'] == '1',
            ),
            servicios: Value(json['servicios']),
          );
        }).toList(),
      );
    });
  }

  /// 📥 Obtiene todas las canchas almacenadas localmente
  Future<List<CanchaLocal>> obtenerCanchas() => select(canchas).get();

  /// 🧹 Limpia toda la caché local
  Future<void> limpiarCache() => delete(canchas).go();
}

/// ⚙️ Inicializa la base local en un archivo físico
LazyDatabase _openConnection() {
  return LazyDatabase(() async {
    final dir = await getApplicationDocumentsDirectory();
    final file = File(p.join(dir.path, 'app_cache.db'));
    return NativeDatabase(file);
  });
}
