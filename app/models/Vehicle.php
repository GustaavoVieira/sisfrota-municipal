<?php
/**
 * SisFrota Municipal - Model de Veículo
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/../helpers/FormatHelper.php';

class Vehicle
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getPdo();
    }

    /**
     * Retorna lista de veículos filtrada com dados da secretaria associada.
     */
    public function findAll(array $filters = [], bool $includeDeleted = false): array
    {
        $sql = "SELECT v.*, 
                       s.name AS secretariat_name, 
                       s.acronym AS secretariat_acronym
                FROM vehicles v
                LEFT JOIN secretariats s ON v.secretariat_id = s.id
                WHERE 1=1";

        $params = [];

        if (!$includeDeleted) {
            $sql .= " AND v.is_deleted = 0";
        }

        if (!empty($filters['secretariat_id'])) {
            $sql .= " AND v.secretariat_id = :secretariat_id";
            $params[':secretariat_id'] = (int) $filters['secretariat_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND v.status = :status";
            $params[':status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $search = '%' . strtolower(trim($filters['search'])) . '%';
            $sql .= " AND (LOWER(v.plate) LIKE :search OR LOWER(v.model) LIKE :search OR LOWER(v.brand) LIKE :search)";
            $params[':search'] = $search;
        }

        $sql .= " ORDER BY v.id DESC";

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
     * Busca veículo por ID.
     */
    public function findById(int $id, bool $includeDeleted = false): ?array
    {
        $sql = "SELECT v.*, 
                       s.name AS secretariat_name, 
                       s.acronym AS secretariat_acronym
                FROM vehicles v
                LEFT JOIN secretariats s ON v.secretariat_id = s.id
                WHERE v.id = :id";

        if (!$includeDeleted) {
            $sql .= " AND v.is_deleted = 0";
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Busca veículo pela placa limpa.
     */
    public function findByPlate(string $plate, ?int $excludeId = null): ?array
    {
        $cleanPlate = FormatHelper::cleanPlate($plate);

        $sql = "SELECT * FROM vehicles WHERE plate = :plate";
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':plate', $cleanPlate, PDO::PARAM_STR);
        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cadastra um novo veículo.
     */
    public function create(array $data, int $userId): int|false
    {
        $plate         = FormatHelper::cleanPlate($data['plate'] ?? '');
        $model         = trim($data['model'] ?? '');
        $brand         = !empty($data['brand']) ? trim($data['brand']) : null;
        $year          = !empty($data['year']) ? (int) $data['year'] : null;
        $secretariatId = !empty($data['secretariat_id']) ? (int) $data['secretariat_id'] : null;
        $fuelType      = $data['fuel_type'] ?? 'FLEX';
        $currentKm     = isset($data['current_km']) ? (int) $data['current_km'] : 0;
        $status        = $data['status'] ?? 'ACTIVE';

        if (empty($plate) || empty($model)) {
            return false;
        }

        $sql = "INSERT INTO vehicles 
                (plate, model, brand, year, secretariat_id, fuel_type, current_km, status, is_deleted, created_by, created_at)
                VALUES 
                (:plate, :model, :brand, :year, :secretariat_id, :fuel_type, :current_km, :status, 0, :created_by, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':plate', $plate, PDO::PARAM_STR);
        $stmt->bindValue(':model', $model, PDO::PARAM_STR);
        $stmt->bindValue(':brand', $brand, $brand !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':year', $year, $year !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':secretariat_id', $secretariatId, $secretariatId !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':fuel_type', $fuelType, PDO::PARAM_STR);
        $stmt->bindValue(':current_km', $currentKm, PDO::PARAM_INT);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':created_by', $userId, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Atualiza os dados de um veículo existente.
     */
    public function update(int $id, array $data, int $userId): bool
    {
        $plate         = FormatHelper::cleanPlate($data['plate'] ?? '');
        $model         = trim($data['model'] ?? '');
        $brand         = !empty($data['brand']) ? trim($data['brand']) : null;
        $year          = !empty($data['year']) ? (int) $data['year'] : null;
        $secretariatId = !empty($data['secretariat_id']) ? (int) $data['secretariat_id'] : null;
        $fuelType      = $data['fuel_type'] ?? 'FLEX';
        $currentKm     = isset($data['current_km']) ? (int) $data['current_km'] : 0;
        $status        = $data['status'] ?? 'ACTIVE';

        if (empty($plate) || empty($model)) {
            return false;
        }

        $sql = "UPDATE vehicles 
                SET plate = :plate,
                    model = :model,
                    brand = :brand,
                    year = :year,
                    secretariat_id = :secretariat_id,
                    fuel_type = :fuel_type,
                    current_km = :current_km,
                    status = :status,
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':plate', $plate, PDO::PARAM_STR);
        $stmt->bindValue(':model', $model, PDO::PARAM_STR);
        $stmt->bindValue(':brand', $brand, $brand !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':year', $year, $year !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':secretariat_id', $secretariatId, $secretariatId !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':fuel_type', $fuelType, PDO::PARAM_STR);
        $stmt->bindValue(':current_km', $currentKm, PDO::PARAM_INT);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':updated_by', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Inativa o veículo (Soft Delete) mantendo os registros passados para auditoria.
     */
    public function softDelete(int $id, int $userId): bool
    {
        $sql = "UPDATE vehicles 
                SET is_deleted = 1, 
                    deleted_at = NOW(), 
                    status = 'UNAVAILABLE',
                    updated_by = :updated_by,
                    updated_at = NOW()
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':updated_by', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Tenta excluir o veículo fisicamente do banco de dados (Hard Delete).
     * Se houver registros transacionais (FK restriction), retorna mensagem amigável para inativação.
     */
    public function hardDelete(int $id): array
    {
        try {
            $sql = "DELETE FROM vehicles WHERE id = :id";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'success' => true,
                'message' => 'Veículo excluído permanentemente do sistema com sucesso.'
            ];
        } catch (PDOException $e) {
            // Código SQLSTATE 23000 indica violação de restrição de chave estrangeira
            if ($e->getCode() === '23000') {
                return [
                    'success' => false,
                    'message' => 'Não é possível excluir permanentemente este veículo pois ele possui registros históricos de abastecimento ou manutenção vinculados. Para desativá-lo com segurança mantendo a auditoria financeira, utilize a opção "Inativar".'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro ao tentar excluir veículo permanentemente: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Retorna o histórico de abastecimentos de um veículo.
     */
    public function getFuelingHistory(int $vehicleId): array
    {
        $sql = "SELECT f.*, u.full_name AS creator_name 
                FROM fuelings f 
                LEFT JOIN users u ON f.created_by = u.id 
                WHERE f.vehicle_id = :vehicle_id 
                ORDER BY f.fueling_date DESC, f.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':vehicle_id', $vehicleId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Retorna o histórico de manutenções de um veículo.
     */
    public function getMaintenanceHistory(int $vehicleId): array
    {
        $sql = "SELECT m.*, 
                       mc.name AS category_name, 
                       u.full_name AS creator_name 
                FROM maintenances m 
                LEFT JOIN maintenance_categories mc ON m.category_id = mc.id 
                LEFT JOIN users u ON m.created_by = u.id 
                WHERE m.vehicle_id = :vehicle_id 
                ORDER BY m.service_date DESC, m.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':vehicle_id', $vehicleId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll() ?: [];
    }

    /**
     * Calcula a média de consumo geral do veículo em KM/L.
     */
    public function calculateOverallAvgKml(int $vehicleId): ?float
    {
        $sql = "SELECT AVG(avg_kml) AS avg_kml 
                FROM fuelings 
                WHERE vehicle_id = :vehicle_id AND avg_kml IS NOT NULL";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':vehicle_id', $vehicleId, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch();
        return ($row && $row['avg_kml'] !== null) ? (float) $row['avg_kml'] : null;
    }
}
