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
        $query = "SELECT 
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
            AND (a.approvallevel IN ('TeamLead', 'DeptUnitLead', 'HOD') OR a.approvallevel IS NULL)
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
                // Determine status display
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
}
