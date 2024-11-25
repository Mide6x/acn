<?php
require_once('../include/config.php');
require_once('subunit.php');

$subunit = new Subunit($con);
$createdby = getCurrentUser('email');
$subdeptunitcode = getCurrentUser('subdeptunitcode');

$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action']) && $_POST['action'] === 'createSubunitRequest') {
            // Get form data
            $jdtitle = $_POST['jdtitle'];

            // Decode the JSON string to array
            $stations = json_decode($_POST['stations'], true);

            if (!is_array($stations)) {
                throw new Exception('Invalid stations data');
            }

            // Calculate total novacpost from all stations
            $novacpost = array_sum(array_column($stations, 'staffperstation'));

            // Create the main request
            $jdrequestid = $subunit->createSubunitRequest($jdtitle, $novacpost, $subdeptunitcode, $createdby);

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

            $response['success'] = true;
            $response['message'] = 'Request created successfully';
            $response['jdrequestid'] = $jdrequestid;
        }
    } catch (Exception $e) {
        $response['message'] = $e->getMessage();
    } finally {
        echo json_encode($response);
        exit;
    }
}
