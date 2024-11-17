<?php
class Revenue
{
    protected $db;

    public function __construct($con)
    {
        $this->db = $con;
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

    // Save draft request
    public function saveDraftRequest($jdrequestid, $jdtitle, $novacpost, $deptunitcode, $createdby)
    {
        try {
            // Validate available positions
            $availablePositions = $this->getAvailablePositions($deptunitcode);
            if ($novacpost > $availablePositions) {
                throw new Exception("Requested positions exceed available positions");
            }

            $sql = "INSERT INTO staffrequest (jdrequestid, jdtitle, novacpost, deptunitcode, status, createdby)
                    VALUES (:jdrequestid, :jdtitle, :novacpost, :deptunitcode, 'draft', :createdby)
                    ON DUPLICATE KEY UPDATE
                    jdtitle = :jdtitle,
                    novacpost = :novacpost";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':jdrequestid' => $jdrequestid,
                ':jdtitle' => $jdtitle,
                ':novacpost' => $novacpost,
                ':deptunitcode' => $deptunitcode,
                ':createdby' => $createdby
            ]);
        } catch (Exception $e) {
            throw new Exception("Error saving draft: " . $e->getMessage());
        }
    }

    // Save station request
    public function saveStationRequest($jdrequestid, $station, $employmenttype, $staffperstation, $createdby)
    {
        try {
            // Get main request status
            $mainStatus = $this->getRequestStatus($jdrequestid);
            if ($mainStatus === 'pending') {
                throw new Exception("Cannot modify a pending request");
            }

            $sql = "INSERT INTO staffrequestperstation 
                    (jdrequestid, station, employmenttype, staffperstation, status, createdby)
                    VALUES (:jdrequestid, :station, :employmenttype, :staffperstation, 'draft', :createdby)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':jdrequestid' => $jdrequestid,
                ':station' => $station,
                ':employmenttype' => $employmenttype,
                ':staffperstation' => $staffperstation,
                ':createdby' => $createdby
            ]);
        } catch (Exception $e) {
            throw new Exception("Error saving station request: " . $e->getMessage());
        }
    }

    // Submit final request
    public function submitRequest($jdrequestid)
    {
        try {
            $this->db->beginTransaction();

            // Get request details first
            $request = $this->getRequestDetails($jdrequestid);

            // Validate total staff count matches novacpost
            $this->validateStaffCount($request['novacpost'], $this->getAllStationRequests($jdrequestid));

            // Update main request status
            $sql1 = "UPDATE staffrequest SET status = 'pending' WHERE jdrequestid = :jdrequestid";
            $stmt1 = $this->db->prepare($sql1);
            $stmt1->execute([':jdrequestid' => $jdrequestid]);

            // Update all station requests status
            $sql2 = "UPDATE staffrequestperstation SET status = 'pending' 
                    WHERE jdrequestid = :jdrequestid AND status = 'draft'";
            $stmt2 = $this->db->prepare($sql2);
            $stmt2->execute([':jdrequestid' => $jdrequestid]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error submitting request: " . $e->getMessage());
        }
    }

    // HR Methods
    public function updateStationRequestStatus($jdrequestid, $station, $status, $reason = null)
    {
        try {
            // Validate status
            $validStatuses = ['approved', 'declined', 'pending'];
            if (!in_array($status, $validStatuses)) {
                throw new Exception("Invalid status provided");
            }

            $sql = "UPDATE staffrequestperstation 
                    SET status = :status, 
                        reason = :reason,
                        modifiedby = :modifiedby,
                        modifieddandt = NOW()
                    WHERE jdrequestid = :jdrequestid 
                    AND station = :station";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':status' => $status,
                ':reason' => $reason,
                ':modifiedby' => $_SESSION['email'] ?? DEFAULT_CREATED_BY,
                ':jdrequestid' => $jdrequestid,
                ':station' => $station
            ]);

            if ($result) {
                // Update main request status if all sub-requests are processed
                $this->updateMainRequestStatus($jdrequestid);
            }

            return $result;
        } catch (Exception $e) {
            throw new Exception("Error updating status: " . $e->getMessage());
        }
    }

    // Get request summary for display
    public function getRequestSummary($jdrequestid, $format = 'json')
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

        if ($format === 'html') {
            return "<div class='request-summary'>
                        <p>Approved: {$result['approved']}</p>
                        <p>Declined: {$result['declined']}</p>
                        <p>Pending: {$result['pending']}</p>
                    </div>";
        }

        return $result;
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
        try {
            // Get main request details
            $mainQuery = "SELECT * FROM staffrequest WHERE jdrequestid = ?";
            $mainStmt = $this->db->prepare($mainQuery);
            $mainStmt->execute([$jdrequestid]);
            $request = $mainStmt->fetch(PDO::FETCH_ASSOC);

            if ($request) {
                // Get station requests
                $stationQuery = "SELECT * FROM staffrequestperstation WHERE jdrequestid = ?";
                $stationStmt = $this->db->prepare($stationQuery);
                $stationStmt->execute([$jdrequestid]);
                $request['stations'] = $stationStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $request;
        } catch (Exception $e) {
            throw new Exception("Error getting request details: " . $e->getMessage());
        }
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
        try {
            // First get all main requests for the department
            $query = "SELECT sr.*, 
                      (SELECT COUNT(*) FROM staffrequestperstation WHERE jdrequestid = sr.jdrequestid) as station_count,
                      (SELECT SUM(staffperstation) FROM staffrequestperstation WHERE jdrequestid = sr.jdrequestid) as total_staff
                      FROM staffrequest sr
                      WHERE sr.deptunitcode = ? 
                      ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$deptunitcode]);
            $mainRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // For each main request, get its station requests
            foreach ($mainRequests as &$request) {
                $stationQuery = "SELECT station, employmenttype, staffperstation, status 
                               FROM staffrequestperstation 
                               WHERE jdrequestid = ?";

                $stationStmt = $this->db->prepare($stationQuery);
                $stationStmt->execute([$request['jdrequestid']]);
                $request['stations'] = $stationStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $mainRequests;
        } catch (Exception $e) {
            throw new Exception("Error fetching requests: " . $e->getMessage());
        }
    }

    public function createStaffRequest($requestId, $jdtitle, $novacpost, $deptunitcode, $status, $createdby)
    {
        $sql = "INSERT INTO staffrequest (jdrequestid, jdtitle, novacpost, deptunitcode, status, createdby) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$requestId, $jdtitle, $novacpost, $deptunitcode, $status, $createdby]);
    }

    public function createStaffRequestPerStation($requestId, $station, $employmenttype, $staffperstation, $status, $createdby)
    {
        $sql = "INSERT INTO staffrequestperstation (jdrequestid, station, employmenttype, staffperstation, status, createdby) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$requestId, $station, $employmenttype, $staffperstation, $status, $createdby]);
    }

    public function getPendingRequests()
    {
        try {
            // Get all requests with pending status
            $query = "SELECT sr.* 
                     FROM staffrequest sr 
                     WHERE sr.status = 'pending'
                     ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // For each main request, get its station requests
            foreach ($requests as &$request) {
                $stationQuery = "SELECT station, employmenttype, staffperstation, status, reason 
                               FROM staffrequestperstation 
                               WHERE jdrequestid = ?";

                $stationStmt = $this->db->prepare($stationQuery);
                $stationStmt->execute([$request['jdrequestid']]);
                $request['stations'] = $stationStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $requests;
        } catch (Exception $e) {
            throw new Exception("Error fetching pending requests: " . $e->getMessage());
        }
    }
}
