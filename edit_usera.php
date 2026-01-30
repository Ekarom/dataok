<?php
include "cfg/konek.php";
include "cfg/secure.php";

if($_REQUEST['urut']) {
    $id = $_POST['urut'];
    
    // Mengambil data berdasarkan id
    $sql = mysqli_query($sqlconn, "SELECT * FROM usera WHERE id = '$id'");
    $r = mysqli_fetch_array($sql);
    $stts = $r['status'];
    $log = $r['level'];
?>

<form action="?modul=User" method="post">
    <input type="hidden" name="simpan" value="yes">
    <input type="hidden" name="id" value="<?php echo $r['id']; ?>">
    <div class="card card-outline card-primary shadow-sm">
        <div class="card-header text-center bg-primary text-white">
            <h4 class="card-title w-100 mb-0">Edit Data User</h4>
        </div>
    </div>
    <!-- Profile Section -->
    <div class="card-body box-profile">
        <div id="upload2" class="text-center">
            <?php 
            if(!empty($r['poto']) && file_exists("images/" . $r['poto'])) {
            ?>
                <img class="profile-user-img img-fluid img-circle" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #fff;" src="images/<?php echo $r['poto']; ?>" alt="User profile picture">
            <?php 
            } else {
            ?>
                <img class="profile-user-img img-fluid img-circle" style="width: 120px; height: 120px; object-fit: cover; border: 3px solid #fff;" src="images/default.png" alt="Default profile picture">
            <?php 
            } 
            ?>
        </div>
        <span id="status2"></span>

        <h3 class="profile-username text-center"><?php echo $r['nama']; ?></h3>
        
        <p class="text-muted text-center">
            <?php
            if ($r['level'] == "1") {
                echo "<span class='badge bg-menu-gradient'>Administrator</span>";
            } elseif ($r['level'] == "2") {
                echo "<span class='badge bg-menu-gradient'>Staff</span>";
            } else {
                echo "<span class='badge bg-menu-gradient'>User</span>";
            }
            ?>
        </p>
    </div>
    
    <!-- Form Fields -->
    <div class="container-fluid">
        <!-- Row 1: Level User & Username -->
        <div class="row">
            <!-- Level User -->
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fas fa-user-shield"></i> Level User</label>
                    <select class="form-control select2-edit-user" name="level" style="width: 100%;">
                        <option value="">Pilih Level</option>
                        <option value="1" <?php if ($log == "1") { echo "selected"; } ?>>Admin</option>
                        <option value="2" <?php if ($log == "2") { echo "selected"; } ?>>Staff</option>
                        <option value="3" <?php if ($log == "3") { echo "selected"; } ?>>User</option>
                    </select>
                </div>
            </div>
            
            <!-- Username -->
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fas fa-user"></i> Username</label>
                    <input type="text" class="form-control" name="userid" value="<?php echo $r['userid']; ?>" placeholder="Username Login">
                </div>
            </div>
        </div>

        <!-- Row 2: Nama Lengkap & NRK/NIKI -->
        <div class="row">
            <!-- Nama Lengkap -->
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fas fa-id-card"></i> Nama Lengkap</label>
                    <input type="text" class="form-control" name="nama" value="<?php echo $r['nama']; ?>" placeholder="Nama Lengkap">
                </div>
            </div>
            
            <!-- NRK/NIKI/- -->
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fas fa-address-card"></i> NRK/NIKI/-</label>
                    <input type="text" class="form-control" name="nik" value="<?php echo $r['nik']; ?>" placeholder="NRK/NIKI/Strip">
                </div>
            </div>
        </div>

        <!-- Row 3: Status -->
        <div class="row">
            <!-- Status -->
            <div class="col-md-6">
                <div class="form-group">
                    <label><i class="fas fa-toggle-on"></i> Status</label>
                    <div class="mt-2">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="edit_status1" value="1" <?php if ($stts == "1") { echo "checked"; } ?> style="transform: scale(1.5); margin-right: 10px;">
                            <label class="form-check-label" for="edit_status1">Aktif</label>
                        </div>
                        <div class="form-check form-check-inline ml-3">
                            <input class="form-check-input" type="radio" name="status" id="edit_status0" value="0" <?php if ($stts == "0") { echo "checked"; } ?> style="transform: scale(1.5); margin-right: 10px;">
                            <label class="form-check-label" for="edit_status0">Non Aktif</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Form Actions -->
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary btn-flat" data-dismiss="modal"><i class="fas fa-times"></i> Batal</button>
        <button class="btn btn-primary btn-flat" type="submit"><i class="fas fa-save"></i> Update</button>
    </div>
</form>
<script>
    $(function () {
        $('.select2-edit-user').select2({
            theme: 'bootstrap4',
            dropdownParent: $('#myEdit')
        })
    });
</script>

<?php 
} 
?>