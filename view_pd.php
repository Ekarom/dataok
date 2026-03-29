<?php

include "cfg/konek.php";
include "cfg/secure.php";


    if($_REQUEST['urut']) {
        $id = $_POST['urut'];
        // mengambil data berdasarkan id
        // dan menampilkan data ke dalam form modal bootstrap
        $sql = mysqli_query($sqlconn,"SELECT * FROM siswa WHERE id = '$id'");
        $r = mysqli_fetch_array($sql);
        $photo=$r['photo'];
        //$pic = $r['XPoto'];
        	
        
?>



 
            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
            
            </div>
            </div>
                                                                         <div class="card-body box-profile">
                         <div id="upload2" class="text-center">
                         <?php 
                         if($r['id'] !=="")
                         {
                         
                         ?>
                  <img class="profile-user-img img-fluid img-circle" src="file/fotopd/<?php echo $r['photo']; ?>" alt="User profile picture">
                   </div><span id="status2" ></span>

 <?php }
 else
 {
?>
<img class="profile-user-img img-fluid img-circle" src="images/male.png" alt="User profile picture">
                   </div><span id="status2" ></span>
                   <?php } ?>


                <h3 class="profile-username text-center"><?php echo $r['pd']; ?></h3>
                
                <p class="text-muted text-center">NIS : <?php echo $log = $r['nis']; ?></p>
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
                                                <p>NIS</p> 
                                                       </div>
                                                <div class="col-lg-8 mb-2"> 
                                                <input type="text" name="nis" value="<?php echo $r['nis']; ?>" class="form-control form-control" disabled>
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
			<button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal">Tutup</button>
</div>
                                                </form>
                                            </div>
                                            </div>
                                         </div>
 
<?php } ?>
