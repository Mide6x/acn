<?php
class Revenue
{
    protected $db;

    public function __construct($con)
    {
        $this->db = $con;
    }

    // Generate unique request ID
    public function generateRequestId()
    {
        $year = date('Y');

        do {
            // Generate a random 4-digit number
            $randomNumber = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
            $requestId = "REQ" . $year . $randomNumber;

            // Check if this ID already exists
            $query = "SELECT COUNT(*) as count FROM staffrequest WHERE jdrequestid = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$requestId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
        } while ($result['count'] > 0); // Keep generating until we find an unused ID

        return $requestId;
    }

    // Get job titles for dropdown
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

    // Create or update main staff request
    public function saveMainRequest($jdrequestid, $jdtitle, $novacpost, $deptunitcode, $status, $createdby)
    {
        // Validate request ID format
        if (!$this->isValidRequestId($jdrequestid)) {
            throw new InvalidArgumentException("Invalid request ID format");
        }

        // Validate against headcount limits
        try {
            $this->validateVacantPositions($deptunitcode, $novacpost);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        // First check if request exists
        $checkQuery = "SELECT jdrequestid FROM staffrequest WHERE jdrequestid = ?";
        $checkStmt = $this->db->prepare($checkQuery);
        $checkStmt->execute([$jdrequestid]);
        $exists = $checkStmt->fetch(PDO::FETCH_ASSOC);

        if ($exists) {
            // Update existing request
            $query = "UPDATE staffrequest 
                     SET jdtitle = ?, 
                         novacpost = ?, 
                         status = ? 
                     WHERE jdrequestid = ?";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$jdtitle, $novacpost, $status, $jdrequestid]);
        } else {
            // Insert new request
            $query = "INSERT INTO staffrequest 
                     (jdrequestid, jdtitle, novacpost, deptunitcode, status, createdby) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([$jdrequestid, $jdtitle, $novacpost, $deptunitcode, $status, $createdby]);
        }
    }

    // Save station-specific request
    public function saveStationRequest($jdrequestid, $station, $employmenttype, $staffperstation, $status, $createdby)
    {
        $query = "INSERT INTO staffrequestperstation 
                 (jdrequestid, station, employmenttype, staffperstation, status, createdby) 
                 VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([
            $jdrequestid,
            $station,
            $employmenttype,
            $staffperstation,
            $status,
            $createdby
        ]);
    }

    // Add method to validate total staff count
    public function validateStaffCount($novacpost, $stationRequests)
    {
        $totalStaffPerStation = 0;
        foreach ($stationRequests as $request) {
            $totalStaffPerStation += intval($request['staffperstation']);
        }
        return $totalStaffPerStation === intval($novacpost);
    }

    // Add method to get all stations for a request
    public function getStationsByRequestId($jdrequestid)
    {
        $query = "SELECT s.*, st.stationname 
                 FROM staffrequestperstation s 
                 JOIN stationtbl st ON s.station = st.stationcode 
                 WHERE s.jdrequestid = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$jdrequestid]);

