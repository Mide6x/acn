var phoneRegex = /^(\+?\d{1,3})?[-.\s]?(\d{3})[-.\s]?(\d{3})[-.\s]?(\d{4})$/;
if (document.getElementById("loadaircraft")) {
    window.addEventListener("load", loadaircraft());
}
if (document.getElementById("loadstation")) {
    window.addEventListener("load", loadstation());
}
if (document.getElementById("schstarttime")) {
    let routeFromDropdown = document.getElementById('schstarttime'),
         routeToDropdown = document.getElementById('schendtime');
    populateTimeDropdown(routeFromDropdown);
    populateTimeDropdown(routeToDropdown);
    loadroutes();
    loadflightdets();
} 

if (document.getElementById("chargetype")) {
    loadchargetype();
    loadchargebody();
    loadavachargetype();
}

if (document.getElementById("sidebar")) {
    loadmenuforuser();
}

if (document.getElementById("stafflfname")) {
    getstaffname();
}

if (document.getElementById("loadmenu")) {
    loadmenu();
}if (document.getElementById("rolecheck")) {
    //getroles();
    loadrolechecklist();
    loaduser();
    loadoptstation()
    loadbusinessunit()
}
document.addEventListener("DOMContentLoaded", function() {
    if (document.getElementById("menucheck")) {
        loadmenuchecklist();
        loadrole();
    }

});
// if (document.getElementById("loadroles")) {
//    loadrole();
// }

 

