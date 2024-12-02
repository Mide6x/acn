<?php
class DeptUnit
{
    protected $db;

    public function __construct($con)
    {
        $this->db = $con;
    }
    // Get DeptUnitLead info
    public function getDeptUnitLeadInfo($staffid)
    {
        // If user is admin, return all access
        if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true) {
            return [
                'deptunitname' => 'All Department Units (Admin View)',
                'deptunitcode' => 'ALL',
                'isAdmin' => true
            ];
        }

        $query = "SELECT e.*, d.deptunitname, d.deptunitcode 
                 FROM employeetbl e 
                 JOIN departmentunit d ON e.deptunitcode = d.deptunitcode 
                 WHERE e.staffid = ? 
                 AND e.position = 'DeptUnitLead' 
                 AND e.status = 'Active' 
                 AND d.status = 'Active'";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$staffid]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return [
                'deptunitname' => 'No Department Unit Found',
                'deptunitcode' => null,
                'error' => 'Not a DeptUnitLead or Invalid Staff ID'
            ];
        }

        return $result;
    }
    // Generate unique request ID with format REQ + YEAR + 4-digit sequence
    public function generateRequestId()
    {
        $year = date('Y');
        $query = "SELECT MAX(SUBSTRING(jdrequestid, 8)) as max_seq 
                 FROM staffrequest 
                 WHERE jdrequestid LIKE 'REQ{$year}%'";

        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $sequence = $result['max_seq'] ? intval($result['max_seq']) + 1 : 1;
        return sprintf("REQ%d%04d", $year, $sequence);
    }

    public function getJobTitles()
    {
        $query = "SELECT jdtitle 
                 FROM jobtitletbl 
                 WHERE jdstatus = 'Active' 
                 AND deptunitcode = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$_SESSION['deptunitcode'] ?? DEFAULT_DEPT_UNIT_CODE]);

        $output = "";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output .= "<option value='" . htmlspecialchars($row['jdtitle']) . "'>"
                . htmlspecialchars($row['jdtitle']) . "</option>";
        }
        return $output;
    }
    // Get stations for dropdown
    public function getStations()
    {
        $query = "SELECT stationcode, stationname FROM stationtbl WHERE status = 'Active'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $output = "";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output .= "<option value='" . htmlspecialchars($row['stationcode']) . "'>"
                . htmlspecialchars($row['stationname']) . "</option>";
        }
        return $output;
    }

    // Get staff types for dropdown
    public function getStaffTypes()
    {
        $query = "SELECT stafftype FROM stafftype WHERE status = 'Active'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $output = "";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output .= "<option value='" . htmlspecialchars($row['stafftype']) . "'>"
                . htmlspecialchars($row['stafftype']) . "</option>";
        }
        return $output;
    }
    // Add new function to get draft requests for DeptUnitLead


    public function getDeptUnitLeadDraftRequests($deptunitcode)
    {
        $query = "SELECT sr.*, e.staffname as requestor
               FROM staffrequest sr
               JOIN employeetbl e ON sr.createdby = e.staffid
               WHERE sr.deptunitcode = ? 
               AND sr.status = 'draft'
               ORDER BY sr.dandt DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($requests)) {
            return "<tr><td colspan='6' class='text-center'>No draft requests found</td></tr>";
        }

        $output = "<table class='table table-bordered'>
                 <thead>
                     <tr>
                         <th>Request ID</th>
                         <th>Job Title</th>
                         <th>Positions</th>
                         <th>Requestor</th>
                         <th>Status</th>
                         <th>Actions</th>
                     </tr>
                 </thead>
                 <tbody>";

        foreach ($requests as $request) {
            $output .= "<tr>
                     <td>{$request['jdrequestid']}</td>
                     <td>{$request['jdtitle']}</td>
                     <td>{$request['novacpost']}</td>
                     <td>{$request['requestor']}</td>
                     <td>Draft</td>
                     <td>
                         <button class='btn btn-sm btn-primary' 
                                 onclick='viewRequest(\"{$request['jdrequestid']}\")'>
                             View
                         </button>
                     </td>
                 </tr>";
        }

        $output .= "</tbody></table>";
        return $output;
    }

    public function saveDeptUnitLeadDraftRequest($data)
    {
        try {
            $this->db->beginTransaction();

            // Calculate total staff from station requests
            $totalStaff = array_sum($data['staffPerStation']);

            // Insert/Update main request
            $query = "INSERT INTO staffrequest 
                  (jdrequestid, jdtitle, novacpost, deptunitcode, status, createdby, dandt) 
                  VALUES (?, ?, ?, ?, 'draft', ?, NOW())
                  ON DUPLICATE KEY UPDATE 
                  jdtitle = VALUES(jdtitle),
                  novacpost = VALUES(novacpost),
                  dandt = NOW()";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $data['jdrequestid'],
                $data['jdtitle'],
                $totalStaff,
                $data['deptunitcode'],
                $data['createdby']
            ]);

            // Delete existing station requests
            $deleteQuery = "DELETE FROM staffrequestperstation WHERE jdrequestid = ?";
            $stmt = $this->db->prepare($deleteQuery);
            $stmt->execute([$data['jdrequestid']]);

            // Insert new station requests
            foreach ($data['stations'] as $index => $station) {
                if (empty($station)) continue;

                $query = "INSERT INTO staffrequestperstation 
                      (jdrequestid, station, employmenttype, staffperstation, status, createdby, dandt)
                      VALUES (?, ?, ?, ?, 'draft', ?, NOW())";

                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    $data['jdrequestid'],
                    $station,
                    $data['employmentTypes'][$index],
                    $data['staffPerStation'][$index],
                    $data['createdby']
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error saving DeptUnitLead draft: " . $e->getMessage());
        }
    }

    public function getDeptUnitLeadRequests($deptunitcode)
    {
        try {
            $query = "SELECT 
                        sr.jdrequestid,
                        sr.jdtitle,
                        sr.novacpost,
                        sr.dandt as request_date,
                        sr.subdeptunitcode,
                        sr.status,
                        e.staffname as requestor,
                        (SELECT GROUP_CONCAT(station) 
                         FROM staffrequestperstation 
                         WHERE jdrequestid = sr.jdrequestid) as stations
                    FROM staffrequest sr
                    LEFT JOIN employeetbl e ON sr.staffid = e.staffid
                    WHERE sr.deptunitcode = ? 
                    AND sr.staffid != ?  -- Exclude DeptUnitLead's own requests
                    ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$deptunitcode, $_SESSION['staffid']]);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($requests)) {
                return "<div class='alert alert-info'>No requests found for department unit: " . htmlspecialchars($deptunitcode) . "</div>";
            }

            $output = "<table class='table table-bordered'>
                     <thead>
                         <tr>
                             <th>Request ID</th>
                             <th>Job Title</th>
                             <th>Positions</th>
                             <th>Stations</th>
                             <th>Initiator</th>
                             <th>Status</th>
                             <th>Actions</th>
                         </tr>
                     </thead>
                     <tbody>";

            foreach ($requests as $request) {
                $statusClass = $this->getStatusClass($request['status']);

                $output .= "<tr>
                         <td>" . htmlspecialchars($request['jdrequestid']) . "</td>
                         <td>" . htmlspecialchars($request['jdtitle']) . "</td>
                         <td>" . htmlspecialchars($request['novacpost']) . "</td>
                         <td>" . htmlspecialchars($request['stations'] ?? 'N/A') . "</td>
                         <td>" . htmlspecialchars($request['requestor'] ?? 'N/A') . "</td>
                         <td><span class='badge {$statusClass}'>" . htmlspecialchars($request['status']) . "</span></td>
                         <td>
                             <button class='btn btn-sm btn-primary btn-view-request' 
                                     data-requestid='" . htmlspecialchars($request['jdrequestid']) . "'>
                                 View Details
                             </button>
                         </td>
                     </tr>";
            }

            $output .= "</tbody></table>";
            return $output;
        } catch (Exception $e) {
            error_log("Error in getDeptUnitLeadRequests: " . $e->getMessage());
            return "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }



    public function getDeptUnitLeadRequestDetails($jdrequestid)
    {
        try {
            // Get main request details with proper joins
            $requestQuery = "SELECT sr.*, d.deptunitname, e.staffname as requestor,
                                   j.jddescription, j.eduqualification, j.proqualification,
                                   j.workrelation, j.jdcondition, j.agebracket,
                                   j.personspec, j.fuctiontech, j.managerial, j.behavioural
                            FROM staffrequest sr
                            LEFT JOIN departmentunit d ON sr.deptunitcode = d.deptunitcode
                            LEFT JOIN employeetbl e ON sr.staffid = e.staffid
                            LEFT JOIN jobtitletbl j ON sr.jdtitle = j.jdtitle
                            WHERE sr.jdrequestid = ?";

            $stmt = $this->db->prepare($requestQuery);
            $stmt->execute([$jdrequestid]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                return "<div class='alert alert-danger'>Request not found</div>";
            }

            // Get station details
            $stationQuery = "SELECT srps.*, st.stationname 
                            FROM staffrequestperstation srps
                            JOIN stationtbl st ON srps.station = st.stationcode 
                            WHERE srps.jdrequestid = ?";
            $stationStmt = $this->db->prepare($stationQuery);
            $stationStmt->execute([$jdrequestid]);
            $stations = $stationStmt->fetchAll(PDO::FETCH_ASSOC);


            // Build the output HTML
            $output = "<div class='request-details'>";

            // Main request details section
            $output .= "<div class='main-details mb-4'>";
            $output .= "<h5 class='mb-3'>Request Details</h5>";
            $output .= "<div class='row'>";
            $output .= "<div class='col-md-6'>";
            $output .= "<p><strong>Request ID:</strong> " . htmlspecialchars($request['jdrequestid']) . "</p>";
            $output .= "<p><strong>Job Title:</strong> " . htmlspecialchars($request['jdtitle']) . "</p>";
            $output .= "<p><strong>Department Unit:</strong> " . htmlspecialchars($request['deptunitname']) . "</p>";
            $output .= "<p><strong>Total Positions:</strong> " . htmlspecialchars($request['novacpost']) . "</p>";
            $output .= "</div>";
            $output .= "<div class='col-md-6'>";
            $output .= "<p><strong>Status:</strong> " . htmlspecialchars($request['status']) . "</p>";
            $output .= "<p><strong>Initiator:</strong> " . htmlspecialchars($request['requestor'] ?? 'N/A') . "</p>";
            $output .= "<p><strong>Date:</strong> " . date('Y-m-d', strtotime($request['dandt'])) . "</p>";
            $output .= "</div>";
            $output .= "</div>";
            $output .= "</div>";

            // Station requests section
            if (!empty($stations)) {
                $output .= "<div class='station-requests mb-4'>";
                $output .= "<h5 class='mb-3'>Station Requests</h5>";
                $output .= "<div class='table-responsive'>";
                $output .= "<table class='table table-bordered'>";
                $output .= "<thead><tr>
                            <th>Station</th>
                            <th>Employment Type</th>
                            <th>Staff Count</th>
                            <th>Status</th>
                            <th>Actions</th>
                            </tr></thead><tbody>";

                foreach ($stations as $station) {
                    $actionButtons = '';
                    if (in_array(strtolower($station['status']), ['pending', 'draft', 'new', '']) || $station['status'] === NULL) {
                        $actionButtons = "
                            <button class='btn btn-success btn-sm me-2 btn-approve-station' 
                                    data-requestid='" . htmlspecialchars($request['jdrequestid']) . "'
                                    data-station='" . htmlspecialchars($station['station']) . "'>
                                Approve
                            </button>
                            <button class='btn btn-danger btn-sm btn-decline-station' 
           data-bs-toggle='modal' 
           data-bs-target='#declineModal'
           data-requestid='" . htmlspecialchars($request['jdrequestid']) . "'
           data-station='" . htmlspecialchars($station['station']) . "'>
       Decline
   </button>";
                    } else {
                        $badgeClass = ($station['status'] === 'DeptUnit Lead Approved') ? 'bg-success' : 'bg-danger';
                        $actionButtons = "<span class='badge {$badgeClass}' style='color: white;'>" .
                            htmlspecialchars($station['status']) . "</span>";

                        // Show reason if declined
                        if ($station['status'] === 'DeptUnit Lead Declined' && !empty($station['reason'])) {
                            $actionButtons .= "<br><small class='text-danger mt-1 d-block'>" .
                                htmlspecialchars($station['reason']) . "</small>";
                        }
                    }

                    $output .= "<tr>
                                <td>" . htmlspecialchars($station['stationname']) . "</td>
                                <td>" . htmlspecialchars($station['employmenttype']) . "</td>
                                <td>" . htmlspecialchars($station['staffperstation']) . "</td>
                                <td>" . htmlspecialchars($station['status']) . "</td>
                                <td>{$actionButtons}</td>
                                </tr>";
                }

                $output .= "</tbody></table>";
                $output .= "</div></div>";
            }

            // Job details section
            if ($request['jddescription'] || $request['eduqualification']) {
                $output .= "<div class='job-details mt-4'>";
                $output .= "<h5 class='mb-3'>Job Details</h5>";
                $output .= "<div class='row'>";
                $output .= "<div class='col-md-6'>";
                if ($request['eduqualification']) {
                    $output .= "<p><strong>Educational Qualification:</strong> " . htmlspecialchars($request['eduqualification']) . "</p>";
                }
                if ($request['proqualification']) {
                    $output .= "<p><strong>Professional Qualification:</strong> " . htmlspecialchars($request['proqualification']) . "</p>";
                }
                if ($request['workrelation']) {
                    $output .= "<p><strong>Work Relation:</strong> " . htmlspecialchars($request['workrelation']) . "</p>";
                }
                if ($request['jdcondition']) {
                    $output .= "<p><strong>Job Condition:</strong> " . htmlspecialchars($request['jdcondition']) . "</p>";
                }
                $output .= "</div>";
                $output .= "<div class='col-md-6'>";
                if ($request['agebracket']) {
                    $output .= "<p><strong>Age Bracket:</strong> " . htmlspecialchars($request['agebracket']) . "</p>";
                }
                if ($request['personspec']) {
                    $output .= "<p><strong>Person Specification:</strong> " . htmlspecialchars($request['personspec']) . "</p>";
                }
                if ($request['fuctiontech']) {
                    $output .= "<p><strong>Technical Skills:</strong> " . htmlspecialchars($request['fuctiontech']) . "</p>";
                }
                if ($request['managerial']) {
                    $output .= "<p><strong>Managerial Skills:</strong> " . htmlspecialchars($request['managerial']) . "</p>";
                }
                if ($request['behavioural']) {
                    $output .= "<p><strong>Behavioral Skills:</strong> " . htmlspecialchars($request['behavioural']) . "</p>";
                }
                $output .= "</div>";
                $output .= "</div>";
                $output .= "</div>";
            }

            $output .= "</div>";
            return $output;
        } catch (Exception $e) {
            error_log("Error in getDeptUnitLeadRequestDetails: " . $e->getMessage());
            return "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }


    public function updateDeptUnitLeadApproval($jdrequestid, $action, $reason = null)
    {
        try {
            $this->db->beginTransaction();

            // Check if all stations are declined
            $checkStationsQuery = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'DeptUnit Lead Declined' THEN 1 ELSE 0 END) as declined
            FROM staffrequestperstation 
            WHERE jdrequestid = ?";

            $stmt = $this->db->prepare($checkStationsQuery);
            $stmt->execute([$jdrequestid]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $allStationsDeclined = ($result['total'] > 0 && $result['total'] == $result['declined']);

            // Update DeptUnitLead approval status
            $deptUnitLeadStatus = $allStationsDeclined ? 'declined' : 'approved';
            $updateDeptUnitLead = "UPDATE approvaltbl 
                                  SET status = ?, 
                                      dandt = NOW()
                                  WHERE jdrequestid = ? 
                                  AND approvallevel = 'DeptUnitLead'";
            $stmt = $this->db->prepare($updateDeptUnitLead);
            $stmt->execute([$deptUnitLeadStatus, $jdrequestid]);

            // Update HOD approval status based on DeptUnitLead decision
            if (!$allStationsDeclined) {
                // If not all declined, set HOD to pending
                $updateHOD = "UPDATE approvaltbl 
                             SET status = 'pending',
                                 dandt = NOW()
                             WHERE jdrequestid = ? 
                             AND approvallevel = 'HOD'";
                $stmt = $this->db->prepare($updateHOD);
                $stmt->execute([$jdrequestid]);
            }

            // Update main request status
            $mainStatus = $allStationsDeclined ? 'DeptUnit Lead Declined' : 'DeptUnit Lead Approved';
            $updateRequest = "UPDATE staffrequest 
                             SET status = ?,
                                 decline_reason = ?
                             WHERE jdrequestid = ?";
            $stmt = $this->db->prepare($updateRequest);
            $stmt->execute([$mainStatus, $allStationsDeclined ? $reason : null, $jdrequestid]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /* START DEPT UNIT LEAD FUNCTIONS */
    public function getDeptUnitLeadAvailablePositions($deptunitcode)
    {
        try {
            // Get the total number of employees in the department unit
            $employeeQuery = "SELECT COUNT(*) as totalEmployees 
                           FROM employeetbl e
                           WHERE e.deptunitcode = ? 
                           AND e.status = 'Active'";
            $stmt = $this->db->prepare($employeeQuery);
            $stmt->execute([$deptunitcode]);
            $totalEmployees = $stmt->fetch(PDO::FETCH_ASSOC)['totalEmployees'] ?? 0;

            // Get the required number of staff for the department unit
            $requiredStaffQuery = "SELECT deptunitnostaff 
                                FROM departmentunit 
                                WHERE deptunitcode = ?";
            $stmt = $this->db->prepare($requiredStaffQuery);
            $stmt->execute([$deptunitcode]);
            $requiredStaff = $stmt->fetch(PDO::FETCH_ASSOC)['deptunitnostaff'] ?? 0;

            // Calculate available positions
            $availablePositions = $requiredStaff - $totalEmployees;

            return $availablePositions;
        } catch (Exception $e) {
            return "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }
    /* END DEPT UNIT LEAD FUNCTIONS */


    private function updateRequestAndApprovalStatus($jdrequestid, $declinedStation = null, $reason = null)
    {
        // Check all stations status
        $checkQuery = "SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'DeptUnit Lead Approved' THEN 1 ELSE 0 END) as approved
        FROM staffrequestperstation 
        WHERE jdrequestid = ?";

        $stmt = $this->db->prepare($checkQuery);
        $stmt->execute([$jdrequestid]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        // If there's at least one approved station, the whole request is approved
        $hasApprovedStations = ($result['approved'] > 0);

        // Update main request status
        if ($hasApprovedStations) {
            $updateRequest = "UPDATE staffrequest 
                             SET status = 'DeptUnit Lead Approved'
                             WHERE jdrequestid = ?";
            $stmt = $this->db->prepare($updateRequest);
            $stmt->execute([$jdrequestid]);
        } else {
            $updateRequest = "UPDATE staffrequest 
                             SET status = 'DeptUnit Lead Declined',
                                 decline_reason = ?
                             WHERE jdrequestid = ?";
            $stmt = $this->db->prepare($updateRequest);
            $stmt->execute([$reason, $jdrequestid]);
        }
        // Update DeptUnitLead approval status
        $deptUnitLeadStatus = $hasApprovedStations ? 'approved' : 'declined';
        $updateDeptUnitLead = "UPDATE approvaltbl 
                              SET status = ?,
                                  comments = ?,
                                  dandt = NOW()
                              WHERE jdrequestid = ? 
                              AND approvallevel = 'DeptUnitLead'";

        $comments = null;
        if ($declinedStation && $hasApprovedStations) {
            $comments = "Partially approved. Station $declinedStation declined: $reason";
        }

        $stmt = $this->db->prepare($updateDeptUnitLead);
        $stmt->execute([$deptUnitLeadStatus, $comments, $jdrequestid]);
        // If approved, update next level (HOD) to pending
        if ($hasApprovedStations) {
            $updateNextLevel = "UPDATE approvaltbl 
                              SET status = 'pending',
                                  dandt = NOW()
                              WHERE jdrequestid = ? 
                              AND approvallevel = 'HOD'";
            $stmt = $this->db->prepare($updateNextLevel);
            $stmt->execute([$jdrequestid]);
        }
    }

    public function approveDeptUnitLeadStation($jdrequestid, $station)
    {
        try {
            $this->db->beginTransaction();

            // Update the station request status
            $query = "UPDATE staffrequestperstation 
                     SET status = 'DeptUnit Lead Approved',
                         dandt = NOW()
                     WHERE jdrequestid = ? AND station = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$jdrequestid, $station]);

            // Update main request and approval statuses
            $this->updateRequestAndApprovalStatus($jdrequestid);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in approveDeptUnitLeadStation: " . $e->getMessage());
            throw $e;
        }
    }

    public function declineDeptUnitLeadRequest($jdrequestid, $station, $reason)
    {
        try {
            $this->db->beginTransaction();

            // Update the station status and reason
            $updateStation = "UPDATE staffrequestperstation 
                             SET status = 'DeptUnit Lead Declined',
                                 reason = ?,
                                 dandt = NOW()
                             WHERE jdrequestid = ? 
                             AND station = ?";
            $stmt = $this->db->prepare($updateStation);
            $stmt->execute([$reason, $jdrequestid, $station]);

            // Update main request and approval statuses
            $this->updateRequestAndApprovalStatus($jdrequestid, $station, $reason);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in declineDeptUnitLeadRequest: " . $e->getMessage());
            throw $e;
        }
    }

    public function createStaffRequest($formData)
    {
        try {
            $this->db->beginTransaction();

            // Delete any existing draft without jdrequestid
            $deleteDraft = "DELETE FROM staffrequest 
                           WHERE jdrequestid = '' 
                           AND createdby = ? 
                           AND status = 'draft'";
            $stmt = $this->db->prepare($deleteDraft);
            $stmt->execute([$_SESSION['staffid']]);

            // Generate new request ID if not provided
            if (empty($formData['jdrequestid'])) {
                $formData['jdrequestid'] = $this->generateRequestId();
            }

            // Set default values
            $formData['createdby'] = $_SESSION['staffid'] ?? '';
            $formData['staffid'] = $_SESSION['staffid'] ?? '';

            // Insert main request
            $insertRequest = "INSERT INTO staffrequest (
                jdrequestid, jdtitle, novacpost, deptunitcode, 
                status, createdby, subdeptunitcode, staffid
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

            $stmt = $this->db->prepare($insertRequest);
            $stmt->execute([
                $formData['jdrequestid'],
                $formData['jdtitle'],
                $formData['novacpost'],
                $formData['deptunitcode'],
                $formData['status'],
                $formData['staffid'], // Using staffid instead of username
                $formData['subdeptunitcode'],
                $formData['staffid']
            ]);

            // Insert station requests
            if (!empty($formData['stations'])) {
                foreach ($formData['stations'] as $station) {
                    $insertStation = "INSERT INTO staffrequestperstation (
                        jdrequestid, station, employmenttype, 
                        staffperstation, status, createdby
                    ) VALUES (?, ?, ?, ?, ?, ?)";

                    $stmt = $this->db->prepare($insertStation);
                    $result = $stmt->execute([
                        $formData['jdrequestid'],
                        $station['station'],
                        $station['employmenttype'],
                        $station['staffperstation'],
                        $formData['status'],
                        $formData['staffid']
                    ]);

                    if (!$result) {
                        throw new Exception("Failed to insert station request: " . print_r($stmt->errorInfo(), true));
                    }
                }
            }

            // If it's not a draft, update the status to pending
            if ($formData['status'] !== 'draft') {
                // Update the main request status
                $updateRequest = "UPDATE staffrequest 
                                SET status = 'pending'
                                WHERE jdrequestid = ?";
                $stmt = $this->db->prepare($updateRequest);
                $stmt->execute([$formData['jdrequestid']]);

                // Update DeptUnitLead approval to approved if it's pending
                $updateDeptUnitLead = "UPDATE approvaltbl 
                                      SET status = 'approved',
                                          dandt = NOW()
                                      WHERE jdrequestid = ? 
                                      AND approvallevel = 'DeptUnitLead'
                                      AND status = 'pending'";
                $stmt = $this->db->prepare($updateDeptUnitLead);
                $stmt->execute([$formData['jdrequestid']]);

                // Update HOD approval to pending
                $updateHOD = "UPDATE approvaltbl 
                             SET status = 'pending',
                                 dandt = NOW()
                             WHERE jdrequestid = ? 
                             AND approvallevel = 'HOD'";
                $stmt = $this->db->prepare($updateHOD);
                $stmt->execute([$formData['jdrequestid']]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error creating staff request: " . $e->getMessage());
        }
    }

    // Helper function to get HOD approver
    private function getHODApprover($deptunitcode)
    {
        $query = "SELECT staffid FROM employeetbl 
                  WHERE position = 'HOD' 
                  AND deptunitcode = ? 
                  AND status = 'Active' 
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['staffid'] : null;
    }

    // Helper function to get approver by level
    private function getApproverByLevel($level)
    {
        $query = "SELECT staffid FROM employeetbl 
                  WHERE position = ? 
                  AND status = 'Active' 
                  LIMIT 1";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$level]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? $result['staffid'] : null;
    }

    public function getMyStaffRequests($staffid)
    {
        try {
            $query = "SELECT 
                        sr.jdrequestid,
                        sr.jdtitle,
                        sr.novacpost,
                        sr.dandt as request_date,
                        CASE 
                            WHEN sr.status = 'draft' THEN 'Draft'
                            ELSE COALESCE(
                                (SELECT status 
                                 FROM approvaltbl 
                                 WHERE jdrequestid = sr.jdrequestid 
                                 AND status != 'draft'
                                 ORDER BY dandt DESC 
                                 LIMIT 1),
                                'Pending'
                            )
                        END as current_status,
                        (SELECT approvallevel 
                         FROM approvaltbl 
                         WHERE jdrequestid = sr.jdrequestid 
                         AND status = 'pending'
                         LIMIT 1) as current_level
                    FROM staffrequest sr
                    WHERE sr.staffid = ?
                    ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$staffid]);

            $output = '<div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>Request ID</th>
                                    <th>Job Title</th>
                                    <th>Vacant Positions</th>
                                    <th>Request Date</th>
                                    <th>Status</th>
                                    <th>Current Level</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>';

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $statusClass = $this->getStatusClass($row['current_status']);

                $output .= '<tr>
                            <td>' . htmlspecialchars($row['jdrequestid']) . '</td>
                            <td>' . htmlspecialchars($row['jdtitle']) . '</td>
                            <td>' . htmlspecialchars($row['novacpost']) . '</td>
                            <td>' . date('Y-m-d', strtotime($row['request_date'])) . '</td>
                            <td><span class="badge ' . $statusClass . '">' .
                    htmlspecialchars($row['current_status']) . '</span></td>
                            <td>' . htmlspecialchars($row['current_level'] ?? 'N/A') . '</td>
                            <td>
                                <button type="button" class="btn btn-primary btn-sm" 
                                        onclick="viewRequestDetails(\'' . $row['jdrequestid'] . '\')">
                                    View Details
                                </button>
                                ' . ($row['current_status'] === 'Draft' ? '' : '') . '
                            </td>
                        </tr>';
            }

            $output .= '</tbody></table></div>';
            return $output;
        } catch (Exception $e) {
            error_log("Error in getMyStaffRequests: " . $e->getMessage());
            return '<div class="alert alert-danger">Error loading requests</div>';
        }
    }

    private function getStatusClass($status)
    {
        switch (strtolower($status)) {
            case 'approved':
                return 'bg-success';
            case 'pending':
                return 'bg-warning';
            case 'declined':
                return 'bg-danger';
            default:
                return 'bg-secondary';
        }
    }

    public function getRequestDetails($requestId)
    {
        $query = "SELECT 
                    sr.jdrequestid,
                    sr.jdtitle,
                    sr.status,
                    sr.dandt,
                    sr.createdby,
                    sr.novacpost,
                    j.jddescription,
                    j.eduqualification,
                    j.proqualification,
                    j.workrelation,
                    j.jdcondition,
                    j.agebracket,
                    j.personspec,
                    j.fuctiontech,
                    j.managerial,
                    j.behavioural,
                    e.staffname as requestor,
                    COALESCE(
                        (SELECT approvallevel 
                         FROM approvaltbl 
                         WHERE jdrequestid = sr.jdrequestid 
                         AND status = 'pending'
                         ORDER BY id ASC 
                         LIMIT 1),
                        'Completed'
                    ) as current_level
                FROM staffrequest sr
                LEFT JOIN jobtitletbl j ON sr.jdtitle = j.jdtitle
                LEFT JOIN employeetbl e ON sr.staffid = e.staffid
                WHERE sr.jdrequestid = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$requestId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStationDetails($requestId)
    {
        $query = "SELECT srps.*, s.stationname 
                  FROM staffrequestperstation srps
                  LEFT JOIN stationtbl s ON srps.station = s.stationcode
                  WHERE srps.jdrequestid = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$requestId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getApprovalWorkflow($requestId)
    {
        $query = "SELECT approvallevel, status, dandt, comments 
                  FROM approvaltbl 
                  WHERE jdrequestid = ?
                  ORDER BY id ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$requestId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateStaffRequest($data)
    {
        try {
            $this->db->beginTransaction();

            // Update main request - removed modifiedby and modifieddandt
            $updateRequest = "UPDATE staffrequest 
                             SET jdtitle = ?, 
                                 novacpost = ?
                             WHERE jdrequestid = ?";

            $stmt = $this->db->prepare($updateRequest);
            $stmt->execute([
                $data['jdtitle'],
                $data['novacpost'],
                $data['jdrequestid']
            ]);

            // Delete existing stations for this request
            $deleteStations = "DELETE FROM staffrequestperstation 
                              WHERE jdrequestid = ?";
            $stmt = $this->db->prepare($deleteStations);
            $stmt->execute([$data['jdrequestid']]);

            // Insert updated stations
            $insertStation = "INSERT INTO staffrequestperstation 
                             (jdrequestid, station, employmenttype, 
                              staffperstation, status, createdby, dandt) 
                             VALUES (?, ?, ?, ?, ?, ?, NOW())";

            foreach ($data['stations'] as $station) {
                $stmt = $this->db->prepare($insertStation);
                $stmt->execute([
                    $data['jdrequestid'],
                    $station['station'],
                    $station['employmenttype'],
                    $station['staffperstation'],
                    'draft',
                    $_SESSION['staffid']
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in updateStaffRequest: " . $e->getMessage());
            throw $e;
        }
    }

    public function getEditRequestData($requestId, $staffId)
    {
        try {
            // Get request details
            $query = "SELECT sr.*, e.staffname as requestor
                     FROM staffrequest sr
                     LEFT JOIN employeetbl e ON sr.staffid = e.staffid
                     WHERE sr.jdrequestid = ?";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$requestId]);
            $requestDetails = $stmt->fetch(PDO::FETCH_ASSOC);

            // Get station details with full information
            $stationQuery = "SELECT srps.*, s.stationname
                            FROM staffrequestperstation srps
                            LEFT JOIN stationtbl s ON srps.station = s.stationcode
                            WHERE srps.jdrequestid = ?";

            $stmt = $this->db->prepare($stationQuery);
            $stmt->execute([$requestId]);
            $stations = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Check if request exists and belongs to current user
            if (!$requestDetails || $requestDetails['createdby'] !== $staffId) {
                throw new Exception("Request not found or unauthorized access.");
            }

            // Check if request is still in draft status
            if ($requestDetails['status'] !== 'draft') {
                throw new Exception("Only draft requests can be edited.");
            }

            return [
                'details' => $requestDetails,
                'stations' => $stations
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getStationsWithSelected($selectedValue)
    {
        $options = '';
        $query = "SELECT stationcode, stationname FROM stationtbl WHERE status = 'Active' ORDER BY stationname";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $selected = ($row['stationcode'] === $selectedValue) ? 'selected' : '';
            $options .= "<option value='" . htmlspecialchars($row['stationcode']) . "' {$selected}>" .
                htmlspecialchars($row['stationname']) . "</option>";
        }
        return $options;
    }

    public function getStaffTypesWithSelected($selectedValue)
    {
        $options = '';
        $query = "SELECT DISTINCT employmenttype as value, employmenttype as label
                  FROM staffrequestperstation 
                  WHERE status IN ('draft', 'pending', 'approved')
                  UNION
                  SELECT stprefix as value, stafftype as label
                  FROM stafftype
                  WHERE status = 'Active'
                  ORDER BY label";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $selected = ($row['value'] === $selectedValue) ? 'selected' : '';
            $options .= "<option value='" . htmlspecialchars($row['value']) . "' {$selected}>" .
                htmlspecialchars($row['label']) . "</option>";
        }
        return $options;
    }

    public function submitStaffRequest($data)
    {
        try {
            $this->db->beginTransaction();

            // Insert main request with 'pending' status
            $insertRequest = "INSERT INTO staffrequest (
                jdrequestid, jdtitle, novacpost, 
                deptunitcode, subdeptunitcode, 
                staffid, status, dandt
            ) VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())";

            $stmt = $this->db->prepare($insertRequest);
            $stmt->execute([
                $data['jdrequestid'],
                $data['jdtitle'],
                $data['novacpost'],
                $data['deptunitcode'],
                $data['subdeptunitcode'],
                $_SESSION['staffid']
            ]);

            // Insert stations with 'pending' status
            $insertStation = "INSERT INTO staffrequestperstation 
                             (jdrequestid, station, employmenttype, 
                              staffperstation, status, createdby, dandt) 
                             VALUES (?, ?, ?, ?, 'pending', ?, NOW())";

            foreach ($data['stations'] as $station) {
                $stmt = $this->db->prepare($insertStation);
                $stmt->execute([
                    $data['jdrequestid'],
                    $station['station'],
                    $station['employmenttype'],
                    $station['staffperstation'],
                    $_SESSION['staffid']
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in submitStaffRequest: " . $e->getMessage());
            throw $e;
        }
    }

    // Add this method to check if a request is editable
    public function isRequestEditable($requestId)
    {
        $query = "SELECT status FROM staffrequest WHERE jdrequestid = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$requestId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result && $result['status'] === 'draft';
    }
}
