<?php
require_once __DIR__ . '/Model.php';

class RapportExpert extends Model
{
    protected string $table = 'rapports_experts';
    protected array $allowedOrderColumns = ['id', 'date_visite', 'created_at'];

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO rapports_experts (echange_cour_id, type_rapport, document_url, decision_patrimoine, date_visite, echanges_patrimoine, created_by) VALUES (:echange_cour_id,:type_rapport,:document_url,:decision_patrimoine,:date_visite,:echanges_patrimoine,:created_by)');
        return $stmt->execute($data);
    }
}
