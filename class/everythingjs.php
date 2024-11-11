<?php
include '../include/config.php';
$response = "";
$loadcountry = "";
$loadallcountry = "";
$fname = $lname = $email = $phoneno = $companyname = $companyaddress = $city = $postalcode = $country = "";
if(isset($_SESSION['username'])){


function check_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
if (isset($_POST["doctoupload"])) {
    $regtype = $_POST['doctoupload'];
    $coydoctouplload = $shipping->loaddocsforregistration($regtype, 'company');
    // $condoctouplload = $shipping->loaddocsforregistration($regtype, 'contact');
    echo $coydoctouplload;
}

if (isset($_POST["condoctoupload"])) {
    $contype = $_POST['condoctoupload'];
    $coydoctouplload = $shipping->loaddocsforregistration($contype, 'contact');
    // $condoctouplload = $shipping->loaddocsforregistration($regtype, 'contact');
    echo $coydoctouplload;
}
if (isset($_POST["odoctoupload"])) {
    $odocstype = $_POST['odoctoupload'];
    $odoctouplload = $shipping->loaddocsforregistration($odocstype, 'Truck');
    // $condoctouplload = $shipping->loaddocsforregistration($regtype, 'contact');
    echo $odoctouplload;
}
if (isset($_POST["ltrucks"])) {
    $odocstype = $_POST['ltrucks'];
    $loadvendtrucks = $shipping->loadtrucks($_SESSION['username']);
    echo $loadvendtrucks;
}
if (isset($_POST['vin'])) {
    $vin = $_POST['vin'];
    $coyname = $_SESSION['coyname'];
    $plateno = $_POST['plateno'];
    $ttplocation = $_POST['ttplocation'];
    $truckreg = $shipping->registertruck($vin, $coyname, $plateno, $ttplocation);
    echo $truckreg;
}
if (isset($_POST['tvin'])) {
    //echo "we are good";
    if (isset($_FILES['tpfile']['name'])) {
        $vin = $_POST['tvin'];
        $companyemail = $_SESSION['username'];; //$_POST['createdby'];
        $plateno = $_POST['tplateno'];
        $extension1 = explode('.', $_FILES['tpfile']['name']);
        $extension = $extension1[1];
        $flname = rand(1000000, 9999999);
        $filename = $_FILES['tpfile']['name'];
        if ($filename != '') {
            $file_basename = $flname;
            $file_ext = $extension;
            $filesize = $_FILES['tpfile']['size'];
            $allowed_file_types = array('PDF', 'pdf', 'PNG', 'png', 'JPEG', 'jpeg', 'JPG', 'jpg');
            $tmpFilePath = $_FILES['tpfile']['tmp_name'];
            if (in_array($file_ext, $allowed_file_types) && ($filesize < 20000000)) {
                $newfilename = $file_basename . '.' . $file_ext;
                if (file_exists('../documentuploads/' . $newfilename)) {

                } else {
                    $newFilePath = '../documentuploads/' . $newfilename;
                    move_uploaded_file($tmpFilePath, $newFilePath);
                    $newFilePath =str_replace('../',"",$newFilePath);
                    $truckpics = $shipping->addtruckpictures($vin, $plateno, $newFilePath, $companyemail);
                    echo $truckpics;
                }
            } elseif ($filesize > 2000000) {
                // file size error
                echo 1; //;
            }

        }
    }
}
if(isset($_POST['truckdocname'])){ 
    //echo "we are good";
    if(isset($_FILES['dpfile']['name'])){
        $companyregtype = $_SESSION['coyname'];
        $companyemail = $_SESSION['username'];
        $documentbelongs = $_POST['documentbelongs'];
        $documentname =  $_POST['truckdocname'];
        $extension1 = explode('.',$_FILES['dpfile']['name']);
        $extension = $extension1[1];
        $flname = rand(1000000,9999999);
        $filename = $_FILES['dpfile']['name'];
        if($filename != ''){
            $file_basename = $flname;
            $file_ext = $extension;
            $filesize = $_FILES['dpfile']['size'];
            $allowed_file_types = array( 'PDF', 'pdf','PNG','png','JPEG','jpeg','JPG','jpg' );
            $tmpFilePath = $_FILES['dpfile']['tmp_name'];
            if (in_array( $file_ext, $allowed_file_types ) && ( $filesize < 20000000 ) )
            {
                $newfilename = $file_basename .'.'.$file_ext;
                if ( file_exists( '../documentuploads/' . $newfilename )){
                    
                }
                else{
                    $newFilePath = '../documentuploads/' . $newfilename;
                    move_uploaded_file( $tmpFilePath, $newFilePath );
                   $documentupload = $shipping->documentupload($companyregtype,$documentname,$documentbelongs,'',$newFilePath,'','');
                   
                }
            }
                    elseif ( $filesize > 2000000 )
                {
                    // file size error
                    echo 1; //;
                }
            
        }
    }
};

// if($_SERVER["REQUEST_METHOD"]=='POST'){
//      if($email ==""){
//         $loadcountries = $marketplace->loadcountries();
//         for($lc = 0; $lc < sizeof($loadcountries); $lc++){
//             $country = $loadcountries[$lc]['country'];
//             $code = $loadcountries[$lc]['code'];
//             $loadcountry = $loadcountry.'<option value ='.$code.'>'.$country.'</option>';
//         }
//         $loadallcountry = '<option>Choose...</option>'.$loadcountry;

//             echo $loadallcountry;
//     }
// }

function randomPassword()
{
    $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
    $pass = array(); //remember to declare $pass as an array
    $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
    for ($i = 0; $i < 8; $i++) {
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    return implode($pass); //turn the array into a string
}

}else{
   
}
// if($_SERVER["REQUEST_METHOD"]=="POST"){
