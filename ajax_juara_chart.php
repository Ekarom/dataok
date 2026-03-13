<?php
// Simpan parameter GET SEBELUM include apa pun
// karena secure.php menimpa $semester dengan $_SESSION['semester']
$_bulan    = isset($_GET['bulan'])    ? (int)trim($_GET['bulan'])    : 0;
$_semester = isset($_GET['semester']) ? (int)trim($_GET['semester']) : 0;
$_tahun    = isset($_GET['tahun'])    ? trim($_GET['tahun'])         : '';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "cfg/konek.php";
include "cfg/secure.php";

header('Content-Type: application/json');

$bulan    = $_bulan;
$semester = $_semester;
$tahun    = $_tahun;

$smt1_bulan = [7, 8, 9, 10, 11, 12];
$smt2_bulan = [1, 2, 3, 4, 5, 6];

$nama_bulan_map = [
    'Januari'=>1,'Februari'=>2,'Maret'=>3,'April'=>4,'Mei'=>5,'Juni'=>6,
    'Juli'=>7,'Agustus'=>8,'September'=>9,'Oktober'=>10,'November'=>11,'Desember'=>12
];
$no_to_bulan = array_flip($nama_bulan_map);
$nama_bulan_short = [
    1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
    7=>'Jul',8=>'Agt',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des'
];
$nama_bulan_full = [
    1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',
    7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'
];

// Ambil daftar database dari dbset
$q_db = mysqli_query($sqlconn, "SELECT dbname, tahun FROM dbset ORDER BY tahun ASC");
$db_list = [];
if ($q_db) {
    while ($r = mysqli_fetch_assoc($q_db)) {
        $db_list[] = $r;
    }
}

// Data: monthly_juara[bulan][juara] = count
$monthly_juara = [];
$available_years = [];

foreach ($db_list as $db) {
    try {
        $c_db = @new mysqli("localhost", "root", "", $db['dbname']);
        if ($c_db->connect_error) continue;

        $tbl_check = $c_db->query("SHOW TABLES LIKE 'prestasi'");
        if (!$tbl_check || $tbl_check->num_rows == 0) { $c_db->close(); continue; }

        $cols = [];
        $res_cols = $c_db->query("DESCRIBE prestasi");
        if ($res_cols) { while ($col = $res_cols->fetch_assoc()) $cols[] = $col['Field']; }

        $has_tgl   = in_array('tgl_kegiatan', $cols);
        $has_bulan = in_array('bulan', $cols);
        $has_juara = in_array('juara', $cols);
        if (!$has_juara) { $c_db->close(); continue; }

        // Kumpulkan tahun
        if ($has_tgl) {
            $q_years = $c_db->query("SELECT DISTINCT YEAR(tgl_kegiatan) as thn FROM prestasi WHERE tgl_kegiatan IS NOT NULL AND tgl_kegiatan != '0000-00-00' AND YEAR(tgl_kegiatan) > 0");
            if ($q_years) {
                while ($yr = $q_years->fetch_assoc()) {
                    $y = (int)$yr['thn'];
                    if ($y > 0 && !in_array($y, $available_years)) $available_years[] = $y;
                }
            }
        }

        // WHERE filter
        $where_parts = [];
        if ($tahun !== '' && $has_tgl) {
            $where_parts[] = "tgl_kegiatan IS NOT NULL AND tgl_kegiatan != '0000-00-00' AND YEAR(tgl_kegiatan) = " . (int)$tahun;
        }
        if ($bulan > 0) {
            if ($has_tgl && $has_bulan) {
                $bln_name = $c_db->real_escape_string($no_to_bulan[$bulan] ?? '');
                $where_parts[] = "((tgl_kegiatan IS NOT NULL AND tgl_kegiatan != '0000-00-00' AND MONTH(tgl_kegiatan) = $bulan) OR ((tgl_kegiatan IS NULL OR tgl_kegiatan = '0000-00-00') AND bulan = '$bln_name'))";
            } elseif ($has_tgl) {
                $where_parts[] = "tgl_kegiatan IS NOT NULL AND tgl_kegiatan != '0000-00-00' AND MONTH(tgl_kegiatan) = $bulan";
            } elseif ($has_bulan) {
                $bln_name = $c_db->real_escape_string($no_to_bulan[$bulan] ?? '');
                $where_parts[] = "bulan = '$bln_name'";
            }
        } elseif ($semester > 0) {
            $months = ($semester == 1) ? $smt1_bulan : $smt2_bulan;
            $in_str = implode(',', $months);
            if ($has_tgl && $has_bulan) {
                $bln_list = [];
                foreach ($months as $m) $bln_list[] = "'" . $c_db->real_escape_string($no_to_bulan[$m] ?? '') . "'";
                $where_parts[] = "((tgl_kegiatan IS NOT NULL AND tgl_kegiatan != '0000-00-00' AND MONTH(tgl_kegiatan) IN ($in_str)) OR ((tgl_kegiatan IS NULL OR tgl_kegiatan = '0000-00-00') AND bulan IN (" . implode(',', $bln_list) . ")))";
            } elseif ($has_tgl) {
                $where_parts[] = "tgl_kegiatan IS NOT NULL AND tgl_kegiatan != '0000-00-00' AND MONTH(tgl_kegiatan) IN ($in_str)";
            } elseif ($has_bulan) {
                $bln_list = [];
                foreach ($months as $m) $bln_list[] = "'" . $c_db->real_escape_string($no_to_bulan[$m] ?? '') . "'";
                $where_parts[] = "bulan IN (" . implode(',', $bln_list) . ")";
            }
        }

        $where_sql = !empty($where_parts) ? "WHERE " . implode(" AND ", $where_parts) : "";

        // Query per bulan per juara
        if ($has_tgl && $has_bulan) {
            // Punya kedua kolom: gunakan CASE untuk tentukan bulan
            // agar record dengan tgl_kegiatan NULL/0000 tetap terhitung via kolom bulan
            $bulan_case = "CASE 
                WHEN tgl_kegiatan IS NOT NULL AND tgl_kegiatan != '0000-00-00' THEN MONTH(tgl_kegiatan)
                ELSE CASE bulan 
                    WHEN 'Januari' THEN 1 WHEN 'Februari' THEN 2 WHEN 'Maret' THEN 3
                    WHEN 'April' THEN 4 WHEN 'Mei' THEN 5 WHEN 'Juni' THEN 6
                    WHEN 'Juli' THEN 7 WHEN 'Agustus' THEN 8 WHEN 'September' THEN 9
                    WHEN 'Oktober' THEN 10 WHEN 'November' THEN 11 WHEN 'Desember' THEN 12
                    ELSE 0 END
                END";
            $sql = "SELECT ($bulan_case) as bln, juara, COUNT(*) as total 
                    FROM prestasi $where_sql
                    GROUP BY bln, juara HAVING bln > 0 ORDER BY bln ASC";
            $res = $c_db->query($sql);
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $m = (int)$row['bln'];
                    $j = trim($row['juara']);
                    if ($m < 1 || $m > 12 || $j === '') continue;
                    if (!isset($monthly_juara[$m])) $monthly_juara[$m] = [];
                    if (!isset($monthly_juara[$m][$j])) $monthly_juara[$m][$j] = 0;
                    $monthly_juara[$m][$j] += (int)$row['total'];
                }
            }
        } elseif ($has_tgl) {
            // Hanya tgl_kegiatan, filter NULL/0000
            $sql = "SELECT MONTH(tgl_kegiatan) as bln, juara, COUNT(*) as total 
                    FROM prestasi " . ($where_sql ? "$where_sql AND" : "WHERE") . " tgl_kegiatan IS NOT NULL AND tgl_kegiatan != '0000-00-00'
                    GROUP BY bln, juara ORDER BY bln ASC";
            $res = $c_db->query($sql);
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $m = (int)$row['bln'];
                    $j = trim($row['juara']);
                    if ($m < 1 || $m > 12 || $j === '') continue;
                    if (!isset($monthly_juara[$m])) $monthly_juara[$m] = [];
                    if (!isset($monthly_juara[$m][$j])) $monthly_juara[$m][$j] = 0;
                    $monthly_juara[$m][$j] += (int)$row['total'];
                }
            }
        } elseif ($has_bulan) {
            $sql = "SELECT bulan as bln_nama, juara, COUNT(*) as total FROM prestasi $where_sql GROUP BY bln_nama, juara";
            $res = $c_db->query($sql);
            if ($res) {
                while ($row = $res->fetch_assoc()) {
                    $m = $nama_bulan_map[trim($row['bln_nama'])] ?? 0;
                    $j = trim($row['juara']);
                    if ($m < 1 || $m > 12 || $j === '') continue;
                    if (!isset($monthly_juara[$m])) $monthly_juara[$m] = [];
                    if (!isset($monthly_juara[$m][$j])) $monthly_juara[$m][$j] = 0;
                    $monthly_juara[$m][$j] += (int)$row['total'];
                }
            }
        }

        $c_db->close();
    } catch (Exception $e) {
        // Ignore connection errors if database doesn't exist
        continue;
    }
}

