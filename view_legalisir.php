<?php

include "cfg/konek.php";

include "cfg/secure.php";



if(isset($_REQUEST['urut'])) {

    // Sanitasi input untuk mencegah SQL injection

    $id = mysqli_real_escape_string($sqlconn, $_POST['urut']);

    

    // Mengambil data berdasarkan id

    $rql = mysqli_query($sqlconn, "SELECT * FROM legalisir WHERE id = '$id'");

    $r = mysqli_fetch_array($rql);

    

    if($r) {

?>



<div class="modal-body">

    <!-- PDF Viewer -->

    <div class="form-group row">

        <div class="col-12">

            <embed type="application/pdf" src="file/legalisir/<?php echo htmlspecialchars($r['pdf']); ?>" frameborder="0" width="100%" height="600px">

        </div>
    </div>

    

    <!-- No Surat -->

    <div class="form-group row">

        <label class="col-md-5 col-form-label text-left">No. Surat</label>

        <div class="col-md-7">

            <input type="text" name="no_surat" value="<?php echo htmlspecialchars($r['no_surat']); ?>" class="form-control" disabled>

        </div>

    </div>

    

    <!-- Tanggal Surat -->

    <div class="form-group row">

        <label class="col-md-5 col-form-label text-left">Tanggal Surat</label>

        <div class="col-md-7">

            <input type="date" name="tgl_dokumen" value="<?php echo htmlspecialchars($r['tgl_dokumen']); ?>" class="form-control" disabled>

        </div>

    </div>

    

    <!-- Ditujukan Kepada -->

    <div class="form-group row">

        <label class="col-md-5 col-form-label text-left">Ditujukan Kepada</label>

        <div class="col-md-7">

            <input type="text" name="ditujukan" value="<?php echo htmlspecialchars($r['ditujukan']); ?>" class="form-control" disabled>

        </div>

    </div>

    

    <!-- Perihal -->

    <div class="form-group row">

        <label class="col-md-5 col-form-label text-left">Perihal</label>

        <div class="col-md-7">

            <textarea class="form-control" cols="10" rows="7" name="perihal" disabled><?php echo htmlspecialchars($r['perihal']); ?></textarea>

        </div>

    </div>

    

    <!-- Pembuat -->

    <div class="form-group row">

        <label class="col-md-5 col-form-label text-left">Pembuat</label>

        <div class="col-md-7">

            <input type="text" name="pembuat" value="<?php echo htmlspecialchars($r['pembuat']); ?>" class="form-control" disabled>

        </div>

    </div>

</div>



<!-- Modal Footer -->

<div class="modal-footer">

    <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Tutup</button>

</div>



<?php 

    } else {

        echo '<div class="alert alert-danger">Data tidak ditemukan!</div>';

    }

} 

?>