<?php
/**
 * SisFrota Municipal - View: Listagem de Veículos da Frota
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-slate-800 fw-bold">Frota de Veículos</h1>
        <p class="text-muted small mb-0">Gestão e acompanhamento da frota municipal de Crateús</p>
    </div>
    <?php if (AuthHelper::hasRole(['ADMIN', 'OPERATOR'])): ?>
        <a href="<?= BASE_URL ?>/vehicles/new" class="btn btn-primary d-inline-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            <span>Novo Veículo</span>
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
        <form method="GET" action="<?= BASE_URL ?>/vehicles" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label small fw-semibold text-muted">Buscar (Placa, Modelo ou Marca)</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>" 
                       placeholder="Ex: ABC-1234, Gol, FIAT...">
            </div>

            <div class="col-md-3">
                <label for="secretariat_id" class="form-label small fw-semibold text-muted">Secretaria / Setor</label>
                <select class="form-select" id="secretariat_id" name="secretariat_id">
                    <option value="">Todas as Secretarias</option>
                    <?php foreach ($secretariats as $sec): ?>
                        <option value="<?= $sec['id'] ?>" <?= ($filters['secretariat_id'] == $sec['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($sec['name'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label for="status" class="form-label small fw-semibold text-muted">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Todos os Status</option>
                    <option value="ACTIVE" <?= ($filters['status'] === 'ACTIVE') ? 'selected' : '' ?>>Ativo</option>
                    <option value="MAINTENANCE" <?= ($filters['status'] === 'MAINTENANCE') ? 'selected' : '' ?>>Em Manutenção</option>
                    <option value="UNAVAILABLE" <?= ($filters['status'] === 'UNAVAILABLE') ? 'selected' : '' ?>>Indisponível</option>
                </select>
            </div>

            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-secondary w-100">Filtrar</button>
                <?php if (!empty($filters['search']) || !empty($filters['secretariat_id']) || !empty($filters['status'])): ?>
                    <a href="<?= BASE_URL ?>/vehicles" class="btn btn-outline-secondary" title="Limpar Filtros">✕</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Tabela de Veículos -->
<div class="card border-subtle shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th scope="col" class="ps-3">Placa</th>
                        <th scope="col">Modelo / Marca</th>
                        <th scope="col">Secretaria</th>
                        <th scope="col">Combustível</th>
                        <th scope="col" class="text-end">KM Atual</th>
                        <th scope="col" class="text-center">Status</th>
                        <th scope="col" class="text-end pe-3">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($vehicles)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                Nenhum veículo encontrado com os filtros aplicados.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($vehicles as $v): ?>
                            <tr>
                                <td class="ps-3">
                                    <span class="data-mono fw-bold text-dark fs-6 bg-light px-2 py-1 rounded border border-subtle">
                                        <?= htmlspecialchars(FormatHelper::formatPlate($v['plate']), ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-slate-800">
                                        <?= htmlspecialchars($v['model'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                    <?php if (!empty($v['brand']) || !empty($v['year'])): ?>
                                        <div class="text-muted small">
                                            <?= htmlspecialchars($v['brand'] ?? '', ENT_QUOTES, 'UTF-8') ?> 
                                            <?= !empty($v['year']) ? '(' . $v['year'] . ')' : '' ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($v['secretariat_name'] ?? 'Não informada', ENT_QUOTES, 'UTF-8') ?>
                                </td>
                                <td>
                                    <?php
                                    $fuelMap = [
                                        'GASOLINE' => 'Gasolina',
                                        'ETHANOL'  => 'Etanol',
                                        'DIESEL'   => 'Diesel',
                                        'FLEX'     => 'Flex'
                                    ];
                                    echo htmlspecialchars($fuelMap[$v['fuel_type']] ?? $v['fuel_type'], ENT_QUOTES, 'UTF-8');
                                    ?>
                                </td>
                                <td class="text-end">
                                    <span class="data-mono fw-semibold">
                                        <?= FormatHelper::formatNumber($v['current_km'], 0) ?> KM
                                    </span>
                                </td>
                                <td class="text-center">
                                    <?php if ($v['status'] === 'ACTIVE'): ?>
                                        <span class="badge rounded-pill bg-success px-3 py-1">Ativo</span>
                                    <?php elseif ($v['status'] === 'MAINTENANCE'): ?>
                                        <span class="badge rounded-pill bg-warning text-dark px-3 py-1">Manutenção</span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-danger px-3 py-1">Indisponível</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <!-- Ver Ficha (Todos os perfis) -->
                                        <a href="<?= BASE_URL ?>/vehicles/show/<?= $v['id'] ?>" 
                                           class="btn btn-sm btn-outline-primary" title="Ver Ficha do Veículo">
                                            Ficha
                                        </a>

                                        <?php if (AuthHelper::hasRole(['ADMIN'])): ?>
                                            <!-- Editar (Apenas ADMIN) -->
                                            <a href="<?= BASE_URL ?>/vehicles/edit/<?= $v['id'] ?>" 
                                               class="btn btn-sm btn-outline-secondary" title="Editar Veículo">
                                                Editar
                                            </a>

                                            <!-- Dropdown com Opções de Inativação e Exclusão Permanente -->
                                            <div class="dropdown d-inline">
                                                <button class="btn btn-sm btn-outline-danger dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                                    Opções
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <!-- Inativar (Soft Delete) -->
                                                    <li>
                                                        <form method="POST" action="<?= BASE_URL ?>/vehicles/inactivate/<?= $v['id'] ?>" 
                                                              onsubmit="return confirm('Deseja inativar o veículo <?= htmlspecialchars(FormatHelper::formatPlate($v['plate']), ENT_QUOTES, 'UTF-8') ?>? Ele não aparecerá em novos lançamentos, mas seu histórico será mantido.');">
                                                            <input type="hidden" name="csrf_token" value="<?= AuthHelper::generateCsrfToken() ?>">
                                                            <button type="submit" class="dropdown-item text-warning">
                                                                🟡 Inativar (Soft Delete)
                                                            </button>
                                                        </form>
                                                    </li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <!-- Excluir Permanentemente (Hard Delete) -->
                                                    <li>
                                                        <form method="POST" action="<?= BASE_URL ?>/vehicles/destroy/<?= $v['id'] ?>" 
                                                              onsubmit="return confirm('ATENÇÃO: Deseja EXCLUIR PERMANENTEMENTE o veículo <?= htmlspecialchars(FormatHelper::formatPlate($v['plate']), ENT_QUOTES, 'UTF-8') ?>? Esta ação não poderá ser desfeita.');">
                                                            <input type="hidden" name="csrf_token" value="<?= AuthHelper::generateCsrfToken() ?>">
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                🔴 Excluir Permanentemente
                                                            </button>
                                                        </form>
                                                    </li>
                                                </ul>
                                            </div>
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
