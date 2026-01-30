-- Database Schema for Arsip Data System
-- Extracted from instal.php

CREATE TABLE IF NOT EXISTS `legalisir` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_surat` varchar(50) NOT NULL,
  `tgl_dokumen` varchar(200) NOT NULL,
  `ditujukan` varchar(300) NOT NULL,
  `perihal` varchar(200) NOT NULL,
  `pembuat` varchar(25) NOT NULL,
  `pdf` varchar(200) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

INSERT INTO `legalisir` (`id`, `no_surat`, `tgl_dokumen`, `ditujukan`, `perihal`, `pembuat`, `pdf`) VALUES
(1, '673/PK01.02', '2025-09-15', 'MUHAMMAD RAFA ARRAYA', 'LEGALISIR IJAZAH', 'FAJRI YANTO', '16-12-2025-673-PK01.02_MUHAMMAD RAFA ARRAYA_IJASAH 3.pdf'),
(2, '681/PK01.02', '2025-09-17', 'FADHIL INDRA YUDHA WIBOWO', 'LEGALISIR IJAZAH', 'FAJRI YANTO', '16-12-2025-681-PK01.02_FADHIL INDRA YUDHA WIBOWO_IJASAH 2.pdf'),
(3, '663/PK01.02', '2025-09-11', 'INDIRA SALSABILA  DRISTA AULIA', 'LEGALISIR RAPORT', 'FAJRI YANTO', '16-12-2025-663-PK01.02_INDIRA SALSABILA  DRISTA AULIA_BIODATA RAPOR 3.pdf'),
(4, '684/PK.01.02', '2025-09-22', 'YHOLA ANDESTA', 'LEGALISIR IJAZAH DAN NILAI SIDANIRA ', 'EKA', '16-12-2025-684-PK.01.02_YHOLA ANDESTA_IJASAH 1.pdf'),
(5, '691/PK.01.01', '2025-09-23', 'SEPTI FIRANDA', 'LEGALISIR IJAZAH', 'YANTO', '16-12-2025-691-PK.01.01_SEPTI FIRANDA_IJASAH 4.pdf'),
(6, '705/PK.01.02', '2025-09-30', 'FADHIL INDRA YUDHA WIBOWO', 'LEGALISIR RAPORT', 'YANTO', '16-12-2025-705-PK.01.02_FADHIL INDRA YUDHA WIBOWO_BIODATA RAPOR 2.pdf'),
(7, '749/PK.01.02', '2025-11-03', 'VINCENTUS KRISNA PUTRA', 'LEGALISIR RAPORT', 'YANTO', '16-12-2025-749-PK.01.02_VINCENTUS KRISNA PUTRA_RAPOR 1.pdf');

CREATE TABLE IF NOT EXISTS `login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ip_address` varchar(45) NOT NULL,
  `attempts` int(11) NOT NULL DEFAULT '0',
  `last_attempt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user` varchar(100) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `userid` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ip_address` (`ip_address`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `prestasi` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pd` varchar(99) NOT NULL,
  `kelas` varchar(25) NOT NULL,
  `prestasisiswa` varchar(99) NOT NULL,
  `jenisprestasi` varchar(99) NOT NULL,
  `tgl_kegiatan` varchar(80) NOT NULL,
  `tingkat` varchar(99) NOT NULL,
  `lokasi` varchar(88) NOT NULL,
  `pdf` varchar(200) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

INSERT INTO `prestasi` (`id`, `pd`, `kelas`, `prestasisiswa`, `jenisprestasi`, `tgl_kegiatan`, `tingkat`, `lokasi`, `pdf`, `date`) VALUES
(1, 'MUHAMMAD RIFAN MAULANA', 'VIII-F', 'TENIS MEJA GANDA CAMPURAN', 'PEKAN OLAHRAGA PROVINSI (PORPOV) TINGKAT PROVINSI DKI JAKARTA', '2025-09-04', 'Provinsi', 'JAKARTA', '16-12-2025-MUHAMMAD RIFAN MAULANA_VIII-F_PIAGAM 1.pdf', '2025-12-16 12:54:02'),
(2, 'MUHAMMAD RIFAN MAULANA', 'VIII-F', 'TENIS MEJA TUNGGAL PUTRA', 'PEKAN OLAHRAGA PROVINSI (PORPOV) TINGKAT PROVINSI DKI JAKARTA', '2025-09-04', 'Provinsi', 'JAKARTA', '16-12-2025-MUHAMMAD RIFAN MAULANA_VIII-F_PIAGAM 2.pdf', '2025-12-16 12:56:28'),
(3, 'MUHAMMAD RIFAN MAULANA', 'VIII-F', 'TENIS MEJA GANDA CAMPURAN', 'TENIS MEJA BEREGU PUTRA', '2025-10-14', 'Provinsi', 'JAKARTA', '16-12-2025-MUHAMMAD RIFAN MAULANA_VIII-F_PIAGAM 3.pdf', '2025-12-16 12:57:12'),
(4, 'SISWA SMP 171', '8', 'LOMBA KOREOGRAFI PENCAK ', 'PENCAK MALIOBORO FESTIVAL 8 PENCAL SILAT DIY', '2025-10-27', 'Provinsi', 'DINAS KEBUDAYAAN DIY', '16-12-2025-SISWA SMP 171_8_piagam 1.pdf', '2025-12-16 13:02:16'),
(6, 'NCHOLAS ADRIAN MEHAN GINTING', '7', 'KOMPETISI MATEMATIKA SMP ', 'KOMPETISI MATEMATIKA SMP ', '2025-11-22', 'Internasional', 'MGMP MTK ', '16-12-2025-NCHOLAS ADRIAN MEHAN GINTING_7_piagam 4.pdf', '2025-12-16 13:04:34'),
(7, 'AHMAD ZULFIKAR FADHLY', '9I', 'PRAMUKA GARUDA PENGGALANG', 'PRAMUKA GARUDA PENGGALANG', '2025-11-17', 'Kabupaten/Kota', 'KWARTR JAKARTA TIMUR', '16-12-2025-AHMAD ZULFIKAR FADHLY_9I_piagam 2.pdf', '2025-12-16 13:05:09');

CREATE TABLE IF NOT EXISTS `usulan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `no_surat` varchar(100) NOT NULL,
  `judul` varchar(100) NOT NULL,
  `tujuan` varchar(100) NOT NULL,
  `tgl_dokumen` date DEFAULT NULL,
  `pdf` varchar(200) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

INSERT INTO `usulan` (`id`, `no_surat`, `judul`, `tujuan`, `tgl_dokumen`, `pdf`, `date`) VALUES
(1, '641/PK.01.02', 'SURAT PERMOHONAN INPUT LAPORAN PRESTASI BULAN JULI - SEPTEMBER 2025', 'SATUAN PELAKSANA PENDIDIKAN KECAMATAN', '2025-09-02', '20251216_641_PK_01_02_JULI_-_SEPTEMBER_2025.pdf', '2025-12-16 13:20:51'),
(2, '214/PK.01.02', 'SURAT PERMOHONAN INPUT LAPORAN PRESTASI BULAN APRIL- JUNI 2025', 'SATUAN PELAKSANA PENDIDIKAN KECAMATAN', '2025-06-19', '20251216_214_PK_01_02_APRIL-_JUNI_2025.pdf', '2025-12-16 13:19:53'),
(3, '99/PK.01.02', 'SURAT PERMOHONAN INPUT LAPORAN PRESTASI BULAN JANUARI - MARET 2025', 'SATUAN PELAKSANA PENDIDIKAN KECAMATAN', '2025-03-21', '20251216_99_PK_01_02_JANUARI_-_MARET_2025.pdf', '2025-12-16 13:18:03');

DROP TABLE IF EXISTS `profils`;
CREATE TABLE IF NOT EXISTS `profils` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nsekolah` varchar(255) DEFAULT NULL,
  `njalan` varchar(255) DEFAULT NULL,
  `nkec` varchar(100) DEFAULT NULL,
  `nkel` varchar(100) DEFAULT NULL,
  `nprovinsi` varchar(100) DEFAULT NULL,
  `nkab` varchar(100) DEFAULT NULL,
  `pos` varchar(10) DEFAULT NULL,
  `tlp` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `web` varchar(100) DEFAULT NULL,
  `kasudin` varchar(100) DEFAULT NULL,
  `nipkasudin` varchar(50) DEFAULT NULL,
  `nrkkasudin` varchar(50) DEFAULT NULL,
  `kepsek` varchar(100) DEFAULT NULL,
  `nipkepsek` varchar(50) DEFAULT NULL,
  `nrkkepsek` varchar(50) DEFAULT NULL,
  `pengawas` varchar(100) DEFAULT NULL,
  `nippengawas` varchar(50) DEFAULT NULL,
  `nrkpengawas` varchar(50) DEFAULT NULL,
  `kasi` varchar(100) DEFAULT NULL,
  `nipkasi` varchar(50) DEFAULT NULL,
  `nrkkasi` varchar(50) DEFAULT NULL,
  `ktu` varchar(100) DEFAULT NULL,
  `nipktu` varchar(50) DEFAULT NULL,
  `nrkktu` varchar(50) DEFAULT NULL,
  `logo_pemda` varchar(255) DEFAULT NULL,
  `logo_sekolah` varchar(255) DEFAULT NULL,
  `background_login` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


CREATE TABLE IF NOT EXISTS `runtxt` (
  `id` int NOT NULL,
  `txt` varchar(250) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `server` (
  `id` int NOT NULL,
  `weboff` varchar(2) NOT NULL,
  `log_server` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `siswa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pd` varchar(100) NOT NULL,
  `nis` varchar(20) NOT NULL,
  `kelas` varchar(50) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;


CREATE TABLE IF NOT EXISTS `tapel` (
  `id` int NOT NULL,
  `tapel` varchar(20) NOT NULL,
  `aktif` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `usera` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `nik` varchar(50) DEFAULT NULL,
  `poto` varchar(255) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT '1',
  `ip` varchar(45) NOT NULL,
  `google_secret` VARCHAR(32) NULL,
  `lastlogin` datetime DEFAULT NULL,
  `level` varchar(20) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `usera_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user` varchar(50) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `waktu` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `ip` varchar(45) NOT NULL,
  `info` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS `dbset` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dbname` varchar(100) NOT NULL,
  `tahun` varchar(20) NOT NULL,
  `aktif` varchar(20) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;

create table if not exists `version` (
  `id` int NOT NULL AUTO_INCREMENT,
  `version` varchar(20) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB;
