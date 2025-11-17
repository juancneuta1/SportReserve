// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'app_database.dart';

// ignore_for_file: type=lint
class $CanchasTable extends Canchas with TableInfo<$CanchasTable, CanchaLocal> {
  @override
  final GeneratedDatabase attachedDatabase;
  final String? _alias;
  $CanchasTable(this.attachedDatabase, [this._alias]);
  static const VerificationMeta _idMeta = const VerificationMeta('id');
  @override
  late final GeneratedColumn<int> id = GeneratedColumn<int>(
    'id',
    aliasedName,
    false,
    type: DriftSqlType.int,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _nombreMeta = const VerificationMeta('nombre');
  @override
  late final GeneratedColumn<String> nombre = GeneratedColumn<String>(
    'nombre',
    aliasedName,
    false,
    type: DriftSqlType.string,
    requiredDuringInsert: true,
  );
  static const VerificationMeta _tipoMeta = const VerificationMeta('tipo');
  @override
  late final GeneratedColumn<String> tipo = GeneratedColumn<String>(
    'tipo',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _ubicacionMeta = const VerificationMeta(
    'ubicacion',
  );
  @override
  late final GeneratedColumn<String> ubicacion = GeneratedColumn<String>(
    'ubicacion',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _latitudMeta = const VerificationMeta(
    'latitud',
  );
  @override
  late final GeneratedColumn<double> latitud = GeneratedColumn<double>(
    'latitud',
    aliasedName,
    true,
    type: DriftSqlType.double,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _longitudMeta = const VerificationMeta(
    'longitud',
  );
  @override
  late final GeneratedColumn<double> longitud = GeneratedColumn<double>(
    'longitud',
    aliasedName,
    true,
    type: DriftSqlType.double,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _descripcionMeta = const VerificationMeta(
    'descripcion',
  );
  @override
  late final GeneratedColumn<String> descripcion = GeneratedColumn<String>(
    'descripcion',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  static const VerificationMeta _disponibilidadMeta = const VerificationMeta(
    'disponibilidad',
  );
  @override
  late final GeneratedColumn<bool> disponibilidad = GeneratedColumn<bool>(
    'disponibilidad',
    aliasedName,
    false,
    type: DriftSqlType.bool,
    requiredDuringInsert: false,
    defaultConstraints: GeneratedColumn.constraintIsAlways(
      'CHECK ("disponibilidad" IN (0, 1))',
    ),
    defaultValue: const Constant(true),
  );
  static const VerificationMeta _serviciosMeta = const VerificationMeta(
    'servicios',
  );
  @override
  late final GeneratedColumn<String> servicios = GeneratedColumn<String>(
    'servicios',
    aliasedName,
    true,
    type: DriftSqlType.string,
    requiredDuringInsert: false,
  );
  @override
  List<GeneratedColumn> get $columns => [
    id,
    nombre,
    tipo,
    ubicacion,
    latitud,
    longitud,
    descripcion,
    disponibilidad,
    servicios,
  ];
  @override
  String get aliasedName => _alias ?? actualTableName;
  @override
  String get actualTableName => $name;
  static const String $name = 'canchas';
  @override
  VerificationContext validateIntegrity(
    Insertable<CanchaLocal> instance, {
    bool isInserting = false,
  }) {
    final context = VerificationContext();
    final data = instance.toColumns(true);
    if (data.containsKey('id')) {
      context.handle(_idMeta, id.isAcceptableOrUnknown(data['id']!, _idMeta));
    }
    if (data.containsKey('nombre')) {
      context.handle(
        _nombreMeta,
        nombre.isAcceptableOrUnknown(data['nombre']!, _nombreMeta),
      );
    } else if (isInserting) {
      context.missing(_nombreMeta);
    }
    if (data.containsKey('tipo')) {
      context.handle(
        _tipoMeta,
        tipo.isAcceptableOrUnknown(data['tipo']!, _tipoMeta),
      );
    }
    if (data.containsKey('ubicacion')) {
      context.handle(
        _ubicacionMeta,
        ubicacion.isAcceptableOrUnknown(data['ubicacion']!, _ubicacionMeta),
      );
    }
    if (data.containsKey('latitud')) {
      context.handle(
        _latitudMeta,
        latitud.isAcceptableOrUnknown(data['latitud']!, _latitudMeta),
      );
    }
    if (data.containsKey('longitud')) {
      context.handle(
        _longitudMeta,
        longitud.isAcceptableOrUnknown(data['longitud']!, _longitudMeta),
      );
    }
    if (data.containsKey('descripcion')) {
      context.handle(
        _descripcionMeta,
        descripcion.isAcceptableOrUnknown(
          data['descripcion']!,
          _descripcionMeta,
        ),
      );
    }
    if (data.containsKey('disponibilidad')) {
      context.handle(
        _disponibilidadMeta,
        disponibilidad.isAcceptableOrUnknown(
          data['disponibilidad']!,
          _disponibilidadMeta,
        ),
      );
    }
    if (data.containsKey('servicios')) {
      context.handle(
        _serviciosMeta,
        servicios.isAcceptableOrUnknown(data['servicios']!, _serviciosMeta),
      );
    }
    return context;
  }

  @override
  Set<GeneratedColumn> get $primaryKey => {id};
  @override
  CanchaLocal map(Map<String, dynamic> data, {String? tablePrefix}) {
    final effectivePrefix = tablePrefix != null ? '$tablePrefix.' : '';
    return CanchaLocal(
      id: attachedDatabase.typeMapping.read(
        DriftSqlType.int,
        data['${effectivePrefix}id'],
      )!,
      nombre: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}nombre'],
      )!,
      tipo: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}tipo'],
      ),
      ubicacion: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}ubicacion'],
      ),
      latitud: attachedDatabase.typeMapping.read(
        DriftSqlType.double,
        data['${effectivePrefix}latitud'],
      ),
      longitud: attachedDatabase.typeMapping.read(
        DriftSqlType.double,
        data['${effectivePrefix}longitud'],
      ),
      descripcion: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}descripcion'],
      ),
      disponibilidad: attachedDatabase.typeMapping.read(
        DriftSqlType.bool,
        data['${effectivePrefix}disponibilidad'],
      )!,
      servicios: attachedDatabase.typeMapping.read(
        DriftSqlType.string,
        data['${effectivePrefix}servicios'],
      ),
    );
  }

  @override
  $CanchasTable createAlias(String alias) {
    return $CanchasTable(attachedDatabase, alias);
  }
}

