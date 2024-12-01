<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once 'HODClass.php';

$hod = new HOD($con);

if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'getPendingRequests':
            try {
                $deptCode = CURRENT_USER['departmentcode'];
                $requests = $hod->getHODPendingRequests($deptCode);

                if (empty($requests)) {
                    echo "<tr><td colspan='5' class='text-center'>No pending requests found</td></tr>";
                    return;
                }

                foreach ($requests as $request) {
                    echo "<tr>
                        <td>{$request['jdrequestid']}</td>
                        <td>{$request['jdtitle']}</td>
                        <td>{$request['novacpost']}</td>
                        <td><span class='badge " . getBadgeClass($request['approval_status']) . "'>{$request['approval_status']}</span></td>
                        <td>
                            <button class='btn btn-sm btn-info' onclick='viewDetails(\"{$request['jdrequestid']}\")'>
                                View Details
                            </button>";

                    if ($request['approval_status'] === 'pending') {
                        echo "<button class='btn btn-sm btn-success ms-1' onclick='updateStatus(\"{$request['jdrequestid']}\", \"approved\")'>
                                Approve
                            </button>
                            <button class='btn btn-sm btn-danger ms-1' onclick='updateStatus(\"{$request['jdrequestid']}\", \"declined\")'>
                                Decline
                            </button>";
                    }

                    echo "</td></tr>";
                }
            } catch (Exception $e) {
                echo "<tr><td colspan='5' class='text-center text-danger'>Error: {$e->getMessage()}</td></tr>";
            }
            break;
    }
}

function getBadgeClass($status)
{
    switch ($status) {
        case 'pending':
            return 'bg-warning';
        case 'approved':
            return 'bg-success';
        case 'declined':
            return 'bg-danger';
        default:
            return 'bg-secondary';
    }
}
