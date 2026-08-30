<?php
/**
 * SisFrota Municipal - Model de Categoria de Manutenção
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once __DIR__ . '/Database.php';

class MaintenanceCategory
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getPdo();
    }

    /**
     * Retorna todas as categorias de manutenção.
     */
    public function getAll(bool $onlyActive = false): array
    {
        $sql = "SELECT c.*, 
                       (SELECT COUNT(*) FROM maintenances m WHERE m.category_id = c.id) AS total_maintenances
                FROM maintenance_categories c";
        if ($onlyActive) {
            $sql .= " WHERE c.status = 'ACTIVE'";
        }
        $sql .= " ORDER BY c.name ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Busca uma categoria pelo ID.
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM maintenance_categories WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Cadastra uma nova categoria de manutenção.
     */
    public function create(array $data): int|false
    {
        $name        = trim($data['name'] ?? '');
        $description = !empty($data['description']) ? trim($data['description']) : null;
        $status      = in_array($data['status'] ?? '', ['ACTIVE', 'INACTIVE'], true) ? $data['status'] : 'ACTIVE';
        $createdBy   = $data['created_by'] ?? null;

        if (empty($name)) {
            return false;
        }

        $sql = "INSERT INTO maintenance_categories (name, description, status, created_by, created_at)
                VALUES (:name, :description, :status, :created_by, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, $description !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':created_by', $createdBy, $createdBy !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

        if ($stmt->execute()) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Atualiza os dados de uma categoria de manutenção.
     */
    public function update(int $id, array $data): bool
    {
        $name        = trim($data['name'] ?? '');
        $description = !empty($data['description']) ? trim($data['description']) : null;
        $status      = in_array($data['status'] ?? '', ['ACTIVE', 'INACTIVE'], true) ? $data['status'] : 'ACTIVE';
        $updatedBy   = $data['updated_by'] ?? null;

        if (empty($name)) {
            return false;
        }

        $sql = "UPDATE maintenance_categories 
                SET name = :name, 
                    description = :description, 
                    status = :status, 
                    updated_by = :updated_by, 
                    updated_at = NOW() 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':description', $description, $description !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':updated_by', $updatedBy, $updatedBy !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Alterna o status (ACTIVE / INACTIVE) de uma categoria.
     */
    public function toggleStatus(int $id, ?int $updatedBy = null): bool
    {
        $category = $this->getById($id);
        if (!$category) {
            return false;
        }

        $newStatus = ($category['status'] === 'ACTIVE') ? 'INACTIVE' : 'ACTIVE';

        $sql = "UPDATE maintenance_categories 
                SET status = :status, 
                    updated_by = :updated_by, 
                    updated_at = NOW() 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $newStatus, PDO::PARAM_STR);
        $stmt->bindValue(':updated_by', $updatedBy, $updatedBy !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Remove uma categoria de manutenção se não houver registros associados.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM maintenance_categories WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Verifica se já existe uma categoria com o mesmo nome (exceto no ID informado).
     */
    public function existsName(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM maintenance_categories WHERE LOWER(name) = LOWER(:name)";
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', trim($name), PDO::PARAM_STR);
        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return (bool) $stmt->fetch();
    }
}
