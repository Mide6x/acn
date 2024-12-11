<?php
class CFO {
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
                     WHERE at.approvallevel = 'CFO' 
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

            // Update CFO status
            $updateQuery = "UPDATE approvaltbl 
                          SET status = :status, 
                              comments = :comments,
                              dandt = NOW() 
                          WHERE jdrequestid = :requestId 
                          AND approvallevel = 'CFO'";

            $stmt = $this->db->prepare($updateQuery);
            $stmt->execute([
                'status' => $status,
                'comments' => $comments,
                'requestId' => $requestId
            ]);

            if ($status === 'approved') {
                // Set CEO status to pending
                $ceoQuery = "UPDATE approvaltbl 
                            SET status = 'pending',
                                dandt = NOW() 
                            WHERE jdrequestid = :requestId 
                            AND approvallevel = 'CEO'";
                
                $ceoStmt = $this->db->prepare($ceoQuery);
                $ceoStmt->execute(['requestId' => $requestId]);

                // Update main request status to CFO Approved
                $updateMain = "UPDATE staffrequest 
                              SET status = 'CFO Approved' 
                              WHERE jdrequestid = :requestId";
                
                $mainStmt = $this->db->prepare($updateMain);
                $mainStmt->execute(['requestId' => $requestId]);
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
                sr.*,
                jt.jddescription,
                jt.eduqualification,
                jt.proqualification,
                jt.workrelation,
                jt.jdcondition,
                sr.createdby as requestor
            FROM staffrequest sr
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

    public function getStationDetails($requestId) {
        try {
            $query = "SELECT 
                srps.station,
                srps.staffperstation,
                srps.employmenttype,
                st.stationname
            FROM staffrequestperstation srps
            LEFT JOIN stationtbl st ON srps.station = st.stationcode
            WHERE srps.jdrequestid = :requestId";

            $stmt = $this->db->prepare($query);
            $stmt->execute(['requestId' => $requestId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("Error in getStationDetails: " . $e->getMessage());
            throw $e;
        }
    }
}