//#region aircraft
    function  createaircraft(){
        let xhr = new XMLHttpRequest();
        let acname = document.getElementById("acname");
            actype = document.getElementById("actype"),
            acregno = document.getElementById("acregno"),
            accapacity = document.getElementById("accapacity"),
            acversion = document.getElementById("acversion");
        //let dataString =""
        if( acname.value === "" || actype.value === "" || acregno.value === "" || accapacity.value === "" || acversion.value === ""){
            alert("Please fill all necessary information");
        } else {
            dataString= "acname=" +  acname.value + "&actype=" + actype.value + "&acregno="+ acregno.value + "&accapacity=" + accapacity.value + "&acversion=" + acversion.value;
         
            xhr.open("POST", 'parameter/parametersetup.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if(this.status == 200) {
                    let vars = this.responseText.trim();
                    if(vars == "1"){
                        alert("Station already exsit")
                        this.loadstation()
                    }else{
                        document.getElementById("acname").value="";
                        document.getElementById("actype").value="";
                        document.getElementById("acregno").value=""; 
                        document.getElementById("accapacity").value="";
                        document.getElementById("acversion").value="";
                        document.getElementById("loadaircraft").innerHTML =vars;
                    }
                }
            }
        };
        xhr.send(dataString);
    }
    function editaircraft(button) {
        var row = button.closest("tr"); // Find the parent row of the clicked button
        var cells = row.cells;
        // Access cell data by index
        document.getElementById('acname').value = cells[1].textContent;
        document.getElementById('actype').value = cells[2].textContent;
        document.getElementById('acregno').value = cells[3].textContent;
        document.getElementById('accapacity').value = cells[5].textContent;
        document.getElementById('acversion').value = cells[4].textContent;
        let xhr = new XMLHttpRequest();
        //window.open("../admin/vendorprofile?email=" + emailAddress +"&regtype=" +registrationType , "_self");
    }
    function enabledisableaircraft(acregno) {
        let xhr = new XMLHttpRequest(),
          sacregno = acregno,
          sacstatus = document.getElementById(acregno);
        if (sacstatus.checked == true) {
          acstatus = 'Active'
        } else {
            acstatus = 'In-active'
        }
        dataString = "sacstatus=" + acstatus + "&sacregno=" + acregno;
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
          if(this.status == 200) {
            if (sacstatus.checked == true) {
                alert('Aircraft activated.')
                loadaircraft()
              } else {
                alert('Aircraft deactivated.')
                loadaircraft()
              }
          }
        }
        xhr.send(dataString);
    }; 
    function nextacrecord() {
        if (document.querySelector('table tr:last-child td:first-child') === null) {
    
        } else {
        document.querySelector('table tr:last-child td:first-child').innerHTML = "";
        }
    
        loadaircraft()
    }
    function preacrecord() {
        document.querySelector('table tr:last-child td:first-child').innerText = document.querySelector('table tr:first-child td:first-child').innerHTML - 51;
    
        loadaircraft()
    }
    function loadaircraft() {
        let xhr = new XMLHttpRequest();
        let acname = document.getElementById('acname'),
            actype = document.getElementById('actype'),
            //acstatus = document.getElementById('acstatus'),
        nextxx = document.querySelector('table tr:last-child td:first-child');
    
        // if((acname.value != "" || actype.value !="Type" || acstatus.value !="Status")){
        //   nextxx = document.querySelector('table tr:last-child td:first-child');
        //     nextxx = ""
        // }
        if (nextxx === null) {
        nextxx = ""
        } else {
        nextxx = document.querySelector('table tr:last-child td:first-child').innerHTML;
        // if((searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor") && (nextxx !== null)){
        //   if((nextxx >= 50) && (searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor"))
        //   nextxx = "";
        //   }else{
        //     nextxx = nextxx;
        // }
        }
    
        dataString = "facname=" +acname.value+ "&factype=" +actype.value+ "&nextxx=" +nextxx;
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
        if (this.status == 200) {
            let vars = this.responseText;
            document.getElementById("loadaircraft").innerHTML = vars;
            if (typeof $('table tr:first-child td:first-child').html() == 'undefined') {
            document.getElementById("pre").disabled = true;
            document.getElementById("next").disabled = true;
            }
            else if ($('table tr:first-child td:first-child').html() == 1 && document.getElementById("aircrafttbl").rows.length < 50) {
            document.getElementById("pre").disabled = true;
            document.getElementById("next").disabled = true;
            } else if ($('table tr:first-child td:first-child').html() > 1 && (document.getElementById("aircrafttbl").rows.length < 50)) {
            document.getElementById("pre").disabled = false;
            document.getElementById("next").disabled = true;
    
            } else if (document.getElementById("aircrafttbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() != 1) {
            document.getElementById("pre").disabled = false;
            document.getElementById("next").disabled = false;
    
            }
            else if (document.getElementById("aircrafttbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() == 1) {
            document.getElementById("pre").disabled = true;
            document.getElementById("next").disabled = false;
    
            }
        }
        $("#aircrafttbl").DataTable();
        }
    
    
        xhr.send(dataString);
    
    };
//#endregion

//#region station
    function createstation() {
    let xhr = new XMLHttpRequest();
    let stationname = document.getElementById("stationname"),
        stationcode = document.getElementById("stationcode"),
        stationtype = document.getElementById("stationtype"),
        operationtype = document.getElementById("operationtype");   
    if(stationname.value == "" || stationcode.value == ""){
        alert("Please fill all necessary information");
    } else {
        let dataString = "stationname=" + stationname.value + "&stationcode=" + stationcode.value +"&stationtyp="+ stationtype.value +"&operationtype="+ operationtype.value;
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if(this.status == 200) {
                let vars = this.responseText.trim();
                if(vars == "1"){
                    alert("Station already exsit")
                    this.loadstation()
                }else{
                    document.getElementById("stationname").value="";
                    document.getElementById("stationcode").value="";
                    document.getElementById("stationtype").value=""; 
                    document.getElementById("operationtype").value="";
                    document.getElementById("loadstation").innerHTML =vars;
                }
            }
        };
        xhr.send(dataString);
    }
    }
    function loadoptstation() {
        let xhr = new XMLHttpRequest();
        let routes = "";
            let dataString = "routes=" + routes; 
            xhr.open("POST", 'parameter/parametersetup.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if(this.status == 200) {
                    let vars = this.responseText.trim();
                    document.getElementById('station').innerHTML = vars;
                }
            };
            xhr.send(dataString);
        }
    function loadroutes() {
        let xhr = new XMLHttpRequest();
        let routes = "";
            let dataString = "routes=" + routes; 
            xhr.open("POST", 'parameter/parametersetup.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if(this.status == 200) {
                    let vars = this.responseText.trim();
                    document.getElementById('routefrom').innerHTML = vars;
                    document.getElementById('routeto').innerHTML = vars;
                }
            };
            xhr.send(dataString);
    }
    function editstation(button) {
        var row = button.closest("tr"); // Find the parent row of the clicked button
        var cells = row.cells;
        // Access cell data by index
        document.getElementById('stationname').value = cells[1].textContent;
        document.getElementById('stationcode').value = cells[2].textContent;
        document.getElementById('stationtype').value = cells[3].textContent;
        document.getElementById('operationtype').value = cells[4].textContent;
        let xhr = new XMLHttpRequest();
        //window.open("../admin/vendorprofile?email=" + emailAddress +"&regtype=" +registrationType , "_self");
    }
    function removestation(button) {
        var row = button.closest("tr"); // Find the parent row of the clicked button
        var cells = row.cells;
        // Access cell data by index
        document.getElementById('stationname').value = cells[1].textContent;
        document.getElementById('stationcode').value = cells[2].textContent;
        document.getElementById('stationtype').innerText = cells[3].textContent;
        let xhr = new XMLHttpRequest();
        //window.open("../admin/vendorprofile?email=" + emailAddress +"&regtype=" +registrationType , "_self");
    }
    function enabledisablestations(station) {
        let xhr = new XMLHttpRequest(),
          sstation = station,
          sstatus = document.getElementById(station);
        if (sstatus.checked == true) {
          stationstatus = 'Active'
        } else {
            stationstatus = 'In-active'
        }
        dataString = "sstatus=" + stationstatus + "&sstation=" + sstation;
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
          if(this.status == 200) {
            if (sstatus.checked == true) {
                alert('Station activated.')
                loadstation()
              } else {
                alert('Station deactivated.')
                loadstation()
              }
          }
        }
        xhr.send(dataString);
    };
    function nextstationrecord() {
        if (document.querySelector('table tr:last-child td:first-child') === null) {
    
        } else {
        document.querySelector('table tr:last-child td:first-child').innerHTML = "";
        }
    
        loadstation()
    }
    function prestationrecord() {
        document.querySelector('table tr:last-child td:first-child').innerText = document.querySelector('table tr:first-child td:first-child').innerHTML - 51;
    
        loadstation()
    }
    function loadstation() {
    
        let xhr = new XMLHttpRequest();
        let stationtype = document.getElementById('stationtype'),
        nextxx = document.querySelector('table tr:last-child td:first-child');
    
        // if((acname.value != "" || actype.value !="Type" || acstatus.value !="Status")){
        //   nextxx = document.querySelector('table tr:last-child td:first-child');
        //     nextxx = ""
        // }
        if (nextxx === null) {
        nextxx = ""
        } else {
        nextxx = document.querySelector('table tr:last-child td:first-child').innerHTML;
        // if((searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor") && (nextxx !== null)){
        //   if((nextxx >= 50) && (searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor"))
        //   nextxx = "";
        //   }else{
        //     nextxx = nextxx;
        // }
        }
        if(stationtype.value == 'All' ){
            stationtype.value ="";
        }
        dataString = "stationtype=" + stationtype.value + "&nextxx=" + nextxx;
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
        if (this.status == 200) {
            let vars = this.responseText;
            document.getElementById("loadstation").innerHTML = vars;
            if (typeof $('table tr:first-child td:first-child').html() == 'undefined') {
            document.getElementById("pre").disabled = true;
            document.getElementById("next").disabled = true;
            }
            else if ($('table tr:first-child td:first-child').html() == 1 && document.getElementById("stationtbl").rows.length < 50) {
            document.getElementById("pre").disabled = true;
            document.getElementById("next").disabled = true;
            } else if ($('table tr:first-child td:first-child').html() > 1 && (document.getElementById("stationtbl").rows.length < 50)) {
            document.getElementById("pre").disabled = false;
            document.getElementById("next").disabled = true;
    
            } else if (document.getElementById("stationtbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() != 1) {
            document.getElementById("pre").disabled = false;
            document.getElementById("next").disabled = false;
    
            }
            else if (document.getElementById("stationtbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() == 1) {
            document.getElementById("pre").disabled = true;
            document.getElementById("next").disabled = false;
    
            }
        }
        //$("#stationtbl").DataTable();
        }
    
    
        xhr.send(dataString);
    
    };
//#endregion station

//#region  Avaition Charges
function createaviachargetype() {
    let xhr = new XMLHttpRequest();
    let name = document.getElementById("chargename");
    //let checkseason = document.getElementById("checkseason");
    let checkaircraft = document.getElementById("checkaircraft");
    let chargetype = document.getElementById("chargetype");
    let body = document.getElementById("chargebody");

    if (name.value === "" || chargetype.value === "" || body.value === "") {
        alert("Please fill all necessary information");
        return;
    }

    if (checkaircraft.checked) {
        aircraft = "Yes";
    }else{
        aircraft = "No";
    }

    let dataString = "chargename=" + name.value + 
                     "&isaircraft=" +aircraft+ 
                     "&chargetype=" + chargetype.value + 
                     "&chargebody=" + body.value;
    
    xhr.open("POST", 'parameter/parametersetup.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (this.status === 200) {
            let vars = this.responseText;
           document.getElementById('loadaviationchargestype').innerHTML = vars
        }
    };
    xhr.send(dataString);
}
function loadchargetype() {
    let xhr = new XMLHttpRequest();
    let gchargetype = "";
    let dataString = "gchargetype=" + gchargetype ; 
    xhr.open("POST", 'parameter/parametersetup.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if(this.status == 200) {
            let vars = this.responseText.trim();
            document.getElementById('chargetype').innerHTML = vars;
        }
    };
    xhr.send(dataString);
}
function loadchargebody() {
let xhr = new XMLHttpRequest();
let gchargebody = "";
    let dataString = "gchargebody=" + gchargebody ; 
    xhr.open("POST", 'parameter/parametersetup.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if(this.status == 200) {
            let vars = this.responseText.trim();
            document.getElementById('chargebody').innerHTML = vars;
        }
    };
    xhr.send(dataString);
} 
function enabledisablechargetype(cchargetypeid) {
    let xhr = new XMLHttpRequest(),
        chargetypeid = cchargetypeid,
        cchargetype = document.getElementById(chargetypeid);
    if (cchargetype.checked == true) {
      chargetypestatus = 'Active'
    } else {
        chargetypestatus = 'In-active'
    }
    dataString = "chargetypestatus=" + chargetypestatus + "&chargetypeid=" + chargetypeid;
    xhr.open("POST", 'parameter/parametersetup.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
      if(this.status == 200) {
        if (cchargetype.checked == true) {
            alert('Charge type activated.')
            loadavachargetype();

          } else {
            alert('Charge type deactivated.')
            loadavachargetype();
          }
      }
    }
    xhr.send(dataString);
  };
