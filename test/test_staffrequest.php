<?php
session_start();
require_once('../include/config.php');
require_once('../class/StaffRequest.php');

// Set up test environment
$createdby = 'adewole.o@acn.aero';
$deptunitcode = 'ICT';
$staffRequest = new StaffRequest($con);

// Test function to display results
function displayTestResult($testName, $result, $message = null)
{
    echo "<div style='margin: 10px; padding: 10px; border: 1px solid #ccc;'>";
    echo "<strong>Test: " . htmlspecialchars($testName) . "</strong><br>";
    if ($message) {
        echo "Message: " . htmlspecialchars($message) . "<br>";
        echo "Result: " . ($result ? "✅ Passed" : "⚠️ Expected Failure") . "<br>";
    } else {
        echo "Result: " . ($result ? "✅ Passed" : "❌ Failed") . "<br>";
    }
    echo "</div>";
}


echo "<style>
    body { font-family: Arial, sans-serif; margin: 20px; }
    .test-section { margin-bottom: 30px; padding: 20px; background: #f9f9f9; }
    h2 { color: #333; }
    .sub-request { margin-left: 20px; padding: 10px; background: #fff; }
</style>";

echo "<h1>Staff Request Module - Comprehensive Backend Tests</h1>";

// Test Section 0: Vacant Position Validation
echo "<div class='test-section'>";
echo "<h2>0. Vacant Position Validation</h2>";

// Get current available positions
$availablePositions = $staffRequest->getAvailablePositions($deptunitcode);
echo "<div style='background: #eee; padding: 10px; margin: 10px 0;'>";
echo "Available Positions for $deptunitcode: " . $availablePositions;
echo "</div>";

// Test 1: Request within limit
try {
    $withinLimitResult = $staffRequest->saveMainRequest(
        $staffRequest->generateRequestId(),
        'Senior Captain',
        2,
        $deptunitcode,
        'draft',
        $createdby
    );
    displayTestResult(
        "Request Within Available Limit (2 positions)",
        $withinLimitResult
    );
} catch (Exception $e) {
    displayTestResult(
        "Request Within Available Limit (2 positions)",
        false,
        "Error: " . $e->getMessage()
    );
}

// Test 2: Request exceeding limit
try {
    $exceedingResult = $staffRequest->saveMainRequest(
        $staffRequest->generateRequestId(),
        'Senior Captain',
        100, // Request more than available
        $deptunitcode,
        'draft',
        $createdby
    );
    displayTestResult(
        "Request Exceeding Available Limit (100 positions)",
        false
    );
} catch (Exception $e) {
    displayTestResult(
        "Request Exceeding Available Limit (Expected Exception)",
        strpos($e->getMessage(), "Cannot request") !== false,
        "Error (Expected): " . $e->getMessage()
    );
}

// Test 3: Request exact limit
try {
    $exactLimitResult = $staffRequest->saveMainRequest(
        $staffRequest->generateRequestId(),
        'Senior Captain',
        $availablePositions, // Request exactly available amount
        $deptunitcode,
        'draft',
        $createdby
    );
    displayTestResult(
        "Request Exact Available Limit ($availablePositions positions)",
        $exactLimitResult
    );
} catch (Exception $e) {
    displayTestResult(
        "Request Exact Available Limit ($availablePositions positions)",
        false,
        "Error: " . $e->getMessage()
    );
}

// Test 4: Invalid department unit
try {
    $invalidDeptResult = $staffRequest->saveMainRequest(
        $staffRequest->generateRequestId(),
        'Senior Captain',
        1,
        'INVALID', // Invalid department unit code
        'draft',
        $createdby
    );
    displayTestResult(
        "Request with Invalid Department Unit",
        false
    );
} catch (Exception $e) {
    displayTestResult(
        "Request with Invalid Department Unit (Expected Exception)",
        strpos($e->getMessage(), "No headcount data found") !== false,
        "Error (Expected): " . $e->getMessage()
    );
}

echo "</div>";

// Test Section 1: Initial Request Creation
echo "<div class='test-section'>";
echo "<h2>1. Initial Request Creation</h2>";

// Generate new request ID
$requestId = $staffRequest->generateRequestId();
displayTestResult("Generate Request ID", $requestId);

// Test draft creation
$mainRequest = $staffRequest->saveMainRequest(
    $requestId,
    'Senior Captain',
    3, // 3 positions total
    $deptunitcode,
    'draft',
    $createdby
);
displayTestResult("Save Initial Draft", $mainRequest);
echo "</div>";

// Test Section 2: Multiple Station Requests
echo "<div class='test-section'>";
echo "<h2>2. Multiple Station Requests</h2>";

// Test data for multiple stations - Total should be 3
$stationRequests = [
    [
        'station' => 'LOS',
        'employmenttype' => 'Permanent',
        'staffperstation' => 2
    ],
    [
        'station' => 'ABV',
        'employmenttype' => 'Permanent',
        'staffperstation' => 1  // Total = 3 matching novacpost
    ]
];

// Test valid staff count
try {
    $isValidCount = $staffRequest->validateStaffCount(3, $stationRequests);
    displayTestResult(
        "Validate Matching Staff Count",
        $isValidCount,
        "Total staff per station matches novacpost (3)"
    );
} catch (Exception $e) {
    displayTestResult(
        "Validate Matching Staff Count",
        false,
        "Error: " . $e->getMessage()
    );
}

// Test invalid staff count
try {
    $invalidStationRequests = [
        ['station' => 'LOS', 'staffperstation' => 2],
        ['station' => 'ABV', 'staffperstation' => 2]
    ];
    $staffRequest->validateStaffCount(3, $invalidStationRequests);
    displayTestResult("Validate Mismatched Staff Count", false);
} catch (Exception $e) {
    displayTestResult(
        "Validate Mismatched Staff Count (Expected Exception)",
        strpos($e->getMessage(), "must match") !== false,
        "Error (Expected): " . $e->getMessage()
    );
}

// Save station requests
$success = true;
foreach ($stationRequests as $request) {
    $result = $staffRequest->saveStationRequest(
        $requestId,
        $request['station'],
        $request['employmenttype'],
        $request['staffperstation'],
        'draft',
        $createdby
    );
    $success = $success && $result;
}
displayTestResult("Save Multiple Station Requests", $success);
echo "</div>";

// Test Section 3: Submit Draft to Pending
echo "<div class='test-section'>";
echo "<h2>3. Submit Draft to Pending</h2>";

// Update main request to pending
$result = $staffRequest->saveMainRequest(
    $requestId,
    'Senior Captain',
    3,
    $deptunitcode,
    'pending',
    $createdby
);
displayTestResult("Update Main Request to Pending", $result);

// Update all station requests to pending
$success = true;
foreach ($stationRequests as $request) {
    $result = $staffRequest->saveStationRequest(
        $requestId,
        $request['station'],
        $request['employmenttype'],
        $request['staffperstation'],
        'pending',
        $createdby
    );
    $success = $success && $result;
}
displayTestResult("Update Station Requests to Pending", $success);
echo "</div>";

// Test Section 4: HR Review and Actions
echo "<div class='test-section'>";
echo "<h2>4. HR Review and Actions</h2>";

// Display request details
echo "<h3>Request Details:</h3>";
$stationDetails = $staffRequest->getStationsByRequestId($requestId);
echo $stationDetails;

// Test HR actions
$hrActions = [
    ['station' => 'LOS', 'status' => 'approved', 'reason' => null],
    ['station' => 'ABV', 'status' => 'declined', 'reason' => 'Budget constraints']
];

foreach ($hrActions as $action) {
    $result = $staffRequest->updateStationRequestStatus(
        $requestId,
        $action['station'],
        $action['status'],
        $action['reason']
    );
    displayTestResult(
        "HR Action: {$action['status']} for {$action['station']}",
        $result
    );
}

// Get updated summary
$summary = $staffRequest->getRequestSummary($requestId);
echo "<h3>Final Status Summary:</h3>";
echo $summary;
echo "</div>";

// Test Section 5: Editing Permissions
echo "<div class='test-section'>";
echo "<h2>5. Editing Permissions Test</h2>";

// Create a new request ID for this test
$testRequestId = $staffRequest->generateRequestId();

// Create initial draft request
$draftResult = $staffRequest->saveMainRequest(
    $testRequestId,
    'Senior Captain',
    3,
    $deptunitcode,
    'draft',
    $createdby
);
displayTestResult("Create Draft Request", $draftResult);

// Verify draft is editable
$isDraftEditable = $staffRequest->isRequestEditable($testRequestId);
displayTestResult("Draft Stage Editable", $isDraftEditable);

// Update to pending
$pendingResult = $staffRequest->saveMainRequest(
    $testRequestId,
    'Senior Captain',
    3,
    $deptunitcode,
    'pending',
    $createdby
);
displayTestResult("Update to Pending", $pendingResult);

// Verify pending is not editable
$isPendingEditable = $staffRequest->isRequestEditable($testRequestId);
displayTestResult("Pending Stage Not Editable", !$isPendingEditable);

// Display current status for debugging
$currentStatus = $staffRequest->getRequestDetails($testRequestId);
echo "<div style='background: #eee; padding: 10px; margin: 10px 0;'>";
echo "Current Request Status: " . ($currentStatus['status'] ?? 'Not found') . "<br>";
echo "Request ID: " . $testRequestId;
echo "</div>";

echo "</div>";

// Test Section 6: Dropdown Data Tests
echo "<div class='test-section'>";
echo "<h2>6. Dropdown Data Tests</h2>";

// Test Job Titles
echo "<h3>Job Titles (for $deptunitcode):</h3>";
$jobTitles = $staffRequest->getJobTitles();
displayTestResult(
    "Get Job Titles",
    !empty($jobTitles),
    "Output: " . $jobTitles
);

// Test Stations
echo "<h3>Active Stations:</h3>";
$stations = $staffRequest->getStations();
displayTestResult(
    "Get Stations",
    !empty($stations),
    "Output: " . $stations
);

// Test Staff Types
echo "<h3>Staff Types:</h3>";
$staffTypes = $staffRequest->getStaffTypes();
displayTestResult(
    "Get Staff Types",
    !empty($staffTypes),
    "Output: " . $staffTypes
);

// Test Department Units
echo "<h3>Department Units:</h3>";
$departmentUnits = $staffRequest->getDepartmentUnits();
displayTestResult(
    "Get Department Units",
    !empty($departmentUnits),
    "Output: " . $departmentUnits
);

// Test with invalid department unit
$_SESSION['deptunitcode'] = 'INVALID';
$invalidJobTitles = $staffRequest->getJobTitles();
displayTestResult(
    "Get Job Titles (Invalid Department)",
    empty($invalidJobTitles),
    "Expected empty output for invalid department"
);

// Reset session
$_SESSION['deptunitcode'] = $deptunitcode;
echo "</div>";

// Display final database state
echo "<div class='test-section'>";
echo "<h2>Final Database State</h2>";
echo "<pre>";
echo "Main Request:\n";
print_r($staffRequest->getRequestDetails($requestId));
echo "\nStation Requests:\n";
print_r($staffRequest->getAllStationRequests($requestId));
echo "</pre>";
echo "</div>";
