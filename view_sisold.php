<?php

include "cfg/conn.php";
include "cfg/secure.php";
        


    if($_REQUEST['nis']) {
        $id = $_POST['nis'];
        // mengambil data berdasarkan id
        // dan menampilkan data ke dalam form modal bootstrap
        $sql = mysqli_query($conn,"SELECT * FROM pd WHERE id = '$id'");
        $r = mysqli_fetch_array($sql);
        $nis=$r['nis'];
        	
        
?>



 
        	<form action="?modul=usera&simpan=yes" method="post">
            <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
            
			<div class="form-group">
			<center><div style="width:100%; background-color:#ff8d33; color:#FFFFFF; padding:10px; margin-top:10px; font-size:22px">Edit Data User</div></center>
            </div>
            
                
                         <div class="card-body box-profile">
                         <div id="upload2" class="text-center">
                         <?php 
                         if($r['id'] !=="")
                         {
                         
                         ?>
                  <img class="profile-user-img img-fluid img-circle" src="photo/<?php echo $r['photo']; ?>" alt="User profile picture">
                   </div><span id="status2" ></span>

 <?php }
 else
 {
?>
<img class="profile-user-img img-fluid img-circle" src="../images/male.png" alt="User profile picture">
                   </div><span id="status2" ></span>
                   <?php } ?>


                <h3 class="profile-username text-center"><?php echo $r['nama']; ?></h3>
                
                <p class="text-muted text-center">Level : <?php echo $log = $r['level']; ?></p>
                 </div>
            
            <table><table width="100%" >
			<tr><td><label>Level</label></td><td width="5%"><br><br></td><td>
			<select class="form-control" name="level">
					
					<option value="1" <?php if ($log=="1"){echo "selected";} ?>>Admin</option>
					<option value="3" <?php if ($log=="3"){echo "selected";} ?>>Staff</option>
					<option value="4" <?php if ($log=="4"){echo "selected";} ?>>Guru</option>
					<!--
					<option value="3">Siswa</option>
					!-->
				</select>
				</tr></td>
            <tr><td><label>User Id</label></td><td width="5%"><br><br></td><td>
                <input type="text" class="form-control" name="userid" value="<?php echo $r['userid']; ?>">
            </td></tr>
            
            <tr><td><label>Password</label></td><td width="5%"><br><br></td><td>
                <input type="text" class="form-control" name="pass">
            </td></tr>
            <tr><td><label>Nama</label></td><td width="5%"><br><br></td><td>
                <input type="text" class="form-control" name="nama" value="<?php echo $r['nama']; ?>">
            </td></tr>
           
           <tr><td><label>NIP/NIKI/-</label></td><td width="5%"><br><br></td><td>
                <input type="text" class="form-control" name="nik" value="<?php echo $r['nik']; ?>">
            </td></tr>
            <tr><td><label>Status</label></td><td width="5%"><br><br></td><td>
              <select class="form-control" name="status">
					<option value="">Pilih Status</option>
					<option value="1" <?php if ($stts=="1"){echo "selected";} ?>>Aktif</option>
					<option value="0" <?php if ($stts=="0"){echo "selected";} ?>>Non Aktif</option>
								
				</select>
            </td></tr>
            
			</table>
			            <div class="modal-footer">
              </div>

			<button type="button" class="btn bg-gradient-secondary" data-dismiss="modal">Batal</button>
			<button class="btn bg-gradient-primary" type="submit">Update Data User</button>
        </form>
 
<?php } ?>