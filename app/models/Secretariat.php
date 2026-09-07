<?php
/**
 * SisFrota Municipal - Model de Secretaria
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once __DIR__ . '/Database.php';

class Secretariat
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getPdo();
    }

    /**
     * Retorna todas as secretarias cadastradas ordenadas pelo nome.
     */
    public function getAll(): array
    {
        $sql = "SELECT s.*, 
                       (SELECT COUNT(*) FROM vehicles v WHERE v.secretariat_id = s.id AND v.is_deleted = 0) AS total_vehicles
                FROM secretariats s
                ORDER BY s.name ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Alias de getAll() para padronização.
     */
    public function findAll(): array
    {
        return $this->getAll();
    }

    /**
     * Busca uma secretaria pelo ID.
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT * FROM secretariats WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Alias de getById() para padronização.
     */
    public function findById(int $id): ?array
    {
        return $this->getById($id);
    }

    /**
     * Cadastra uma nova secretaria.
     */
    public function create(array $data): int|false
    {
        $name      = trim($data['name'] ?? '');
        $acronym   = !empty($data['acronym']) ? trim($data['acronym']) : null;
        $createdBy = $data['created_by'] ?? null;

        if (empty($name)) {
            return false;
        }

        $sql = "INSERT INTO secretariats (name, acronym, created_by, created_at)
                VALUES (:name, :acronym, :created_by, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':acronym', $acronym, $acronym !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':created_by', $createdBy, $createdBy !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

        if ($stmt->execute()) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Atualiza os dados de uma secretaria existente.
     */
    public function update(int $id, array $data): bool
    {
        $name      = trim($data['name'] ?? '');
        $acronym   = !empty($data['acronym']) ? trim($data['acronym']) : null;
        $updatedBy = $data['updated_by'] ?? null;

        if (empty($name)) {
            return false;
        }

        $sql = "UPDATE secretariats 
                SET name = :name, 
                    acronym = :acronym, 
                    updated_by = :updated_by, 
                    updated_at = NOW() 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':acronym', $acronym, $acronym !== null ? PDO::PARAM_STR : PDO::PARAM_NULL);
        $stmt->bindValue(':updated_by', $updatedBy, $updatedBy !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Remove uma secretaria se não houver veículos vinculados.
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM secretariats WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Verifica se já existe uma secretaria com o mesmo nome (exceto a informada no id).
     */
    public function existsName(string $name, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM secretariats WHERE LOWER(name) = LOWER(:name)";
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
