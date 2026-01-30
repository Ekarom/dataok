<?php
include "../cfg/konek.php";
include "../cfg/secure.php";

if (isset($_REQUEST['urut'])) {
    // Sanitize input to prevent SQL injection
    $id = mysqli_real_escape_string($sqlconn, $_REQUEST['urut']);
    
    // Get running text data by ID
    $sql = mysqli_query($sqlconn, "SELECT * FROM runtxt WHERE id = '$id'");
    $r = mysqli_fetch_array($sql);
    
    if ($r) {
?>
<style>
input.a { 
    text-align: center; 
}
</style>

<!-- MEMBUAT FORM -->
<script src="js/iinfoe.js"></script>

<div class="card-body table-responsive p-0">
    <div class="form-group">
        <label for="infop">Informasi WEB SKL</label>
        <input type="hidden" name="idp" id="idp" value="<?php echo htmlspecialchars($r['id']); ?>">
        <input type="text" class="form-control" id="infop" name="infop" 
               placeholder="Masukan Informasi" value="<?php echo htmlspecialchars($r['txt']); ?>" 
               maxlength="250" onkeyup="cekDatap()" required>
        <small class="form-text text-muted">Maksimal 250 Karakter.</small>
    </div>
</div>

<?php 
    } else {
        echo '<div class="alert alert-danger">Data tidak ditemukan!</div>';
    }
} else {
    echo '<div class="alert alert-warning">ID tidak valid!</div>';
}
?>
