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

    // Insert or update a staff request in the staffrequest table
    public function createOrUpdateStaffRequest($jdrequestid, $jdtitle, $novacpost, $reason, $eduqualification, $proqualification, $fuctiontech, $managerial, $behavioural, $keyresult, $empdeliveries, $keysuccess)
    {
        $sql = "INSERT INTO staffrequest (jdrequestid, jdtitle, novacpost, reason, eduqualification, proqualification, fuctiontech, managerial, behavioural, keyresult, empdeliveries, keysuccess, createdby, dandt)
                VALUES (:jdrequestid, :jdtitle, :novacpost, :reason, :eduqualification, :proqualification, :fuctiontech, :managerial, :behavioural, :keyresult, :empdeliveries, :keysuccess, :createdby, NOW())
                ON DUPLICATE KEY UPDATE 
                jdtitle = :jdtitle, novacpost = :novacpost, reason = :reason, eduqualification = :eduqualification, 
                proqualification = :proqualification, fuctiontech = :fuctiontech, managerial = :managerial, 
                behavioural = :behavioural, keyresult = :keyresult, empdeliveries = :empdeliveries, 
                keysuccess = :keysuccess, createdby = :createdby";

        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':jdrequestid', $jdrequestid);
        $stmt->bindParam(':jdtitle', $jdtitle);
        $stmt->bindParam(':novacpost', $novacpost);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':eduqualification', $eduqualification);
        $stmt->bindParam(':proqualification', $proqualification);
        $stmt->bindParam(':fuctiontech', $fuctiontech);
        $stmt->bindParam(':managerial', $managerial);
        $stmt->bindParam(':behavioural', $behavioural);
        $stmt->bindParam(':keyresult', $keyresult);
        $stmt->bindParam(':empdeliveries', $empdeliveries);
        $stmt->bindParam(':keysuccess', $keysuccess);
        $stmt->bindParam(':createdby', $_SESSION['username']);

        return $stmt->execute();
    }

    // Insert or update staff request per station in the staffrequestperstation table
    public function createOrUpdateStaffRequestPerStation($jdrequestid, $station, $employmenttype, $staffperstation)
    {
        $sql = "INSERT INTO staffrequestperstation (jdrequestid, station, employmenttype, staffperstation)
                VALUES (:jdrequestid, :station, :employmenttype, :staffperstation)
                ON DUPLICATE KEY UPDATE 
                station = :station, employmenttype = :employmenttype, staffperstation = :staffperstation";

        $stmt = $this->db->prepare($sql);

        // Bind the parameters to the placeholders in the SQL query
        $stmt->bindParam(':jdrequestid', $jdrequestid);
        $stmt->bindParam(':station', $station);
        $stmt->bindParam(':employmenttype', $employmenttype);
        $stmt->bindParam(':staffperstation', $staffperstation);

        // Execute the statement
        return $stmt->execute();
    }

    // Handle staff request submission and echo results directly
    public function handleStaffRequestSubmission()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $jdrequestid = htmlspecialchars($_POST['jdrequestid'] ?? '');
            $jdtitle = htmlspecialchars($_POST['jdtitle'] ?? '');
            $novacpost = htmlspecialchars($_POST['novacpost'] ?? '');
            $reason = htmlspecialchars($_POST['reason'] ?? '');
            $eduqualification = htmlspecialchars($_POST['eduqualification'] ?? '');
            $proqualification = htmlspecialchars($_POST['proqualification'] ?? '');
            $function = htmlspecialchars($_POST['fuctiontech'] ?? '');
            $techmanagerial = htmlspecialchars($_POST['managerial'] ?? '');
            $behavioural = htmlspecialchars($_POST['behavioural'] ?? '');
            $keyresult = htmlspecialchars($_POST['keyresult'] ?? '');
            $empdeliveries = htmlspecialchars($_POST['empdeliveries'] ?? '');
            $keysuccess = htmlspecialchars($_POST['keysuccess'] ?? '');
            $station = htmlspecialchars($_POST['station'] ?? '');
            $employmenttype = htmlspecialchars($_POST['employmenttype'] ?? '');
            $staffperstation = htmlspecialchars($_POST['staffperstation'] ?? '');

            // Check if required fields are filled
            if (empty($jdrequestid) || empty($jdtitle) || empty($novacpost)) {
                echo 'Missing data for JD request ID or title';
                return false;
            }

            if ($this->createOrUpdateStaffRequest($jdrequestid, $jdtitle, $novacpost, $reason, $eduqualification, $proqualification, $function, $techmanagerial, $behavioural, $keyresult, $empdeliveries, $keysuccess)) {
                echo 'Staff request submitted successfully!';
            } else {
                echo 'Failed to submit staff request.';
                return false;
            }

            if ($this->createOrUpdateStaffRequestPerStation($jdrequestid, $station, $employmenttype, $staffperstation)) {
                echo 'Station-specific information saved.';
            } else {
                echo 'Failed to save station-specific information.';
                return false;
            }

            $_SESSION['form_submitted'] = true;
            header("Location: " . $_SERVER['REQUEST_URI']);
            exit;
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
    public function getjobtitletbl()
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

    public function getStations()
    {
        $stmt = $this->db->prepare("SELECT id, stationname, stationcode FROM stationtbl");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStaffType()
    {
        $stmt = $this->db->prepare("SELECT id, stafftype FROM stafftype");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

// Ensure the submission is handled correctly
$revenue = new Revenue($con);
if ($revenue->handleStaffRequestSubmission()) {
    echo "Staff request submitted successfully!";
}
