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

    // Save draft request team lead
    public function saveTeamLeadDraftRequest($data)
    {
        try {
            $this->db->beginTransaction();

            // Convert staffPerStation to array if it's a string
            if (is_string($data['staffPerStation'])) {
                $data['staffPerStation'] = explode(',', $data['staffPerStation']);
            }

            // Calculate total staff from station requests
            $totalStaff = array_sum(array_map('intval', $data['staffPerStation']));
            $subdeptunitcode = getCurrentUser('subdeptunitcode');

            // Insert/Update main request
            $query = "INSERT INTO staffrequest 
                 (jdrequestid, jdtitle, novacpost, deptunitcode, subdeptunitcode, status, createdby, dandt, staffid) 
                 VALUES (?, ?, ?, ?, ?, 'draft', ?, NOW(), ?)
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
                $subdeptunitcode,
                $data['createdby'],
                $data['staffid']
            ]);

            // Delete existing station requests for this jdrequestid
            $deleteQuery = "DELETE FROM staffrequestperstation WHERE jdrequestid = ?";
            $stmt = $this->db->prepare($deleteQuery);
            $stmt->execute([$data['jdrequestid']]);

            // Insert new station requests
            foreach ($data['stations'] as $index => $station) {
                if (empty($station)) continue;

                $query = "INSERT INTO staffrequestperstation 
                     (jdrequestid, station, employmenttype, staffperstation, status, createdby, dandt, staffid)
                     VALUES (?, ?, ?, ?, 'draft', ?, NOW(), ?)";

                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    $data['jdrequestid'],
                    $station,
                    $data['employmentTypes'][$index],
                    $data['staffPerStation'][$index],
                    $data['createdby'],
                    $data['staffid']
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error saving team lead draft: " . $e->getMessage());
        }
    }

    //save draft request
    public function saveDraftRequest()
    {
        return $this->saveTeamLeadDraftRequest($_POST);
    }


    // Save station request
    public function saveStationRequest($jdrequestid, $station, $employmenttype, $staffperstation, $createdby)
    {
        try {
            $mainStatus = $this->getRequestStatus($jdrequestid);
            if ($mainStatus === 'pending') {
                throw new Exception("Cannot modify a pending request");
            }

            $subrequestid = $jdrequestid . $station;

            $sql = "INSERT INTO staffrequestperstation 
                    (jdrequestid, subrequestid, station, employmenttype, staffperstation, status, createdby)
                    VALUES (:jdrequestid, :subrequestid, :station, :employmenttype, :staffperstation, 'draft', :createdby)
                    ON DUPLICATE KEY UPDATE
                    employmenttype = VALUES(employmenttype),
                    staffperstation = VALUES(staffperstation)";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':jdrequestid' => $jdrequestid,
                ':subrequestid' => $subrequestid,
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
                        hrmodifiedby = :hrmodifiedby,
                        hrmodifieddandt = NOW()
                    WHERE jdrequestid = :jdrequestid 
                    AND station = :station";

            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([
                ':status' => $status,
                ':reason' => $reason,
                ':hrmodifiedby' => $_SESSION['email'] ?? DEFAULT_CREATED_BY,
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

    /*
    public function getJobTitles()
    {
        try {
            $query = "SELECT DISTINCT jdtitle FROM staffrequest ORDER BY jdtitle";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $titles = $stmt->fetchAll(PDO::FETCH_COLUMN);

            $output = '';
            foreach ($titles as $title) {
                $output .= "<option value='" . htmlspecialchars($title) . "'>" . htmlspecialchars($title) . "</option>";
            }
            return $output;
        } catch (Exception $e) {
            throw new Exception("Error getting job titles: " . $e->getMessage());
        }
    } */

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

        // Get sum of processed requests
        $query = "SELECT COALESCE(SUM(novacpost), 0) as processed_count 
                 FROM staffrequest 
                 WHERE deptunitcode = ? AND status = 'processed'";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        $processedRequests = $stmt->fetch(PDO::FETCH_ASSOC);

        $currentCount = intval($currentEmployees['current_count']);
        $pendingCount = intval($pendingRequests['pending_count']);
        $processedCount = intval($processedRequests['processed_count']);
        $shcnostaff = intval($headcount['shcnostaff']);
        // Calculate available positions
        $availablePositions = $shcnostaff - ($currentCount + $pendingCount + $processedCount);

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

        // Get sum of processed requests
        $query = "SELECT COALESCE(SUM(novacpost), 0) as processed_count 
                 FROM staffrequest 
                 WHERE deptunitcode = ? AND status = 'processed'";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$deptunitcode]);
        $processedRequests = $stmt->fetch(PDO::FETCH_ASSOC);

        $currentCount = intval($currentEmployees['current_count']);
        $pendingCount = intval($pendingRequests['pending_count']);
        $processedCount = intval($processedRequests['processed_count']);
        $shcnostaff = intval($headcount['shcnostaff']);

        // Available positions = shcnostaff - (current employees + pending requests + processed requests)
        $availablePositions = $shcnostaff - ($currentCount + $pendingCount + $processedCount);
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
            $query = "SELECT sr.jdrequestid, sr.deptunitcode, sr.jdtitle, sr.novacpost, sr.status
                     FROM staffrequest sr
                     WHERE sr.status = 'pending'
                     ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return $requests;
        } catch (Exception $e) {
            throw new Exception("Error fetching pending requests: " . $e->getMessage());
        }
    }

    public function getJobTitleDetails($jdtitle)
    {
        try {
            $query = "SELECT * FROM jobtitletbl WHERE jdtitle = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$jdtitle]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching job title details: " . $e->getMessage());
        }
    }

    public function getStationRequests($jdrequestid)
    {
        try {
            $query = "SELECT station, employmenttype, staffperstation, status, reason 
                     FROM staffrequestperstation 
                     WHERE jdrequestid = :jdrequestid";

            $stmt = $this->db->prepare($query);
            $stmt->execute([':jdrequestid' => $jdrequestid]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching station requests: " . $e->getMessage());
        }
    }

    public function updateStationStatus($jdrequestid, $station, $status, $reason = null)
    {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE staffrequestperstation 
                    SET status = :status, reason = :reason 
                    WHERE jdrequestid = :jdrequestid AND station = :station";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':jdrequestid' => $jdrequestid,
                ':station' => $station,
                ':status' => $status,
                ':reason' => $reason
            ]);

            // Update main request status
            $this->updateMainRequestStatus($jdrequestid);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error updating status: " . $e->getMessage());
        }
    }

    public function updateStaffRequest($jdrequestid, $jdtitle, $novacpost)
    {
        try {
            $this->db->beginTransaction();

            $sql = "UPDATE staffrequest 
                SET jdtitle = ?, novacpost = ? 
                WHERE jdrequestid = ? AND status = 'draft'";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$jdtitle, $novacpost, $jdrequestid]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error updating request: " . $e->getMessage());
        }
    }

    public function deleteStationRequest($jdrequestid, $station)
    {
        try {
            $sql = "DELETE FROM staffrequestperstation 
                    WHERE jdrequestid = :jdrequestid 
                    AND station = :station 
                    AND status = 'draft'";

            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':jdrequestid' => $jdrequestid,
                ':station' => $station
            ]);
        } catch (Exception $e) {
            throw new Exception("Error deleting station request: " . $e->getMessage());
        }
    }

    public function getDeptUnitLeadInfo($staffid)
    {
        $query = "SELECT e.*, s.deptunitname
                  FROM employeetbl e
                  JOIN departmentunit s ON e.deptunitcode = s.deptunitcode
                  WHERE e.staffid = ? AND e.position = 'DeptUnitLead' AND e.status = 'Active'";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$staffid]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
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

    public function getSubunitRequests($subdeptunitcode)
    {
        $query = "SELECT 
                sr.jdrequestid, 
                sr.jdtitle, 
                sr.novacpost,
                sr.status AS request_status, -- Explicitly fetch status from staffrequest
                (SELECT SUM(staffperstation) 
                 FROM staffrequestperstation 
                 WHERE jdrequestid = sr.jdrequestid) as total_positions,
                a.status AS approval_status,
                a.approvallevel
            FROM staffrequest sr
            LEFT JOIN approvaltbl a ON sr.jdrequestid = a.jdrequestid -- Use LEFT JOIN for optional approvals
            WHERE sr.subdeptunitcode = ?
            AND (a.approvallevel IN ('TeamLead', 'DeptUnitLead', 'HOD') OR a.approvallevel IS NULL)
            ORDER BY sr.dandt DESC";

        $stmt = $this->db->prepare($query);
        $stmt->execute([$subdeptunitcode]);
        $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (empty($requests)) {
            return "<tr><td colspan='5' class='text-center'>No requests found</td></tr>";
        }

        $output = "<table class='table table-bordered'>
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
            // Prioritize showing the request status; otherwise, fallback to approval status
            $status = $request['request_status'] ?? $request['approval_status'] ?? 'Unknown';
            $output .= "<tr>
                            <td>{$request['jdrequestid']}</td>
                            <td>{$request['jdtitle']}</td>
                            <td>{$request['total_positions']}</td>
                            <td>{$status}</td>
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


    private function getApprovalStatus($request)
    {
        $status = '';
        switch ($request['approval_status']) {
            case 'draft':
                $status = 'Draft';
                break;
            case 'pending':
                $status = 'Pending ' . $request['approvallevel'] . ' Approval';
                break;
            case 'approved':
                if ($request['approvallevel'] === 'HOD') {
                    $status = 'Approved';
                } else {
                    $status = 'Pending ' . $this->getNextLevel($request['approvallevel']) . ' Approval';
                }
                break;
            case 'declined':
                $status = 'Declined by ' . $request['approvallevel'];
                break;
        }
        return $status;
    }

    private function getNextLevel($currentLevel)
    {
        $levels = [
            'TeamLead' => 'DeptUnitLead',
            'DeptUnitLead' => 'HOD',
            'HOD' => 'HR'
        ];
        return $levels[$currentLevel] ?? $currentLevel;
    }

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

    public function createTeamLeadRequest($data)
    {
        try {
            $this->db->beginTransaction();

            // Calculate total staff from station requests
            $totalStaff = array_sum($data['staffPerStation']);

            // Insert into staffrequest with draft status and staffid
            $query = "INSERT INTO staffrequest (jdrequestid, jdtitle, novacpost, deptunitcode, 
                     subdeptunitcode, status, createdby, staffid) 
                     VALUES (?, ?, ?, ?, ?, 'draft', ?, ?)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $data['jdrequestid'],
                $data['jdtitle'],
                $totalStaff,
                $data['deptunitcode'],
                $data['subdeptunitcode'],
                $data['createdby'],
                $data['staffid']  // Add staffid here
            ]);

            // Insert station details
            foreach ($data['stations'] as $index => $station) {
                $query = "INSERT INTO staffrequestperstation (jdrequestid, station, employmenttype, staffperstation, status, createdby)
                         VALUES (?, ?, ?, ?, 'draft', ?)";
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
            return $data['jdrequestid'];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
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
            // Debug log
            error_log("Fetching requests for deptunitcode: " . $deptunitcode);

            $query = "SELECT sr.*, e.staffname as requestor
                     FROM staffrequest sr
                     JOIN employeetbl e ON sr.staffid = e.staffid
                     WHERE sr.deptunitcode = ?
                     ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$deptunitcode]);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug log
            error_log("Found " . count($requests) . " requests");

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
                $output .= "<tr>
                            <td>{$request['jdrequestid']}</td>
                            <td>{$request['jdtitle']}</td>
                            <td>{$request['novacpost']}</td>
                            <td>{$request['subdeptunitcode']}</td>
                            <td>{$request['requestor']}</td>
                            <td>{$request['status']}</td>
                            <td>
                                <button class='btn btn-sm btn-primary' 
                                        onclick='viewDeptUnitLeadRequest(\"{$request['jdrequestid']}\")'>
                                    View
                                </button>
                            </td>
                        </tr>";
            }

            $output .= "</tbody></table>";
            return $output;
        } catch (Exception $e) {
            return "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
        }
    }

    public function getDeptUnitLeadRequestDetails($jdrequestid)
    {
        try {
            // Get main request details
            $requestQuery = "SELECT r.*, d.deptunitname, e.staffname as requestor, 
                         j.jdtitle, j.jddescription, j.eduqualification, 
                         j.proqualification, j.workrelation, 
                         j.jdcondition, j.agebracket, j.personspec, 
                         j.fuctiontech, j.managerial, j.behavioural
                         FROM staffrequest r
                         JOIN departmentunit d ON r.deptunitcode = d.deptunitcode
                         JOIN employeetbl e ON r.staffid = e.staffid
                         LEFT JOIN jobtitletbl j ON r.jdtitle = j.jdtitle
                         WHERE r.jdrequestid = ?";

            $stmt = $this->db->prepare($requestQuery);
            $stmt->execute([$jdrequestid]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                return "<div class='alert alert-danger'>Request not found</div>";
            }

            // Get station details
            $stationQuery = "SELECT * FROM staffrequestperstation WHERE jdrequestid = ?";
            $stationStmt = $this->db->prepare($stationQuery);
            $stationStmt->execute([$jdrequestid]);
            $stations = $stationStmt->fetchAll(PDO::FETCH_ASSOC);

            // Build the output HTML
            $output = "<div class='request-details'>";
            $output .= "<h5><strong>Job Title:</strong> {$request['jdtitle']}</h5>";
            $output .= "<p><strong>Request ID:</strong> {$request['jdrequestid']}</p>";
            $output .= "<p><strong>Department Unit:</strong> {$request['deptunitname']}</p>";
            $output .= "<p><strong>Status:</strong> {$request['status']}</p>";
            $output .= "<p><strong>Requestor:</strong> {$request['requestor']}</p>";
            $output .= "<p><strong>Date:</strong> " . date('Y-m-d', strtotime($request['dandt'])) . "</p>";

            // Add station details with approve/decline buttons
            $output .= "<div class='stations-info mt-4'>";
            $output .= "<h6>Station Requests</h6>";
            $output .= "<table class='table table-bordered'>";
            $output .= "<thead><tr><th>Station</th><th>Employment Type</th><th>Staff Count</th><th>Status</th><th>Actions</th></tr></thead><tbody>";

            foreach ($stations as $station) {
                $output .= "<tr>";
                $output .= "<td>{$station['station']}</td>";
                $output .= "<td>{$station['employmenttype']}</td>";
                $output .= "<td>{$station['staffperstation']}</td>";
                $output .= "<td>{$station['status']}</td>";
                if ($station['status'] === 'pending') {
                    $output .= "<td>
                        <button class='btn btn-sm btn-success me-2' onclick='approveStation(\"{$request['jdrequestid']}\", \"{$station['station']}\")'>Approve</button>
                        <button class='btn btn-sm btn-danger' onclick='declineStation(\"{$request['jdrequestid']}\", \"{$station['station']}\")'>Decline</button>
                    </td>";
                } else {
                    $output .= "<td>Already processed</td>";
                }
                $output .= "</tr>";
            }

            $output .= "</tbody></table></div>";

            // Add additional details
            $output .= "<div class='job-details mt-3'>";
            $output .= "<h6><strong>Job Details</strong></h6>";
            $output .= "<p><strong>Professional Qualification:</strong> {$request['proqualification']}</p>";
            $output .= "<p><strong>Work Relation:</strong> {$request['workrelation']}</p>";
            $output .= "<p><strong>Job Condition:</strong> {$request['jdcondition']}</p>";
            $output .= "<p><strong>Age Bracket:</strong> {$request['agebracket']}</p>";
            $output .= "<p><strong>Person Specification:</strong> {$request['personspec']}</p>";
            $output .= "<p><strong>Functional/Technical Skills:</strong> {$request['fuctiontech']}</p>";
            $output .= "<p><strong>Managerial Skills:</strong> {$request['managerial']}</p>";
            $output .= "<p><strong>Behavioral Skills:</strong> {$request['behavioural']}</p>";
            $output .= "</div>";


            $output .= "</div>";
            return $output;
        } catch (Exception $e) {
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
    /* END TEAMLEAD FUNCTIONS */

    /* START DEPT UNIT LEAD FUNCTIONS */
    public function getDeptUnitLeadAvailablePositions($deptunitcode)
    {
        try {
            // Get the total number of employees in the department unit
            $employeeQuery = "SELECT COUNT(*) as totalEmployees 
                              FROM employeetbl e
                              WHERE e.deptunitcode = ? 
                              AND e.status = 'Active'";  // Only count active employees
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
}
