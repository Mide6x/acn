<?php
require_once('../include/config.php');
require_once('subunit.php');

$subunit = new Subunit($con);
$createdby = getCurrentUser('email');
$subdeptunitcode = getCurrentUser('subdeptunitcode');
$staffid = getCurrentUser('staffid');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'createSubunitRequest') {
            // Get form data
            $jdtitle = $_POST['jdtitle'];

            // Decode the JSON string to array
            $stations = json_decode($_POST['stations'], true);

            if (!is_array($stations)) {
                echo "Error: Invalid stations data";
                exit;
            }

            // Calculate total novacpost from all stations
            $novacpost = array_sum(array_column($stations, 'staffperstation'));

            // Create the main request with staffid
            $jdrequestid = $subunit->createSubunitRequest($jdtitle, $novacpost, $subdeptunitcode, $createdby, $staffid);

            // Create entries for each station
            foreach ($stations as $station) {
                $stmt = $con->prepare("INSERT INTO staffrequestperstation 
                    (jdrequestid, station, employmenttype, staffperstation, createdby) 
                    VALUES (?, ?, ?, ?, ?)");

                $stmt->execute([
                    $jdrequestid,
                    $station['station'],
                    $station['employmenttype'],
                    $station['staffperstation'],
                    $createdby
                ]);
            }

            echo "success";
            exit;
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
        exit;
    }
}
