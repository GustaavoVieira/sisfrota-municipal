<?php
/**
 * SisFrota Municipal - View: Categorias de Manutenção
 */
defined('SYS_ENTRY') or die('Acesso direto negado.');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 font-weight-bold mb-1" style="color: #0f172a;">Categorias de Manutenção</h1>
        <p class="text-muted mb-0" style="font-size: 0.9rem;">Gerencie os tipos de serviços e reparos mecânicos da frota municipal.</p>
    </div>
    <button type="button" class="btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateCategory">
        + Nova Categoria
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
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">Nome da Categoria</th>
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">Descrição</th>
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; width: 120px; text-align: center;">Status</th>
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; width: 160px; text-align: center;">Manutenções</th>
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; text-align: right; white-space: nowrap;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Nenhuma categoria cadastrada até o momento.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px 16px; font-family: monospace; font-size: 0.85rem; color: #475569;">
                                #<?= sprintf('%03d', $cat['id']) ?>
                            </td>
                            <td style="padding: 12px 16px; font-weight: 600; color: #0f172a;">
                                <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td style="padding: 12px 16px; color: #475569; font-size: 0.875rem;">
                                <?= htmlspecialchars($cat['description'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <?php if ($cat['status'] === 'ACTIVE'): ?>
                                    <span class="badge" style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 9999px; font-weight: 600; font-size: 0.75rem;">
                                        Ativo
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background-color: #f1f5f9; color: #64748b; padding: 4px 12px; border-radius: 9999px; font-weight: 600; font-size: 0.75rem;">
                                        Inativo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <span class="badge" style="background-color: #f1f5f9; color: #475569; font-family: monospace; font-size: 0.85rem; padding: 4px 10px; border-radius: 9999px; border: 1px solid #cbd5e1;">
                                    <?= (int)($cat['total_maintenances'] ?? 0) ?> registro(s)
                                </span>
                            </td>
                            <td style="padding: 12px 16px; text-align: right; white-space: nowrap;">
                                <div class="d-inline-flex align-items-center justify-content-end gap-1">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary btn-edit-cat" 
                                            data-id="<?= $cat['id'] ?>"
                                            data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-description="<?= htmlspecialchars($cat['description'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                                            data-status="<?= $cat['status'] ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditCategory"
                                            style="font-size: 0.8rem;">
                                        Editar
                                    </button>
                                    <form action="<?= BASE_URL ?>/settings/categories/toggle" method="POST" class="d-inline m-0">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <button type="submit" 
                                                class="btn btn-sm <?= ($cat['status'] === 'ACTIVE') ? 'btn-outline-warning' : 'btn-outline-success' ?>" 
                                                style="font-size: 0.8rem;">
                                            <?= ($cat['status'] === 'ACTIVE') ? 'Inativar' : 'Ativar' ?>
                                        </button>
                                    </form>
                                    <?php if (($cat['total_maintenances'] ?? 0) == 0): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger btn-delete-cat" 
                                                data-id="<?= $cat['id'] ?>"
                                                data-name="<?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDeleteCategory"
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

<!-- Modal: Nova Categoria -->
<div class="modal fade" id="modalCreateCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 0.75rem; border: 1px solid #e2e8f0;">
            <form action="<?= BASE_URL ?>/settings/categories/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <h5 class="modal-title font-weight-bold" style="color: #0f172a;">Nova Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="create_cat_name" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nome da Categoria <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_cat_name" name="name" required placeholder="Ex: Troca de Óleo e Filtros" style="border-radius: 0.5rem; border-color: #e2e8f0;">
                    </div>
                    <div class="mb-3">
                        <label for="create_cat_desc" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Descrição</label>
                        <textarea class="form-control" id="create_cat_desc" name="description" rows="3" placeholder="Descrição dos serviços contemplados nesta categoria..." style="border-radius: 0.5rem; border-color: #e2e8f0;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="create_cat_status" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Status</label>
                        <select class="form-select" id="create_cat_status" name="status" style="border-radius: 0.5rem; border-color: #e2e8f0;">
                            <option value="ACTIVE" selected>Ativo</option>
                            <option value="INACTIVE">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 0.5rem;">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar Categoria</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Categoria -->
<div class="modal fade" id="modalEditCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 0.75rem; border: 1px solid #e2e8f0;">
            <form action="<?= BASE_URL ?>/settings/categories/update" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" id="edit_cat_id" value="">
                <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <h5 class="modal-title font-weight-bold" style="color: #0f172a;">Editar Categoria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="edit_cat_name" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nome da Categoria <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_cat_name" name="name" required style="border-radius: 0.5rem; border-color: #e2e8f0;">
                    </div>
                    <div class="mb-3">
                        <label for="edit_cat_desc" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Descrição</label>
                        <textarea class="form-control" id="edit_cat_desc" name="description" rows="3" style="border-radius: 0.5rem; border-color: #e2e8f0;"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="edit_cat_status" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Status</label>
                        <select class="form-select" id="edit_cat_status" name="status" style="border-radius: 0.5rem; border-color: #e2e8f0;">
                            <option value="ACTIVE">Ativo</option>
                            <option value="INACTIVE">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 0.5rem;">Cancelar</button>
                    <button type="submit" class="btn-primary">Atualizar Categoria</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Excluir Categoria -->
<div class="modal fade" id="modalDeleteCategory" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 0.75rem; border: 1px solid #e2e8f0;">
            <form action="<?= BASE_URL ?>/settings/categories/delete" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" id="delete_cat_id" value="">
                <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; background-color: #fff1f2;">
                    <h5 class="modal-title font-weight-bold" style="color: #991b1b;">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <p style="margin-bottom: 0;">Tem certeza que deseja excluir a categoria <strong id="delete_cat_name" style="color: #0f172a;"></strong>?</p>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 0.5rem;">Cancelar</button>
                    <button type="submit" class="btn btn-danger" style="border-radius: 0.5rem; font-weight: 600;">Excluir Categoria</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-edit-cat').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('edit_cat_id').value = this.getAttribute('data-id');
            document.getElementById('edit_cat_name').value = this.getAttribute('data-name');
            document.getElementById('edit_cat_desc').value = this.getAttribute('data-description');
            document.getElementById('edit_cat_status').value = this.getAttribute('data-status');
        });
    });

    document.querySelectorAll('.btn-delete-cat').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('delete_cat_id').value = this.getAttribute('data-id');
            document.getElementById('delete_cat_name').textContent = this.getAttribute('data-name');
        });
    });
});
</script>
