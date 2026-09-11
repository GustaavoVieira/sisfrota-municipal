<?php
/**
 * SisFrota Municipal - View: Gestão de Usuários
 */
defined('SYS_ENTRY') or die('Acesso direto negado.');

$roleLabels = [
    'ADMIN'    => 'Administrador',
    'OPERATOR' => 'Operador',
    'MANAGER'  => 'Gestor',
];
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 font-weight-bold mb-1" style="color: #0f172a;">Gestão de Usuários</h1>
        <p class="text-muted mb-0" style="font-size: 0.9rem;">Gerencie as contas de acesso, perfis e permissões dos servidores no sistema.</p>
    </div>
    <button type="button" class="btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateUser">
        + Novo Usuário
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
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">Usuário (Login)</th>
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569;">Nome Completo</th>
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; width: 140px; text-align: center;">Perfil (RBAC)</th>
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; width: 110px; text-align: center;">Status</th>
                    <th style="padding: 12px 16px; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #475569; text-align: right; white-space: nowrap;">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            Nenhum usuário cadastrado.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $u): ?>
                        <tr style="border-bottom: 1px solid #e2e8f0;">
                            <td style="padding: 12px 16px; font-family: monospace; font-weight: 600; color: #16a34a;">
                                <?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td style="padding: 12px 16px; font-weight: 600; color: #0f172a;">
                                <?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <?php
                                $roleClass = match($u['role']) {
                                    'ADMIN' => 'background-color: #fee2e2; color: #991b1b;',
                                    'OPERATOR' => 'background-color: #e0f2fe; color: #075985;',
                                    'MANAGER' => 'background-color: #fef3c7; color: #92400e;',
                                    default => 'background-color: #f1f5f9; color: #475569;'
                                };
                                ?>
                                <span class="badge" style="<?= $roleClass ?> padding: 4px 12px; border-radius: 9999px; font-weight: 600; font-size: 0.75rem;">
                                    <?= htmlspecialchars($roleLabels[$u['role']] ?? $u['role'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td style="padding: 12px 16px; text-align: center;">
                                <?php if ($u['status'] === 'ACTIVE'): ?>
                                    <span class="badge" style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 9999px; font-weight: 600; font-size: 0.75rem;">
                                        Ativo
                                    </span>
                                <?php else: ?>
                                    <span class="badge" style="background-color: #f1f5f9; color: #64748b; padding: 4px 12px; border-radius: 9999px; font-weight: 600; font-size: 0.75rem;">
                                        Inativo
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 16px; text-align: right; white-space: nowrap;">
                                <div class="d-inline-flex align-items-center justify-content-end gap-1">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary btn-edit-user" 
                                            data-id="<?= $u['id'] ?>"
                                            data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-fullname="<?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-role="<?= $u['role'] ?>"
                                            data-status="<?= $u['status'] ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalEditUser"
                                            style="font-size: 0.8rem;">
                                        Editar
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-secondary btn-pass-user" 
                                            data-id="<?= $u['id'] ?>"
                                            data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#modalPasswordUser"
                                            style="font-size: 0.8rem;">
                                        Alterar Senha
                                    </button>
                                    <?php if (($currentUser['id'] ?? 0) != $u['id']): ?>
                                        <button type="button" 
                                                class="btn btn-sm btn-outline-danger btn-delete-user" 
                                                data-id="<?= $u['id'] ?>"
                                                data-username="<?= htmlspecialchars($u['username'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-fullname="<?= htmlspecialchars($u['full_name'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalDeleteUser"
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

<!-- Modal: Novo Usuário -->
<div class="modal fade" id="modalCreateUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 0.75rem; border: 1px solid #e2e8f0;">
            <form action="<?= BASE_URL ?>/settings/users/store" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <h5 class="modal-title font-weight-bold" style="color: #0f172a;">Novo Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="create_username" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nome de Usuário (Login) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_username" name="username" required placeholder="Ex: joao.silva" style="border-radius: 0.5rem; border-color: #e2e8f0;">
                    </div>
                    <div class="mb-3">
                        <label for="create_full_name" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="create_full_name" name="full_name" required placeholder="Ex: João da Silva" style="border-radius: 0.5rem; border-color: #e2e8f0;">
                    </div>
                    <div class="mb-3">
                        <label for="create_password" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Senha Inicial <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="create_password" name="password" required minlength="6" placeholder="Mínimo de 6 caracteres" style="border-radius: 0.5rem; border-color: #e2e8f0;">
                    </div>
                    <div class="mb-3">
                        <label for="create_role" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Perfil de Acesso (RBAC) <span class="text-danger">*</span></label>
                        <select class="form-select" id="create_role" name="role" required style="border-radius: 0.5rem; border-color: #e2e8f0;">
                            <option value="OPERATOR" selected>Operador (Entradas operacionais)</option>
                            <option value="MANAGER">Gestor (Leitura e relatórios)</option>
                            <option value="ADMIN">Administrador (Acesso total)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="create_status" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Status da Conta</label>
                        <select class="form-select" id="create_status" name="status" style="border-radius: 0.5rem; border-color: #e2e8f0;">
                            <option value="ACTIVE" selected>Ativo</option>
                            <option value="INACTIVE">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 0.5rem;">Cancelar</button>
                    <button type="submit" class="btn-primary">Salvar Usuário</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Editar Usuário -->
<div class="modal fade" id="modalEditUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 0.75rem; border: 1px solid #e2e8f0;">
            <form action="<?= BASE_URL ?>/settings/users/update" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" id="edit_user_id" value="">
                <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <h5 class="modal-title font-weight-bold" style="color: #0f172a;">Editar Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nome de Usuário (Login)</label>
                        <input type="text" class="form-control" id="edit_user_username" readonly disabled style="border-radius: 0.5rem; background-color: #f1f5f9;">
                    </div>
                    <div class="mb-3">
                        <label for="edit_user_fullname" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nome Completo <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_user_fullname" name="full_name" required style="border-radius: 0.5rem; border-color: #e2e8f0;">
                    </div>
                    <div class="mb-3">
                        <label for="edit_user_role" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Perfil de Acesso (RBAC) <span class="text-danger">*</span></label>
                        <select class="form-select" id="edit_user_role" name="role" required style="border-radius: 0.5rem; border-color: #e2e8f0;">
                            <option value="OPERATOR">Operador (Entradas operacionais)</option>
                            <option value="MANAGER">Gestor (Leitura e relatórios)</option>
                            <option value="ADMIN">Administrador (Acesso total)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_user_status" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Status da Conta</label>
                        <select class="form-select" id="edit_user_status" name="status" style="border-radius: 0.5rem; border-color: #e2e8f0;">
                            <option value="ACTIVE">Ativo</option>
                            <option value="INACTIVE">Inativo</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 0.5rem;">Cancelar</button>
                    <button type="submit" class="btn-primary">Atualizar Usuário</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Alterar Senha -->
<div class="modal fade" id="modalPasswordUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 0.75rem; border: 1px solid #e2e8f0;">
            <form action="<?= BASE_URL ?>/settings/users/password" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" id="pass_user_id" value="">
                <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <h5 class="modal-title font-weight-bold" style="color: #0f172a;">Alterar Senha do Usuário</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <p style="font-size: 0.9rem; color: #475569;">Definir nova senha para o usuário: <strong id="pass_user_username" style="color: #16a34a;"></strong></p>
                    <div class="mb-3">
                        <label for="new_password" class="form-label" style="font-weight: 600; font-size: 0.875rem;">Nova Senha <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6" placeholder="Mínimo 6 caracteres" style="border-radius: 0.5rem; border-color: #e2e8f0;">
                    </div>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 0.5rem;">Cancelar</button>
                    <button type="submit" class="btn-primary">Redefinir Senha</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Excluir Usuário -->
<div class="modal fade" id="modalDeleteUser" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 0.75rem; border: 1px solid #e2e8f0;">
            <form action="<?= BASE_URL ?>/settings/users/delete" method="POST">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="id" id="delete_user_id" value="">
                <div class="modal-header" style="border-bottom: 1px solid #e2e8f0; background-color: #fff1f2;">
                    <h5 class="modal-title font-weight-bold" style="color: #991b1b;">Confirmar Exclusão</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <p style="margin-bottom: 0;">Tem certeza que deseja excluir o usuário <strong id="delete_user_fullname" style="color: #0f172a;"></strong> (<span id="delete_user_username" style="font-family: monospace; color: #16a34a;"></span>)?</p>
                    <p style="margin-top: 0.5rem; font-size: 0.85rem; color: #64748b; margin-bottom: 0;">Esta ação só será permitida se o usuário não possuir registros vinculados no sistema.</p>
                </div>
                <div class="modal-footer" style="border-top: 1px solid #e2e8f0; background-color: #f8fafc;">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" style="border-radius: 0.5rem;">Cancelar</button>
                    <button type="submit" class="btn btn-danger" style="border-radius: 0.5rem; font-weight: 600;">Excluir Usuário</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-edit-user').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('edit_user_id').value = this.getAttribute('data-id');
            document.getElementById('edit_user_username').value = this.getAttribute('data-username');
            document.getElementById('edit_user_fullname').value = this.getAttribute('data-fullname');
            document.getElementById('edit_user_role').value = this.getAttribute('data-role');
            document.getElementById('edit_user_status').value = this.getAttribute('data-status');
        });
    });

    document.querySelectorAll('.btn-pass-user').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('pass_user_id').value = this.getAttribute('data-id');
            document.getElementById('pass_user_username').textContent = this.getAttribute('data-username');
        });
    });

    document.querySelectorAll('.btn-delete-user').forEach(function (button) {
        button.addEventListener('click', function () {
            document.getElementById('delete_user_id').value = this.getAttribute('data-id');
            document.getElementById('delete_user_username').textContent = this.getAttribute('data-username');
            document.getElementById('delete_user_fullname').textContent = this.getAttribute('data-fullname');
        });
    });
});
</script>
