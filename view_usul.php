<?php

include "cfg/konek.php";

include "cfg/secure.php";



if($_REQUEST['urut']) {

    $id = $_POST['urut'];

    // mengambil data berdasarkan id

    // dan menampilkan data ke dalam form modal bootstrap

    $sql = mysqli_query($sqlconn,"SELECT * FROM usulan WHERE id = '$id'");

    $r = mysqli_fetch_array($sql);

?>



<input type="hidden" name="id" value="<?php echo $r['id']; ?>">



<!-- Modal body -->

<div class="modal-body">

    <div class="form-group row">

        <div class="col-12">

            <embed src="file/usulan/<?php echo $r['pdf'];?>" frameborder="0" width="100%" height="600px">

        </div>
    </div>

    <div class="form-group row">

        <label class="col-md-5 col-form-label text-left">No. Surat</label>

        <div class="col-md-7">

            <input type="text" name="no_surat" value="<?php echo $r['no_surat'];?>" class="form-control form-control-sm" disabled>

        </div>

    </div>

    

    <div class="form-group row">

        <label class="col-md-5 col-form-label text-left">Judul</label>

        <div class="col-md-7">

            <textarea class="form-control form-control-sm" cols="10" rows="7" name="judul" disabled><?php echo $r['judul'];?></textarea>

        </div>

    </div>

    

    <div class="form-group row">

        <label class="col-md-5 col-form-label text-left">Tujuan</label>

        <div class="col-md-7">

            <input type="text" name="tujuan" value="<?php echo $r['tujuan'];?>" class="form-control form-control-sm" disabled>

        </div>

    </div>

    

    <div class="form-group row">

        <label class="col-md-5 col-form-label text-left">Tanggal Dikirim</label>

        <div class="col-md-7">

            <input type="text" name="tgl_dokumen" value="<?php echo $r['tgl_dokumen'];?>" class="form-control form-control-sm" disabled>

        </div>

    </div>

</div>



<div class="modal-footer">

    <button type="button" class="btn btn-secondary custom" data-dismiss="modal">Tutup</button>

</div>



<?php } ?>