<?php
/**
 * SisFrota Municipal - Model de Manutenção
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once __DIR__ . '/Database.php';

class Maintenance
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getPdo();
    }

    /**
     * Retorna a listagem de manutenções com filtros e joins.
     */
    public function findAll(array $filters = []): array
    {
        $sql = "SELECT m.*, 
                       v.plate AS vehicle_plate, 
                       v.model AS vehicle_model, 
                       v.brand AS vehicle_brand,
                       s.name AS secretariat_name, 
                       mc.name AS category_name,
                       u.full_name AS creator_name
                FROM maintenances m
                INNER JOIN vehicles v ON m.vehicle_id = v.id
                LEFT JOIN secretariats s ON v.secretariat_id = s.id
                LEFT JOIN maintenance_categories mc ON m.category_id = mc.id
                LEFT JOIN users u ON m.created_by = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['vehicle_id'])) {
            $sql .= " AND m.vehicle_id = :vehicle_id";
            $params[':vehicle_id'] = (int) $filters['vehicle_id'];
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND m.category_id = :category_id";
            $params[':category_id'] = (int) $filters['category_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND m.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND m.service_date >= :start_date";
            $params[':start_date'] = $filters['start_date'];
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND m.service_date <= :end_date";
            $params[':end_date'] = $filters['end_date'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . strtolower(trim($filters['search'])) . '%';
            $sql .= " AND (LOWER(v.plate) LIKE :search1 OR LOWER(v.model) LIKE :search2 OR LOWER(m.workshop_name) LIKE :search3 OR LOWER(m.os_number) LIKE :search4)";
            $params[':search1'] = $search;
            $params[':search2'] = $search;
            $params[':search3'] = $search;
            $params[':search4'] = $search;
        }

        $sql .= " ORDER BY m.service_date DESC, m.id DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int) $filters['limit'];
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            if (is_int($val)) {
                $stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $stmt->bindValue($key, $val, PDO::PARAM_STR);
            }
        }

        $stmt->execute();
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Busca manutenção por ID.
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT m.*, 
                       v.plate AS vehicle_plate, 
                       v.model AS vehicle_model, 
                       v.brand AS vehicle_brand,
                       s.name AS secretariat_name, 
                       mc.name AS category_name,
                       u.full_name AS creator_name
                FROM maintenances m
                INNER JOIN vehicles v ON m.vehicle_id = v.id
                LEFT JOIN secretariats s ON v.secretariat_id = s.id
                LEFT JOIN maintenance_categories mc ON m.category_id = mc.id
                LEFT JOIN users u ON m.created_by = u.id
                WHERE m.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cadastra uma nova manutenção mecânica e atualiza o status do veículo se necessário.
     */
    public function create(array $data, int $userId): array
    {
        $vehicleId    = isset($data['vehicle_id']) ? (int) $data['vehicle_id'] : 0;
        $categoryId   = isset($data['category_id']) ? (int) $data['category_id'] : 0;
        $serviceDate  = !empty($data['service_date']) ? trim($data['service_date']) : date('Y-m-d');
        $workshopName = trim($data['workshop_name'] ?? '');
        $osNumber     = trim($data['os_number'] ?? '');
        $cost         = isset($data['cost']) ? (float) $data['cost'] : 0.0;
        $status       = $data['status'] ?? 'PENDING';
        $notes        = !empty($data['notes']) ? trim($data['notes']) : null;

        // Validações básicas
        if ($vehicleId <= 0) {
            return ['success' => false, 'message' => 'Selecione um veículo válido.'];
        }
        if ($categoryId <= 0) {
            return ['success' => false, 'message' => 'Selecione uma categoria de manutenção válida.'];
        }
        if (empty($serviceDate)) {
            return ['success' => false, 'message' => 'A data do serviço é obrigatória.'];
        }
        if (empty($workshopName)) {
            return ['success' => false, 'message' => 'O nome da oficina contratada é obrigatório.'];
        }
        if (empty($osNumber)) {
            return ['success' => false, 'message' => 'O número da Ordem de Serviço (OS) é obrigatório.'];
        }
        if ($cost < 0) {
            return ['success' => false, 'message' => 'O valor da manutenção não pode ser negativo.'];
        }

        $validStatuses = ['SCHEDULED', 'IN_PROGRESS', 'PENDING', 'COMPLETED'];
        if (!in_array($status, $validStatuses, true)) {
            $status = 'PENDING';
        }

        // Verifica existência do veículo
        $stmtV = $this->db->prepare("SELECT id, is_deleted FROM vehicles WHERE id = :id LIMIT 1");
        $stmtV->bindValue(':id', $vehicleId, PDO::PARAM_INT);
        $stmtV->execute();
        $vehicle = $stmtV->fetch();

        if (!$vehicle || (int)$vehicle['is_deleted'] === 1) {
            return ['success' => false, 'message' => 'Veículo não encontrado ou inativado no sistema.'];
        }

        // Inserção no banco
        $sql = "INSERT INTO maintenances 
                (vehicle_id, category_id, service_date, workshop_name, os_number, cost, status, notes, created_by, created_at)
                VALUES 
                (:vehicle_id, :category_id, :service_date, :workshop_name, :os_number, :cost, :status, :notes, :created_by, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':vehicle_id', $vehicleId, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':service_date', $serviceDate, PDO::PARAM_STR);
        $stmt->bindValue(':workshop_name', $workshopName, PDO::PARAM_STR);
        $stmt->bindValue(':os_number', $osNumber, PDO::PARAM_STR);
        $stmt->bindValue(':cost', $cost, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':notes', $notes, $notes !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':created_by', $userId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $maintenanceId = (int) $this->db->lastInsertId();

            // Sincroniza o status do veículo (se IN_PROGRESS ou PENDING, altera para MAINTENANCE)
            $this->syncVehicleStatus($vehicleId, $userId);

            return [
                'success' => true,
                'message' => 'Ordem de manutenção cadastrada com sucesso!',
                'id'      => $maintenanceId
            ];
        }

        return ['success' => false, 'message' => 'Falha ao gravar a manutenção no banco de dados.'];
    }

    /**
     * Atualização de manutenção existente e recálculo do status do veículo.
     */
    public function update(int $id, array $data, int $userId): array
    {
        $current = $this->findById($id);
        if (!$current) {
            return ['success' => false, 'message' => 'Registro de manutenção não encontrado.'];
        }

        $oldVehicleId = (int) $current['vehicle_id'];
        $vehicleId    = isset($data['vehicle_id']) ? (int) $data['vehicle_id'] : $oldVehicleId;
        $categoryId   = isset($data['category_id']) ? (int) $data['category_id'] : (int) $current['category_id'];
        $serviceDate  = !empty($data['service_date']) ? trim($data['service_date']) : $current['service_date'];
        $workshopName = trim($data['workshop_name'] ?? $current['workshop_name']);
        $osNumber     = trim($data['os_number'] ?? $current['os_number']);
        $cost         = isset($data['cost']) ? (float) $data['cost'] : (float) $current['cost'];
        $status       = $data['status'] ?? $current['status'];
        $notes        = isset($data['notes']) ? (empty(trim($data['notes'])) ? null : trim($data['notes'])) : $current['notes'];

        if ($vehicleId <= 0 || $categoryId <= 0 || empty($serviceDate) || empty($workshopName) || empty($osNumber) || $cost < 0) {
            return ['success' => false, 'message' => 'Dados inválidos fornecidos para atualização da manutenção.'];
        }

        $validStatuses = ['SCHEDULED', 'IN_PROGRESS', 'PENDING', 'COMPLETED'];
        if (!in_array($status, $validStatuses, true)) {
            $status = $current['status'];
        }

        $sql = "UPDATE maintenances 
                SET vehicle_id = :vehicle_id,
                    category_id = :category_id,
                    service_date = :service_date,
                    workshop_name = :workshop_name,
                    os_number = :os_number,
                    cost = :cost,
                    status = :status,
                    notes = :notes,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':vehicle_id', $vehicleId, PDO::PARAM_INT);
        $stmt->bindValue(':category_id', $categoryId, PDO::PARAM_INT);
        $stmt->bindValue(':service_date', $serviceDate, PDO::PARAM_STR);
        $stmt->bindValue(':workshop_name', $workshopName, PDO::PARAM_STR);
        $stmt->bindValue(':os_number', $osNumber, PDO::PARAM_STR);
        $stmt->bindValue(':cost', $cost, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':notes', $notes, $notes !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':updated_by', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Sincroniza o status do veículo atual
            $this->syncVehicleStatus($vehicleId, $userId);

            // Caso o veículo tenha sido alterado na edição, sincroniza também o antigo
            if ($oldVehicleId !== $vehicleId) {
                $this->syncVehicleStatus($oldVehicleId, $userId);
            }

            return ['success' => true, 'message' => 'Manutenção atualizada com sucesso.'];
        }

        return ['success' => false, 'message' => 'Erro ao atualizar registro de manutenção.'];
    }

    /**
     * Exclusão administrativa de manutenção (com ressincronização de status).
     */
    public function delete(int $id, int $userId): array
    {
        $current = $this->findById($id);
        if (!$current) {
            return ['success' => false, 'message' => 'Registro de manutenção não encontrado.'];
        }

        $vehicleId = (int) $current['vehicle_id'];

        $sql = "DELETE FROM maintenances WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $this->syncVehicleStatus($vehicleId, $userId);
            return ['success' => true, 'message' => 'Manutenção excluída com sucesso.'];
        }

        return ['success' => false, 'message' => 'Erro ao excluir manutenção.'];
    }

    /**
     * Sincroniza o status do veículo com base nas manutenções em andamento/pendentes.
     * Se houver manutenção em andamento (IN_PROGRESS) ou pendente (PENDING), ajusta veículo para MAINTENANCE.
     * Se não houver pendências ativas e o veículo estiver em MAINTENANCE, restaura para ACTIVE.
     */
    public function syncVehicleStatus(int $vehicleId, int $userId): void
    {
        // Conta manutenções com status 'IN_PROGRESS' ou 'PENDING'
        $stmtActive = $this->db->prepare("SELECT COUNT(*) FROM maintenances WHERE vehicle_id = :v_id AND status IN ('IN_PROGRESS', 'PENDING')");
        $stmtActive->bindValue(':v_id', $vehicleId, PDO::PARAM_INT);
        $stmtActive->execute();
        $activeCount = (int) $stmtActive->fetchColumn();

        // Busca o status atual do veículo
        $stmtV = $this->db->prepare("SELECT status FROM vehicles WHERE id = :v_id AND is_deleted = 0 LIMIT 1");
        $stmtV->bindValue(':v_id', $vehicleId, PDO::PARAM_INT);
        $stmtV->execute();
        $vehicle = $stmtV->fetch();

        if (!$vehicle) {
            return;
        }

        $currentVehicleStatus = $vehicle['status'];

        if ($activeCount > 0) {
            // Se possui manutenção ativa e status atual não é MAINTENANCE, altera para MAINTENANCE
            if ($currentVehicleStatus !== 'MAINTENANCE') {
                $stmtUp = $this->db->prepare("UPDATE vehicles SET status = 'MAINTENANCE', updated_by = :u_id, updated_at = NOW() WHERE id = :v_id");
                $stmtUp->bindValue(':u_id', $userId, PDO::PARAM_INT);
                $stmtUp->bindValue(':v_id', $vehicleId, PDO::PARAM_INT);
                $stmtUp->execute();
            }
        } else {
            // Se NÃO possui manutenção ativa em andamento/pendente e status é MAINTENANCE, restaura para ACTIVE
            if ($currentVehicleStatus === 'MAINTENANCE') {
                $stmtUp = $this->db->prepare("UPDATE vehicles SET status = 'ACTIVE', updated_by = :u_id, updated_at = NOW() WHERE id = :v_id");
                $stmtUp->bindValue(':u_id', $userId, PDO::PARAM_INT);
                $stmtUp->bindValue(':v_id', $vehicleId, PDO::PARAM_INT);
                $stmtUp->execute();
            }
        }
    }
}
