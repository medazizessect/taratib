<?php
require_once __DIR__ . '/Model.php';

class DecisionFinale extends Model
{
    protected string $table = 'decisions_finales';

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO decisions_finales (rapport_expert_id, type_decision, date_decision, document_url, details, statut, created_by) VALUES (:rapport_expert_id,:type_decision,:date_decision,:document_url,:details,:statut,:created_by)');
        return $stmt->execute($data);
    }
}
