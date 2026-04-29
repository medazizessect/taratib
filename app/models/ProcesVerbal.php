<?php
require_once __DIR__ . '/Model.php';

class ProcesVerbal extends Model
{
    protected string $table = 'proces_verbaux';
    protected array $allowedOrderColumns = ['id', 'numero_pv', 'annee', 'date_pv', 'created_at'];

    public function nextNumeroForYear(int $year): int
    {
        $stmt = $this->db->prepare('SELECT COALESCE(MAX(numero_pv), 0) + 1 AS next_num FROM proces_verbaux WHERE annee = :annee');
        $stmt->execute(['annee' => $year]);
        return (int) $stmt->fetch()['next_num'];
    }

    public function create(array $data): bool
    {
        $stmt = $this->db->prepare('INSERT INTO proces_verbaux (reclamation_id, numero_pv, annee, date_pv, cin_proprietaire, proprietaire_nom, exploitant_nom, est_exploite, lieu_id, description_situation, degre_confirmation, directive_ministere, membres_comite, date_reunion, statut, created_by) VALUES (:reclamation_id,:numero_pv,:annee,:date_pv,:cin_proprietaire,:proprietaire_nom,:exploitant_nom,:est_exploite,:lieu_id,:description_situation,:degre_confirmation,:directive_ministere,:membres_comite,:date_reunion,:statut,:created_by)');
        return $stmt->execute($data);
    }

    public function updateById(int $id, array $data): bool
    {
        $data['id'] = $id;
        $stmt = $this->db->prepare('UPDATE proces_verbaux SET date_pv=:date_pv, cin_proprietaire=:cin_proprietaire, proprietaire_nom=:proprietaire_nom, exploitant_nom=:exploitant_nom, est_exploite=:est_exploite, lieu_id=:lieu_id, description_situation=:description_situation, degre_confirmation=:degre_confirmation, directive_ministere=:directive_ministere, membres_comite=:membres_comite, date_reunion=:date_reunion, statut=:statut WHERE id=:id');
        return $stmt->execute($data);
    }
}
