USE `taratib`;

INSERT INTO users (username, password, full_name, role, email) VALUES
('admin',   '$2y$10$2frA2XYRij4XzLWVf8h1uO07M3N4QifAN4BfWRM6rWp0j9qPJ4uj6', 'Administrateur', 'admin',   'admin@taratib.local'),
('haifa',   '$2y$10$2frA2XYRij4XzLWVf8h1uO07M3N4QifAN4BfWRM6rWp0j9qPJ4uj6', 'Haifa',         'haifa',   'haifa@taratib.local'),
('khaoula', '$2y$10$2frA2XYRij4XzLWVf8h1uO07M3N4QifAN4BfWRM6rWp0j9qPJ4uj6', 'Khaoula',       'khaoula', 'khaoula@taratib.local'),
('mohamed', '$2y$10$2frA2XYRij4XzLWVf8h1uO07M3N4QifAN4BfWRM6rWp0j9qPJ4uj6', 'Mohamed',       'mohamed', 'mohamed@taratib.local'),
('viewer',  '$2y$10$2frA2XYRij4XzLWVf8h1uO07M3N4QifAN4BfWRM6rWp0j9qPJ4uj6', 'Viewer',        'viewer',  'viewer@taratib.local');

INSERT INTO reclamations (bureau_ordre_id, date_reclamation, proprietaire_nom, description, document_url, statut, created_by)
VALUES ('BO-001', '2026-01-10', 'Ahmed Ben Ali', 'Réclamation initiale', '', 'rouge', 1);

INSERT INTO notifications (user_id, type, message, link) VALUES
(1, 'pending', 'Des dossiers nécessitent un traitement.', '/index.php?route=dashboard');
