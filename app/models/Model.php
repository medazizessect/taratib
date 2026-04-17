<?php
require_once __DIR__ . '/../config/Database.php';

abstract class Model
{
    protected PDO $db;
    protected string $table;
    protected array $allowedOrderColumns = ['id'];

    public function __construct()
    {
        $this->db = Database::connection();
    }

    public function find(int $id): ?array
    {
        if (!preg_match('/^[a-z_]+$/', $this->table)) {
            throw new RuntimeException('Invalid table name');
        }
        $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function all(string $order = 'id DESC'): array
    {
        if (!preg_match('/^[a-z_]+$/', $this->table)) {
            throw new RuntimeException('Invalid table name');
        }
        $order = $this->sanitizeOrder($order);
        $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY {$order}");
        return $stmt->fetchAll();
    }

    private function sanitizeOrder(string $order): string
    {
        if (!preg_match('/^([a-z_]+)\s+(ASC|DESC)$/i', trim($order), $matches)) {
            return 'id DESC';
        }

        $column = strtolower($matches[1]);
        $direction = strtoupper($matches[2]);
        if (!in_array($column, $this->allowedOrderColumns, true)) {
            return 'id DESC';
        }

        return $column . ' ' . $direction;
    }
}