class CanchaLocal extends DataClass implements Insertable<CanchaLocal> {
  final int id;
  final String nombre;
  final String? tipo;
  final String? ubicacion;
  final double? latitud;
  final double? longitud;
  final String? descripcion;
  final bool disponibilidad;
  final String? servicios;
  const CanchaLocal({
    required this.id,
    required this.nombre,
    this.tipo,
    this.ubicacion,
    this.latitud,
    this.longitud,
    this.descripcion,
    required this.disponibilidad,
    this.servicios,
  });
  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    map['id'] = Variable<int>(id);
    map['nombre'] = Variable<String>(nombre);
    if (!nullToAbsent || tipo != null) {
      map['tipo'] = Variable<String>(tipo);
    }
    if (!nullToAbsent || ubicacion != null) {
      map['ubicacion'] = Variable<String>(ubicacion);
    }
    if (!nullToAbsent || latitud != null) {
      map['latitud'] = Variable<double>(latitud);
    }
    if (!nullToAbsent || longitud != null) {
      map['longitud'] = Variable<double>(longitud);
    }
    if (!nullToAbsent || descripcion != null) {
      map['descripcion'] = Variable<String>(descripcion);
    }
    map['disponibilidad'] = Variable<bool>(disponibilidad);
    if (!nullToAbsent || servicios != null) {
      map['servicios'] = Variable<String>(servicios);
    }
    return map;
  }

  CanchasCompanion toCompanion(bool nullToAbsent) {
    return CanchasCompanion(
      id: Value(id),
      nombre: Value(nombre),
      tipo: tipo == null && nullToAbsent ? const Value.absent() : Value(tipo),
      ubicacion: ubicacion == null && nullToAbsent
          ? const Value.absent()
          : Value(ubicacion),
      latitud: latitud == null && nullToAbsent
          ? const Value.absent()
          : Value(latitud),
      longitud: longitud == null && nullToAbsent
          ? const Value.absent()
          : Value(longitud),
      descripcion: descripcion == null && nullToAbsent
          ? const Value.absent()
          : Value(descripcion),
      disponibilidad: Value(disponibilidad),
      servicios: servicios == null && nullToAbsent
          ? const Value.absent()
          : Value(servicios),
    );
  }

  factory CanchaLocal.fromJson(
    Map<String, dynamic> json, {
    ValueSerializer? serializer,
  }) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return CanchaLocal(
      id: serializer.fromJson<int>(json['id']),
      nombre: serializer.fromJson<String>(json['nombre']),
      tipo: serializer.fromJson<String?>(json['tipo']),
      ubicacion: serializer.fromJson<String?>(json['ubicacion']),
      latitud: serializer.fromJson<double?>(json['latitud']),
      longitud: serializer.fromJson<double?>(json['longitud']),
      descripcion: serializer.fromJson<String?>(json['descripcion']),
      disponibilidad: serializer.fromJson<bool>(json['disponibilidad']),
      servicios: serializer.fromJson<String?>(json['servicios']),
    );
  }
  @override
  Map<String, dynamic> toJson({ValueSerializer? serializer}) {
    serializer ??= driftRuntimeOptions.defaultSerializer;
    return <String, dynamic>{
      'id': serializer.toJson<int>(id),
      'nombre': serializer.toJson<String>(nombre),
      'tipo': serializer.toJson<String?>(tipo),
      'ubicacion': serializer.toJson<String?>(ubicacion),
      'latitud': serializer.toJson<double?>(latitud),
      'longitud': serializer.toJson<double?>(longitud),
      'descripcion': serializer.toJson<String?>(descripcion),
      'disponibilidad': serializer.toJson<bool>(disponibilidad),
      'servicios': serializer.toJson<String?>(servicios),
    };
  }

  CanchaLocal copyWith({
    int? id,
    String? nombre,
    Value<String?> tipo = const Value.absent(),
    Value<String?> ubicacion = const Value.absent(),
    Value<double?> latitud = const Value.absent(),
    Value<double?> longitud = const Value.absent(),
    Value<String?> descripcion = const Value.absent(),
    bool? disponibilidad,
    Value<String?> servicios = const Value.absent(),
  }) => CanchaLocal(
    id: id ?? this.id,
    nombre: nombre ?? this.nombre,
    tipo: tipo.present ? tipo.value : this.tipo,
    ubicacion: ubicacion.present ? ubicacion.value : this.ubicacion,
    latitud: latitud.present ? latitud.value : this.latitud,
    longitud: longitud.present ? longitud.value : this.longitud,
    descripcion: descripcion.present ? descripcion.value : this.descripcion,
    disponibilidad: disponibilidad ?? this.disponibilidad,
    servicios: servicios.present ? servicios.value : this.servicios,
  );
  CanchaLocal copyWithCompanion(CanchasCompanion data) {
    return CanchaLocal(
      id: data.id.present ? data.id.value : this.id,
      nombre: data.nombre.present ? data.nombre.value : this.nombre,
      tipo: data.tipo.present ? data.tipo.value : this.tipo,
      ubicacion: data.ubicacion.present ? data.ubicacion.value : this.ubicacion,
      latitud: data.latitud.present ? data.latitud.value : this.latitud,
      longitud: data.longitud.present ? data.longitud.value : this.longitud,
      descripcion: data.descripcion.present
          ? data.descripcion.value
          : this.descripcion,
      disponibilidad: data.disponibilidad.present
          ? data.disponibilidad.value
          : this.disponibilidad,
      servicios: data.servicios.present ? data.servicios.value : this.servicios,
    );
  }

  @override
  String toString() {
    return (StringBuffer('CanchaLocal(')
          ..write('id: $id, ')
          ..write('nombre: $nombre, ')
          ..write('tipo: $tipo, ')
          ..write('ubicacion: $ubicacion, ')
          ..write('latitud: $latitud, ')
          ..write('longitud: $longitud, ')
          ..write('descripcion: $descripcion, ')
          ..write('disponibilidad: $disponibilidad, ')
          ..write('servicios: $servicios')
          ..write(')'))
        .toString();
  }

  @override
  int get hashCode => Object.hash(
    id,
    nombre,
    tipo,
    ubicacion,
    latitud,
    longitud,
    descripcion,
    disponibilidad,
    servicios,
  );
  @override
  bool operator ==(Object other) =>
      identical(this, other) ||
      (other is CanchaLocal &&
          other.id == this.id &&
          other.nombre == this.nombre &&
          other.tipo == this.tipo &&
          other.ubicacion == this.ubicacion &&
          other.latitud == this.latitud &&
          other.longitud == this.longitud &&
          other.descripcion == this.descripcion &&
          other.disponibilidad == this.disponibilidad &&
          other.servicios == this.servicios);
}

