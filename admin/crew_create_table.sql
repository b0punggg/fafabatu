CREATE TABLE IF NOT EXISTS `crew` (
  `no_urut` int(11) NOT NULL AUTO_INCREMENT,
  `kd_crew` varchar(50) NOT NULL,
  `nm_crew` varchar(255) NOT NULL,
  `nm_toko` varchar(255) DEFAULT NULL,
  `al_crew` varchar(255) DEFAULT '',
  `no_telp` varchar(50) DEFAULT '',
  `aktif` tinyint(1) NOT NULL DEFAULT 1,
  `kd_toko` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`no_urut`),
  KEY `idx_kd_crew` (`kd_crew`),
  KEY `idx_crew_toko` (`kd_toko`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `mas_jual` ADD COLUMN IF NOT EXISTS `kd_crew` varchar(50) DEFAULT '' AFTER `kd_member`;