sort($available_years);
ksort($monthly_juara);

// --------------------------------------------------------
// Bangun data chart: X = bulan, Y = persentase Juara 1, 2, 3
// --------------------------------------------------------
$chart_labels = [];
$data_j1 = [];
$data_j2 = [];
$data_j3 = [];
$monthly_stats = [];

foreach ($monthly_juara as $m => $juara_data) {
    $total_bulan = array_sum($juara_data);
    $j1 = isset($juara_data['1']) ? (int)$juara_data['1'] : 0;
    $j2 = isset($juara_data['2']) ? (int)$juara_data['2'] : 0;
    $j3 = isset($juara_data['3']) ? (int)$juara_data['3'] : 0;

    $pct1 = ($total_bulan > 0) ? round(($j1 / $total_bulan) * 100, 1) : 0;
    $pct2 = ($total_bulan > 0) ? round(($j2 / $total_bulan) * 100, 1) : 0;
    $pct3 = ($total_bulan > 0) ? round(($j3 / $total_bulan) * 100, 1) : 0;

    $chart_labels[] = $nama_bulan_short[$m] ?? "Bln $m";
    $data_j1[] = $pct1;
    $data_j2[] = $pct2;
    $data_j3[] = $pct3;

    $monthly_stats[] = [
        'bulan'     => $nama_bulan_full[$m] ?? "Bulan $m",
        'bulan_num' => $m,
        'total'     => $total_bulan,
        'juara_1'   => $j1,
        'juara_2'   => $j2,
        'juara_3'   => $j3,
        'pct_1'     => $pct1,
        'pct_2'     => $pct2,
        'pct_3'     => $pct3,
    ];
}

echo json_encode([
    'labels'        => $chart_labels,
    'datasets'      => [
        ['label' => 'Juara 1', 'data' => $data_j1, 'color' => '#f39c12'],
        ['label' => 'Juara 2', 'data' => $data_j2, 'color' => '#00c0ef'],
        ['label' => 'Juara 3', 'data' => $data_j3, 'color' => '#3c8dbc'],
    ],
    'tahun_list'    => $available_years,
    'monthly_stats' => $monthly_stats
]);
?>
