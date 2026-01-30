<?php

include "cfg/conn.php";
include "cfg/secure.php";


    if($_REQUEST['urut']) {
        $id = $_POST['urut'];
        // mengambil data berdasarkan id
        // dan menampilkan data ke dalam form modal bootstrap
        $sql = mysqli_query($conn,"SELECT * FROM siswa WHERE id = '$id'");
        $r = mysqli_fetch_array($sql);
        $photo=$r['photo'];
        //$pic = $r['XPoto'];
        	
        
?>



 
            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
            
            </div>
            </div>
                                                                        
            
                                                <!-- Modal body -->
                                                <form method="post" enctype="multipart/form-data">
                                               <div class="modal-body">
                                                <div class="row row-sm">
                                                <div class="col-lg-4">

                                                <p>PESERTA DIDIK</p> 
                                                      </div>
                                                <div class="col-lg-8 mb-2"> 
                                                <input type="text" name="pesertadidik"value="<?php echo $r['pd']; ?>" class="form-control form-control " disabled>
                                                </div>
                                                </div>
                                                <div class="row row-sm">
                                                <div class="col-lg-4">

                                                <p>JENIS KELAMIN</p> 
                                                      </div>
                                                <div class="col-lg-8 mb-2"> 
                                                <input type="text" name="pesertadidik"value="<?php echo $r['jk']; ?>" class="form-control form-control " disabled>
                                                </div>
                                                </div>
                                                <div class="row row-sm">
                                                <div class="col-lg-4">
                                                <p>NIS</p> 
                                                       </div>
                                                <div class="col-lg-8 mb-2"> 
                                                <input type="text" name="nis" value="<?php echo $r['nis']; ?>" class="form-control form-control" disabled>
                                                 </div>
                                                </div>
                                                <div class="row row-sm">
                                                <div class="col-lg-4">
                                                <p>NISN</p> 
                                                          </div>
                                                <div class="col-lg-8 mb-2"> 
                                                <input type="text" name="nisn" value="<?php echo $r['nisn']; ?>" class="form-control form-control" disabled>
                                                </div>
                                                </div>
                                                        <div class="row row-sm">
                                                        <div class="col-lg-4">
                                                <p>Kelas</p> 
                                                          </div>
                                                <div class="col-lg-8 mb-2"> 
                                                <input type="text" name="kelas" value="<?php echo $r['kelas']; ?>" class="form-control form-control" disabled>
                                                </div>
                                                </div>
                                                    

                
                </div>
                <div class="modal-footer">
			<button type="button" class="btn btn-secondary btn-flat custom" data-dismiss="modal">Tutup</button>
</div>
                                                </form>
                                            </div>
                                            </div>
                                         </div>
 
<?php } ?>