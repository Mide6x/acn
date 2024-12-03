<?php
class HOD
{
    private $db;

    public function __construct($con)
    {
        $this->db = $con;
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
            $stmt->execute(['requestId' => $requestId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
}
