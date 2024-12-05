<?php
class HR
{
    private $db;

    public function __construct($con)
    {
        $this->db = $con;
    }

    public function getPendingRequests()
    {
        try {
            // Get all requests where HOD has approved, regardless of department
            $query = "SELECT DISTINCT sr.*, 
                            hod.status as hod_status,
                            hod.dandt as hod_date,
                            hod.comments as hod_comments,
                            hr.status as hr_status,
                            hr.dandt as hr_date,
                            hr.comments as hr_comments,
                            dept.departmentname,  -- Include department name
                            COALESCE(
                                (SELECT SUM(staffperstation) 
                                 FROM staffrequestperstation 
                                 WHERE jdrequestid = sr.jdrequestid 
                                ), 0
                            ) as approved_positions_count
                     FROM staffrequest sr
                     JOIN approvaltbl hod ON sr.jdrequestid = hod.jdrequestid 
                        AND hod.approvallevel = 'HOD'
                     JOIN approvaltbl hr ON sr.jdrequestid = hr.jdrequestid 
                        AND hr.approvallevel = 'HR'
                     LEFT JOIN departmentunit du ON sr.deptunitcode = du.deptunitcode
                     LEFT JOIN departments dept ON du.deptcode = dept.departmentcode
                     WHERE hod.status = 'approved'
                     AND (hr.status IN ('pending', 'draft'))
                     ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getPendingRequests: " . $e->getMessage());
            throw $e;
        }
    }

    public function getApprovedStationRequests($requestId)
    {
        try {
            // First, let's debug what stations exist for this request
            $debugQuery = "SELECT * FROM staffrequestperstation WHERE jdrequestid = :requestId";
            $debugStmt = $this->db->prepare($debugQuery);
            $debugStmt->execute(['requestId' => $requestId]);
            $debugResults = $debugStmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("All stations for request $requestId: " . print_r($debugResults, true));

            // Now get the stations without the status filter
            $query = "SELECT 
                        srs.station,
                        srs.employmenttype,
                        srs.staffperstation,
                        srs.status,
                        srs.dandt
                     FROM staffrequestperstation srs
                     WHERE srs.jdrequestid = :requestId";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['requestId' => $requestId]);

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Station Results without status filter: " . print_r($results, true));

            return $results;
        } catch (Exception $e) {
            error_log("Error in getApprovedStationRequests: " . $e->getMessage());
            throw $e;
        }
    }

    public function getRequestDetails($requestId)
    {
        try {
            $query = "SELECT sr.*, 
                            hod.status as hod_status,
                            hod.dandt as hod_date,
                            hod.comments as hod_comments,
                            hr.status as hr_status,
                            hr.dandt as hr_date,
                            hr.comments as hr_comments,
                            dept.departmentname,
                            jt.jddescription,
                            jt.eduqualification,
                            jt.proqualification,
                            jt.workrelation,
                            jt.jdposition,
                            jt.jdcondition,
                            jt.agebracket,
                            jt.personspec,
                            jt.fuctiontech,
                            jt.managerial,
                            jt.behavioural
                     FROM staffrequest sr
                     JOIN approvaltbl hod ON sr.jdrequestid = hod.jdrequestid 
                        AND hod.approvallevel = 'HOD'
                     JOIN approvaltbl hr ON sr.jdrequestid = hr.jdrequestid 
                        AND hr.approvallevel = 'HR'
                     LEFT JOIN departmentunit du ON sr.deptunitcode = du.deptunitcode
                     LEFT JOIN departments dept ON du.deptcode = dept.departmentcode
                     LEFT JOIN jobtitletbl jt ON sr.jdtitle = jt.jdtitle
                     WHERE sr.jdrequestid = :requestId";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['requestId' => $requestId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getRequestDetails: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateRequestStatus($requestId, $status, $comments = null)
    {
        try {
            $this->db->beginTransaction();

            // Update HR status in approvaltbl
            $hrQuery = "UPDATE approvaltbl 
                        SET status = :status,
                            comments = :comments,
                            dandt = NOW()
                        WHERE jdrequestid = :requestId 
                        AND approvallevel = 'HR'";

            $hrStmt = $this->db->prepare($hrQuery);
            $hrStmt->execute([
                'status' => $status,
                'comments' => $comments,
                'requestId' => $requestId
            ]);

            if ($status === 'approved') {
                // Check if Head of HR record exists
                $checkQuery = "SELECT COUNT(*) FROM approvaltbl 
                              WHERE jdrequestid = :requestId 
                              AND approvallevel = 'HeadofHR'";

                $checkStmt = $this->db->prepare($checkQuery);
                $checkStmt->execute(['requestId' => $requestId]);
                $exists = $checkStmt->fetchColumn();

                if (!$exists) {
                    // Insert new Head of HR record
                    $hohrQuery = "INSERT INTO approvaltbl 
                                 (jdrequestid, approvallevel, status, dandt) 
                                 VALUES 
                                 (:requestId, 'HeadofHR', 'pending', NOW())";

                    $hohrStmt = $this->db->prepare($hohrQuery);
                    $hohrStmt->execute(['requestId' => $requestId]);
                } else {
                    // Update existing Head of HR record to pending
                    $updateHohrQuery = "UPDATE approvaltbl 
                                      SET status = 'pending',
                                          dandt = NOW()
                                      WHERE jdrequestid = :requestId 
                                      AND approvallevel = 'HeadofHR'";

                    $updateHohrStmt = $this->db->prepare($updateHohrQuery);
                    $updateHohrStmt->execute(['requestId' => $requestId]);
                }
            } elseif ($status === 'declined') {
                // Update all stations for this request in staffrequestperstation
                $stationQuery = "UPDATE staffrequestperstation 
                               SET status = 'rejected',
                                   reason = :reason,
                                   dandt = NOW()
                               WHERE jdrequestid = :requestId";

                $stationStmt = $this->db->prepare($stationQuery);
                $stationStmt->execute([
                    'reason' => $comments,
                    'requestId' => $requestId
                ]);
            }

            // Commit transaction
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            // Rollback transaction on error
            $this->db->rollBack();
            error_log("Error in updateRequestStatus: " . $e->getMessage());
            throw $e;
        }
    }
}
