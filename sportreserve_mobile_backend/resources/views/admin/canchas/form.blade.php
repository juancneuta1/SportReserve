<div class="row g-3 mt-2">
    <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $cancha->nombre) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Tipo</label>
        <input type="text" name="tipo" class="form-control" value="{{ old('tipo', $cancha->tipo) }}" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Ubicación</label>
        <input type="text" name="ubicacion" class="form-control" value="{{ old('ubicacion', $cancha->ubicacion) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Latitud</label>
        <input type="number" step="any" name="latitud" class="form-control" value="{{ old('latitud', $cancha->latitud) }}" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Longitud</label>
        <input type="number" step="any" name="longitud" class="form-control" value="{{ old('longitud', $cancha->longitud) }}" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Precio por hora (COP)</label>
        <input type="number" name="precio_por_hora" class="form-control" value="{{ old('precio_por_hora', $cancha->precio_por_hora) }}" required>
    </div>
    <div class="col-md-8">
        <label class="form-label">Servicios (opcional)</label>
        <input type="text" name="servicios" class="form-control" value="{{ old('servicios', $cancha->servicios) }}">
    </div>
    <div class="col-12">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $cancha->descripcion) }}</textarea>
    </div>
    <div class="col-md-6">
        <label class="form-label">Imagen (opcional)</label>
        <input type="file" name="imagen" class="form-control">
        @if($cancha->imagen)
            <img src="{{ $cancha->imagen }}" alt="imagen cancha" class="mt-2" width="100">
        @endif
    </div>
    <div class="col-md-6 d-flex align-items-end">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="disponibilidad" id="disponibilidad"
                {{ old('disponibilidad', $cancha->disponibilidad) ? 'checked' : '' }}>
            <label class="form-check-label" for="disponibilidad">Disponible</label>
        </div>
    </div>
</div>
