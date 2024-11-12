<?php
include('../include/config.php');
// Hardcoded session values at the start
$_SESSION['username'] = 'adewole.o@acn.aero';
$_SESSION['staffid'] = 'O2024011';
$_SESSION['stnames'] = 'Adewole Olumide';
$data = json_decode(file_get_contents('php://input'), true);
#Station details
if (isset($_POST['stationtype'])) {
    $stationtype = $_POST["stationtype"];
    $nextxx = $_POST["nextxx"];
    if ($nextxx == "") {
        $nextxx = "0";
    }
    $stationdets = $revenue->loadstations($stationtype, '', $nextxx);
    echo $stationdets;
}
if (isset($_POST['stationname'])) {
    $station = $_POST['stationname'];
    $stationcode = $_POST['stationcode'];
    $stationtype = $_POST['stationtyp'];
    $operationtype = $_POST['operationtype'];
    $stationdet = $revenue->createstation($station, $stationcode, $stationtype, $operationtype, 'Active', $_SESSION['username'], 0);
    echo $stationdet;
}

if (isset($_POST['sstatus'])) {
    $sstatus = $_POST['sstatus'];
    $station = $_POST['sstation'];
    $revenue->updatestationsatus($station, $sstatus);
}
if (isset($_POST['routes'])) {
    $routes = $revenue->loadroutes();
    echo $routes;
}
#end station detals
#Aircraft details
if (isset($_POST['acname'])) {
    $acname = $_POST['acname'];
    $actype = $_POST['actype'];
    $acregno = $_POST['acregno'];
    $accapacity = $_POST['accapacity'];
    $acversion = $_POST['acversion'];
    $aircraftdet = $revenue->createaircraftinfo($acname, $actype, $acregno, $accapacity, $acversion, 'Active', $_SESSION['username'], 0);
    echo $aircraftdet;
}
if (isset($_POST['factype'])) {
    $actype = $_POST["factype"];
    $acname = $_POST["facname"];
    $nextxx = $_POST["nextxx"];
    if ($nextxx == "") {
        $nextxx = "0";
    }
    $aircraftdets = $revenue->loadacdetails($acname, $actype, '', $nextxx);
    echo $aircraftdets;
}

if (isset($_POST['sacstatus'])) {
    $acstatus = $_POST['sacstatus'];
    $acregno = $_POST['sacregno'];
    $revenue->updateaircraftsatus($acregno, $acstatus);
}
#End Aircraft details
#Fight details
if (isset($_POST['fltno'])) {
    $flightno = $_POST["fltno"];
    $nextxx = $_POST["nextxx"];
    if ($nextxx == "") {
        $nextxx = "0";
    }
    $flightdets = $revenue->loadflightdetails('', '', $nextxx);
    echo $flightdets;
}
if (isset($_POST['flightno'])) {
    $flightno = $_POST['flightno'];
    $crewreporttime = $_POST['crewreporttime'];
    $schstarttime = $_POST['schstarttime'];
    $schendtime = $_POST['schendtime'];
    $routefrom = $_POST['routefrom'];
    $routeto = $_POST['routeto'];
    $flightdet = $revenue->createflightdetails($flightno, $schstarttime, $schendtime, $routefrom, $routeto, $crewreporttime, 'Active', $_SESSION['username']);
    echo $flightdet;
}

if (isset($_POST['sstatus'])) {
    $sstatus = $_POST['sstatus'];
    $station = $_POST['sstation'];
    $revenue->updatestationsatus($station, $sstatus);
}
#End Flight details
# Aviation Charges
if (isset($_POST['lchargetype'])) {
    $flightno = $_POST["lchargetype"];
    $nextxx = $_POST["nextxx"];
    if ($nextxx == "") {
        $nextxx = "0";
    }
    $lavachargetype = $revenue->loadaviationchargetype('', '', '', $nextxx);
    echo $lavachargetype;
}

