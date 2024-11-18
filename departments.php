<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/class/rev.php';

include("addon/header.html");
include("addon/sidebar.html");

$revenue = new Revenue($con);
?>

<main id="main" class="main">
    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Departments Management</h5>
                        <!-- Content goes here -->
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php include("addon/footer.html"); ?>