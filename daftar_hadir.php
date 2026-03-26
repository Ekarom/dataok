<?php
include "cfg/konek.php";

// ─── PARAMETER FILTER ───────────────────────────────────────────────────────
$filter_kelas  = isset($_GET['kelas'])    ? trim($_GET['kelas'])    : '';
$filter_search = isset($_GET['search'])   ? trim($_GET['search'])   : '';
$judul_hadir   = isset($_GET['judul'])    ? trim($_GET['judul'])    : 'Tanda Terima Kartu Pelajar RFID';
$tanggal_hadir = isset($_GET['tanggal']) ? trim($_GET['tanggal'])  : date('Y-m-d');


// ─── QUERY DATA SISWA ────────────────────────────────────────────────────────
$where  = [];
$kparams = [];
if (!empty($filter_kelas)) {
    $where[]  = "kelas = '" . $sqlconn->real_escape_string($filter_kelas) . "'";
}
if (!empty($filter_search)) {
    $kw = $sqlconn->real_escape_string($filter_search);
    $where[] = "(pd LIKE '%$kw%' OR nis LIKE '%$kw%')";
}
$sql_where = !empty($where) ? " WHERE " . implode(" AND ", $where) : "";
$sql = "SELECT pd, nis, kelas FROM siswa $sql_where ORDER BY kelas ASC, pd ASC";
$result = @mysqli_query($sqlconn, $sql);
$siswa_list = [];
if ($result) {
    while ($r = mysqli_fetch_assoc($result)) {
        $r['pd'] = str_replace(['@','#','*'], '', $r['pd']);
        $siswa_list[] = $r;
    }
}
$total = count($siswa_list);

// Kelompokkan siswa berdasarkan kelas
$siswa_per_kelas = [];
foreach ($siswa_list as $s) {
    if (!isset($siswa_per_kelas[$s['kelas']])) {
        $siswa_per_kelas[$s['kelas']] = [];
    }
    $siswa_per_kelas[$s['kelas']][] = $s;
}
ksort($siswa_per_kelas);

// Format tanggal untuk tampilan
$tgl_display = date('d F Y', strtotime($tanggal_hadir));
$months_id = [
    'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
    'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
    'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
    'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
];
foreach($months_id as $en => $id) {
    $tgl_display = str_replace($en, $id, $tgl_display);
}
?>
<script>
    window.onload = function() {
        window.print();
    };
</script>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Hadir Siswa</title>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap">
<style>
/* ─── DESIGN SYSTEM ─── */
:root {
    --primary:    #2563eb;
    --primary-dk: #1e40af;
    --secondary:  #0f172a;
    --accent:     #f59e0b;
    --bg:         #f1f5f9;
    --surface:    #ffffff;
    --border:     #e2e8f0;
    --text:       #1e293b;
    --muted:      #64748b;
    --success:    #059669;
    --radius:     12px;
    --shadow:     0 4px 24px rgba(0,0,0,.08);
}

*, *::before, *::after { box-sizing: border-box; }

body {
    font-family: 'Inter', sans-serif;
    background: var(--bg);
    color: var(--text);
    margin: 0;
    padding: 0;
}


/* ─── CONTAINER ─── */
.page-wrap {
    max-width: 1100px;
    margin: 28px auto;
    padding: 0 16px;
}

/* ─── KERTAS CETAK ─── */
.paper {
    background: var(--surface);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    padding: 36px 40px;
    position: relative;
    margin-bottom: 30px;
}

/* ─── HEADER KOP SURAT ─── */
.kop {
    display: flex;
    align-items: center;
    gap: 20px;
    padding-bottom: 16px;
    border-bottom: 3px double #000;
    margin-bottom: 10px;
}
.kop img {
    width: 80px;
    height: 80px;
    object-fit: contain;
}
.kop-text { flex: 1; }
.kop-text .sekolah-name {
    font-size: 20px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin: 0 0 2px 0;
    line-height: 1.2;
}
.kop-text .sekolah-alamat {
    font-size: 12px;
    color: var(--muted);
    margin: 0;
    line-height: 1.5;
}

/* ─── JUDUL DAFTAR ─── */
.doc-title {
    text-align: center;
    margin: 20px 0 6px;
}
.doc-title h2 {
    font-size: 17px;
    font-weight: 800;
    text-transform: uppercase;
    text-decoration: underline;
    margin: 0;
}
.doc-meta {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: var(--muted);
    margin-bottom: 16px;
    padding: 0 2px;
}
.badge-kelas {
    display: inline-block;
    background: var(--primary);
    color: #fff;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 600;
}