if (isset($_POST['chargetype'])) {
    $chargetype  = $_POST['chargetype'];
    $chargebody = $_POST['chargebody'];
    $chargename = $_POST['chargename'];
    $isaircrafttype = $_POST['isaircraft'];
    $avachargetypedets = $revenue->createaviationchargetype($chargetype, $chargebody, $chargename, $isaircrafttype, 'Active', 0, $_SESSION['username']);
    echo $avachargetypedets;
}
if (isset($_POST['gchargetype'])) {
    $chargetype = $revenue->loadchargetype();
    echo $chargetype;
}
if (isset($_POST['gchargebody'])) {
    $chargebody = $revenue->loadchargebody();
    echo $chargebody;
}
if (isset($_POST['chargetypestatus'])) {
    $chargetypestatus = $_POST['chargetypestatus'];
    $chargetypeid = $_POST['chargetypeid'];
    $revenue->updateaviationchargetypestatus($chargetypeid, $chargetypestatus);
}
#End Aviation Charges
#Menu details
if (isset($_POST['menuname'])) {
    $menuname = $_POST['menuname'];
    $menulink = $_POST['menulink'];
    $menutitle = $_POST['menutitle'];
    $menudet = $revenue->createmenu($menuname, $menulink, $menutitle, 'Active', $_SESSION['username'], 0);
    echo $menudet;
}
if (isset($_POST['menustatus'])) {
    $menuid = $_POST['menuid'];
    $menustatus = $_POST['menustatus'];
    $revenue->updatemenustatus($menuid, $menustatus);;
}

if (isset($_POST['lmenu'])) {
    $nextxx = $_POST['nextxx'];
    $loadmenu = $revenue->loadmenu($nextxx);
    echo $loadmenu;
}

if (isset($_POST['gmenu'])) {
    $loadmenucheck = $revenue->loadmenuchecklist();
    echo $loadmenucheck;
}
if (isset($_POST['umenu'])) {
    if (isset($_SESSION['staffid'])) {
        $loadumenu = $revenue->getuserroles($_SESSION['staffid']);
        echo $loadumenu;
    } else {
        // Handle the case when staffid is not set in session
        echo "Staff ID is not set in session.";
    }
}

#End Menu details
#Role Details
#Start Role Details
if (isset($_POST['lrole'])) {
    $loadrole = $revenue->loadroles();
    echo $loadrole;
}
// Get the posted data from the fetch request

if (isset($data['menuname']) && isset($data['items'])) {
    $roleName = $data['menuname'];
    $items = $data['items'];
    print_r($items);
    foreach ($items as $item) {
        $loadroles = $revenue->createrole($roleName, $item, 'Active', $_SESSION['username']);
    }
    echo $loadroles;
}
if (isset($_POST['urole'])) {
    $geturole = $revenue->getuserrole();
    echo $geturole;
}
if (isset($_POST['grole'])) {
    $loadrolecheck = $revenue->loadrolechecklist();
    echo $loadrolecheck;
}
#End Role Details
#Start Register User
if (isset($_POST['luser'])) {
    $loadrole = $revenue->loaduser();
    echo $loadrole;
}
if (isset($data['cstaffid'])) {
    $cstaffid = $data['cstaffid'];
    $cstation = $data['cstation'];
    $ctitle = $data['ctitle'];
    $clastname = $data['clastname'];
    $cfirstname = $data['cfirstname'];
    $cemailaddress = $data['cemailaddress'];
    $cbusinessunit = $data['cbizunit'];
    $cdepartment = $data['cdeparment'];
    $cdepartunit = $data['cdepartunit'];
    $cpagetitle = $data['cpagetitle'];
    $curoles = $data['cuserroles'];
    $password = randomPassword();
    $hashpass = password_hash($password, PASSWORD_DEFAULT);
    $ucreate = $revenue->createuser($cstaffid, $cstation, $ctitle, $clastname, $cfirstname, $cemailaddress, $cbusinessunit, $cdepartment, $cdepartunit, $curoles, $hashpass, $_SESSION['username']);
    //$request ='getnotificationbytitle.php';
    $getnotify = $revenue->getnotificationbytitle($data['cpagetitle']);
    $emailsubject = $getnotify['emailsubject'];
    $emailBodyString = $getnotify['emailbody'];

    $emailbody = str_replace(
        ['clastname', 'cuname', 'cpword'],
        [$clastname, $cemailaddress, $password],
        $emailBodyString
    );
    $mailsending = $revenue->insertemail('damilolaadeniji"gmail.com', $cemailaddress, $emailsubject, $emailbody);
    echo $ucreate;
}
if (isset($_POST['loadoptdept'])) {
    $loadumenu = $revenue->getuserroles($_SESSION['staffid']);
    echo $loadumenu;
}
if (isset($_POST['bizunit'])) {
    $loadoptbizunit = $revenue->loadbusinessunit();
    echo '<option>Select...</option>' . $loadoptbizunit;
}
if (isset($_POST['bizcode'])) {
    $loadoptdepart = $revenue->loaddepartment($_POST['bizcode']);
    echo '<option>Select...</option>' . $loadoptdepart;
}
if (isset($_POST['deptcode'])) {
    $loadoptdepartunit = $revenue->loaddeptunit($_POST['deptcode']);
    echo '<option>Select...</option>' . $loadoptdepartunit;
}
if (isset($_POST['stname'])) {
    if (isset($_SESSION['stnames'])) {
        // print_r($_SESSION['staffid']); // Uncomment if needed
        print_r($_SESSION['stnames']);
    } else {
        // Handle the case when stnames is not set in session
        echo "Staff names are not set in session.";
    }
}

