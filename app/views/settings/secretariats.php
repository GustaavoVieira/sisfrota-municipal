<?php
/**
 * SisFrota Municipal - View: Gestão de Secretarias
 */
defined('SYS_ENTRY') or die('Acesso direto negado.');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 font-weight-bold mb-1" style="color: #0f172a;">Gestão de Secretarias</h1>
        <p class="text-muted mb-0" style="font-size: 0.9rem;">Cadastre e gerencie as secretarias e departamentos municipais.</p>
    </div>
    <button type="button" class="btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateSecretariat">
        + Nova Secretaria
    </button>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 0.5rem;">
        <strong>Atenção:</strong> <?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius: 0.5rem; background-color: #f7fff2; border-color: #bdcaba; color: #006b2c;">
        <strong>Sucesso:</strong> <?= htmlspecialchars($success, ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
    </div>
<?php endif; ?>

<div class="card p-0" style="overflow: hidden;">
    <div class="table-responsive">
        <table class="table table-hover mb-0" style="vertical-align: middle;">
            <thead style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                <tr>
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; width: 80px;">ID</th>
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">Nome da Secretaria</th>
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; width: 120px;">Sigla</th>
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; width: 160px; text-align: center;">Veículos Vinculados</th>
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; text-align: right; white-space: nowrap;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($secretariats)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            Nenhuma secretaria cadastrada até o momento.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($secretariats as $sec): ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px 16px; font-family: monospace; font-size: 0.85rem; color: #475569;">
                                #<?= sprintf('%03d', $sec['id']) ?>
                            </td>
                            <td style="padding: 12px 16px; font-weight: 600; color: #0f172a;">
                                <?= htmlspecialchars($sec['name'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td style="padding: 12px 16px; font-family: monospace; font-weight: 600; color: #16a34a;">
                                <?= htmlspecialchars($sec['acronym'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <span class="badge" style="background-color: #f1f5f9; color: #475569; font-family: monospace; font-size: 0.85rem; padding: 4px 10px; border-radius: 9999px; border: 1px solid #cbd5e1;">
                                    <?= (int)($sec['total_vehicles'] ?? 0) ?> veículo(s)
                                </span>
                            </td>
                            <td style="padding: 12px 16px; text-align: right; white-space: nowrap;">
                                <div class="d-inline-flex align-items-center justify-content-end gap-1">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary btn-edit-sec" 
                                            data-id="<?= $sec['id'] ?>"
                                            data-name="<?= htmlspecialchars($sec['name'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-acronym="<?= htmlspecialchars($sec['acronym'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditSecretariat"
                                            style="font-size: 0.8rem;">
                                        Editar
                                    </button>
                                    <?php if (($sec['total_vehicles'] ?? 0) == 0): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger btn-delete-sec" 
                                                data-id="<?= $sec['id'] ?>"
                                                data-name="<?= htmlspecialchars($sec['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDeleteSecretariat"
                                                style="font-size: 0.8rem;">
                                            Excluir
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: Criar Secretaria -->
<div class="modal fade" id="modalCreateSecretariat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 0.75rem; border: 1px solid #e2e8f0;">
            <form action="<?= BASE_URL ?>/settings/secretariats/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <h5 class="modal-title font-weight-bold" style="color: #0f172a;">Nova Secretaria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="create_name" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nome da Secretaria <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_name" name="name" required placeholder="Ex: Secretaria de Saúde" style="border-radius: 0.5rem; border-color: #e2e8f0;">
                    </div>
                    <div class="mb-3">
                        <label for="create_acronym" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Sigla</label>
                        <input type="text" class="form-control" id="create_acronym" name="acronym" placeholder="Ex: SMS" maxlength="20" style="border-radius: 0.5rem; border-color: #e2e8f0;">
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 0.5rem;">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar Secretaria</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Secretaria -->
<div class="modal fade" id="modalEditSecretariat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 0.75rem; border: 1px solid #e2e8f0;">
            <form action="<?= BASE_URL ?>/settings/secretariats/update" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" id="edit_sec_id" value="">
                <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <h5 class="modal-title font-weight-bold" style="color: #0f172a;">Editar Secretaria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_sec_name" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nome da Secretaria <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_sec_name" name="name" required style="border-radius: 0.5rem; border-color: #e2e8f0;">
                    </div>
                    <div class="mb-3">
                        <label for="edit_sec_acronym" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Sigla</label>
                        <input type="text" class="form-control" id="edit_sec_acronym" name="acronym" maxlength="20" style="border-radius: 0.5rem; border-color: #e2e8f0;">
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 0.5rem;">Cancelar</button>
                    <button type="submit" class="btn-primary">Atualizar Secretaria</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Excluir Secretaria -->
<div class="modal fade" id="modalDeleteSecretariat" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 0.75rem; border: 1px solid #e2e8f0;">
            <form action="<?= BASE_URL ?>/settings/secretariats/delete" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" id="delete_sec_id" value="">
                <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; background-color: #fff1f2;">
                    <h5 class="modal-title font-weight-bold" style="color: #991b1b;">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <p style="margin-bottom: 0;">Tem certeza que deseja excluir a secretaria <strong id="delete_sec_name" style="color: #0f172a;"></strong>?</p>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 0.5rem;">Cancelar</button>
                    <button type="submit" class="btn btn-danger" style="border-radius: 0.5rem; font-weight: 600;">Excluir Secretaria</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Preenche o modal de edição
    document.querySelectorAll('.btn-edit-sec').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('edit_sec_id').value = this.getAttribute('data-id');
            document.getElementById('edit_sec_name').value = this.getAttribute('data-name');
            document.getElementById('edit_sec_acronym').value = this.getAttribute('data-acronym');
        });
    });

    // Preenche o modal de exclusão
    document.querySelectorAll('.btn-delete-sec').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('delete_sec_id').value = this.getAttribute('data-id');
            document.getElementById('delete_sec_name').textContent = this.getAttribute('data-name');
        });
    });
});
</script>
