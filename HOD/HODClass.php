<?php
class HOD
{
    private $db;

    public function __construct($con)
    {
        $this->db = $con;
    }
    public function getApprovallevelbyjdrequestid($jdrequestid)
    {
        $approvalStages = ['HOD', 'HR', 'HeadOfHR', 'CFO', 'CEO'];
        $query = "SELECT approvallevel, status, dandt, comments 
                  FROM approvaltbl 
                  WHERE jdrequestid = ? 
                  AND approvallevel IN ('" . implode("','", $approvalStages) . "')";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$jdrequestid]);
        $approvals = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $approvalStatuses = [];
        foreach ($approvals as $approval) {
            $approvalStatuses[$approval['approvallevel']] = [
                'status' => $approval['status'],
                'date' => $approval['dandt'],
                'comments' => $approval['comments']
            ];
        }
        return $approvalStatuses;
    }


    public function generateRequestId()
    {
        try {
            $year = date('Y');
            $query = "SELECT MAX(CAST(SUBSTRING(jdrequestid, 8) AS UNSIGNED)) as max_id 
                      FROM staffrequest 
                      WHERE jdrequestid LIKE 'REQ{$year}%'";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            $nextId = ($result['max_id'] ?? 0) + 1;
            $requestId = "REQ" . $year . str_pad($nextId, 4, '0', STR_PAD_LEFT);

            error_log("Generated Request ID: " . $requestId); // Debug log
            return $requestId;
        } catch (Exception $e) {
            error_log("Error generating request ID: " . $e->getMessage());
            throw $e;
        }
    }

