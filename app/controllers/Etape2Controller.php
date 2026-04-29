<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/ProcesVerbal.php';
require_once __DIR__ . '/../models/Lieu.php';

class Etape2Controller extends BaseController
{
    public function list(): void
    {
        $this->requireRoles(['admin', 'haifa', 'khaoula', 'mohamed', 'viewer']);
        $items = (new ProcesVerbal())->all();
        $this->render('etape2/list', ['items' => $items]);
    }

    public function create(): void
    {
        $this->requireRoles(['admin', 'haifa']);
        $lieux = (new Lieu())->all();
        $this->render('etape2/create', ['lieux' => $lieux]);
    }

    public function show(): void
    {
        $this->requireRoles(['admin', 'haifa', 'khaoula', 'mohamed', 'viewer']);
        $id = (int)($_GET['id'] ?? 0);
        $item = (new ProcesVerbal())->find($id);
        $this->render('etape2/show', ['item' => $item]);
    }

    public function edit(): void
    {
        $this->requireRoles(['admin', 'haifa']);
        $id = (int)($_GET['id'] ?? 0);
        $item = (new ProcesVerbal())->find($id);
        $lieux = (new Lieu())->all();
        $this->render('etape2/edit', ['item' => $item, 'lieux' => $lieux]);
    }
}
