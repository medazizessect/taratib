<?php
require_once __DIR__ . '/BaseModel.php';

class User extends BaseModel
{
    protected string $table = 'users';

    public function findByUsername(string $username): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        return $stmt->fetch() ?: null;
    }
}
