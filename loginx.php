<?php
// Database connection configuration
$server = "localhost";
$username = "arsip";
$password = "BHmD8VlJELecRqw4S5OAYXDpc";
$database = "";

// Connect to server for database listing
$db_conn = new mysqli($server, $username, $password, $database);

// Self-healing removed as it conflicts with explicit period selection and is handled better in proses./

$db_list = [];
if ($db_conn->connect_error) {
  $db_list[] = ["name" => "Koneksi Gagal", "display" => "Koneksi DB Gagal"];
} else {
  // Ambil daftar database
  $result = $db_conn->query("SHOW DATABASES");
  if ($result) {
    while ($row = $result->fetch_array(MYSQLI_NUM)) {
      $db_name = $row[0];
      // Filter database yang berawalan 'pnet_pd' atau 'dnet_ad' (Maintain compatibility)
      if (strpos($db_name, 'dnet_ad') === 0) {
        // Display adjustment
        if (strpos($db_name, 'dnet_ad') === 0) {
            $display_name = substr($db_name, 7);
            if (substr($display_name, 0, 1) === '_') $display_name = substr($display_name, 1);
        }

        if (empty($display_name)) {
          $display_name = $db_name;
        } else {
             if (is_numeric($display_name) && strlen($display_name) == 4) {
                 $display_name = $display_name . "/" . ($display_name + 1);
             }
        }
        $db_list[] = ["name" => $db_name, "display" => $display_name];
      }
    }
  }
  $db_conn->close();
}

// Ensure Connection is available for profile info (logo, etc)
require_once "cfg/konek.php";
require_once "cfg/recaptcha_config.php";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Arsip Data</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    <!-- Icons -->
    <link rel="icon" type="image/png" href="images/<?php echo $sklogo; ?>">
    <link rel="stylesheet" href="plugins/fontawesome-free/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="plugins/iconic/css/material-design-iconic-font.min.css">
    
    <!-- Styles -->
    <link rel="stylesheet" type="text/css" href="plugins/css/util.css">
    <link rel="stylesheet" type="text/css" href="plugins/css/main.css">
    
    <!-- Scripts -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
        <style>
        /* The container must be positioned relative: */
        .custom-select {
            position: relative;
            font-family: Arial;
        }

        .custom-select select {
            display: none; /*hide original SELECT element: */
        }

        .select-selected {
            background-color: rgba(0,0,0,0.5);
        }

        /* Style the arrow inside the select element: */
        .select-selected:after {
            position: absolute;
            content: "";
            top: 14px;
            right: 10px;
            width: 0;
            height: 0;
            border: 6px solid transparent;
            border-color: #fff transparent transparent transparent;
        }

        /* Point the arrow upwards when the select box is open (active): */
        .select-selected.select-arrow-active:after {
            border-color: transparent transparent #fff transparent;
            top: 7px;
        }

        /* style the items (options), including the selected item: */
        .select-items div, .select-selected {
            color: #ffffff;
            padding: 8px 16px;
            border: 1px solid transparent;
            border-color: transparent transparent rgba(0, 0, 0, 0.1) transparent;
            cursor: pointer;
        }

        /* Style items (options): */
        .select-items {
            position: absolute;
            background-color: rgba(0, 0, 0, 0.8);
            top: 100%;
            left: 0;
            right: 0;
            z-index: 99;
        }

        /* Hide the items when the select box is closed: */
        .select-hide {
            display: none;
        }

        .select-items div:hover, .same-as-selected {
            background-color: rgba(66, 135, 245, 0.8);
        }

        select option {
            margin: 40px;
            background: rgba(0, 0, 0, 0.8);
            color: #fff;
            text-shadow: 0 1px 0 rgba(0, 0, 0, 0.4);
        }

        .eye-icon {
            position: absolute;
            right: 14px;
            top: 5px;
            transform: translateY(50%);
            cursor: pointer;
            color: #ff0000;
            z-index: 1;
            font-size: 16px;
        }

        .eye-icon:hover {
            color: #0010ff;
        }

        .form-group {
            position: relative;
        }

        input[type="password"], input[type="text"] {
            padding-right: 30px;
        }

        /* Animated gradient border for error messages */
        .error-gradient-border {
            position: relative;
            background-color: transparent;
            padding: 17px;
            border-radius: 10px;
            margin-top: 10px;
            color: #fc0505ff;
            font-size: 14px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .error-gradient-border strong {
            color: #ff0000;
            font-weight: bold;
        }

        .error-gradient-border::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, #ff0000, #ff6600, #000000ff, #ff0000, #ff6600);
            background-size: 400% 400%;
            border-radius: 10px;
            z-index: -2;
            animation: gradientMove 5s ease infinite;
        }

        .error-gradient-border::after {
            content: '';
            position: absolute;
            top: 2px;
            left: 2px;
            right: 2px;
            bottom: 2px;
            background-color: rgba(0, 0, 0, 0.9);
            border-radius: 8px;
            z-index: -1;
        }

        .error-gradient-border {
            z-index: 1;
        } 
        .container-login100 {
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
        }

        @keyframes gradientMove {
            0% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
            100% {
                background-position: 0% 50%;
            }
        }

    </style>
