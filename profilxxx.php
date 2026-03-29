 <script
 src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<link rel="stylesheet"
 href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script>
function simpan() {

         Command: toastr["success"]("Password berhasil diganti!")

toastr.options = {
  "closeButton": false,
  "debug": false,
  "newestOnTop": false,
  "progressBar": true,
  "positionClass": "toast-top-center",
  "preventDuplicates": false,
  "onclick": null,
  "showDuration": "300",
  "hideDuration": "1000",
  "timeOut": "2000",
  "extendedTimeOut": "1000",
  "showEasing": "swing",
  "hideEasing": "linear",
  "showMethod": "fadeIn",
  "hideMethod": "fadeOut"
}

}
</script>
<?php 
//include "cfg/conn.php";
include "cfg/secure.php";

//$sql = mysqli_query($conn,"select * from usera");
//$xadm = mysqli_fetch_array($sql);
//$nik= $xadm['nik'];
//$poto= $xadm['image'];


?>
<script type="text/javascript" src="js/ajaxupload.3.5.js" ></script>
                                
<script type="text/javascript" >

function uploadFile(){

var file = document.getElementById("photo1").files[0];
	// alert(file.name+" | "+file.size+" | "+file.type);
	var formdata = new FormData();
	formdata.append("photo1", file);
	var ajax = new XMLHttpRequest();
	ajax.upload.addEventListener("progress", progressHandler, false);
	ajax.addEventListener("load", completeHandler, false);
	ajax.addEventListener("error", errorHandler, false);
	ajax.addEventListener("abort", abortHandler, false);
	ajax.open("POST", "post/photo.php");
	ajax.send(formdata);
}
function progressHandler(event){
	document.getElementById("loaded_n_total").innerHTML = "Uploaded "+event.loaded+" bytes of "+event.total;
	var percent = (event.loaded / event.total) * 100;
	document.getElementById("progressBar").value = Math.round(percent);
	document.getElementById("status").innerHTML = "Mohon Tunggu.. " + Math.round(percent)+"%";
}
function completeHandler(event){
	document.getElementById("status").innerHTML = event.target.responseText;
	document.getElementById("progressBar").value = 0;
	
$("#exisImage").hide();	

document.getElementById("berhasil").innerHTML = "<font color='green'>Upload Berhasil..!</font>";
}
function errorHandler(event){
	document.getElementById("status").innerHTML = "Upload Gagal";
}
function abortHandler(event){
	document.getElementById("status").innerHTML = "Upload Terputus";
}
</script>
<!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
       <h1>
      Profil
        
      </h1>
      <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="#">Profil</a></li>
            </ol>

    </section>
	
     <!-- Main content -->
    <section class="content">
      <!-- Small boxes (Stat box) -->
      <div class="row">
        <div class="col-xs-3">
     
          <div class="box box-primary">
            <div class="box-body box-profile">
                
                  <img class="profile-user-img img-responsive img-circle" src='up/profil/<?php echo $poto?>' alt="User profile picture"  style="cursor:pointer">
           
                <?php
        if($nuser !=="")
        {
        echo "<h3 class='profile-username text-center'>$nuser</h3>";
        }
       else
       {
        echo "<h3 class='profile-username text-center'>$nis</h3>";
       }
        ?>
                

                

                <p class="text-muted text-center"><?php 
                if ($lv =="1")
                {
                echo "Administrator";
                }
                else
                {
                echo "Staff";
                }
                ?>
                </p>
<form id="upload_form" enctype="multipart/form-data" method="post">
  <input type="file" name="photo1" id="photo1" onchange="uploadFile()"><br>
  <br/>
  <progress id="progressBar" value="0" max="100" style="width:210px;"></progress>
  
  <p id="loaded_n_total"></p>
  <div id='berhasil' hidden></div>
