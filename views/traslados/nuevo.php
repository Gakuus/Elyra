<?php $titulo = 'Nuevo traslado'; ?>
<?php ob_start(); ?>
<h2>Nuevo traslado</h2>
<form>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Conductor</label>
            <select name="conductor" class="form-select" required></select>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Copiloto</label>
            <input type="text" name="copiloto" class="form-control">
        </div>
    </div>
    <div class="mb-3">
        <label class="form-label">Elemento a trasladar</label>
        <input type="text" name="elemento" class="form-control" required>
    </div>
    <div class="mb-3">
        <label class="form-label">Tipo</label>
        <select name="tipo" class="form-select">
            <option value="paciente">Paciente</option>
            <option value="equipamiento">Equipamiento</option>
            <option value="insumo">Insumo</option>
        </select>
    </div>
    <div class="row">
        <div class="col-md-6 mb-3">
            <label class="form-label">Origen</label>
            <input type="text" name="origen" class="form-control" value="Hospital de Clínicas" required>
        </div>
        <div class="col-md-6 mb-3">
            <label class="form-label">Destino</label>
            <input type="text" name="destino" class="form-control" required>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Salida estimada</label>
            <input type="date" name="fecha_salida" class="form-control">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Hora salida</label>
            <input type="time" name="hora_salida" class="form-control">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Hora llegada estimada</label>
            <input type="time" name="hora_llegada" class="form-control">
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Registrar traslado</button>
    <a href="/traslados" class="btn btn-secondary">Cancelar</a>
</form>
<?php $contenido = ob_get_clean(); ?>
<?php require __DIR__ . '/../layout/base.php'; ?>
