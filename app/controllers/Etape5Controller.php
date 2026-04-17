<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/DecisionFinale.php';

class Etape5Controller extends BaseController
{
    public function list(): void
    {
        $this->requireRoles(['admin', 'haifa', 'khaoula', 'mohamed', 'viewer']);
        $items = (new DecisionFinale())->all();
        $this->render('etape5/list', ['items' => $items]);
    }

    public function create(): void
    {
        $this->requireRoles(['admin', 'mohamed']);
        $this->render('etape5/create');
    }

    public function show(): void
    {
        $this->requireRoles(['admin', 'haifa', 'khaoula', 'mohamed', 'viewer']);
        $id = (int)($_GET['id'] ?? 0);
        $item = (new DecisionFinale())->find($id);
        $this->render('etape5/show', ['item' => $item]);
    }
}
