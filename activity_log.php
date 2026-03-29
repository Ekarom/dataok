<?php
// usera_log_view.php
// Pastikan konek.php dan secure.php sudah terinclude di index.php

if ($lv != "1") {
    echo "<div class='alert alert-danger'>Akses ditolak! Halaman ini hanya untuk Administrator.</div>";
    exit;
}

// Logic for Filters
$where_clauses = [];
$search = isset($_GET['search_log']) ? mysqli_real_escape_string($sqlconn, $_GET['search_log']) : '';
$start_date = isset($_GET['start_date']) ? mysqli_real_escape_string($sqlconn, $_GET['start_date']) : '';
$end_date = isset($_GET['end_date']) ? mysqli_real_escape_string($sqlconn, $_GET['end_date']) : '';

if (!empty($search)) {
    $where_clauses[] = "(user LIKE '%$search%' OR nama LIKE '%$search%' OR info LIKE '%$search%' OR action LIKE '%$search%')";
}
if (!empty($start_date)) {
    $where_clauses[] = "DATE(waktu) >= '$start_date'";
}
if (!empty($end_date)) {
    $where_clauses[] = "DATE(waktu) <= '$end_date'";
}

$where_sql = count($where_clauses) > 0 ? "WHERE " . implode(" AND ", $where_clauses) : "";
?>


    <!-- Filter Section -->
    <section class="content px-3">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header box-shadow-0 bg-gradient-x-info">
                <h5 class="card-title text-white">Filter Log</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="index.php" class="row align-items-end gx-3 gy-2">
                    <input type="hidden" name="modul" value="Log-Aktivitas">
                    
                    <div class="col-md-3">
                        <input type="text" name="search_log" class="form-control form-control-sm" placeholder="Search User/Action..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <input type="date" name="start_date" class="form-control form-control-sm" value="<?php echo $start_date; ?>">
                    </div>
                    
                    <div class="col-md-3">
                        <input type="date" name="end_date" class="form-control form-control-sm" value="<?php echo $end_date; ?>">
                    </div>
                    
                    <div class="col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-4 me-2">Filter</button>
                        <a href="?modul=Log-Aktivitas" class="btn btn-outline-secondary btn-sm px-3 text-muted">Reset</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Section -->
        <div class="card shadow-sm border-0">
            <div class="card-header box-shadow-0 bg-gradient-x-info">
                <h5 class="card-title text-white">Activity Logs</h5>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-striped projects">
                    <thead>
                        <tr>
                            <th>Time</th>
                                <th>User</th>
                                <th>Action</th>
                                <th>Details</th>
                                <th>URL</th>
                                <th>Duration</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $db_log = mysqli_connect("localhost", "arsip", "BHmD8VlJELecRqw4S5OAYXDpc", "dnet_ad2025");
                            if (!$db_log) {
                                echo "<tr><td colspan='7' class='text-center py-5 text-danger'>Gagal terhubung ke database log: " . mysqli_connect_error() . "</td></tr>";
                            } else {
                                $sql_log = mysqli_query($db_log, "SELECT * FROM activity $where_sql ORDER BY timestamp DESC LIMIT 1000");
                            
                            if (!$sql_log || mysqli_num_rows($sql_log) == 0) {
                                echo "<tr><td colspan='7' class='text-muted'>No activity logs found.</td></tr>";
                            } else {
                                while ($l = mysqli_fetch_array($sql_log)) {
                                    $action = strtoupper($l['action'] ?? '');
                                    $badge_class = 'bg-info';
                                    if ($action =='USER LOGGED IN' || $action == 'LOGIN') $badge_class = 'bg-info';
                                    elseif ($action == 'DELETE') $badge_class = 'bg-danger';
                                    elseif ($action == 'EDIT' || $action == 'UPDATE') $badge_class = 'bg-primary';
                                    elseif ($action == 'ADD') $badge_class = 'bg-info';
                                    
                                        // Format Time
                                    $display_time = date("d-M-Y H:i:s", strtotime($l['timestamp']));
                                    ?>
                                    <tr class="border-bottom">
                                        <td class="px-4 py-3 font-weight-normal text-dark small"><?php echo $display_time; ?></td>
                                        <td class="px-4 py-3 small text-dark">
                                            <div style="font-weight: 700; color: #1e3c72;"><?php echo htmlspecialchars($l['user_name'] ?? ''); ?></div>
                                            <div style="color: #4a69bd; font-size: 0.7rem; font-weight: 500;"><?php echo htmlspecialchars($l['user_id'] ?? ''); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $badge_class; ?> px-2 py-1" style="font-size: 0.65rem; border-radius: 4px;"><?php echo $action; ?></span>
                                        </td>
                                        <td class="px-4 py-3 small text-muted"><?php echo htmlspecialchars($l['details'] ?? ''); ?></td>
                                        <td class="px-4 py-3 small text-info"><?php echo htmlspecialchars($l['url'] ?? ''); ?></td>
                                        <td>
                                            <?php 
                                            $val_dur = $l['duration'] ?? '';
                                            echo ($val_dur !== '') ? htmlspecialchars($val_dur) : '-'; 
                                            ?>
                                        </td>
                                        <td class="px-4 py-3 small text-dark"><?php echo htmlspecialchars($l['ip'] ?? ''); ?></td>
                                    </tr>
                                    <?php
                                }
                            }
                        }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php if ($sql_log && mysqli_num_rows($sql_log) > 0): ?>
            <div class="card-footer bg-white text-muted small py-3 border-top">
                <i class="fas fa-info-circle me-1"></i> Showing the most recent activities matching your criteria.
            </div>
            <?php endif; ?>
        </div>
    </section>
</div>

<style>
    /* Custom spacing and styles to match the look of the reference image */
    #logTableRef thead th {
        font-weight: 500;
        font-size: 0.85rem;
        letter-spacing: 0.5px;
    }
    #logTableRef tbody tr:nth-of-type(odd) {
        background-color: #fcfcfc;
    }
    #logTableRef tbody tr:hover {
        background-color: #f8f9fa !important;
    }
    .badge {
        font-weight: 600;
    }
    .form-control:focus {
        border-color: #3498db;
        box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
    }
    .btn-primary {
        background-color: #007bff;
        border-color: #007bff;
    }
    .btn-outline-secondary {
        border-color: #dee2e6;
    }
    .gap-2 {
        gap: 0.5rem !important;
    }
    .gx-3 {
        margin-right: -0.75rem;
        margin-left: -0.75rem;
    }
    .gx-3 > [class*="col-"] {
        padding-right: 0.75rem;
        padding-left: 0.75rem;
    }
</style>

<!-- DataTables setup (optional, but keep it for responsiveness) -->
<script>
    $(document).ready(function() {
        // We handle sorting and filtering server-side via SQL or basic JS
        // but keeping DataTable for basic responsive structure if needed.
        // Actually, matching the ref image exactly might be easier with raw table + basic CSS.
    });
</script>
