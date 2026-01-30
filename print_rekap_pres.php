<?php
include "cfg/konek.php";

$m1 = isset($_GET['m1']) ? $_GET['m1'] : 'Januari';
$m2 = isset($_GET['m2']) ? $_GET['m2'] : 'Desember';
$y = isset($_GET['y']) ? $_GET['y'] : date('Y');
$db = isset($_GET['db']) ? $_GET['db'] : '';

if (!empty($db)) {
    mysqli_select_db($sqlconn, $db);
}

$month_list = "'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'";
$month_map = [
    'Januari'=>1, 'Februari'=>2, 'Maret'=>3, 'April'=>4, 'Mei'=>5, 'Juni'=>6,
    'Juli'=>7, 'Agustus'=>8, 'September'=>9, 'Oktober'=>10, 'November'=>11, 'Desember'=>12
];
$m1_n = $month_map[$m1];
$m2_n = $month_map[$m2];

$sql = mysqli_query($sqlconn, "SELECT * FROM prestasi 
    WHERE (YEAR(tgl_kegiatan) = '$y' OR '$y' = '')
    AND FIELD(bulan, $month_list) BETWEEN $m1_n AND $m2_n
    ORDER BY FIELD(bulan, $month_list), tgl_kegiatan ASC");

?>
<!DOCTYPE html>
<html>
<head>
    <title>Rekap Prestasi Siswa</title>
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 12px; margin: 30px; }
        .header { margin-bottom: 20px; border-bottom: 3px double #000; padding-bottom: 10px; }
        .header h2 { margin: 0; text-transform: uppercase; }
        .header p { margin: 5px 0 0 0; }
        .title { text-align: center; font-size: 16px; font-weight: bold; margin-bottom: 20px; text-decoration: underline; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
        .text-left { text-align: left; }
        .footer { margin-top: 50px; }
        .signature { float: right; width: 250px; text-align: center; }
        .clear { clear: both; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <div class="row">
            <div class="col-md-4">
                <img src="<?php echo $sklogo; ?>" alt="Logo Sekolah" style="max-width: 100px;">
            </div>
            <div class="col-md-8">
                <h2><?php echo $namasek; ?></h2>
                <p><?php echo $alamatsek; ?>, <?php echo $kecsek; ?>, <?php echo $kabsek; ?></p>
                <p>Email: <?php echo $emailsek; ?> | Website: <?php echo $website; ?></p>
            </div>
        </div>
    </div>

    <div class="title">REKAPITULASI PRESTASI SISWA</div>
    <p>Periode: <?php echo $m1; ?> s.d <?php echo $m2; ?> <?php echo $y; ?></p>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="20%">Nama Siswa</th>
                <th width="10%">Kelas</th>
                <th width="30%">Prestasi / Juara</th>
                <th width="10%">Tingkat</th>
                <th width="15%">Tanggal</th>
                <th width="10%">Bulan</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $no = 1;
            while($r = mysqli_fetch_array($sql)) {
                echo "<tr>";
                echo "<td>".$no++."</td>";
                echo "<td class='text-left'>".$r['pd']."</td>";
                echo "<td>".$r['kelas']."</td>";
                echo "<td class='text-left'>".$r['prestasisiswa']." (".$r['juara'].")</td>";
                echo "<td>".$r['tingkat']."</td>";
                echo "<td>".date('d/m/Y', strtotime($r['tgl_kegiatan']))."</td>";
                echo "<td>".$r['bulan']."</td>";
                echo "</tr>";
            }
            if(mysqli_num_rows($sql) == 0) {
                echo "<tr><td colspan='7'>Tidak ada data untuk periode ini</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="footer">
        <div class="signature">
            <p><?php echo $kabsek; ?>, <?php echo date('d F Y'); ?></p>
            <p>Kepala Sekolah,</p>
            <br><br><br>
            <p><strong><u><?php echo $kepsek; ?></u></strong></p>
            <p>NIP. <?php echo $nipkepsek; ?></p>
        </div>
        <div class="clear"></div>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()">Cetak Lagi</button>
        <button onclick="window.close()">Tutup</button>
    </div>
</body>
</html>