        $output = "";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output .= "<div class='station-request'>";
            $output .= "Station: " . htmlspecialchars($row['stationname']) . "<br>";
            $output .= "Staff Required: " . htmlspecialchars($row['staffperstation']) . "<br>";
            $output .= "Employment Type: " . htmlspecialchars($row['employmenttype']) . "<br>";
            $output .= "Status: " . htmlspecialchars($row['status']) . "<br>";
            $output .= "</div>";
        }
        return $output;
    }

    // Update status of a station request
    public function updateStationRequestStatus($jdrequestid, $station, $status, $reason = null)
    {
        $query = "UPDATE staffrequestperstation 
                 SET status = ?, reason = ? 
                 WHERE jdrequestid = ? AND station = ?";

        $stmt = $this->db->prepare($query);
        return $stmt->execute([$status, $reason, $jdrequestid, $station]);
    }

    // Get summary of request statuses
    public function getRequestSummary($jdrequestid)
    {
        $query = "SELECT 
                    COUNT(CASE WHEN status = 'approved' THEN 1 END) as approved,
                    COUNT(CASE WHEN status = 'declined' THEN 1 END) as declined,
                    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending
                 FROM staffrequestperstation 
                 WHERE jdrequestid = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$jdrequestid]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return "<div class='request-summary'>
                    <p>Approved: {$result['approved']}</p>
                    <p>Declined: {$result['declined']}</p>
                    <p>Pending: {$result['pending']}</p>
                </div>";
    }

    // Check if request is editable
    public function isRequestEditable($jdrequestid)
    {
        $query = "SELECT status FROM staffrequest WHERE jdrequestid = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$jdrequestid]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // If no record found or status is not 'draft', return false
        if (!$result) {
            return false;
        }

        return $result['status'] === 'draft';
    }

    // Get main request details
    public function getRequestDetails($jdrequestid)
    {
        $query = "SELECT * FROM staffrequest WHERE jdrequestid = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$jdrequestid]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Get all station requests for a request ID
    public function getAllStationRequests($jdrequestid)
    {
        $query = "SELECT s.*, st.stationname 
                 FROM staffrequestperstation s 
                 JOIN stationtbl st ON s.station = st.stationcode 
                 WHERE s.jdrequestid = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$jdrequestid]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Update status of main request based on station requests
    public function updateMainRequestStatus($jdrequestid)
    {
        $query = "SELECT 
                    CASE 
                        WHEN COUNT(*) = COUNT(CASE WHEN status = 'approved' THEN 1 END) 
                        THEN 'processed'
                        WHEN COUNT(*) = COUNT(CASE WHEN status IN ('approved', 'declined') THEN 1 END) 
                        THEN 'processed'
                        ELSE 'pending'
                    END as new_status
                 FROM staffrequestperstation 
                 WHERE jdrequestid = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$jdrequestid]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        $updateQuery = "UPDATE staffrequest SET status = ? WHERE jdrequestid = ?";
        $updateStmt = $this->db->prepare($updateQuery);
        return $updateStmt->execute([$result['new_status'], $jdrequestid]);
    }

    // Add this debug method
    public function getRequestStatus($jdrequestid)
    {
        $query = "SELECT status FROM staffrequest WHERE jdrequestid = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$jdrequestid]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ? $result['status'] : 'Not found';
    }

    // Add this validation method
    private function isValidRequestId($requestId)
    {
        // Check format: REQ + year (4 digits) + sequence (4 digits)
        $pattern = '/^REQ\d{8}$/';
        return preg_match($pattern, $requestId) === 1;
    }

    // Add this new validation method
    private function validateVacantPositions($deptunitcode, $requestedPositions)
    {
        // Get headcount data
        $query = "SELECT shcnostaff, shcwaiver, shctotal 
                 FROM staffheadcount 
                 WHERE deptunitcode = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        $headcount = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$headcount) {
            throw new Exception("No headcount data found for department unit");
        }

        // Get current employee count
        $query = "SELECT COUNT(*) as current_count 
                 FROM employeetbl 
                 WHERE deptunitcode = ? AND status = 'Active'";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        $currentEmployees = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get sum of pending requests
        $query = "SELECT COALESCE(SUM(novacpost), 0) as pending_count 
                 FROM staffrequest 
                 WHERE deptunitcode = ? AND status = 'pending'";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        $pendingRequests = $stmt->fetch(PDO::FETCH_ASSOC);

        $currentCount = intval($currentEmployees['current_count']);
        $pendingCount = intval($pendingRequests['pending_count']);
        $shcnostaff = intval($headcount['shcnostaff']);

        // Calculate available positions
        $availablePositions = $shcnostaff - ($currentCount + $pendingCount);

        // Throw exception if requested positions exceed available positions
        if ($requestedPositions > $availablePositions) {
            throw new Exception("Cannot request {$requestedPositions} positions. Only {$availablePositions} positions available based on headcount limits.");
        }

        return true;
    }

    // Add a method to get available positions (useful for frontend display)
    public function getAvailablePositions($deptunitcode)
    {
        // Get headcount data
        $query = "SELECT shcnostaff, shcwaiver, shctotal 
                 FROM staffheadcount 
                 WHERE deptunitcode = ?";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        $headcount = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$headcount) {
            return 0;
        }

        // Get current employee count
        $query = "SELECT COUNT(*) as current_count 
                 FROM employeetbl 
                 WHERE deptunitcode = ? AND status = 'Active'";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        $currentEmployees = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get sum of pending requests
        $query = "SELECT COALESCE(SUM(novacpost), 0) as pending_count 
                 FROM staffrequest 
                 WHERE deptunitcode = ? AND status = 'pending'";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        $pendingRequests = $stmt->fetch(PDO::FETCH_ASSOC);

        $currentCount = intval($currentEmployees['current_count']);
        $pendingCount = intval($pendingRequests['pending_count']);
        $shcnostaff = intval($headcount['shcnostaff']);

        // Available positions = shcnostaff - (current employees + pending requests)
        $availablePositions = $shcnostaff - ($currentCount + $pendingCount);
        return max(0, $availablePositions); // Never return negative values
    }

    public function getDepartmentUnits()
    {
        $query = "SELECT deptunitcode, deptunitname 
                 FROM departmentunit 
                 WHERE status = 'Active'";

        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $output = "";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $output .= "<option value='" . htmlspecialchars($row['deptunitcode']) . "'>"
                . htmlspecialchars($row['deptunitname']) . "</option>";
        }
        return $output;
    }

    private function getActiveRequests($deptunitcode)
    {
        // Get current employees count
        $query = "SELECT COUNT(*) as current_count 
                 FROM employeetbl 
                 WHERE deptunitcode = ? AND status = 'Active'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        $currentEmployees = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get sum of pending novacpost
        $query = "SELECT COALESCE(SUM(novacpost), 0) as pending_count 
                 FROM staffrequest 
                 WHERE deptunitcode = ? AND status = 'pending'";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        $pendingRequests = $stmt->fetch(PDO::FETCH_ASSOC);

        // Get shcnostaff
        $query = "SELECT shcnostaff 
                 FROM staffheadcount 
                 WHERE deptunitcode = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        $headcount = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$headcount) {
            throw new Exception("No headcount data found for department unit");
        }

        return intval($currentEmployees['current_count']) +
            intval($pendingRequests['pending_count']) -
            intval($headcount['shcnostaff']);
    }

    public function getRequestsByDepartment($deptunitcode)
    {
        $query = "SELECT * FROM staffrequest 
                  WHERE deptunitcode = ? 
                  ORDER BY createdon DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
