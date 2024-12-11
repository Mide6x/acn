<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/acnnew/include/config.php';
require_once 'rev.php';

$revenue = new Revenue($con);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        switch ($_POST['action']) {
            case 'toggle_department_status':
                if (!isset($_POST['departmentcode'])) {
                    throw new Exception("Department code is required");
                }
                $result = $revenue->toggleDepartmentStatus($_POST['departmentcode']);
                echo $result ? 'success' : 'error';
                break;

            case 'create_department':
                if (!isset($_POST['departmentname'])) {
                    throw new Exception("Department name is required");
                }
                $result = $revenue->createDepartment($_POST['departmentname']);
                echo $result ? 'success' : 'error';
                break;

            case 'toggle_station_status':
                if (!isset($_POST['stationcode'])) {
                    throw new Exception("Station code is required");
                }
                $result = $revenue->toggleStationStatus($_POST['stationcode']);
                echo $result ? 'success' : 'error';
                break;

            case 'create_station':
                if (!isset($_POST['stationname']) || !isset($_POST['stationtype']) || !isset($_POST['operationtype'])) {
                    throw new Exception("All station details are required");
                }
                $result = $revenue->createStation(
                    $_POST['stationname'],
                    $_POST['stationtype'],
                    $_POST['operationtype']
                );
                echo $result ? 'success' : 'error';
                break;

            case 'toggle_jobtitle_status':
                if (!isset($_POST['jobtitle']) || !isset($_POST['deptunitcode'])) {
                    throw new Exception("Job title and department code are required");
                }
                $result = $revenue->toggleJobTitleStatus($_POST['jobtitle'], $_POST['deptunitcode']);
                echo $result ? 'success' : 'error';
                break;

            case 'create_jobtitle':
                if (!isset($_POST['jobtitle']) || !isset($_POST['deptunitcode']) || !isset($_POST['description'])) {
                    throw new Exception("All job title details are required");
                }
                $result = $revenue->createJobTitle(
                    $_POST['jobtitle'],
                    $_POST['deptunitcode'],
                    $_POST['description']
                );
                echo $result ? 'success' : 'error';
                break;

            default:
                throw new Exception("Invalid action");
        }
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
} 