<div class="row g-3 mt-2">
    <div class="col-12">
        <h6 class="text-uppercase text-muted small mb-1">Información general</h6>
    </div>
    <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="{{ old('nombre', $cancha->nombre) }}"
            placeholder="Ej: Cancha sintética 5 vs 5" required>
        <small class="text-muted">Nombre visible para los usuarios.</small>
    </div>
    @php
        $tiposDisponibles = [
            'Fútbol 5',
            'Fútbol 7',
            'Fútbol 9',
            'Fútbol 11',
            'Pádel',
            'Vóley playa',
        ];
        $tiposSeleccionados = collect(old('tipo', $cancha->tipo ?? []))->map(fn ($v) => trim($v))->filter()->all();
    @endphp
    <div class="col-md-6">
        <label class="form-label">Tipo</label>
        <select name="tipo[]" id="tipo" class="form-select" multiple required>
            @foreach ($tiposDisponibles as $tipo)
                <option value="{{ $tipo }}" @selected(in_array($tipo, $tiposSeleccionados))>{{ $tipo }}</option>
            @endforeach
        </select>
        <small class="text-muted">Selecciona uno o varios deportes (Ctrl/Cmd + clic). Desplegable tipo menú.</small>
    </div>

    <div class="col-md-4">
        <label class="form-label">Precio por hora (COP)</label>
        <input type="number" name="precio_por_hora" class="form-control"
            value="{{ old('precio_por_hora', $cancha->precio_por_hora) }}"
            placeholder="Ej: 90000" min="0" step="1000" required>
        <small class="text-muted">Monto total por hora.</small>
    </div>
    <div class="col-md-8">
        <label class="form-label">Servicios (opcional)</label>
        <input type="text" name="servicios" class="form-control" value="{{ old('servicios', $cancha->servicios) }}"
            placeholder="Ej: Parqueadero, cafetería, arriendo de balones">
        <small class="text-muted">Lista separada por comas.</small>
    </div>

    <div class="col-12 mt-2">
        <h6 class="text-uppercase text-muted small mb-1">Ubicación</h6>
    </div>
    <div class="col-md-6">
        <label class="form-label">Ubicación</label>
        <input type="text" name="ubicacion" class="form-control" value="{{ old('ubicacion', $cancha->ubicacion) }}"
            placeholder="Ej: Cra 7 #123, Bogotá" required>
        <small class="text-muted">Dirección o punto de referencia.</small>
    </div>
    <div class="col-md-3">
        <label class="form-label">Latitud</label>
        <input type="number" step="0.0000001" name="latitud" class="form-control"
            value="{{ old('latitud', $cancha->latitud) }}" placeholder="Ej: 4.7111" min="-90" max="90" required>
    </div>
    <div class="col-md-3">
        <label class="form-label">Longitud</label>
        <input type="number" step="0.0000001" name="longitud" class="form-control"
            value="{{ old('longitud', $cancha->longitud) }}" placeholder="Ej: -74.0721" min="-180" max="180" required>
    </div>

    <div class="col-12 mt-2">
        <h6 class="text-uppercase text-muted small mb-1">Descripción</h6>
    </div>
    <div class="col-12">
        <label class="form-label">Descripción</label>
        <textarea name="descripcion" class="form-control" rows="3" placeholder="Ej: Cancha sintética techada con gradería, luces y camerinos.">{{ old('descripcion', $cancha->descripcion) }}</textarea>
    </div>

    <div class="col-12 mt-2 d-none">
        <h6 class="text-uppercase text-muted small mb-1">Imagen y disponibilidad</h6>
    </div>
    <div class="col-md-8 d-none">
        <label class="form-label">Imagen (opcional)</label>
        <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*">
        <small class="text-muted">Formatos: JPG, PNG. Tamaño recomendado 1200x800.</small>
        <div class="mt-3 p-2 border rounded-3 bg-light d-flex align-items-center gap-3">
            <div class="ratio ratio-16x9" style="max-width: 280px;">
                <img id="imagen-preview" src="{{ $cancha->imagen ?: 'https://via.placeholder.com/640x360?text=Sin+imagen' }}"
                    alt="Previsualización de imagen" class="rounded-3 w-100 h-100 object-fit-cover">
            </div>
            <div class="small text-muted">
                Previsualización rápida. La imagen final se cargará al guardar.
            </div>
        </div>
    </div>
    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" name="disponibilidad" id="disponibilidad"
                {{ old('disponibilidad', $cancha->disponibilidad) ? 'checked' : '' }}>
            <label class="form-check-label fw-semibold" for="disponibilidad">Disponible</label>
            <small class="text-muted d-block">Muestra la cancha como reservable.</small>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const fileInput = document.getElementById('imagen');
        const preview = document.getElementById('imagen-preview');

        if (!fileInput || !preview) return;

        fileInput.addEventListener('change', function (event) {
            const [file] = event.target.files;
            if (file) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target?.result;
                };
                reader.readAsDataURL(file);
            }
        });
    });
</script>
@endpush