class CanchasCompanion extends UpdateCompanion<CanchaLocal> {
  final Value<int> id;
  final Value<String> nombre;
  final Value<String?> tipo;
  final Value<String?> ubicacion;
  final Value<double?> latitud;
  final Value<double?> longitud;
  final Value<String?> descripcion;
  final Value<bool> disponibilidad;
  final Value<String?> servicios;
  const CanchasCompanion({
    this.id = const Value.absent(),
    this.nombre = const Value.absent(),
    this.tipo = const Value.absent(),
    this.ubicacion = const Value.absent(),
    this.latitud = const Value.absent(),
    this.longitud = const Value.absent(),
    this.descripcion = const Value.absent(),
    this.disponibilidad = const Value.absent(),
    this.servicios = const Value.absent(),
  });
  CanchasCompanion.insert({
    this.id = const Value.absent(),
    required String nombre,
    this.tipo = const Value.absent(),
    this.ubicacion = const Value.absent(),
    this.latitud = const Value.absent(),
    this.longitud = const Value.absent(),
    this.descripcion = const Value.absent(),
    this.disponibilidad = const Value.absent(),
    this.servicios = const Value.absent(),
  }) : nombre = Value(nombre);
  static Insertable<CanchaLocal> custom({
    Expression<int>? id,
    Expression<String>? nombre,
    Expression<String>? tipo,
    Expression<String>? ubicacion,
    Expression<double>? latitud,
    Expression<double>? longitud,
    Expression<String>? descripcion,
    Expression<bool>? disponibilidad,
    Expression<String>? servicios,
  }) {
    return RawValuesInsertable({
      if (id != null) 'id': id,
      if (nombre != null) 'nombre': nombre,
      if (tipo != null) 'tipo': tipo,
      if (ubicacion != null) 'ubicacion': ubicacion,
      if (latitud != null) 'latitud': latitud,
      if (longitud != null) 'longitud': longitud,
      if (descripcion != null) 'descripcion': descripcion,
      if (disponibilidad != null) 'disponibilidad': disponibilidad,
      if (servicios != null) 'servicios': servicios,
    });
  }

