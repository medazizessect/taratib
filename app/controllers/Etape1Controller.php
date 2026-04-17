<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../models/Reclamation.php';

class Etape1Controller extends BaseController
{
    public function list(): void
    {
        AuthController::requireAuth();
        $model = new Reclamation();
        $this->render('etape1/list', ['title' => 'Étape 1 - Réclamations', 'items' => $model->all()]);
    }

    public function create(): void
    {
        AuthController::requireAuth();
        if (!AuthController::canAccessStep(1)) {
            http_response_code(403);
            exit('Accès refusé');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $model = new Reclamation();
            $model->create([
                'bureau_ordre_id' => trim($_POST['bureau_ordre_id'] ?? ''),
                'date_reclamation' => $_POST['date_reclamation'] ?: null,
                'proprietaire_nom' => trim($_POST['proprietaire_nom'] ?? ''),
                'description' => trim($_POST['description'] ?? ''),
                'document_url' => trim($_POST['document_url'] ?? ''),
                'statut' => 'rouge',
                'created_by' => $_SESSION['user']['id'],
            ]);
            $this->redirect('etape1/list');
        }

        $this->render('etape1/create', ['title' => 'Créer Réclamation']);
    }

    public function show(): void
    {
        AuthController::requireAuth();
        $item = (new Reclamation())->find((int) ($_GET['id'] ?? 0));
        $this->render('etape1/show', ['title' => 'Détail Réclamation', 'item' => $item]);
    }
}
