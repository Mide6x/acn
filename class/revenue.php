<?php
//  use PHPMailer\PHPMailer\PHPMailer;
//  use PHPMailer\PHPMailer\Exception;

//  require '../mail/PHPMailer/src/Exception.php';
//  require '../mail/PHPMailer/src/PHPMailer.php';
//  require '../mail/PHPMailer/src/SMTP.php';
ini_set('max_execution_time', '0');
class revenue
{
    protected $db;
    function __construct($con)
    {
        $this->db = $con;
    }
    public function africaDate()
    {
        date_default_timezone_set('Asia/Dubai');
        $dbDate = date('Y-m-d H:i:s');
        return $dbDate;
    }
    //Load the file names from DB
    public function loadcsvname()
    {
        $sql = "SELECT * FROM csvnames";
        $stmt = $this->db->query($sql);
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $tfilename[] = array('tname' => $rows['tname'], 'tfname' => $rows['tfname'], 'vname' => $rows['vname'], 'vstatement' => $rows['vstatement']);
        }
        return $tfilename;
    }
    #Start Station Details
    //create stations
    public function createstation($station, $stationcode, $stationtype, $operationtype, $status, $createdby, $nextxx)
    {
        $dandt = $this->africaDate();
        $stationname = $this->checkstation($station, $stationcode, $stationtype, $operationtype);
        if (empty($stationname)) {
            $stationname = $this->checkstationeither($station, $stationcode, $stationtype, $operationtype);
            if (empty($stationname)) {
                $sql = "INSERT INTO stationtbl(station,stationcode,stationtype,operationtype,status,createdby,dandt)VALUES('$station','$stationcode','$stationtype','$operationtype','$status','$createdby','$dandt')";
                $stmt = $this->db->query($sql);
                $count = $stmt->rowCount();
            } else {
                $upstationdets = $this->updatestationdetails($station, $stationcode, $stationtype, $operationtype);
            }
            $loadstation = $this->loadstations($stationtype, $status, $nextxx);
        } else {
            $loadstation = "1";
        }
        return $loadstation;
    }
    //check if station exist
    public function checkstation($station, $stationcode, $stationtype, $operationtype)
    {
        if (empty($station)) {
            $sql = "SELECT * from stationtbl where stationtype ='$stationtype' and stationcode = '$stationcode' and operatingtype = '$operationtype'";
        } elseif (empty($stationcode)) {
            $sql = "SELECT * from stationtbl where stationtype ='$stationtype' and station = '$station' and operatingtype = '$operationtype'";
        } elseif (empty($stationtype)) {
            $sql = "SELECT * from stationtbl where stationcode ='$stationcode' and station = '$station' and operatingtype = '$operationtype'";
        } else {
            $sql = "SELECT * from stationtbl where station = '$station' and stationtype ='$stationtype' and stationcode ='$stationcode' and operationtype = '$operationtype'";
        }

        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $coyname = $rows['station'];
        return $coyname;
    }
    public function checkstationeither($station, $stationcode, $stationtype, $operationtype)
    {
        $sql = "SELECT * from stationtbl where (station = '$station' and stationcode ='$stationcode')"; // or (stationtype ='$stationtype' or operationtype = '$operationtype')";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $coyname = $rows['station'];
        return $coyname;
    }
    public function updatestationsatus($station, $sstatus)
    {
        $sql = "UPDATE stationtbl set status ='$sstatus' where station = '$station' ";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    public function updatestationdetails($station, $stationcode, $stationtype, $operationtype)
    {
        $sql = "UPDATE stationtbl set station = '$station', stationcode='$stationcode', stationtype = '$stationtype', operationtype ='$operationtype' where (station = '$station' and  stationcode='$stationcode')"; //and (stationtype = '$stationtype'or operationtype ='$operationtype')
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    public function loadroutes()
    {
        $station = "";
        $sql = "SELECT * FROM stationtbl where status ='Active' order by station ";
        $stmt = $this->db->query($sql);
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $station = $station . '<option value =' . $rows['stationcode'] . '>' . $rows['station'] . '</option>';
        }
        return $station;
    }
    //Get Stations
    public function loadstations($stationtype, $status, $nextxx)
    {
        $station = $stations = $sn = "";
        if ($nextxx <= 50) {
            $nextxx = "0";
        }
        // if($stationtype == '' && $status ==''){
        $sql = "SELECT * FROM stationtbl order by station asc LIMIT $nextxx, 50";
        // }else{
        //     $sql = "SELECT * FROM stationtbl WHERE stationtype = '$stationtype' and status ='$status' order by station asc LIMIT $nextxx,50";
        // }
        $stmt = $this->db->query($sql);
        $sn = 0;
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sn += 1;
            if (trim($rows['status']) == 'Active') {
                $status = "checked";
            } else {
                $status = "unchecked";
            }
            $btntt = '
            <td style="width:5%">
                <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "edstation" value ="edstation" onclick="return editstation(this)"  data-toggle="tooltip" title="Edit"><i class="ri-edit-2-line" style="color:#FC7F14">

            </<td>
            <td style="width:5%">
               <div class="custom-control custom-switch form-group col-md-12" style ="text-align: right;"><input type="checkbox" class="custom-control-input" id="' . trim($rows['station']) . '" onchange ="return enabledisablestations(this.id)" data-toggle="tooltip" title="Change Status"' . $status . '>
                <label class="custom-control-label" for="' . trim($rows['station']) . '"></label></div>
            </<td>';

            $station = $station . '
                <tr>
                    <td>' . $sn . '</td>
                    <td>' . $rows['station'] . '</td>
                    <td>' . $rows['stationcode'] . '</td>
                    <td>' . $rows['stationtype'] . '</td>
                     <td>' . $rows['operationtype'] . '</td>
                    <td>' . $rows['status'] . '</td>
                    ' . $btntt . '
                </tr>';
        }

        $stations = $stations . '
        <div class="table-responsive" style="width: 100%;">
            <table class="table table-striped table-light" style ="font-size: small"  id="stationtbl"  cellspacing="0">
                <thead class="thead-dark" style ="font-size: 0.8rem;">
                    <tr>
                        <th style="width:5%">S/N</th>
                        <th style="width:20%">STATION</th>
                        <th style="width:20%">CODE</th> 
                        <th style="width:20%">ROUTE</th>
                        <th style="width:20%">OPERATION</th>
                        <th style="width:10%">STATUS</th>
                        <th style="width:5%"></th>
                        <th style="width:5%"></th>
                    </tr>
                </thead>
                    
                <tbody>
                    ' . $station . '
                </tbody>
            </table>
              
            </div>';
        return $stations;
    }
    #End Station Details  

    #Start Aircraft Details
    //Create Aircraft Details
    public function createaircraftinfo($acname, $actype, $acregno, $accapacity, $acversion, $acstatus, $createdby, $nextxx)
    {
        $dandt = $this->africaDate();
        $regno = $this->checkaircraft($acregno);
        if (empty($regno)) {
            $sql = "INSERT INTO aircrafttbl(acname,actype,acregno,acversion,accapacity,acstatus,createdby,dandt)VALUES('$acname','$actype','$acregno','$acversion','$accapacity','$acstatus','$createdby','$dandt')";
            $stmt = $this->db->query($sql);
            $count = $stmt->rowCount();
        } else {
            $this->updateaircraftdetails($acname, $actype, $acversion, $accapacity, $acregno);
        }
        $loadacdetails = $this->loadacdetails($acname, $actype, $acstatus, $nextxx);
        return "$loadacdetails";
    }
    //check if station exist
    public function checkaircraft($acregno)
    {
        $sql = "SELECT * from aircrafttbl where acregno = '$acregno'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $regno = $rows['acregno'];
        return $regno;
    }
    public function updateaircraftsatus($acregno, $acstatus)
    {
        $sql = "UPDATE aircrafttbl set acstatus ='$acstatus' where acregno = '$acregno' ";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    public function updateaircraftdetails($acname, $actype, $acversion, $accapacity, $acregno)
    {
        $sql = "UPDATE aircrafttbl set acname = '$acname', actype='$actype', acversion = '$acversion', accapacity ='$accapacity' where acregno ='$acregno'";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    //Get Stations
    public function loadacdetails($acname, $actype, $acstatus, $nextxx)
    {
        $acdetail = $acdetails = $sn = "";
        if ($nextxx <= 50) {
            $nextxx = "0";
        }
        //if($acname =='' && $actype =='' && $acstatus==''){
        $sql = "SELECT * FROM aircrafttbl limit $nextxx, 50";
        // }elseif($acname != ''  && $actype =='' && $acstatus == ''){
        //     $sql = "SELECT * FROM aircrafttbl WHERE acname = '$acname' limit $nextxx, 50";
        // }elseif($acname == ''  && $actype !='' && $acstatus == ''){
        //     $sql = "SELECT * FROM aircrafttbl WHERE actype = '$actype' limit $nextxx, 50";
        // }elseif($acname == ''  && $actype =='' && $acstatus != ''){
        //     $sql = "SELECT * FROM aircrafttbl WHERE acstatus = '$acstatus' limit $nextxx, 50";
        // }elseif($acname != ''  && $actype !='' && $acstatus == ''){
        //     $sql = "SELECT * FROM aircrafttbl WHERE actype = '$actype' and acname = $acname limit $nextxx, 50";
        // }elseif($acname == ''  && $actype !='' && $acstatus != ''){
        //     $sql = "SELECT * FROM aircrafttbl WHERE actype = '$actype' and acstatus = '$acstatus' limit $nextxx, 50";
        // }elseif($acname != ''  && $actype =='' && $acstatus != ''){
        //     $sql = "SELECT * FROM aircrafttbl WHERE acname = '$acname' and acstatus = '$acstatus' limit $nextxx, 50";
        // }elseif($acname != ''  && $actype !='' && $acstatus != ''){
        //     $sql = "SELECT * FROM aircrafttbl WHERE acname = '$acname' and acstatus = '$acstatus' and actype = '$actype' limit $nextxx, 50";
        // }

        $stmt = $this->db->query($sql);
        $sn = 0;

        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sn += 1;
            if (trim($rows['acstatus']) == 'Active') {
                $acstatus = "checked";
            } else {
                $acstatus = "unchecked";
            }
            $btntt = '
            <td style="width:5%">
                <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "edstation" value ="edstation" onclick="return editaircraft(this)"  data-toggle="tooltip" title="Edit"><i class="ri-edit-2-line" style="color:#FC7F14">

            </<td>
            <td style="width:5%">
               <div class="custom-control custom-switch form-group col-md-12" style ="text-align: right;"><input type="checkbox" class="custom-control-input" id="' . trim($rows['acregno']) . '" onchange ="return enabledisableaircraft(this.id)" data-toggle="tooltip" title="Change Status"' . $acstatus . '>
                <label class="custom-control-label" for="' . trim($rows['acregno']) . '"></label></div>
            </<td>';

            $acdetail = $acdetail . '
                <tr>
                    <td>' . $sn . '</td>
                    <td>' . $rows['acname'] . '</td>
                    <td>' . $rows['actype'] . '</td>
                    <td>' . $rows['acregno'] . '</td>
                    <td>' . $rows['acversion'] . '</td>
                     <td>' . $rows['accapacity'] . '</td>
                    <td>' . $rows['acstatus'] . '</td>
                    ' . $btntt . '
                </tr>';
        }

        $acdetails = $acdetails . '
        <div class="table-responsive" style="width: 100%;">
            <table class="table table-striped table-light" style ="font-size: small"  id="aircrafttbl"  cellspacing="0">
                <thead class="thead-dark" style ="font-size: 0.8rem">
                    <tr>
                        <th style="width:5%">S/N</th>
                        <th style="width:10%">NAME</th>
                        <th style="width:10%">TYPE</th> 
                        <th style="width:10%">REG NO</th>
                        <th style="width:10%">VERSION</th> 
                        <th style="width:10%">CAPACITY</th>
                        <th style="width:10%">STATUS</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                    
                <tbody>
                    ' . $acdetail . '
                </tbody>
            </table>
              
            </div>';
        return $acdetails;
    }

    #End Aircraft Details

    #Sart Avaition Charges 
    //create a new avaition charge type
    public function createaviationchargetype($chargetype, $chargebody, $chargename, $isaircraft, $chargestatus, $nextxx, $createdby)
    {
        $dandt = $this->africaDate();
        $avacharge = $this->checkaviationchargetype($chargename, $chargetype);
        if (empty($avacharge)) {
            $sql = "INSERT INTO avachargetype(chargetype,chargebody,chargename,isaircraft,chargestatus,createdby,dandt)VALUES('$chargetype','$chargebody','$chargename','$isaircraft','$chargestatus','$createdby','$dandt')";
            $stmt = $this->db->query($sql);
            $count = $stmt->rowCount();
        } else
            $loadaviationcharge = $this->loadaviationchargetype($chargetype, $chargebody, $chargestatus, $nextxx);
        return $loadaviationcharge;
    }
    //check if aviation charge type exist
    public function checkaviationchargetype($chargename, $chargetype)
    {
        $sql = "SELECT * from avachargetype where chargename = '$chargename' and chargetype ='$chargetype'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $avacharge = $rows['chargename'];
        return $avacharge;
    }
    public function updateaviationchargetypestatus($chargetypeid, $chargetypestatus)
    {
        $sql = "UPDATE avachargetype set chargestatus ='$chargetypestatus' where id = '$chargetypeid'";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    public function updatechargetypedetails($chargetype, $chargebody, $chargename, $isaircraft)
    {
        $sql = "UPDATE aircrafttbl set chargename = '$chargename', isaircraft = '$isaircraft'  where chargetype ='$chargetype' and chargebody = '$chargebody'";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    //Get aviation charge type
    public function loadaviationchargetype($chargetype, $chargebody, $chargestatus, $nextxx)
    {
        $aviachargetype = $aviachargetypes = "";
        if ($nextxx <= 50) {
            $nextxx = "0";
        }
        //if($chargetype =='' && $chargebody =='' && $chargestatus ==''){
        $sql = "SELECT * FROM avachargetype limit $nextxx, 50";
        // }elseif($chargetype != ''  && $chargebody =='' && $chargestatus == ''){
        //     $sql = "SELECT * FROM avachargetype WHERE chargetype = '$chargetype'";
        // }elseif($chargetype == ''  && $chargebody !='' && $chargestatus == ''){
        //     $sql = "SELECT * FROM avachargetype WHERE chargebody = '$chargebody'";
        // }elseif($chargetype == ''  && $chargebody =='' && $chargestatus != ''){
        //     $sql = "SELECT * FROM avachargetype WHERE chargestatus = '$chargestatus'";
        // }elseif($chargetype != ''  && $chargebody !='' && $chargestatus == ''){
        //     $sql = "SELECT * FROM avachargetype WHERE chargetype = '$chargetype' and chargebody = $chargebody";
        // }elseif($chargetype == ''  && $chargebody !='' && $chargestatus != ''){
        //     $sql = "SELECT * FROM avachargetype WHERE chargebody = '$chargebody' and acstatus = '$chargestatus'";
        // }elseif($chargetype != ''  && $chargebody =='' && $chargestatus != ''){
        //     $sql = "SELECT * FROM avachargetype WHERE chargetype = '$chargetype' and chargestatus = '$chargestatus'";
        // }elseif($acname != ''  && $actype !='' && $acstatus != ''){
        //     $sql = "SELECT * FROM avachargetye WHERE chargename = '$chargename' and chargestatus = '$chargestatus' and chargetype = $chargetype";
        // }

        $stmt = $this->db->query($sql);
        $sn = 0;
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sn += 1;
            if (trim($rows['chargestatus']) == 'Active') {
                $chargestatus = "checked";
            } else {
                $chargestatus = "unchecked";
            }
            $btntt = '
                <td style="width:5%">
                    <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "edavachargetype" value ="edavachargetype" onclick="return editchargetype(this)"  data-toggle="tooltip" title="Edit"><i class="ri-edit-2-line" style="color:#FC7F14">
    
                </<td>
                <td style="width:5%">
                   <div class="custom-control custom-switch form-group col-md-12" style ="text-align: right;"><input type="checkbox" class="custom-control-input" id="' . trim($rows['id']) . '" onchange ="return enabledisablechargetype(this.id)" data-toggle="tooltip" title="Change Status"' . $chargestatus . '>
                    <label class="custom-control-label" for="' . trim($rows['id']) . '"></label></div>
                </<td>';

            $aviachargetype = $aviachargetype . '
                <tr>
                    <td>' . $sn . '</td>
                    <td>' . $rows['chargetype'] . '</td>
                    <td>' . $rows['chargebody'] . '</td>
                    <td>' . $rows['chargename'] . '</td>
                    <td>' . $rows['isaircraft'] . '</td>
                    <td>' . $rows['chargestatus'] . '</td>
                    ' . $btntt . '
                </tr>';
        }

        $aviachargetypes = $aviachargetypes . '
        <div class="table-responsive" style="width: 100%;">
            <table class="table table-striped table-light" style ="font-size: small"  id="aircrafttbl"  cellspacing="0">
                <thead class="thead-dark" style ="font-size: 0.8rem">
                    <tr>
                        <th style="width:5%">S/N</th>
                        <th style="width:15%">TYPE</th>
                        <th style="width:15%">BODY</th> 
                        <th style="width:25%">NAME</th>
                        <th style="width:15%">AIRCRAFT</th> 
                        <th style="width:10%">STATUS</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                    
                <tbody>
                    ' . $aviachargetype . '
                </tbody>
            </table>
            
            </div>';
        return $aviachargetypes;
    }
    public function loadchargetype()
    {
        $chargetype = "";
        $sql = "SELECT * FROM aviationcategorytbl where chargecatstatus ='Active' order by aviachargecategory asc ";
        $stmt = $this->db->query($sql);
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $chargetype = $chargetype . '<option value =' . $rows['aviachargecategory'] . '>' . $rows['aviachargecategory'] . '</option>';
        }
        return $chargetype;
    }
    public function loadchargebody()
    {
        $chargebody = "";
        $sql = "SELECT * FROM aviationbodytbl order by aviabody asc ";
        $stmt = $this->db->query($sql);
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $chargebody = $chargebody . '<option value =' . $rows['aviabody'] . '>' . $rows['aviabody'] . '</option>';
        }
        return $chargebody;
    }
    //Create aviation charges
    public function createaviationcharges($chargesname, $aircrafttype, $season, $station, $chargesvaluetype, $chargesvalue, $shargesstatus, $createdby)
    {
        $dandt = $this->africaDate();
        $avacharge = $this->checkaviationcharges($chargesname, $chargesvalue);
        if (empty($avacharge)) {
            $sql = "INSERT INTO avacharges(chargesname,aircrafttype,season,station,chargesvaluetype,chargesvalue,chargestatus,createdby,dandt)VALUES('$chargesname','$aircrafttype','$season','$station','$chargesvaluetype','$chargesvalue','$chargesstatus','$createdby','$dandt')";
        }
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        $loadaviationcharges = $this->loadaviationcharges($chargetype, $chargebody, $chargesstatus);
        return $loadaviationcharge;
    }
    //check if aviation charges exist
    public function checkaviationcharges($chargesname, $aircrafttype, $season, $chargestatus)
    {
        $sql = "SELECT * from avacharges where chargesname = '$chargesname' and aircrafttype ='$aircrafttype' and season = '$season' and chragestatus = '$chargestatus'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $avacharge = $rows['chargesname'];
        return $avacharge;
    }
    //Edit avivation charges 
    public function editaviationcharges($chargesname, $aircrafttype, $season, $chargevalue)
    {
        $sql = "UPDATE avacharges SET chargesvalue = '$chargesvalue' where chargesname = '$chargesname' and season = '$season' and aircrafttype = '$aircrafttype'";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    //Get aviation charge type
    public function loadaviationcharges($chargesname, $aircrafttype, $season, $station, $chargesvaluetype, $chargesstatus, $nextxx)
    {
        $aviacharge = $aviacharges = "";
        if ($chargesname == '' && $aircrafttype == '' && $season == '' && $station == '' && $chargesvaluetype && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges limit $nextxx, 50";
        } elseif ($chargesname != '' && $aircrafttype == '' && $season == '' && $station == '' && $chargesvaluetype == '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' limit $nextxx, 50";
        } elseif ($chargesname == '' && $aircrafttype != '' && $season == '' && $station == '' && $chargesvaluetype == '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE aircrafttype = '$aircrafttype' limit $nextxx, 50";
        } elseif ($chargesname == '' && $aircrafttype == '' && $season != '' && $station == '' && $chargesvaluetype == '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE seasion = '$season' limit $nextxx, 50";
        } elseif ($chargesname == '' && $aircrafttype == '' && $season == '' && $station != '' && $chargesvaluetype == '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE station = '$station' limit $nextxx, 50";
        } elseif ($chargesname == '' && $aircrafttype == '' && $season == '' && $station == '' && $chargesvaluetype != '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE chargevaluetype = '$chargevaluetype' limit $nextxx, 50";
        } elseif ($chargesname == '' && $aircrafttype == '' && $season == '' && $station == '' && $chargesvaluetype == '' && $chargesstatus != '') {
            $sql = "SELECT * FROM avacharges WHERE  chargesstatus = '$chargesstatus' limit $nextxx, 50";
        } elseif ($chargesname != '' && $aircrafttype != '' && $season != '' && $station != '' && $chargesvaluetype != '' && $chargesstatus != '') {
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and aircrafttype ='$aircrafttype' and season = '$season' and station = '$station' and chargesvaluetype='$chargesvaluetype' and chargesstatus = '$chargesstatus' limit $nextxx, 50";
        } elseif ($chargesname != '' && $aircrafttype != '' && $season == '' && $station == '' && $chargesvaluetype == '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and aircrafttype ='$aircrafttype'";
        } elseif ($chargesname != '' && $aircrafttype == '' && $season != '' && $station == '' && $chargesvaluetype == '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and season = '$season' limit $nextxx, 50";
        } elseif ($chargesname != '' && $aircrafttype == '' && $season == '' && $station != '' && $chargesvaluetype == '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname'and station = '$station' limit $nextxx, 50";
        } elseif ($chargesname != '' && $aircrafttype == '' && $season != '' && $station == '' && $chargesvaluetype != '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avachargetye WHERE chargesname = '$chargesname' and chargesvaluetype='$chargesvaluetype' ";
        } elseif ($chargesname != '' && $aircrafttype == '' && $season == '' && $station == '' && $chargesvaluetype == '' && $chargesstatus != '') {
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and chargesstatus = '$chargesstatus' limit $nextxx, 50";
        } elseif ($chargesname == '' && $aircrafttype != '' && $season != '' && $station == '' && $chargesvaluetype == '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE aircrafttype ='$aircrafttype' and season = '$season' limit $nextxx, 50";
        } elseif ($chargesname == '' && $aircrafttype != '' && $season == '' && $station != '' && $chargesvaluetype == '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE aircrafttype ='$aircrafttype' and station = '$station' limit $nextxx, 50";
        } elseif ($chargesname == '' && $aircrafttype != '' && $season == '' && $station == '' && $chargesvaluetype != '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE aircrafttype ='$aircrafttype' and chargesvaluetype='$chargesvaluetype' ";
        } elseif ($chargesname == '' && $aircrafttype != '' && $season == '' && $station == '' && $chargesvaluetype == '' && $chargesstatus != '') {
            $sql = "SELECT * FROM avacharges WHERE aircrafttype ='$aircrafttype' and chargesstatus = '$chargesstatus' limit $nextxx, 50";
        } elseif ($chargesname == '' && $aircrafttype == '' && $season != '' && $station != '' && $chargesvaluetype != '' && $chargesstatus != '') {
            $sql = "SELECT * FROM avacharges WHERE  season = '$season' and station = '$station' limit $nextxx, 50";
        } elseif ($chargesname == '' && $aircrafttype == '' && $season != '' && $station == '' && $chargesvaluetype != '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE season = '$season' and chargesvaluetype='$chargesvaluetype' limit $nextxx, 50";
        } elseif ($chargesname == '' && $aircrafttype == '' && $season != '' && $station == '' && $chargesvaluetype == '' && $chargesstatus != '') {
            $sql = "SELECT * FROM avacharges WHERE  season = '$season' and chargesstatus = '$chargesstatus'";
        } elseif ($chargesname == '' && $aircrafttype == '' && $season == '' && $station != '' && $chargesvaluetype != '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE station = '$station' and chargesvaluetype='$chargesvaluetype'";
        } elseif ($chargesname == '' && $aircrafttype == '' && $season == '' && $station != '' && $chargesvaluetype == '' && $chargesstatus != '') {
            $sql = "SELECT * FROM avacharges WHERE station = '$station' and chargesstatus = '$chargesstatus' limit $nextxx, 50";
        } elseif ($chargesname != '' && $aircrafttype != '' && $season != '' && $station == '' && $chargesvaluetype == '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and aircrafttype ='$aircrafttype' and season = '$season'";
        } elseif ($chargesname != '' && $aircrafttype == '' && $season == '' && $station == '' && $chargesvaluetype != '' && $chargesstatus != '') {
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and station = '$station' and chargesvaluetype='$chargesvaluetype' and chargesstatus = '$chargesstatus' limit $nextxx, 50";
        } elseif ($chargesname == '' && $aircrafttype != '' && $season != '' && $station != '' && $chargesvaluetype == '' && $chargesstatus == '') {
            $sql = "SELECT * FROM avacharges WHERE  aircrafttype ='$aircrafttype' and season = '$season' and station = '$station' limit $nextxx, 50";
        } elseif ($chargesname == '' && $aircrafttype != '' && $season == '' && $station == '' && $chargesvaluetype != '' && $chargesstatus != '') {
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and aircrafttype ='$aircrafttype' and chargesvaluetype='$chargesvaluetype' and chargesstatus = '$chargesstatus' limit $nextxx, 50";
        }

        $stmt = $this->db->query($sql);
        $sn = 0;
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $btntt = '
            <td style="width:5%"> <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "removeproduct" value ="removedproduct" onclick="return removesingleproduct()"  data-toggle="tooltip" title="Delist Product"><img src="../assets/images/delete.ico"/></<td>';
            $aviacharge = $aviacharge . '
                <tr>
                    <td>' . $rows['chargesname'] . '</td>
                    <td>' . $rows['aircrafttype'] . '</td>
                    <td>' . $rows['season'] . '</td>
                    <td>' . $rows['station'] . '</td>
                    <td>' . $rows['chargesvaluetype'] . '</td>
                    <td>' . $rows['chargesvalue'] . '</td>
                    <td>' . $rows['chargesstatus'] . '</td>
                    <td>' . $btntt . '</td>
                </tr>';
        }

        $aviacharges = $aviacharges . '
        <div class="table-responsive" style="width: 100%;">
            <table class="table table-striped table-light" style ="font-size: small"  id="aircrafttbl"  cellspacing="0">
                <thead class="thead-dark" style ="font-size: 0.8rem">
                    <tr>
                        <th style="width:20%">ITEM</th>
                        <th style="width:10%">AIRCRAFT TYPE</th> 
                        <th style="width:10%">SEASON</th>
                        <th style="width:10%">STATION</th> 
                        <th style="width:10%">SEASON</th>
                        <th style="width:10%">VALUE TYPE</th>
                        <th style="width:10%">VALUE</th>
                        <th style="width:10%">STATUS</th>
                        <th></th>
                    </tr>
                </thead>
                    
                <tbody>
                    ' . $aviacharge . '
                </tbody>
            </table>
            
            </div>';
        return $aviacharges;
    }


    #End aviation charges

    #Start Flight Details Setup
    public function createflightdetails($flightno, $schstarttime, $schendtime, $routefrom, $routeto, $crewreporttime, $status, $createdby)
    {
        $dandt = $this->africaDate();
        $dflight = $this->checkflightdetails($flightno);
        if (empty($dflight)) {
            $sql = "INSERT INTO flightdetails(flightno,schstarttime,schendtime,routefrom,routeto,crewreporttime,fltstatus,createdby,dandt)VALUES('$flightno','$schstarttime','$schendtime','$routefrom','$routeto','$crewreporttime','$status','$createdby','$dandt')";
            $stmt = $this->db->query($sql);
            $count = $stmt->rowCount();
        } else {
            $this->updateflightdetails($flightno, $schstarttime, $schendtime, $routefrom, $routeto, $crewreporttime);
        }

        $loadfltdets = $this->loadflightdetails('', '', 0);
        return $loadfltdets;
    }
    //check if station exist
    public function checkflightdetails($flightno)
    {
        $sql = "SELECT * from flightdetails where flightno = '$flightno'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $flightdet = $rows['flightno'];
        return $flightdet;
    }
    public function updateflightstatus($flightno, $fltstatus)
    {
        $sql = "UPDATE flightdetails set fltstatus ='$fltstatus' where fltightno = '$flightno' ";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    public function updateflightdetails($flightno, $schstarttime, $schendtime, $routefrom, $routeto, $crewreporttime)
    {
        $sql = "UPDATE flightdetails set schstarttime = '$schstarttime', schendtime = '$schendtime', routefrom = '$routefrom', routeto ='$routeto',crewreporttime = '$crewreporttime' where flightno ='$flightno'";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    //Get Stations
    public function loadflightdetails($flightno, $fltstatus, $nextxx)
    {
        $flightdet = $flightdets = "";
        // if($flightno == '' && $status =='' && $route == ''){
        $sql = "SELECT * FROM flightdetails LIMIT $nextxx, 50";
        // }elseif($flightno != '' && $status =='' && $route == ''){
        //     $sql = "SELECT * FROM flightdetals WHERE flightno = '$flightno' LIMIT $nextxx,50";
        // }elseif($flightno == '' && $status !='' && $route == ''){
        //     $sql = "SELECT * FROM flightdetals WHERE status = '$status' LIMIT $nextxx,50";
        // }elseif($flightno == '' && $status =='' && $route != ''){
        //     $sql = "SELECT * FROM flightdetals WHERE route = '$route' LIMIT $nextxx,50";
        // }elseif($flightno != '' && $status !='' && $route == ''){
        //     $sql = "SELECT * FROM flightdetals WHERE flightno = '$flightno' and status ='$status' LIMIT $nextxx,50";
        // }elseif($flightno != '' && $status =='' && $route != ''){
        //     $sql = "SELECT * FROM flightdetals WHERE flightno = '$flightno' and route = '$route' LIMIT $nextxx,50";
        // }elseif($flightno == '' && $status !='' && $route != ''){
        //     $sql = "SELECT * FROM flightdetals WHERE status = '$status' and route ='$route' LIMIT $nextxx,50";
        // }
        $stmt = $this->db->query($sql);
        $sn = 0;
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sn += 1;
            if (trim($rows['fltstatus']) == 'Active') {
                $fltstatus = "checked";
            } else {
                $fltstatus = "unchecked";
            }
            $btntt = '
                <td style="width:5%">
                    <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "edflightdet" value ="edflightdet" onclick="return editafightdetails(this)"  data-toggle="tooltip" title="Edit"><i class="ri-edit-2-line" style="color:#FC7F14">
    
                </<td>
                <td style="width:5%">
                   <div class="custom-control custom-switch form-group col-md-12" style ="text-align: right;"><input type="checkbox" class="custom-control-input" id="' . trim($rows['flightno']) . '" onchange ="return enabledisableaircraft(this.id)" data-toggle="tooltip" title="Change Status"' . $fltstatus . '>
                    <label class="custom-control-label" for="' . trim($rows['flightno']) . '"></label></div>
                </<td>';


            $flightdet = $flightdet . '
                <tr>
                    <td>' . $sn . '</td>
                    <td>' . $rows['flightno'] . '</td>
                    <td>' . $rows['schstarttime'] . '</td>
                    <td>' . $rows['schendtime'] . '</td>
                    <td>' . $rows['routefrom'] . '</td>
                    <td>' . $rows['routeto'] . '</td>
                    <td>' . $rows['crewreporttime'] . '</td>
                    <td>' . $rows['fltstatus'] . '</td>
                    <td>' . $btntt . '</td>
                </tr>';
        }

        $flightdets = $flightdets . '
        <div class="table-responsive" style="width: 100%;">
            <table class="table table-striped table-light" style ="font-size: small"  id="flightdettbl"  cellspacing="0">
                <thead class="thead-dark" style ="font-size: 0.8rem">
                    <tr>
                        <th style="width:5%">S/N</th> 
                        <th style="width:15%">FLIGHT NO</th>
                        <th style="width:20%">SCH START TIME</th> 
                        <th style="width:20%">SCH END TIME</th>
                        <th style="width:15%">FROM</th>
                        <th style="width:10%">TO</th>
                        <th style="width:10%">CRT</th>
                        <th style="width:10%">STATUS</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                    
                <tbody>
                    ' . $flightdet . '
                </tbody>
            </table>
              
            </div>';
        return $flightdets;
    }
    #End Flight Details Setup

    #Start Menu
    public function createmenu($menuname, $menulink, $menutitle, $menustatus, $createdby, $nextxx)
    {
        $dandt = $this->africaDate();
        $lmenu = $this->checkmenu($menulink);
        if (empty($lmenu)) {
            $sql = "INSERT INTO menutbl(menuname,menulink,menutitle,menustatus,createdby,dandt)VALUES('$menuname','$menulink','$menutitle','$menustatus','$createdby','$dandt')";
            $stmt = $this->db->query($sql);
            $count = $stmt->rowCount();
        } else {
            $this->updatemenudetails($menuname, $menulink, $menutitle);
        }
        $loadmenu = $this->loadmenu($nextxx);
        return $loadmenu;
    }
    //check if station exist
    public function checkmenu($menulink)
    {
        $sql = "SELECT * from menutbl where menulink = '$menulink'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $lmenu = $rows['menulink'];
        return $lmenu;
    }
    public function updatemenustatus($menuid, $menustatus)
    {
        $sql = "UPDATE menutbl set menustatus ='$menustatus' where id = '$menuid' ";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    public function updatemenudetails($menuname, $menulink, $menutitle)
    {
        $sql = "UPDATE menutbl set menuname= '$menuname',menutitle = '$menutitle' where menulink ='$menulink'";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    //Load Menu
    public function loadmenu($nextxx)
    {
        $menudet = $menudets = $sn = "";
        if ($nextxx <= 50) {
            $nextxx = "0";
        }
        $sql = "SELECT * FROM menutbl limit $nextxx, 50";
        $stmt = $this->db->query($sql);
        $sn = 0;
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sn += 1;
            if (trim($rows['menustatus']) == 'Active') {
                $menustatus = "checked";
            } else {
                $menustatus = "unchecked";
            }
            $btntt = '
            <td style="width:5%">
                <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "edmenu" value ="edstation" onclick="return editmenu(this)"  data-toggle="tooltip" title="Edit"><i class="ri-edit-2-line" style="color:#FC7F14">

            </<td>
            <td style="width:5%">
            <div class="custom-control custom-switch form-group col-md-12" style ="text-align: right;"><input type="checkbox" class="custom-control-input" id="' . trim($rows['id']) . '" onchange ="return enabledisablemenu(this.id)" data-toggle="tooltip" title="Change Status"' . $menustatus . '>
                <label class="custom-control-label" for="' . trim($rows['id']) . '"></label></div>
            </<td>';

            $menudet = $menudet . '
                <tr>
                    <td>' . $rows['id'] . '</td>
                    <td>' . $rows['menuname'] . '</td>
                    <td>' . $rows['menulink'] . '</td>
                     <td>' . $rows['menutitle'] . '</td>
                    <td>' . $rows['menustatus'] . '</td>
                    ' . $btntt . '
                </tr>';
        }

        $menudets = $menudets . '
        <div class="table-responsive" style="width: 100%;">
            <table class="table table-striped table-light" style ="font-size: small"  id="menutbl"  cellspacing="0">
                <thead class="thead-dark" style ="font-size: 0.8rem">
                    <tr>
                        <th style="width:5%">S/N</th>
                        <th style="width:10%">NAME</th>
                        <th style="width:10%">LINK</th> 
                         <th style="width:10%">PAGE TITLE</th> 
                        <th style="width:10%">STATUS</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                    
                <tbody>
                    ' . $menudet . '
                </tbody>
            </table>
            
            </div>';
        return $menudets;
    }
    //Load Menuchecklist
    public function loadmenuchecklist()
    {
        $menucheck = $menuchecks = $sn = "";
        // $lmenu = $this->checkmenu($menulink);
        $sql = "SELECT * FROM menutbl";
        $stmt = $this->db->query($sql);
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $menucheck = $menucheck . "<li style='margin-right: 20px;'><input type='checkbox' name='item[]' style='margin-right: 5px;' value='" . trim($rows['menuname']) . "'><label for='" . trim($rows['id']) . "'>" . trim($rows['menuname']) . "</label></li>";
        }
        $menuchecks = '<label for="menuchecklist" class="col-sm-2 col-form-label">Menus</label>
                         <div class="col-sm-10">
                            <ul style="display: flex; list-style-type: none;padding: 0;" id ="menuchecklist">
                                    ' . $menucheck . '
                            </ul>
                        </div>';
        return $menuchecks;
    }
    #End Menu

    #Start Roles
    public function createrole($rolename, $roleitems, $rolestatus, $createdby)
    {
        $dandt = $this->africaDate();
        $lrole = $this->checkrole($rolename, $roleitems);
        if (empty($lrole)) {
            //$menuItems = implode(',', $roleitems);
            $sql = "INSERT INTO roletbl(rolename,menuname,rolestatus,createdby,dandt) VALUES ('$rolename','$roleitems','$rolestatus','$createdby','$dandt')";
            $stmt = $this->db->query($sql);
            $count = $stmt->rowCount();
        } else {
            // $menuitems = implode(',', $roleitems);
            // $this->updateroledetails($rolename,$menuitems);
        }
        $loadroles = $this->loadroles();
        return $loadroles;
    }
    public function checkrole($rolename, $roleitems)
    {
        $sql = "SELECT * from roletbl where rolename = '$rolename' and menuname = '$roleitems'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $lrole = $rows['rolename'];
        return $lrole;
    }

    public function updaterolestatus($roleid, $rolestatus)
    {
        $sql = "UPDATE roletbl set rolestatus ='$rolestatus' where id = '$roleid' ";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    public function updateroledetails($rolename, $roleitems)
    {
        $sql = "UPDATE roletbl set menuname = '$roleitems' where rolename = '$rolename'";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    //Get Stations
    public function loadroles()
    {
        $roledet = $roledets = $sn = "";

        $sql = "SELECT * FROM roletbl";
        $stmt = $this->db->query($sql);
        $sn = 0;
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sn += 1;
            if (trim($rows['rolestatus']) == 'Active') {
                $rolestatus = "checked";
            } else {
                $rolestatus = "unchecked";
            }
            $btntt = '  <td style="width:5%">
                <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "edrole" onclick="return editrole(this)"  data-toggle="tooltip" title="Edit"><i class="ri-edit-2-line" style="color:#FC7F14">

            </<td>
            <td style="width:5%">
            <div class="custom-control custom-switch form-group col-md-12" style ="text-align: right;"><input type="checkbox" class="custom-control-input" id="' . trim($rows['id']) . '" onchange ="return enabledisablerolemenu(this.id)" data-toggle="tooltip" title="Change Status"' . $rolestatus . '>
                <label class="custom-control-label" for="' . trim($rows['id']) . '"></label></div>
            </<td>';

            $roledet = $roledet . '
                <tr>
                    <td>' . $rows['id'] . '</td>
                    <td>' . $rows['rolename'] . '</td>
                    <td>' . $rows['menuname'] . '</td>
                    <td>' . $rows['rolestatus'] . '</td>
                    ' . $btntt . '
                </tr>';
        }

        $roledets = $roledets . '
        <div class="table-responsive" style="width: 100%;">
            <table class="table table-striped table-light" style ="font-size: small"  id="roletbl"  cellspacing="0">
                <thead class="thead-dark" style ="font-size: 0.8rem">
                    <tr>
                        <th style="width:5%">S/N</th>
                        <th style="width:20%">NAME</th>
                        <th style="width:40%">MENU</th> 
                        <th style="width:20%">STATUS</th>
                        <th></th>
                        <th></th>
                    </tr>
                </thead>
                    
                <tbody>
                    ' . $roledet . '
                </tbody>
            </table>
            
            </div>';
        return $roledets;
    }
    // public function getuserrole() {
    //     $urole = "";
    //     $query = "select * from roletbl where rolestatus ='Active'";
    //     $stmt = $this->db->query($query);   
    //     while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
    //         $urole = $urole.'<option>'.$rows['rolename'].'</option>';
    //     }
    //     return '<option></option>'.$urole;
    // }
    public function loadrolechecklist()
    {
        $rolecheck = $rolechecks = $sn = "";
        // $lmenu = $this->checkmenu($menulink);
        $sql = "SELECT distinct(rolename) FROM roletbl";
        $stmt = $this->db->query($sql);
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rolecheck = $rolecheck . "<li style='margin-right: 20px;'><input type='checkbox' name='item[]' style='margin-right: 5px;' value='" . trim($rows['rolename']) . "'><label for='" . trim($rows['rolename']) . "'>" . trim($rows['rolename']) . "</label></li>";
        }
        $rolechecks = '
                            <ul style="display: flex; list-style-type: none;padding: 0;" id ="menuchecklist">
                                    ' . $rolecheck . '
                            </ul>
                       ';
        return $rolechecks;
    }

    #End Role

    #Start create user
    public function createuser($cstaffid, $cstation, $ctitle, $clastname, $cfirstname, $cemailaddress, $cbusinessunit, $cdepartment, $cdeptunit, $cuserroles, $pword, $createdby)
    {
        $dandt = $this->africaDate();
        $cuser = $this->checkuser($cstaffid);
        if ($cuser == false) {
            $sql = "INSERT INTO userregistration(staffid,station,title,lastname,firstname,emailaddress,businessunit,department,deptunit,ustatus,createdby,dandt)VALUES('$cstaffid','$cstation','$ctitle','$clastname','$cfirstname','$cemailaddress','$cbusinessunit','$cdepartment','$cdeptunit','Active','$createdby','$dandt')";
            $stmt = $this->db->query($sql);
            $count = $stmt->rowCount();
            $this->userlogin($cstaffid, $cemailaddress, $pword, $createdby);
            $this->assignuserrole($cstaffid, $cuserroles, $createdby);
            $loaduser = $this->loaduser();
            return $loaduser;
        } else {
            return 1;
        }
    }
    public function loadbusinessunit()
    {
        $bizunit = "";
        $sql = "SELECT * FROM businessunittbl where status ='Active' order by businessunit  ";
        $stmt = $this->db->query($sql);
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $bizunit = $bizunit . '<option value =' . $rows['buscode'] . '>' . $rows['businessunit'] . '</option>';
        }
        return $bizunit;
    }
    public function loaddepartment($bizcode)
    {
        $departs = "";
        $sql = "SELECT departmenttbl.businessunit, departmenttbl.deptcode as deptcode, departments.departmentname as deptname FROM departmenttbl INNER JOIN departments ON departmenttbl.deptcode = departments.departmentcode where depstatus ='Active' and businessunit = '$bizcode' order by department asc ";
        $stmt = $this->db->query($sql);
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $departs = $departs . '<option value =' . $rows['deptcode'] . '>' . $rows['deptname'] . '</option>';
        }
        return $departs;
    }
    public function loaddeptunit($deptcode)
    {
        $departs = "";
        $sql = "SELECT * FROM departmentunit where status ='Active' and deptcode = '$deptcode' order by deptunitname asc";
        $stmt = $this->db->query($sql);
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $departs = $departs . '<option value =' . $rows['deptunitcode'] . '>' . $rows['deptunitname'] . '</option>';
        }
        return $departs;
    }
    public function userlogin($cstaffid, $uname, $password, $createdby)
    {
        $dandt = $this->africaDate();
        $sql = "INSERT INTO userlogintbl(staffid,uname,pword,dpword,ustatus,createdby,dandt)VALUES('$cstaffid','$uname','$password','0','Active','$createdby','$dandt')";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    public function assignuserrole($cstaffid, $cuserroles, $createdby)
    {
        $dandt = $this->africaDate();
        $curoles = implode(',', $cuserroles);
        $sql = "INSERT INTO userrole(staffid,roles,createdby,dandt)VALUES('$cstaffid','$curoles','$createdby','$dandt')";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    //check if station exist
    public function checkuser($staffid)
    {
        $sql = "SELECT * from userregistration where staffid = '$staffid'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $cstaffid = $rows['staffid'];
        return $cstaffid;
    }
    public function updateuserstatus($staffid, $staffstatus)
    {
        $sql = "UPDATE userregistration set ustatus ='$staffid' where ustatus = '$staffstatus' ";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    //Load Menu
    public function loaduser()
    {
        $userdet = $userdets = $sn = "";
        $sql = "SELECT * FROM userregistration";
        $stmt = $this->db->query($sql);
        $sn = 0;
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sn += 1;
            if (trim($rows['ustatus']) == 'Active') {
                $ustatus = "checked";
            } else {
                $ustatus = "unchecked";
            }
            $btntt = '
            <td style="width:5%">
            <div class="custom-control custom-switch form-group col-md-12" style ="text-align: right;"><input type="checkbox" class="custom-control-input" id="' . trim($rows['staffid']) . '" onchange ="return enabledisablemenu(this.id)" data-toggle="tooltip" title="Change Status"' . $ustatus . '>
                <label class="custom-control-label" for="' . trim($rows['staffid']) . '"></label></div>
            </td>';

            $userdet = $userdet . '
                <tr>
                    <td>' . $rows['id'] . '</td>
                    <td>' . $rows['staffid'] . '</td>
                    <td>' . $rows['lastname'] . ' ' . $rows['firstname'] . '</td>
                    <td>' . $rows['emailaddress'] . '</td>
                    <td>' . $rows['ustatus'] . '</td>
                    ' . $btntt . '
                </tr>';
        }

        $userdets = $userdets . '
        <div class="table-responsive" style="width: 100%;">
            <table class="table table-striped table-light" style ="font-size: small"  id="aircrafttbl"  cellspacing="0">
                <thead class="thead-dark" style ="font-size: 0.8rem">
                    <tr>
                        <th style="width:10%">S/N</th>
                         <th style="width:20%">STAFF ID</th>
                        <th style="width:20%">NAME</th>
                        <th style="width:20%">EMAIL ADDRESS</th> 
                        <th style="width:20%">STATUS</th>
                        <th></th>
                    </tr>
                </thead>
                    
                <tbody>
                    ' . $userdet . '
                </tbody>
            </table>
            
            </div>';
        return $userdets;
    }
    //End User Creation

    #Email sending and Notification
    public function insertemail($emaifrom, $emailto, $emailsubject, $emailbody)
    {
        echo 'entered';
        $dandt = $this->africaDate();
        $sql = "INSERT INTO emaillog(emailfrom,emailto,emailsubject,emailbody,sentat,status)VALUES('$emaifrom','$emailto','$emailsubject','$emailbody','$dandt','Pending')";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    public function getnotificationbytitle($title)
    {
        $sql = "SELECT * FROM notifytemplate WHERE title = '$title' AND emailstatus = 'Active'";
        $stmt = $this->db->query($sql);
        $stmt->execute();
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $testdata = array('emailsubject' => $row['emailsubject'], 'emailbody' => $row['emailbody'], 'createdby' => $row['createdby']);
        }
        return $testdata;
    }

    #End Email Notification

    #Authentication and Authorisation
    public function authenticate($username, $password)
    {
        //print_r('it worked');
        $sql = "SELECT userlogintbl.staffid, userlogintbl.pword, userlogintbl.dpword, userregistration.lastname as lname, userregistration.firstname as fname, userregistration.station,userlogintbl.ustatus FROM userlogintbl INNER JOIN userregistration ON userlogintbl.staffid = userregistration.staffid where(uname = '$username' or userlogintbl.staffid = '$username')";
        print_r($sql);
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        // print_r($rows);
        // print_r($password);
        $pword = $rows['pword'];
        $dpword = $rows['dpword'];
        $ustatus = $rows['ustatus'];
        if ($ustatus == 'Active') {

            //print_r('Success');
            if (password_verify($password, $pword)) {
                // print_r('Success'.password_verify($password, $pword));
                $_SESSION['staffid'] = $rows['staffid'];
                $_SESSION['stnames'] = $rows['lname'] . ' ' . $rows['fname'];
                if ($dpword == '0') {
                    print_r('Success');
                    header("Location: changepassword.php");
                    //echo 'success';
                } else {
                    header("Location: dashboard.php");
                    // $listmenu = $this->getuserroles($_SESSION['staffid']);
                    //return $listmenu;
                }
            } else {
                print_r('not successful');
                echo "<script>alert(Invalid Username or Password);</script>";
                return 'Invalid Username or Password';
            }
        } else {
            return 'Your Account has been decativated. Kindly contact support';
        }

        //return $regno;
    }

    public function getuserroles($staffid)
    {
        $menulist = $menulst = $mainmenu = "";
        $sql = "SELECT * FROM userrole WHERE staffid = '$staffid'";
        //print_r($sql);
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        //print_r(explode(',',$rows['roles']));
        $uroles = explode(',', $rows['roles']);
        //$rimage = $rows['roleimage'];
        //print_r($uroles);
        foreach ($uroles as $urole) {
            //print_r($urole);
            $menulst =  $this->getumenubyrole($urole);
            // $menulist = $menulist.'<li class="nav-heading">'.$urole.'</li>'.$menulst;
            $mainmenu = $mainmenu . '
            <a class="nav-link " data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
            <span>' . $urole . '</span><i class="bi bi-chevron-down ms-auto"></i>
            </a>
            <ul id="charts-nav" class="nav-content collapse show" data-bs-parent="#sidebar-nav">
        ' . $menulst . '
            </ul>
        ';
        }

        $menulist = '<ul class="sidebar-nav" id="sidebar-nav"><li class="nav-item">
        <a class="nav-link " data-bs-target="#charts-nav" data-bs-toggle="collapse" href="#">
                ' . $mainmenu . '
        </li></ul>';


        return $menulist;
    }
    public function getumenubyrole($rolename)
    {
        $mlisting = "";
        // $lmenu = $this->checkmenu($menulink);
        $sql = "SELECT menutbl.menuname as menname, menutbl.menulink as menlink FROM roletbl INNER JOIN menutbl ON roletbl.menuname = menutbl.menuname where rolename = '$rolename' and menutbl.menustatus ='Active' order by menutbl.menuname asc";
        //print_r($sql);
        //die;()
        $stmt = $this->db->query($sql);

        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // $mlisting = $mlisting.' <li class="nav-item">
            //   <a class="nav-link collapsed" href='.$rows['menlink'].'>
            //     <span>'.$rows['menname'].'</span>
            //   </a>
            // </li>';

            $mlisting = $mlisting . '<li>
            <a href=' . $rows['menlink'] . '>
        <span style="font-size:small; font-weight:100">' . $rows['menname'] . '</span>
            </a>
            </li>';
        }
        //print_r($mlisting);

        return $mlisting;
    }
    #End Authentication

    #Profiling
    # Bio information 
    //Create Bio Data
    public function createbiodata($staffid, $btitle, $bsurname, $bfirstname, $bmiddlename, $bmaidenname, $bregligion, $bemail, $bphonenumber, $bdob, $bgender, $bmstatus, $blangspoken, $bnationality, $bstateoforigin, $blga, $braddress, $bstateofres, $bcountryofres, $bgovname, $bgovidno, $porganisation, $pensionid, $taxid)
    {
        $datetime = $this->africadate();
        $regno = rand(100000, 999999);
        $_SESSION['regno'] = $regno;
        $biodata = $this->checkbiodata($regno, $staffid);
        if (empty($biodata)) {
            $sql = "INSERT INTO biodata(rogno,staffid,title,surname,firstname,middlename,maidenname,religion,emailaddress,phoneno,nationality, languagespoken, dob, phonenumber,dob,gender,maritalstatus,nationalilty,stateoforigin,lga,raddress,rcountry,govname,govidno,porganisation,pensionid,taxid,bstatus,createdby,dandt) VALUES
                ('$regno''$staffid','$btitle', '$bsurname', '$bfirstname', '$bmiddlename','$bmaidenname','$bregligion','$bemail','$bphonenumber','$bdob','$bgender','$bmstatus','$bnationality', '$blangspoken', '$bstateoforigin', '$blga', '$braddress','$bstateofres', '$bcountryofres', '$bgovname','$bgovidno','$porganisation','$pensionid','$taxid','Pending','$regno','$datetime')";
            $stmt = $this->db->query($sql);
            $count = $stmt->rowCount();
        }
        return $count;
    }
    public function createfamilyinfo($regno, $staffid, $fname, $frelationship, $fdob, $foccupa)
    {
        $datetime = $this->africadate();
        $regno = rand(100000, 999999);
        $biodata = $this->checkbiodata($regno, $staffid);
        if (empty($biodata)) {
            $sql = "INSERT INTO biodata(rogno,staffid,) VALUES
                ('$regno''$staffid','$btitle', '$bsurname', '$bfirstname', '$bmiddlename','$bmaidenname','$bregligion','$bemail','$bphonenumber','$bdob','$bgender','$bmstatus','$bnationality', '$blangspoken', '$bstateoforigin', '$blga', '$braddress','$bstateofres', '$bcountryofres', '$bgovname','$bgovidno','$porganisation','$pensionid','$taxid','Pending','$regno','$datetime')";
            $stmt = $this->db->query($sql);
            $count = $stmt->rowCount();
        }
        return $count;
    }
    //Check if Bio Data Exists
    public function checkbiodata($regno, $staffid)
    {
        $sql = "SELECT * FROM biodata WHERE email = '$regno' or staffid = '$staffid'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rows['regno'];
    }
    //update status
    public function updatebiostatus($regno, $staffid)
    {
        $sql = "UPDATE biodata SET status = 'Completed' WHERE email = '$regno' or staffid = '$staffid'";
        $this->db->query($sql);
    }
    //Update Bio Data
    public function updatebiodata($nationality, $languagespoken, $phonenumber, $email, $staffid)
    {
        $sql = "UPDATE biodata SET nationality = '$nationality', languagespoken = '$languagespoken',  phonenumber = '$phonenumber' 
            WHERE email = '$email' AND staffid = '$staffid'";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    # End Bio information




    # Next of kin
    //create next of kin info
    public function createnextofkininfo(
        $email,
        $staffid,
        $noksurname,
        $nokfname,
        $nokphone,
        $nokgender,
        $nokemail,
        $nokaddress,
        $noknationality,
        $nokstate,
        $nokrelationship
    ) {
        //$biodata = $this->checkbiodata($email, $staffid);
        //if(!empty($biodata)){
        $nokinfo = $this->checknextofkininfo($nokemail);
        if (empty($nokinfo)) {
            $sql = "INSERT INTO nextofkin(email, staffid, noksurname, nokfname, nokphone, nokgender, nokemail, nokaddress, noknationality, nokstate, nokrelationship)
                    VALUES ('$email', '$staffid', '$noksurname', '$nokfname', '$nokgender', '$nokphone', '$nokemail', '$nokaddress', '$noknationality', '$nokstate', '$nokrelationship')";
            $stmt = $this->db->query($sql);
            $count = $stmt->rowCount();
            if ($count == 1) {
                $status = "Approved";
            }
        } else {
            $status = "Approved";
        }
        //}else{
        //$status = "Disapproved";
        //}
        return $status;
    }
    //check if next of kin info exists
    public function checknextofkininfo($nokemail)
    {
        $sql = "SELECT * FROM nextofkin WHERE nokemail = '$nokemail'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rows['nokemail'];
    }
    //update next of kin info
    public function updatenextofkininfo($email, $staffid, $nokphone, $nokaddress, $nokcity, $nokstate, $nokrelationship)
    {
        $sql = "UPDATE nextofkin SET nokphone = '$nokphone', nokaddress = '$nokaddress' nokcity = '$nokcity' nokstate = '$nokstate' nokrelationship = '$nokrelationship' WHERE email = '$email' AND staffid = '$staffid'";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    # End Next of kin




    # Employment information
    //create employment info
    public function createemploymentinfo($staffid, $email, $rank, $base, $callsign, $employmentdate)
    {
        $biodata = $this->checkbiodata($email, $staffid);
        if (!empty($biodata)) {
            $employmentinfo = $this->checkemploymentinfo($staffid, $email);
            if (empty($employmentinfo)) {
                $sql = "INSERT INTO employmentinfo(staffid, email, rank, base, callsign, employmentdate) VALUES('$staffid', '$email', '$rank',
                    '$base', '$callsign', '$employmentdate')";
                $stmt = $this->db->query($sql);
                $count = $stmt->rowCount();
            } else {
                $count = "1";
            }
        } else {
            $count = "0";
        }
        return $count;
    }

    //load base
    public function loadbase()
    {
        $sql = "SELECT * FROM base";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rows;
    }
    //check if employment info exists
    public function checkemploymentinfo($staffid, $email)
    {
        $sql = "SELECT * FROM employmentinfo WHERE staffid = '$staffid' AND email = '$email'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rows['staffid'];
    }
    //update employment info
    public function updateemploymentinfo($staffid, $email, $rank, $base, $callsign)
    {
        $sql = "UPDATE employmentinfo SET rank = '$rank', base = '$base', callsign = '$callsign' WHERE staffid = '$staffid' AND email = '$email'";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    # End Employment information

    # Professional information
    //create professional info
    public function createprofessionalinfo($staffid, $email, $crewlicensenumber, $licensetype, $licenseupload, $dateofissue, $expiry)
    {
        $employmentinfo = $this->checkemploymentinfo($staffid, $email);
        if (!empty($employmentinfo)) {
            $checkprofessionalinfo = $this->checkprofessionalinfo($staffid, $email);
            if (empty($checkprofessionalinfo)) {
                $sql = "INSERT INTO professionalinfo(staffid, email, crewlicensenumber, dateofissue, expiry, licensetype, actype, licenseupload)
                    VALUES('$staffid', '$email', '$crewlicensenumber', '$dateofissue', '$expiry', '$licensetype', '$licenseupload')";
                $stmt = $this->db->query($sql);
                $count = $stmt->rowCount();
                if ($count == 1) {
                    $status = "Approved";
                }
            } else {
                $status = "Approved";
            }
        } else {
            $status = "Disapproved";
        }
        return $status;
    }

    //load actype
    public function loadactype()
    {
        $sql = "SELECT * FROM actype";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rows;
    }
    //load professional info
    public function loadprofessionalinformation($staffid, $email)
    {
        $professionalrows = $professionalrow = "";
        $sql = "SELECT * FROM professionalinfo WHERE staffid = '$staffid' AND email = '$email'";
        $stmt = $this->db->query($sql);
        $sn = 0;
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sn += 1;
            $status = $rows['expiry'];
            $btntt = '
                <td style="width:5%">
                    <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "edstation" value ="edexperience" onclick="return editstation(this)"  data-toggle="tooltip" title="Edit"><i class="ri-edit-2-line" style="color:#FC7F14">
                </<td>
    
                <td style="width:5%">
                   <div class="custom-control custom-switch form-group col-md-12" style ="text-align: right;"><input type="checkbox" class="custom-control-input" id="' . trim($rows['crewlicensenumber']) . '" onchange ="return enabledisablestations(this.id)" data-toggle="tooltip" title="Change Status"' . $status . '>
                    <label class="custom-control-label" for="' . trim($rows['crewlicensenumber']) . '"></label></div>
                </<td>';
            $professionalrow .= '
                <tr>
                    <td>' . $sn . '</td>
                    <td>' . $rows['crewlicensenumber'] . '</td>
                    <td>' . $rows['dateofissue'] . '</td>
                    <td>' . $rows['expiry'] . '</td>
                    <td>' . $rows['licensetype'] . '</td>
                    <td>' . $rows['uploadedlicense'] . '</td>
                    ' . $btntt . '
                </tr>';
        }
        $professionalrows .= '
            <div class="table-responsive" style="width: 100%;">
                <table class="table table-striped table-light" style ="font-size: small"  id="stationtbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem;">
                        <tr>
                            <th style="width:5%">P/N</th>
                            <th style="width:20%">CREW LICNESE NUMBER</th>
                            <th style="width:20%">DATE OF ISSUE</th> 
                            <th style="width:20%">EXPIRY</th>
                            <th style="width:20%">PLACE OF ISSUE</th>
                            <th style="width:20%">LICENSE TYPE</th>
                            <th style="width:20%">UPLOADED LICENSE</th>
                            <th style="width:10%">STATUS</th>
                            <th style="width:5%"></th>
                            <th style="width:5%"></th>
                        </tr>
                    </thead>
                        
                    <tbody>
                        ' . $professionalrow . '
                    </tbody>
                </table>
                
                </div>';
        return $professionalrows;
    }
    //check if profesional info exists
    public function checkprofessionalinfo($staffid, $email)
    {
        $sql = "SELECT * FROM professionalinfo WHERE staffid = '$staffid' AND email = '$email'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rows['email'];
    }
    //update professional info
    public function updateprofessionalinfo($staffid, $email, $crewlicensenumber)
    {
        $sql = "UPDATE professionalinfo SET crewlicensenumber = '$crewlicensenumber' WHERE staffid = '$staffid' AND email = '$email'";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    # End Professional information

    # Exprience information
    //create experience information
    public function createexperienceinformation($staffid, $email, $actype, $hours, $position, $atdate)
    {
        $professionalinfo = $this->checkprofessionalinfo($staffid, $email);
        if (!empty($professionalinfo)) {
            $sql = "INSERT INTO experienceinfo(staffid, email, actype, position, hours, atdate) VALUES('$staffid', '$email', '$actype', '$position', '$hours', '$atdate)";
            $stmt = $this->db->query($sql);
            $count = $stmt->rowCount();
        } else {
            $count = "0";
        }
        return $count;
    }
    //get experience table in form of a table
    public function loadexperienceinformation($staffid, $email)
    {
        $experiencerows = $experiencerow = "";
        $sql = "SELECT * FROM experienceinfo WHERE staffid = '$staffid' AND email = '$email'";
        $stmt = $this->db->query($sql);
        $sn = 0;
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sn += 1;
            $status = $rows['hours'];
            $btntt = '
                <td style="width:5%">
                    <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "edstation" value ="edexperience" onclick="return editstation(this)"  data-toggle="tooltip" title="Edit"><i class="ri-edit-2-line" style="color:#FC7F14">
                </<td>
    
                <td style="width:5%">
                   <div class="custom-control custom-switch form-group col-md-12" style ="text-align: right;"><input type="checkbox" class="custom-control-input" id="' . trim($rows['actype']) . '" onchange ="return enabledisablestations(this.id)" data-toggle="tooltip" title="Change Status"' . $status . '>
                    <label class="custom-control-label" for="' . trim($rows['actype']) . '"></label></div>
                </<td>';
            $experiencerow .= '
                <tr>
                    <td>' . $sn . '</td>
                    <td>' . $rows['actype'] . '</td>
                    <td>' . $rows['position'] . '</td>
                    <td>' . $rows['hours'] . '</td>
                     <td>' . $rows['atdate'] . '</td>
                    ' . $btntt . '
                </tr>';
        }
        $experiencerows .= '
            <div class="table-responsive" style="width: 100%;">
                <table class="table table-striped table-light" style ="font-size: small"  id="stationtbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem;">
                        <tr>
                            <th style="width:5%">E/N</th>
                            <th style="width:20%">AIRCRATF TYPE</th>
                            <th style="width:20%">POSITION</th> 
                            <th style="width:20%">HOURS</th>
                            <th style="width:20%">DATE</th>
                            <th style="width:10%">STATUS</th>
                            <th style="width:5%"></th>
                            <th style="width:5%"></th>
                        </tr>
                    </thead>
                        
                    <tbody>
                        ' . $experiencerow . '
                    </tbody>
                </table>
                
                </div>';
        return $experiencerows;
    }
    //check experience info
    public function checkexperirenceinfo($staffid, $email, $actype)
    {
        $sql = "SELECT * FROM experienceinfo WHERE staffid = '$staffid' AND email = '$email' AND actype = '$actype'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        return $rows['staffid'];
    }
    //update experienceinfo
    public function updateexperirenceinfo($staffid, $email, $actype, $position, $hours, $atdate)
    {
        $sql = "UPDATE experienceinfo SET actype = '$actype', position = '$position', hours = '$hours', atdate = '$atdate' WHERE staffid = '$staffid' AND email = '$email'";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    #End Profiling

    #HR Module
    //Create Staff head count
    public function staffheadcount($shcdepartmentunit, $shcnostaff)
    {
        $dandt = $this->africaDate();
        $createdby = $_SESSION('username');
        $sql = "INSERT INTO staffheadcount(deptunitcode,shcnostaff,shcstatus,createdby,dandt)VALUES('$shcdepartmentunit','$shcnostaff','Active','$createdby','$dandt')";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }

    //check staff head count
    public function checkstaffheadcount($shcdepartmentunit)
    {
        $sql = "SELECT * from staffheadcount where deptunitcode = '$shcdepartmentunit'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $deptunitcode = $rows['deptunitcode'];
        return $deptunitcode;
    }
    public function update($station, $sstatus)
    {
        $sql = "UPDATE stationtbl set status ='$sstatus' where station = '$station' ";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }
    public function buisnessunit($shcdepartmentunit, $shcnostaff)
    {
        $dandt = $this->africaDate();
        $createdby = $_SESSION('username');
        $sql = "INSERT INTO staffheadcount(deptunitcode,shcnostaff,shcstatus,createdby,dandt)VALUES('$shcdepartmentunit','$shcnostaff','Active','$createdby','$dandt')";
        $stmt = $this->db->query($sql);
        $count = $stmt->rowCount();
        return $count;
    }

    //Job Titles
    //Insert a new job title into the jobtitletbl table
    public function createJobTitle($jdtitle, $jddepartmentunit, $jdstatus)
    {
        $dandt = $this->africaDate();
        $createdby = $_SESSION['staffid'];
        $sql = "INSERT INTO jobtitletbl (jdtitle, jddepartmentunit, jdstatus, createdby, dandt) 
            VALUES (:jdtitle, :jddepartmentunit, :jdstatus, :createdby, :dandt)";


        $stmt = $this->db->prepare($sql);
        // Bind parameters to prevent SQL injection
        $stmt->bindParam(':jdtitle', $jdtitle);
        $stmt->bindParam(':jddepartmentunit', $jddepartmentunit);
        $stmt->bindParam(':jdstatus', $jdstatus);
        $stmt->bindParam(':createdby', $createdby);
        $stmt->bindParam(':dandt', $dandt);
        $stmt->execute();
    }

    //Update an existing job title in the jobtitletbl table
    public function updateJobTitle($id, $updatedjdtitle, $jddepartmentunit, $jdstatus)
    {
        // Prepare the SQL query to update the job title
        $sql = "UPDATE jobtitletbl
            SET jdtitle = :updatedjdtitle, jddepartmentunit = :jddepartmentunit, jdstatus = :jdstatus
            WHERE id = :id";
        $stmt = $this->db->prepare($sql);

        $stmt->bindParam(':updatedjdtitle', $updatedjdtitle);
        $stmt->bindParam(':jddepartmentunit', $jddepartmentunit);
        $stmt->bindParam(':jdstatus', $jdstatus);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
    }

    // Fetch job titles from the jobtitletbl table
    public function getJobTitles()
    {
        $jobTitleOptions = "";
        $sql = "SELECT id, jdtitle FROM jobtitletbl WHERE jdstatus = 'Active'";
        $stmt = $this->db->prepare($sql);

        $stmt->execute();
        while ($rows = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $jobTitleOptions .= '<option value="' . $rows['id'] . '">' . htmlspecialchars($rows['jdtitle']) . '</option>';
        }

        return $jobTitleOptions;
    }



    //STAFF REQUEST MODULE
    //create a new staff request
    public function createOrUpdateStaffRequest($jdrequestid, $jdtitle, $novacpost, $reason, $eduqualification, $proqualification, $fuctiontech, $managerial, $behavioural, $keyresult, $empdeliveries, $keysuccess)
    {
        $dandt = $this->africaDate();
        $createdby = $_SESSION['username'];

        // Check if the request already exists
        $sql = "SELECT * FROM staffrequest WHERE jdrequestid = '$jdrequestid'";
        $stmt = $this->db->query($sql);
        $existingRequest = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingRequest) {
            // Update existing request
            $sql = "UPDATE staffrequest 
                    SET jdtitle = '$jdtitle', reason = '$reason', eduqualification = '$eduqualification', proqualification = '$proqualification', 
                        fuctiontech = '$fuctiontech', managerial = '$managerial', behavioural = '$behavioural', keyresult = '$keyresult', 
                        empdeliveries = '$empdeliveries', keysuccess = '$keysuccess', novacpost = '$novacpost'
                    WHERE jdrequestid = '$jdrequestid'";
            $stmt = $this->db->query($sql);
        } else {
            // Insert new staff request
            $sql = "INSERT INTO staffrequest (jdrequestid, jdtitle, novacpost, reason, eduqualification, proqualification, fuctiontech, 
                                             managerial, behavioural, keyresult, empdeliveries, keysuccess, createdby, dandt) 
                    VALUES ('$jdrequestid', '$jdtitle', '$novacpost', '$reason', '$eduqualification', '$proqualification', '$fuctiontech', 
                            '$managerial', '$behavioural', '$keyresult', '$empdeliveries', '$keysuccess', '$createdby', '$dandt')";
            $stmt = $this->db->query($sql);
        }

        return $stmt->rowCount();
    }


    //Handle staff request per station information
    public function createOrUpdateStaffRequestPerStation($jdrequestid, $station, $employmenttype, $staffperstation)
    {
        //Check if the entry for the given station already exists
        $sql = "SELECT * FROM staffrequestperstation WHERE jdrequestid = '$jdrequestid' AND station = '$station'";
        $stmt = $this->db->query($sql);
        $existingStation = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingStation) {
            $sql = "UPDATE staffrequestperstation 
                    SET employmenttype = '$employmenttype', staffperstation = '$staffperstation' 
                    WHERE jdrequestid = '$jdrequestid' AND station = '$station'";
            $stmt = $this->db->query($sql);
        } else {
            $sql = "INSERT INTO staffrequestperstation (jdrequestid, station, employmenttype, staffperstation) 
                    VALUES ('$jdrequestid', '$station', '$employmenttype', '$staffperstation')";
            $stmt = $this->db->query($sql);
        }

        return $stmt->rowCount();
    }

    //Get staff request details by jdrequestid
    public function getStaffRequest($jdrequestid)
    {
        $sql = "SELECT * FROM staffrequest WHERE jdrequestid = '$jdrequestid'";
        $stmt = $this->db->query($sql);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    //Get staff request per station details by jdrequestid
    public function getStaffRequestPerStation($jdrequestid)
    {
        $sql = "SELECT * FROM staffrequestperstation WHERE jdrequestid = '$jdrequestid'";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    #End HR Module


    //Load Menuchecklist
    //  public function loadmenuchecklist(){
    //     $menucheck=$menuchecks=$sn="";
    //    // $lmenu = $this->checkmenu($menulink);
    //     $sql = "SELECT * FROM menutbl";
    //     $stmt = $this->db->query($sql);
    //     while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
    //         $menucheck = $menucheck. "<li style='margin-right: 20px;'><input type='checkbox' name='item[]' style='margin-right: 5px;' value='".trim($rows['menuname'])."'><label for='".trim($rows['id'])."'>".trim($rows['menuname'])."</label></li>";
    //     }
    //     $menuchecks = '<label for="menuchecklist" class="col-sm-2 col-form-label">Menus</label>
    //                      <div class="col-sm-10">
    //                         <ul style="display: flex; list-style-type: none;padding: 0;" id ="menuchecklist">
    //                                 '.$menucheck.'
    //                         </ul>
    //                     </div>';    
    //     return $menuchecks;
}

function generateComplexPassword($length = 12)
{
    // Define character sets
    $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $lowercase = 'abcdefghijklmnopqrstuvwxyz';
    $numbers = '0123456789';
    $specialCharacters = '!@#$%^&*()_+-=[]{}|;:,.<>?';

    // Combine all character sets
    $allCharacters = $uppercase . $lowercase . $numbers . $specialCharacters;

    // Ensure each character type is represented
    $password = '';
    $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
    $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
    $password .= $numbers[random_int(0, strlen($numbers) - 1)];
    $password .= $specialCharacters[random_int(0, strlen($specialCharacters) - 1)];

    // Fill the remaining length with random characters from all sets
    for ($i = 4; $i < $length; $i++) {
        $password .= $allCharacters[random_int(0, strlen($allCharacters) - 1)];
    }

    // Shuffle the password to ensure randomness
    $password = str_shuffle($password);

    return $password;
}

#End Menu
