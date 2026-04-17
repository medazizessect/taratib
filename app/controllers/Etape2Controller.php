<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/ProcesVerbal.php';
require_once __DIR__ . '/../models/Reclamation.php';
require_once __DIR__ . '/../models/Lieu.php';

class Etape2Controller extends BaseController
{
    public function list(): void
    {
        AuthController::requireAuth();
        $this->render('etape2/list', ['title' => 'Étape 2 - Procès-verbaux', 'items' => (new ProcesVerbal())->all()]);
    }

    public function create(): void
    {
        AuthController::requireAuth();
        if (!AuthController::canAccessStep(2)) {
            http_response_code(403);
            exit('Accès refusé');
        }

        $pv = new ProcesVerbal();
        $year = (int) date('Y');
        $nextNumero = $pv->nextNumeroForYear($year);
        $reclamations = (new Reclamation())->all();
        $lieux = (new Lieu())->all('adresse_libelle ASC');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $membres = array_values(array_filter(array_map('trim', $_POST['membres_comite'] ?? [])));
            $pv->create([
                'reclamation_id' => (int) ($_POST['reclamation_id'] ?? 0),
                'numero_pv' => (int) ($_POST['numero_pv'] ?? $nextNumero),
                'annee' => (int) ($_POST['annee'] ?? $year),
                'date_pv' => $_POST['date_pv'] ?: null,
                'cin_proprietaire' => trim($_POST['cin_proprietaire'] ?? ''),
                'proprietaire_nom' => trim($_POST['proprietaire_nom'] ?? ''),
                'exploitant_nom' => trim($_POST['exploitant_nom'] ?? ''),
                'est_exploite' => isset($_POST['est_exploite']) ? 1 : 0,
                'lieu_id' => (int) ($_POST['lieu_id'] ?? 0),
                'description_situation' => trim($_POST['description_situation'] ?? ''),
                'degre_confirmation' => (int) ($_POST['degre_confirmation'] ?? 1),
                'directive_ministere' => trim($_POST['directive_ministere'] ?? ''),
                'membres_comite' => json_encode($membres, JSON_UNESCAPED_UNICODE),
                'date_reunion' => $_POST['date_reunion'] ?: null,
                'statut' => 'finalisé',
                'created_by' => $_SESSION['user']['id'],
            ]);
            $this->redirect('etape2/list');
        }

        $this->render('etape2/create', [
            'title' => 'Créer Procès-verbal',
            'nextNumero' => $nextNumero,
            'year' => $year,
            'reclamations' => $reclamations,
            'lieux' => $lieux,
        ]);
    }

    public function edit(): void
    {
        AuthController::requireAuth();
        if (!AuthController::canAccessStep(2)) {
            http_response_code(403);
            exit('Accès refusé');
        }

        $id = (int) ($_GET['id'] ?? 0);
        $model = new ProcesVerbal();
        $item = $model->find($id);
        if (!$item) {
            http_response_code(404);
            exit('Introuvable');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $membres = array_values(array_filter(array_map('trim', $_POST['membres_comite'] ?? [])));
            $model->updateById($id, [
                'date_pv' => $_POST['date_pv'] ?: null,
                'cin_proprietaire' => trim($_POST['cin_proprietaire'] ?? ''),
                'proprietaire_nom' => trim($_POST['proprietaire_nom'] ?? ''),
                'exploitant_nom' => trim($_POST['exploitant_nom'] ?? ''),
                'est_exploite' => isset($_POST['est_exploite']) ? 1 : 0,
                'lieu_id' => (int) ($_POST['lieu_id'] ?? 0),
                'description_situation' => trim($_POST['description_situation'] ?? ''),
                'degre_confirmation' => (int) ($_POST['degre_confirmation'] ?? 1),
                'directive_ministere' => trim($_POST['directive_ministere'] ?? ''),
                'membres_comite' => json_encode($membres, JSON_UNESCAPED_UNICODE),
                'date_reunion' => $_POST['date_reunion'] ?: null,
                'statut' => $_POST['statut'] ?? 'brouillon',
            ]);
            $this->redirect('etape2/show', ['id' => $id]);
        }

        $lieux = (new Lieu())->all('adresse_libelle ASC');
        $this->render('etape2/edit', ['title' => 'Modifier Procès-verbal', 'item' => $item, 'lieux' => $lieux]);
    }

    public function show(): void
    {
        AuthController::requireAuth();
        $item = (new ProcesVerbal())->find((int) ($_GET['id'] ?? 0));
        $this->render('etape2/show', ['title' => 'Détail Procès-verbal', 'item' => $item]);
    }

    public function pdfTemplate(): void
    {
        AuthController::requireAuth();
        $item = (new ProcesVerbal())->find((int) ($_GET['id'] ?? 0));
        $this->render('etape2/pdf_template', ['title' => 'Template PV', 'item' => $item]);
    }
}
