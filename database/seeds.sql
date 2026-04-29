USE taratib;

INSERT INTO users (username, password, full_name, role, email) VALUES
('admin',   '$2y$10$gdzsXYGwW3gwMup655RqEeAsw19SWF08QX4dB43Q580.j9wM8mHTu', 'Administrateur', 'admin',   'admin@taratib.local'),
('haifa',   '$2y$10$/4QHHsZvFHePVRJN//OsluaV1lyg.GHJyfWo.WX0CmMPHg8AIBTMa', 'هيفاء', 'haifa',   'haifa@taratib.local'),
('khaoula', '$2y$10$6NBPVz/cA.fGOCQhMICoOeQYz69IygRsNApLgYHBUw6fApmt/nkvS', 'خولة', 'khaoula', 'khaoula@taratib.local'),
('mohamed', '$2y$10$XjQ8GD0TUsHqrTqnGHDKsuzW.HWnwJpON6p9tm2Py/8dBJsPVWwue', 'محمد', 'mohamed', 'mohamed@taratib.local'),
('viewer',  '$2y$10$NkhHX1sPizxuj2UNW.G9pOjyPwPQ65G1w8XAm.b4YmpyK4hdvPsQ.', 'Lecteur', 'viewer',  'viewer@taratib.local');

INSERT INTO lieux (adresse_libelle, code) VALUES
('شارع الحبيب بورقيبة - سوسة', 'L001'),
('نهج المنزه - سوسة', 'L002'),
('حي الرياض - سوسة', 'L003');
