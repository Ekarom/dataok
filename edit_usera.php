<?php
include_once "cfg/konek.php";
include_once "cfg/secure.php";

global $sqlconn;
if (!$sqlconn) {
    include "cfg/konek.php";
}

$id = isset($_GET['urut']) ? mysqli_real_escape_string($sqlconn, $_GET['urut']) : '';
$sql = mysqli_query($sqlconn, "SELECT * FROM usera WHERE id = '$id'");
$r = mysqli_fetch_array($sql);

if (!$r) {
    header("Location: user");
    exit();
}

$stts = $r['status'];
$log = $r['level'];
?>

<!-- Konten Utama -->
<section class="content">
    <div class="row">

        <!-- Profile Card -->
        <div class="col-md-3">
            <div class="card card-navy card-outline">
                <div class="card-body box-profile">
                    <div class="text-center">
                        <?php if (!empty($r['poto']) && file_exists("images/" . $r['poto'])) { ?>
                            <img class="profile-user-img img-fluid img-circle" src="images/<?php echo $r['poto']; ?>"
                                alt="User profile picture" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php } else { ?>
                            <img class="profile-user-img img-fluid img-circle" src="images/default.png"
                                alt="Default profile picture" style="width: 150px; height: 150px; object-fit: cover;">
                        <?php } ?>
                    </div>

                    <h3 class="profile-username text-center mt-3">
                        <?php echo htmlspecialchars($r['nama']); ?>
                    </h3>

                    <div class="text-center">
                        <h5 class="badge bg-gradient-x-info text-white">
                            <?php echo htmlspecialchars($r['nik']); ?>
                        </h5>
                    </div>

                    <div class="text-center mt-1">
                        <?php
                        if ($r['level'] == "1") {
                            echo "<span class='badge bg-gradient-x-info text-white'>Administrator</span>";
                        } elseif ($r['level'] == "2") {
                            echo "<span class='badge bg-gradient-x-info text-white'>Staff</span>";
                        } else {
                            echo "<span class='badge bg-gradient-x-info text-white'>User</span>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Form Edit -->
        <div class="col-md-9">
            <div class="card shadow-sm border-0">
                <div class="card-header box-shadow-0 bg-gradient-x-info d-flex align-items-center justify-content-between">
                    <h5 class="card-title text-white mb-0">Form Edit Data User</h5>
                    <div class="card-tools">
                        <a href="management/usermanagement" class="btn btn-warning btn-sm rounded-pill px-3 shadow-sm">
                            <i class="fas fa-arrow-left mr-1"></i> Kembali
                        </a>
                    </div>
                </div>

                <div class="card-body">
                    <form action="usermanagement" method="post">
                        <input type="hidden" name="simpan" value="yes">
                        <input type="hidden" name="id" value="<?php echo $r['id']; ?>">

                        <!-- Level User -->
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">
                                <i class="fas fa-user-shield"></i> Level User
                            </label>
                            <div class="col-sm-8">
                                <select class="form-control" name="level" id="leveluser" style="width: 100%;">
                                    <option value="">Pilih Level</option>
                                    <option value="1" <?php if ($log == "1")
                                        echo "selected"; ?>>Admin</option>
                                    <option value="2" <?php if ($log == "2")
                                        echo "selected"; ?>>Staff</option>
                                    <option value="3" <?php if ($log == "3")
                                        echo "selected"; ?>>User</option>
                                </select>
                            </div>
                        </div>

                        <!-- Username -->
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">
                                <i class="fas fa-user"></i> Username
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="userid"
                                    value="<?php echo htmlspecialchars($r['userid']); ?>" placeholder="Username Login">
                            </div>
                        </div>

                        <!-- Nama Lengkap -->
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">
                                <i class="fas fa-id-card"></i> Nama Lengkap
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="nama"
                                    value="<?php echo htmlspecialchars($r['nama']); ?>" placeholder="Nama Lengkap">
                            </div>
                        </div>

                        <!-- NRK/NIKI/- -->
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">
                                <i class="fas fa-address-card"></i> NRK/NIKI/-
                            </label>
                            <div class="col-sm-8">
                                <input type="text" class="form-control" name="nik"
                                    value="<?php echo htmlspecialchars($r['nik']); ?>" placeholder="NRK/NIKI/Strip">
                            </div>
                        </div>

                        <!-- Status -->
                        <div class="form-group row">
                            <label class="col-sm-4 col-form-label">
                                <i class="fas fa-toggle-on"></i> Status
                            </label>
                            <div class="col-sm-8 d-flex align-items-center">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status" id="edit_status1"
                                        value="1" <?php if ($stts == "1")
                                            echo "checked"; ?>
                                        style="transform: scale(1.5); margin-right: 10px;">
                                    <label class="form-check-label" for="edit_status1">Aktif</label>
                                </div>
                                <div class="form-check form-check-inline ml-3">
                                    <input class="form-check-input" type="radio" name="status" id="edit_status0"
                                        value="0" <?php if ($stts == "0")
                                            echo "checked"; ?>
                                        style="transform: scale(1.5); margin-right: 10px;">
                                    <label class="form-check-label" for="edit_status0">Non Aktif</label>
                                </div>
                            </div>
                        </div>

                        <div class="card-footer text-right">

                            <button class="btn btn-primary btn-flat" type="submit">
                                <i class="fas fa-save"></i> Update
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
</section>

<script>
    $(function () {
        if (typeof $.fn.select2 !== 'undefined') {
            $("#leveluser").select2({
                placeholder: "Pilih Level User",
                allowClear: false,
                width: '100%',
                minimumResultsForSearch: 0
            });
        }
    });
</script>
