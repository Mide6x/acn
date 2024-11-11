<?php
    //  use PHPMailer\PHPMailer\PHPMailer;
    //  use PHPMailer\PHPMailer\Exception;
 
    //  require '../mail/PHPMailer/src/Exception.php';
    //  require '../mail/PHPMailer/src/PHPMailer.php';
    //  require '../mail/PHPMailer/src/SMTP.php';
     ini_set('max_execution_time', '0');
    class revenue{
        protected $db;
        function __construct($con){
             $this->db = $con;
        }
        public function africaDate() {
            date_default_timezone_set('Asia/Dubai');
            $dbDate = date('Y-m-d H:i:s');
            return $dbDate;
    }
     //Load the file names from DB
     public function loadcsvname(){
        $sql = "SELECT * FROM csvnames";
        $stmt = $this->db->query($sql);
        while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
            $tfilename[] = array('tname'=>$rows['tname'],'tfname'=>$rows['tfname'],'vname'=>$rows['vname'],'vstatement'=>$rows['vstatement']);
        }
        return $tfilename;    
    }
#Start Station Details
    //create stations
    public function createstation($station,$stationcode,$stationtype,$operationtype,$status,$createdby,$nextxx){
        $dandt = $this->africaDate();
        $stationname = $this->checkstation($station,$stationcode,$stationtype,$operationtype);
        if(empty($stationname)){
            $stationname = $this->checkstationeither($station,$stationcode,$stationtype,$operationtype);
            if(empty($stationname)){
                $sql = "INSERT INTO stationtbl(station,stationcode,stationtype,operationtype,status,createdby,dandt)VALUES('$station','$stationcode','$stationtype','$operationtype','$status','$createdby','$dandt')";
                $stmt = $this->db->query($sql); 
                $count= $stmt->rowCount();
            }else{
              $upstationdets = $this->updatestationdetails($station,$stationcode,$stationtype,$operationtype);
            }
            $loadstation = $this->loadstations($stationtype,$status,$nextxx);
        }else{
            $loadstation = "1";
        }
        return $loadstation;
    }
    //check if station exist
    public function checkstation($station,$stationcode,$stationtype,$operationtype){
        if(empty($station)){
            $sql = "SELECT * from stationtbl where stationtype ='$stationtype' and stationcode = '$stationcode' and operatingtype = '$operationtype'";
        }elseif(empty($stationcode)){
            $sql = "SELECT * from stationtbl where stationtype ='$stationtype' and station = '$station' and operatingtype = '$operationtype'";
        }elseif(empty($stationtype)){
            $sql = "SELECT * from stationtbl where stationcode ='$stationcode' and station = '$station' and operatingtype = '$operationtype'";
        }else {
            $sql = "SELECT * from stationtbl where station = '$station' and stationtype ='$stationtype' and stationcode ='$stationcode' and operationtype = '$operationtype'";
        } 
       
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $coyname = $rows['station'];
        return $coyname;
    }
    public function checkstationeither($station,$stationcode,$stationtype,$operationtype){
        $sql = "SELECT * from stationtbl where (station = '$station' and stationcode ='$stationcode')"; // or (stationtype ='$stationtype' or operationtype = '$operationtype')";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $coyname = $rows['station'];
        return $coyname;
    }                                                               
    public function updatestationsatus ($station,$sstatus){
        $sql = "UPDATE stationtbl set status ='$sstatus' where station = '$station' ";
        $stmt = $this->db->query($sql);
        $count= $stmt->rowCount();
        return $count;
    }
    public function updatestationdetails ($station,$stationcode,$stationtype,$operationtype){
        $sql = "UPDATE stationtbl set station = '$station', stationcode='$stationcode', stationtype = '$stationtype', operationtype ='$operationtype' where (station = '$station' and  stationcode='$stationcode')"; //and (stationtype = '$stationtype'or operationtype ='$operationtype')
        $stmt = $this->db->query($sql);
        $count= $stmt->rowCount();
        return $count;
    }
    public function loadroutes(){
        $station="";
        $sql = "SELECT * FROM stationtbl where status ='Active' order by station ";
        $stmt = $this->db->query($sql);
        while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
            $station = $station.'<option value ='.$rows['stationcode'].'>'.$rows['station'].'</option>';   
        }
        return $station;
   }
    //Get Stations
    public function loadstations($stationtype,$status,$nextxx){
         $station=$stations=$sn="";
        if($nextxx <= 50){
            $nextxx ="0";
        }
        // if($stationtype == '' && $status ==''){
            $sql = "SELECT * FROM stationtbl order by station asc LIMIT $nextxx, 50";
        // }else{
        //     $sql = "SELECT * FROM stationtbl WHERE stationtype = '$stationtype' and status ='$status' order by station asc LIMIT $nextxx,50";
        // }
        $stmt = $this->db->query($sql);
        $sn = 0;
        while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
            $sn+=1;
            if(trim($rows['status']) == 'Active'){
                $status ="checked";
            }else{
                $status ="unchecked";
            }
            $btntt = '
            <td style="width:5%">
                <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "edstation" value ="edstation" onclick="return editstation(this)"  data-toggle="tooltip" title="Edit"><i class="ri-edit-2-line" style="color:#FC7F14">

            </<td>
            <td style="width:5%">
               <div class="custom-control custom-switch form-group col-md-12" style ="text-align: right;"><input type="checkbox" class="custom-control-input" id="'.trim($rows['station']).'" onchange ="return enabledisablestations(this.id)" data-toggle="tooltip" title="Change Status"'.$status.'>
                <label class="custom-control-label" for="'.trim($rows['station']).'"></label></div>
            </<td>';
           
            $station = $station.'
                <tr>
                    <td>'.$sn.'</td>
                    <td>'.$rows['station'].'</td>
                    <td>'.$rows['stationcode'].'</td>
                    <td>'.$rows['stationtype'].'</td>
                     <td>'.$rows['operationtype'].'</td>
                    <td>'.$rows['status'].'</td>
                    '.$btntt.'
                </tr>';
        }
       
        $stations = $stations.'
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
                    '.$station.'
                </tbody>
            </table>
              
            </div>';        
        return $stations;
    }
#End Station Details  

#Start Aircraft Details
      //Create Aircraft Details
    public function createaircraftinfo($acname,$actype,$acregno,$accapacity,$acversion,$acstatus,$createdby,$nextxx){
        $dandt = $this->africaDate();
        $regno = $this->checkaircraft($acregno);
        if(empty($regno)){
            $sql = "INSERT INTO aircrafttbl(acname,actype,acregno,acversion,accapacity,acstatus,createdby,dandt)VALUES('$acname','$actype','$acregno','$acversion','$accapacity','$acstatus','$createdby','$dandt')";
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
        }else{
            $this->updateaircraftdetails($acname,$actype,$acversion,$accapacity,$acregno);
        }
        $loadacdetails = $this->loadacdetails($acname,$actype,$acstatus,$nextxx);
        return "$loadacdetails";
    }
    //check if station exist
    public function checkaircraft($acregno){
        $sql = "SELECT * from aircrafttbl where acregno = '$acregno'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $regno = $rows['acregno'];
        return $regno;
    }                                           
    public function updateaircraftsatus ($acregno,$acstatus){
        $sql = "UPDATE aircrafttbl set acstatus ='$acstatus' where acregno = '$acregno' ";
        $stmt = $this->db->query($sql);
        $count= $stmt->rowCount();
        return $count;
    }
    public function updateaircraftdetails ($acname,$actype,$acversion,$accapacity,$acregno){
        $sql = "UPDATE aircrafttbl set acname = '$acname', actype='$actype', acversion = '$acversion', accapacity ='$accapacity' where acregno ='$acregno'";
        $stmt = $this->db->query($sql);
        $count= $stmt->rowCount();
        return $count;
    }
    //Get Stations
    public function loadacdetails($acname,$actype,$acstatus,$nextxx){
        $acdetail=$acdetails=$sn="";
        if($nextxx <= 50){
            $nextxx ="0";
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
       
        while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
            $sn+=1;
        if(trim($rows['acstatus']) == 'Active'){
            $acstatus ="checked";
        }else{
            $acstatus ="unchecked";
        }
            $btntt = '
            <td style="width:5%">
                <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "edstation" value ="edstation" onclick="return editaircraft(this)"  data-toggle="tooltip" title="Edit"><i class="ri-edit-2-line" style="color:#FC7F14">

            </<td>
            <td style="width:5%">
               <div class="custom-control custom-switch form-group col-md-12" style ="text-align: right;"><input type="checkbox" class="custom-control-input" id="'.trim($rows['acregno']).'" onchange ="return enabledisableaircraft(this.id)" data-toggle="tooltip" title="Change Status"'.$acstatus.'>
                <label class="custom-control-label" for="'.trim($rows['acregno']).'"></label></div>
            </<td>';

            $acdetail = $acdetail.'
                <tr>
                    <td>'.$sn.'</td>
                    <td>'.$rows['acname'].'</td>
                    <td>'.$rows['actype'].'</td>
                    <td>'.$rows['acregno'].'</td>
                    <td>'.$rows['acversion'].'</td>
                     <td>'.$rows['accapacity'].'</td>
                    <td>'.$rows['acstatus'].'</td>
                    '.$btntt.'
                </tr>';
        }
       
        $acdetails = $acdetails.'
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
                    '.$acdetail.'
                </tbody>
            </table>
              
            </div>';        
        return $acdetails;
    }

#End Aircraft Details

