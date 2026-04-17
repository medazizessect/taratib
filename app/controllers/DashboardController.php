<?php
require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/AuthController.php';
require_once __DIR__ . '/../config/Database.php';

class DashboardController extends BaseController
{
    public function index(): void
    {
        AuthController::requireAuth();
        $db = Database::connection();

        $rows = $db->query("SELECT r.id, r.bureau_ordre_id, r.proprietaire_nom, r.date_reclamation,
            CASE
                WHEN d.id IS NOT NULL THEN 'vert'
                WHEN pv.id IS NOT NULL OR ec.id IS NOT NULL OR re.id IS NOT NULL THEN 'orange'
                ELSE 'rouge'
            END AS couleur
            FROM reclamations r
            LEFT JOIN proces_verbaux pv ON pv.reclamation_id = r.id
            LEFT JOIN echanges_cour ec ON ec.proces_verbal_id = pv.id
            LEFT JOIN rapports_experts re ON re.echange_cour_id = ec.id
            LEFT JOIN decisions_finales d ON d.rapport_expert_id = re.id
            ORDER BY r.id DESC")->fetchAll();

        $notifications = $db->prepare('SELECT * FROM notifications WHERE user_id = :uid ORDER BY id DESC LIMIT 10');
        $notifications->execute(['uid' => $_SESSION['user']['id']]);

        $this->render('dashboard', [
            'title' => 'Dashboard',
            'rows' => $rows,
            'notifications' => $notifications->fetchAll(),
        ]);
    }
}