function nextavachargestyperecord() {
    if (document.querySelector('table tr:last-child td:first-child') === null) {

    } else {
        document.querySelector('table tr:last-child td:first-child').innerHTML = "";
    }
        
    loadavachargetype()
}
function preavachargestyperecord() {
    document.querySelector('table tr:last-child td:first-child').innerText = document.querySelector('table tr:first-child td:first-child').innerHTML - 51;
    loadavachargetype()
}

function loadavachargetype(){
    let xhr = new XMLHttpRequest();
    let lchargetype = '',
        nextxx = document.querySelector('table tr:last-child td:first-child');

    // if((acname.value != "" || actype.value !="Type" || acstatus.value !="Status")){
    //   nextxx = document.querySelector('table tr:last-child td:first-child');
    //     nextxx = ""
    // }
    if (nextxx === null) {
    nextxx = ""
    } else {
    nextxx = document.querySelector('table tr:last-child td:first-child').innerHTML;
    // if((searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor") && (nextxx !== null)){
    //   if((nextxx >= 50) && (searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor"))
    //   nextxx = "";
    //   }else{
    //     nextxx = nextxx;
    // }
    }

    dataString = "lchargetype="+ lchargetype + "&nextxx=" + nextxx;
    xhr.open("POST", 'parameter/parametersetup.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
    if (this.status == 200) {
        let vars = this.responseText;
        document.getElementById("loadaviationchargestype").innerHTML = vars;
        if (typeof $('table tr:first-child td:first-child').html() == 'undefined') {
        document.getElementById("pre").disabled = true;
        document.getElementById("next").disabled = true;
        }
        else if ($('table tr:first-child td:first-child').html() == 1 && document.getElementById("avachargestypetbl").rows.length < 50) {
        document.getElementById("pre").disabled = true;
        document.getElementById("next").disabled = true;
        } else if ($('table tr:first-child td:first-child').html() > 1 && (document.getElementById("avachargestypetbl").rows.length < 50)) {
        document.getElementById("pre").disabled = false;
        document.getElementById("next").disabled = true;

        } else if (document.getElementById("avachargestypetbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() != 1) {
        document.getElementById("pre").disabled = false;
        document.getElementById("next").disabled = false;

        }
        else if (document.getElementById("avachargestypetbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() == 1) {
        document.getElementById("pre").disabled = true;
        document.getElementById("next").disabled = false;

        }
    }
    $("#avachargestbl").DataTable();
    }


    xhr.send(dataString);

}



function nextavachargesrecord() {
    if (document.querySelector('table tr:last-child td:first-child') === null) {

    } else {
        document.querySelector('table tr:last-child td:first-child').innerHTML = "";
    }
        
    loadavacharges()
}
function preavachargesrecord() {
    document.querySelector('table tr:last-child td:first-child').innerText = document.querySelector('table tr:first-child td:first-child').innerHTML - 51;
    loadavacharges()
}
function loadavacharges() {

    let xhr = new XMLHttpRequest();
    let chargesname = document.getElementById('chargesname'),
        aircrafttype = document.getElementById('aircrafttype'),
        season = document.getElementById('season'),
        station = document.getElementById('station'),
        chargesvaluetype = document.getElementById('chargesvaluetype'),
        chargesstatus = document.getElementById('chargesstatus');
        nextxx = document.querySelector('table tr:last-child td:first-child');

    // if((acname.value != "" || actype.value !="Type" || acstatus.value !="Status")){
    //   nextxx = document.querySelector('table tr:last-child td:first-child');
    //     nextxx = ""
    // }
    if (nextxx === null) {
    nextxx = ""
    } else {
    nextxx = document.querySelector('table tr:last-child td:first-child').innerHTML;
    // if((searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor") && (nextxx !== null)){
    //   if((nextxx >= 50) && (searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor"))
    //   nextxx = "";
    //   }else{
    //     nextxx = nextxx;
    // }
    }

    dataString = "chargesname="+ chargesname.value+ "&aircrafttype="+aircrafttype.value+"&season"+season.value+"station=" + station.value+ "&chargesvaluetype=" + chargesvaluetype.value + "chargesstatus" +chargesstatus.value+ "&nextxx=" + nextxx;
    xhr.open("POST", 'parameter/parametersetup.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
    if (this.status == 200) {
        let vars = this.responseText;
        document.getElementById("loadavacharges").innerHTML = vars;
        if (typeof $('table tr:first-child td:first-child').html() == 'undefined') {
        document.getElementById("pre").disabled = true;
        document.getElementById("next").disabled = true;
        }
        else if ($('table tr:first-child td:first-child').html() == 1 && document.getElementById("avachargestbl").rows.length < 50) {
        document.getElementById("pre").disabled = true;
        document.getElementById("next").disabled = true;
        } else if ($('table tr:first-child td:first-child').html() > 1 && (document.getElementById("avachargestbl").rows.length < 50)) {
        document.getElementById("pre").disabled = false;
        document.getElementById("next").disabled = true;

        } else if (document.getElementById("avachargestbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() != 1) {
        document.getElementById("pre").disabled = false;
        document.getElementById("next").disabled = false;

        }
        else if (document.getElementById("avachargestbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() == 1) {
        document.getElementById("pre").disabled = true;
        document.getElementById("next").disabled = false;

        }
    }
    $("#avachargestbl").DataTable();
    }


    xhr.send(dataString);

}

//#endregion Aviation
//#endregion Aviation
//#region Flight detials
function  createflightdetails(){
    let xhr = new XMLHttpRequest();
    let flightno = document.getElementById("flightno");
    let schstarttime = document.getElementById("schstarttime");
    let schendtime = document.getElementById("schendtime");
    let routefrom = document.getElementById("routefrom");
    let routeto = document.getElementById("routeto");
    let crewreporttime = document.getElementById("crewreporttime");
    let dataString =""
    if( flightno.value === "" || schstarttime.value === "" || schendtime.value === "" || routefrom.value === "" || routeto.value === "" || crewreporttime.value === ""){
        alert("Please fill all necessary information");
    } else {
        dataString= "flightno=" +  flightno.value + "&schstarttime=" + schstarttime.value + "&schendtime="+ schendtime.value + "&routefrom=" + routefrom.value + "&routeto=" + routeto.value + "&crewreporttime=" + crewreporttime.value;
     
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if(this.status == 200) {
                let vars = this.responseText.trim();
                if(vars == "1"){
                    alert("Station already exsit")
                    this.loadstation()
                }else{
                    document.getElementById("flightno").value="";
                    document.getElementById("schstarttime").value="";
                    document.getElementById("schendtime").value=""; 
                    document.getElementById("routefrom").value="";
                    document.getElementById("routeto").value="";
                    document.getElementById("crewreporttime").value="";
                    document.getElementById("loadflightdetails").innerHTML =vars;
                }
            }
        }
    };
    xhr.send(dataString);
}
function editafightdetails(button) {
    var row = button.closest("tr"); // Find the parent row of the clicked button
    var cells = row.cells;
    // Access cell data by index
    document.getElementById('flightno').value = cells[1].textContent;
    document.getElementById('schstarttime').value = cells[2].textContent;
    document.getElementById('schendtime').value = cells[3].textContent;
    document.getElementById('routefrom').value = cells[4].textContent;
    document.getElementById('routeto').value = cells[5].textContent;
    document.getElementById('crewreporttime').value = cells[6].textContent;
    let xhr = new XMLHttpRequest();
    //window.open("../admin/vendorprofile?email=" + emailAddress +"&regtype=" +registrationType , "_self");
}
function enabledisablefightdetails(flightno) {
    let xhr = new XMLHttpRequest(),
        fltno = flightno,
        flstatus = document.getElementById(fltno);
    if (flstatus.checked == true) {
        flstatus = 'Active'
    } else {
        flstatus = 'In-active'
    }
    dataString = "fltstatus=" + flstatus + "&flightno=" + fltno;
    xhr.open("POST", 'parameter/parametersetup.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
      if(this.status == 200) {
        if (flstatus.checked == true) {
            alert('Aircraft activated.') 
            loadaircraft()
          } else {
            alert('Aircraft deactivated.')
            loadaircraft()
          }
      }
    }
    xhr.send(dataString);
}; 
function nextflightdetrecord() {
    if (document.querySelector('table tr:last-child td:first-child') === null) {

    } else {
        document.querySelector('table tr:last-child td:first-child').innerHTML = "";
    }
        
    loadavacharges()
}
function preflightdetrecord() {
    document.querySelector('table tr:last-child td:first-child').innerText = document.querySelector('table tr:first-child td:first-child').innerHTML - 51;
    loadavacharges()
}
function loadflightdets() {

    let xhr = new XMLHttpRequest();
    let loadfltno = document.getElementById('flightno'),
        nextxx = document.querySelector('table tr:last-child td:first-child');

    // if((acname.value != "" || actype.value !="Type" || acstatus.value !="Status")){
    //   nextxx = document.querySelector('table tr:last-child td:first-child');
    //     nextxx = ""
    // }
    if (nextxx === null) {
    nextxx = ""
    } else {
    nextxx = document.querySelector('table tr:last-child td:first-child').innerHTML;
    // if((searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor") && (nextxx !== null)){
    //   if((nextxx >= 50) && (searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor"))
    //   nextxx = "";
    //   }else{
    //     nextxx = nextxx;
    // }
    }

    dataString = "fltno="+ loadfltno.value+ "&nextxx="+ nextxx;
    xhr.open("POST", 'parameter/parametersetup.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
    if (this.status == 200) {
        let vars = this.responseText;
        document.getElementById("loadflightdetails").innerHTML = vars;
        if (typeof $('table tr:first-child td:first-child').html() == 'undefined') {
        document.getElementById("pre").disabled = true;
        document.getElementById("next").disabled = true;
        }
        else if ($('table tr:first-child td:first-child').html() == 1 && document.getElementById("flightdettbl").rows.length < 50) {
        document.getElementById("pre").disabled = true;
        document.getElementById("next").disabled = true;
        } else if ($('table tr:first-child td:first-child').html() > 1 && (document.getElementById("flightdettbl").rows.length < 50)) {
        document.getElementById("pre").disabled = false;
        document.getElementById("next").disabled = true;

        } else if (document.getElementById("flightdettbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() != 1) {
        document.getElementById("pre").disabled = false;
        document.getElementById("next").disabled = false;

        }
        else if (document.getElementById("flightdettbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() == 1) {
        document.getElementById("pre").disabled = true;
        document.getElementById("next").disabled = false;

        }
    }
    $("#flightdettbl").DataTable();
    }


    xhr.send(dataString);

}
function populateTimeDropdown(dropdown) {
    for (let h = 0; h < 24; h++) {
        for (let m = 0; m < 60; m += 1) {
            const hour = h.toString().padStart(2, '0');
            const minute = m.toString().padStart(2, '0');
            const time = `${hour}:${minute}`;
            const option = document.createElement('option');
            option.value = time;
            option.textContent = time;
            dropdown.appendChild(option);
        }
    }
}
//#endregion
//#region Vendor Details
//#region Menu Creation
    function  createmenu(){
        let xhr = new XMLHttpRequest();
        let menuname = document.getElementById("menuname"),
            menulink = document.getElementById("menulink"),
            menutitle = document.getElementById("menutitle");


        //let dataString =""
        if( menuname.value === "" || menulink.value === "" || menutitle.value === "" ){
            alert("Please fill all necessary information");
        } else {
            dataString= "menuname=" +  menuname.value + "&menulink=" + menulink.value + "&menutitle=" + menutitle.value;
        
            xhr.open("POST", 'parameter/parametersetup.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if(this.status == 200) {
                    let vars = this.responseText.trim();
                    if(vars == "1"){
                        alert("Menu already exist.")
                        this.loadmenu()
                    }else{
                        document.getElementById("menuname").value="";
                        document.getElementById("menulink").value="";
                        document.getElementById("menutitle").value="";
                        document.getElementById("loadmenu").innerHTML =vars;
                    }
                }
            }
        };
        xhr.send(dataString);
    }
    function editmenu(button) {
        var row = button.closest("tr"); // Find the parent row of the clicked button
        var cells = row.cells;
        // Access cell data by index
        document.getElementById('menuname').value = cells[1].textContent;
        document.getElementById('menulink').value = cells[2].textContent;
        document.getElementById('menutitle').value = cells[3].textContent;
        let xhr = new XMLHttpRequest();
        //window.open("../admin/vendorprofile?email=" + emailAddress +"&regtype=" +registrationType , "_self");
    }
    function enabledisablemenu(menuid) {
        let xhr = new XMLHttpRequest(),
        menuids = menuid,
        menustatus = document.getElementById(menuid);
        if (menustatus.checked == true) { 
        menustat = 'Active';
        } else {
            menustat = 'In-active';
        }
        dataString = "menustatus=" + menustat + "&menuid=" + menuids;
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
        if(this.status == 200) {
            if (menustatus.checked == true) {
                alert('Menu activated.')
                loadmenu()
            } else {
                alert('Menu deactivated.')
                loadmenu()
            }
        }
        }
        xhr.send(dataString);
    }; 
    function nextmenurecord() {
        if (document.querySelector('table tr:last-child td:first-child') === null) {

        } else {
        document.querySelector('table tr:last-child td:first-child').innerHTML = "";
        }

        loadmenu()
    }
    function premenurecord() {
        document.querySelector('table tr:last-child td:first-child').innerText = document.querySelector('table tr:first-child td:first-child').innerHTML - 51;

        loadmenu()
    }
    function loadmenu() {
        let xhr = new XMLHttpRequest();
        let lmenu = "",
            //acstatus = document.getElementById('acstatus'),
        nextxx = document.querySelector('table tr:last-child td:first-child');

        // if((acname.value != "" || actype.value !="Type" || acstatus.value !="Status")){
        //   nextxx = document.querySelector('table tr:last-child td:first-child');
        //     nextxx = ""
        // }
        if (nextxx === null) {
        nextxx = ""
        } else {
        nextxx = document.querySelector('table tr:last-child td:first-child').innerHTML;
        // if((searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor") && (nextxx !== null)){
        //   if((nextxx >= 50) && (searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor"))
        //   nextxx = "";
        //   }else{
        //     nextxx = nextxx;
        // }
        }

        dataString = "lmenu=" +lmenu+ "&nextxx=" +nextxx;
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
        if (this.status == 200) {
            let vars = this.responseText;
            document.getElementById("loadmenu").innerHTML = vars;
            if (typeof $('table tr:first-child td:first-child').html() == 'undefined') {
            document.getElementById("pre").disabled = true;
            document.getElementById("next").disabled = true;
            }
            else if ($('table tr:first-child td:first-child').html() == 1 && document.getElementById("menutbl").rows.length < 50) {
            document.getElementById("pre").disabled = true;
            document.getElementById("next").disabled = true;
            } else if ($('table tr:first-child td:first-child').html() > 1 && (document.getElementById("menutbl").rows.length < 50)) {
            document.getElementById("pre").disabled = false;
            document.getElementById("next").disabled = true;

            } else if (document.getElementById("menutbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() != 1) {
            document.getElementById("pre").disabled = false;
            document.getElementById("next").disabled = false;

            }
            else if (document.getElementById("menutbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() == 1) {
            document.getElementById("pre").disabled = true;
            document.getElementById("next").disabled = false;

            }
        }
        $("#menutbl").DataTable();
        }


        xhr.send(dataString);

    };
    function loadmenuchecklist() {
        let xhr = new XMLHttpRequest(),
            gmenu = "";
        dataString = "gmenu=" + gmenu;
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if(this.status == 200) {
                vars = this.responseText;
                document.getElementById('menucheck').innerHTML = vars;
                loadrole();
            }
        }
        xhr.send(dataString);
    };
    function loadmenuforuser() {
        let xhr = new XMLHttpRequest(),
            umenu = "";
        dataString = "umenu=" + umenu;
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if(this.status == 200) {
                vars = this.responseText;
                document.getElementById('sidebar').innerHTML = vars;
            }
        }
        xhr.send(dataString);
    }; 
//#endregion 
//#region Roles
    function createroles() {
        // Get form elements
        const roleName = document.getElementById('rolename').value;
        const checkboxes = document.querySelectorAll('input[name="item[]"]:checked');
        
        // Get selected checkbox values
        const selectedItems = [];
        checkboxes.forEach((checkbox) => {
            selectedItems.push(checkbox.value);
        });

        // Prepare data to be sent
        const data = {
            menuname: roleName,
            items: selectedItems
        };

        // Send data to PHP script using fetch
        fetch('parameter/parametersetup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.text())
        .then(data => {
            // Display response
            document.getElementById('loadroles').innerHTML = data;
        })
        // .catch(error => console.error('Error:', error));
    };
    function  getroles(){
        let xhr = new XMLHttpRequest();
        let urole = "";
        //let dataString =""
       
            dataString= "urole=" +  urole.value ;
        
            xhr.open("POST", 'parameter/parametersetup.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if(this.status == 200) {
                    let vars = this.responseText.trim();
                    document.getElementById('userroles').innerHTML = vars;
                }
          
        };
        xhr.send(dataString);
    }
    function editrole(button) {
        var row = button.closest("tr"); // Find the parent row of the clicked button
        var cells = row.cells;
        // Access cell data by index
        document.getElementById('rolename').value = cells[1].textContent;
        checkeditems = cells[2].textContent.split(",");
        const checkboxes = document.querySelectorAll('input[name="item[]"]');
        let selectedValues =[];
        checkboxes.forEach(function(checkbox) {
            selectedValues.push(checkbox.value);
        });
        let checkliststring ='';
        selectedValues.forEach(function(item) {
            // Check if the current item is in the array of items to be checked
            let isChecked = checkeditems.includes(item) ? 'checked' : '';
        
            // Build the checklist HTML string with the checked attribute if needed
            checkliststring += `<li style="margin-right: 20px;">
                                  <input type="checkbox" name="item[]" style="margin-right: 5px;" value="${item}" ${isChecked}>
                                  <label>${item}</label>
                                </li>`;
        });
        menuchecks = '<label for="menuchecklist" class="col-sm-2 col-form-label">Menus</label><div class="col-sm-10"><ul style="display: flex; list-style-type: none;padding: 0;" id ="menuchecklist">'+checkliststring+'</ul></div>';  


        document.getElementById('menucheck').innerHTML = menuchecks;
       

        //window.open("../admin/vendorprofile?email=" + emailAddress +"&regtype=" +registrationType , "_self");
    }
    function nextrolerecord() {
        if (document.querySelector('table tr:last-child td:first-child') === null) {

        } else {
        document.querySelector('table tr:last-child td:first-child').innerHTML = "";
        }

        loadrole()
    }
    function prerolerecord() {
        document.querySelector('table tr:last-child td:first-child').innerText = document.querySelector('table tr:first-child td:first-child').innerHTML - 51;

        loadrole()
    }
    function loadrole() {
        let xhr = new XMLHttpRequest();
        let lrole = "";
        dataString = "lrole=" +lrole;
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
        if (this.status == 200) {
            let vars = this.responseText;
            document.getElementById("loadroles").innerHTML = vars;
        }
        $("#menutbl").DataTable();
        }


        xhr.send(dataString);

    };
    function loadrolechecklist() {
        let xhr = new XMLHttpRequest(),
            rmenu = "";
        dataString = "grole=" + rmenu;
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if(this.status == 200) {
                vars = this.responseText;
                document.getElementById('rolecheck').innerHTML = vars;
                //loadrole();
            }
        }
        xhr.send(dataString);
    }; 
