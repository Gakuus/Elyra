<div class="modal-overlay" id="eliminarModal">
    <div class="modal-box">
        <div class="modal-header">
            <h6><i class="bi bi-exclamation-triangle text-danger me-2"></i>Eliminar documento</h6>
            <button type="button" class="modal-close" onclick="this.closest('.modal-overlay').classList.remove('open')" aria-label="Cerrar">&times;</button>
        </div>
        <div class="modal-body">
            <p class="mb-1" id="eliminarMensaje"></p>
            <p class="text-muted small mb-0">El c&oacute;digo QR dejar&aacute; de funcionar. Esta acci&oacute;n se puede deshacer desde la edici&oacute;n del documento.</p>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn" onclick="this.closest('.modal-overlay').classList.remove('open')">Cancelar</button>
            <a href="#" class="btn btn-danger" id="eliminarConfirmar"><i class="bi bi-trash me-1"></i>Eliminar</a>
        </div>
    </div>
</div>
