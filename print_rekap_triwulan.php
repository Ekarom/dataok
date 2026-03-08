<?php
include "cfg/konek.php";
$query_identitas = mysqli_query($sqlconn, "SELECT * FROM profils LIMIT 1");
$identitas = mysqli_fetch_assoc($query_identitas);

$ks        = $identitas['kop_dinas'] ?? '';
$kepsek    = $identitas['kepsek'] ?? '';
$nipkepsek = $identitas['nipkepsek'] ?? '';

$m1 = isset($_GET['m1']) ? $_GET['m1'] : 'Januari';
$m2 = isset($_GET['m2']) ? $_GET['m2'] : 'Desember';
$y = isset($_GET['y']) ? $_GET['y'] : date('Y');
$db = isset($_GET['db']) ? $_GET['db'] : '';
$tw = isset($_GET['tw']) ? $_GET['tw'] : '';
$kej = isset($_GET['kej']) ? $_GET['kej'] : '';

if (!empty($db)) {
    mysqli_select_db($sqlconn, $db);
}

$month_list = "'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'";
$month_map = [
    'Januari'=>1, 'Februari'=>2, 'Maret'=>3, 'April'=>4, 'Mei'=>5, 'Juni'=>6,
    'Juli'=>7, 'Agustus'=>8, 'September'=>9, 'Oktober'=>10, 'November'=>11, 'Desember'=>12
];
$m1_n = isset($month_map[$m1]) ? $month_map[$m1] : 1;
$m2_n = isset($month_map[$m2]) ? $month_map[$m2] : 12;

$where = "WHERE (YEAR(tgl_kegiatan) = '$y' OR '$y' = '')";
$where .= " AND FIELD(bulan, $month_list) BETWEEN $m1_n AND $m2_n";
if (!empty($kej)) {
    $kej_esc = mysqli_real_escape_string($sqlconn, $kej);
    $where .= " AND prestasi = '$kej_esc'";
}

$sql = mysqli_query($sqlconn, "SELECT * FROM prestasi $where ORDER BY FIELD(bulan, $month_list), tgl_kegiatan ASC");

$triwulan_map = [
    '1' => 'I (JANUARI - MARET)',
    '2' => 'II (APRIL - JUNI)',
    '3' => 'III (JULI - SEPTEMBER)',
    '4' => 'IV (OKTOBER - DESEMBER)'
];
$tw_text = isset($triwulan_map[$tw]) ? $triwulan_map[$tw] : "";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Rekap Prestasi Triwulan</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 13px; margin: 20px; color: #000; }
        
        /* Header (Kop Surat) Styles */
        .kop-surat { margin-bottom: 25px; text-align: center; }
        .kop-surat table, .kop-surat td, .kop-surat th { border: none !important; }

        /* Report Header Styles */
        .report-header { text-align: center; margin-bottom: 25px; }
        .report-header h3 { margin: 4px 0; text-transform: uppercase; font-size: 20px; }
        .report-header h4 { margin: 4px 0; text-transform: uppercase; font-size: 16px; }
        
        /* Table Styles */
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #000; padding: 8px 5px; text-align: center; vertical-align: middle; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; font-size: 13px; }
        td { font-size: 14px; }
        .text-left { text-align: left; }
        
        /* Footer & Signature Styles */
        .footer { margin-top: 30px; }
        .signature { float: right; width: 300px; text-align: center; }
        .signature-date { margin-bottom: 5px; display: block; }
        .signature-role { margin: 0; }
        .signature-name { margin-top: 60px; margin-bottom: 0; text-decoration: underline; font-weight: bold; }
        .signature-nip { margin: 0; }
        .clear { clear: both; }
        
        @media print {
            .no-print { display: none; }
            @page { size: landscape; margin: 1cm; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="kop-surat">
        <?php echo $ks; ?>
    </div>

    <div class="report-header">
        <h3>REKAP DAN DATA PRESTASI </h3>
        <h3><?php echo strtoupper($namasek); ?></h3>
        <?php if (!empty($tw)) { ?>
            <h3>PER - TRIWULAN <?php echo $tw_text; ?> <?php echo $y; ?></h3>
        <?php } else { ?>
            <h3>PERIODE <?php echo strtoupper($m1); ?> - <?php echo strtoupper($m2); ?> <?php echo $y; ?></h3>
        <?php } ?>
        <?php if (!empty($kej)) { ?>
            <h4>KEJUARAAN: <?php echo strtoupper($kej); ?></h4>
        <?php } ?>
    </div>

    <table>
        <thead>
            <tr>
                <th width="3%">NO</th>
                <th width="12%">NAMA SEKOLAH</th>
                <th width="15%">KETERANGAN PESERTA DIDIK</th>
                <th width="7%">KELAS</th>
                <th width="15%">PRESTASI SISWA</th>
                <th width="10%">JENIS PRESTASI</th>
                <th width="10%">NAMA KEGIATAN</th>
                <th width="10%">TANGGAL KEGIATAN</th>
                <th width="10%">PERINGKAT</th>
                <th width="8%">TINGKAT</th>
                <th width="8%">PENYELENGGARAAN</th>
                <th width="10%">LOKASI</th>
            </tr>
            <tr style="font-size: 8px; background: #eee;">
                <th>1</th>
                <th>2</th>
                <th>3</th>
                <th>4</th>
                <th>5</th>
                <th>6</th>
                <th>7</th>
                <th>8</th>
                <th>9</th>
                <th>10</th>
                <th>11</th>
                <th>12</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            $months_id = [
                'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret',
                'April' => 'April', 'May' => 'Mei', 'June' => 'Juni',
                'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September',
                'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
            ];
            
            while($r = mysqli_fetch_array($sql)) {
                $tgl = date('d F Y', strtotime($r['tgl_kegiatan']));
                foreach($months_id as $en => $id) {
                    $tgl = str_replace($en, $id, $tgl);
                }

                echo "<tr>";
                echo "<td>".$no++."</td>";
                echo "<td>".$namasek."</td>";
                echo "<td class='text-left'>".$r['pd']."</td>";
                echo "<td>".$r['kelas']."</td>";
                echo "<td class='text-left'>".$r['prestasi']."</td>";
                echo "<td>".$r['jenisprestasi']."</td>";
                echo "<td>".$r['nama_kegiatan']."</td>";
                echo "<td>".$tgl."</td>";
                echo "<td>".$r['juara']."</td>";
                echo "<td>".$r['tingkat']."</td>";
                echo "<td class='text-left'>".$r['penyelenggara']."</td>";
                echo "<td class='text-left'>".$r['lokasi']."</td>";
                echo "</tr>";
            }
            if(mysqli_num_rows($sql) == 0) {
                echo "<tr><td colspan='11'>Tidak ada data untuk periode ini</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <?php 
                $curr_date = date('d F Y');
                foreach($months_id as $en => $id) {
                    $curr_date = str_replace($en, $id, $curr_date);
                }
            ?>
            <span class="signature-date"><?php echo $provsek; ?>, <?php echo $curr_date; ?></span>
            <p class="signature-role">Kepala Sekolah,</p>
            <p class="signature-name"><?php echo $kepsek; ?></p>
            <p class="signature-nip">NIP. <?php echo $nipkepsek; ?></p>
        </div>
        <div class="clear"></div>
    </div>

</body>
</html>
