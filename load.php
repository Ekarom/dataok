<?php
include "cfg/secure.php";
?>
<style>
    .small-box {
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .card {
        border-radius: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
</style>

<!-- Content Wrapper. Contains page content -->

<div class="content-wrapper">

    <!-- Content Header (Page header) -->

    <div class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">

                    <h1>Dashboard</h1>

                </div><!-- /.col -->

                <div class="col-sm-6">

                    <ol class="breadcrumb float-sm-right">

                        <li class="breadcrumb-item"><a href="#">Home</a></li>

                        <li class="breadcrumb-item active">Dashboard</li>

                    </ol>

                </div><!-- /.col -->

            </div><!-- /.row -->

        </div><!-- /.container-fluid -->

    </div>

    <!-- /.content-header -->



    <!-- Main content -->

    <section class="content">

        <!-- Small cards (Stat card) -->

        <div class="row">

            <!-- Box 1: Prestasi -->

            <div class="col-lg-2 col-6">

                <div class="small-box bg-1">

                    <div class="inner">
                    <?php $query = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM prestasi");
                    $row = mysqli_fetch_assoc($query);
                    $prestasi= $row['total'];
                    ?>
                        <h3><?php echo $prestasi; ?></h3>

                        <p>Laporan Prestasi</p>

                    </div>

                    <div class="icon">

                        <i class="ion ion-trophy"></i>

                    </div>

                    <a href="?modul=press" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>

                </div>

            </div>



            <!-- Box 2: Usulan -->

            <div class="col-lg-2 col-6">

                <div class="small-box bg-2">

                    <div class="inner">
                    <?php $query = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM usulan");
                    $row = mysqli_fetch_assoc($query);
                    $usulan= $row['total'];
                    ?>
                        <h3><?php echo $usulan; ?></h3>
                        <p>Laporan Usulan</p>

                    </div>

                    <div class="icon">

                        <i class="ion ion-android-mail"></i>

                    </div>

                    <a href="?modul=usulan" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>

                </div>

            </div>



            <!-- Box 3: Legalisir -->

            <div class="col-lg-2 col-6">

                <div class="small-box bg-3">

                    <div class="inner">
                    <?php $query = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM legalisir");
                    $row = mysqli_fetch_assoc($query);
                    $legalisir= $row['total'];
                    ?>
                        <h3><?php echo $legalisir; ?></h3>
                        <p>Laporan Legalisir</p>

                    </div>

                    <div class="icon">

                        <i class="ion ion-archive"></i>

                    </div>

                    <a href="?modul=legalisir" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>

                </div>

            </div>



            <!-- Box 4: Siswa -->

            <div class="col-lg-2 col-6">

                <div class="small-box bg-4">

                    <div class="inner">
                    <?php $query = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM siswa");
                    $row = mysqli_fetch_assoc($query);
                    $siswa= $row['total'];
                    ?>
                        <h3><?php echo $siswa; ?></h3>
                        <p>Manajemen PD</p>

                    </div>

                    <div class="icon">

                        <i class="ion ion-person-stalker"></i>

                    </div>

                    <a href="?modul=siswa" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>

                </div>

            </div>



            <div class="col-lg-2 col-6">

                <div class="small-box bg-5">

                    <div class="inner">
                    <?php $query = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM usera");
                    $row = mysqli_fetch_assoc($query);
                    $user= $row['total'];
                    ?>
                        <h3><?php echo $user; ?></h3>

                        <p>Manajemen User</p>

                    </div>

                    <div class="icon">

                        <i class="ion ion-person-stalker"></i>

                    </div>

                    <a href="?modul=user" class="small-box-footer">More info <i class="fa fa-arrow-circle-right"></i></a>

                </div>

            </div>

        </div>

        <!-- /.row -->



        <!-- Main row -->

        <div class="row">

            <!-- Left col -->

            <section class="col-lg-5 connectedSortable">

                <!-- Welcome Card -->

                    <div class="card">

                    <div class="card-header bg-menu-gradient">
                    <div class="card-tools">
                   <button type="button" class="btn btn-tool" data-card-widget="collapse">

                                <i class="fas fa-minus"></i>

                            </button>

                        </div>
                        <h3 class="card-title">

                            <i class="fas fa-chart-pie mr-1"></i>

                            Selamat Datang, <b><?php echo $nama; ?></b>

                        </h3>

                    </div>

                    <div class="card-body border">

                        <div class="card">

                            <div class="card-header border">

                                <b class="card-title"><i class="fas fa-info-circle mr-1"></i> &nbsp;Informasi Terbaru</b>

                            </div>
                            
                            <div class="card-body border">

                                Harap Teliti Sebelum Menginput Prestasi Siswa Terima Kasih

                            </div>

                        </div>

                    </div>

                </div>

                <!-- /.card -->



                <!-- DIRECT CHAT -->

                <div class="card direct-chat direct-chat">

                    <div class="card-header bg-menu-gradient">

                        <h3 class="card-title">

                            <i class="fas fa-history mr-1"></i>

                            History Log

                        </h3>



                        <div class="card-tools">

                            <button type="button" class="btn btn-tool" data-card-widget="collapse">

                                <i class="fas fa-minus"></i>

                            </button>

                        </div>

                    </div>

                    <!-- /.card-header -->

                    <div class="card-body">

                        <!-- Conversations are loaded here -->

                        <div class="direct-chat-messages">

                            <?php

                            // Check if log variables exist (from secure.php)

                            if (isset($log1) && isset($log5)) {

                                $i = isset($log5['n1']) ? $log5['n1'] : 0;

                                

                                while ($log2 = mysqli_fetch_array($log1)) {

                            ?>

                                    <div class="direct-chat-msg">

                                        <div class="direct-chat-infos clearfix">

                                            <span class="direct-chat-name float-left"><?php echo htmlspecialchars($log2['nama']); ?></span>

                                            <span class="direct-chat-timestamp float-right"><?php echo $log2['waktu']; ?></span>

                                        </div>

                                        <img class="direct-chat-img" src="images/info.png" alt="message user image">

                                        <div class="direct-chat-text">

                                            <?php echo htmlspecialchars($log2['info']); ?>

                                        </div>

                                    </div>

                            <?php

                                    $i--;

                                }

                            } else {

                                echo '<div class="p-3 text-center text-muted">No history logs available</div>';

                            }

                            ?>

                        </div>

                    </div>

                </div>

                <!-- /.direct-chat -->



            </section>

            <!-- /.Left col -->

        </div>

        <!-- /.row -->



    </section>

    <!-- /.content -->

</div>

<!-- /.content-wrapper -->



<?php

// Close connection if appropriate, usually footer handles this but previous code had it here.

// Keeping it to minimize side effects, though generally better in footer.

// mysqli_close($sqlconn); 

?>