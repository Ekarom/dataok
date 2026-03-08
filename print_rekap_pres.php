<?php
/**
 * Print Rekap Prestasi Siswa
 * Generates a printable report of student achievements for a specified period
 * Updated: 2026-02-05 - Final Refinement
 */

// Error reporting for debugging
error_reporting(E_ALL & ~E_NOTICE);

// Dependency check - look in common locations
$konek_path = "cfg/konek.php";
if (!file_exists($konek_path)) {
    $konek_path = "konek.php"; 
}
include $konek_path;

// ============================================================================
// GET PARAMETERS & VALIDATION
// ============================================================================
$m1 = isset($_GET['m1']) ? mysqli_real_escape_string($sqlconn, $_GET['m1']) : 'Januari';
$m2 = isset($_GET['m2']) ? mysqli_real_escape_string($sqlconn, $_GET['m2']) : 'Desember';
$y  = isset($_GET['y']) ? mysqli_real_escape_string($sqlconn, $_GET['y']) : date('Y');
$db = isset($_GET['db']) ? mysqli_real_escape_string($sqlconn, $_GET['db']) : '';

// Switch database if specified
if (!empty($db)) {
    if (!mysqli_select_db($sqlconn, $db)) {
        die("Error: Database '{$db}' tidak ditemukan.");
    }
}

// ============================================================================
// FETCH SCHOOL IDENTITY
// ============================================================================
$query_identitas = mysqli_query($sqlconn, "SELECT * FROM profils LIMIT 1");
$identitas = mysqli_fetch_assoc($query_identitas);

$ks        = $identitas['kop_dinas'] ?? '';
$kepsek    = $identitas['kepsek'] ?? '';
$nipkepsek = $identitas['nipkepsek'] ?? '';

// ============================================================================
// DATE & MONTH LOCALIZATION
// ============================================================================
$indo_months = [
    'January'   => 'Januari', 'February' => 'Februari', 'March'     => 'Maret',
    'April'     => 'April',   'May'      => 'Mei',      'June'      => 'Juni',
    'July'      => 'Juli',    'August'   => 'Agustus',  'September' => 'September',
    'October'   => 'Oktober', 'November' => 'November', 'December'  => 'Desember'
];

$current_date_in = date('d') . ' ' . ($indo_months[date('F')] ?? date('F')) . ' ' . date('Y');

$month_map = [
    'Januari'   => 1,  'Februari' => 2,  'Maret'    => 3,  'April'    => 4,
    'Mei'       => 5,  'Juni'     => 6,  'Juli'     => 7,  'Agustus'  => 8,
    'September' => 9,  'Oktober'  => 10, 'November' => 11, 'Desember' => 12
];

$m1_n = $month_map[$m1] ?? 1;
$m2_n = $month_map[$m2] ?? 12;

// Standard list for SQL FIELD function
$month_list_sql = "'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'";

// ============================================================================
// DATABASE QUERY
// ============================================================================
$sql_query = "SELECT * FROM prestasi 
    WHERE (YEAR(tgl_kegiatan) = '$y' OR '$y' = '')
    AND FIELD(bulan, $month_list_sql) BETWEEN $m1_n AND $m2_n
    ORDER BY FIELD(bulan, $month_list_sql), tgl_kegiatan ASC";

$sql = mysqli_query($sqlconn, $sql_query);

