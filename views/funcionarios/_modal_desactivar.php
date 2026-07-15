<div class="modal" id="modalDesactivar" style="display:none">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Desactivar funcionario</h5>
                <button type="button" class="btn-close" onclick="cerrarModalDesactivar()" aria-label="Cerrar">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro que deseas desactivar a <strong id="nombreFuncionario"></strong>?</p>
                <p class="text-muted small">El funcionario no podrá iniciar sesión hasta que sea reactivado.</p>
            </div>
            <div class="modal-footer">
                <form method="post" id="formDesactivar" action="funcionarios/desactivar">
                    <input type="hidden" name="_csrf_token" value="<?= \Elyra\Infrastructure\Service\SessionManager::getCsrfToken() ?>">
                    <input type="hidden" name="id" id="idFuncionario" value="">
                    <button type="submit" class="btn btn-danger">Desactivar</button>
                    <button type="button" class="btn" onclick="cerrarModalDesactivar()">Cancelar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= $nonce ?>">
function abrirModalDesactivar(id, nombre) {
    document.getElementById('idFuncionario').value = id;
    document.getElementById('nombreFuncionario').textContent = nombre;
    document.getElementById('modalDesactivar').style.display = 'flex';
}
function cerrarModalDesactivar() {
    document.getElementById('modalDesactivar').style.display = 'none';
}
</script>
