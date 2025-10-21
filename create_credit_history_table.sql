-- Crear tabla credit_histories
CREATE TABLE IF NOT EXISTS `credit_histories` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` bigint(20) unsigned NOT NULL,
  `type` enum('credit_granted','credit_used','payment_received','credit_adjustment') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `balance` decimal(15,2) NOT NULL,
  `description` text NOT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `reference_type` varchar(255) DEFAULT NULL,
  `changer_id` bigint(20) unsigned DEFAULT NULL,
  `status` enum('completed','pending','cancelled') NOT NULL DEFAULT 'completed',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `credit_histories_client_id_created_at_index` (`client_id`,`created_at`),
  KEY `credit_histories_type_status_index` (`type`,`status`),
  KEY `credit_histories_reference_id_reference_type_index` (`reference_id`,`reference_type`),
  KEY `credit_histories_changer_id_foreign` (`changer_id`),
  CONSTRAINT `credit_histories_client_id_foreign` FOREIGN KEY (`client_id`) REFERENCES `clients` (`id`) ON DELETE CASCADE,
  CONSTRAINT `credit_histories_changer_id_foreign` FOREIGN KEY (`changer_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