function randomPassword()
{
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789!@#$%^&*()_+-=[]{}|;:,.<>?";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}
#End Register User
#Start Notification 
// function getemail($data,$request){
//     // var_dump($data);
//     // var_dump($request);
//     $url = "http://localhost/emailnotificationsystem/api/post/".$request; // Replace with the API endpoint URL

//     var_dump($url);

//     $postData= $data;

//     // Initialize a cURL session
//     $ch = curl_init();

//     // Set cURL options
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_POST, true); // Specify POST method
//     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

//     // Execute the request and fetch the response
//     $response = curl_exec($ch);
//     //print_r($url);
//     // Check for cURL errors
//     if (curl_errno($ch)) {
//         echo 'Error:' . curl_error($ch);
//     } else {
//         // Decode and process the JSON response

//         $data = json_decode($response, true);
//         return $data;
//     }
//     curl_close($ch);

// }
// function sendingemail($maildata,$mailrequest){
//     // var_dump($data);
//     // var_dump($request);
//     $url = "http://localhost/emailnotificationsystem/api/post/".$mailrequest; // Replace with the API endpoint URL

//     var_dump($url);

//     $postData= $maildata;

//     // Initialize a cURL session
//     $ch = curl_init();

//     // Set cURL options
//     curl_setopt($ch, CURLOPT_URL, $url);
//     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//     curl_setopt($ch, CURLOPT_POST, true); // Specify POST method
//     curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));

//     // Execute the request and fetch the response
//     $response = curl_exec($ch);
//     //print_r($url);
//     // Check for cURL errors
//     if (curl_errno($ch)) {
//         echo 'Error:' . curl_error($ch);
//     } else {
//         // Decode and process the JSON response

//         $data = json_decode($response, true);
//         return $data;
//     }
//     curl_close($ch);

// }
// //create Notification
// if(isset($data['emailsubj'])){
//     $request ='createnotification.php';
//     $createnotify = getemail($data,$request);
//     echo $createnotify;
// }
// #Get Notification by title
// if(isset($data['$title'])){
//     $request ='getnotificationb.php';
//     $getnotify = getemail($data,$request);
//      echo $getnotify;
// }

#End Notification
#Profiling  
#Bio details
/*if(isset($_POST['email'])){
            $email = $_POST['email'];
            $userdets = $revenue->loadbiodata($email);
            echo $userdets;
        }*/
// $_SESSION['staffid'];
// $_SESSION['email'];