//#endregion     
//#region Createuser
    function createusers() {
        // Get form elements
        let staffid = document.getElementById('staffid').value,
            station = document.getElementById('station').value,
            title = document.getElementById('title').value,
            lastname = document.getElementById('lastname').value,
            firstname = document.getElementById('firstname').value,
            emailaddress = document.getElementById('emailaddress').value,
            businessunit = document.getElementById('businessunit').value,
            department = document.getElementById('department').value,
            departunit= document.getElementById('departunit').value,
            userroles = document.querySelectorAll('input[name="item[]"]:checked');
            const selectedItems = [];
            userroles.forEach((checkbox) => {
                selectedItems.push(checkbox.value);
            });

        // Prepare data to be sent
        const data = {
            cstaffid: staffid,
            cstation:station,
            ctitle:title,
            clastname: lastname,
            cfirstname: firstname,
            cemailaddress: emailaddress,
            cbizunit:businessunit,
            cdeparment:department,
            cdepartunit:departunit,
            cpagetitle: 'User Creation',
            cuserroles: selectedItems
        };
       
        // Send data to PHP script using fetch
        fetch('parameter/parametersetup.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        })
        .then(response => response.text())
        .then(data => {
            if(data == 1){
                alert('User already exsist.')
                loaduser();
            }else{
                document.getElementById('loadusers').innerHTML = data;
            }
            // Display response
           // document.getElementById('loadusers').innerHTML = data;
        })
        .catch(error => console.error('Error:', error));
    };
    function  getroles(){
        let xhr = new XMLHttpRequest();
        let urole = "";
        //let dataString =""
       
            dataString= "urole=" +  urole.value ;
        
            xhr.open("POST", 'parameter/parametersetup.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if(this.status == 200) {
                    let vars = this.responseText.trim();
                    document.getElementById('userroles').innerHTML = vars;
                }
          
        };
        xhr.send(dataString);
    }
    function  getstaffname(){
        let xhr = new XMLHttpRequest();
        let stname = "";
        //let dataString =""
       
            dataString= "stname="+stname;
        
            xhr.open("POST", 'parameter/parametersetup.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if(this.status == 200) {
                    let vars = this.responseText.trim();
                    document.getElementById('stafflfname').innerHTML = vars;
                }
          
        };
        xhr.send(dataString);
    }
    function editrole(button) {
        var row = button.closest("tr"); // Find the parent row of the clicked button
        var cells = row.cells;
        // Access cell data by index
        document.getElementById('rolename').value = cells[1].textContent;
        checkeditems = cells[2].textContent.split(",");
        const checkboxes = document.querySelectorAll('input[name="item[]"]');
        let selectedValues =[];
        checkboxes.forEach(function(checkbox) {
            selectedValues.push(checkbox.value);
        });
        let checkliststring ='';
        selectedValues.forEach(function(item) {
            // Check if the current item is in the array of items to be checked
            let isChecked = checkeditems.includes(item) ? 'checked' : '';
        
            // Build the checklist HTML string with the checked attribute if needed
            checkliststring += `<li style="margin-right: 20px;">
                                  <input type="checkbox" name="item[]" style="margin-right: 5px;" value="${item}" ${isChecked}>
                                  <label>${item}</label>
                                </li>`;
        });
        menuchecks = '<label for="menuchecklist" class="col-sm-2 col-form-label">Menus</label><div class="col-sm-10"><ul style="display: flex; list-style-type: none;padding: 0;" id ="menuchecklist">'+checkliststring+'</ul></div>';  


        document.getElementById('menucheck').innerHTML = menuchecks;
       

        //window.open("../admin/vendorprofile?email=" + emailAddress +"&regtype=" +registrationType , "_self");
    }
    function nextuserrecord() {
        if (document.querySelector('table tr:last-child td:first-child') === null) {

        } else {
        document.querySelector('table tr:last-child td:first-child').innerHTML = "";
        }

        loaduser()
    }
    function preuserrecord() {
        document.querySelector('table tr:last-child td:first-child').innerText = document.querySelector('table tr:first-child td:first-child').innerHTML - 51;

        loaduser()
    }
    function loaduser() {
        let xhr = new XMLHttpRequest();
        let luser = "";
            //acstatus = document.getElementById('acstatus'),
        //nextxx = document.querySelector('table tr:last-child td:first-child');

        // if((acname.value != "" || actype.value !="Type" || acstatus.value !="Status")){
        //   nextxx = document.querySelector('table tr:last-child td:first-child');
        //     nextxx = ""
        // }
        // if (nextxx === null) {
        // nextxx = ""
        // } else {
        // nextxx = document.querySelector('table tr:last-child td:first-child').innerHTML;
        // // if((searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor") && (nextxx !== null)){
        // //   if((nextxx >= 50) && (searchs.value !="" || prodstatus.value !="Status" || prodtype.value !="Product Type" || svendor.value !="Select Vendor"))
        // //   nextxx = "";
        // //   }else{
        // //     nextxx = nextxx;
        // // }
        // }

        dataString = "luser=" +luser;
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
        if (this.status == 200) {
            let vars = this.responseText;
            document.getElementById('loadusers').innerHTML =vars;
            //document.getElementById("loaduser").innerHTML = vars;
            // if (typeof $('table tr:first-child td:first-child').html() == 'undefined') {
            // document.getElementById("pre").disabled = true;
            // document.getElementById("next").disabled = true;
            // }
            // else if ($('table tr:first-child td:first-child').html() == 1 && document.getElementById("menutbl").rows.length < 50) {
            // document.getElementById("pre").disabled = true;
            // document.getElementById("next").disabled = true;
            // } else if ($('table tr:first-child td:first-child').html() > 1 && (document.getElementById("menutbl").rows.length < 50)) {
            // document.getElementById("pre").disabled = false;
            // document.getElementById("next").disabled = true;

            // } else if (document.getElementById("menutbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() != 1) {
            // document.getElementById("pre").disabled = false;
            // document.getElementById("next").disabled = false;

            // }
            // else if (document.getElementById("menutbl").rows.length >= 50 && $('table tr:first-child td:first-child').html() == 1) {
            // document.getElementById("pre").disabled = true;
            // document.getElementById("next").disabled = false;

            // }
        }
        $("#usertbl").DataTable();
        }


        xhr.send(dataString);

    };
    function loadbusinessunit() {
        let xhr = new XMLHttpRequest();
        let bizunit = '';
            let dataString = "bizunit=" +bizunit; 
            xhr.open("POST", 'parameter/parametersetup.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if(this.status == 200) {
                    let vars = this.responseText.trim();
                    document.getElementById('businessunit').innerHTML = vars;
                    document.getElementById('departunit').innerHTML = '';
                    
            };
        }
        xhr.send(dataString);
    }
    function loaddepartment() {
        let xhr = new XMLHttpRequest();
        let bizcode= document.getElementById('businessunit').value;
            let dataString = "bizcode=" +bizcode; 
            xhr.open("POST", 'parameter/parametersetup.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if(this.status == 200) {
                    let vars = this.responseText.trim();
                    document.getElementById('department').innerHTML = vars;
            };
        }
        xhr.send(dataString);
    }
    function loaddeptunit() {
        let xhr = new XMLHttpRequest();
        let deptcode= document.getElementById('department').value;
            let dataString = "deptcode=" +deptcode; 
            xhr.open("POST", 'parameter/parametersetup.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if(this.status == 200) {
                    let vars = this.responseText.trim();
                    document.getElementById('departunit').innerHTML = vars;
            };
        }
        xhr.send(dataString);
    }
