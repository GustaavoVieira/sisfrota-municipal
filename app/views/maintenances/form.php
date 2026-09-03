<?php
/**
 * SisFrota Municipal - View: Formulário de Manutenção Mecânica (Nova / Editar)
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

$isEdit = !empty($maintenance);
$formTitle = $isEdit ? 'Editar Ordem de Serviço #' . htmlspecialchars($maintenance['os_number'], ENT_QUOTES, 'UTF-8') : 'Nova Ordem de Manutenção';
$formAction = $isEdit ? BASE_URL . '/maintenances/update/' . $maintenance['id'] : BASE_URL . '/maintenances/store';

// Restauração de dados submetidos em caso de erro de validação
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']);

$vehicleIdVal   = $formData['vehicle_id'] ?? ($maintenance['vehicle_id'] ?? ($selectedVehicleId ?? ''));
$categoryIdVal  = $formData['category_id'] ?? ($maintenance['category_id'] ?? '');
$serviceDateVal = $formData['service_date'] ?? ($maintenance['service_date'] ?? date('Y-m-d'));
$workshopVal    = $formData['workshop_name'] ?? ($maintenance['workshop_name'] ?? '');
$osNumberVal    = $formData['os_number'] ?? ($maintenance['os_number'] ?? '');
$costVal        = $formData['cost'] ?? ($maintenance['cost'] ?? '');
$statusVal      = $formData['status'] ?? ($maintenance['status'] ?? 'PENDING');
$notesVal       = $formData['notes'] ?? ($maintenance['notes'] ?? '');
?>

<div class="mb-4">
    <a href="<?= BASE_URL ?>/maintenances" class="text-decoration-none text-muted small d-inline-flex align-items-center gap-1">
        ‹ Voltar para Manutenções
    </a>
    <h1 class="h3 mb-0 text-slate-800 fw-bold mt-2"><?= htmlspecialchars($formTitle, ENT_QUOTES, 'UTF-8') ?></h1>
    <p class="text-muted small mb-0">Registre e acompanhe as ordens de serviço mecânico realizadas nas oficinas</p>
</div>

<!-- Mensagens de Erro -->
<?php if (!empty($_SESSION['flash_error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show border border-danger-subtle shadow-sm" role="alert">
        <div class="d-flex align-items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="text-danger" viewBox="0 0 16 16">
                <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
            </svg>
            <div class="fw-bold">
                <?= htmlspecialchars($_SESSION['flash_error'], ENT_QUOTES, 'UTF-8') ?>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['flash_error']); ?>
<?php endif; ?>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-subtle shadow-sm">
            <div class="card-body p-4">
                <form method="POST" action="<?= $formAction ?>" id="maintenanceForm">
                    <input type="hidden" name="csrf_token" value="<?= AuthHelper::generateCsrfToken() ?>">

                    <!-- Seleção do Veículo -->
                    <div class="mb-4">
                        <label for="vehicle_id" class="form-label fw-bold text-dark">Veículo <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" id="vehicle_id" name="vehicle_id" required>
                            <option value="">-- Selecione o Veículo --</option>
                            <?php foreach ($vehicles as $v): ?>
                                <option value="<?= $v['id'] ?>" 
                                        <?= ((string)$vehicleIdVal === (string)$v['id']) ? 'selected' : '' ?>>
                                    [<?= htmlspecialchars(FormatHelper::formatPlate($v['plate']), ENT_QUOTES, 'UTF-8') ?>] 
                                    <?= htmlspecialchars($v['model'], ENT_QUOTES, 'UTF-8') ?> 
                                    (<?= htmlspecialchars($v['status'], ENT_QUOTES, 'UTF-8') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text text-muted">
                            Selecione o bem automotor integrante da frota municipal que receberá o serviço.
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Categoria da Manutenção -->
                        <div class="col-md-6">
                            <label for="category_id" class="form-label fw-semibold text-muted">Categoria do Serviço <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">-- Selecione a Categoria --</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>" 
                                            <?= ((string)$categoryIdVal === (string)$cat['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Data do Serviço -->
                        <div class="col-md-6">
                            <label for="service_date" class="form-label fw-semibold text-muted">Data do Serviço <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="service_date" name="service_date" 
                                   value="<?= htmlspecialchars($serviceDateVal, ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Oficina Contratada -->
                        <div class="col-md-6">
                            <label for="workshop_name" class="form-label fw-semibold text-muted">Oficina Contratada <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="workshop_name" name="workshop_name" 
                                   value="<?= htmlspecialchars($workshopVal, ENT_QUOTES, 'UTF-8') ?>" 
                                   placeholder="Ex: Auto Mecânica Crateús..." required>
                        </div>

                        <!-- Número da Ordem de Serviço (OS) -->
                        <div class="col-md-6">
                            <label for="os_number" class="form-label fw-semibold text-muted">Número da OS <span class="text-danger">*</span></label>
                            <input type="text" class="form-control data-mono fw-semibold" id="os_number" name="os_number" 
                                   value="<?= htmlspecialchars($osNumberVal, ENT_QUOTES, 'UTF-8') ?>" 
                                   placeholder="Ex: OS-2026-001" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <!-- Valor Total em R$ -->
                        <div class="col-md-6">
                            <label for="cost" class="form-label fw-semibold text-muted">Valor Total da Manutenção (R$) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted">R$</span>
                                <input type="number" step="0.01" min="0" class="form-control data-mono fw-bold text-dark" id="cost" name="cost" 
                                       value="<?= htmlspecialchars($costVal, ENT_QUOTES, 'UTF-8') ?>" 
                                       placeholder="0.00" required>
                            </div>
                        </div>

                        <!-- Status do Atendimento -->
                        <div class="col-md-6">
                            <label for="status" class="form-label fw-semibold text-muted">Status do Atendimento <span class="text-danger">*</span></label>
                            <select class="form-select fw-semibold" id="status" name="status" required>
                                <option value="SCHEDULED"   <?= ($statusVal === 'SCHEDULED')   ? 'selected' : '' ?>>📅 Agendada</option>
                                <option value="IN_PROGRESS" <?= ($statusVal === 'IN_PROGRESS') ? 'selected' : '' ?>>⚙️ Em Andamento (Veículo em Oficina)</option>
                                <option value="PENDING"     <?= ($statusVal === 'PENDING')     ? 'selected' : '' ?>>⏳ Pendente (Aguardando Peças)</option>
                                <option value="COMPLETED"   <?= ($statusVal === 'COMPLETED')   ? 'selected' : '' ?>>✅ Concluída (Veículo Liberado)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Observações e Detalhes do Serviço -->
                    <div class="mb-4">
                        <label for="notes" class="form-label fw-semibold text-muted">Observações / Descrição dos Serviços</label>
                        <textarea class="form-control" id="notes" name="notes" rows="4" 
                                  placeholder="Descreva detalhes das peças trocadas, serviços efetuados ou garantias..."><?= htmlspecialchars($notesVal, ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>

                    <!-- Botões -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="<?= BASE_URL ?>/maintenances" class="btn btn-outline-secondary px-4">Cancelar</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold">
                            <?= $isEdit ? 'Atualizar Ordem de Manutenção' : 'Salvar Ordem de Manutenção' ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Card Lateral Orientativo de Regras da Manutenção -->
    <div class="col-lg-4">
        <div class="card border-subtle shadow-sm mb-4 bg-light">
            <div class="card-body">
                <h5 class="card-title h6 fw-bold text-dark d-flex align-items-center gap-2">
                    <span>⚙️</span> Gestão Automática de Status
                </h5>
                <hr class="my-2 border-subtle">
                <ul class="small text-muted ps-3 mb-0">
                    <li class="mb-2"><strong>Entrada na Oficina:</strong> Se o status for alterado para <u>Em Andamento</u> ou <u>Pendente</u>, o veículo correspondente passará automaticamente para a situação <strong>MANUTENÇÃO</strong>.</li>
                    <li class="mb-2"><strong>Ocultação Operacional:</strong> Veículos em estado de manutenção são temporariamente removidos dos seletores de novos abastecimentos.</li>
                    <li class="mb-2"><strong>Conclusão e Liberação:</strong> Ao alterar a OS para <u>Concluída</u>, o sistema restaura automaticamente a situação do veículo para <strong>ATIVO</strong>.</li>
                    <li><strong>Quilometragem:</strong> O módulo de manutenções não coleta e não altera a quilometragem atual do veículo.</li>
                </ul>
            </div>
        </div>
    </div>
</div>
