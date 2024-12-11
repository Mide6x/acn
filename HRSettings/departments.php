<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

include("../includes/header.html");
include("../includes/sidebar.html");

$revenue = new Revenue($con);
$pendingRequests = $hr->getPendingRequests();

// Get current hour
$hour = date('H');
$greeting = '';
if ($hour < 12) {
    $greeting = 'Good Morning';
} else if ($hour < 17) {
    $greeting = 'Good Afternoon'; 
} else {
    $greeting = 'Good Evening';
}

// Get HR name from email
$hrName = explode('@', CURRENT_USER['email'])[0];
$hrName = ucwords(str_replace('.', ' ', $hrName));
?>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $greeting . ', ' . $hrName; ?></h5>
                        <h6 class="card-subtitle">Departments Management</h6>
                        <!-- Content goes here -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include("../acnnew/includes/footer.html"); ?>