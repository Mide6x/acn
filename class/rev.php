<?php
session_start();
ini_set('max_execution_time', '0');

class Revenue
{
    protected $db;

    public function __construct($con)
    {
        $this->db = $con;
    }

    // Method to return current date and time in Dubai timezone
    public function africaDate()
    {
        date_default_timezone_set('Asia/Dubai');
        return date('Y-m-d H:i:s');
    }

    public function createOrUpdateStaffRequest($jdrequestid, $jdtitle, $novacpost, $status, $createdby)
    {
        $query = "INSERT INTO staffrequest (jdrequestid, jdtitle, novacpost, status, createdby)
              VALUES (?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE jdtitle = ?, novacpost = ?, status = ?, createdby = ?";
        $stmt = $this->db->prepare($query);

        // Correcting bindValue method
        $stmt->bindValue(1, $jdrequestid, PDO::PARAM_INT);
        $stmt->bindValue(2, $jdtitle, PDO::PARAM_STR);
        $stmt->bindValue(3, $novacpost, PDO::PARAM_INT);
        $stmt->bindValue(4, $status, PDO::PARAM_STR);
        $stmt->bindValue(5, $createdby, PDO::PARAM_INT);
        $stmt->bindValue(6, $jdtitle, PDO::PARAM_STR);
        $stmt->bindValue(7, $novacpost, PDO::PARAM_INT);
        $stmt->bindValue(8, $status, PDO::PARAM_STR);
        $stmt->bindValue(9, $createdby, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->rowCount(); // Use rowCount() instead of insert_id for updates
        }
        return false;
    }

    public function createOrUpdateStaffRequestPerStation($jdrequestid, $station, $employmenttype, $staffperstation, $status, $reason, $createdby)
    {
        $query = "INSERT INTO staffrequestperstation (jdrequestid, station, employmenttype, staffperstation, status, reason, createdby)
              VALUES (?, ?, ?, ?, ?, ?, ?)
              ON DUPLICATE KEY UPDATE station = ?, employmenttype = ?, staffperstation = ?, status = ?, reason = ?, createdby = ?";
        $stmt = $this->db->prepare($query);

        // Correcting bindValue method
        $stmt->bindValue(1, $jdrequestid, PDO::PARAM_INT);
        $stmt->bindValue(2, $station, PDO::PARAM_STR);
        $stmt->bindValue(3, $employmenttype, PDO::PARAM_STR);
        $stmt->bindValue(4, $staffperstation, PDO::PARAM_INT);
        $stmt->bindValue(5, $status, PDO::PARAM_STR);
        $stmt->bindValue(6, $reason, PDO::PARAM_STR);
        $stmt->bindValue(7, $createdby, PDO::PARAM_INT);
        $stmt->bindValue(8, $station, PDO::PARAM_STR);
        $stmt->bindValue(9, $employmenttype, PDO::PARAM_STR);
        $stmt->bindValue(10, $staffperstation, PDO::PARAM_INT);
        $stmt->bindValue(11, $status, PDO::PARAM_STR);
        $stmt->bindValue(12, $reason, PDO::PARAM_STR);
        $stmt->bindValue(13, $createdby, PDO::PARAM_INT);

        if ($stmt->execute()) {
            return $stmt->rowCount(); // Use rowCount() instead of insert_id for updates
        }
        return false;
    }




    // Create a new job title
    public function createJobTitle($newjdtitle, $jddepartmentunit, $jdstatus)
    {
        $sql = "INSERT INTO jobtitletbl (title, department_unit, status) VALUES (:title, :department_unit, :status)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':title', $newjdtitle);
        $stmt->bindParam(':department_unit', $jddepartmentunit);
        $stmt->bindParam(':status', $jdstatus);
        return $stmt->execute();
    }

    // Update job title information
    public function updateJobTitle($jdtitleid, $updatedjdtitle, $jddepartmentunit, $jdstatus)
    {
        $sql = "UPDATE jobtitletbl SET title = :title, department_unit = :department_unit, status = :status WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':id', $jdtitleid);
        $stmt->bindParam(':title', $updatedjdtitle);
        $stmt->bindParam(':department_unit', $jddepartmentunit);
        $stmt->bindParam(':status', $jdstatus);
        return $stmt->execute();
    }

    // Get job titles
    public function getJobTitles()
    {
        $sql = "SELECT * FROM jobtitletbl";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get department units
    public function getDepartmentUnit()
    {
        $stmt = $this->db->prepare("SELECT id, deptunitname, deptunitcode FROM departmentunit WHERE status = 'Active'");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get positions by department
    public function getPositionsByDepartment($deptunitcode)
    {
        $stmt = $this->db->prepare("SELECT id, poname FROM position WHERE deptunitcode = :deptunitcode AND postatus = 'Active'");
        $stmt->bindParam(':deptunitcode', $deptunitcode);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get stations
    public function getStations()
    {
        $stmt = $this->db->prepare("SELECT id, stationname, stationcode FROM stationtbl");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get staff types
    public function getStaffType()
    {
        $stmt = $this->db->prepare("SELECT id, stafftype FROM stafftype");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
