<?php
include "header";
include "sidebar";
?>
<main id="main" class="main">

<section class="section">
  <div class="row">
  <section class="section">
      <div class="row">
        <div class="col-lg-12">
        <div class="card">
        <div class="card">
            <div class="card-body">
              <h5 class="card-title">Flight Details Setup</h5>

                <form class="row g-3">
                <div class="col-md-6">
                  <label for="flightno" class="form-label">Flight No</label>
                    <input type="text" id="flightno" name="flightno" class="form-control">
                </div>
                <div class="col-md-6">
                        <label for="crewreporttime" class="form-label">Crew Reporting Time(Hours)</label>
                        <input type="input" class="form-control" id="crewreporttime" name="crewreporttime">
                    </div>
                      <div class="col-md-6">
                        <label for="schstarttime" class="form-label">Schedule Start Time:</label>
                        <select id="schstarttime" class="form-control" name="schstarttime"></select>
                      </div>
                      <div class="col-md-6">
                        <label for="schendtime" class="form-label">Schedule End Time</label>
                        <select id="schendtime"class="form-control" name="schendtime"></select>

                    </div>

                    <div class="col-md-6">
                        <label for="routefrom" class="form-label">Route From:</label>
                        <select id="routefrom" class="form-control" name="routefrom"></select>
                      </div>
                      <div class="col-md-6">
                        <label for="routeto" class="form-label">Route To:</label>
                        <select id="routeto"class="form-control" name="routeto"></select>

                      </div>
                        <!-- <label for="routefrom" class="form-label">From</label>
                        <input type="text" class="form-control" id="routefrom" name="routefrom">
                        </div>
                        <div class="col-md-6">
                        <label for="from" class="form-label">To</label>
                        <input type="text" class="form-control" id="routeto" name="routeto"> -->




                    <div class="row mb-3">
                  <div class="col-sm-10">
                    <button type="button" class="btn btn-primary" onclick=" return createflightdetails()"
                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">ADD
                    </button>
                  </div>
                </div>

                <div class="col-lg-12" id="loadflightdetails">
                    </div>
                </form><!-- End General Form Elements -->

                </div>

            </div>
          </div>

        </div>
  </div>
</section>

</main><!-- End #main -->
<?php
include "footer";
?>
