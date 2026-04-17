<?php
require_once __DIR__ . '/Model.php';

class Reclamation extends Model
{
    protected string $table = 'reclamations';

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO reclamations (bureau_ordre_id, date_reclamation, proprietaire_nom, description, document_url, statut, created_by) VALUES (:bureau_ordre_id,:date_reclamation,:proprietaire_nom,:description,:document_url,:statut,:created_by)');
        return $stmt->execute($data);
    }
}
