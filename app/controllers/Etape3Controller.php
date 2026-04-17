<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/EchangeCour.php';

class Etape3Controller extends BaseController
{
    public function list(): void
    {
        $this->requireRoles(['admin', 'haifa', 'khaoula', 'mohamed', 'viewer']);
        $items = (new EchangeCour())->all();
        $this->render('etape3/list', ['items' => $items]);
    }

    public function create(): void
    {
        $this->requireRoles(['admin', 'khaoula']);
        $this->render('etape3/create');
    }

    public function show(): void
    {
        $this->requireRoles(['admin', 'haifa', 'khaoula', 'mohamed', 'viewer']);
        $id = (int)($_GET['id'] ?? 0);
        $item = (new EchangeCour())->find($id);
        $this->render('etape3/show', ['item' => $item]);
    }
}
