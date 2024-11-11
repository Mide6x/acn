<?php
    include('../include/config.php');
    $_SESSION['username'] = 'adeniji.o@acn.aero';
#Station details
    if(isset($_POST['stationtype'])){
        $stationtype = $_POST["stationtype"];
        $nextxx = $_POST["nextxx"];
        if($nextxx ==""){
            $nextxx = "0";
        }
        $stationdets = $revenue->loadstations($stationtype,'',$nextxx);
        echo $stationdets;
    }
    if(isset($_POST['stationname'])){
        $station = $_POST['stationname'];
        $stationcode = $_POST['stationcode'];
        $stationtype = $_POST['stationtyp'];
        $operationtype = $_POST['operationtype'];
        $stationdet = $revenue->createstation($station,$stationcode,$stationtype,$operationtype,'Active',$_SESSION['username'],0);
        echo $stationdet;
    }
   
    if(isset($_POST['sstatus'])){
        $sstatus = $_POST['sstatus'];
        $station= $_POST['sstation'];
        $revenue->updatestationsatus($station,$sstatus);
    }
    if(isset($_POST['routes'])){
        $routes = $revenue->loadroutes();
        echo $routes;
    }
#end station detals
#Aircraft details
    if(isset($_POST['acname'])){
        $acname = $_POST['acname'];
        $actype = $_POST['actype'];
        $acregno = $_POST['acregno'];
        $accapacity = $_POST['accapacity'];
        $acversion = $_POST['acversion'];
        $aircraftdet = $revenue->createaircraftinfo($acname,$actype,$acregno,$accapacity,$acversion,'Active',$_SESSION['username'],0);
        echo $aircraftdet; 
    }
    if(isset($_POST['factype'])){
        $actype = $_POST["factype"];
        $acname = $_POST["facname"];
        $nextxx = $_POST["nextxx"];
        if($nextxx ==""){
            $nextxx = "0";
        }
        $aircraftdets = $revenue->loadacdetails($acname,$actype,'',$nextxx);
        echo $aircraftdets;
    }

    if(isset($_POST['sacstatus'])){
        $acstatus = $_POST['sacstatus'];
        $acregno = $_POST['sacregno'];
        $revenue-> updateaircraftsatus($acregno,$acstatus);
    }
#End Aircraft details
#Fight details
    if(isset($_POST['fltno'])){
        $flightno = $_POST["fltno"];
        $nextxx = $_POST["nextxx"];
        if($nextxx ==""){
            $nextxx = "0";
        }
        $flightdets = $revenue->loadflightdetails('','',$nextxx);
        echo $flightdets;
    }
    if(isset($_POST['flightno'])){
        $flightno = $_POST['flightno'];
        $crewreporttime = $_POST['crewreporttime'];
        $schstarttime = $_POST['schstarttime'];
        $schendtime = $_POST['schendtime'];
        $routefrom = $_POST['routefrom'];
        $routeto = $_POST['routeto'];
        $flightdet = $revenue->createflightdetails($flightno,$schstarttime,$schendtime,$routefrom,$routeto,$crewreporttime,'Active',$_SESSION['username']);
        echo $flightdet;
    }

    if(isset($_POST['sstatus'])){
        $sstatus = $_POST['sstatus'];
        $station= $_POST['sstation'];
        $revenue->updatestationsatus($station,$sstatus);
    }
#End Flight details
    if(isset($_POST['chargetype'])){
        $chargetype  = $_POST['chargetype'];
        $chargebody = $_POST['chargebody'];
        $chargename = $_POST['chargename'];
        $isaircrafttype = $_POST['aircrafttype'];
        $isseason = $_POST['season'];
        $station = $_POST['station'];
        $chargesvaluetype = $_POST['chargesvaluetype'];
        $chargesstatus = $_POST['chargesstatus'];
        $avachargetypedets = $revenue->createaviationchargetype($chargetype,$chargebody,$chargename,$isseason,$isaircrafttype,'Active',$_SESSION['username']);
        return $avachargetypedets; 
    }



?>  