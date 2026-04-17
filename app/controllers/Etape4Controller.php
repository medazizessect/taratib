<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/RapportExpert.php';

class Etape4Controller extends BaseController
{
    public function list(): void
    {
        $this->requireRoles(['admin', 'haifa', 'khaoula', 'mohamed', 'viewer']);
        $items = (new RapportExpert())->all();
        $this->render('etape4/list', ['items' => $items]);
    }

    public function create(): void
    {
        $this->requireRoles(['admin', 'khaoula']);
        $this->render('etape4/create');
    }

    public function show(): void
    {
        $this->requireRoles(['admin', 'haifa', 'khaoula', 'mohamed', 'viewer']);
        $id = (int)($_GET['id'] ?? 0);
        $item = (new RapportExpert())->find($id);
        $this->render('etape4/show', ['item' => $item]);
    }
}