if (!$sql) {
    die("Error Database: " . mysqli_error($sqlconn));
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Prestasi Siswa - <?php echo $namapendeknegeri ?? 'Sekolah'; ?></title>
    <style>
        /* ================================================================ */
        /* RESET & BASE STYLES */
        /* ================================================================ */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
            line-height: 1.5;
            padding: 2.5cm;
        }

        /* ================================================================ */
        /* HEADER (KOP SURAT) */
        /* ================================================================ */
        .kop-surat {
            display: block;
            padding-bottom: 15px;
            margin-bottom: 25px;
            text-align: center;
            position: relative;
            min-height: 100px;
        }

        .logo-wrapper {
            position: absolute;
            left: 0;
            top: 0;
        }

        .logo-wrapper img {
            width: 85px;
            height: auto;
        }

        .kop-info {
            padding-left: 95px;
            padding-right: 95px;
        }

        .kop-info .ks-line {
            font-size: 13pt;
            font-weight: normal;
            margin-bottom: 2px;
            text-transform: uppercase;
        }

        .kop-info .sudin-line {
            font-size: 13pt;
            font-weight: normal;
            margin-bottom: 4px;
            text-transform: uppercase;
        }

        .kop-info h1 {
            font-size: 16pt;
            font-weight: bold;
            margin: 0 0 5px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .kop-info .address-line {
            font-size: 10pt;
            font-weight: normal;
            margin-bottom: 3px;
        }

        .kop-info .contact-line {
            font-size: 9pt;
            font-style: italic;
        }

        /* ================================================================ */
        /* CONTENT STYLES */
        /* ================================================================ */
        .report-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .report-title {
            font-size: 14pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .report-period {
            font-size: 12pt;
            font-weight: normal;
        }

        /* ================================================================ */
        /* TABLE STYLES */
        /* ================================================================ */
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        table thead {
            display: table-header-group;
        }

        th {
            border-bottom: 1px solid #000;
            padding: 10px 6px;
            text-align: center;
            vertical-align: middle;
            background-color: #f2f2f2;
            font-size: 9pt;
            text-transform: uppercase;
            font-weight: bold;
        }

        td {
            font-size: 10pt;
        }

        .align-left { text-align: left; }
        .font-bold { font-weight: bold; }

        /* ================================================================ */
        /* SIGNATURE SECTION */
        /* ================================================================ */
        .signature-section {
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-wrapper {
            float: right;
            width: 300px;
            text-align: center;
        }

        .signature-wrapper p {
            margin-bottom: 2px;
        }

        .signature-space {
            height: 75px;
        }

        .signature-name {
            font-weight: bold;
            text-decoration: underline;
            font-size: 11pt;
        }

        .clear { clear: both; }

        /* ================================================================ */
        /* PRINT OPTIMIZATION */
        /* ================================================================ */
        @media print {
            @page {
                size: A4;
                margin: 0;
            }
            body {
                padding: 1.5cm 2cm;
            }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <?php echo $ks; ?>
    <div class="report-header">
        <h2 class="report-title">REKAPITULASI PRESTASI SISWA TAHUN <?php echo $y; ?></h2>
        <p class="report-period">Periode: <?php echo $m1; ?> s.d <?php echo $m2; ?></p>
    </div>

    <table id="achievement-table">
        <thead>
            <tr>
                <th style="width: 40px;">No</th>
                <th style="width: 180px;">Nama Peserta Didik</th>
                <th style="width: 80px;">Kelas</th>
                <th>Jenis Prestasi / Kejuaraan</th>
                <th style="width: 100px;">Tingkat</th>
                <th style="width: 100px;">Tanggal</th>
                <th style="width: 100px;">Bulan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $has_records = false;
            
            if ($sql && mysqli_num_rows($sql) > 0) {
                while ($r = mysqli_fetch_assoc($sql)) {
                    $has_records = true;
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td class="align-left font-bold"><?php echo htmlspecialchars($r['pd']); ?></td>
                        <td><?php echo htmlspecialchars($r['kelas']); ?></td>
                        <td class="align-left"><?php echo htmlspecialchars($r['prestasi']); ?></td>
                        <td><?php echo htmlspecialchars($r['tingkat']); ?></td>
                        <td><?php 
                            $raw_date = $r['tgl_kegiatan'];
                            echo ($raw_date && $raw_date != '0000-00-00') ? date('d/m/Y', strtotime($raw_date)) : '-';
                        ?></td>
                        <td><?php echo htmlspecialchars($r['bulan']); ?></td>
                    </tr>
                    <?php
                }
            }
            
            if (!$has_records) {
                echo '<tr><td colspan="7" style="padding: 30px; font-style: italic; color: #555;">Tidak ada data prestasi siswa yang ditemukan untuk periode ini.</td></tr>';
            }
            ?>
        </tbody>
    </table>

    <footer class="signature-section">
        <div class="signature-wrapper">
            <p><?php echo $provsek ?? $identitas['kota'] ?? 'Jakarta'; ?>, <?php echo $current_date_in; ?></p>
            <p>Kepala Sekolah,</p>
            <div class="signature-space"></div>
            <p class="signature-name"><?php echo $kepsek ?? '............................................'; ?></p>
            <p>NIP. <?php echo $nipkepsek ?? '............................................'; ?></p>
        </div>
        <div class="clear"></div>
    </footer>

</body>
</html>
