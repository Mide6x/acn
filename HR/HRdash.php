<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';
require_once 'HRClass.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$hr = new HR($con);
?>

<!-- Include Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<main id="main" class="main">
    <section class="section dashboard">
        <div class="row">
            <!-- Metric Cards Row -->
            <div class="col-12">
                <div class="row">
                    <!-- Total Requests Card -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card" style="box-shadow: 0 4px 8px var(--shadow);">
                            <div class="card-body">
                                <h5 class="card-title">Total Requests <span>| This Month</span></h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" 
                                         style="background: var(--shadow); color: var(--primary);">
                                        <i class="bi bi-file-text"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $hr->getTotalRequestsThisMonth(); ?></h6>
                                        <span class="small pt-1">Active Requests</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pending Approvals Card -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card" style="box-shadow: 0 4px 8px var(--shadow);">
                            <div class="card-body">
                                <h5 class="card-title">Pending Approvals</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" 
                                         style="background: var(--shadow); color: var(--primary);">
                                        <i class="bi bi-hourglass-split"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $hr->getPendingApprovalsCount(); ?></h6>
                                        <span class="small pt-1">Awaiting Action</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- CEO Approved Card -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card" style="box-shadow: 0 4px 8px var(--shadow);">
                            <div class="card-body">
                                <h5 class="card-title">CEO Approved</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" 
                                         style="background: var(--shadow); color: var(--primary);">
                                        <i class="bi bi-check-circle"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $hr->getCEOApprovedCount(); ?></h6>
                                        <span class="small pt-1">Final Approvals</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Positions Filled Card -->
                    <div class="col-xxl-3 col-md-6">
                        <div class="card info-card" style="box-shadow: 0 4px 8px var(--shadow);">
                            <div class="card-body">
                                <h5 class="card-title">Positions Filled</h5>
                                <div class="d-flex align-items-center">
                                    <div class="card-icon rounded-circle d-flex align-items-center justify-content-center" 
                                         style="background: var(--shadow); color: var(--primary);">
                                        <i class="bi bi-person-check"></i>
                                    </div>
                                    <div class="ps-3">
                                        <h6><?php echo $hr->getPositionsFilledCount(); ?></h6>
                                        <span class="small pt-1">This Year</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="col-12">
                <div class="row">
                    <!-- Requests Timeline Chart -->
                    <div class="col-lg-8">
                        <div class="card" style="box-shadow: 0 4px 8px var(--shadow);">
                            <div class="card-body">
                                <h5 class="card-title">Request Timeline</h5>
                                <canvas id="requestsTimelineChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Departments Chart -->
                    <div class="col-lg-4">
                        <div class="card" style="box-shadow: 0 4px 8px var(--shadow);">
                            <div class="card-body">
                                <h5 class="card-title">Requests by Department</h5>
                                <canvas id="departmentsPieChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status Overview -->
            <div class="col-12">
                <div class="card" style="box-shadow: 0 4px 8px var(--shadow);">
                    <div class="card-body">
                        <h5 class="card-title">Request Status Overview</h5>
                        <canvas id="statusBarChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<script>
// Chart Configurations
document.addEventListener('DOMContentLoaded', function() {
    // Timeline Chart
    const timelineCtx = document.getElementById('requestsTimelineChart').getContext('2d');
    new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($hr->getRequestTimelineLabels()); ?>,
            datasets: [{
                label: 'Requests',
                data: <?php echo json_encode($hr->getRequestTimelineData()); ?>,
                borderColor: '#fc7f14',
                tension: 0.4,
                fill: true,
                backgroundColor: 'rgba(252, 127, 20, 0.1)'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' }
            }
        }
    });

    // Departments Pie Chart
    const deptCtx = document.getElementById('departmentsPieChart').getContext('2d');
    new Chart(deptCtx, {
        type: 'doughnut',
        data: {
            labels: <?php echo json_encode($hr->getDepartmentLabels()); ?>,
            datasets: [{
                data: <?php echo json_encode($hr->getDepartmentData()); ?>,
                backgroundColor: [
                    '#fc7f14', '#ff9642', '#ffb980', '#ffddbf',
                    '#1a1a1a', '#4d4d4d', '#808080', '#b3b3b3'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Status Bar Chart
    const statusCtx = document.getElementById('statusBarChart').getContext('2d');
    new Chart(statusCtx, {
        type: 'bar',
        data: {
            labels: ['Draft', 'Pending', 'Approved', 'Declined'],
            datasets: [{
                label: 'Number of Requests',
                data: <?php echo json_encode($hr->getStatusData()); ?>,
                backgroundColor: [
                    '#808080',
                    '#ff9642',
                    '#28a745',
                    '#dc3545'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { stepSize: 1 }
                }
            }
        }
    });
});
</script>

<?php include("../includes/footer.html"); ?>