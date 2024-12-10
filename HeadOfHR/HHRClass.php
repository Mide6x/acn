<?php
class HHR {
    private $db;

    public function __construct($con) {
        $this->db = $con;
    }

    public function getPendingRequests() {
        try {
            $query = "SELECT DISTINCT 
                        at.id,
                        at.jdrequestid,
                        at.jdtitle,
                        at.status as approval_status,
                        sr.novacpost,
                        sr.departmentcode,
                        d.departmentname as department
                     FROM approvaltbl at
                     LEFT JOIN staffrequest sr ON at.jdrequestid = sr.jdrequestid
                     LEFT JOIN departments d ON sr.departmentcode = d.departmentcode
                     WHERE at.approvallevel = 'HeadOfHR' 
                     AND at.status = 'pending'
                     ORDER BY at.dandt DESC";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute();
            $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Format the response as HTML directly
            $html = '';
            foreach ($requests as $request) {
                $html .= '<tr>';
                $html .= '<td>' . ($request['jdrequestid'] ?? 'N/A') . '</td>';
                $html .= '<td>' . ($request['jdtitle'] ?? 'N/A') . '</td>';
                $html .= '<td>' . ($request['department'] ?? 'N/A') . '</td>';
                $html .= '<td>' . ($request['approval_status'] ?? 'N/A') . '</td>';
                $html .= '<td>
                            <button class="btn btn-primary btn-sm" onclick="viewDetails(\'' . $request['jdrequestid'] . '\')">
                                View Details
                            </button>
                        </td>';
                $html .= '</tr>';
            }
            
            return empty($requests) ? '<tr><td colspan="5" class="text-center">No pending requests found</td></tr>' : $html;
        } catch (Exception $e) {
            error_log("Error in getPendingRequests: " . $e->getMessage());
            return '<tr><td colspan="5" class="text-center text-danger">Error loading requests</td></tr>';
        }
    }

    public function updateRequestStatus($requestId, $status, $comments = null) {
        try {
            $this->db->beginTransaction();

            // Update HeadOfHR status
            $updateQuery = "UPDATE approvaltbl 
                          SET status = :status, 
                              comments = :comments,
                              dandt = NOW() 
                          WHERE jdrequestid = :requestId 
                          AND approvallevel = 'HeadOfHR'";

            $stmt = $this->db->prepare($updateQuery);
            $stmt->execute([
                'status' => $status,
                'comments' => $comments,
                'requestId' => $requestId
            ]);

            if ($status === 'approved') {
                // Set CFO status to pending
                $cfoQuery = "UPDATE approvaltbl 
                            SET status = 'pending',
                                dandt = NOW() 
                            WHERE jdrequestid = :requestId 
                            AND approvallevel = 'CFO'";
                
                $cfoStmt = $this->db->prepare($cfoQuery);
                $cfoStmt->execute(['requestId' => $requestId]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in updateRequestStatus: " . $e->getMessage());
            return false;
        }
    }

    public function getRequestDetails($requestId) {
        try {
            $query = "SELECT 
                        at.id, at.jdrequestid, at.jdtitle, at.status as approval_status,
                        sr.novacpost, sr.departmentcode, sr.subdeptunitcode,
                        d.departmentname as department,
                        jt.jddescription, jt.eduqualification, jt.proqualification, jt.workrelation
                     FROM approvaltbl at
                     LEFT JOIN staffrequest sr ON at.jdrequestid = sr.jdrequestid
                     LEFT JOIN departments d ON sr.departmentcode = d.departmentcode
                     LEFT JOIN jobtitletbl jt ON at.jdtitle = jt.jdtitle
                     WHERE at.jdrequestid = :requestId 
                     AND at.approvallevel = 'HeadOfHR'";
            
            $stmt = $this->db->prepare($query);
            $stmt->execute(['requestId' => $requestId]);
            $details = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($details) {
                // Get station details
                $stationQuery = "SELECT 
                                    srps.station,
                                    srps.staffperstation,
                                    srps.employmenttype,
                                    srps.status,
                                    st.stationname
                                FROM staffrequestperstation srps
                                LEFT JOIN stationtbl st ON srps.station = st.stationcode
                                WHERE srps.jdrequestid = :requestId";
                
                $stationStmt = $this->db->prepare($stationQuery);
                $stationStmt->execute(['requestId' => $requestId]);
                $details['stations'] = $stationStmt->fetchAll(PDO::FETCH_ASSOC);
            }

            return $details;
        } catch (Exception $e) {
            error_log("Error in getRequestDetails: " . $e->getMessage());
            throw $e;
        }
    }
}
