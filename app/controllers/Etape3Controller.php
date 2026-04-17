<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/EchangeCour.php';
require_once __DIR__ . '/../models/ProcesVerbal.php';

class Etape3Controller extends BaseController
{
    public function list(): void
    {
        AuthController::requireAuth();
        $this->render('etape3/list', ['title' => 'Étape 3 - Échanges Cour', 'items' => (new EchangeCour())->all()]);
    }

    public function create(): void
    {
        AuthController::requireAuth();
        if (!AuthController::canAccessStep(3)) {
            http_response_code(403);
            exit('Accès refusé');
        }

        $procesVerbaux = (new ProcesVerbal())->all();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            (new EchangeCour())->create([
                'proces_verbal_id' => (int) ($_POST['proces_verbal_id'] ?? 0),
                'bureau_ordre_id' => trim($_POST['bureau_ordre_id'] ?? ''),
                'sujet' => trim($_POST['sujet'] ?? ''),
                'type' => $_POST['type'] ?? 'صادر',
                'couleur' => $_POST['couleur'] ?? 'orange',
                'document_url' => trim($_POST['document_url'] ?? ''),
                'designation_expert' => trim($_POST['designation_expert'] ?? ''),
                'created_by' => $_SESSION['user']['id'],
            ]);
            $this->redirect('etape3/list');
        }

        $this->render('etape3/create', ['title' => 'Créer Échange Cour', 'procesVerbaux' => $procesVerbaux]);
    }

    public function show(): void
    {
        AuthController::requireAuth();
        $item = (new EchangeCour())->find((int) ($_GET['id'] ?? 0));
        $this->render('etape3/show', ['title' => 'Détail Échange Cour', 'item' => $item]);
    }
}
