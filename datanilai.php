<?php
include_once "cfg/konek.php";
?>
<!-- Main content -->
<section class="content">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header box-shadow-0 bg-gradient-x-warning">
                    <h3 class="card-title text-white">Data Nilai Siswa Kelas 9</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered" style="width:100%">
                        <thead class="bg-gradient-x-secondary text-white">
                            <tr>
                                <th rowspan="2" class="align-middle text-center">No</th>
                                <th rowspan="2" class="align-middle text-center">Nama Peserta Didik</th>
                                <th rowspan="2" class="align-middle text-center">JK</th>
                                <th rowspan="2" class="align-middle text-center">NIS</th>
                                <th rowspan="2" class="align-middle text-center">NISN</th>
                                <th rowspan="2" class="align-middle text-center">Kelas</th>
                                <th rowspan="2" class="align-middle text-center">Tempat Lahir</th>
                                <th rowspan="2" class="align-middle text-center">Tgl Lahir</th>
                                <th rowspan="2" class="align-middle text-center">NIK</th>
                                <th rowspan="2" class="align-middle text-center">Agama</th>
                                <th rowspan="2" class="align-middle text-center">Alamat</th>
                                <th rowspan="2" class="align-middle text-center">No HP</th>
                                <th rowspan="2" class="align-middle text-center">Email</th>
                                <th rowspan="2" class="align-middle text-center">Nama Ayah</th>
                                <th rowspan="2" class="align-middle text-center">Nama Ibu</th>
                                <th colspan="6" class="text-center bg-info">Semester 1</th>
                                <th colspan="6" class="text-center bg-primary">Semester 2</th>
                                <th colspan="6" class="text-center bg-info">Semester 3</th>
                                <th colspan="6" class="text-center bg-primary">Semester 4</th>
                                <th colspan="6" class="text-center bg-info">Semester 5</th>
                            </tr>
                            <tr>
                                <!-- Semester 1 -->
                                <th class="bg-light text-dark">PKN</th>
                                <th class="bg-light text-dark">IND</th>
                                <th class="bg-light text-dark">MTK</th>
                                <th class="bg-light text-dark">IPA</th>
                                <th class="bg-light text-dark">IPS</th>
                                <th class="bg-light text-dark">ENG</th>
                                <!-- Semester 2 -->
                                <th class="bg-light text-dark">PKN</th>
                                <th class="bg-light text-dark">IND</th>
                                <th class="bg-light text-dark">MTK</th>
                                <th class="bg-light text-dark">IPA</th>
                                <th class="bg-light text-dark">IPS</th>
                                <th class="bg-light text-dark">ENG</th>
                                <!-- Semester 3 -->
                                <th class="bg-light text-dark">PKN</th>
                                <th class="bg-light text-dark">IND</th>
                                <th class="bg-light text-dark">MTK</th>
                                <th class="bg-light text-dark">IPA</th>
                                <th class="bg-light text-dark">IPS</th>
                                <th class="bg-light text-dark">ENG</th>
                                <!-- Semester 4 -->
                                <th class="bg-light text-dark">PKN</th>
                                <th class="bg-light text-dark">IND</th>
                                <th class="bg-light text-dark">MTK</th>
                                <th class="bg-light text-dark">IPA</th>
                                <th class="bg-light text-dark">IPS</th>
                                <th class="bg-light text-dark">ENG</th>
                                <!-- Semester 5 -->
                                <th class="bg-light text-dark">PKN</th>
                                <th class="bg-light text-dark">IND</th>
                                <th class="bg-light text-dark">MTK</th>
                                <th class="bg-light text-dark">IPA</th>
                                <th class="bg-light text-dark">IPS</th>
                                <th class="bg-light text-dark">ENG</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sqlSiswa = mysqli_query($sqlconn, "SELECT * FROM nilai ORDER BY pd ASC");
                            if ($sqlSiswa) {
                                $noS = 1;
                                while ($ds = mysqli_fetch_array($sqlSiswa)) {
                                    $jk_label = ($ds['jk'] == 'Laki-laki') ? 'L' : (($ds['jk'] == 'Perempuan') ? 'P' : $ds['jk']);
                                    ?>
                                    <tr>
                                        <td class="text-center"><?php echo $noS++; ?></td>
                                        <td class="text-left px-3 font-weight-bold">
                                            <?php echo htmlspecialchars($ds['pd']); ?>
                                        </td>
                                        <td class="text-center"><?php echo $jk_label; ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($ds['nis']); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($ds['nisn']); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($ds['kelas']); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($ds['tempat_lahir'] ?? '-'); ?>
                                        </td>
                                        <td class="text-center"><?php echo htmlspecialchars($ds['tgl_lahir'] ?? '-'); ?>
                                        </td>
                                        <td class="text-center"><?php echo htmlspecialchars($ds['nik'] ?? '-'); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($ds['agama'] ?? '-'); ?></td>
                                        <td class="text-left small"
                                            style="max-width: 200px; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo htmlspecialchars($ds['alamat'] ?? '-'); ?>
                                        </td>
                                        <td class="text-center"><?php echo htmlspecialchars($ds['no_hp'] ?? '-'); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($ds['email'] ?? '-'); ?></td>
                                        <td class="text-center"><?php echo htmlspecialchars($ds['nama_ayah'] ?? '-'); ?>
                                        </td>
                                        <td class="text-center"><?php echo htmlspecialchars($ds['nama_ibu'] ?? '-'); ?></td>

                                        <!-- Grades -->
                                        <?php for ($s = 1; $s <= 5; $s++): ?>
                                            <td class="text-center"><?php echo $ds['pkn_' . $s] ?: '-'; ?></td>
                                            <td class="text-center"><?php echo $ds['ind_' . $s] ?: '-'; ?></td>
                                            <td class="text-center"><?php echo $ds['mtk_' . $s] ?: '-'; ?></td>
                                            <td class="text-center"><?php echo $ds['ipa_' . $s] ?: '-'; ?></td>
                                            <td class="text-center"><?php echo $ds['ips_' . $s] ?: '-'; ?></td>
                                            <td class="text-center"><?php echo $ds['eng_' . $s] ?: '-'; ?></td>
                                        <?php endfor; ?>

                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='45' class='text-center'>Error: " . mysqli_error($sqlconn) . "</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    </div>
</section>
<!-- Global scripts provided by index.php -->
<script>
    $(document).ready(function () {
        $('table.table').DataTable({
            scrollY: 450,
            scrollX: true,
            scrollCollapse: true,
            paging: false,
            // fixedColumns:   {
            //     leftColumns: 3
            // }
        });
        // Premium styling for search input
        $('.dataTables_filter input').addClass('form-control form-control-sm').css({
            'display': 'inline-block',
            'width': '200px',
            'margin-left': '10px',
            'border': '1px solid #ced4da'
        });
    });
</script>