/* ─── TABEL ─── */
.tbl-hadir {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}
.tbl-hadir thead tr {
    background: var(--secondary);
    color: #fff;
}
.tbl-hadir thead th {
    padding: 11px 12px;
    text-align: center;
    font-weight: 600;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: .4px;
    border: 1px solid #000;
}
.tbl-hadir tbody tr:nth-child(even) { background: #f8fafc; }
.tbl-hadir tbody tr:hover { background: #eff6ff; }
.tbl-hadir tbody td {
    padding: 9px 12px;
    border: 1px solid #cbd5e1;
    vertical-align: middle;
}
.tbl-hadir .td-no    { text-align: center; width: 48px; font-weight: 600; color: var(--muted); }
.tbl-hadir .td-nis   { text-align: center; width: 120px; font-family: monospace; font-size: 12.5px; }
.tbl-hadir .td-kelas { text-align: center; width: 80px; }
.tbl-hadir .td-ttd   { width: 160px; text-align: center; }
.tbl-hadir .ttd-box  {
    height: 48px;
    border-bottom: 1.5px solid #334155;
    margin: 0 8px;
}

/* ─── FOOTER TANDA TANGAN KS ─── */
.doc-footer {
    margin-top: 40px;
    display: flex;
    justify-content: flex-end;
}
.ttd-ks {
    text-align: center;
    font-size: 13px;
    min-width: 250px;
}
.ttd-ks p { margin: 0; }
.ttd-ks .kota-tgl { margin-bottom: 5px; }
.ttd-ks .ks-name  { 
    margin-top: 60px;
    font-weight: 700; 
    text-decoration: underline; 
    font-size: 14px; 
    text-transform: uppercase;
}
.ttd-ks .ks-nip   { margin-top: 0; }


/* ─── EMPTY STATE ─── */
.empty {
    text-align: center;
    padding: 60px 20px;
    color: var(--muted);
}
.empty svg { opacity: .25; margin-bottom: 12px; }
.empty p { font-size: 14px; margin: 0; }

/* ─── MEDIA PRINT ─── */
@media print {
    body { background: #fff !important; }
    .page-wrap { margin: 0; padding: 0; max-width: 100%; }
    .paper { box-shadow: none; border-radius: 0; padding: 20px 24px; margin-bottom: 0; page-break-after: always; }
    .paper:last-child { page-break-after: auto; }
    .tbl-hadir thead tr { background: #1e293b !important; -webkit-print-color-adjust: exact; }
    .tbl-hadir tbody tr:nth-child(even) { background: #f8fafc !important; -webkit-print-color-adjust: exact; }
    .tbl-hadir tbody tr:hover { background: transparent !important; }
}
</style>
</head>
<body>



<div class="page-wrap">

    <?php if ($total > 0): ?>
        <?php foreach ($siswa_per_kelas as $kls => $siwas): ?>
        <!-- Paper / Kertas Cetak per Kelas -->
        <div class="paper">

            <!-- KOP SURAT -->
            <div class="kop">
                <img src="images/<?= $sklogo ?? 'logo_default.png' ?>" alt="Logo Sekolah" onerror="this.src='images/logo_default.png'">
                <div class="kop-text">
                    <p class="sekolah-name"><?= htmlspecialchars($namasek) ?></p>
                    <p class="sekolah-alamat">
                        <?= htmlspecialchars($alamatsek) ?><?= $alamatsek ? ', ' : '' ?><?= htmlspecialchars($kecsek) ?><?= $kecsek ? ', ' : '' ?><?= htmlspecialchars($kabsek) ?><br>
                        <?php if ($tlpsek): ?>Telp: <?= htmlspecialchars($tlpsek) ?> &nbsp;|&nbsp; <?php endif; ?>
                        <?php if ($emailsek): ?>Email: <?= htmlspecialchars($emailsek) ?> &nbsp;|&nbsp; <?php endif; ?>
                        <?php if ($website): ?>Website: <?= htmlspecialchars($website) ?><?php endif; ?>
                        <?php if ($npsn): ?><br>NPSN: <?= htmlspecialchars($npsn) ?><?php endif; ?>
                    </p>
                </div>
            </div>

            <!-- JUDUL -->
            <div class="doc-title">
                <h2><?= htmlspecialchars($judul_hadir) ?></h2>
            </div>
            <div class="doc-meta">
                <span>
                    Kelas: 
                    <span class="float-right" style="margin-left: 10px;"><?= htmlspecialchars($kls) ?></span>
                </span>
                <span class="float-right">Jumlah Siswa: <strong><?= count($siwas) ?> orang</strong></span>
            </div>

            <!-- TABEL DAFTAR HADIR -->
            <table class="tbl-hadir" style="margin-top: 10px;">
                <thead>
                    <tr>
                        <th style="width: 40px;">No</th>
                        <th>Nama Siswa</th>
                        <th style="width: 120px;">NIS</th>
                        <th style="width: 100px;">Kelas</th>
                        <th style="width: 200px;">Tanda Tangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($siwas as $idx => $s): ?>
                    <tr>
                        <td style="text-align: center;"><?= $idx + 1 ?></td>
                        <td><?= htmlspecialchars($s['pd']) ?></td>
                        <td style="text-align: center;"><?= htmlspecialchars($s['nis']) ?></td>
                        <td style="text-align: center;"><?= htmlspecialchars($s['kelas']) ?></td>
                        <td><div class="ttd-box"></div></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <!-- TANDA TANGAN KEPALA SEKOLAH -->
            <div class="doc-footer">
                <div class="ttd-ks">
                    <p class="kota-tgl"><?= htmlspecialchars($provsek ?: 'Jakarta') ?>, <?= $tgl_display ?></p>
                    <p>Kepala Sekolah,</p>
                    <p class="ks-name"><?= htmlspecialchars($kepsek) ?: '.................................' ?></p>
                    <p class="ks-nip">NIP. <?= htmlspecialchars($nipkepsek) ?: '-' ?></p>
                </div>
            </div>

        </div><!-- /paper -->
        <?php endforeach; ?>
    <?php else: ?>
        <div class="paper">
            <div class="empty">
                <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p>Tidak ada data siswa yang ditemukan.<br>Silakan sesuaikan filter di atas.</p>
            </div>
        </div>
    <?php endif; ?>

</div><!-- /page-wrap -->

</body>
</html>
