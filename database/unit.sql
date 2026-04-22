CREATE TABLE unit (
  `id_unit` bigint(20) UNSIGNED NOT NULL,
  `nm_unit` varchar(255) NOT NULL,
  lokasi varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (id_unit)
) ENGINE=InnoDB ;
