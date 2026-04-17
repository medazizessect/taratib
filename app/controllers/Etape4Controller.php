<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/RapportExpert.php';
require_once __DIR__ . '/../models/EchangeCour.php';

class Etape4Controller extends BaseController
{
    public function list(): void
    {
        AuthController::requireAuth();
        $this->render('etape4/list', ['title' => 'Étape 4 - Rapports Experts', 'items' => (new RapportExpert())->all()]);
    }

    public function create(): void
    {
        AuthController::requireAuth();
        if (!AuthController::canAccessStep(4)) {
            http_response_code(403);
            exit('Accès refusé');
        }

        $echanges = (new EchangeCour())->all();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new RapportExpert())->create([
                'echange_cour_id' => (int) ($_POST['echange_cour_id'] ?? 0),
                'type_rapport' => $_POST['type_rapport'] ?? 'تقرير اختيار اولي',
                'document_url' => trim($_POST['document_url'] ?? ''),
                'decision_patrimoine' => trim($_POST['decision_patrimoine'] ?? ''),
                'date_visite' => $_POST['date_visite'] ?: null,
                'echanges_patrimoine' => json_encode(array_filter(array_map('trim', preg_split('/\R/u', $_POST['echanges_patrimoine'] ?? ''))), JSON_UNESCAPED_UNICODE),
                'created_by' => $_SESSION['user']['id'],
            ]);
            $this->redirect('etape4/list');
        }

        $this->render('etape4/create', ['title' => 'Créer Rapport Expert', 'echanges' => $echanges]);
    }

    public function show(): void
    {
        AuthController::requireAuth();
        $item = (new RapportExpert())->find((int) ($_GET['id'] ?? 0));
        $this->render('etape4/show', ['title' => 'Détail Rapport Expert', 'item' => $item]);
    }
}
