<?php
class HR
{
    private $db;

    public function __construct($con)
    {
        $this->db = $con;
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

    public function getAvailablePositions($deptunitcode)
    {
        try {
            // Get the department code from the department unit code
            $deptQuery = "SELECT deptcode FROM departmentunit WHERE deptunitcode = ?";
            $deptStmt = $this->db->prepare($deptQuery);
            $deptStmt->execute([$deptunitcode]);
            $deptResult = $deptStmt->fetch(PDO::FETCH_ASSOC);
            $deptCode = $deptResult['deptcode'];

            // Get the deptnostaff value from the departments table
            $deptNoStaffQuery = "SELECT deptnostaff FROM departments WHERE departmentcode = ?";
            $deptNoStaffStmt = $this->db->prepare($deptNoStaffQuery);
            $deptNoStaffStmt->execute([$deptCode]);
            $deptNoStaffResult = $deptNoStaffStmt->fetch(PDO::FETCH_ASSOC);
            $deptNoStaff = $deptNoStaffResult['deptnostaff'];

            // Get the count of active staff in the department
            $activeStaffQuery = "SELECT COUNT(*) as count FROM employeetbl WHERE deptunitcode = ? AND status = 'Active'";
            $activeStaffStmt = $this->db->prepare($activeStaffQuery);
            $activeStaffStmt->execute([$deptunitcode]);
            $activeStaffResult = $activeStaffStmt->fetch(PDO::FETCH_ASSOC);
            $activeStaffCount = $activeStaffResult['count'];

            // Get the count of pending staff requests for the department
            $pendingRequestsQuery = "SELECT COUNT(*) as count FROM staffrequest WHERE deptunitcode = ? AND status = 'pending'";
            $pendingRequestsStmt = $this->db->prepare($pendingRequestsQuery);
            $pendingRequestsStmt->execute([$deptunitcode]);
            $pendingRequestsResult = $pendingRequestsStmt->fetch(PDO::FETCH_ASSOC);
            $pendingRequestsCount = $pendingRequestsResult['count'];

            // Calculate available positions
            $availablePositions = $deptNoStaff - ($activeStaffCount + $pendingRequestsCount);

            return $availablePositions;
        } catch (Exception $e) {
            error_log("Error in getAvailablePositions: " . $e->getMessage());
            throw $e;
        }
    }
    public function getHRInfo($staffid)
    {
        try {
            $query = "SELECT e.*, d.deptunitname, d.deptunitcode, d.deptcode, dept.departmentname 
                     FROM employeetbl e 
                     JOIN departmentunit d ON e.deptunitcode = d.deptunitcode 
                     JOIN departments dept ON d.deptcode = dept.departmentcode 
                     WHERE e.staffid = ? 
                     AND e.status = 'Active'";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$staffid]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                // If no HR info found, return default HR department info
                $defaultQuery = "SELECT d.deptunitname, d.deptunitcode, d.deptcode, dept.departmentname 
                               FROM departmentunit d 
                               JOIN departments dept ON d.deptcode = dept.departmentcode 
                               WHERE d.deptcode = 'HRD' 
                               LIMIT 1";

                $defaultStmt = $this->db->prepare($defaultQuery);
                $defaultStmt->execute();
                $defaultResult = $defaultStmt->fetch(PDO::FETCH_ASSOC);

                if (!$defaultResult) {
                    throw new Exception("No HR department information found");
                }

                return array_merge([
                    'staffid' => $staffid,
                    'position' => 'HR',
                    'email' => CURRENT_USER['email'] ?? 'hr@acn.aero'
                ], $defaultResult);
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error in getHRInfo: " . $e->getMessage());
            throw $e;
        }
    }
    public function getPendingRequests()
    {
        try {
            // Get all requests with HR pending, regardless of HOD approval status
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
                     LEFT JOIN approvaltbl hod ON sr.jdrequestid = hod.jdrequestid 
                        AND hod.approvallevel = 'HOD'
                     JOIN approvaltbl hr ON sr.jdrequestid = hr.jdrequestid 
                        AND hr.approvallevel = 'HR'
                     LEFT JOIN departmentunit du ON sr.deptunitcode = du.deptunitcode
                     LEFT JOIN departments dept ON du.deptcode = dept.departmentcode
                     WHERE hr.status IN ('pending')
                     ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getPendingRequests: " . $e->getMessage());
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
            // Get main request details
            $query = "SELECT 
                sr.jdrequestid,
                sr.jdtitle,
                sr.deptunitcode,
                sr.status as request_status,
                sr.dandt as request_date,
                sr.createdby,
                dept.departmentname,
                du.deptunitname,
                GROUP_CONCAT(DISTINCT 
                    CONCAT(
                        srps.station, ':', 
                        srps.employmenttype, ':', 
                        srps.staffperstation
                    )
                ) as station_details,
                (
                    SELECT GROUP_CONCAT(
                        CONCAT(
                            approvallevel, ':', 
                            status, ':', 
                            COALESCE(comments, '')
                        ) SEPARATOR '|'
                    )
                    FROM approvaltbl 
                    WHERE jdrequestid = sr.jdrequestid
                    ORDER BY FIELD(approvallevel, 'TeamLead', 'DeptUnitLead', 'HOD', 'HR', 'HeadOfHR', 'CFO', 'CEO')
                ) as approval_details
            FROM staffrequest sr
            LEFT JOIN departmentunit du ON sr.deptunitcode = du.deptunitcode
            LEFT JOIN departments dept ON du.deptcode = dept.departmentcode
            LEFT JOIN staffrequestperstation srps ON sr.jdrequestid = srps.jdrequestid
            WHERE sr.jdrequestid = ?
            GROUP BY sr.jdrequestid";

            $stmt = $this->db->prepare($query);
            $stmt->execute([$requestId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new Exception("Request not found");
            }

            // Format the response
            $response = [
                'requestDetails' => [
                    'requestId' => $result['jdrequestid'],
                    'jobTitle' => $result['jdtitle'],
                    'department' => $result['departmentname'],
                    'departmentUnit' => $result['deptunitname'],
                    'status' => $result['request_status'],
                    'requestDate' => $result['request_date'],
                    'createdBy' => $result['createdby']
                ],
                'stations' => [],
                'approvals' => []
            ];

            // Parse station details
            if ($result['station_details']) {
                foreach (explode(',', $result['station_details']) as $station) {
                    list($code, $type, $count) = explode(':', $station);
                    $response['stations'][] = [
                        'station' => $code,
                        'employmentType' => $type,
                        'count' => $count
                    ];
                }
            }

            // Parse approval details
            if ($result['approval_details']) {
                foreach (explode('|', $result['approval_details']) as $approval) {
                    list($level, $status, $comments) = explode(':', $approval);
                    $response['approvals'][] = [
                        'level' => $level,
                        'status' => $status,
                        'comments' => $comments
                    ];
                }
            }

            return $response;
        } catch (Exception $e) {
            error_log("Error in getRequestDetails: " . $e->getMessage());
            throw $e;
        }
    }

    public function getJobDetailsByTitle($jdtitle)
    {
        try {
            $query = "SELECT * FROM jobtitletbl WHERE jdtitle = :jdtitle";
            $stmt = $this->db->prepare($query);
            $stmt->execute(['jdtitle' => $jdtitle]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                throw new Exception("Job title not found");
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error in getJobDetailsByTitle: " . $e->getMessage());
            throw $e;
        }
    }

    public function updateRequestStatus($requestId, $status, $comments = null)
    {
        try {
            $this->db->beginTransaction();

            // Update HR approval status
            $hrUpdateQuery = "UPDATE approvaltbl 
                             SET status = :status,
                                 comments = :comments,
                                 dandt = NOW()
                             WHERE jdrequestid = :requestId 
                             AND approvallevel = 'HR'";

            $hrStmt = $this->db->prepare($hrUpdateQuery);
            $hrStmt->execute([
                'status' => $status,
                'comments' => $comments,
                'requestId' => $requestId
            ]);

            if ($status === 'approved') {
                // Check if HeadOfHR record exists
                $checkQuery = "SELECT COUNT(*) FROM approvaltbl 
                              WHERE jdrequestid = :requestId 
                              AND approvallevel = 'HeadOfHR'";

                $checkStmt = $this->db->prepare($checkQuery);
                $checkStmt->execute(['requestId' => $requestId]);
                $exists = $checkStmt->fetchColumn();

                if (!$exists) {
                    // Insert new Head of HR record
                    $hohrQuery = "INSERT INTO approvaltbl 
                                 (jdrequestid, approvallevel, status, 
                                  approverstaffid, createdby, dandt) 
                                 VALUES 
                                 (:requestId, 'HeadOfHR', 'pending', 
                                  'HR001', :createdby, NOW())";

                    $hohrStmt = $this->db->prepare($hohrQuery);
                    $hohrStmt->execute([
                        'requestId' => $requestId,
                        'createdby' => $_SESSION['email'] ?? 'system'
                    ]);
                } else {
                    // Update existing Head of HR record to pending
                    $updateHohrQuery = "UPDATE approvaltbl 
                                      SET status = 'pending',
                                          dandt = NOW()
                                      WHERE jdrequestid = :requestId 
                                      AND approvallevel = 'HeadOfHR'";
                    
                    $updateHohrStmt = $this->db->prepare($updateHohrQuery);
                    $updateHohrStmt->execute(['requestId' => $requestId]);
                }
            }

            // Update main request status
            $mainStatusQuery = "UPDATE staffrequest 
                               SET status = :status 
                               WHERE jdrequestid = :requestId";
            
            $mainStmt = $this->db->prepare($mainStatusQuery);
            $mainStmt->execute([
                'status' => $status,
                'requestId' => $requestId
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in updateRequestStatus: " . $e->getMessage());
            throw $e;
        }
    }

    public function createHRRequest($data)
    {
        try {
            $this->db->beginTransaction();

            // Insert into staffrequest table
            $requestQuery = "INSERT INTO staffrequest (
                jdrequestid, jdtitle, novacpost, deptunitcode, 
                status, createdby, subdeptunitcode, staffid, departmentcode
            ) VALUES (
                :jdrequestid, :jdtitle, :novacpost, :deptunitcode,
                'draft', :createdby, :subdeptunitcode, :staffid, :departmentcode
            )";

            $requestStmt = $this->db->prepare($requestQuery);
            $requestStmt->execute([
                'jdrequestid' => $data['jdrequestid'],
                'jdtitle' => $data['jdtitle'],
                'novacpost' => $data['total_positions'],
                'deptunitcode' => getCurrentUser('deptunitcode'),
                'createdby' => getCurrentUser('email'),
                'subdeptunitcode' => getCurrentUser('subdeptunitcode'),
                'staffid' => getCurrentUser('staffid'),
                'departmentcode' => getCurrentUser('departmentcode')
            ]);

            // Insert station requests
            foreach ($data['stations'] as $station) {
                $stationQuery = "INSERT INTO staffrequestperstation (
                    jdrequestid, station, employmenttype, 
                    staffperstation, status, createdby
                ) VALUES (
                    :jdrequestid, :station, :employmenttype,
                    :staffperstation, 'draft', :createdby
                )";

                $stationStmt = $this->db->prepare($stationQuery);
                $stationStmt->execute([
                    'jdrequestid' => $data['jdrequestid'],
                    'station' => $station['station'],
                    'employmenttype' => $station['employmenttype'],
                    'staffperstation' => $station['staffperstation'],
                    'createdby' => $data['createdby']
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in createHRRequest: " . $e->getMessage());
            throw $e;
        }
    }

    public function getStations()
    {
        try {
            $query = "SELECT stationcode, stationname FROM stationtbl WHERE status = 'Active' ORDER BY stationname";
            $stmt = $this->db->prepare($query);
            $stmt->execute();

            $options = '';
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $options .= "<option value='{$row['stationcode']}'>{$row['stationname']}</option>";
            }
            return $options;
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
    public function getPendingRequestsHRonly()
    {
        try {
            $query = "SELECT 
                    sr.jdrequestid,
                    sr.jdtitle,
                    sr.deptunitcode,
                    sr.status,
                    sr.dandt as request_date,
                    dept.departmentname as deptname,
                    COUNT(CASE WHEN srps.status = 'approved' THEN 1 END) as approved_positions_count
                FROM staffrequest sr
                LEFT JOIN departmentunit du ON sr.deptunitcode = du.deptunitcode
                LEFT JOIN departments dept ON du.deptcode = dept.departmentcode
                LEFT JOIN staffrequestperstation srps ON sr.jdrequestid = srps.jdrequestid
                WHERE (sr.deptunitcode = 'HRD' OR sr.departmentcode = 'HRD')
                GROUP BY sr.jdrequestid, sr.jdtitle, sr.deptunitcode, sr.status, 
                         sr.dandt, dept.departmentname
                ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getPendingRequestsHRonly: " . $e->getMessage());
            throw $e;
        }
    }

    public function submitHRRequest($requestId) {
        try {
            $query = "UPDATE staffrequest 
                      SET status = 'pending', 
                          submitdate = NOW() 
                      WHERE jdrequestid = ? 
                      AND status = 'draft'";

            $stmt = $this->db->prepare($query);
            $result = $stmt->execute([$requestId]);

            if ($result) {
                // Update all station requests
                $stationQuery = "UPDATE staffrequestperstation 
                                 SET status = 'pending' 
                                 WHERE jdrequestid = ? 
                                 AND status = 'draft'";

                $stationStmt = $this->db->prepare($stationQuery);
                $stationStmt->execute([$requestId]);
            } else {
                error_log("Failed to update staffrequest for requestId: $requestId");
            }

            return $result;
        } catch (Exception $e) {
            error_log("Error in submitHRRequest: " . $e->getMessage());
            return false;
        }
    }

    public function getHRRequests()
    {
        try {
            // First, let's log the current user's staffid
            error_log("Current staffid: " . $_SESSION['staffid']);

            $query = "SELECT 
                sr.jdrequestid,
                sr.jdtitle,
                sr.status,
                sr.deptunitcode,
                sr.dandt,
                sr.createdby,
                COUNT(srps.id) as total_positions
            FROM staffrequest sr
            LEFT JOIN staffrequestperstation srps ON sr.jdrequestid = srps.jdrequestid
            WHERE sr.deptunitcode = 'HRD'
            GROUP BY sr.jdrequestid
            ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute();

            // Log the query results
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("HR Requests found: " . count($results));
            error_log("Query results: " . print_r($results, true));

            return $results;
        } catch (Exception $e) {
            error_log("Error in getHRRequests: " . $e->getMessage());
            error_log("Error trace: " . $e->getTraceAsString());
            throw $e;
        }
    }

    public function getOtherDepartmentRequests()
    {
        try {
            $query = "SELECT 
                sr.jdrequestid,
                sr.jdtitle,
                sr.status,
                sr.deptunitcode,
                d.departmentname,
                COUNT(srps.id) as total_positions
            FROM staffrequest sr
            LEFT JOIN staffrequestperstation srps ON sr.jdrequestid = srps.jdrequestid
            LEFT JOIN departmentunit du ON sr.deptunitcode = du.deptunitcode
            LEFT JOIN departments d ON du.deptcode = d.departmentcode
            WHERE sr.deptunitcode != 'HRD'
            GROUP BY sr.jdrequestid
            ORDER BY sr.dandt DESC";

            $stmt = $this->db->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getOtherDepartmentRequests: " . $e->getMessage());
            throw $e;
        }
    }
}
