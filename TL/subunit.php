<?php
class Subunit
{
    protected $db;

    public function __construct($con)
    {
        $this->db = $con;
    }


    // Station
    public function getStations()
    {
        $query = "SELECT stationcode, stationname FROM stationtbl";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $output = '';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output .= "<option value='{$row['stationcode']}'>{$row['stationname']}</option>";
        }
        return $output;
    }

    // Employment type
    public function getStaffTypes()
    {
        $query = "SELECT stafftype FROM stafftype";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $output = '';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output .= "<option value='{$row['stafftype']}'>{$row['stafftype']}</option>";
        }
        return $output;
    }

    // Generate request ID
    public function generateRequestId()
    {
        return 'REQ' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
    // Get team lead info
    /* START TEAMLEAD FUNCTIONS */
    public function getTeamLeadInfo($staffid)
    {
        // If user is admin, return all access
        if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] === true) {
            return [
                'subdeptunit' => 'All Subunits (Admin View)',
                'subdeptunitcode' => 'ALL',
                'isAdmin' => true
            ];
        }

        $query = "SELECT e.*, s.subdeptunit 
                 FROM employeetbl e 
                 JOIN subdeptunittbl s ON e.subdeptunitcode = s.subdeptunitcode 
                 WHERE e.staffid = ? AND e.position = 'TeamLead' AND e.status = 'Active'";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$staffid]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            return [
                'subdeptunit' => 'No Subunit Found',
                'subdeptunitcode' => null,
                'error' => 'Not a TeamLead or Invalid Staff ID'
            ];
        }

        return $result;
    }

    // Create subunit request
    public function createSubunitRequest($jdtitle, $novacpost, $subdeptunitcode, $createdby)
    {
        try {
            $this->db->beginTransaction();

            // Get department unit code from subunit
            $query = "SELECT deptunitcode FROM subdeptunittbl WHERE subdeptunitcode = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$subdeptunitcode]);
            $deptUnit = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$deptUnit) {
                throw new Exception("Invalid subunit code");
            }

            // Generate request ID
            $requestId = 'REQ' . date('Y') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Create staff request with subdeptunitcode
            $sql = "INSERT INTO staffrequest 
                    (jdrequestid, jdtitle, novacpost, deptunitcode, subdeptunitcode, status, createdby) 
                    VALUES (?, ?, ?, ?, ?, 'draft', ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $requestId,
                $jdtitle,
                $novacpost,
                $deptUnit['deptunitcode'],
                $subdeptunitcode,
                $createdby
            ]);

            $this->db->commit();
            return $requestId;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error creating request: " . $e->getMessage());
        }
    }

    public function getSubunitAvailablePositions($subdeptunitcode)
    {
        try {
            // Get subunit headcount allocation
            $query = "SELECT subdeptnostaff 
                 FROM subdeptunittbl 
                 WHERE subdeptunitcode = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$subdeptunitcode]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                return 0;
            }

            $allocatedStaff = intval($result['subdeptnostaff']);

            // Get current staff count for the subunit
            $query = "SELECT COUNT(*) as current_count 
                 FROM employeetbl 
                 WHERE subdeptunitcode = ? 
                 AND status = 'Active'";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$subdeptunitcode]);
            $currentStaff = $stmt->fetch(PDO::FETCH_ASSOC);

            $currentCount = intval($currentStaff['current_count']);

            // Calculate available positions
            $availablePositions = $allocatedStaff - $currentCount;

            // Return 0 if negative (shouldn't happen in normal circumstances)
            return max(0, $availablePositions);
        } catch (Exception $e) {
            error_log("Error in getSubunitAvailablePositions: " . $e->getMessage());
            return 0;
        }
    }
    public function getSubunitJobTitles($subdeptunitcode)
    {
        $query = "SELECT jdtitle FROM jobtitletbl WHERE subdeptunitcode = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$subdeptunitcode]);

        $output = '';
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output .= "<option value='{$row['jdtitle']}'>{$row['jdtitle']}</option>";
        }
        return $output;
    }

    public function getSubunitRequests($subdeptunitcode)
    {
        $query = "SELECT DISTINCT
                sr.jdrequestid, 
                sr.jdtitle, 
                sr.novacpost,
                sr.status AS request_status,
                (SELECT SUM(staffperstation) 
                 FROM staffrequestperstation 
                 WHERE jdrequestid = sr.jdrequestid) as total_positions,
                a.status AS approval_status,
                a.approvallevel
            FROM staffrequest sr
            LEFT JOIN approvaltbl a ON sr.jdrequestid = a.jdrequestid
            WHERE sr.subdeptunitcode = ?
            GROUP BY sr.jdrequestid
            ORDER BY sr.dandt DESC";

        try {
            $stmt = $this->db->prepare($query);
            $stmt->execute([$subdeptunitcode]);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($requests)) {
                return "<div class='alert alert-info'>No requests found for this subunit</div>";
            }

            $output = "<table class='table table-bordered table-striped'>
                        <thead>
                            <tr>
                                <th>Request ID</th>
                                <th>Job Title</th>
                                <th>Total Positions</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>";

            foreach ($requests as $request) {
                $status = $request['request_status'];
                $statusClass = match ($status) {
                    'draft' => 'text-secondary',
                    'pending' => 'text-warning',
                    'processed' => 'text-success',
                    default => 'text-secondary'
                };

                $output .= "<tr>
                            <td>{$request['jdrequestid']}</td>
                            <td>{$request['jdtitle']}</td>
                            <td>{$request['total_positions']}</td>
                            <td><span class='{$statusClass}'>" . ucfirst($status) . "</span></td>
                            <td>
                                <button class='btn btn-sm btn-primary' 
                                        onclick='viewSubunitRequest(\"{$request['jdrequestid']}\")'>
                                    View
                                </button>
                            </td>
                        </tr>";
            }

            $output .= "</tbody></table>";
            return $output;
        } catch (Exception $e) {
            error_log("Error in getSubunitRequests: " . $e->getMessage());
            return "<div class='alert alert-danger'>Error fetching requests</div>";
        }
    }

    public function getRequestDetails($jdrequestid)
    {
        try {
            // Get main request details with job title information
            $requestQuery = "SELECT 
                    sr.jdrequestid,
                    sr.jdtitle,
                    sr.status,
                    sr.dandt,
                    sr.createdby,
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
                    e.staffname as requestor
                FROM staffrequest sr
                LEFT JOIN jobtitletbl j ON sr.jdtitle = j.jdtitle
                LEFT JOIN employeetbl e ON sr.createdby = e.staffid
                WHERE sr.jdrequestid = ?";

            $stmt = $this->db->prepare($requestQuery);
            $stmt->execute([$jdrequestid]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                return "<div class='alert alert-danger'>Request not found</div>";
            }

            // Get station details
            $stationQuery = "SELECT s.*, st.stationname 
                            FROM staffrequestperstation s
                            JOIN stationtbl st ON s.station = st.stationcode
                            WHERE s.jdrequestid = ?";
            $stationStmt = $this->db->prepare($stationQuery);
            $stationStmt->execute([$jdrequestid]);
            $stations = $stationStmt->fetchAll(PDO::FETCH_ASSOC);

            $output = "<div class='modal-header'>
                        <h5 class='modal-title'>Job Details - {$request['jdtitle']}</h5>
                        <button type='button' class='btn-close' data-bs-dismiss='modal'></button>
                      </div>
                      <div class='modal-body'>";

            // Basic Request Info
            $output .= "<div class='request-info mb-4'>";
            $output .= "<h6 class='text-primary'>Request Information</h6>";
            $output .= "<p><strong>Request ID:</strong> {$request['jdrequestid']}</p>";
            $output .= "<p><strong>Status:</strong> " . ucfirst($request['status']) . "</p>";
            $output .= "<p><strong>Requested By:</strong> {$request['requestor']}</p>";
            $output .= "<p><strong>Date:</strong> " . date('Y-m-d', strtotime($request['dandt'])) . "</p>";
            $output .= "</div>";

            // Job Description Details
            $output .= "<div class='job-details mb-4'>";
            $output .= "<h6 class='text-primary'>Job Description</h6>";
            $output .= "<p><strong>Description:</strong> {$request['jddescription']}</p>";
            $output .= "<p><strong>Educational Qualification:</strong> {$request['eduqualification']}</p>";
            $output .= "<p><strong>Professional Qualification:</strong> {$request['proqualification']}</p>";
            $output .= "<p><strong>Work Relations:</strong> {$request['workrelation']}</p>";
            $output .= "<p><strong>Job Conditions:</strong> {$request['jdcondition']}</p>";
            $output .= "<p><strong>Age Bracket:</strong> {$request['agebracket']}</p>";
            $output .= "</div>";

            // Additional Requirements
            $output .= "<div class='requirements mb-4'>";
            $output .= "<h6 class='text-primary'>Requirements</h6>";
            $output .= "<p><strong>Person Specification:</strong> {$request['personspec']}</p>";
            $output .= "<p><strong>Functional/Technical Skills:</strong> {$request['fuctiontech']}</p>";
            $output .= "<p><strong>Managerial Requirements:</strong> {$request['managerial']}</p>";
            $output .= "<p><strong>Behavioral Competencies:</strong> {$request['behavioural']}</p>";
            $output .= "</div>";

            // Station Details
            if (!empty($stations)) {
                $output .= "<div class='stations-info'>";
                $output .= "<h6 class='text-primary'>Station Requirements</h6>";
                $output .= "<table class='table table-bordered table-striped'>";
                $output .= "<thead><tr><th>Station</th><th>Employment Type</th><th>Staff Count</th><th>Status</th></tr></thead><tbody>";

                foreach ($stations as $station) {
                    $statusClass = match ($station['status']) {
                        'pending' => 'text-warning',
                        'approved' => 'text-success',
                        'declined' => 'text-danger',
                        default => ''
                    };

                    $output .= "<tr>";
                    $output .= "<td>{$station['stationname']}</td>";
                    $output .= "<td>{$station['employmenttype']}</td>";
                    $output .= "<td>{$station['staffperstation']}</td>";
                    $output .= "<td class='{$statusClass}'>" . ucfirst($station['status']) . "</td>";
                    $output .= "</tr>";
                }

                $output .= "</tbody></table></div>";
            }

            $output .= "</div>"; // close modal-body
            $output .= "<div class='modal-footer'>
                        <button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Close</button>
                       </div>";

            return $output;
        } catch (Exception $e) {
            error_log("Error in getRequestDetails: " . $e->getMessage());
            return "<div class='modal-body'><div class='alert alert-danger'>Error fetching request details</div></div>";
        }
    }
}