#Sart Avaition Charges 
    //create a new avaition charge type
    public function createaviationchargetype($chargetype,$chargebody,$chargename,$isseason,$isaircraft,$chargestatus,$createdby){
        $dandt = $this->africaDate();
        $charge = $this->checkaviationchargetype($chargename,$chargetype);
        if(empty($chargetype)){
            $sql = "INSERT INTO avachargetype(chargetype,chargebody,chargename,isaircraft,isseason,chargestatus,createdby,dandt)VALUES('$chargetype','$chargebody','$chargename','$isaircraft','$isseason','$chargestatus','$createdby','$dandt')";
        }
        $stmt = $this->db->query($sql); 
        $count= $stmt->rowCount();
        $loadaviationcharge = $this->loadaviationchargetype($chargetype,$chargebody,$chargestatus);
        return $loadaviationcharge;
    }
    //check if aviation charge type exist
    public function checkaviationchargetype($chargename,$chargetype){
        $sql = "SELECT * from avachargetype where chargename = '$charge' and chargetype ='$chargetype'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $avacharge = $rows['chargename'];
        return $avacharge;
    }
    //Get aviation charge type
    public function loadaviationchargetype($chargetype,$chargebody,$chargestatus){
        $aviachargetype=$aviachargetypes="";
        if($chargetype =='' && $chargebody =='' && $chargestatus ==''){
            $sql = "SELECT * FROM avachargetype limit $nextxx, 50";
        }elseif($chargetype != ''  && $chargebody =='' && $chargestatus == ''){
            $sql = "SELECT * FROM avachargetype WHERE chargetype = '$chargetype'";
        }elseif($chargetype == ''  && $chargebody !='' && $chargestatus == ''){
            $sql = "SELECT * FROM avachargetype WHERE chargebody = '$chargebody'";
        }elseif($chargetype == ''  && $chargebody =='' && $chargestatus != ''){
            $sql = "SELECT * FROM avachargetype WHERE chargestatus = '$chargestatus'";
        }elseif($chargetype != ''  && $chargebody !='' && $chargestatus == ''){
            $sql = "SELECT * FROM avachargetype WHERE chargetype = '$chargetype' and chargebody = $chargebody";
        }elseif($chargetype == ''  && $chargebody !='' && $chargestatus != ''){
            $sql = "SELECT * FROM avachargetype WHERE chargebody = '$chargebody' and acstatus = '$chargestatus'";
        }elseif($chargetype != ''  && $chargebody =='' && $chargestatus != ''){
            $sql = "SELECT * FROM avachargetype WHERE chargetype = '$chargetype' and chargestatus = '$chargestatus'";
        }elseif($acname != ''  && $actype !='' && $acstatus != ''){
            $sql = "SELECT * FROM avachargetye WHERE chargename = '$chargename' and chargestatus = '$chargestatus' and chargetype = $chargetype";
        }
    
        $stmt = $this->db->query($sql);
        $sn = 0;
        while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
        
            $btntt = '
            <td style="width:5%"> <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "removeproduct" value ="removedproduct" onclick="return removesingleproduct()"  data-toggle="tooltip" title="Delist Product"><img src="../assets/images/delete.ico"/></<td>';
            $aviachargetype = $aviachargetype.'
                <tr>
                    <td>'.$rows['chargetype'].'</td>
                    <td>'.$rows['chargebody'].'</td>
                    <td>'.$rows['chargename'].'</td>
                    <td>'.$rows['isaircraft'].'</td>
                    <td>'.$rows['isseason'].'</td>
                    <td>'.$rows['chargestatus'].'</td>
                    <td>'.$btntt.'</td>
                </tr>';
        }
    
        $aviachargetypes = $aviachargetypes.'
        <div class="table-responsive" style="width: 100%;">
            <table class="table table-striped table-light" style ="font-size: small"  id="aircrafttbl"  cellspacing="0">
                <thead class="thead-dark" style ="font-size: 0.8rem">
                    <tr>
                        
                        <th style="width:20%">TYPE</th>
                        <th style="width:20%">BODY</th> 
                        <th style="width:10%">NAME</th>
                        <th style="width:20%">AIRCRAFT</th> 
                        <th style="width:10%">SEASON</th>
                        <th style="width:10%">STATUS</th>
                        <th></th>
                    </tr>
                </thead>
                    
                <tbody>
                    '.$aviachargetype.'
                </tbody>
            </table>
            
            </div>';        
        return $aviachargetypes;
    }
    //Create aviation charges
    public function createaviationcharges($chargesname,$aircrafttype,$season,$station,$chargesvaluetype,$chargesvalue,$shargesstatus,$createdby){
        $dandt = $this->africaDate();
        $avacharge = $this->checkaviationcharges($chargesname,$chargesvalue);
        if(empty($avacharge)){
            $sql = "INSERT INTO avacharges(chargesname,aircrafttype,season,station,chargesvaluetype,chargesvalue,chargestatus,createdby,dandt)VALUES('$chargesname','$aircrafttype','$season','$station','$chargesvaluetype','$chargesvalue','$chargesstatus','$createdby','$dandt')";
        }
        $stmt = $this->db->query($sql); 
        $count= $stmt->rowCount();
        $loadaviationcharges = $this->loadaviationcharges($chargetype,$chargebody,$chargesstatus);
        return $loadaviationcharge;
    }
    //check if aviation charges exist
    public function checkaviationcharges($chargesname,$aircrafttype,$season,$chargestatus){
        $sql = "SELECT * from avacharges where chargesname = '$chargesname' and aircrafttype ='$aircrafttype' and season = '$season' and chragestatus = '$chargestatus'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $avacharge = $rows['chargesname'];
        return $avacharge;
    }
    //Edit avivation charges 
    public function editaviationcharges($chargesname,$aircrafttype,$season,$chargevalue){
        $sql ="UPDATE avacharges SET chargesvalue = '$chargesvalue' where chargesname = '$chargesname' and season = '$season' and aircrafttype = '$aircrafttype'";
        $stmt = $this->db->query($sql);
        $count= $stmt->rowCount();
        return $count;
    }
    //Get aviation charge type
    public function loadaviationcharges($chargesname,$aircrafttype,$season,$station,$chargesvaluetype,$chargesstatus, $nextxx){
        $aviacharge=$aviacharges="";
        if($chargesname =='' && $aircrafttype =='' && $season=='' && $station =='' && $chargesvaluetype && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges limit $nextxx, 50";
        }elseif($chargesname !='' && $aircrafttype =='' && $season=='' && $station =='' && $chargesvaluetype =='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' limit $nextxx, 50";
        }elseif($chargesname =='' && $aircrafttype !='' && $season=='' && $station =='' && $chargesvaluetype == '' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE aircrafttype = '$aircrafttype' limit $nextxx, 50";
        }elseif($chargesname =='' && $aircrafttype =='' && $season!='' && $station =='' && $chargesvaluetype =='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE seasion = '$season' limit $nextxx, 50";
        }elseif($chargesname =='' && $aircrafttype =='' && $season=='' && $station !='' && $chargesvaluetype =='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE station = '$station' limit $nextxx, 50";
        }elseif($chargesname =='' && $aircrafttype =='' && $season=='' && $station =='' && $chargesvaluetype !='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE chargevaluetype = '$chargevaluetype' limit $nextxx, 50";
        }elseif($chargesname =='' && $aircrafttype =='' && $season=='' && $station =='' && $chargesvaluetype =='' && $chargesstatus !=''){ 
            $sql = "SELECT * FROM avacharges WHERE  chargesstatus = '$chargesstatus' limit $nextxx, 50";
        }elseif($chargesname !='' && $aircrafttype !='' && $season !='' && $station !='' && $chargesvaluetype !='' && $chargesstatus !=''){
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and aircrafttype ='$aircrafttype' and season = '$season' and station = '$station' and chargesvaluetype='$chargesvaluetype' and chargesstatus = '$chargesstatus' limit $nextxx, 50";
        }elseif($chargesname !='' && $aircrafttype !='' && $season =='' && $station =='' && $chargesvaluetype =='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and aircrafttype ='$aircrafttype'";
        }elseif($chargesname !='' && $aircrafttype =='' && $season !='' && $station =='' && $chargesvaluetype =='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and season = '$season' limit $nextxx, 50";
        }elseif($chargesname !='' && $aircrafttype =='' && $season =='' && $station !='' && $chargesvaluetype =='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname'and station = '$station' limit $nextxx, 50";
        }elseif($chargesname !='' && $aircrafttype =='' && $season !='' && $station =='' && $chargesvaluetype !='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avachargetye WHERE chargesname = '$chargesname' and chargesvaluetype='$chargesvaluetype' ";
        }elseif($chargesname !='' && $aircrafttype =='' && $season =='' && $station =='' && $chargesvaluetype =='' && $chargesstatus !=''){
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and chargesstatus = '$chargesstatus' limit $nextxx, 50";
        }elseif($chargesname =='' && $aircrafttype !='' && $season !='' && $station =='' && $chargesvaluetype =='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE aircrafttype ='$aircrafttype' and season = '$season' limit $nextxx, 50";
        }elseif($chargesname =='' && $aircrafttype !='' && $season =='' && $station !='' && $chargesvaluetype =='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE aircrafttype ='$aircrafttype' and station = '$station' limit $nextxx, 50";
        }elseif($chargesname =='' && $aircrafttype !='' && $season =='' && $station =='' && $chargesvaluetype !='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE aircrafttype ='$aircrafttype' and chargesvaluetype='$chargesvaluetype' ";
        }elseif($chargesname =='' && $aircrafttype !='' && $season =='' && $station =='' && $chargesvaluetype =='' && $chargesstatus !=''){
            $sql = "SELECT * FROM avacharges WHERE aircrafttype ='$aircrafttype' and chargesstatus = '$chargesstatus' limit $nextxx, 50";
        }elseif($chargesname =='' && $aircrafttype =='' && $season !='' && $station !='' && $chargesvaluetype !='' && $chargesstatus !=''){
            $sql = "SELECT * FROM avacharges WHERE  season = '$season' and station = '$station' limit $nextxx, 50";
        }elseif($chargesname =='' && $aircrafttype =='' && $season !='' && $station =='' && $chargesvaluetype !='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE season = '$season' and chargesvaluetype='$chargesvaluetype' limit $nextxx, 50";
        }elseif($chargesname =='' && $aircrafttype =='' && $season !='' && $station =='' && $chargesvaluetype =='' && $chargesstatus !=''){
            $sql = "SELECT * FROM avacharges WHERE  season = '$season' and chargesstatus = '$chargesstatus'";
        }elseif($chargesname =='' && $aircrafttype =='' && $season =='' && $station !='' && $chargesvaluetype !='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE station = '$station' and chargesvaluetype='$chargesvaluetype'";
        }elseif($chargesname =='' && $aircrafttype =='' && $season =='' && $station !='' && $chargesvaluetype =='' && $chargesstatus !=''){
            $sql = "SELECT * FROM avacharges WHERE station = '$station' and chargesstatus = '$chargesstatus' limit $nextxx, 50";
        }elseif($chargesname !='' && $aircrafttype !='' && $season !='' && $station =='' && $chargesvaluetype =='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and aircrafttype ='$aircrafttype' and season = '$season'";
        }elseif($chargesname !='' && $aircrafttype =='' && $season =='' && $station =='' && $chargesvaluetype !='' && $chargesstatus !=''){
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and station = '$station' and chargesvaluetype='$chargesvaluetype' and chargesstatus = '$chargesstatus' limit $nextxx, 50";
        }elseif($chargesname =='' && $aircrafttype !='' && $season !='' && $station !='' && $chargesvaluetype =='' && $chargesstatus ==''){
            $sql = "SELECT * FROM avacharges WHERE  aircrafttype ='$aircrafttype' and season = '$season' and station = '$station' limit $nextxx, 50";
        }elseif($chargesname =='' && $aircrafttype !='' && $season =='' && $station =='' && $chargesvaluetype !='' && $chargesstatus !=''){
            $sql = "SELECT * FROM avacharges WHERE chargesname = '$chargesname' and aircrafttype ='$aircrafttype' and chargesvaluetype='$chargesvaluetype' and chargesstatus = '$chargesstatus' limit $nextxx, 50";
        }
    
        $stmt = $this->db->query($sql);
        $sn = 0;
        while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
        
            $btntt = '
            <td style="width:5%"> <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "removeproduct" value ="removedproduct" onclick="return removesingleproduct()"  data-toggle="tooltip" title="Delist Product"><img src="../assets/images/delete.ico"/></<td>';
            $aviacharge = $aviacharge.'
                <tr>
                    <td>'.$rows['chargesname'].'</td>
                    <td>'.$rows['aircrafttype'].'</td>
                    <td>'.$rows['season'].'</td>
                    <td>'.$rows['station'].'</td>
                    <td>'.$rows['chargesvaluetype'].'</td>
                    <td>'.$rows['chargesvalue'].'</td>
                    <td>'.$rows['chargesstatus'].'</td>
                    <td>'.$btntt.'</td>
                </tr>';
        }
    
        $aviacharges = $aviacharges.'
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
                    '.$aviacharge.'
                </tbody>
            </table>
            
            </div>';        
        return $aviacharges;
    }
    

