<?php
/**
 * SisFrota Municipal - Model de Abastecimento
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once __DIR__ . '/Database.php';

class Fueling
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getPdo();
    }

    /**
     * Retorna a listagem de abastecimentos com filtros e joins.
     */
    public function findAll(array $filters = []): array
    {
        $sql = "SELECT f.*, 
                       v.plate AS vehicle_plate, 
                       v.model AS vehicle_model, 
                       v.brand AS vehicle_brand,
                       s.name AS secretariat_name, 
                       u.full_name AS creator_name
                FROM fuelings f
                INNER JOIN vehicles v ON f.vehicle_id = v.id
                LEFT JOIN secretariats s ON v.secretariat_id = s.id
                LEFT JOIN users u ON f.created_by = u.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['vehicle_id'])) {
            $sql .= " AND f.vehicle_id = :vehicle_id";
            $params[':vehicle_id'] = (int) $filters['vehicle_id'];
        }

        if (!empty($filters['secretariat_id'])) {
            $sql .= " AND v.secretariat_id = :secretariat_id";
            $params[':secretariat_id'] = (int) $filters['secretariat_id'];
        }

        if (!empty($filters['start_date'])) {
            $sql .= " AND f.fueling_date >= :start_date";
            $params[':start_date'] = $filters['start_date'] . ' 00:00:00';
        }

        if (!empty($filters['end_date'])) {
            $sql .= " AND f.fueling_date <= :end_date";
            $params[':end_date'] = $filters['end_date'] . ' 23:59:59';
        }

        if (!empty($filters['fuel_type'])) {
            $sql .= " AND f.fuel_type = :fuel_type";
            $params[':fuel_type'] = $filters['fuel_type'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . strtolower(trim($filters['search'])) . '%';
            $sql .= " AND (LOWER(v.plate) LIKE :search OR LOWER(v.model) LIKE :search OR LOWER(f.station_name) LIKE :search)";
            $params[':search'] = $search;
        }

        $sql .= " ORDER BY f.fueling_date DESC, f.id DESC";

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
     * Busca abastecimento por ID.
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT f.*, 
                       v.plate AS vehicle_plate, 
                       v.model AS vehicle_model, 
                       v.brand AS vehicle_brand,
                       s.name AS secretariat_name, 
                       u.full_name AS creator_name
                FROM fuelings f
                INNER JOIN vehicles v ON f.vehicle_id = v.id
                LEFT JOIN secretariats s ON v.secretariat_id = s.id
                LEFT JOIN users u ON f.created_by = u.id
                WHERE f.id = :id
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Busca o último abastecimento registrado para um veículo.
     */
    public function getLastFueling(int $vehicleId, ?int $excludeId = null): ?array
    {
        $sql = "SELECT * FROM fuelings WHERE vehicle_id = :vehicle_id";
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        $sql .= " ORDER BY fueling_date DESC, id DESC LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':vehicle_id', $vehicleId, PDO::PARAM_INT);
        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Registra um novo abastecimento com validação bloqueante de KM e cálculo automático de KM/L.
     */
    public function create(array $data, int $userId): array
    {
        $vehicleId   = isset($data['vehicle_id']) ? (int) $data['vehicle_id'] : 0;
        $fuelingDate = !empty($data['fueling_date']) ? trim($data['fueling_date']) : date('Y-m-d H:i:s');
        $km          = isset($data['km']) ? (int) $data['km'] : 0;
        $liters      = isset($data['liters']) ? (float) $data['liters'] : 0.0;
        $totalPrice  = isset($data['total_price']) ? (float) $data['total_price'] : 0.0;
        $fuelType    = $data['fuel_type'] ?? 'FLEX';
        $stationName = trim($data['station_name'] ?? '');

        // Formatação de data/hora caso venha do HTML datetime-local (YYYY-MM-DDTHH:MM)
        if (str_contains($fuelingDate, 'T')) {
            $fuelingDate = str_replace('T', ' ', $fuelingDate);
            if (strlen($fuelingDate) === 16) {
                $fuelingDate .= ':00';
            }
        }

        // Validações básicas de campos
        if ($vehicleId <= 0) {
            return ['success' => false, 'message' => 'Veículo inválido.'];
        }
        if ($km <= 0) {
            return ['success' => false, 'message' => 'A quilometragem informada deve ser maior que zero.'];
        }
        if ($liters <= 0) {
            return ['success' => false, 'message' => 'A quantidade de litros deve ser maior que zero.'];
        }
        if ($totalPrice <= 0) {
            return ['success' => false, 'message' => 'O valor total deve ser maior que zero.'];
        }
        if (empty($stationName)) {
            return ['success' => false, 'message' => 'O nome do posto fornecedor é obrigatório.'];
        }

        // Verificar se o veículo existe e está ativo
        $stmtV = $this->db->prepare("SELECT id, current_km, status, is_deleted FROM vehicles WHERE id = :id LIMIT 1");
        $stmtV->bindValue(':id', $vehicleId, PDO::PARAM_INT);
        $stmtV->execute();
        $vehicle = $stmtV->fetch();

        if (!$vehicle || (int)$vehicle['is_deleted'] === 1) {
            return ['success' => false, 'message' => 'Veículo não encontrado ou inativado no sistema.'];
        }

        // Obter a última KM registrada para o veículo (seja pelo cadastro do veículo ou por abastecimento prévio)
        $lastFueling = $this->getLastFueling($vehicleId);
        $lastKm = $lastFueling ? (int) $lastFueling['km'] : (int) $vehicle['current_km'];

        // ── VALIDAÇÃO BLOQUEANTE RÍGIDA DE KM ──────────────────────────────────
        // A KM informada DEVE ser estritamente maior que a última KM registrada.
        if ($km <= $lastKm) {
            return [
                'success' => false,
                'message' => sprintf(
                    "Erro: A quilometragem informada (%s KM) é menor ou igual à última registrada (%s KM) para este veículo.",
                    number_format($km, 0, '', '.'),
                    number_format($lastKm, 0, '', '.')
                )
            ];
        }

        // ── CÁLCULO AUTOMÁTICO DE CONSUMO MÉDIO (KM/L) ──────────────────────────
        // Calculado a partir do 2º abastecimento: (KM_atual - KM_anterior) / Litros_atual
        $avgKml = null;
        if ($lastFueling) {
            $kmDiff = $km - $lastKm;
            if ($kmDiff > 0 && $liters > 0) {
                $avgKml = round($kmDiff / $liters, 2);
            }
        }

        // Inserção no banco de dados
        $sql = "INSERT INTO fuelings 
                (vehicle_id, fueling_date, km, liters, total_price, fuel_type, station_name, avg_kml, created_by, created_at)
                VALUES 
                (:vehicle_id, :fueling_date, :km, :liters, :total_price, :fuel_type, :station_name, :avg_kml, :created_by, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':vehicle_id', $vehicleId, PDO::PARAM_INT);
        $stmt->bindValue(':fueling_date', $fuelingDate, PDO::PARAM_STR);
        $stmt->bindValue(':km', $km, PDO::PARAM_INT);
        $stmt->bindValue(':liters', $liters, PDO::PARAM_STR);
        $stmt->bindValue(':total_price', $totalPrice, PDO::PARAM_STR);
        $stmt->bindValue(':fuel_type', $fuelType, PDO::PARAM_STR);
        $stmt->bindValue(':station_name', $stationName, PDO::PARAM_STR);
        $stmt->bindValue(':avg_kml', $avgKml, $avgKml !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':created_by', $userId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $fuelingId = (int) $this->db->lastInsertId();

            // Atualiza a KM atual do veículo na tabela vehicles
            $stmtUp = $this->db->prepare("UPDATE vehicles SET current_km = :km, updated_by = :updated_by, updated_at = NOW() WHERE id = :id");
            $stmtUp->bindValue(':km', $km, PDO::PARAM_INT);
            $stmtUp->bindValue(':updated_by', $userId, PDO::PARAM_INT);
            $stmtUp->bindValue(':id', $vehicleId, PDO::PARAM_INT);
            $stmtUp->execute();

            return [
                'success' => true,
                'message' => 'Abastecimento cadastrado com sucesso!',
                'id' => $fuelingId
            ];
        }

        return ['success' => false, 'message' => 'Falha ao gravar o abastecimento no banco de dados.'];
    }

    /**
     * Atualização administrativa de abastecimento existente (Exclusivo ADMIN).
     */
    public function update(int $id, array $data, int $userId): array
    {
        $current = $this->findById($id);
        if (!$current) {
            return ['success' => false, 'message' => 'Registro de abastecimento não encontrado.'];
        }

        $vehicleId   = (int) $current['vehicle_id'];
        $fuelingDate = !empty($data['fueling_date']) ? trim($data['fueling_date']) : $current['fueling_date'];
        $km          = isset($data['km']) ? (int) $data['km'] : (int) $current['km'];
        $liters      = isset($data['liters']) ? (float) $data['liters'] : (float) $current['liters'];
        $totalPrice  = isset($data['total_price']) ? (float) $data['total_price'] : (float) $current['total_price'];
        $fuelType    = $data['fuel_type'] ?? $current['fuel_type'];
        $stationName = trim($data['station_name'] ?? $current['station_name']);

        if (str_contains($fuelingDate, 'T')) {
            $fuelingDate = str_replace('T', ' ', $fuelingDate);
            if (strlen($fuelingDate) === 16) {
                $fuelingDate .= ':00';
            }
        }

        if ($km <= 0 || $liters <= 0 || $totalPrice <= 0 || empty($stationName)) {
            return ['success' => false, 'message' => 'Dados inválidos fornecidos para atualização.'];
        }

        // Buscar abastecimento anterior para recalcular KM/L
        $stmtPrev = $this->db->prepare("SELECT * FROM fuelings WHERE vehicle_id = :v_id AND id < :id ORDER BY id DESC LIMIT 1");
        $stmtPrev->bindValue(':v_id', $vehicleId, PDO::PARAM_INT);
        $stmtPrev->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtPrev->execute();
        $prevFueling = $stmtPrev->fetch();

        $avgKml = null;
        if ($prevFueling) {
            $kmDiff = $km - (int) $prevFueling['km'];
            if ($kmDiff > 0 && $liters > 0) {
                $avgKml = round($kmDiff / $liters, 2);
            }
        }

        $sql = "UPDATE fuelings 
                SET fueling_date = :fueling_date,
                    km = :km,
                    liters = :liters,
                    total_price = :total_price,
                    fuel_type = :fuel_type,
                    station_name = :station_name,
                    avg_kml = :avg_kml,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':fueling_date', $fuelingDate, PDO::PARAM_STR);
        $stmt->bindValue(':km', $km, PDO::PARAM_INT);
        $stmt->bindValue(':liters', $liters, PDO::PARAM_STR);
        $stmt->bindValue(':total_price', $totalPrice, PDO::PARAM_STR);
        $stmt->bindValue(':fuel_type', $fuelType, PDO::PARAM_STR);
        $stmt->bindValue(':station_name', $stationName, PDO::PARAM_STR);
        $stmt->bindValue(':avg_kml', $avgKml, $avgKml !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':updated_by', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            // Recalcular current_km do veículo para a maior KM de seus abastecimentos
            $this->syncVehicleMaxKm($vehicleId);

            return ['success' => true, 'message' => 'Abastecimento atualizado com sucesso.'];
        }

        return ['success' => false, 'message' => 'Erro ao atualizar abastecimento.'];
    }

    /**
     * Exclusão administrativa de abastecimento (Exclusivo ADMIN).
     */
    public function delete(int $id, int $userId): array
    {
        $current = $this->findById($id);
        if (!$current) {
            return ['success' => false, 'message' => 'Registro de abastecimento não encontrado.'];
        }

        $vehicleId = (int) $current['vehicle_id'];

        $sql = "DELETE FROM fuelings WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            $this->syncVehicleMaxKm($vehicleId);
            return ['success' => true, 'message' => 'Abastecimento excluído com sucesso.'];
        }

        return ['success' => false, 'message' => 'Erro ao excluir abastecimento.'];
    }

    /**
     * Sincroniza a current_km do veículo com a maior KM dos abastecimentos registrados.
     */
    private function syncVehicleMaxKm(int $vehicleId): void
    {
        $stmtMax = $this->db->prepare("SELECT MAX(km) AS max_km FROM fuelings WHERE vehicle_id = :v_id");
        $stmtMax->bindValue(':v_id', $vehicleId, PDO::PARAM_INT);
        $stmtMax->execute();
        $row = $stmtMax->fetch();

        if ($row && $row['max_km'] !== null) {
            $maxKm = (int) $row['max_km'];
            $stmtUp = $this->db->prepare("UPDATE vehicles SET current_km = :max_km, updated_at = NOW() WHERE id = :v_id");
            $stmtUp->bindValue(':max_km', $maxKm, PDO::PARAM_INT);
            $stmtUp->bindValue(':v_id', $vehicleId, PDO::PARAM_INT);
            $stmtUp->execute();
        }
    }
}
