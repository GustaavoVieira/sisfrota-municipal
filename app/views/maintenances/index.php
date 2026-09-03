<?php
/**
 * SisFrota Municipal - View: Listagem de Manutenções Mecânicas
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-slate-800 fw-bold">Manutenções Mecânicas</h1>
        <p class="text-muted small mb-0">Gestão de ordens de serviço, acompanhamento de oficinas e status operacionais</p>
    </div>
    <?php if (AuthHelper::hasRole(['ADMIN', 'OPERATOR'])): ?>
        <a href="<?= BASE_URL ?>/maintenances/new" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Nova Manutenção</span>
        </a>
    <?php endif; ?>
</div>

<!-- Flash Messages -->
<?php if (!empty($_SESSION['flash_success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_success'], ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_success']); ?>
<?php endif; ?>

<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<!-- Toolbar de Filtros -->
<div class="card border-subtle shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/maintenances" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label small fw-semibold text-muted">Buscar (Nº OS, Placa, Oficina)</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                       placeholder="Ex: OS-2026-01, ABC-1234, Auto Center...">
            </div>

            <div class="col-md-2">
                <label for="vehicle_id" class="form-label small fw-semibold text-muted">Veículo</label>
                <select class="form-select" id="vehicle_id" name="vehicle_id">
                    <option value="">Todos os Veículos</option>
                    <?php foreach ($vehicles as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= ($filters['vehicle_id'] == $v['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars(FormatHelper::formatPlate($v['plate']), ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($v['model'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="category_id" class="form-label small fw-semibold text-muted">Categoria</label>
                <select class="form-select" id="category_id" name="category_id">
                    <option value="">Todas as Categorias</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= ($filters['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-2">
                <label for="status" class="form-label small fw-semibold text-muted">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos os Status</option>
                    <option value="SCHEDULED"   <?= ($filters['status'] === 'SCHEDULED')   ? 'selected' : '' ?>>Agendada</option>
                    <option value="IN_PROGRESS" <?= ($filters['status'] === 'IN_PROGRESS') ? 'selected' : '' ?>>Em Andamento</option>
                    <option value="PENDING"     <?= ($filters['status'] === 'PENDING')     ? 'selected' : '' ?>>Pendente</option>
                    <option value="COMPLETED"   <?= ($filters['status'] === 'COMPLETED')   ? 'selected' : '' ?>>Concluída</option>
                </select>
            </div>

            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                <?php if (!empty($filters['search']) || !empty($filters['vehicle_id']) || !empty($filters['category_id']) || !empty($filters['status']) || !empty($filters['start_date']) || !empty($filters['end_date'])): ?>
                    <a href="<?= BASE_URL ?>/maintenances" class="btn btn-outline-secondary" title="Limpar Filtros">✕</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de Ordens de Manutenção -->
<div class="card border-subtle shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-3">Nº OS</th>
                        <th scope="col">Data Serviço</th>
                        <th scope="col">Veículo</th>
                        <th scope="col">Categoria</th>
                        <th scope="col">Oficina Contratada</th>
                        <th scope="col" class="text-end">Valor Total (R$)</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col" class="text-end pe-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($maintenances)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                Nenhuma ordem de manutenção encontrada.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($maintenances as $m): ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="data-mono fw-bold text-dark">
                                        <?= htmlspecialchars($m['os_number'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="data-mono text-dark">
                                        <?= FormatHelper::formatDate($m['service_date']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="data-mono fw-bold bg-light px-2 py-1 rounded border border-subtle">
                                            <?= htmlspecialchars(FormatHelper::formatPlate($m['vehicle_plate']), ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                        <span class="fw-semibold text-slate-800">
                                            <?= htmlspecialchars($m['vehicle_model'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                    <?php if (!empty($m['secretariat_name'])): ?>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars($m['secretariat_name'], ENT_QUOTES, 'UTF-8') ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border">
                                        <?= htmlspecialchars($m['category_name'] ?? 'Não informada', ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark">
                                        <?= htmlspecialchars($m['workshop_name'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-end">
                                    <span class="data-mono fw-bold text-dark">
                                        <?= FormatHelper::formatMoney($m['cost']) ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php
                                    $statusMap = [
                                        'SCHEDULED'   => ['label' => 'Agendada',     'class' => 'bg-info-subtle text-dark border-info-subtle'],
                                        'IN_PROGRESS' => ['label' => 'Em Andamento', 'class' => 'bg-warning-subtle text-dark border-warning-subtle'],
                                        'PENDING'     => ['label' => 'Pendente',     'class' => 'bg-danger-subtle text-danger border-danger-subtle'],
                                        'COMPLETED'   => ['label' => 'Concluída',    'class' => 'bg-success-subtle text-success border-success-subtle'],
                                    ];
                                    $stInfo = $statusMap[$m['status']] ?? ['label' => $m['status'], 'class' => 'bg-secondary-subtle text-dark'];
                                    ?>
                                    <span class="badge <?= $stInfo['class'] ?> px-2 py-1 border">
                                        <?= htmlspecialchars($stInfo['label'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <a href="<?= BASE_URL ?>/vehicles/show/<?= $m['vehicle_id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Ver Ficha do Veículo">
                                            Ficha
                                        </a>

                                        <?php if (AuthHelper::hasRole(['ADMIN', 'OPERATOR'])): ?>
                                            <a href="<?= BASE_URL ?>/maintenances/edit/<?= $m['id'] ?>" 
                                               class="btn btn-sm btn-outline-secondary" title="Editar Ordem de Serviço">
                                                Editar
                                            </a>
                                        <?php endif; ?>

                                        <?php if (AuthHelper::hasRole(['ADMIN'])): ?>
                                            <form method="POST" action="<?= BASE_URL ?>/maintenances/delete/<?= $m['id'] ?>" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Deseja realmente excluir esta ordem de manutenção?');">
                                                <input type="hidden" name="csrf_token" value="<?= AuthHelper::generateCsrfToken() ?>">
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Excluir">
                                                    Excluir
                                                </button>
                                            </form>
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
</div>