  CanchasCompanion copyWith({
    Value<int>? id,
    Value<String>? nombre,
    Value<String?>? tipo,
    Value<String?>? ubicacion,
    Value<double?>? latitud,
    Value<double?>? longitud,
    Value<String?>? descripcion,
    Value<bool>? disponibilidad,
    Value<String?>? servicios,
  }) {
    return CanchasCompanion(
      id: id ?? this.id,
      nombre: nombre ?? this.nombre,
      tipo: tipo ?? this.tipo,
      ubicacion: ubicacion ?? this.ubicacion,
      latitud: latitud ?? this.latitud,
      longitud: longitud ?? this.longitud,
      descripcion: descripcion ?? this.descripcion,
      disponibilidad: disponibilidad ?? this.disponibilidad,
      servicios: servicios ?? this.servicios,
    );
  }

  @override
  Map<String, Expression> toColumns(bool nullToAbsent) {
    final map = <String, Expression>{};
    if (id.present) {
      map['id'] = Variable<int>(id.value);
    }
    if (nombre.present) {
      map['nombre'] = Variable<String>(nombre.value);
    }
    if (tipo.present) {
      map['tipo'] = Variable<String>(tipo.value);
    }
    if (ubicacion.present) {
      map['ubicacion'] = Variable<String>(ubicacion.value);
    }
    if (latitud.present) {
      map['latitud'] = Variable<double>(latitud.value);
    }
    if (longitud.present) {
      map['longitud'] = Variable<double>(longitud.value);
    }
    if (descripcion.present) {
      map['descripcion'] = Variable<String>(descripcion.value);
    }
    if (disponibilidad.present) {
      map['disponibilidad'] = Variable<bool>(disponibilidad.value);
    }
    if (servicios.present) {
      map['servicios'] = Variable<String>(servicios.value);
    }
    return map;
  }

