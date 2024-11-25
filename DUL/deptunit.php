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
            $query = "SELECT sr.*, e.staffname as requestor, su.subdeptunit
                     FROM staffrequest sr
                     LEFT JOIN employeetbl e ON sr.staffid = e.staffid
                     LEFT JOIN subdeptunittbl su ON sr.subdeptunitcode = su.subdeptunitcode
                     WHERE sr.deptunitcode = ? 
                     ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$deptunitcode]);
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
                             <th>Sub Unit</th>
                             <th>Initiator</th>
                             <th>Status</th>
                             <th>Actions</th>
                         </tr>
                     </thead>
                     <tbody>";

            foreach ($requests as $request) {
                $subunit = $request['subdeptunit'] ? htmlspecialchars($request['subdeptunit']) : 'Main Unit';
                $requestor = $request['requestor'] ? htmlspecialchars($request['requestor']) : 'N/A';

                $output .= "<tr>
                         <td>" . htmlspecialchars($request['jdrequestid']) . "</td>
                         <td>" . htmlspecialchars($request['jdtitle']) . "</td>
                         <td>" . htmlspecialchars($request['novacpost']) . "</td>
                         <td>{$subunit}</td>
                         <td>{$requestor}</td>
                         <td>" . htmlspecialchars($request['status']) . "</td>
                         <td>
                             <button class='btn btn-sm btn-primary btn-view-request' 
                                     data-requestid='" . htmlspecialchars($request['jdrequestid']) . "'>
                                 View
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
            $output .= "<p><strong>Requestor:</strong> " . htmlspecialchars($request['requestor'] ?? 'N/A') . "</p>";
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
                    if ($station['status'] === 'pending') {
                        $actionButtons = "
                            <button class='btn btn-success btn-sm me-2' 
                                    onclick='approveDeptUnitLeadStation(\"{$request['jdrequestid']}\", \"{$station['station']}\")'>
                                Approve
                            </button>
                            <button class='btn btn-danger btn-sm' 
                                    onclick='showDeclineStationModal(\"{$request['jdrequestid']}\", \"{$station['station']}\")'>
                                Decline
                            </button>";
                    } else {
                        $actionButtons = "<span class='badge " .
                            ($station['status'] === 'approved' ? 'bg-success' : 'bg-danger') . "'>" .
                            ucfirst($station['status']) . "</span>";
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

            if ($action === 'approve') {
                // Update staffrequest status
                $updateRequest = "UPDATE staffrequest SET status = 'Unit Lead approved' WHERE jdrequestid = ?";
                $stmt = $this->db->prepare($updateRequest);
                $stmt->execute([$jdrequestid]);

                // Update HOD approval status to pending
                $updateApproval = "UPDATE approvaltbl SET status = 'pending' 
                              WHERE jdrequestid = ? AND approvallevel = 'HOD'";
                $stmt = $this->db->prepare($updateApproval);
                $stmt->execute([$jdrequestid]);
            } else {
                // Update staffrequest status and reason
                $updateRequest = "UPDATE staffrequest SET status = 'declined', decline_reason = ? WHERE jdrequestid = ?";
                $stmt = $this->db->prepare($updateRequest);
                $stmt->execute([$reason, $jdrequestid]);
            }

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

    public function approveDeptUnitLeadStation($jdrequestid, $station)
    {
        try {
            $this->db->beginTransaction();

            // Update the station request status
            $query = "UPDATE staffrequestperstation 
                     SET status = 'approved' 
                     WHERE jdrequestid = ? AND station = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$jdrequestid, $station]);

            // Check if all stations are approved
            $checkQuery = "SELECT COUNT(*) as total, 
                             SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved
                      FROM staffrequestperstation 
                      WHERE jdrequestid = ?";
            $stmt = $this->db->prepare($checkQuery);
            $stmt->execute([$jdrequestid]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            // If all stations are approved, update main request status
            if ($result['total'] == $result['approved']) {
                $updateRequest = "UPDATE staffrequest 
                                SET status = 'processed' 
                                WHERE jdrequestid = ?";
                $stmt = $this->db->prepare($updateRequest);
                $stmt->execute([$jdrequestid]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function declineDeptUnitLeadStation($jdrequestid, $station, $reason)
    {
        try {
            $this->db->beginTransaction();

            // Update the station request status and reason
            $query = "UPDATE staffrequestperstation 
                     SET status = 'declined', 
                         reason = ? 
                     WHERE jdrequestid = ? AND station = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$reason, $jdrequestid, $station]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}