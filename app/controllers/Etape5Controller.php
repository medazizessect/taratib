<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/DecisionFinale.php';
require_once __DIR__ . '/../models/RapportExpert.php';

class Etape5Controller extends BaseController
{
    public function list(): void
    {
        AuthController::requireAuth();
        $this->render('etape5/list', ['title' => 'Étape 5 - Décisions', 'items' => (new DecisionFinale())->all()]);
    }

    public function create(): void
    {
        AuthController::requireAuth();
        if (!AuthController::canAccessStep(5)) {
            http_response_code(403);
            exit('Accès refusé');
        }

        $rapports = (new RapportExpert())->all();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new DecisionFinale())->create([
                'rapport_expert_id' => (int) ($_POST['rapport_expert_id'] ?? 0),
                'type_decision' => $_POST['type_decision'] ?? 'قرار إخلاء',
                'date_decision' => $_POST['date_decision'] ?: null,
                'document_url' => trim($_POST['document_url'] ?? ''),
                'details' => trim($_POST['details'] ?? ''),
                'statut' => 'vert',
                'created_by' => $_SESSION['user']['id'],
            ]);
            $this->redirect('etape5/list');
        }

        $this->render('etape5/create', ['title' => 'Créer Décision Finale', 'rapports' => $rapports]);
    }

    public function show(): void
    {
        AuthController::requireAuth();
        $item = (new DecisionFinale())->find((int) ($_GET['id'] ?? 0));
        $this->render('etape5/show', ['title' => 'Détail Décision Finale', 'item' => $item]);
    }
}