  @override
  String toString() {
    return (StringBuffer('CanchasCompanion(')
          ..write('id: $id, ')
          ..write('nombre: $nombre, ')
          ..write('tipo: $tipo, ')
          ..write('ubicacion: $ubicacion, ')
          ..write('latitud: $latitud, ')
          ..write('longitud: $longitud, ')
          ..write('descripcion: $descripcion, ')
          ..write('disponibilidad: $disponibilidad, ')
          ..write('servicios: $servicios')
          ..write(')'))
        .toString();
  }
}

abstract class _$AppDatabase extends GeneratedDatabase {
  _$AppDatabase(QueryExecutor e) : super(e);
  $AppDatabaseManager get managers => $AppDatabaseManager(this);
  late final $CanchasTable canchas = $CanchasTable(this);
  @override
  Iterable<TableInfo<Table, Object?>> get allTables =>
      allSchemaEntities.whereType<TableInfo<Table, Object?>>();
  @override
  List<DatabaseSchemaEntity> get allSchemaEntities => [canchas];
}

typedef $$CanchasTableCreateCompanionBuilder =
    CanchasCompanion Function({
      Value<int> id,
      required String nombre,
      Value<String?> tipo,
      Value<String?> ubicacion,
      Value<double?> latitud,
      Value<double?> longitud,
      Value<String?> descripcion,
      Value<bool> disponibilidad,
      Value<String?> servicios,
    });
typedef $$CanchasTableUpdateCompanionBuilder =
    CanchasCompanion Function({
      Value<int> id,
      Value<String> nombre,
      Value<String?> tipo,
      Value<String?> ubicacion,
      Value<double?> latitud,
      Value<double?> longitud,
      Value<String?> descripcion,
      Value<bool> disponibilidad,
      Value<String?> servicios,
    });