</head>
<body>
    <div class="container-login100" style="background-image: url('images/<?php echo $skback; ?>');">
        <div class="wrap-login100">
            <section id="region-main" class="col-12 h-100" aria-label="Content">
                <form method="post" action="proses./">
                    <span class="login100-form-logo">
                        <i class="zmdi landscape"><img src="images/<?php echo $sklogo; ?>" width="120" height="110"/></i>
                    </span>

                    <span class="login100-form-title p-b-34 p-t-27">
                        Arsip Data<br>
                        <?php echo $namasek; ?>
                    </span>

                    <div class="wrap-input100 validate-input" data-validate="Masukan Username">
                        <input class="input100" type="text" id="skradm" name="skradm" placeholder="Username">
                        <span class="focus-input100" data-placeholder="&#xf207;"></span>
                    </div>

                    <div class="wrap-input100 validate-input" data-validate="Masukan Password">
                        <input class="input100" type="password" id="skrpass" name="skrpass" placeholder="Password">
                        <span class="focus-input100" data-placeholder="&#xf191;"></span>
                        <i class="fa fa-eye-slash eye-icon" id="toggle-password"></i>
                    </div>
                    <!-- Pilihan Database -->
                    <div class="wrap-input100 validate-input" data-validate="Database Harus Dipilih">
                        <select id="database" name="database_name" class="input100 form-control selectpicker" data-live-search="true" required>
                            <option value="">Pilih Tahun Pelajaran</option>
                            <?php foreach ($db_list as $db_item): ?>
                                <option value="<?php echo htmlspecialchars($db_item['name']); ?>">
                                    <?php echo htmlspecialchars($db_item['display']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Pilihan Semester -->
                    <div class="wrap-input100 validate-input" data-validate="Semester Harus Dipilih">
                        <select id="semester" name="semester" class="input100 form-control" required style="border: none; background: transparent; color: white;">
                            <option value="" style="color: black;">Pilih Semester:</option>
                            <option value="1" style="color: black;">Semester 1 (Ganjil)</option>
                            <option value="2" style="color: black;">Semester 2 (Genap)</option>
                        </select>
                        <span class="focus-input100" data-placeholder="&#xf271;"></span>
                    </div>
                  
                    <div class="g-recaptcha" data-sitekey="<?php echo $recaptcha_site_key; ?>"></div>
                    <br>
                    <div class="container-login100-form-btn">
                        <span class="text-center p-t-90 txt1"></span>
                        <button class="login100-form-btn">
                            Login
                        </button>
                    </div>
                </form>
            </section>

       <?php

// Pastikan tidak ada spasi sebelum tag php

        if(isset($_GET['salah'])){
            // --- KASUS 1: user diblokir (salah=3) ---
            if($_GET['salah'] == 3){
                // Ambil waktu tunggu dari URL parameter 't'
                $remaining_seconds = isset($_GET['wait']) ? (int)$_GET['wait'] : (isset($_GET['t']) ? (int)$_GET['t'] : 0);
                $minutes = floor($remaining_seconds / 60);
                $seconds = $remaining_seconds % 60;
                $sPadded = $seconds < 10 ? '0' . $seconds : $seconds;

                echo "<div class='error-gradient-border' style='color: red; padding: 10px; border: 1px solid red; background: #ffe6e6; margin-bottom: 10px;'>
                        <strong>AKSES DIBLOKIR!</strong><br>
                        Anda salah memasukkan password sebanyak 3x.<br>
                        Silahkan tunggu: <span id='countdown' style='font-weight:bold; font-size:1.2em;'>$minutes menit $sPadded detik</span>
                      </div>";
                // Javascript untuk hitung mundur real-time
                echo "<script>
                    var timeLeft = $remaining_seconds;
                    var elem = document.getElementById('countdown');
                    var timerId = setInterval(function() {
                        if (timeLeft <= 0) {
                            clearInterval(timerId);
                            elem.innerHTML = '0 menit 00 detik';
                            window.location.href = './';
                        } else {
                            timeLeft--;
                            var m = Math.floor(timeLeft / 60);
                            var s = timeLeft % 60;
                            var sPadded = s < 10 ? '0' + s : s;
                            elem.innerHTML = m + ' menit ' + sPadded + ' detik';
                        }
                    }, 1000);
                </script>";
            }
            // --- KASUS 2: Error umum/akses langsung (salah=2) ---
            elseif($_GET['salah'] == 2){
                echo "<div class='error-gradient-border' style='color: red; padding: 10px;'><strong>Error!</strong> Akses tidak valid atau koneksi gagal.</div>";
            }
            // --- KASUS 3: Password salah, tapi belum diblokir (salah=1) ---
            elseif($_GET['salah'] == 1){
                // Ambil sisa percobaan dari URL parameter 'sisa'
                $remaining = isset($_GET['attempts']) ? (int)$_GET['attempts'] : (isset($_GET['sisa']) ? (int)$_GET['sisa'] : 0);
                echo "<div class='error-gradient-border' style='color: orange; padding: 10px; border: 1px solid orange; background: #fff8e1; margin-bottom: 10px;'>
                        <strong>LOGIN GAGAL!</strong><br>
                        Username atau Password salah.<br>
                        Sisa percobaan: <strong>$remaining kali</strong> lagi sebelum diblokir selama 5 menit.
                      </div>";
            }
            // --- KASUS 4: reCAPTCHA tidak dicentang (salah=4) ---
            elseif($_GET['salah'] == 4){
                echo "<div class='error-gradient-border' style='color: red; padding: 10px; border: 1px solid red; background: #ffe6e6; margin-bottom: 10px;'>
                        <strong>VERIFIKASI DIPERLUKAN!</strong><br>
                        Silakan centang kotak 'I'm not a robot' untuk melanjutkan.
                      </div>";
            }
            // --- KASUS 5: reCAPTCHA verifikasi gagal (salah=5) ---
            elseif($_GET['salah'] == 5){
                echo "<div class='error-gradient-border' style='color: red; padding: 10px; border: 1px solid red; background: #ffe6e6; margin-bottom: 10px;'>
                        <strong>VERIFIKASI GAGAL!</strong><br>
                        Verifikasi reCAPTCHA gagal. Silakan coba lagi.
                      </div>";
            }
            // --- KASUS 6: reCAPTCHA connection error (salah=6) ---
            elseif($_GET['salah'] == 6){
                echo "<div class='error-gradient-border' style='color: red; padding: 10px; border: 1px solid red; background: #ffe6e6; margin-bottom: 10px;'>
                        <strong>KONEKSI GAGAL!</strong><br>
                        Tidak dapat menghubungi server verifikasi. Silakan coba lagi.
                      </div>";
            }
            // --- KASUS 7: Barcode belum discan (salah=7) ---
            elseif($_GET['salah'] == 7){
                echo "<div class='error-gradient-border' style='color: red; padding: 10px; border: 1px solid red; background: #ffe6e6; margin-bottom: 10px;'>
                        <strong>SCAN BARCODE DIPERLUKAN!</strong><br>
                        Silakan scan barcode terlebih dahulu sebelum login.
                      </div>";
            }
            // --- KASUS 8: Database tidak dipilih (salah=8) ---
            elseif($_GET['salah'] == 8){
                echo "<div class='error-gradient-border' style='color: orange; padding: 10px; border: 1px solid orange; background: #fff8e1; margin-bottom: 10px;'>
                        <strong>DATABASE BELUM DIPILIH!</strong><br>
                        Silakan pilih tahun database terlebih dahulu.
                      </div>";
            }
           
        }


?>
        <div class="text-center p-t-90 txt1">
             <?php echo $namasek; ?><br>
             <span>
             S.A.D Versi <?php echo $ver; ?>
             </span><br>
            Copyright &copy; <?php echo date("Y"); ?>
        </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var togglePassword = document.getElementById('toggle-password');
            var passwordField = document.getElementById('skrpass');

            if (togglePassword && passwordField) {
                togglePassword.addEventListener('click', function() {
                    var type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    
                    if (type === 'text') {
                        this.classList.remove('fa-eye-slash');
                        this.classList.add('fa-eye');
                    } else {
                        this.classList.remove('fa-eye');                   this.classList.add('fa-eye-slash');
                    }
                });
            }

           // AJAX for Dynamic Semester
           // Menggunakan jQuery yang sudah diload (pastikan jQuery diload, jika belum, gunakan Vanilla atau load jQuery)
           // Cek diatas, ./ belum meload jQuery. Kita tambahkan CDN jQuery.
        });
    </script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    $(document).ready(function() {
      // AJAX for Dynamic Semester
      $('#database').change(function(){
          var dbName = $(this).val();
          var semesterSelect = $('#semester');
          
          // Clear current options
          semesterSelect.empty();
          semesterSelect.append('<option value="">Loading...</option>');
          
          if(dbName){
              $.ajax({
                  type: 'POST',
                  url: 'get_semester.php',
                  data: {database_name: dbName},
                  dataType: 'json',
                  success: function(response){
                      semesterSelect.empty();
                      semesterSelect.append('<option value="">Pilih Semester:</option>');
                      
                      if(response.error){
                          console.error(response.error);
                           // Fallback or show error
                           semesterSelect.append('<option value="">Error loading semesters</option>');
                      } else if(response.length > 0){
                          $.each(response, function(index, value){
                              var text = (value == 1) ? "Semester 1 (Ganjil)" : "Semester 2 (Genap)";
                              semesterSelect.append('<option value="'+value+'" style="color: black;">'+text+'</option>');
                          });
                      } else {
                           semesterSelect.append('<option value="">Data Semester Kosong</option>');
                      }
                  },
                  error: function(xhr, status, error){
                      console.error("AJAX Error: " + error);
                      semesterSelect.empty();
                      semesterSelect.append('<option value="">Gagal memuat semester</option>');
                  }
              });
          } else {
              semesterSelect.empty();
              semesterSelect.append('<option value="">Pilih Semester:</option>');
              semesterSelect.append('<option value="1" style="color: black;">Semester 1 (Ganjil)</option>');
              semesterSelect.append('<option value="2" style="color: black;">Semester 2 (Genap)</option>');
          }
      });
    });
    </script>

</body>
</html>
