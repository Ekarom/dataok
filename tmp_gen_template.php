<?php
require_once "PHPExcel/PHPExcel.php";
$objPHPExcel = new PHPExcel();
$sheet = $objPHPExcel->getActiveSheet();

$cols = [
    'pd', 'jk', 'nis', 'nisn', 'kelas',
    'pkn_1', 'ind_1', 'mtk_1', 'ipa_1', 'ips_1', 'eng_1',
    'pkn_2', 'ind_2', 'mtk_2', 'ipa_2', 'ips_2', 'eng_2',
    'pkn_3', 'ind_3', 'mtk_3', 'ipa_3', 'ips_3', 'eng_3',
    'pkn_4', 'ind_4', 'mtk_4', 'ipa_4', 'ips_4', 'eng_4',
    'pkn_5', 'ind_5', 'mtk_5', 'ipa_5', 'ips_5', 'eng_5'
];

for ($i = 0; $i < count($cols); $i++) {
    $sheet->setCellValueByColumnAndRow($i, 1, strtoupper($cols[$i]));
}

$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
$objWriter->save('file/data_nilai_siswa.xls');
echo "Template generated successfully.";
?>
