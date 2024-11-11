if (document.getElementById("frmaircraftdetails")) {
    window.addEventListener("load", loadaircraft());
}
//#region aircraft
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
        acstatus = document.getElementById('acstatus'),
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
    
        dataString = "acname=" + decodeEntity(acname.value) + "&actype=" + decodeEntity(actype.value) + "&acstatus=" + acstatus.value + "&nextxx=" + nextxx;
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

    function  createaircraft() {
        let xhr = new XMLHttpRequest();
        let acname = document.getElementById("acname");
        let actype = document.getElementById("actype");
        let regno = document.getElementById("regno");
        let capacity = document.getElementById("capacity");
        let aircraftclass = document.getElementById("class");

        if( acname.value == "" || actype.value == "" || regno.value == "" || capacity.value == "" || aircraftclass.value == ""){
            alert("Please fill all necessary information");
        } else {
            let dataString = " acname=" +  acname.value + "&actype=" + actype.value + "&regno=" + regno.value + "&capacity=" + capacity.value + "&aircraftclass=" + aircraftclass.value;
          xhr.open("POST", 'parameter/parametersetup.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if(this.status == 200) {
                    let response = this.responseText;
                    if (response == "Approved") {
                        alert("OKAY.");
                        location.reload();
                    } else if (response == "Disapproved") {
                        alert("Not Okay.");
                        location.reload();
                    }
                    else {
                        alert("Unexpected response: " + response);
                    }
                } else {
                    alert("An error occurred: " + this.statusText);
                }
                }
            };
            xhr.send(dataString);
        }
    
//#end aircraft region

//#region station
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
        let station = document.getElementById('station'),
        stcode = document.getElementById('code'),
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
    
        dataString = "station=" + decodeEntity(station.value) + "&stcode=" + decodeEntity(stcode.value)  + "&nextxx=" + nextxx;
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
        $("#stationtbl").DataTable();
        }
    
        xhr.send(dataString);
    
    };


    function createstation() {
        let xhr = new XMLHttpRequest();
        let stationname = document.getElementById("stationname");
        let stationcode = document.getElementById("stationcode");
        
        if(stationname.value == "" || stationcode.value == ""){
            alert("Please fill all necessary information");
        } else {
            let dataString = "stationname=" + stationname.value + "&stationcode=" + stationcode.value;
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
    
//#endregion station


// region avaiation charge type

function createaviationtype() {
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

// End aviation charge type

// vendor
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
// endregion vendor


function createdistance() {
    let xhr = new XMLHttpRequest();
    let from = document.getElementById('from');
    let to = document.getElementById('to');
    let distancekm = document.getElementById('distancekm');
    let distancemiles = document.getElementById('distancemiles');
    
    if(from.value == "" || to.value == "" || distancekm.value == "" || distancemiles.value == ""){
        alert("Please fill all necessary information");
    } else {
        let dataString = "from=" + from.value + "&to=" + to.value + "&distancekm=" + distancekm.value+ "&distancemiles=" + distancemiles.value;
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

//region callsign

function createcallsign() {
    let xhr = new XMLHttpRequest();
    let callname = document.getElementById("callname");
    let abbv = document.getElementById("abbv");
    let cst = document.getElementById("cst");

    if (callname.value === "" || abbv.value === "" || cst.value === "") {
        alert("Please fill all necessary information");
    } else {
        let dataString = "callname=" + callname.value+ "&abbv=" + abbv.value + "&cst=" + cst.value;
        xhr.open("POST", 'parameter/parametersetup.php', true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onload = function () {
            if (this.status === 200) {
                let response = this.responseText;
                if (response === "Approved") {
                    alert("OKAY.");
                    location.reload();
                } else if (response === "Disapproved") {
                    alert("Not Okay.");
                    location.reload();
                } else {
                    alert("Unexpected response: " + response);
                }
            } else {
                alert("An error occurred: " + this.statusText);
            }
        };
        xhr.send(dataString);
    }
}
//endregion callsign

//region flight
function createflightdetails() {
    let xhr = new XMLHttpRequest();
    let flightno = document.getElementById("flightno");
    let sst= document.getElementById("sst");
    let set = document.getElementById("set");
    let from= document.getElementById("from");
    let  to= document.getElementById("to");
    let crt= document.getElementById("crt");
    
    if(flightno.value == "" ||  sst.value == "" ||  set.value == "" || from.value == "" ||to.value == "" || crt.value == "" ){
        alert("Please fill all necessary information");
    } else {
        let dataString = "flightno=" + flightno.value + "&sst=" + sst.value + "&set=" + set.value + "&from=" + from.value + "&to=" + to.value + "&crt=" + crt.value;
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

// end regionflight

// aviationcharge
    function createaviationcharges(){
        let xhr = new XMLHttpRequest();
        let name = document.getElementById('name');
        let aircrafttype = document.getElementById('aircrafttype');
        let season = document.getElementById('season');
        let station = document.getElementById('station');
        let amount = document.getElementById('amount');
        let value = document.getElementById('value');

        if(name.value==="" || aircrafttype.value==="" || season.value==="" || station.value==="" || amount.value==="" || value.value===""){
            alert("Please fill all necessary information"); 
        } else {
            let dataString = "name=" + name.value+ "&aircrafttype=" + aircrafttype.value + "&season=" + season.value + "&station=" + station.value+ "&amount=" + amount.value + "&value=" + value.value;
            xhr.open("POST", 'parameter/parametersetup.php', true);
            xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
            xhr.onload = function () {
                if (this.status === 200) {
                    let response = this.responseText;
                    if (response === "Approved") {
                        alert("OKAY.");
                        location.reload();
                    } else if (response === "Disapproved") {
                        alert("Not Okay.");
                        location.reload();
                    } else {
                        alert("Unexpected response: " + response);
                    }
                } else {
                    alert("An error occurred: " + this.statusText);
                }
            };
            xhr.send(dataString);
        }
    }