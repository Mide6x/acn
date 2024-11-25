<?php
// Include header, sidebar, and footer
include("../acnnew/includes/header.html");
include("../acnnew/includes/sidebar.html");
?>
<main id="main" class="main">

  <section class="section">
    <div class="row">
      <section class="section">
        <div class="row">
          <div class="col-lg-12">

            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Station Details</h5>

                <!-- General Form Elements -->
                <form>
                  <div class="row mb-3">
                    <label for="stationname" class="col-sm-2 col-form-label">Station</label>
                    <div class="col-sm-10">
                      <input type="text" id="stationname" name="stationname" class="form-control">
                    </div>
                  </div>
                  <div class="row mb-3">
                    <label for="stationcode" class="col-sm-2 col-form-label">Station Code</label>
                    <div class="col-sm-10">
                      <input type="text" id="stationcode" name="stationcode" class="form-control">
                    </div>
                  </div>
                  <div class="row mb-3">
                    <label for="stationtype" class="col-sm-2 col-form-label">Station type</label>
                    <div class="col-sm-10">
                      <select class='form-control text-xs' name='stationtype' id='stationtype'>
                        <option value="">Select Station Type</option>
                        <option value="Domestic">Domestic</option>
                        <option value="Regional">Regional</option>
                        <option Value="International">International</option>
                      </select>

                    </div>
                  </div>
                  <div class="row mb-3">

                    <label for="operationtype" class="col-sm-2 col-form-label">Operation type</label>
                    <div class="col-sm-10">
                      <select class='form-control text-xs' name='operationtype' id='operationtype'>
                        <option value="">Select Operation's Type</option>
                        <option value="Fixed">Fixed</option>
                        <option value="Rotary">Rotary</option>
                      </select>
                    </div>
                  </div>
                  <div class="row mb-3">
                    <div class="col-sm-10">
                      <button type="button" class="btn btn-primary" onclick="return createstation()"
                        style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display:block; margin: 0 auto; margin-top:20px"
                        onmouseover="this.style.backgroundColor='#000000';"
                        onmouseout="this.style.backgroundColor='#fc7f14';">ADD
                      </button>
                    </div>
                  </div>

                  <div class="col-lg-12" id="loadstation">

                  </div>

                </form><!-- End General Form Elements -->

              </div>
            </div>

          </div>
        </div>
      </section>

</main><!-- End #main -->

<?php include("../acnnew/includes/footer.html"); ?>