#End aviation charges

#Start Flight Details Setup
    public function createflightdetails($flightno,$schstarttime,$schendtime,$routefrom,$routeto,$crewreporttime,$status,$createdby){
        $dandt = $this->africaDate();
        $dflight = $this->checkflightdetails($flightno);
        if(empty($dflight)){
            $sql = "INSERT INTO flightdetails(flightno,schstarttime,schendtime,routefrom,routeto,crewreporttime,fltstatus,createdby,dandt)VALUES('$flightno','$schstarttime','$schendtime','$routefrom','$routeto','$crewreporttime','$status','$createdby','$dandt')";
        }
        $stmt = $this->db->query($sql); 
        $count= $stmt->rowCount();
        $loadfltdets = $this->loadflightdetails('','',0);
        return $loadfltdets;
    }
    //check if station exist
    public function checkflightdetails($flightno){
        $sql = "SELECT * from flightdetails where flightno = '$flightno'";
        $stmt = $this->db->query($sql);
        $rows = $stmt->fetch(PDO::FETCH_ASSOC);
        $flightdet = $rows['flightno'];
        return $flightdet;
    }
    public function updateflightstatus ($flightno,$fltstatus){
        $sql = "UPDATE aircrafttbl set fltstatus ='$fltstatus' where fltightno = '$flightno' ";
        $stmt = $this->db->query($sql);
        $count= $stmt->rowCount();
        return $count;
    }
    public function updateflightdetails ($flightno,$schstarttime,$schendtime,$routefrom,$routeto,$crewreporttime){
        $sql = "UPDATE aircrafttbl set schstarttime = '$schstarttime', schendtime = '$schendtime', routefrom = '$routefrom', routeto ='$routeto',crewreporttime = '$crewreporttime' where flightno ='$flightno'";
        $stmt = $this->db->query($sql);
        $count= $stmt->rowCount();
        return $count;
    }
    //Get Stations
    public function loadflightdetails($flightno,$fltstatus,$nextxx){
        $flightdet=$flightdets="";
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
        while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
            $sn+=1;
            if(trim($rows['fltstatus']) == 'Active'){
                $fltstatus ="checked";
            }else{
                $fltstatus ="unchecked";
            }
                $btntt = '
                <td style="width:5%">
                    <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "edflightdet" value ="edflightdet" onclick="return editafightdetails(this)"  data-toggle="tooltip" title="Edit"><i class="ri-edit-2-line" style="color:#FC7F14">
    
                </<td>
                <td style="width:5%">
                   <div class="custom-control custom-switch form-group col-md-12" style ="text-align: right;"><input type="checkbox" class="custom-control-input" id="'.trim($rows['flightno']).'" onchange ="return enabledisableaircraft(this.id)" data-toggle="tooltip" title="Change Status"'.$fltstatus.'>
                    <label class="custom-control-label" for="'.trim($rows['flightno']).'"></label></div>
                </<td>';
    
           
            $flightdet = $flightdet.'
                <tr>
                    <td>'.$sn.'</td>
                    <td>'.$rows['flightno'].'</td>
                    <td>'.$rows['schstarttime'].'</td>
                    <td>'.$rows['schendtime'].'</td>
                    <td>'.$rows['routefrom'].'</td>
                    <td>'.$rows['routeto'].'</td>
                    <td>'.$rows['crewreporttime'].'</td>
                    <td>'.$rows['fltstatus'].'</td>
                    <td>'.$btntt.'</td>
                </tr>';
        }
       
        $flightdets = $flightdets.'
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
                    '.$flightdet.'
                </tbody>
            </table>
              
            </div>';        
        return $flightdets;
    }
#End Flight Details Setup


    public function getdatafromfile($headers, $records, $tablename, $vname, $vstatement) {
        try {
            // Add the timestamp column
            $headers[] = 'timestamp';

            // Create table SQL with the timestamp column
            $create_table_sql = "CREATE TABLE IF NOT EXISTS $tablename (";
            foreach ($headers as $header) {
                $create_table_sql .= "`$header` VARCHAR(255),";
            }
            $create_table_sql = rtrim($create_table_sql, ',') . ")";
            
            // Execute the create table query
            $this->db->exec($create_table_sql);

            // Prepare insert statement
            $columns = implode(", ", $headers);
            $placeholders = implode(", ", array_fill(0, count($headers), '?'));
            $insert_sql = "INSERT INTO $tablename ($columns) VALUES ($placeholders)";
            $stmt = $this->db->prepare($insert_sql);

            // Insert records into the table with the current timestamp
            $today = new DateTime();
            $current_time = $today->format('Ymd');

            // Batch size
            $batchSize = 1000;
            $batch = [];

            foreach ($records as $record) {
                $record['timestamp'] = $current_time;
                $batch[] = array_values($record);

                if (count($batch) >= $batchSize) {
                    $this->db->beginTransaction();
                    try {
                        foreach ($batch as $params) {
                            if (count($params) == count($headers)) {
                                $stmt->execute($params);
                            } else {
                                print_r($stmt);
                                print_r($params);
                                throw new Exception('Mismatched number of placeholders and parameters.');
                            }
                        }
                        $this->db->commit();
                    } catch (Exception $e) {
                        $this->db->rollBack();
                        throw $e;
                    }
                    // Clear the batch
                    $batch = [];
                }
            }

            // Insert any remaining records
            if (count($batch) > 0) {
                $this->db->beginTransaction();
                try {
                    foreach ($batch as $params) {
                        if (count($params) == count($headers)) {
                            $stmt->execute($params);
                        } else {
                            print_r($stmt);
                            print_r($params);
                            throw new Exception('Mismatched number of placeholders and parameters.');
                        }
                    }
                    $this->db->commit();
                } catch (Exception $e) {
                    $this->db->rollBack();
                    throw $e;
                }
            }

            if ($vstatement != "") {
                $this->CheckView($vname, $vstatement);
            }
        } catch (PDOException $e) {
            echo 'Database error: ' . $e->getMessage();
        } catch (Exception $e) {
            echo 'General error: ' . $e->getMessage();
        }
    }
    public function ticketrev(){
        
    }

   

    
    // public function getdatafromfile($headers, $records, $tablename,$vname,$vstatement) {
    //     // Add the timestamp column
    //     $headers[] = 'timestamp';
    
    //     // Create table SQL with the timestamp column
    //     $create_table_sql = "CREATE TABLE IF NOT EXISTS $tablename (";
    //     foreach ($headers as $header) {
    //         $create_table_sql .= "`$header` VARCHAR(255),";
    //     }
    //     $create_table_sql = rtrim($create_table_sql, ',') . ")";
        
    //     // Execute the create table query
    //     $this->db->exec($create_table_sql);
    //     //echo "Table created successfully.<br>";
    
    //     // Prepare insert statement
    //     $columns = implode(", ", $headers);
    //     $placeholders = implode(", ", array_fill(0, count($headers), '?'));
    //     $insert_sql = "INSERT INTO $tablename ($columns) VALUES ($placeholders)";
    //     $stmt = $this->db->prepare($insert_sql);
    
    //     // Insert records into the table with the current timestamp
    //     $today = new DateTime();
    //     $current_time = $today->format('Ymd');
    //     foreach ($records as $record) {
    //         $record['timestamp'] = $current_time;
    //         $stmt->execute(array_values($record));
    //         //echo "New record created successfully.<br>";
    //     }
    //     if($vstatement != ""){
    //         $this->CheckView($vname,$vstatement);
    //     }
        
    // }
    
