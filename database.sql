CREATE DATABASE IF NOT EXISTS `fish_habit_tracker`
  DEFAULT CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `fish_habit_tracker`;

CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `username`   VARCHAR(50)  NOT NULL UNIQUE,
  `password`   VARCHAR(255) NOT NULL,
  `nama_ikan`  VARCHAR(60)  NOT NULL DEFAULT 'Ikan Cupang Alpha',
  `jenis_ikan` VARCHAR(60)  NOT NULL DEFAULT 'Betta splendens',
  `created_at` TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `habits` (
  `id`            INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`       INT NOT NULL,
  `nama_habit`    VARCHAR(100) NOT NULL,
  `kategori`      VARCHAR(50)  NOT NULL DEFAULT 'PAKAN',
  `waktu`         VARCHAR(40)  NOT NULL DEFAULT 'Rutin Harian',
  `emblem`        VARCHAR(10)  NOT NULL DEFAULT '🐟',
  `target_harian` INT NOT NULL DEFAULT 1,
  `created_at`    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_habits_user` (`user_id`),
  CONSTRAINT `fk_habits_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `habit_logs` (
  `id`           INT AUTO_INCREMENT PRIMARY KEY,
  `habit_id`     INT NOT NULL,
  `tanggal`      DATE NOT NULL,
  `status`       TINYINT(1) NOT NULL DEFAULT 1,
  `completed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_habit_date` (`habit_id`, `tanggal`),
  CONSTRAINT `fk_logs_habit`
    FOREIGN KEY (`habit_id`) REFERENCES `habits`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `fishes` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT NOT NULL,
  `nama`       VARCHAR(60)  NOT NULL,
  `jenis`      VARCHAR(60)  NOT NULL,
  `ukuran_cm`  DECIMAL(5,1) NOT NULL DEFAULT 0,
  `umur_bulan` INT          NOT NULL DEFAULT 0,
  `kondisi`    VARCHAR(20)  NOT NULL DEFAULT 'SEHAT',
  `catatan`    VARCHAR(300) DEFAULT NULL,
  `emblem`     VARCHAR(10)  NOT NULL DEFAULT '🐟',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_fishes_user` (`user_id`),
  CONSTRAINT `fk_fishes_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `water_params` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT NOT NULL,
  `tanggal`    DATE NOT NULL,
  `ph`         DECIMAL(3,1) NOT NULL DEFAULT 7.0,
  `suhu_c`     DECIMAL(4,1) NOT NULL DEFAULT 26.0,
  `ammonia`    DECIMAL(4,2) NOT NULL DEFAULT 0.00,
  `nitrit`     DECIMAL(4,2) NOT NULL DEFAULT 0.00,
  `nitrat`     DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `catatan`    VARCHAR(300) DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_date` (`user_id`, `tanggal`),
  CONSTRAINT `fk_water_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `feedings` (
  `id`          INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`     INT NOT NULL,
  `fish_id`     INT DEFAULT NULL,
  `tanggal`     DATE NOT NULL,
  `waktu`       TIME NOT NULL,
  `jenis_pakan` VARCHAR(60) NOT NULL,
  `jumlah`      DECIMAL(6,2) NOT NULL DEFAULT 1.00,
  `satuan`      VARCHAR(20) NOT NULL DEFAULT 'butir',
  `habis`       TINYINT(1) NOT NULL DEFAULT 1,
  `catatan`     VARCHAR(300) DEFAULT NULL,
  `created_at`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  KEY `idx_feedings_user` (`user_id`, `tanggal`),
  KEY `idx_feedings_fish` (`fish_id`),
  CONSTRAINT `fk_feeding_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_feeding_fish`
    FOREIGN KEY (`fish_id`) REFERENCES `fishes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `pakan_jenis` (
  `id`         INT AUTO_INCREMENT PRIMARY KEY,
  `user_id`    INT NOT NULL,
  `nama`       VARCHAR(60) NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_user_jenis` (`user_id`, `nama`),
  CONSTRAINT `fk_pakan_jenis_user`
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


INSERT INTO `users` (`id`, `username`, `password`, `nama_ikan`, `jenis_ikan`)
VALUES (
  1,
  'admin',
  '$2y$10$MQg/GDrowU8umUqyyBe6AeqUFH5qG1fx4gG6Gjm2bYSHg4uVNZV9C',
  'Alpha Striker',
  'Channa Marulioides'
)
ON DUPLICATE KEY UPDATE
  `password`   = VALUES(`password`),
  `nama_ikan`  = VALUES(`nama_ikan`),
  `jenis_ikan` = VALUES(`jenis_ikan`);

INSERT INTO `habits` (`id`, `user_id`, `nama_habit`, `kategori`, `waktu`, `emblem`, `target_harian`) VALUES
(1, 1, 'Beri pakan pelet berprotein tinggi', 'PAKAN', '07:30 WIB', '🐟', 1),
(2, 1, 'Cek suhu air (25°C - 28°C) & heater', 'SUHU & LAMPU', '09:00 WIB', '🌡️', 1),
(3, 1, 'Siphon kotoran dasar & cuci busa filter', 'SANITASI', '14:00 WIB', '🧽', 1),
(4, 1, 'Inspeksi sirip, sisik & keaktifan renang', 'KESEHATAN', '16:00 WIB', '🐠', 1),
(5, 1, 'Pakan cacing sutra / artemia sore', 'PAKAN', '17:30 WIB', '🐡', 1),
(6, 1, 'Water change 20% air endapan anti-klorin', 'KUALITAS AIR', 'Setiap 2 Hari', '💧', 1)
ON DUPLICATE KEY UPDATE `nama_habit` = VALUES(`nama_habit`);

INSERT INTO `fishes` (`id`, `user_id`, `nama`, `jenis`, `ukuran_cm`, `umur_bulan`, `kondisi`, `catatan`, `emblem`) VALUES
(1, 1, 'Alpha Striker', 'Channa Marulioides', 32.5, 14, 'SEHAT', 'Spesimen utama, agresif saat makan.', '🐟'),
(2, 1, 'Beta Nebula', 'Betta splendens', 6.0, 8, 'SEHAT', 'Sirip lebar, perlu air tenang.', '🐠'),
(3, 1, 'Gamma Abyss', 'Paracheirodon innesi', 3.2, 5, 'PERHATIAN', 'Nafsu makan menurun dua hari terakhir.', '🐡')
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`);

INSERT INTO `habit_logs` (`habit_id`, `tanggal`, `status`) VALUES
(1, CURDATE(), 1),
(2, CURDATE(), 1),
(3, CURDATE(), 1),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1),
(2, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1),
(4, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1),
(5, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1),
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1),
(3, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1),
(6, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 1)
ON DUPLICATE KEY UPDATE `status` = VALUES(`status`);

INSERT INTO `water_params` (`user_id`, `tanggal`, `ph`, `suhu_c`, `ammonia`, `nitrit`, `nitrat`, `catatan`) VALUES
(1, CURDATE(), 7.2, 26.5, 0.00, 0.00, 10.00, 'Air jernih, ikan aktif'),
(1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 7.1, 26.0, 0.02, 0.00, 15.00, 'Setelah water change 20%'),
(1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 7.0, 25.5, 0.05, 0.10, 20.00, 'Amonia sedikit naik'),
(1, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 6.9, 25.0, 0.10, 0.25, 25.00, 'PERHATIAN: ammonia tinggi'),
(1, DATE_SUB(CURDATE(), INTERVAL 4 DAY), 7.3, 26.0, 0.00, 0.00, 5.00, 'Kondisi ideal'),
(1, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 7.2, 26.5, 0.02, 0.00, 10.00, 'Normal'),
(1, DATE_SUB(CURDATE(), INTERVAL 6 DAY), 7.1, 27.0, 0.00, 0.05, 8.00, 'Normal')
ON DUPLICATE KEY UPDATE
  `ph`      = VALUES(`ph`),
  `suhu_c`  = VALUES(`suhu_c`),
  `ammonia` = VALUES(`ammonia`),
  `nitrit`  = VALUES(`nitrit`),
  `nitrat`  = VALUES(`nitrat`),
  `catatan` = VALUES(`catatan`);

INSERT INTO `feedings` (`user_id`, `fish_id`, `tanggal`, `waktu`, `jenis_pakan`, `jumlah`, `satuan`, `habis`, `catatan`)
SELECT 1, 1, CURDATE(), '07:30:00', 'Pelet protein tinggi', 15.00, 'butir', 1, 'Habis dalam 2 menit'
WHERE NOT EXISTS (
  SELECT 1 FROM `feedings` WHERE `user_id` = 1 AND `tanggal` = CURDATE()
    AND `waktu` = '07:30:00' AND `jenis_pakan` = 'Pelet protein tinggi'
);

INSERT INTO `feedings` (`user_id`, `fish_id`, `tanggal`, `waktu`, `jenis_pakan`, `jumlah`, `satuan`, `habis`, `catatan`)
SELECT 1, 1, CURDATE(), '17:30:00', 'Cacing sutra', 10.00, 'butir', 0, 'Sisa 2 butir'
WHERE NOT EXISTS (
  SELECT 1 FROM `feedings` WHERE `user_id` = 1 AND `tanggal` = CURDATE()
    AND `waktu` = '17:30:00' AND `jenis_pakan` = 'Cacing sutra'
);

INSERT INTO `feedings` (`user_id`, `fish_id`, `tanggal`, `waktu`, `jenis_pakan`, `jumlah`, `satuan`, `habis`, `catatan`)
SELECT 1, 2, CURDATE(), '08:00:00', 'Pelet kecil', 8.00, 'butir', 1, NULL
WHERE NOT EXISTS (
  SELECT 1 FROM `feedings` WHERE `user_id` = 1 AND `tanggal` = CURDATE()
    AND `waktu` = '08:00:00' AND `jenis_pakan` = 'Pelet kecil'
);

INSERT INTO `feedings` (`user_id`, `fish_id`, `tanggal`, `waktu`, `jenis_pakan`, `jumlah`, `satuan`, `habis`, `catatan`)
SELECT 1, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '07:30:00', 'Pelet protein tinggi', 12.00, 'butir', 1, NULL
WHERE NOT EXISTS (
  SELECT 1 FROM `feedings` WHERE `user_id` = 1 AND `tanggal` = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    AND `waktu` = '07:30:00' AND `jenis_pakan` = 'Pelet protein tinggi'
);

INSERT INTO `feedings` (`user_id`, `fish_id`, `tanggal`, `waktu`, `jenis_pakan`, `jumlah`, `satuan`, `habis`, `catatan`)
SELECT 1, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '17:30:00', 'Artemia', 20.00, 'butir', 1, 'Nafsu makan bagus'
WHERE NOT EXISTS (
  SELECT 1 FROM `feedings` WHERE `user_id` = 1 AND `tanggal` = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    AND `waktu` = '17:30:00' AND `jenis_pakan` = 'Artemia'
);

INSERT INTO `feedings` (`user_id`, `fish_id`, `tanggal`, `waktu`, `jenis_pakan`, `jumlah`, `satuan`, `habis`, `catatan`)
SELECT 1, 3, DATE_SUB(CURDATE(), INTERVAL 1 DAY), '08:00:00', 'Pelet kecil', 5.00, 'butir', 0, 'Nafsu makan turun'
WHERE NOT EXISTS (
  SELECT 1 FROM `feedings` WHERE `user_id` = 1 AND `tanggal` = DATE_SUB(CURDATE(), INTERVAL 1 DAY)
    AND `waktu` = '08:00:00' AND `jenis_pakan` = 'Pelet kecil'
);

INSERT INTO `feedings` (`user_id`, `fish_id`, `tanggal`, `waktu`, `jenis_pakan`, `jumlah`, `satuan`, `habis`, `catatan`)
SELECT 1, 1, DATE_SUB(CURDATE(), INTERVAL 2 DAY), '07:30:00', 'Pelet protein tinggi', 15.00, 'butir', 1, NULL
WHERE NOT EXISTS (
  SELECT 1 FROM `feedings` WHERE `user_id` = 1 AND `tanggal` = DATE_SUB(CURDATE(), INTERVAL 2 DAY)
    AND `waktu` = '07:30:00' AND `jenis_pakan` = 'Pelet protein tinggi'
);
