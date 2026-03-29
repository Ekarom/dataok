<?php
/**
 * Routing Configuration for Sistem Arsip Data (S.A.D)
 * Centralizes route mapping, titles, and inclusion logic.
 */

$route_map = [
    // Dashboard / Defaults
    'home'       => ['file' => 'load.php', 'title' => 'Dashboard'],
    'dashboard'  => ['file' => 'load.php', 'title' => 'Dashboard'],
    'arsipdata'  => ['file' => 'load.php', 'title' => 'Arsip Data'],
    'print'      => ['file' => 'load.php', 'title' => 'Print'],
    'management' => ['file' => 'load.php', 'title' => 'Management'],
    'system'     => ['file' => 'load.php', 'title' => 'System'],
    'sistem'     => ['file' => 'load.php', 'title' => 'System'],

    // Data Siswa
    'datasiswa'  => ['file' => 'siswa.php', 'title' => 'Data Siswa'],

    // Input Prestasi
    'arsipdata/inputprestasi' => ['file' => 'inputprestasi_logic', 'title' => 'Input Prestasi'],
    'inputprestasi'           => ['file' => 'inputprestasi_logic', 'title' => 'Input Prestasi'],
    'dataprestasi'            => ['file' => 'inputprestasi_logic', 'title' => 'Input Prestasi'],
    'input'                   => ['file' => 'inputprestasi_logic', 'title' => 'Input Prestasi'],

    // Input Legalisir
    'arsipdata/inputlegalisir' => ['file' => 'legalisir_logic', 'title' => 'Input Legalisir'],
    'inputlegalisir'           => ['file' => 'legalisir_logic', 'title' => 'Input Legalisir'],
    'legalisir'                => ['file' => 'laporanlegalisir.php', 'title' => 'Legalisir'],

    // Laporan
    'print/laporanprestasi' => ['file' => 'laporanpress.php', 'title' => 'Laporan Prestasi'],
    'laporanprestasi'       => ['file' => 'laporanpress.php', 'title' => 'Laporan Prestasi'],
    'laporan'               => ['file' => 'laporanpress.php', 'title' => 'Laporan'],

    'print/laporanlegalisir' => ['file' => 'laporanlegalisir.php', 'title' => 'Laporan Legalisir'],
    'laporanlegalisir'       => ['file' => 'laporanlegalisir.php', 'title' => 'Laporan Legalisir'],

    // User Management
    'management/usermanagement' => ['file' => 'user.php', 'title' => 'User Staff & Admin'],
    'usermanagement'            => ['file' => 'user.php', 'title' => 'User Staff & Admin'],
    'user'                      => ['file' => 'user.php', 'title' => 'User'],
    'edit_usera'                => ['file' => 'edit_usera.php', 'title' => 'Edit Data User'],
    'tambah_usera'              => ['file' => 'tambah_usera.php', 'title' => 'Tambah User Baru'],

    // Data Sekolah
    'management/datasekolah' => ['file' => 'datasek.php', 'title' => 'Data Sekolah'],
    'datasekolah'            => ['file' => 'datasek.php', 'title' => 'Data Sekolah'],
    'datasek'                => ['file' => 'datasek.php', 'title' => 'Data Sekolah'],

    // Settings
    'management/settings' => ['file' => 'setting.php', 'title' => 'Settings'],
    'pengaturan'          => ['file' => 'setting.php', 'title' => 'Settings'],
    'settings'            => ['file' => 'setting.php', 'title' => 'Settings'],

    // Profile
    'management/profil' => ['file' => 'profil.php', 'title' => 'Profile'],
    'profil'            => ['file' => 'profil.php', 'title' => 'Profile'],

    // Uploads
    'management/uploadsiswa' => ['file' => 'upload_siswa.php', 'title' => 'Upload Excel Siswa'],
    'uploadsiswa'            => ['file' => 'upload_siswa.php', 'title' => 'Upload Excel Siswa'],
    'management/uploaduser'  => ['file' => 'upload_usera.php', 'title' => 'Upload Data User'],
    'uploaduser'             => ['file' => 'upload_usera.php', 'title' => 'Upload Data User'],
    'management/uploadfoto'  => ['file' => 'upload_foto.php', 'title' => 'Upload Foto (ZIP)'],
    'uploadfoto'             => ['file' => 'upload_foto.php', 'title' => 'Upload Foto (ZIP)'],

    // System
    'system/database' => ['file' => 'brd.php', 'title' => 'Database'],
    'sistem/database' => ['file' => 'brd.php', 'title' => 'Database'],
    'database'        => ['file' => 'brd.php', 'title' => 'Database'],
    'brd'             => ['file' => 'brd.php', 'title' => 'Database'],

    'system/checkupdate' => ['file' => 'chckupdate.php', 'title' => 'Check Update'],
    'sistem/checkupdate' => ['file' => 'chckupdate.php', 'title' => 'Check Update'],
    'checkupdate'        => ['file' => 'chckupdate.php', 'title' => 'Check Update'],

    'system/activitylog' => ['file' => 'activity_log.php', 'title' => 'Activity Log'],
    'sistem/activitylog' => ['file' => 'activity_log.php', 'title' => 'Activity Log'],
    'activitylog'        => ['file' => 'activity_log.php', 'title' => 'Activity Log'],
    'activity'           => ['file' => 'activity_log.php', 'title' => 'Activity Log'],

    // Others
    'press'          => ['file' => 'prosespress.php', 'title' => 'Proses Prestasi'],
    'viewpress'      => ['file' => 'view_pres.php', 'title' => 'Detail Prestasi'],
    'editpress'      => ['file' => 'edit_press.php', 'title' => 'Edit Prestasi'],
    'editlegalisir'  => ['file' => 'edit_legalisir.php', 'title' => 'Edit Legalisir'],
    'viewlegalisir'  => ['file' => 'view_legalisir.php', 'title' => 'Detail Legalisir'],
    'dh'             => ['file' => 'daftar_hadir.php', 'title' => 'Daftar Hadir'],
    'logout'         => ['file' => 'index.php', 'title' => 'Logout']
];

