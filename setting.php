<?php
include "cfg/konek.php";
include "cfg/secure.php";

if(isset($_REQUEST['stapel']))
{


		$sql = mysqli_query($sqlconn,"insert into tapel (tapel, smt, tahun, aktif) values  
		('$_REQUEST[tapel]', '$_REQUEST[smt], year(now()),'')");

	if ($sqlconn->error) {
    try {   
        throw new Exception("MySQL error $sqlconn->error <br> Query:<br> $sql", $sqlconn->errno);   
    } catch(Exception $e ) {
        echo "Error No: ".$e->getCode(). " - ". $e->getMessage() . "<br >";
        echo nl2br($e->getTraceAsString());
    }

}
echo "<script>alert('Tambah Tapel Berhasil'); window.location.href = '?modul=settings';</script>";
}

if(isset($_REQUEST['deltapel']))
{
$sql = mysqli_query($sqlconn,"delete from tapel where id = '$_REQUEST[id]'");
echo '<script>  alert("Data Tapel berhasil dihapus!");</script>';
}

?>
<link rel="stylesheet" href="icon/load/dist/three-dots.min.css">
<script>
    $(document).ready(function() {
        $('a[data-toggle="pill"]').on('show.bs.tab', function(e) {
            localStorage.setItem('activeTab', $(e.target).attr('href'));
        });
        var activeTab = localStorage.getItem('activeTab');
        if (activeTab) {
            $('#myTab a[href="' + activeTab + '"]').tab('show');
        }
    });
</script>

<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Setting data & server</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- DataTables -->
    <link rel="stylesheet" href="plugins/datatables-bs4/css/dataTables.bootstrap4.css">
    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">

                <div class="col-md-12">

                    <div class="card card-primary card-tabs">
                        <div class="card-header bg-gradient-primary p-0 pt-1">
                            <ul class="nav nav-tabs" id="myTab" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link" id="custom-tabs-five-normal-tab" data-toggle="pill" href="#tapels" role="tab" aria-controls="tapels" aria-selected="false">Tahun Pelajaran</a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content" id="custom-tabs-five-tabContent">
                                </div>

                                <!-- Tahun Pelajaran Tab -->
  <div class="tab-pane fade" id="tapels" role="tabpanel" aria-labelledby="tapels">
                <div class="row">
        <div class="col-4">
          <div class="card">
            <div class="card-header bg-menu-gradient">
                
           <a href='#itapel' id='custId' data-toggle='modal' data-id=''><button type="button" class="btn btn-info btn-sm"><i class="fa fa-disk"></i>Tambah Tapel</button></a></div>
                        <!-- /.panel-heading -->
                     
            <!-- /.card-header -->
            <div class="card-body">
              <table id="dataTables-example2" class="table table-bordered table-hover">
                <thead>
                <tr style="text-align:center">
                 <th width="1%">No</th>
                 <th width="50%">Tahun Pelajaran</th>
                 <th width="45%">Semester</th>
                
										
										<!-- <th width="2%">DELETE</th> !-->
										
                </tr>
                </thead>
                <tbody>
                
                <?php 
								$sqli = mysqli_query($sqlconn,"select * from tapel");
								
								
								$no=0;
								while($p = mysqli_fetch_array($sqli)){ 
								
								//$login = $s['level'];
								// $sqlg = mysqli_query($sqlconn,"select * from user inner join gmail on user.nis=gmail.nis where nis='$nis'");
                // $g = mysqli_fetch_array($sqlg);
                 //$gmail = $g['email'];
								$no++
								?>
                
                <tr>
                   <input type="text" id="id" value="<?php echo $p['id']; ?>" hidden>
                  <td align="center"><?php echo $no; ?></td>
                  <td style="text-align:center"><?php echo $p['tapel']; ?></td>
                  
                  <td style="text-align:center"><?php echo $p['smt']; ?></td>
               
                 <!--<td align="center">
                  							
                                        <a href="?modul=setting&deltapel=deltapel&id=<?php echo $p['id']; ?>">
                                     	<button type="button" class="btn btn-info btn-sm"><i class="fa fa-trash"></i></button></a>
									</td>	!-->
                 </tr>



                                                      
                                        
                                <?php } ?>         
                
                            <tfoot>
               
                </tfoot>
              </table>
           
            <!-- /.card-body -->
          </div>
            </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </div>
  </div>
</div>
<!-- /.content-wrapper -->
   </div>
            </div>
          <!-- /.card -->
        </div>
        <!-- /.col -->
      </div>
      <!-- /.row -->
    </div>
  </div>
</div>
<!-- /.content-wrapper -->
<div class="modal fade" id="itapel" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Tambah Tahun Pelajaran</h4>
                </div>
                <div class="modal-body">
        <!-- MEMBUAT FORM -->
        <form action="?modul=setting&stapel=set" method="post">
		<div class="form-group">
    <label for="exampleInputEmail1">Tahun Pelajaran</label>
    <input type="text" class="form-control" id="tapel" name="tapel" aria-describedby="emailHelp" placeholder="Masukan Tahun contoh : 2023/2024" maxlength="250">
    <small id="emailHelp" class="form-text text-muted">Maksimal 250 Karakter.</small>
    
    <label for="exampleInputEmail1">Semester</label>
    <input type="text" class="form-control" id="smt" name="smt" aria-describedby="emailHelp" placeholder="Masukan Angka 1 atau 2" maxlength="250">
    <small id="emailHelp" class="form-text text-muted">Maksimal 250 Karakter.</small>
  </div>
            
			<button type="button" class="btn btn-default" data-dismiss="modal">Tutup</button>
			<button class="btn btn-primary" type="submit">Simpan</button>
        </form>
                
                    <div class="fetched-data2"></div>
                </div>
                <div class="modal-footer">
                    
                </div>
            </div>
        </div>
    </div>    


<!-- Control Sidebar -->
<aside class="control-sidebar control-sidebar-dark">
    <!-- Control sidebar content goes here -->
</aside>

<!-- DataTables Scripts -->
<script src="plugins/datatables/jquery.dataTables.js"></script>
<script src="plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script>
<script>
        // Make modal draggable
        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });
</script>