//#endregion
//#region notification
    function createnotify() {
            let notifytpe = document.getElementById('notifytype').value,
            emailsubject = document.getElementById('emailsubject').value,
            emailbody = document.getElementById('emailbody').value;

            // Prepare data to be sent
            const data = {
                title: notifytpe,
                emailsubj: emailsubject,
                emailbody: emailbody,
                createdby: 'adeniji.o@acn.aero'
            };
        
            // Send data to PHP script using fetch
            fetch('parameter/parametersetup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(data)
            })
            .then(response => response.text())
            .then(data => {
                if(data == 1){
                    alert('Notification already exsist.')
                }else{
                    alert('Notification created.')
                }
            })
            .catch(error => console.error('Error:', error));
        };
//#endregion

//#region profile
        
//region biodata 
function createbiodata() {
    //const phoneRegex = /^(\+?\d{1,3})?[-.\s]?(\d{3})[-.\s]?(\d{3})[-.\s]?(\d{4})$/;
    const bemailaddressRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    let xhr = new XMLHttpRequest();
        staffid = document.getElementById('staffid').value;
        btitle = document.getElementById('btitle').value;
        bsurname = document.getElementById('bsurname').value;
        bfirstname = document.getElementById('bfirstname').value;
        bmiddlename = document.getElementById('bmiddlename').value;
        bmaidenname = document.getElementById('bmaidenname').value;
        breligion = document.getElementById('breligion'). value;
        if (!bemailaddressRegex.test(document.getElementById('bemailaddress').value)) {
            alert("Please enter a valid email address.");
            return;
        }else{
            bemailaddress = document.getElementById('bemailaddress').value;
        }
        if (!phoneRegex.test(document.getElementById('bphonenumber').value)) {
            alert('Incorrect phone number');
            return;
        } else {
            bphonenumber = document.getElementById('bphonenumber').value;
        }
        bdob = document.getElementById('bdob').value;
        bgender = document.getElementById('bgender').value;
        bmstatus = document.getElementById('bmstatus').value;
        blanguage = document.getElementById('blanguage').value;
        bnationality = document.getElementById('bnationality').value;
        bstateoforigin = document.getElementById('bstateoforigin').value;
        blga = document.getElementById('blga').value;
        braddress = document.getElementById('braddress').value;
        bstateofres = document.getElementById('bstateofres').value;
        bcountryofres = document.getElementById('bcountryofres').value;
   
    if (staffid === "" || btitle === "" || bsurname === "" || bfirstname === "" || bmiddlename === "" || bmaidenname === "" || 
      breligion === "" || bemailaddress === "" || bphonenumber === "" ||  bdob === "" || bgender === "" || bmstatus === "" 
      || blanguage === "" || bnationality === "" || bstateoforigin === "" || blga === "" || braddress === "" || bstateofres === "" || bcountryofres === "" ) {
        alert('Please fill all necessary information');
        return;
    }
   
   
    let dataString =
        "staffid=" + staffid +
        "&btitle=" + btitle +
        "&bsurname=" + bsurname +
        "&bfirstname=" + bfirstname +
        "&bmiddlename=" + bmiddlename +
        "&bmaidenname=" + bmaidenname +
        "&breligion=" + breligion +
        "&bemailaddress=" + bemailaddress +
        "&bphonenumber=" + bphonenumber +
        "&bdob=" + bdob +
        "&bgender=" + bgender +
        "&bmstatus=" + bmstatus +
        "&blanguage=" + blanguage +
        "&bnationality=" + bnationality +
        "&bstateoforigin=" + bstateoforigin+
        "&blga=" + blga +
        "&braddress=" + braddress +
        "&bstateofres=" + bstateofres +
        "&bcountryofres=" + bcountryofres ;
        
    xhr.open("POST", 'parameter/parametersetup.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  
    xhr.onload = function () {
      if (this.status == 200) {
        let vars = this.responseText;
        document.getElementById('biodata').style.display = 'none';
        if(bmstatus == 'Single'){
            document.getElementById('nokdata').style.display = 'block';
        }else{
            document.getElementById('familydata').style.display = 'block';
        }
      }
    };
    xhr.send(dataString);
  }
 
   
  
  function loadstate() {
    let xhr = new XMLHttpRequest();
    let state = document.getElementById('bnationality').value;
    let dataString = "state=" + state;
    xhr.open("POST", 'parameter/parametersetup.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
      if (this.status == 200) {
        let vars = this.responseText.trim();
        document.getElementById('chargebody').innerHTML = vars;
      }
    };
    xhr.send(dataString);
  }
  
    function loadlga() {
      let xhr = new XMLHttpRequest();
      let lga = document.getElementById('bstateoforigin').value;
      let dataString = "lga=" + lga;
      xhr.open("POST", 'parameter/parametersetup.php', true);
      xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      xhr.onload = function () {
        if (this.status == 200) {
          let vars = this.responseText.trim();
          document.getElementById('chargebody').innerHTML = vars;
        }
      };
      xhr.send(dataString);
  }
  // #end region biodata
  
  
  // region family data
  function createfamilydata(){
   
    let xhr = new XMLHttpRequest();
    let fsname = document.getElementById('fsname').value, 
        ffname = document.getElementById('ffname').value, 
        frelationship = document.getElementById('frelationship').value, 
        fdob = document.getElementById('fdob').value, 
        fphonenumber = document.getElementById('fphonenumber').value,
        foccupation = document.getElementById('foccupation').value; 
        if (!phoneRegex.test(fphonenumber)) {
            alert('Incorrect phone number');
            return;
        }
  
    if(fsname === "" || ffname === "" || frelationship === "" || fdob === "" || foccupation === "" || fphonenumber === ""){
      alert('Please fill all necessary information');
      return;
    }
  let dataString =
    "fsname=" + fsname +
    "&ffname=" + ffname +
    "&frelationship=" + frelationship +
    "&fdob=" + fdob +
    "&foccupation=" + foccupation+
    "&fphonenumber=" + fphonenumber;
  
    xhr.open("POST", 'parameter/parametersetup.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  
    xhr.onload = function () {
      if (this.status == 200) {
        let vars = this.responseText;
        document.getElementById('familytable').innerHTML = vars;
        // document.getElementById('familydata').style.display = 'none';
        // document.getElementById('nokdata').style.display = 'block';
      }
    };
    xhr.send(dataString);
  }
  function completefamdata(){
    document.getElementById('familydata').style.display = 'none';
    document.getElementById('nokdata').style.display = 'block';
  }

  // end region familydata
  
 
  // region next of kin
  function createnok() {
    let xhr = new XMLHttpRequest();
    let nsname = document.getElementById('nsname').value,
        nfname = document.getElementById('nfname').value,
        nrelationship = document.getElementById('nrelationship').value,
        nraddress = document.getElementById('nraddress').value,
        nphonenumber = document.getElementById('nphonenumber').value,
        bensname = document.getElementById('bensname').value,
        benfname = document.getElementById('benfname').value,
        benrelationship = document.getElementById('benrelationship').value,
        benaddress = document.getElementById('benaddress').value,
        benphonenumber = document.getElementById('benphonenumber').value,
        porganisation = document.getElementById('porganisation').value,
        pid = document.getElementById('pid').value,
        tid  = document.getElementById('tid').value;
    if (!phoneRegex.test(nphonenumber) || !phoneRegex.test(benphonenumber)) {
        alert('Incorrect phone number');
        return;
    }
    if (nsname === "" || nfname === "" || nrelationship === "" || nraddress === "" || nphonenumber === ""|| bensname === "" || benfname === "" || benrelationship === "" || benaddress === "" || benphonenumber === "" || porganisation === "" || pid === ""  || tid === "") {
        alert('Please fill all necessary information');
        return;
    }
  
    // Build the data string
    let dataString =
        "nsname=" + nsname +
        "&nfname=" + nfname +
        "&nrelationship=" + nrelationship +
        "&nraddress=" + nraddress +
        "&nphonenumber=" + nphonenumber +  
        "&bensname=" + bensname + 
        "&benfname=" + benfname +
        "&benrelationship=" + benrelationship +
        "&benaddress=" + benaddress +
        "&benphonenumber=" + benphonenumber + 
        "&porganisation=" + porganisation +
        "&pid =" + pid  +
        "&tid=" + tid ;
  
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      
        xhr.onload = function () {
          if (this.status == 200) {
            let vars = this.responseText;
                document.getElementById('nokdata').style.display = 'none';
                document.getElementById('employmentdata').style.display = 'block';
          }
        };
        xhr.send(dataString);
      }
  //#endregion
  
  
  //employment
  function createmp() {
    let xhr = new XMLHttpRequest();
    let emname = document.getElementById('emname').value,
     emdesignation = document.getElementById('emdesignation').value,
     emfdate = document.getElementById('emfdate').value,
     emtdate = document.getElementById('emtdate').value;
  
    if (emname === "" || emdesignation === "" || emfdate === "" || emtdate === "") {
        alert('Please fill all necessary information');
        return;
    }
  
    let dataString = "emname=" + emname +
        "&emdesignation=" + emdesignation +
        "&emfdate=" + emfdate +
        "&emtdate=" + emtdate 
  
    xhr.open("POST", 'parameter/parametersetup.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  
    xhr.onload = function () {
        if (this.status == 200) {
            let response = this.responseText;
            document.getElementById('employmentdata').style.display = 'none';
            document.getElementById('educationdetail').style.display = 'block'; 
        }
    };
    xhr.send(dataString);
  }
  
      // region education
        function createducation(){
          let xhr = new XMLHttpRequest();
          let edtype = document.getElementById('edtype').value, 
              edinstitution = document.getElementById('edinstitution').value, 
              edfdate = document.getElementById('edfdate').value, 
              edtdate = document.getElementById('edtdate').value, 
              eddegree = document.getElementById('eddegree').value, 
              edgrade = document.getElementById('edgrade').value;
        
          if(edtype === "" ||  edinstitution === "" || edfdate === "" || edtdate === "" || eddegree === "" || edgrade === ""){
            alert('Please fill all necessary information');
            return;
          }
        let dataString =
          "edtype=" + edtype +
          "&edinstitution=" + edinstitution +
          "&edfdate=" + edfdate +
          "&edtdate=" + edtdate +
          "&eddegree=" + eddegree ;
          "&edgrade=" + edgrade ;
        
          xhr.open("POST", 'parameter/parametersetup.php', true);
          xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        
          xhr.onload = function () {
            if (this.status == 200) {
              let vars = this.responseText;
              document.getElementById('educationdetail').style.display = 'none';
              document.getElementById('certificatondetail').style.display = 'block';
            }
          };
          xhr.send(dataString);
        }
  
        // region certificate
        function createcertificate(){
          let xhr = new XMLHttpRequest();
          let cerinstitution = document.getElementById('cerinstitution').value; 
              cercourse = document.getElementById('cercourse').value; 
              cerdate = document.getElementById('cerdate').value; 
              cerexpiry = document.getElementById('cerexpiry').value; 
              cerupload = document.getElementById('cerupload').value; 
        
          if(cerinstitution === "" ||  cercourse === "" || cerdate === "" || cerexpiry === "" || cerupload === "" ){
            alert('Please fill all necessary information');
            return;
          }
        let dataString =
          "cerinstitution=" + cerinstitution +
          "&cercourse=" + cercourse +
          "&cerdate=" + cerdate +
          "&cerexpiry=" + cerexpiry +
          "&cerupload=" + cerupload ;
        
          xhr.open("POST", 'parameter/parametersetup.php', true);
          xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        
          xhr.onload = function () {
            if (this.status == 200) {
              let vars = this.responseText;
              if (vars == "Approved") {
                alert("OKAY.");
                location.reload();
              } else if (vars == "Disapproved") {
                alert("Not Okay.");
                location.reload();
              }
            }
          };
          xhr.send(dataString);
          document.getElementById('certificatondetail').style.display = 'none';
          document.getElementById('trainingdetail').style.display = 'block';
          return false;
        } 
  
      // region training
      function createtraining(){
        let xhr = new XMLHttpRequest();
        let traname = document.getElementById('traname').value; 
            tracourse = document.getElementById('tracourse').value; 
            tradate = document.getElementById('tradate').value; 
            traupload = document.getElementById('traupload').value; 
      
        if(traname === "" ||  tracourse === "" || tradate === "" || traupload === ""){
          alert('Please fill all necessary information');
          return;
        }
      let dataString =
        "traname=" + traname +
        "&tracourse=" + tracourse +
        "&tradate=" + tradate +
        "&traupload=" + traupload ;
      
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      
        xhr.onload = function () {
          if (this.status == 200) {
            let vars = this.responseText;
            if (vars == "Approved") {
              alert("OKAY.");
              location.reload();
            } else if (vars == "Disapproved") {
              alert("Not Okay.");
              location.reload();
            }
          }
        };
        xhr.send(dataString);
        document.getElementById('trainingdetail').style.display = 'none';
        document.getElementById('crewdetail').style.display = 'block';
        return false;
      } 
  
      // region crew
      function createcrew(){
        let xhr = new XMLHttpRequest();
        let cactype = document.getElementById('cactype').value; 
            cposition = document.getElementById('cposition').value; 
            chrs = document.getElementById('chrs').value;  
      
        if(cactype === "" ||  cposition === "" || chrs === ""){
          alert('Please fill all necessary information');
          return;
        }
      let dataString =
        "cactype=" + cactype +
        "&cposition=" + cposition +
        "&chrs=" + chrs ;
      
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
      
        xhr.onload = function () {
          if (this.status == 200) {
            let vars = this.responseText;
            if (vars == "Approved") {
              alert("OKAY.");
              location.reload();
            } else if (vars == "Disapproved") {
              alert("Not Okay.");
              location.reload();
            }
          }
        };
        xhr.send(dataString);
      } 
//#endregion