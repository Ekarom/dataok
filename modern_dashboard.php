<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include "cfg/secure.php";

// Fetch some real stats to populate cards if available
$q_pres = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM prestasi");
$total_prestasi = ($q_pres) ? mysqli_fetch_assoc($q_pres)['total'] : 0;

$q_leg = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM legalisir");
$total_legalisir = ($q_leg) ? mysqli_fetch_assoc($q_leg)['total'] : 0;

$q_siswa = mysqli_query($sqlconn, "SELECT COUNT(*) as total FROM siswa");
$total_siswa = ($q_siswa) ? mysqli_fetch_assoc($q_siswa)['total'] : 0;
?>

<div class="container-fluid py-4">
    <!-- Secondary Dashboard Navigation -->
    <div class="secondary-nav-wrapper mb-4">
        <a href="dashboard" class="secondary-nav-item active"><i class="fas fa-home"></i> Dashboard</a>
        <a href="datasiswa" class="secondary-nav-item"><i class="fas fa-laptop"></i> Templates</a>
        <a href="#" class="secondary-nav-item"><i class="fas fa-mobile-alt"></i> Apps</a>
        <a href="#" class="secondary-nav-item"><i class="fas fa-file-alt"></i> Pages</a>
        <a href="#" class="secondary-nav-item"><i class="fas fa-th-large"></i> Layouts</a>
        <a href="#" class="secondary-nav-item"><i class="fas fa-pen-nib"></i> UI</a>
        <a href="#" class="secondary-nav-item"><i class="fas fa-table"></i> Forms</a>
        <a href="#" class="secondary-nav-item"><i class="fas fa-ellipsis-h"></i> Others</a>
    </div>

    <div class="row">
        <!-- Revenue Card (Line Chart) -->
        <div class="col-lg-7">
            <div class="modern-card">
                <div class="modern-card-header">
                    <div>
                        <div class="modern-card-title">Revenue</div>
                        <div class="row mt-2">
                            <div class="col-6">
                                <small class="text-muted">Current week</small>
                                <div class="revenue-value">$82,124</div>
                            </div>
                            <div class="col-6">
                                <small class="text-muted">Previous week</small>
                                <div class="revenue-compare" style="font-size: 1.5rem; font-weight: 600;">$52,502</div>
                            </div>
                        </div>
                    </div>
                    <button class="btn btn-link text-muted"><i class="fas fa-redo-alt"></i></button>
                </div>
                <div class="chart-container">
                    <canvas id="revenueChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Hit Rate & Deals Column -->
        <div class="col-lg-5">
            <div class="row">
                <!-- Hit Rate Card -->
                <div class="col-md-12">
                    <div class="modern-card">
                        <div class="modern-card-header">
                            <div class="modern-card-title">Hit Rate <span class="text-danger">-12%</span></div>
                            <button class="btn btn-link text-muted"><i class="fas fa-redo-alt"></i></button>
                        </div>
                        <div class="gauge-container">
                            <canvas id="hitRateChart" width="200" height="200"></canvas>
                            <div class="gauge-value" style="color: var(--primary-red);">82%</div>
                        </div>
                    </div>
                </div>

                <!-- Deals Card -->
                <div class="col-md-12">
                    <div class="modern-card hexagon-card">
                        <div class="modern-card-header">
                            <div class="modern-card-title text-white">Deals <span class="text-white-50">-55%</span></div>
                            <div class="text-right text-white-50">152/200</div>
                        </div>
                        <div class="gauge-container">
                            <canvas id="dealsChart" width="200" height="200"></canvas>
                            <div class="gauge-value text-white">76%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Stats Row -->
    <div class="row">
        <div class="col-md-6">
            <div class="modern-card d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted">Order Value</small>
                    <div class="h3 font-weight-bold mb-0">$ 88,568</div>
                </div>
                <div class="display-4 text-success"><i class="fas fa-trophy"></i></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="modern-card d-flex align-items-center justify-content-between">
                <div>
                    <small class="text-muted">Calls</small>
                    <div class="h3 font-weight-bold mb-0">3,568</div>
                </div>
                <div class="display-4 text-danger"><i class="fas fa-phone-slash"></i></div>
            </div>
        </div>
    </div>

    <!-- Bottom Row (Summary Cards) -->
    <div class="row">
        <div class="col-md-4">
            <div class="modern-card">
                <div class="modern-card-header">
                    <div class="modern-card-title">Emails</div>
                    <button class="btn btn-link text-muted"><i class="fas fa-redo-alt"></i></button>
                </div>
                <div class="text-center py-4">
                    <i class="fas fa-envelope-open-text fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No new emails</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="modern-card">
                <div class="modern-card-header">
                    <div class="modern-card-title">Top Products</div>
                    <a href="#" class="small">Show all</a>
                </div>
                <div class="text-center py-4">
                    <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No products found</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="modern-card">
                <div class="modern-card-title text-center">Average Deal Size</div>
                <div class="d-flex justify-content-around mt-4">
                    <div class="text-center">
                        <div class="text-danger h5">30%</div>
                        <small class="text-muted">Lower</small>
                    </div>
                    <div class="text-center">
                        <div class="text-success h5">48%</div>
                        <small class="text-muted">Higher</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating Elements -->
<div class="floating-settings">
    <i class="fas fa-cog fa-spin"></i>
</div>

<div class="floating-buy-now">
    Buy Now
</div>

<!-- Chart initialization scripts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Line Chart
    const revCtx = document.getElementById('revenueChart').getContext('2d');
    new Chart(revCtx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun', 'Mon'],
            datasets: [{
                label: 'Current week',
                data: [5000, 10000, 6000, 14000, 10000, 17000, 11000, 20000],
                borderColor: '#ff4d4d',
                backgroundColor: 'rgba(255, 77, 77, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                pointRadius: 0,
                fill: true
            }, {
                label: 'Previous week',
                data: [8000, 15000, 11000, 9000, 12000, 8000, 14000, 7000],
                borderColor: '#ccc',
                borderDash: [5, 5],
                borderWidth: 2,
                tension: 0.4,
                pointRadius: 0,
                fill: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { display: true, drawBorder: false },
                    ticks: { stepSize: 5000 }
                },
                x: {
                    grid: { display: false }
                }
            }
        }
    });

    // Hit Rate Doughnut Chart
    const hitCtx = document.getElementById('hitRateChart').getContext('2d');
    new Chart(hitCtx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [82, 18],
                backgroundColor: ['#ff4d4d', '#eee'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '85%',
            responsive: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
    });

    // Deals Doughnut Chart
    const dealsCtx = document.getElementById('dealsChart').getContext('2d');
    new Chart(dealsCtx, {
        type: 'doughnut',
        data: {
            datasets: [{
                data: [76, 24],
                backgroundColor: ['#fff', 'rgba(255, 255, 255, 0.2)'],
                borderWidth: 0
            }]
        },
        options: {
            cutout: '85%',
            responsive: false,
            plugins: { legend: { display: false }, tooltip: { enabled: false } }
        }
    });
</script>
