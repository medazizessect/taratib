<?php
require_once __DIR__ . '/../config/Database.php';

abstract class BaseModel
{
    protected PDO $db;
    protected string $table;
    private const ALLOWED_TABLES = [
        'users',
        'lieux',
        'reclamations',
        'proces_verbaux',
        'echanges_cour',
        'rapports_experts',
        'decisions_finales',
    ];

    public function __construct()
    {
        $this->db = Database::getConnection();
        if ($this->table === '' || !in_array($this->table, self::ALLOWED_TABLES, true)) {
            throw new RuntimeException('Invalid table configuration.');
        }
    }

    public function all(): array
    {
        return $this->db->query("SELECT * FROM {$this->table} ORDER BY id DESC")->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id");
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }
}
