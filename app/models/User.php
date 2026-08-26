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
        $sql = "SELECT id, username, full_name, role, status, created_at 
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
     * Atualiza a senha de um usuário.
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);

        $sql = "UPDATE users 
                SET password_hash = :hash, updated_at = NOW() 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':hash', $hash, PDO::PARAM_STR);
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);

        return $stmt->execute();
    }
}
