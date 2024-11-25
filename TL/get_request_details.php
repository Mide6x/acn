<?php
require_once('../include/config.php');
require_once('subunit.php');

if (isset($_GET['id'])) {
    $subunit = new Subunit($con);
    echo $subunit->getRequestDetails($_GET['id']);
} else {
    echo "<div class='alert alert-danger'>No request ID provided</div>";
}
