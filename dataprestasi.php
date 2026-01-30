<?php
include "cfg/konek.php";

?>  

<div class="table-responsive">
    <table id="example3" class="table table-striped table-sm" style="width:100%">
        <thead>
            <tr align="center">
                <th width="5%">No</th>
                <th width="10%">KLS</th>
                <th width="15%">NIS</th>
                <th width="45%">NAMA</th>
                <th width="15%">AKSI</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sqlSiswa = mysqli_query($sqlconn, "SELECT pd, nis, kelas, photo, id FROM siswa ORDER BY pd ASC");
            $noS = 1;
            while ($ds = mysqli_fetch_array($sqlSiswa)) {
                $photo_path = !empty($ds['photo']) && file_exists("file/fotopd/" . $ds['photo']) ? "file/fotopd/" . $ds['photo'] : "images/male.png";
                ?>
                <tr align="center">
                    <td><?php echo $noS++; ?></td>
                    <td><?php echo htmlspecialchars($ds['kelas']); ?></td>
                    <td><?php echo htmlspecialchars($ds['nis']); ?></td>
                    <td class="text-left font-weight-bold"><?php echo htmlspecialchars($ds['pd']); ?></td>
                    <td>
<button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#inputpres" data-id="<?php echo $ds['id']; ?>" data-dismiss="modal">
                      <i class="fas fa-check"></i> Pilih
                    </button>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>

<script>
  $(document).ready(function () {
    $('#example3').DataTable({
       responsive: true
    });
  });
</script>