class $$CanchasTableFilterComposer
    extends Composer<_$AppDatabase, $CanchasTable> {
  $$CanchasTableFilterComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnFilters<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get nombre => $composableBuilder(
    column: $table.nombre,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get tipo => $composableBuilder(
    column: $table.tipo,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get ubicacion => $composableBuilder(
    column: $table.ubicacion,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<double> get latitud => $composableBuilder(
    column: $table.latitud,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<double> get longitud => $composableBuilder(
    column: $table.longitud,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get descripcion => $composableBuilder(
    column: $table.descripcion,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<bool> get disponibilidad => $composableBuilder(
    column: $table.disponibilidad,
    builder: (column) => ColumnFilters(column),
  );

  ColumnFilters<String> get servicios => $composableBuilder(
    column: $table.servicios,
    builder: (column) => ColumnFilters(column),
  );
}

class $$CanchasTableOrderingComposer
    extends Composer<_$AppDatabase, $CanchasTable> {
  $$CanchasTableOrderingComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  ColumnOrderings<int> get id => $composableBuilder(
    column: $table.id,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get nombre => $composableBuilder(
    column: $table.nombre,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get tipo => $composableBuilder(
    column: $table.tipo,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get ubicacion => $composableBuilder(
    column: $table.ubicacion,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<double> get latitud => $composableBuilder(
    column: $table.latitud,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<double> get longitud => $composableBuilder(
    column: $table.longitud,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get descripcion => $composableBuilder(
    column: $table.descripcion,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<bool> get disponibilidad => $composableBuilder(
    column: $table.disponibilidad,
    builder: (column) => ColumnOrderings(column),
  );

  ColumnOrderings<String> get servicios => $composableBuilder(
    column: $table.servicios,
    builder: (column) => ColumnOrderings(column),
  );
}

class $$CanchasTableAnnotationComposer
    extends Composer<_$AppDatabase, $CanchasTable> {
  $$CanchasTableAnnotationComposer({
    required super.$db,
    required super.$table,
    super.joinBuilder,
    super.$addJoinBuilderToRootComposer,
    super.$removeJoinBuilderFromRootComposer,
  });
  GeneratedColumn<int> get id =>
      $composableBuilder(column: $table.id, builder: (column) => column);

  GeneratedColumn<String> get nombre =>
      $composableBuilder(column: $table.nombre, builder: (column) => column);

  GeneratedColumn<String> get tipo =>
      $composableBuilder(column: $table.tipo, builder: (column) => column);

  GeneratedColumn<String> get ubicacion =>
      $composableBuilder(column: $table.ubicacion, builder: (column) => column);

  GeneratedColumn<double> get latitud =>
      $composableBuilder(column: $table.latitud, builder: (column) => column);

  GeneratedColumn<double> get longitud =>
      $composableBuilder(column: $table.longitud, builder: (column) => column);

  GeneratedColumn<String> get descripcion => $composableBuilder(
    column: $table.descripcion,
    builder: (column) => column,
  );

  GeneratedColumn<bool> get disponibilidad => $composableBuilder(
    column: $table.disponibilidad,
    builder: (column) => column,
  );

  GeneratedColumn<String> get servicios =>
      $composableBuilder(column: $table.servicios, builder: (column) => column);
}

class $$CanchasTableTableManager
    extends
        RootTableManager<
          _$AppDatabase,
          $CanchasTable,
          CanchaLocal,
          $$CanchasTableFilterComposer,
          $$CanchasTableOrderingComposer,
          $$CanchasTableAnnotationComposer,
          $$CanchasTableCreateCompanionBuilder,
          $$CanchasTableUpdateCompanionBuilder,
          (
            CanchaLocal,
            BaseReferences<_$AppDatabase, $CanchasTable, CanchaLocal>,
          ),
          CanchaLocal,
          PrefetchHooks Function()
        > {
  $$CanchasTableTableManager(_$AppDatabase db, $CanchasTable table)
    : super(
        TableManagerState(
          db: db,
          table: table,
          createFilteringComposer: () =>
              $$CanchasTableFilterComposer($db: db, $table: table),
          createOrderingComposer: () =>
              $$CanchasTableOrderingComposer($db: db, $table: table),
          createComputedFieldComposer: () =>
              $$CanchasTableAnnotationComposer($db: db, $table: table),
          updateCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                Value<String> nombre = const Value.absent(),
                Value<String?> tipo = const Value.absent(),
                Value<String?> ubicacion = const Value.absent(),
                Value<double?> latitud = const Value.absent(),
                Value<double?> longitud = const Value.absent(),
                Value<String?> descripcion = const Value.absent(),
                Value<bool> disponibilidad = const Value.absent(),
                Value<String?> servicios = const Value.absent(),
              }) => CanchasCompanion(
                id: id,
                nombre: nombre,
                tipo: tipo,
                ubicacion: ubicacion,
                latitud: latitud,
                longitud: longitud,
                descripcion: descripcion,
                disponibilidad: disponibilidad,
                servicios: servicios,
              ),
          createCompanionCallback:
              ({
                Value<int> id = const Value.absent(),
                required String nombre,
                Value<String?> tipo = const Value.absent(),
                Value<String?> ubicacion = const Value.absent(),
                Value<double?> latitud = const Value.absent(),
                Value<double?> longitud = const Value.absent(),
                Value<String?> descripcion = const Value.absent(),
                Value<bool> disponibilidad = const Value.absent(),
                Value<String?> servicios = const Value.absent(),
              }) => CanchasCompanion.insert(
                id: id,
                nombre: nombre,
                tipo: tipo,
                ubicacion: ubicacion,
                latitud: latitud,
                longitud: longitud,
                descripcion: descripcion,
                disponibilidad: disponibilidad,
                servicios: servicios,
              ),
          withReferenceMapper: (p0) => p0
              .map((e) => (e.readTable(table), BaseReferences(db, table, e)))
              .toList(),
          prefetchHooksCallback: null,
        ),
      );
}

typedef $$CanchasTableProcessedTableManager =
    ProcessedTableManager<
      _$AppDatabase,
      $CanchasTable,
      CanchaLocal,
      $$CanchasTableFilterComposer,
      $$CanchasTableOrderingComposer,
      $$CanchasTableAnnotationComposer,
      $$CanchasTableCreateCompanionBuilder,
      $$CanchasTableUpdateCompanionBuilder,
      (CanchaLocal, BaseReferences<_$AppDatabase, $CanchasTable, CanchaLocal>),
      CanchaLocal,
      PrefetchHooks Function()
    >;

class $AppDatabaseManager {
  final _$AppDatabase _db;
  $AppDatabaseManager(this._db);
  $$CanchasTableTableManager get canchas =>
      $$CanchasTableTableManager(_db, _db.canchas);
}
