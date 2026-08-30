<?php
/**
 * SisFrota Municipal - Model de Usuário
 */

defined('SYS_ENTRY') or die('Acesso direto negado.');

require_once __DIR__ . '/Database.php';

class User
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getPdo();
    }

    /**
     * Retorna todos os usuários cadastrados.
     */
    public function getAll(): array
    {
        $sql = "SELECT id, username, full_name, role, status, created_at, updated_at 
                FROM users 
                ORDER BY full_name ASC";

        $stmt = $this->db->query($sql);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Busca um usuário pelo nome de usuário (username).
     */
    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT id, username, password_hash, full_name, role, status, created_at 
                FROM users 
                WHERE username = :username 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', trim($username), PDO::PARAM_STR);
        $stmt->execute();

        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Busca um usuário pelo ID.
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT id, username, full_name, role, status, created_at, updated_at 
                FROM users 
                WHERE id = :id 
                LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $user = $stmt->fetch();
        return $user ?: null;
    }

    /**
     * Autentica um usuário verificando username, status ativo e hash da senha.
     * Retorna o array do usuário em sucesso ou false em falha.
     */
    public function authenticate(string $username, string $password): array|false
    {
        $user = $this->findByUsername($username);

        if (!$user) {
            return false;
        }

        // Apenas usuários com status 'ACTIVE' podem se autenticar
        if (($user['status'] ?? '') !== 'ACTIVE') {
            return false;
        }

        // Verifica a senha digitada contra o hash BCrypt gravado no banco
        if (password_verify($password, $user['password_hash'])) {
            return $user;
        }

        return false;
    }

    /**
     * Cadastra um novo usuário.
     */
    public function create(array $data): int|false
    {
        $username   = trim($data['username'] ?? '');
        $fullName   = trim($data['full_name'] ?? '');
        $password   = $data['password'] ?? '';
        $role       = in_array($data['role'] ?? '', ['ADMIN', 'OPERATOR', 'MANAGER'], true) ? $data['role'] : 'OPERATOR';
        $status     = in_array($data['status'] ?? '', ['ACTIVE', 'INACTIVE'], true) ? $data['status'] : 'ACTIVE';
        $createdBy  = $data['created_by'] ?? null;

        if (empty($username) || empty($fullName) || empty($password)) {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, password_hash, full_name, role, status, created_by, created_at)
                VALUES (:username, :password_hash, :full_name, :role, :status, :created_by, NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', $username, PDO::PARAM_STR);
        $stmt->bindValue(':password_hash', $passwordHash, PDO::PARAM_STR);
        $stmt->bindValue(':full_name', $fullName, PDO::PARAM_STR);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':created_by', $createdBy, $createdBy !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);

        if ($stmt->execute()) {
            return (int) $this->db->lastInsertId();
        }

        return false;
    }

    /**
     * Atualiza os dados de um usuário existente (sem alterar a senha).
     */
    public function update(int $id, array $data): bool
    {
        $fullName  = trim($data['full_name'] ?? '');
        $role      = in_array($data['role'] ?? '', ['ADMIN', 'OPERATOR', 'MANAGER'], true) ? $data['role'] : 'OPERATOR';
        $status    = in_array($data['status'] ?? '', ['ACTIVE', 'INACTIVE'], true) ? $data['status'] : 'ACTIVE';
        $updatedBy = $data['updated_by'] ?? null;

        if (empty($fullName)) {
            return false;
        }

        $sql = "UPDATE users 
                SET full_name = :full_name, 
                    role = :role, 
                    status = :status, 
                    updated_by = :updated_by, 
                    updated_at = NOW() 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':full_name', $fullName, PDO::PARAM_STR);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);
        $stmt->bindValue(':status', $status, PDO::PARAM_STR);
        $stmt->bindValue(':updated_by', $updatedBy, $updatedBy !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Atualiza a senha de um usuário.
     */
    public function updatePassword(int $userId, string $newPassword, ?int $updatedBy = null): bool
    {
        if (empty($newPassword)) {
            return false;
        }

        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        $sql = "UPDATE users 
                SET password_hash = :hash, 
                    updated_by = :updated_by, 
                    updated_at = NOW() 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':hash', $hash, PDO::PARAM_STR);
        $stmt->bindValue(':updated_by', $updatedBy, $updatedBy !== null ? PDO::PARAM_INT : PDO::PARAM_NULL);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }

    /**
     * Verifica se já existe um nome de usuário cadastrado (exceto no ID informado).
     */
    public function existsUsername(string $username, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM users WHERE LOWER(username) = LOWER(:username)";
        if ($excludeId !== null) {
            $sql .= " AND id != :exclude_id";
        }
        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', trim($username), PDO::PARAM_STR);
        if ($excludeId !== null) {
            $stmt->bindValue(':exclude_id', $excludeId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return (bool) $stmt->fetch();
    }
}