// Check if the view exists
    public function CheckView($vname,$vstatement){
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM information_schema.views WHERE table_schema = :dbname AND table_name = :viewname");
        $stmt->execute(['dbname' => 'hititdatadump', 'viewname' => $vname]);
        $viewExists = $stmt->fetchColumn();

        if ($viewExists) {
            echo "View already exists.<br>";
        } else {
            // Create the view
            // $createViewSql = "CREATE VIEW $viewName AS SELECT * FROM $tableName";
            // $pdo->exec($createViewSql);
            $this->createview($vname,$vstatement);
            //echo "View ticketsalesview created successfully.<br>";
        }
    }

    public function createview($vname,$vstatement){
        $sql = $vstatement;
        $stmt = $this->db->query($sql);
        // while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
        //     $tfilename[] = array('tname'=>$rows['tname'],'tfname'=>$rows['tfname']);
        // }
       //return $tfilename;    
    }




        //Document upload
        public function documentupload($regtype,$documentname,$documentbelongs,$dateofreg,$docfilepath,$pemail,$createdby){
            $dandt = $this->africaDate();
            if($documentbelongs == 'company'){
                $docnames = $this->checkcompanydocument($regtype,$createdby,$documentname);
                if($docnames ==""){
                    $sql = "INSERT INTO documentupload(regtype,docname,docbelongs,dateofreg,dateofexpire,docfilepath,status,createdby,dandt)VALUES('$regtype','$documentname','$documentbelongs','$dateofreg','','$docfilepath','New','$createdby','$dandt')";
                    $stmt = $this->db->query($sql); 
                    $count= $stmt->rowCount();
                    $getuploadeddoc = $this->getdocumentuploads($regtype,$createdby,$documentbelongs,'');
                    echo $getuploadeddoc;
                    return "$getuploadeddoc";
                }else{
                    echo "2";
                }
            }elseif($documentbelongs =='contact'){
                $docnames = $this->checkcontactdocument($regtype,$createdby,$documentname,$pemail);
                if($docnames ==""){
                    $sql = "INSERT INTO documentupload(regtype,docname,docbelongs,dateofreg,dateofexpire,pemail,status,docfilepath,createdby,dandt)VALUES('$regtype','$documentname','$documentbelongs','$dateofreg','','$pemail','New','$docfilepath','$createdby','$dandt')";
                    //echo $sql;
                    $stmt = $this->db->query($sql); 
                    $count= $stmt->rowCount();
                    $getuploadeddoc = $this->getdocumentuploads($regtype,$createdby,$documentbelongs,$pemail);
                    echo $getuploadeddoc;
                    return "$getuploadeddoc";
                }else{
                    echo "2";
                }
            }elseif($documentbelongs =='Truck'){
                $createdby = 'damilolaadeniji@gmail.com';
                $docnames = $this->checkcontactdocument($regtype,$createdby,$documentname,'');
                if($docnames ==""){
                    $sql = "INSERT INTO documentupload(regtype,docname,docbelongs,dateofreg,dateofexpire,pemail,status,docfilepath,createdby,dandt)VALUES('$regtype','$documentname','$documentbelongs','$dateofreg','','$pemail','New','$docfilepath','$createdby','$dandt')";
                    //echo $sql;
                    $stmt = $this->db->query($sql); 
                    $count= $stmt->rowCount();
                    $getuploadeddoc = $this->getdocumentuploads($regtype,$createdby,$documentbelongs,'');
                    echo $getuploadeddoc;
                    return "$getuploadeddoc";
                }else{
                    echo "2";
                }
            }
          
        }
        public function uploadpictruck($vin,$plateno,$docpath,$createdby){
            $sql = "INSERT INTO documentupload(vin,plateno,docpath,createdby,dandt)VALUES('$vin','$plateno','$docpath','$createdby','$createdby','$dandt')";
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            $getuploadeddoc = $this->getdocumentuploads($regtype,$createdby,$documentbelongs,'');
            echo $getuploadeddoc;
            return "$getuploadeddoc";
        }
        //Register a truck
        public function registertruck($vin,$coyname,$plateno,$ttplocation){
            $dandt = $this->africaDate();
            $createdby = $_SESSION['username'];
            $coyname = $_SESSION['coyname'];
            $chktruck = $this->checktruckreg($plateno);
            if(isset($chktruck)){
                $truckreg ="Truck has already being registered";
            }else{
                $sql = "INSERT INTO trucks(coyname,vin,plateno,ttplocation,appstatus,availstatus,createdby,reviewedby,dandtcreated,dandtreviewed)VALUES('$coyname','$vin','$plateno','$ttplocation','Pending','Unavailable','$createdby','','$dandt','')";
                $stmt = $this->db->query($sql); 
                $count= $stmt->rowCount();
                $truckreg = $this->loadtrucks($createdby);
            }
            return $truckreg;
        }
        //Register a contact
        public function registercontact($regtype,$coyname,$title,$surname,$firstname,$othernames,$phoneno,$emailaddress,$verifyemail,$pstatus,$nin,$portofops,$dlnumber,$createdby){
            $dandt = $this->africaDate();
            $fname = $this->checkcontactregistration($regtype,$emailaddress,$emailaddress);
            if(empty($fname)){
                $sql = "INSERT INTO contacts(regtype,companyname,title,surname,firstname,othername,phoneno,emailaddress,vemailaddress,pstatus,nin,dlnumber,portofops,status,createdby,dandt)VALUES('$regtype','$coyname','$title','$surname','$firstname','$othernames','$phoneno','$emailaddress','$verifyemail','$pstatus','$nin','$dlnumber','$portofops','New','$createdby','$dandt')";
            
            //echo $sql;
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            $getcont = $this->getcontacts($regtype,$createdby);
            echo $getcont;    
            // return "$count";
            }else{
                echo 2;
            }
        }
        //Register a company
        public function registercompany($regtype,$coyname,$cacregnumber,$tin,$coyphoneno,$coyemail,$officeaddy,$otheraddy,$sector,$createdby){
            $dandt = $this->africaDate();
            $coyyname = $this->checkcompanyregistration($coyemail);
            if(empty($coyyname)){
                $sql = "INSERT INTO regcoy(regtype,coyname,cacregnumber,tin,coyphoneno,coyemail,officeaddy,otheraddys,sector,status,createdby,dandt)VALUES('$regtype','$coyname','$cacregnumber','$tin','$coyphoneno','$coyemail','$officeaddy','$otheraddy','$sector','New','$createdby','$dandt')";
          
                $stmt = $this->db->query($sql); 
                $count= $stmt->rowCount();
                echo 1;
            }
            else {
                echo 2;
            }
        }
       
        //Get document type count
        public function getdocumenttypecount($regtype,$documenttype,$pstatus){
            if($pstatus == 'Driver'){
                $sql = "SELECT * from documentuploadtype where (regtype = '$regtype' or regtype ='All' or regtype ='Driver') and documenttype = '$documenttype'" ;
            }else{
                $sql = "SELECT * from documentuploadtype where (regtype = '$regtype' or regtype ='All') and documenttype = '$documenttype'" ;
            }
           
            $stmt = $this->db->query($sql);
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            return $count;
        }
        //Get document upload count
        public function getdocumentuploadcount($regtype,$documenttype,$createdby,$pemail){
            if($documenttype =='company'){
                $sql = "SELECT * from documentupload where regtype = '$regtype' and docbelongs = '$documenttype' and createdby = '$createdby'" ;
                $stmt = $this->db->query($sql);
                $stmt = $this->db->query($sql); 
                $count= $stmt->rowCount();
            }elseif($documenttype == 'contact'){
               
                $sql = "SELECT * from documentupload where regtype = '$regtype' and docbelongs = '$documenttype' and createdby = '$createdby' and pemail = '$pemail'" ;
              
                //$sql = "SELECT * from documentupload where regtype = '$regtype' and docbelongs = '$documenttype' and createdby = '$createdby' and pemail = '$pemail'" ;
                $stmt = $this->db->query($sql);
                $stmt = $this->db->query($sql); 
                $count= $stmt->rowCount();
            }
            
            return $count;
        }
        //Compare Document count uploaded
        public function comparedocumentuplaod($regtype,$documenttype,$createdby,$pemail,$pstatus){
            if($documenttype == 'company'){
                $counttype = $this->getdocumenttypecount($regtype,$documenttype,'');
                $countupload = $this->getdocumentuploadcount($regtype,$documenttype,$createdby,'');
                if($counttype > $countupload){
                    echo 2;
                }elseif($counttype == $countupload){
                    echo 1;
                }
            }elseif($documenttype == 'contact'){
                $counttype = $this->getdocumenttypecount($regtype,$documenttype,$pstatus);
                $countupload = $this->getdocumentuploadcount($regtype,$documenttype,$createdby,$pemail);
                //echo  $countupload.'~~'.$counttype;

                if($counttype > $countupload){
                    echo 2;
                }elseif($counttype == $countupload){
                    echo 1;
                }
            }
        }
        //Check if company has already been registered
        public function checkcompanyregistration($coyemail){
            $sql = "SELECT * from regcoy where coyemail = '$coyemail'";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $coyname = $rows['coyname'];
            return $coyname;
        }
        public function checkcontactdocument($regtype,$coyemail,$documentname,$pemail){
            $sql = "SELECT * FROM documentupload WHERE regtype = '$regtype' AND createdby ='$coyemail' AND docname ='$documentname' AND pemail = '$pemail'";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $docname = $rows['docname'];
            return $docname;
            
        }
        public function checkcompanydocument($regtype,$coyemail,$documentname){
            $sql = "SELECT * FROM documentupload WHERE regtype = '$regtype' AND createdby ='$coyemail' AND docname ='$documentname'";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $docname = $rows['docname'];
            return $docname;
        }
        public function checktruckdocument($regtype,$coyemail,$documentname){
            $sql = "SELECT * FROM documentupload WHERE regtype = '$regtype' AND createdby ='$coyemail' AND docname ='$documentname'";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $docname = $rows['docname'];
            return $docname;
        }
        //Check if contact has already been registered
        public function checkcontactregistration($regtype,$createdby,$pemail){
            $sql = "SELECT * from contacts where regtype = '$regtype' and emailaddress ='$pemail' and createdby = '$createdby'";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $firstname = $rows['firstname'];
            return $firstname;
        }
        public function checktruckreg($plateno){
            $sql = "SELECT * from trucks where plateno ='$plateno'";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $firstname = $rows['firstname'];
            return $firstname;
        }
        public function gettruckspics($plateno,$vin,$createdby){
            $truckpic="";
          
            $sql = "SELECT * FROM truckpics WHERE vin = '$vin' AND createdby ='$createdby' AND plateno ='$plateno'";
            $stmt = $this->db->query($sql);
            $sn = 0;
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
                $truckpic = $truckpic.'
                <div style="display: flex;justify-content: space-between;margin-bottom: 20px;">
                    <div style="width: 100px; height:100px;margin-bottom: 10px; margin-left:4px;">
                        <img style=" width: 100px;height:100px;" src='.$rows['docpath'].'
                    </div>
                </div>';
            }
            return $truckpic;
        }
        //Document Upload
        public function getdocumentuploads($regtype,$createdby,$documentbelongs,$pemail){
            $docupload=$docuploads="";
            if($documentbelongs =="company"){
                $sql = "SELECT * FROM documentupload WHERE regtype = '$regtype' AND createdby ='$createdby' AND docbelongs ='$documentbelongs'";
            }elseif($documentbelongs=="contact"){
                $sql = "SELECT * FROM documentupload WHERE regtype = '$regtype' AND createdby ='$createdby' AND docbelongs ='$documentbelongs' AND pemail ='$pemail'";
            }elseif($documentbelongs=="Truck"){
                $sql = "SELECT * FROM documentupload WHERE regtype = '$regtype' AND createdby ='$createdby' AND docbelongs ='$documentbelongs'";
            }
           
            $stmt = $this->db->query($sql);
            $sn = 0;
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
               
                $btntt = '
                <td style="width:5%"> <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "removeproduct" value ="removedproduct" onclick="return removesingleproduct()"  data-toggle="tooltip" title="Delist Product"><img src="../assets/images/delete.ico"/></<td>';
                $docupload = $docupload.'
                    <tr>
                        <td>'.$rows['docname'].'</td>
                        <td>'.$rows['dateofreg'].'</td>
                        <td><a href='.$rows['docfilepath'].' target="_blank">View Document</a></td>
                       
                        '.$btntt.'
                    </tr>';
            }
           
            $docuploads = $docuploads.'
            <div class="table-responsive" style="width: 100%;">
                <table class="table table-striped table-light" style ="font-size: small"  id="adminusertbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem">
                        <tr>
                            
                            <th style="width:40%">DOCUMENT NAME</th>
                            <th style="width:40%">DATE OF REGISTRATION</th> 
                            <th style="width:40%">DOCUMENT</th>
                            <th></th>
                        </tr>
                    </thead>
                        
                    <tbody>
                        '.$docupload.'
                    </tbody>
                </table>
                  
                </div>';        
            return $docuploads;
        }
        public function getacontdoc($pemail){
            $docupload=$docuploads="";
            $sql = "SELECT * FROM documentupload WHERE docbelongs ='contact' AND pemail ='$pemail'";
            
            $stmt = $this->db->query($sql);
            $sn = 0;
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
               
                $btntt = '
                <td style="width:5%"> <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "removeproduct" value ="removedproduct" onclick="return removesingleproduct()"  data-toggle="tooltip" title="Delist Product"><img src="../assets/images/delete.ico"/></<td>';
                $docupload = $docupload.'
                    <tr>
                        <td>'.$rows['docname'].'</td>
                        <td>'.$rows['dateofreg'].'</td>
                        <td><a href='.$rows['docfilepath'].' target="_blank">View Document</a></td>
                       
                        '.$btntt.'
                    </tr>';
            }
           
            $docuploads = $docuploads.'
            <div class="table-responsive">
                <table class="table table-striped table-light" style ="font-size: small" width="100%" id="adminusertbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem">
                        <tr>
                            
                            <th>DOCUMENT NAME</th>
                            <th>DATE OF REGISTRATION</th> 
                            <th>DOCUMENT</th>
                            <th></th>
                        </tr>
                    </thead>
                        
                    <tbody>
                        '.$docupload.'
                    </tbody>
                </table>
                  
                </div>';        
            return $docuploads;
        }
        public function getacontact($pemail){
            $sql = "SELECT * FROM contacts WHERE emailaddress = '$pemail'";
            $stmt = $this->db->query($sql);
            $sn = 0;
            $rows=$stmt->fetch(PDO::FETCH_ASSOC);
            $contdoc = $this->getacontdoc($pemail);
            echo json_encode($rows)."~~".$contdoc;   
        }
        //get contacts
        public function getcontacts($regtype,$createdby){
            $contact=$contacts="";
            $sql = "SELECT * FROM contacts WHERE regtype = '$regtype' AND createdby ='$createdby'";
            $stmt = $this->db->query($sql);
            $sn = 0;
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
               
                $btntt = '
                <td>
                <button type="button" id ="viewContactButton" onclick="viewcontact(this)" data-bs-toggle="modal" data-bs-target="#disablebackdrop" style="border: none; background: none;">
                <i class="ri-profile-line"></i></i>
            </button>
            </td>';
                $contact = $contact.'
                    <tr>
                        <td>'.$rows['surname'].' '.$rows['firstname'].'</td>
                        <td>'.$rows['emailaddress'].'</td>
                        <td>'.$rows['phoneno'].'</td>
                        '.$btntt.'
                    </tr>';
            }
           
            $contacts = $contacts.'
            <div class="table-responsive" style="width: 100%;">
                <table class="table table-striped table-light" style ="font-size: small" id="adminusertbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem">
                        <tr>
                            
                            <th>NAME</th>
                            <th>EMAIL ADDRESS</th> 
                            <th>PHONE NUMBER</th>
                            <th></th>
                        </tr>
                    </thead>
                        
                    <tbody>
                        '.$contact.'
                    </tbody>
                </table>
                  
                </div>';        
            return $contacts;
        }
        //Load Contact
       
        //Create a new containner 
        public function createcontainer($coyname,$teu,$containerno,$cargodesc,$containertype,$size,$clearanceterminal,$deliveryaddress,$eod,$status,$createdby){
            $dandt = $this->africaDate();
            $coyyname = $this->checkregistration($regtype,$coyemail);
            if(empty($coyyname)){
                $sql = "INSERT INTO containerdetails(shipliner,teu,containerno,cargodesc,containertype,sizes,clearanceterminal,deliveryaddress,expectedtimeofdelivery,status,createdby,dandt)VALUES('$coyname','$teu','$containerno','$cargodesc','$containertype','$sizes','$clearanceterminal','$deliveryaddress'$eod','New','$createdby','$dandt')";
            }
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            $loadcont = $this->loadcontainer($coyname,$createdby);
            return "$loadcont";
        }
        public function maketranspay($transid,$conttransid,$amountpaid,$modeofpayment,$paygateway,$createdby){
            $dandt = $this->africaDate();
            //$coyyname = $this->checkregistration($regtype,$coyemail);
            if(empty($coyyname)){
                $sql = "INSERT INTO transpayment(transid,conttransid,amountpaid,modeofpayment,paystatus,paygateway,createdby,dandt)VALUES('$transid','$conttransid','$amountpaid','$modeofpayment','Paid','$paygateway','$createdby','$dandt')";
            }
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            $paystatus = $this->updatecontpaystatus($conttransid);
            return $count;
        }
          //Load Container
        public function loadcontainer($coyname){
            $contains = $container="";
            $sql = "SELECT * FROM containerdetails WHERE shipliner = '$coyname'";
            $stmt = $this->db->query($sql);
            $sn = 0;
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
               
                $btntt = '
                <td style="width:5%"> <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "removeproduct" value ="removedproduct" onclick="return removesingleproduct()"  data-toggle="tooltip" title="Delist Product"><img src="../assets/images/delete.ico"/></<td>';
                $contains = $contains.'
                    <tr>
                        <td>'.$rows['shipliner']. '</td>
                        <td>'.$rows['containerno'].'</td>
                        <td>'.$rows['containertype'].'</td>
                        <td>'.$rows['teu'].'</td>
                        <td>'.$rows['status'].'</td>
                        '.$btntt.'
                    </tr>';
            }
           
            $container = $container.'
            <div class="table-responsive-sm">
                <table class="table table-striped table-light" style ="font-size: 0.8rem" id="adminusertbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem">
                        <tr>
                            <th>Ship liner</th>
                            <th>Container Number</th> 
                            <th>Container Type</th>
                            <th>TEU(s)</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                        
                    <tbody>
                        '.$contains.'
                    </tbody>
                </table>
                  
                </div>';        
            return $container;
        }
        public function loadvendors(){
            $vend=$vendors="";
            $sql = "SELECT * FROM regcoy";
            $stmt = $this->db->query($sql);
            $sn = 0;
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
               
                $btntt = ' <td style="width:2%"> <button type="button" id = "viewvendor" value ="viewvendor" onclick="return viewVendor(this)"  data-toggle="tooltip" title="View Details"><i class="ri-profile-line"></i></button></<td>
                <td style="width:2%"> <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" ><img src="../assets/images/delete.ico"/></<td>
               ';
                $vend = $vend.'
                    <tr>
                        <td>'.$rows['regtype']. '</td>
                        <td>'.$rows['coyname'].'</td>
                        <td>'.$rows['coyemail'].'</td>
                        <td>'.$rows['coyphoneno'].'</td>
                        <td>'.$rows['status'].'</td>
                        '.$btntt.'
                    </tr>';
            }
           
            $vendors = $vendors.'
            <div class="table-responsive-sm">
                <table class="table" style ="font-size: 0.8rem" id="adminusertbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem">
                        <tr>
                            <th>Registration Type</th>
                            <th>Company</th> 
                            <th>Email Address</th>
                            <th>Phone No(s)</th>
                            <th>Status</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                        
                    <tbody>
                        '.$vend.'
                    </tbody>
                </table>
                  
                </div>';        
            return $vendors;
        }
        public function floadvendors($vendortype,$searchs,$vendorstatus,$nextxx){
            $vend=$vendors="";
            if($nextxx == ""){
                $pn = 0;
                $sn = 0;
        
            }elseif($nextxx != ""){
                $pn = $nextxx;
                $sn = $nextxx;
            }
            if($vendortype == "" && $vendorstatus =="" && $searchs ==""){
                $sql = "SELECT * FROM regcoy  order by coyname asc limit $pn,50";
            }
            if($vendortype == "" && $vendorstatus =="" && $searchs !=""){
                $sql = "SELECT * FROM regcoy where (coyname like '%$searchs%') order by coyname asc limit $pn,50";
            }
            if($vendortype != "" && $vendorstatus =="" && $searchs ==""){
                $sql = "SELECT * FROM regcoy where regtype = '$vendortype' order by coyname asc limit $pn,50";
            }
            if($vendortype == "" && $vendorstatus !="" && $searchs ==""){
                $sql = "SELECT * FROM regcoy where status = '$vendorstatus' order by coyname asc limit $pn,50";
            }
            if($vendortype != "" && $vendorstatus !="" && $searchs ==""){
                $sql = "SELECT * FROM regcoy where regtype = '$vendortype' and status = '$vendorstatus' order by coyname asc limit $pn,50";
            }
            if($vendortype == "" && $vendorstatus !="" && $searchs !=""){
                $sql = "SELECT * FROM regcoy where (coyname like '%$searchs%') and status ='$vendorstatus' order by coyname asc limit $pn,50";
            }
            if($vendortype != "" && $vendorstatus =="" && $searchs !=""){
                $sql = "SELECT * FROM regcoy where (coyname like '%$searchs%') and regtype='$vendortype' order by surname asc limit $pn,50";
            }

           // $sql = "SELECT * FROM fic_user where app_status = 'New' limit 10";
           $stmt = $this->db->query($sql);
            $sn = 0;
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
               
                $btntt = ' <td style="width:2%"> <button type="button" id = "viewvendor" value ="viewvendor" onclick="return viewVendor(this)"  data-toggle="tooltip" title="View Details"><i class="ri-profile-line"></i></button></<td>
                <td style="width:2%"> <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" ><img src="../assets/images/delete.ico"/></<td>
               ';
                $vend = $vend.'
                    <tr>
                        <td style="display:none; width;5%">'.$sn.'</td>
                        <td>'.$rows['regtype']. '</td>
                        <td>'.$rows['coyname'].'</td>
                        <td>'.$rows['coyemail'].'</td>
                        <td>'.$rows['coyphoneno'].'</td>
                        <td>'.$rows['status'].'</td>
                        '.$btntt.'
                    </tr>';
            }
           
            $vendors = $vendors.'
            <div class="table-responsive-sm">
                <table class="table" style ="font-size: 0.8rem" id="adminusertbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem">
                        <tr>
                            <th style="display:none; width:5%">S/N</th>
                            <th>Registration Type</th>
                            <th>Company</th> 
                            <th>Email Address</th>
                            <th>Phone No(s)</th>
                            <th>Status</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                        
                    <tbody>
                        '.$vend.'
                    </tbody>
                </table>
                  
                </div>';        
            return $vendors;
        }
        public function fmloadvendors($vendortype){
            $vend=$vendors="";
            if($vendortype == 'All'){
                $sql = "SELECT * FROM regcoy";
            }else{
                $sql = "SELECT * FROM regcoy where status = '$vendortype'";
            }
            $stmt = $this->db->query($sql);
            $sn = 0;
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
               
                $btntt = ' <td style="width:2%"> <button type="button" id = "viewvendor" value ="viewvendor" onclick="return viewVendor(this)"  data-toggle="tooltip" title="View Details"><i class="ri-profile-line"></i></button></<td>
                <td style="width:2%"> <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" ><img src="../assets/images/delete.ico"/></<td>
               ';
                $vend = $vend.'
                    <tr>
                        <td style="display:none; width;5%">'.$sn.'</td>
                        <td>'.$rows['regtype']. '</td>
                        <td>'.$rows['coyname'].'</td>
                        <td>'.$rows['coyemail'].'</td>
                        <td>'.$rows['coyphoneno'].'</td>
                        <td>'.$rows['status'].'</td>
                        '.$btntt.'
                    </tr>';
            }
           
            $vendors = $vendors.'
            <div class="table-responsive-sm">
                <table class="table" style ="font-size: 0.8rem" id="adminusertbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem">
                        <tr>
                            <th style="display:none; width:5%">S/N</th>
                            <th>Registration Type</th>
                            <th>Company</th> 
                            <th>Email Address</th>
                            <th>Phone No(s)</th>
                            <th>Status</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                        
                    <tbody>
                        '.$vend.'
                    </tbody>
                </table>
                  
                </div>';        
            return $vendors;
        }
        public function getcoyfortrucks() {
            $coyname = "";
            $query = "select DISTINCT(coyname) from trucks";
            $stmt = $this->db->query($query);   
            while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
                $coyname = $coyname.'<option>'.$rows['coyname'].'</option>';
            }
            return '<option>Select Company</option>'.$coyname;
        }
        public function loadtrucks($createdby){
            $truck=$trucks="";
            $sql = "SELECT * FROM trucks where createdby ='$createdby'";
            $stmt = $this->db->query($sql);
            $sn = 0;
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
               $sn=$sn+1;
            //     $btntt = ' <td style="width:2%"> <button type="button" id = "viewvendor" value ="viewvendor" onclick="return viewVendor(this)"  data-toggle="tooltip" title="View Details"><i class="ri-profile-line"></i></button></<td>
            //     <td style="width:2%"> <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" ><img src="../assets/images/delete.ico"/></<td>
            //    ';
                $truck = $truck.'
                    <tr>
                        <td style="width;5%">'.$sn.'</td>
                        <td>'.$rows['vin']. '</td>
                        <td>'.$rows['plateno'].'</td>
                        <td>'.$rows['ttplocation'].'</td>
                        <td>'.$rows['appstatus'].'</td>
                        <td>'.$rows['availstatus'].'</td>
                    </tr>';
            }
           
            $trucks = $trucks.'
            <div class="table-responsive-sm">
                <table class="table" style ="font-size: 0.8rem" id="adminusertbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem">
                        <tr>
                            <th style="width:5%">S/N</th>
                            <th>VIN</th>
                            <th>PLATE NUMBER</th> 
                            <th>TTP LOCATION</th>
                            <th>APPROVAL STATUS</th>
                            <th>AVAILABILITY Status</th>
                        </tr>
                    </thead>
                        
                    <tbody>
                        '.$truck.'
                    </tbody>
                </table>
                  
                </div>';        
            return $trucks;
        }
        public function loadtrucksforadmin(){
            $truck=$trucks="";
            $sql = "SELECT * FROM trucks";
            $stmt = $this->db->query($sql);
            $sn = 0;
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
               $sn=$sn+1;
                $btntt = ' <td style="width:2%"> <button type="button" id = "viewvendor" value ="viewvendor" onclick="return viewVendor(this)"  data-toggle="tooltip" title="View Details"><i class="ri-profile-line"></i></button></<td>
                <td style="width:2%"> <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" ><img src="../assets/images/delete.ico"/></<td>
               ';
                $truck = $truck.'
                    <tr>
                        <td style="width;5%">'.$sn.'</td>
                        <td>'.$rows['vin']. '</td>
                        <td>'.$rows['plateno'].'</td>
                        <td>'.$rows['ttplocation'].'</td>
                        <td>'.$rows['appstatus'].'</td>
                        <td>'.$rows['coyname'].'</td>
                        '.$btntt.'
                    </tr>';
            }
           
            $trucks = $trucks.'
            <div class="table-responsive-sm">
                <table class="table" style ="font-size: 0.8rem" id="adminusertbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem">
                        <tr>
                            <th style="width:5%">S/N</th>
                            <th>VIN</th>
                            <th>PLATE NUMBER</th> 
                            <th>TTP LOCATION</th>
                            <th>APPROVAL STATUS</th>
                            <th>COMPANY</th>
                            <th></th>
                        </tr>
                    </thead>
                        
                    <tbody>
                        '.$truck.'
                    </tbody>
                </table>
                  
                </div>';        
            return $trucks;
        }
        public function getvendor($coyemail,$regtype){
            $sql = "SELECT * FROM regcoy where coyemail ='$coyemail'";
            $stmt = $this->db->query($sql);
            $rows=$stmt->fetch(PDO::FETCH_ASSOC);
            $coydoc = $this->getdocumentuploads($regtype,$coyemail,'company','');
            $cont = $this->getcontacts($regtype,$coyemail);
            echo json_encode($rows)."~~".$coydoc."~~".$cont;
        }
        public function getavendorcoy($coyemail){
            $sql = "SELECT coyname FROM regcoy where coyemail ='$coyemail'";
            $stmt = $this->db->query($sql);
            $rows=$stmt->fetch(PDO::FETCH_ASSOC);
            $coyname = $rows['coyname'];
            return $coyname;
        }
        public function loaddocsforregistration($regtype,$documentbelongs) {
            $docname ="";
            $query = "SELECT * FROM documentuploadtype WHERE (regtype = 'All' OR regtype = '$regtype') AND documenttype ='$documentbelongs'";
            $stmt = $this->db->query($query);
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
                if($rows["documentname"]==""){
                    $docname ='record not found';
                }else{
                    $docname = $docname.'<option>'.$rows['documentname'].'</option>';
                }
                    
            } 
            return $docname;
        }
        public function approveuprofile($coyemail,$hashpass,$role){
            $dandt = $this->africaDate();
            $sql = "UPDATE regcoy SET status ='Approved' WHERE coyemail ='$coyemail'";
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            $regcon = $this->userlogin($coyemail,$hashpass,$role, $dandt);
            return $regcon;
        } 
        public function disapproveuprofile($coyemail,$reasons){
            $dandt = $this->africaDate();
            $sql = "UPDATE regcoy SET status ='Disapproved',reasons= '$reasons' WHERE coyemail ='$coyemail'";
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
        } 
        public function userlogin($coyemail,$hashpass,$roleid,$dandt){
            //$dandt = $this->africaDate();
            $sql = "INSERT INTO usertbl (username,password,cpassword,role,changefirstlogin,verifydetails,createdby,firstdatecreate,updatecred) VALUES ('$coyemail', '$hashpass','$hashpass','$roleid','0','Yes','Admin','$dandt','$dandt')";
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            return $count;
        }
        public function createuser($username,$password,$urole){
            $dandt = $this->africaDate();
            $sql = "INSERT INTO usertbl(username,upassword,cpassword,urole,changefirstlogin,verifydetails,createdby,firstdatecreate,updatedate)VALUES('$username'$password','$password','$urole','0','0','$username','$dandt','')";
           $stmt = $this->db->query($sql);
           $count= $stmt->rowCount();
           return $count; 
       }
       public function insertemaillog($fromemail,$toemail,$bodyemail,$subjectemail,$statusemail){
            $dandt = $this->africaDate();
            $sql = "INSERT INTO emaillog(fromemail,toemail,bodyemail,subjectemail,statusemail,dandt)VALUES('$fromemail','$toemail','$bodyemail','$subjectemail','$statusemail','$dandt')";
            $stmt = $this->db->query($sql);
            $count = $stmt->rowCount();
            return "0";
        }

        public function loadfforwarder() {
            $cname ="";
            $query = "SELECT * FROM regcoy WHERE regtype = 'Freight Forwarder'";
            $stmt = $this->db->query($query);
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
                if($rows["coyname"]==""){
                    $cname ='record not found';
                }else{
                    $cname = $cname.'<option>'.$rows['coyname'].'</option>';
                }
                    
            } 
            return '<option>Select Frieght Forwarder</option>'.$cname;
        }
        public function mailsender($toemail,$bodyemail,$subjectemail){
                $mail = new PHPMailer();
                $mail->IsSMTP(); // telling the class to use SMTP
                $mail->Host       = "mail.frmlimited.net"; // SMTP server
                $mail->SMTPDebug  = 2;                     // enables SMTP debug information (for testing)
                                                        // 1 = errors and messages
                                                        // 2 = messages only
                $mail->SMTPAuth   = true;                  // enable SMTP authentication
                $mail->SMTPSecure = "tls";                 
                $mail->Host       = "mail.frmlimited.net
                ";      // SMTP server
                $mail->Port       = 587;                   // SMTP port
                // // $mail->Username   = "seller@l";  // username
                // // $var = "32Le6f0r@T".'$'."j";
                // $mail->Password   = "32Le6f0r@T".'$'."j";            // password
                 $mail->Username   = "cichsupport@frmlimited.net";  // username
                 $mail->Password   = "@#Mail123@#";            // password

                $mail->ClearReplyTos();
                $mail->addReplyTo('cichsupport@frmlimited.net','cichsupport@frmlimited.net');
                $mail->SetFrom('cichsupport@frmlimited.net', 'FRM Maritime');

                $mail->Subject    = $subjectemail;

                $mail->MsgHTML($bodyemail);

                $address = $toemail;
                $mail->AddAddress($address);
                if(!$mail->Send()) {
                    //echo "Mailer Error: " . $mail->ErrorInfo;
                  } else {
                    //$upemail = $this->updateemaillog($id);
                    //echo "Message sent!";
                  }
            
        }
        public function approvalprocess($transid,$containerno,$coyname,$conperson,$nextmove,$status,$docpath,$approvedby){
            $dandt = $this->africaDate(); 
            $sql = "INSERT INTO approvalstatus(transid,containerno,coyname,conperson,nextmove,status,docpath,approvedby,dandt)VALUES('$transid','$containerno','$coyname','$conperson','$nextmove','$status','$docpath','$approvedby','$dandt')";
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            $appstatus = $this->updateapprovalprocess($nextmove,$containerno,$transid);
            return "$appstatus";
        }
        public function updateapprovalprocess($nextmove,$containerno,$transid){
            $dandt = $this->africaDate();
            $sql = "UPDATE containertrans SET nextmove ='$nextmove' WHERE containerno='$containerno' and transid ='$transid'";
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            return $count;
        } 
        public function createcontainertrans($transid,$transtype,$shipliner,$containerno,$clearterm,$cargodesc,$deliveryaddy,$expdatentime ){
            $dandt = $this->africaDate(); 
            $sql = "INSERT INTO containertrans(transid,transtype,shipliner,containerno,clearterm,cargodesc,deliveryaddy,expectedtime,payment,nextmove,createdby,dandt
            )VALUES('$transid','$transtype','$shipliner','$containerno','$clearterm','$cargodesc','$deliveryaddy','$expdatentime','Not Paid','Shipper','Dami','$dandt')";
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            return "$count";
        }

        public function updatecontpaystatus($transid){
            $dandt = $this->africaDate();
            $sql = "UPDATE containertrans SET payment ='Paid' WHERE transid='$transid'";
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            return $count;
        } 
        //Register a new container
        public function addcontainer($coyname,$teu,$containerno,$containertype){
            $dandt = $this->africaDate();
            //$coyyname = $this->checkregistration($regtype,$coyemail);
           
            $sql = "INSERT INTO containerdetails(shipliner,teu,containerno,containertype,status,createdby,dandt)VALUES('$coyname','$teu','$containerno','$containertype','Avaliable','$coyname','$dandt')";
          
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            return "$count";
        }
        public function addtruckpictures($vin,$plateno,$docpath,$createdby){
            $dandt = $this->africaDate();
            $sql = "INSERT INTO truckpics(vin,plateno,docpath,createdby,dandt)VALUES('$vin','$plateno','$docpath','$createdby','$dandt')";
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            $truckpic = $this->gettruckspics($plateno,$vin,$createdby);
            return $truckpic;
        }
        public function addservcharge($teu,$servcharge){
            $dandt = $this->africaDate();
            $sql = "INSERT INTO servicecharge(teu,servcharge,status,createdby,dandt)VALUES('$teu','$servcharge','Active','Admin','$dandt')";
            $stmt = $this->db->query($sql); 
            $count= $stmt->rowCount();
            return "$count";
        }
        public function chkcontainner($handle,$title,$vendor){
            $sql = "SELECT count(id) as productid from newpprofile where handle = '$handle' and title = '$title' and vendor = '$vendor'";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $produtid = $rows['productid'];
            return $produtid;
        }
        public function getcontainbyvendor($coyname) {
            $contname = "";
            $query = "select * from containerdetails where shipliner = '$coyname' and status = 'Avaliable' ORDER BY containertype asc ";
            $stmt = $this->db->query($query);
            while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
                $contname = $contname.'<option value="'.$rows['containerno'].'">'.$rows['containertype'].'-'.$rows['containerno'].'</option>';
            }
            return $contname;
        }
        // public function getcontainbyvendor($contname) {
        //     $contname = "";
        //     $query = "select * from containerdetails where shipliner = '$coyname' and status = 'Avaliable' ORDER BY containertype asc ";
        //     $stmt = $this->db->query($query);
        //     while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
        //         $contname = $contname.'<option value="'.$rows['containerno'].'">'.$rows['containertype'].'-'.$rows['containerno'].'</option>';
        //     }
        //     return $contname;
        // }
        public function getteu($contno) {
            $sql = "select * from containerdetails where containerno = '$contno'";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $teu = $rows['teu'];
            return $teu;
           
        }
        public function getservcharge() {
            $sql = "select * from servicecharge";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $servcharge = $rows['servcharge'];
            return $servcharge;
        }
        public function getmenu($urole) {
            $query = "select * from formauth where urole = '$urole' and status <> 'primary' ORDER BY formname asc ";
            $stmt = $this->db->query($query);
            while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
                $menus[] = array('formname'=>$rows['formname'],'formlink'=>$rows['formlink']);
            }
            return $menus;
        }
        public function getcoyregtype($coyregtype) {
            $coyname = "";
            $query = "select * from regcoy where regtype = '$coyregtype' and status = 'Approved' ORDER BY coyname asc ";
            $stmt = $this->db->query($query);   
            while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
                $coyname = $coyname.'<option>'.$rows['coyname'].'</option>';
            }
            return '<option>Select ShipLiner</option>'.$coyname;
        }
        public function getdefaultmenu($urole) {
            $sql = "select * from formauth where urole = '$urole' and status = 'primary'";
            $stmt = $this->db->query($sql);
            $rows = $stmt->fetch(PDO::FETCH_ASSOC);
            $dmenu = $rows['formlink'];
            return $dmenu;
           
        }
        public function verifyuser($coyemail){
            $sql = "SELECT * FROM usertbl where username = '$coyemail'";
            $stmt = $this->db->query($sql);
            while($rows = $stmt->fetch(PDO::FETCH_ASSOC)){
                $vuser[] = array('username'=>$rows['username'],'password'=>$rows['password'],'role'=>$rows['role']);
            }
            if(!isset($vuser)){
                return "";
            }else{
                return $vuser;
            }
           
        }
        public function loadtransactionbyvendors($vendors,$regtype){
            $contrans=$conttrans="";
            $sql = "SELECT * FROM containertrans where createdby = '$vendors'";
            $stmt = $this->db->query($sql);
            $sn = 0;
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
                if($rows['payment'] =='Paid'){
                    if($rows['nextmove'] == $regtype){
                        $btntt ='  <td style="width:2%"> <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "removeproduct" value ="removedproduct" onclick="return selectfforwarder(this)"  data-toggle="tooltip" title="Assign to Freight Forwarder"><img src="assets/images/assigntask.png" style="height:20px"/></<td><td>
                            <button type="button" id ="viewContactButton" onclick="viewcontact(this)" data-toggle="tooltip" title="View Details"style="border: none; background: none;">
                            <i class="fa fa-eye"></i>
                        </button>
                         </td>';
                    }else{
                        $btntt ='<td style="width:2%"> <button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id = "removeproduct" value ="removedproduct" onclick="return selectfforwarder(this)"  data-toggle="tooltip" title="Assign to Freight Forwarder" disabled><img src="assets/images/assigntask.png" style="height:20px" /></<td><td>
                        <button type="button" id ="viewContactButton" onclick="viewcontact(this)" data-toggle="tooltip" title="View Details"style="border: none; background: none;">
                        <i class="fa fa-eye"></i>
                    </button>
                     </td>';
                    }
                    
                    // $btntt =  ' <td style="width:5%"><div class="dropdown no-arrow">
                    //     <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    //         <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    //     </a>
                    //     <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink" style="">
                    //         <a class="dropdown-item" href="#">View Details</a>
                    //         <a class="dropdown-item" href="#"  onclick ="return selectfforwarder()">Assign To Frieght Forwarder</a>
                    //     </div>
                    //     </div></td>';
                }elseif($rows['payment'] =='Not Paid'){
                    $btntt =  ' <td style="width:5%"><div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink" style="">
                    <a class="dropdown-item" href="#">Make Payment</a>
                        <a class="dropdown-item" href="#">View Transaction</a>
                        <a class="dropdown-item" href="#">Edit Transaction</a>
                        <a class="dropdown-item" href="#">Delete Transaction</a>
                    </div>
                    </div></td>';
                }
              

                $contrans = $contrans.'
                    <tr>
                        <td>'.$rows['transid']. '</td>
                        <td>'.$rows['transtype'].'</td>
                        <td>'.$rows['containerno'].'</td>
                        <td>'.$rows['payment'].'</td>
                        <td>'.$rows['nextmove'].'</td>
                        '.$btntt.'
                    </tr>';
            }
           
            $conttrans = $conttrans.'
            <div class="table-responsive-sm">
                <table class="table table-striped table-light" style ="font-size: 0.8rem" id="adminusertbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem">
                        <tr>
                            <th>Transaction id</th>
                            <th>Transaction Type</th> 
                            <th>Containner Number</th>
                            <th>Payment Status</th>
                            <th>Approval Status</th>
                            <th></th>
                            <th></th>
                        </tr>
                    </thead>
                        
                    <tbody>
                        '.$contrans.'
                    </tbody>
                </table>
                  
                </div>';        
            return $conttrans;
        }
        public function loadtransactionforfforwarder($vendors){
            $ffapp=$ffapprove="";
            $sql = "SELECT approvalstatus.containerno,approvalstatus.coyname,approvalstatus.`status`, approvalstatus.docpath, regcoy.coyname as shipper FROM approvalstatus INNER JOIN containertrans ON containertrans.transid = approvalstatus.transid INNER JOIN regcoy ON containertrans.createdby = regcoy.createdby where approvalstatus.coyname = '$vendors'";
            $stmt = $this->db->query($sql);
            $sn = 0;
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
                // $btntt ='<td style="width:2%"><button type="button" class="btn btn-outline-light" style="font-size:small;border-block-color: none;padding: 0;border: none;background: none;text-align: left;" id ="approve" value ="approvetask" onclick="return selectfforwarder(this)" data-toggle="tooltip" title="Approve"><img src="assets/images/approve.png" style="height:20px"/></<td><td>
                //     <button type="button" id ="disapprove" onclick="viewcontact(this)" data-toggle="tooltip" title="Decline"style="border: none; background: none;">
                //     <img src="assets/images/rejected.png" style="height:20px"/>
                //     </button>
                // </td>';
                 $btntt ='<td> <button type="button" id ="disapprove" onclick="viewcontact(this)" data-toggle="tooltip" title="Decline"style="border: none; background: none;">
                 <i class="fa fa-eye"></i>
                    </button>
                </td>';
                $ffapp  = $ffapp.'
                    <tr>
                        <td>'.$rows['containerno']. '</td>
                        <td>'.$rows['coyname'].'</td>
                        <td>'.$rows['status'].'</td>
                        <td>'.$rows['shipper'].'</td>
                        '.$btntt.'
                    </tr>';
            }
           
            $ffapprove = $ffapprove.'
            <div class="table-responsive-sm">
                <table class="table table-striped table-light" style ="font-size: 0.8rem" id="adminusertbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem">
                        <tr>
                        <th>Containner Number</th>
                            <th>Transaction id</th>
                            <th>Company Name</th>
                            <th>Status</th>
                            <th></th>
                         
                        </tr>
                    </thead>
                        
                    <tbody>
                        '.$ffapp.'
                    </tbody>
                </table>
                  
                </div>';        
            return $ffapprove;
        }
        public function loadtransactionbytransid($vendtransid){
            $contrans=$conttrans="";
            $sql = "SELECT containertrans.transtype, containertrans.shipliner, containertrans.containerno, containertrans.cargodesc, containerdetails.teu, containerdetails.containertype FROM containertrans INNER JOIN containerdetails ON containertrans.containerno = containerdetails.containerno
            where transid = '$vendtransid'";
            $stmt = $this->db->query($sql);
            $sn = 0;
            while($rows=$stmt->fetch(PDO::FETCH_ASSOC)){
                // if($rows['payment'] =='Paid'){
                //     $btntt =  ' <td style="width:5%"><div class="dropdown no-arrow">
                //         <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                //             <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                //         </a>
                //         <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink" style="">
                //             <a class="dropdown-item" href="#">View Details</a>
                //             <a class="dropdown-item" href="#">Assign To Frieght Forwarder</a>
                //         </div>
                //         </div></td>';
                // }elseif($rows['payment'] =='Not Paid'){
                //     $btntt =  ' <td style="width:5%"><div class="dropdown no-arrow">
                //     <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                //         <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                //     </a>
                //     <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink" style="">
                //     <a class="dropdown-item" href="#">Make Payment</a>
                //         <a class="dropdown-item" href="#">View Transaction</a>
                //         <a class="dropdown-item" href="#">Edit Transaction</a>
                //         <a class="dropdown-item" href="#">Delete Transaction</a>
                //     </div>
                //     </div></td>';
                // }
              

                $contrans = $contrans.'
                    <tr>
                        <td>'.$rows['shipliner'].'</td>
                        <td>'.$rows['transtype'].'</td>
                        <td>'.$rows['containerno'].'</td>
                        <td>'.$rows['cargodesc'].'</td>
                        <td>'.$rows['teu'].'</td>
                        <td>'.($rows['teu'] * 20).'</td>
                        
                    </tr>';
            }
           
            $conttrans = $conttrans.'
            <div class="table-responsive-sm">
                <table class="table table-striped table-light" style ="font-size: 0.8rem" id="adminusertbl"  cellspacing="0">
                    <thead class="thead-dark" style ="font-size: 0.8rem">
                        <tr>
                            <th>Shipliner</th>
                            <th>Transaction Type</th> 
                            <th>Containner Number</th>
                            <th>Cargo Description</th>
                            <th>TEU</th>
                            <th>Size</th>
                           
                        </tr>
                    </thead>
                        
                    <tbody>
                        '.$contrans.'
                    </tbody>
                </table>
                  
                </div>'; 
                $sercharge =$this->getservcharge();       
            return $conttrans.'~~'.$sercharge;
        }
    }

?>  