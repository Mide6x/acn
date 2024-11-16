<?php
require_once('../include/config.php');
require_once('../class/Revenue.php');

function runTest($name, $callback)
{
    try {
        $result = $callback();
        echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
        echo "<strong>Test: $name</strong><br>";
        echo "Result: " . ($result ? "✅ Passed" : "❌ Failed");
        echo "</div>";
    } catch (Exception $e) {
        echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
        echo "<strong>Test: $name</strong><br>";
        echo "Error: " . $e->getMessage() . "<br>";
        echo "Result: ❌ Failed";
        echo "</div>";
    }
}

$revenue = new Revenue($con);
$deptunitcode = 'ICT';
$createdby = 'adewole.o@acn.aero';

// Test available positions
runTest("Get Available Positions", function () use ($revenue, $deptunitcode) {
    $positions = $revenue->getAvailablePositions($deptunitcode);
    return $positions > 0;
});

// Test staff request creation
$requestId = uniqid('REQ');
runTest("Create Staff Request", function () use ($revenue, $requestId, $deptunitcode, $createdby) {
    return $revenue->createOrUpdateStaffRequest(
        $requestId,
        'Senior Developer',
        2,
        $deptunitcode,
        'draft',
        $createdby
    );
});

// Test station request creation
runTest("Create Station Request", function () use ($revenue, $requestId, $createdby) {
    return $revenue->createOrUpdateStaffRequestPerStation(
        $requestId,
        'LOS',
        'Permanent',
        1,
        'draft',
        $createdby
    );
});

// Test staff count validation
runTest("Validate Staff Count", function () use ($revenue) {
    $stationRequests = [
        ['staffperstation' => 1],
        ['staffperstation' => 1]
    ];
    return $revenue->validateStaffCount(2, $stationRequests);
});