if (isset($_POST['sname'])) {
    $_SESSION['staffid'] = $_POST['staffid'];
    $_SESSION['email'] =  $_POST['email'];
    $title = $_POST['tittle'];
    $sname = $_POST['sname'];
    $fname = $_POST['fname'];
    $mname = $_POST['mname'];
    $phone = $_POST['phone'];
    $email  = $_POST['email'];
    $address = $_POST['addr'];
    $city = $_POST['city'];
    $nationality = $_POST['nationality'];
    $phonenumber = $_POST['phone'];
    $state = $_POST['state'];
    $gender = $_gender['gender'];
    $marital = $_POST['marital'];
    $dob = $_POST['dob'];
    $languages = $_POST['lang'];
    $biodata = $revenue->createbiodata(
        $staffid,
        $title,
        $sname,
        $fname,
        $mname,
        $phonenumber,
        $email,
        $address,
        $nationality,
        $state,
        $gender,
        $marital,
        $languages,
        $dob
    );
    echo $biodata;
}
if (isset($_POST['nationality'])) {
    $email = $_email['email'];
    $address = $_POST['addr'];
    $city = $_POST['city'];
    $languages = $_POST['lang'];
    $nationality = $_POST['nationality'];
    $gender = $_gender['gender'];
    $updatebiodata = $revenue->updatebiodata($nationality, $city, $languages, $phonenumber, $email, $staffid);
    echo $updatebiodata;
}
#End of bio details


#Next of Kin details

if (isset($_POST['nokfname)'])) {
    $nokfname = $_POST['nokfname'];
    $email  = $_POST['email'];
    $noksname = $_POST['noksname'];
    $nokmname = $_POST['nokmname'];
    $noktitle = $_POST['noktitle'];
    $nokphone = $_POST['nokphone'];
    $nokaddress = $_POST['nokaddress'];
    $noknationality = $_POST['noknationality'];
    $nokgender = $_POST['nokgender'];
    $nokcity = $_POST['nokcity'];
    $nokstate = $_POST['nokstate'];
    $nokrelationship = $_POST['nokrelationship'];
    $nextofKindet = $revenue->createnextofkininfo(
        $_SESSION['email'],
        $_SESSION['staffid'],
        $noksurname,
        $nokfname,
        $nokphone,
        $nokgender,
        $nokemail,
        $nokaddress,
        $noknationality,
        $nokstate,
        $nokrelationship
    );
    echo $nextofKindet;
}

if (isset($_POST['noksname'])) {
    $noksname = $_POST['noksname'];
    $nokmname = $_POST['nokmname'];
    $noktitle = $_POST['noktitle'];
    $nokphone = $_POST['nokphone'];
    $nokaddress = $_POST['nokaddress'];
    $nokcity = $_POST['nokcity'];
    $nokstate = $_POST['nokstate'];
    $nokrelationship = $_POST['nokrelationship'];
    $nextofKindet = $revenue->updatenextofkininfo($email, $staffid, $nokphone, $nokaddress, $nokcity, $nokstate, $nokrelationship);
}
#End of next of kin details

#Employee details


if (isset($_POST['base'])) {
    $base = $revenue->loadbase();
    echo $base;
}


if (isset($_POST['issuedate'])) {
    $staffid = $_POST['staffid'];
    $rank = $_POST['rank'];
    $email = $_POST['email'];
    $callsign = $_POST['callsign'];
    $startdate = $_POST['issuedate'];
    $base = $_POST['base'];
    if ($enddate == '') {
        $enddate = 'Present';
    }
    $hours = $_POST['hours'];
    $employmentdate = $startdate . '-' . $enddate;
    $employeedet = $revenue->createemploymentinfo($staffid, $email, $rank, $base, $callsign, $employmentdate);
    echo $employeedet;
}

if (isset($_POST['callsign'])) {
    $rank = $_POST['rank'];
    $base = $_POST['base'];
    $callsign = $_POST['callsign'];
    $employeedet = $revenue->updateemploymentinfo($staffid, $email, $rank, $base, $callsign);
}

#End of employee details details

# Experience Information

#Gets Experience Information
if (isset($_POST['email'])) {
    $email = $_POST['email'];
    $staffid = $_POST['staffid'];
    $experienceInfo = $revenue->loadexperienceinformation($staffid, $email);
    echo $experienceInfo;
}