</form>
                <button type="button" class="btn btn-info btn-block" data-toggle="modal" data-target="#gantiPass">Ubah Password</button>
                       </div>
            <!-- /.box-body -->
          </div>
          <!-- /.box -->

          
        </div>
        <!-- /.col -->
        <div class="col-xs-9">
         <div class="box box-primary">
                <div class="box-header with-border">
                <h3 class="box-title text white">
                <i class="fas fa-history mr-1"></i>
                About Me
                       </h3>
					</div>
    
              <!-- /.card-header -->
              <div class="card-body">
                <!-- Conversations are loaded here -->
                <div class="direct-chat-messages">
                  <!-- Message. Default to the left -->
					   
              <!-- /.card-header -->
              <div class="card-body">
                <strong>Nama</strong>

                <p class="text-muted">
                  <?php echo "$nama"; ?>
                </p>

                <hr>
				<strong>User Id</strong>

                <p class="text-muted">
                  <?php echo "$nuser"; ?>
                </p>

                <hr>

                <strong>Level</strong>

                <p class="text-muted">
                
                  <span class="tag tag-info"><?php
                                            if($lv=="1")
                                            {
                                            echo "<label>Administrator</label>";
									
                                         
                                        } elseif($lv=="2")
                                       
                                       { 
                                       
                                        echo "<label>User</label>";
                                        } 
                                        ?></span>
      </span>
                  <span class="tag tag-warning"></span>
                  <span class="tag tag-primary"></span>

   
                </p>
 <hr>
            <strong>Log</strong>

                <p class="text-muted"><?php echo $log;?></p>
				
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
         

 

                 
               
              </table>
           
            <!-- /.card-body -->
          
          <!-- /.card -->
        </div>
        </div>
        </div>
        </div>
 
     
        
     
        <!-- /.col -->
    <!-- /.content-wrapper -->

  
                  
                         <script>
 var passL;
var passB;
var passK;

$(function () {
 $("#cekpass").on('click', function(){    
//passL  = $("#passL").val();
passB  = $("#passB").val();
passK   = $("#passK").val();
            
            $.ajax({
              method: "POST",
              url:    "post/gantipass.php",
              data: { "passB" : passB, "passK" : passK},
             }).done(function( data ) {
                //simpan();
                //var result = $.parseJSON(data);
               if(data == "1")
               {
                alert('Password baru harus di isi!');
               }
               else if(data == "2")
               {
                alert('Password Konfirmasi harus di isi!');
               }
               else if(data == "3")
               {
                alert('Password konfirmasi harus sama dengan password baru!');
               }
               else if(data == "4")
               {
                alert('Password harus minimal 6 digit!');
               }
               else
               {
                simpan();
                 $('#gantiPass').modal('hide');
              //$("#message").show(3000).html(str).addClass('success').hide(5000);
              }
          });
       
     }); 
     });
     
 </script>
  <!-- Modal -->

<div class="modal fade" id="gantiPass" tabindex="-1" role="dialog" aria-bs-labelledby="gantiPass"  aria-bs-hidden="true" >
                               <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-gradient-primary">
        <h5 class="modal-title" id="exampleModalLabel">Ganti Password</h5>
       
      </div>
      <div class="modal-body">
      <div class="container-fluid">
       <?php
   
        // mengambil data berdasarkan id
        // dan menampilkan data ke dalam form modal bootstrap
        $sql = mysqli_query($conn,"SELECT * FROM usera WHERE username = '$user'");
        $r = mysqli_fetch_array($sql);
        $newpass = $r['password'];
        	
        
?>



 
        	
            <input type="hidden" name="username" value="<?php echo $user; ?>">
            
			<div class="form-group">
			
                      
           
            <table border="0" width="100%" >
			
           <tr>
            <td colspan="3" align="center"><label>Password Baru Minimal  6 Digit</label></td>
            </tr>
            <tr>
            <td><label>Password Baru</label></td><td width="5%"><br><br></td><td>
                <input type="text" class="form-control" id="passB" name="passB" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="20">
            </td>
            </tr>
            <tr>
            <td><label>Konfirmasi Password Baru</label></td><td width="5%"><br><br></td><td>
                <input type="text" class="form-control" id="passK" name="passK" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength="20">
            </td>
            </tr>
            
			</table>
			
			
			
       
 

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger custom" data-dismiss="modal">Batal</button>
        <button class="btn btn-primary custom" type="button" id="cekpass" >Ganti Password</button>
      </div>
      </div>
  
  <!-- /.card-body -->
          
          <!-- /.card -->
        </div>
        </div>
        </div>


     
        <!-- /.col -->
    <!-- /.content-wrapper -->

  
                  
   
		  
		  
		  
