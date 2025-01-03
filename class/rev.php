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

            // Calculate total staff
            $totalStaff = intval($data['staffperstation']);

            // Insert into staffrequest
            $query = "INSERT INTO staffrequest (
                jdrequestid, 
                jdtitle, 
                novacpost, 
                deptunitcode, 
                status, 
                createdby
            ) VALUES (?, ?, ?, ?, 'draft', ?)";

            $stmt = $this->db->prepare($query);
            $stmt->execute([
                $data['jdrequestid'],
                $data['jdtitle'],
                $totalStaff,
                $data['deptunitcode'],
                $data['createdby']
            ]);

            // Insert into staffrequestperstation
            $stationQuery = "INSERT INTO staffrequestperstation (
                jdrequestid,
                station,
                employmenttype,
                staffperstation,
                status,
                createdby
            ) VALUES (?, ?, ?, ?, 'draft', ?)";

            $stationStmt = $this->db->prepare($stationQuery);
            $stationStmt->execute([
                $data['jdrequestid'],
                $data['station'],
                $data['employmenttype'],
                $data['staffperstation'],
                $data['createdby']
            ]);

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
            $query = "INSERT INTO staffrequestperstation 
                    (jdrequestid, station, employmenttype, staffperstation, status, createdby) 
                    VALUES (?, ?, ?, ?, 'draft', ?)";

            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                $jdrequestid,
                $station,
                $employmenttype,
                $staffperstation,
                $createdby
            ]);
        } catch (Exception $e) {
            error_log("Error saving station request: " . $e->getMessage());
            throw new Exception("Error saving station request");
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

    // Add these methods to the Revenue class

    public function getAllDepartments()
    {
        try {
            $query = "SELECT departmentcode, departmentname, status 
                     FROM departments 
                     ORDER BY departmentname";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching departments: " . $e->getMessage());
        }
    }

    public function toggleDepartmentStatus($departmentcode)
    {
        try {
            $this->db->beginTransaction();
            
            // Get current status
            $query = "SELECT status FROM departments WHERE departmentcode = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$departmentcode]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Toggle status
            $newStatus = ($result['status'] === 'Active') ? 'Inactive' : 'Active';
            
            // Update status
            $updateQuery = "UPDATE departments 
                           SET status = ?, 
                               modifiedby = ?, 
                               modifieddandt = CURRENT_TIMESTAMP 
                           WHERE departmentcode = ?";
            
            $stmt = $this->db->prepare($updateQuery);
            $stmt->execute([$newStatus, $_SESSION['email'], $departmentcode]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error toggling department status: " . $e->getMessage());
        }
    }

    public function createDepartment($departmentname)
    {
        try {
            $this->db->beginTransaction();
            
            // Generate department code
            $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $departmentname), 0, 3));
            $query = "SELECT MAX(CAST(SUBSTRING(departmentcode, 4) AS UNSIGNED)) as max_seq 
                     FROM departments 
                     WHERE departmentcode LIKE ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$code . '%']);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $sequence = $result['max_seq'] ? intval($result['max_seq']) + 1 : 1;
            $departmentcode = $code . sprintf("%02d", $sequence);
            
            // Insert new department
            $insertQuery = "INSERT INTO departments (
                departmentcode, 
                departmentname, 
                status, 
                createdby, 
                createddandt
            ) VALUES (?, ?, 'Active', ?, CURRENT_TIMESTAMP)";
            
            $stmt = $this->db->prepare($insertQuery);
            $stmt->execute([
                $departmentcode,
                $departmentname,
                $_SESSION['email']
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error creating department: " . $e->getMessage());
        }
    }

    public function getAllStations()
    {
        try {
            $query = "SELECT stationcode, stationname, stationtype, operationtype, status 
                     FROM stationtbl 
                     ORDER BY stationname";
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            throw new Exception("Error fetching stations: " . $e->getMessage());
        }
    }

    public function toggleStationStatus($stationcode)
    {
        try {
            $this->db->beginTransaction();
            
            // Get current status
            $query = "SELECT status FROM stationtbl WHERE stationcode = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$stationcode]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Toggle status
            $newStatus = ($result['status'] === 'Active') ? 'Inactive' : 'Active';
            
            // Update status
            $updateQuery = "UPDATE stationtbl 
                           SET status = ?, 
                               modifiedby = ?, 
                               modifieddandt = CURRENT_TIMESTAMP 
                           WHERE stationcode = ?";
            
            $stmt = $this->db->prepare($updateQuery);
            $stmt->execute([$newStatus, $_SESSION['email'], $stationcode]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error toggling station status: " . $e->getMessage());
        }
    }

    public function createStation($stationname, $stationtype, $operationtype)
    {
        try {
            $this->db->beginTransaction();
            
            // Generate station code
            $code = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $stationname), 0, 3));
            $query = "SELECT MAX(CAST(SUBSTRING(stationcode, 4) AS UNSIGNED)) as max_seq 
                     FROM stationtbl 
                     WHERE stationcode LIKE ?";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute([$code . '%']);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $sequence = $result['max_seq'] ? intval($result['max_seq']) + 1 : 1;
            $stationcode = $code . sprintf("%02d", $sequence);
            
            // Insert new station
            $insertQuery = "INSERT INTO stationtbl (
                stationcode, 
                stationname, 
                stationtype,
                operationtype,
                status, 
                createdby, 
                createddandt
            ) VALUES (?, ?, ?, ?, 'Active', ?, CURRENT_TIMESTAMP)";
            
            $stmt = $this->db->prepare($insertQuery);
            $stmt->execute([
                $stationcode,
                $stationname,
                $stationtype,
                $operationtype,
                $_SESSION['email']
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error creating station: " . $e->getMessage());
        }
    }

    public function getAllJobTitles($page = 1, $limit = 10, $search = '')
    {
        try {
            // Base query
            $query = "SELECT j.jdtitle, j.deptunitcode, j.jddescription, j.jdstatus, d.deptunitname 
                     FROM jobtitletbl j
                     JOIN departmentunit d ON j.deptunitcode = d.deptunitcode";
            
            // Add search condition if search term is provided
            if (!empty($search)) {
                $query .= " WHERE j.jdtitle LIKE :search 
                           OR d.deptunitname LIKE :search 
                           OR j.jddescription LIKE :search";
            }
            
            // Count total records for pagination
            $countQuery = str_replace("j.jdtitle, j.deptunitcode, j.jddescription, j.jdstatus, d.deptunitname", "COUNT(*) as total", $query);
            $stmt = $this->db->prepare($countQuery);
            if (!empty($search)) {
                $searchTerm = "%{$search}%";
                $stmt->bindParam(':search', $searchTerm);
            }
            $stmt->execute();
            $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
            
            // Add ordering and pagination
            $query .= " ORDER BY j.jdtitle
                       LIMIT :offset, :limit";
            
            // Calculate offset
            $offset = ($page - 1) * $limit;
            
            // Prepare and execute main query
            $stmt = $this->db->prepare($query);
            if (!empty($search)) {
                $searchTerm = "%{$search}%";
                $stmt->bindParam(':search', $searchTerm);
            }
            $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
            $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return [
                'data' => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total_pages' => ceil($total / $limit)
            ];
        } catch (Exception $e) {
            error_log("Error in getAllJobTitles: " . $e->getMessage());
            throw new Exception("Error fetching job titles: " . $e->getMessage());
        }
    }

    public function toggleJobTitleStatus($jobtitle, $deptunitcode)
    {
        try {
            $this->db->beginTransaction();
            
            // Get current status
            $query = "SELECT jdstatus FROM jobtitletbl 
                     WHERE jdtitle = ? AND deptunitcode = ?";
            $stmt = $this->db->prepare($query);
            $stmt->execute([$jobtitle, $deptunitcode]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Toggle status
            $newStatus = ($result['jdstatus'] === 'Active') ? 'Inactive' : 'Active';
            
            // Update status
            $updateQuery = "UPDATE jobtitletbl 
                           SET jdstatus = ?, 
                               modifiedby = ?, 
                               modifieddandt = CURRENT_TIMESTAMP 
                           WHERE jdtitle = ? AND deptunitcode = ?";
            
            $stmt = $this->db->prepare($updateQuery);
            $stmt->execute([$newStatus, $_SESSION['email'], $jobtitle, $deptunitcode]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error toggling job title status: " . $e->getMessage());
        }
    }

    public function createJobTitle($jobtitle, $deptunitcode, $description)
    {
        try {
            $this->db->beginTransaction();
            
            // Check if job title already exists for this department
            $checkQuery = "SELECT COUNT(*) FROM jobtitletbl 
                          WHERE jdtitle = ? AND deptunitcode = ?";
            $stmt = $this->db->prepare($checkQuery);
            $stmt->execute([$jobtitle, $deptunitcode]);
            
            if ($stmt->fetchColumn() > 0) {
                throw new Exception("This job title already exists for the selected department");
            }
            
            // Insert new job title
            $insertQuery = "INSERT INTO jobtitletbl (
                jdtitle,
                deptunitcode,
                jddescription,
                jdstatus,
                createdby,
                dandt
            ) VALUES (?, ?, ?, 'Active', ?, CURRENT_TIMESTAMP)";
            
            $stmt = $this->db->prepare($insertQuery);
            $stmt->execute([
                $jobtitle,
                $deptunitcode,
                $description,
                $_SESSION['email']
            ]);
            
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            throw new Exception("Error creating job title: " . $e->getMessage());
        }
    }
}