#Post new Experience Information
if (isset($_POST['actype'])) {
    $email = $_POST['email'];
    $hours = $_POST['hours'];
    $staffid = $_POST['staffid'];
    $position = $_POST['position'];
    $actype = $_POST['actype'];
    $atdate = $_POST['date'];
    #Add hours to the db. and remove atdate
    $experienceinfo = $revenue->createexperienceinformation($staffid, $email, $actype, $hours, $position, $atdate);
    echo $experienceinfo;
}

#Update Experience Informartion
if (isset($_POST['position'])) {
    $email = $_POST['email'];
    $staffid = $_POST['staffid'];
    $position = $_POST['position'];
    $actype = $_POST['actype'];
    $hours = $_POST['hours'];
    $atdate = $_POST['date'];
    $experienceinfo = $revenue->updateexperirenceinfo($staffid, $email, $actype, $position, $hours, $atdate);
}

#End of experience information


#Professional details

# Load actypes
if (isset($_POST['actype'])) {
    $actype = $revenue->loadactype();
}

if (isset($_POST['placeofissue'])) {
    $crewlinumber = $_POST['crewlicensenumber'];
    $issuedate = $_POST['issuedate'];
    $expirydate = $_POST['expirydate'];
    $license_type = $_POST['crewlicensetype'];
    $actype = $_POST['actype'];
    $file_upload = $_FILES['fileupload'];
    $issueInstitute = $_POST['placeofissue'];
    #Add file upload here to upload the license TYPE, file_upload
    $profesional = $revenue->createprofessionalinfo($staffid, $email, $crewlicensenumber, $license_type, $file_upload, $dateofissue, $expiry);
    $revenue->updatebiostatus($email, $staffid);
    echo $profesional;
}



if (isset($_POST['crewlicensenumber'])) {
    $crewlicensenumber = $_POST['crewlicensenumber'];
    $email = $_POST['email'];
    $staffid = $_POST['staffid'];

    $proffesional = $revenue->updateprofessionalinfo($staffid, $email, $crewlicensenumber);
}



if (isset($_POST['staffid'])) {
    $staffid = $_POST['staffid'];
    $email = $_POST['email'];

    $revenue->loadprofessionalinformation($staffid, $email);
}
#End of professional details

#End Profiling 


#HR Settings
#Staff Request Self Service
#Unified Staff Request Submission
#Check if the main staff request submission is being made
if (isset($_POST['jdrequestid'])) {
    #Retrieve main staff request information
    $jdrequestid = $_POST['jdrequestid'];
    $jdtitle = $_POST['jdtitle'] ?? null;
    $novacpost = $_POST['novacpost'] ?? null;
    $reason = $_POST['reason'] ?? null;
    $eduqualification = $_POST['eduqualification'] ?? null;
    $proqualification = $_POST['proqualification'] ?? null;
    $fuctiontech = $_POST['fuctiontech'] ?? null;
    $managerial = $_POST['managerial'] ?? null;
    $behavioural = $_POST['behavioural'] ?? null;
    $keyresult = $_POST['keyresult'] ?? null;
    $empdeliveries = $_POST['empdeliveries'] ?? null;
    $keysuccess = $_POST['keysuccess'] ?? null;

    #Save or update the main staff request
    $staffrequestInfo = $revenue->createOrUpdateStaffRequest(
        $jdrequestid,
        $jdtitle,
        $novacpost,
        $reason,
        $eduqualification,
        $proqualification,
        $fuctiontech,
        $managerial,
        $behavioural,
        $keyresult,
        $empdeliveries,
        $keysuccess
    );
    echo $staffrequestInfo;

    #Handle Request Per Station Information
    $station = $_POST['station'] ?? null;
    $employmenttype = $_POST['employmenttype'] ?? null;
    $staffperstation = $_POST['staffperstation'] ?? null;

    if ($station && $employmenttype && $staffperstation) {
        $stationInfo = $revenue->createOrUpdateStaffRequestPerStation(
            $jdrequestid,
            $station,
            $employmenttype,
            $staffperstation
        );
        echo $stationInfo;
    }
}

#End HR Settingd
