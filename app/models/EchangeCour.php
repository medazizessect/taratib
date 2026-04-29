<?php
require_once __DIR__ . '/Model.php';

class EchangeCour extends Model
{
    protected string $table = 'echanges_cour';
    protected array $allowedOrderColumns = ['id', 'bureau_ordre_id', 'created_at'];

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO echanges_cour (proces_verbal_id, bureau_ordre_id, sujet, type, couleur, document_url, designation_expert, created_by) VALUES (:proces_verbal_id,:bureau_ordre_id,:sujet,:type,:couleur,:document_url,:designation_expert,:created_by)');
        return $stmt->execute($data);
    }
}
