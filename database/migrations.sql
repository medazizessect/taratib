CREATE DATABASE IF NOT EXISTS taratib CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE taratib;

CREATE TABLE `users` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `username` VARCHAR(100) UNIQUE,
  `password` VARCHAR(255),
  `full_name` VARCHAR(200),
  `role` ENUM('admin','haifa','khaoula','mohamed','viewer'),
  `email` VARCHAR(100),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `lieux` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `adresse_libelle` VARCHAR(500) CHARACTER SET utf8mb4,
  `code` VARCHAR(50),
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `reclamations` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `bureau_ordre_id` VARCHAR(50),
  `date_reclamation` DATE,
  `proprietaire_nom` VARCHAR(200) CHARACTER SET utf8mb4,
  `description` LONGTEXT CHARACTER SET utf8mb4,
  `document_url` VARCHAR(500),
  `statut` ENUM('rouge','orange','vert') DEFAULT 'rouge',
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `proces_verbaux` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `reclamation_id` INT,
  `numero_pv` INT,
  `annee` YEAR DEFAULT 2026,
  `date_pv` DATE,
  `cin_proprietaire` VARCHAR(20),
  `proprietaire_nom` VARCHAR(200) CHARACTER SET utf8mb4,
  `exploitant_nom` VARCHAR(200) CHARACTER SET utf8mb4,
  `est_exploite` BOOLEAN DEFAULT FALSE,
  `lieu_id` INT,
  `description_situation` LONGTEXT CHARACTER SET utf8mb4,
  `degre_confirmation` INT,
  `directive_ministere` TEXT CHARACTER SET utf8mb4,
  `membres_comite` JSON,
  `date_reunion` DATETIME,
  `pdf_url` VARCHAR(500),
  `statut` ENUM('brouillon','finalisé','imprimé') DEFAULT 'brouillon',
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`reclamation_id`) REFERENCES `reclamations`(`id`),
  FOREIGN KEY (`lieu_id`) REFERENCES `lieux`(`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `echanges_cour` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `proces_verbal_id` INT,
  `bureau_ordre_id` VARCHAR(50),
  `sujet` VARCHAR(500) CHARACTER SET utf8mb4,
  `type` ENUM('صادر','وارد') CHARACTER SET utf8mb4,
  `couleur` ENUM('orange','vert'),
  `document_url` VARCHAR(500),
  `designation_expert` VARCHAR(300) CHARACTER SET utf8mb4,
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`proces_verbal_id`) REFERENCES `proces_verbaux`(`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `rapports_experts` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `echange_cour_id` INT,
  `type_rapport` ENUM('تقرير اختيار اولي','تقرير اختبار نهائي') CHARACTER SET utf8mb4,
  `document_url` VARCHAR(500),
  `decision_patrimoine` VARCHAR(255) CHARACTER SET utf8mb4,
  `date_visite` DATE,
  `echanges_patrimoine` JSON,
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`echange_cour_id`) REFERENCES `echanges_cour`(`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `decisions_finales` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `rapport_expert_id` INT,
  `type_decision` ENUM('قرار إخلاء','قرار هدم') CHARACTER SET utf8mb4,
  `date_decision` DATE,
  `document_url` VARCHAR(500),
  `details` LONGTEXT CHARACTER SET utf8mb4,
  `statut` ENUM('vert') DEFAULT 'vert',
  `created_by` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`rapport_expert_id`) REFERENCES `rapports_experts`(`id`),
  FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE TABLE `notifications` (
  `id` INT PRIMARY KEY AUTO_INCREMENT,
  `user_id` INT,
  `type` VARCHAR(100),
  `message` TEXT CHARACTER SET utf8mb4,
  `link` VARCHAR(500),
  `is_read` BOOLEAN DEFAULT FALSE,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
