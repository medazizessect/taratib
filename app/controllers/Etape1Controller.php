<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Reclamation.php';

class Etape1Controller extends BaseController
{
    public function list(): void
    {
        $this->requireRoles(['admin', 'haifa', 'khaoula', 'mohamed', 'viewer']);
        $items = (new Reclamation())->all();
        $this->render('etape1/list', ['items' => $items]);
    }

    public function create(): void
    {
        $this->requireRoles(['admin', 'haifa']);
        $this->render('etape1/create');
    }

    public function show(): void
    {
        $this->requireRoles(['admin', 'haifa', 'khaoula', 'mohamed', 'viewer']);
        $id = (int)($_GET['id'] ?? 0);
        $item = (new Reclamation())->find($id);
        $this->render('etape1/show', ['item' => $item]);
    }
}