/**
 * Get include file based on route
 */
function get_route_include($route, $route_map) {
    if ($route === 'modul') {
        $mod = strtolower($_GET['modul'] ?? '');
        $mod_map = [
            'uploaduser'    => 'upload_usera.php',
            'uploadfoto'    => 'upload_foto.php',
            'profile'       => 'profil.php',
            'profil'        => 'profil.php',
            'settings'      => 'setting.php',
            'setting'       => 'setting.php',
            'pengaturan'    => 'setting.php',
            'activitylog'   => 'activity_log.php',
            'log-aktivitas' => 'activity_log.php'
        ];
        return $mod_map[$mod] ?? 'load.php';
    }

    if (!isset($route_map[$route])) {
        return 'load.php';
    }

    $file = $route_map[$route]['file'];

    // Special Logic Cases
    if ($file === 'inputprestasi_logic') {
        return isset($_GET['nis']) ? 'inputprestasi.php' : 'dataprestasi.php';
    }

    if ($file === 'legalisir_logic') {
        $_GET['aksi'] = 'tambah';
        return 'laporanlegalisir.php';
    }

    return $file;
}

/**
 * Get page title based on route
 */
function get_route_title($route, $route_map) {
    if ($route === 'modul') {
        $mod = strtolower($_GET['modul'] ?? '');
        $titles = [
            'profile' => 'Profile', 'profil' => 'Profile',
            'settings' => 'Settings', 'setting' => 'Settings', 'pengaturan' => 'Settings',
            'activitylog' => 'Activity Log', 'log-aktivitas' => 'Activity Log'
        ];
        return $titles[$mod] ?? 'Dashboard';
    }
    return $route_map[$route]['title'] ?? 'Dashboard';
}
