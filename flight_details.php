<?php
include("header");
include("sidebar");
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
                <div class="row mb-3">
                  <label for="flightno" class="col-sm-2 col-form-label">Flight No
                  </label>
                  <div class="col-sm-10">
                    <input type="text" id="flightno" name="flightno" class="form-control">
                  </div>
                </div>

                    <div class="col-md-6">
                        <label for="sst" class="form-label">Sch Start Time</label>
                        <input type="time" class="form-control" id="sst" name="sst">
                        </div>
                        <div class="col-md-6">
                        <label for="set" class="form-label">Sch End Time</label>
                        <input type="time" class="form-control" id="set" name="set">
                    </div>

                    <div class="col-md-6">
                        <label for="from" class="form-label">From</label>
                        <input type="text" class="form-control" id="from" name="from">
                        </div>
                        <div class="col-md-6">
                        <label for="from" class="form-label">To</label>
                        <input type="text" class="form-control" id="to" name="to">
                    </div>

                    <div class="col-md-6">
                        <label for="crt" class="form-label">Crew Reporting Time</label>
                        <input type="time" class="form-control" id="crt" name="crt">
                    </div>

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
include("footer");
?>
<script src="assets\js\ac.js"></script>