    public function getJobTitles()
    {
        try {
            $query = "SELECT jt.jdtitle 
                      FROM jobtitletbl jt
                      JOIN departmentunit du ON jt.deptunitcode = du.deptunitcode
                      WHERE jt.jdstatus = 'Active' 
                      AND du.deptcode = :deptcode";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['deptcode' => CURRENT_USER['departmentcode']]);

            $output = "";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $output .= "<option value='" . htmlspecialchars($row['jdtitle']) . "'>"
                    . htmlspecialchars($row['jdtitle']) . "</option>";
            }
            return $output;
        } catch (Exception $e) {
            error_log("Error in getJobTitles: " . $e->getMessage());
            throw $e;
        }
    }
    public function getHODPendingRequests($deptCode)
    {
        try {
            $query = "
                SELECT 
                    sr.jdrequestid,
                    sr.jdtitle,
                    sr.novacpost,
                    sr.status as request_status,
                    sr.dandt as request_date,
                    du.deptunitname,
                    du.deptcode,
                    GROUP_CONCAT(DISTINCT srs.station) as stations,
                    GROUP_CONCAT(DISTINCT srs.staffperstation) as staff_counts,
                    GROUP_CONCAT(DISTINCT srs.employmenttype) as employment_types,
                    GROUP_CONCAT(DISTINCT srs.status) as station_statuses,
                    a.status as approval_status
                FROM staffrequest sr
                JOIN departmentunit du ON sr.deptunitcode = du.deptunitcode
                LEFT JOIN staffrequestperstation srs ON sr.jdrequestid = srs.jdrequestid
                JOIN approvaltbl a ON sr.jdrequestid = a.jdrequestid
                WHERE du.deptcode = :deptCode
                AND a.approvallevel = 'HOD'
                AND a.status = 'pending'
                GROUP BY sr.jdrequestid
                ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                error_log("Prepare failed: " . print_r($this->db->errorInfo(), true));
                return [];
            }

            $stmt->execute(['deptCode' => $deptCode]);
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

            error_log("Found " . count($requests) . " requests for department code: " . $deptCode);
            return $requests;
        } catch (Exception $e) {
            error_log("Error in getHODPendingRequests: " . $e->getMessage());
            return [];
        }
    }

    public function getRequestDetails($requestId)
    {
        try {
            $query = "
                SELECT sr.*, srps.*, jt.*
                FROM staffrequest sr
                LEFT JOIN staffrequestperstation srps ON sr.jdrequestid = srps.jdrequestid
                LEFT JOIN jobtitletbl jt ON sr.jdtitle = jt.jdtitle
                WHERE sr.jdrequestid = :requestId";

            $stmt = $this->db->prepare($query);
            if (!$stmt) {
                error_log("Prepare failed: " . print_r($this->db->errorInfo(), true));
                return [];
            }

            $stmt->execute(['requestId' => $requestId]);
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (empty($result)) {
                error_log("No details found for request ID: " . $requestId);
                return [];
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error in getRequestDetails: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateApprovalStatus($requestId, $status, $comments)
    {
        try {
            // Start transaction
            $this->db->beginTransaction();

            // Update HOD approval status
            $hodQuery = "
                UPDATE approvaltbl 
                SET status = :status, 
                    comments = :comments
                WHERE jdrequestid = :requestId 
                AND approvallevel = 'HOD'";

            $stmt = $this->db->prepare($hodQuery);
            $hodSuccess = $stmt->execute([
                'status' => $status,
                'comments' => $comments,
                'requestId' => $requestId
            ]);

            if (!$hodSuccess) {
                throw new Exception("Failed to update HOD approval status");
            }

            // If HOD approved, update HR approval level from draft to pending
            if ($status === 'approved') {
                $hrQuery = "
                    UPDATE approvaltbl 
                    SET status = 'pending'
                    WHERE jdrequestid = :requestId 
                    AND approvallevel = 'HR'
                    AND status = 'draft'";

                $stmt = $this->db->prepare($hrQuery);
                $hrSuccess = $stmt->execute(['requestId' => $requestId]);

                if (!$hrSuccess) {
                    throw new Exception("Failed to update HR approval status");
                }
            }

            // Commit transaction
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            error_log("Error in updateApprovalStatus: " . $e->getMessage());
            return false;
        }
    }

    public function updateStationStatus($requestId, $status, $comments = '')
    {
        try {
            $this->db->beginTransaction();

            // Update HOD approval status
            $updateHODQuery = "
                UPDATE approvaltbl 
                SET status = :status,
                    comments = :comments,
                    dandt = CURRENT_TIMESTAMP
                WHERE jdrequestid = :requestId 
                AND approvallevel = 'HOD'";

            $stmtHOD = $this->db->prepare($updateHODQuery);
            $stmtHOD->execute([
                'status' => $status,
                'comments' => $comments,
                'requestId' => $requestId
            ]);

            // If HOD approves, update HR status to pending
            if ($status === 'approved') {
                $updateHRQuery = "
                    UPDATE approvaltbl 
                    SET status = 'pending'
                    WHERE jdrequestid = :requestId 
                    AND approvallevel = 'HR'";

                $stmtHR = $this->db->prepare($updateHRQuery);
                $stmtHR->execute(['requestId' => $requestId]);
            }

            // If HOD declines, update all subsequent levels to 'draft'
            if ($status === 'declined') {
                $updateSubsequentQuery = "
                    UPDATE approvaltbl 
                    SET status = 'draft'
                    WHERE jdrequestid = :requestId 
                    AND approvallevel IN ('HR', 'HeadOfHR', 'CFO', 'CEO')";

                $stmtSubsequent = $this->db->prepare($updateSubsequentQuery);
                $stmtSubsequent->execute(['requestId' => $requestId]);
            }

            // Update the main staffrequest status
            $updateMainStatus = "
                UPDATE staffrequest 
                SET status = :status
                WHERE jdrequestid = :requestId";

            $stmtMain = $this->db->prepare($updateMainStatus);
            $stmtMain->execute([
                'status' => $status,
                'requestId' => $requestId
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in updateStationStatus: " . $e->getMessage());
            throw $e;
        }
    }
    public function getHODInfo($staffid)
    {
        try {
            $query = "SELECT e.*, d.deptunitname, d.deptunitcode, d.deptcode, dept.departmentname 
                     FROM employeetbl e 
                     JOIN departmentunit d ON e.deptunitcode = d.deptunitcode 
                     JOIN departments dept ON d.deptcode = dept.departmentcode 
                     WHERE e.staffid = ? 
                     AND e.position = 'HOD' 
                     AND e.status = 'Active'";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$staffid]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getHODInfo: " . $e->getMessage());
            throw $e;
        }
    }

    public function getAvailablePositions($deptunitcode)
    {
        try {
            $query = "SELECT COUNT(*) as count 
                     FROM jobtitletbl 
                     WHERE deptunitcode = ? 
                     AND jdstatus = 'Active'";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$deptunitcode]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] ?? 0;
        } catch (Exception $e) {
            error_log("Error in getAvailablePositions: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStations()
    {
        try {
            $query = "SELECT stationcode, stationname 
                     FROM stationtbl 
                     WHERE status = 'Active' 
                     ORDER BY stationname";

            $stmt = $this->db->prepare($query);
            $stmt->execute();

            $output = "";
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $output .= "<option value='" . htmlspecialchars($row['stationcode']) . "'>"
                    . htmlspecialchars($row['stationname']) . "</option>";
            }
            return $output;
        } catch (Exception $e) {
            error_log("Error in getStations: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStaffTypes()
    {
        try {
            $types = ['Permanent', 'Contract', 'Temporary'];
            $output = "";
            foreach ($types as $type) {
                $output .= "<option value='" . htmlspecialchars($type) . "'>"
                    . htmlspecialchars($type) . "</option>";
            }
            return $output;
        } catch (Exception $e) {
            error_log("Error in getStaffTypes: " . $e->getMessage());
            throw $e;
        }
    }

    public function createHODRequest($formData)
    {
        try {
            $this->db->beginTransaction();

            $jdrequestid = $this->generateRequestId();
            $staffid = CURRENT_USER['staffid']; // Get staffid from CURRENT_USER

            // Insert into staffrequest table - added createdby field
            $query = "INSERT INTO staffrequest (jdrequestid, jdtitle, departmentcode, staffid, novacpost, status, createdby) 
                  VALUES (:jdrequestid, :jdtitle, :departmentcode, :staffid, :novacpost, 'draft', :createdby)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                'jdrequestid' => $jdrequestid,
                'jdtitle' => $formData['jdtitle'],
                'departmentcode' => CURRENT_USER['departmentcode'],
                'staffid' => $staffid,
                'novacpost' => count($formData['stations']),
                'createdby' => $staffid
            ]);

            // Insert into staffrequestperstation table - added createdby field
            foreach ($formData['stations'] as $station) {
                $query = "INSERT INTO staffrequestperstation (jdrequestid, station, employmenttype, staffperstation, createdby) 
                      VALUES (:jdrequestid, :station, :employmenttype, :staffperstation, :createdby)";
                $stmt = $this->db->prepare($query);
                $stmt->execute([
                    'jdrequestid' => $jdrequestid,
                    'station' => $station['station'],
                    'employmenttype' => $station['employmenttype'],
                    'staffperstation' => $station['staffperstation'],
                    'createdby' => $staffid
                ]);
            }

            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in createHODRequest: " . $e->getMessage());
            throw $e;
        }
    }

    public function getMyRequests($staffid)
    {
        try {
            $query = "SELECT jdrequestid, jdtitle, status, dandt 
                  FROM staffrequest 
                  WHERE staffid = :staffid 
                  ORDER BY dandt DESC";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['staffid' => $staffid]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getMyRequests: " . $e->getMessage());
            return [];
        }
    }

    public function getHODRequests($staffid)
    {
        try {
            $query = "SELECT sr.jdrequestid, sr.jdtitle, sr.status, sr.dandt, sr.novacpost,
                             GROUP_CONCAT(srs.station) as stations,
                             GROUP_CONCAT(srs.staffperstation) as staff_counts,
                             GROUP_CONCAT(srs.employmenttype) as employment_types
                      FROM staffrequest sr
                      LEFT JOIN staffrequestperstation srs ON sr.jdrequestid = srs.jdrequestid
                      WHERE sr.staffid = :staffid
                      GROUP BY sr.jdrequestid, sr.status
                      ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['staffid' => $staffid]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getHODRequests: " . $e->getMessage());
            return [];
        }
    }

    public function getJobDetails($jdtitle)
    {
        try {
            $query = "SELECT * FROM jobtitletbl WHERE jdtitle = :jdtitle";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['jdtitle' => $jdtitle]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getJobDetails: " . $e->getMessage());
            return null;
        }
    }

    public function submitHODRequest($requestId)
    {
        try {
            $this->db->beginTransaction();

            // Update staffrequest status
            $requestQuery = "UPDATE staffrequest 
                            SET status = 'pending' 
                            WHERE jdrequestid = :requestId 
                            AND status = 'draft'";

            $stmt = $this->db->prepare($requestQuery);
            $stmt->execute(['requestId' => $requestId]);

            // Update staffrequestperstation status
            $stationQuery = "UPDATE staffrequestperstation 
                            SET status = 'pending' 
                            WHERE jdrequestid = :requestId 
                            AND status = 'draft'";

            $stmt = $this->db->prepare($stationQuery);
            $stmt->execute(['requestId' => $requestId]);

            // Update approval table - set HR level to pending
            $approvalQuery = "UPDATE approvaltbl 
                             SET status = 'pending' 
                             WHERE jdrequestid = :requestId 
                             AND approvallevel = 'HR' 
                             AND status = 'draft'";

            $stmt = $this->db->prepare($approvalQuery);
            $stmt->execute(['requestId' => $requestId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in submitHODRequest: " . $e->getMessage());
            throw $e;
        }
    }

    public function getHODDepartmentRequests($deptCode)
    {
        try {
            $query = "SELECT sr.jdrequestid, sr.jdtitle, sr.status as request_status, 
                             sr.dandt as request_date, sr.novacpost,
                             du.deptunitname, du.deptcode,
                             GROUP_CONCAT(DISTINCT srs.station) as stations,
                             GROUP_CONCAT(DISTINCT srs.staffperstation) as staff_counts,
                             GROUP_CONCAT(DISTINCT srs.employmenttype) as employment_types,
                             GROUP_CONCAT(DISTINCT srs.status) as station_statuses,
                             a.status as approval_status
                      FROM staffrequest sr
                      JOIN departmentunit du ON sr.deptunitcode = du.deptunitcode
                      LEFT JOIN staffrequestperstation srs ON sr.jdrequestid = srs.jdrequestid
                      JOIN approvaltbl a ON sr.jdrequestid = a.jdrequestid
                      WHERE du.deptcode = :deptCode
                      AND a.approvallevel = 'HOD'
                      AND a.status = 'pending'
                      GROUP BY sr.jdrequestid
                      ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['deptCode' => $deptCode]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getHODDepartmentRequests: " . $e->getMessage());
            return [];
        }
    }

    public function getDepartmentRequestDetails($requestId)
    {
        try {
            $query = "SELECT sr.*, jt.*, du.deptunitname,
                             GROUP_CONCAT(srs.station) as stations,
                             GROUP_CONCAT(srs.employmenttype) as employment_types,
                             GROUP_CONCAT(srs.staffperstation) as staff_counts
                      FROM staffrequest sr
                      LEFT JOIN staffrequestperstation srs ON sr.jdrequestid = srs.jdrequestid
                      LEFT JOIN jobtitletbl jt ON sr.jdtitle = jt.jdtitle
                      LEFT JOIN departmentunit du ON sr.deptunitcode = du.deptunitcode
                      WHERE sr.jdrequestid = :requestId
                      GROUP BY sr.jdrequestid";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['requestId' => $requestId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getDepartmentRequestDetails: " . $e->getMessage());
            return null;
        }
    }

    public function approveHODDepartmentRequest($requestId, $comments = '')
    {
        try {
            $this->db->beginTransaction();

            // Update HOD approval status
            $hodQuery = "UPDATE approvaltbl 
                         SET status = 'approved',
                             comments = :comments,
                             dandt = CURRENT_TIMESTAMP
                         WHERE jdrequestid = :requestId 
                         AND approvallevel = 'HOD'";

            $stmt = $this->db->prepare($hodQuery);
            $stmt->execute([
                'comments' => $comments,
                'requestId' => $requestId
            ]);

            // Update HR status to pending
            $hrQuery = "UPDATE approvaltbl 
                        SET status = 'pending'
                        WHERE jdrequestid = :requestId 
                        AND approvallevel = 'HR'";

            $stmt = $this->db->prepare($hrQuery);
            $stmt->execute(['requestId' => $requestId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in approveHODDepartmentRequest: " . $e->getMessage());
            throw $e;
        }
    }

    public function declineHODDepartmentRequest($requestId, $comments)
    {
        try {
            $this->db->beginTransaction();

            // Update HOD approval status
            $hodQuery = "UPDATE approvaltbl 
                         SET status = 'declined',
                             comments = :comments,
                             dandt = CURRENT_TIMESTAMP
                         WHERE jdrequestid = :requestId 
                         AND approvallevel = 'HOD'";

            $stmt = $this->db->prepare($hodQuery);
            $stmt->execute([
                'comments' => $comments,
                'requestId' => $requestId
            ]);

            // Set subsequent approval levels to draft
            $updateQuery = "UPDATE approvaltbl 
                           SET status = 'draft'
                           WHERE jdrequestid = :requestId 
                           AND approvallevel IN ('HR', 'HeadOfHR', 'CFO', 'CEO')";

            $stmt = $this->db->prepare($updateQuery);
            $stmt->execute(['requestId' => $requestId]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in declineHODDepartmentRequest: " . $e->getMessage());
            throw $e;
        }
    }

    public function getApprovalWorkflow($requestId)
    {
        try {
            $query = "SELECT approvallevel, status, dandt, comments 
                      FROM approvaltbl 
                      WHERE jdrequestid = :requestId 
                      ORDER BY FIELD(approvallevel, 'HR', 'HeadOfHR', 'CFO', 'CEO')";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['requestId' => $requestId]);

            $approvals = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $approvals[$row['approvallevel']] = [
                    'status' => $row['status'],
                    'date' => $row['dandt'],
                    'comments' => $row['comments']
                ];
            }

            return $approvals;
        } catch (Exception $e) {
            error_log("Error in getApprovalWorkflow: " . $e->getMessage());
            throw $e;
        }
    }

    public function getRequestStatus($requestId)
    {
        try {
            $query = "SELECT status FROM staffrequest WHERE jdrequestid = :requestId";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['requestId' => $requestId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['status'] : null;
        } catch (Exception $e) {
            error_log("Error in getRequestStatus: " . $e->getMessage());
            throw $e;
        }
    }
}
