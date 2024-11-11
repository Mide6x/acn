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
function createavachargetype() {
    let xhr = new XMLHttpRequest();
    let name = document.getElementById("name");
    let checkseason = document.getElementById("checkseason");
    let checkaircraft = document.getElementById("checkaircraft");
    let chargetype = document.getElementById("chargetype");
    let body = document.getElementById("body");

    if (name.value.trim() === "" || chargetype.value === "select" || body.value === "select") {
        alert("Please fill all necessary information");
        return;
    }

    if (checkseason.checked) {
        season = "yes";
    } else {
        season = "no";
    }

    if (checkaircraft.checked) {
        aircraft = "yes";
    } else {
        aircraft = "no";
    }

    let dataString = "name=" + name.value + 
                     "&checkseason=" + checkseason.checked + 
                     "&checkaircraft=" + checkaircraft.checked + 
                     "&chargeType=" + chargetype.value + 
                     "&body=" + body.value;
    
    xhr.open("POST", 'parameter/parametersetup.php', true);
    xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhr.onload = function () {
        if (this.status === 200) {
            let vars = this.responseText;
            if (vars === "Approved") {
                alert("OKAY.");
                location.reload();
            } else if (vars === "Disapproved") {
                alert("Not Okay.");
                location.reload();
            }
        }
    };
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


//#region Vendor Details
    function createvendor() {
        let xhr = new XMLHttpRequest();
        let companyname = document.getElementById("companyname");
        let purpose = document.getElementById("purpose");
        
        if(companyname.value == "" || purpose.value == ""){
            alert("Please fill all necessary information");
        } else {
            let dataString = "companyname=" + companyname.value + "&purpose=" + purpose.value;
            xhr.open("POST", 'parameter/parametersetup.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if(this.status == 200) {
                    let vars = this.responseText;
                    if (vars == "Approved") {
                        alert("OKAY.");
                        location.reload();
                    } else if (vars == "Disapproved"){
                        alert("Not Okay.");
                        location.reload();
                    }
                }
            };
            xhr.send(dataString);
        }
    }
