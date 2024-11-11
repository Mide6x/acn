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
            <div class="card-body">
              <h5 class="card-title">Aircraft Details</h5>

              <!-- General Form Elements -->
              <form>
                <div class="row mb-3">
                  <label for="acname" class="col-sm-2 col-form-label">Aircraft Name</label>
                  <div class="col-sm-10">
                    <input type="text" id="acname" name="acname" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="actype" class="col-sm-2 col-form-label">Aircraft Type</label>
                  <div class="col-sm-10">
                    <input type="text" id="actype" name="actype"class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                <label for="acregno" class="col-sm-2 col-form-label">Reg No</label>
                <div class="col-sm-10">
                  <input type="text" id="acregno" name="acregno"class="form-control">
                </div>
              </div>

              <div class="row mb-3">
                <label for="accapacity" class="col-sm-2 col-form-label">Capacity</label>
                <div class="col-sm-10">
                  <input type="text" id="accapacity" name="accapacity"class="form-control">
                </div>
              </div>

              <div class="row mb-3">
                <label for="acversion" class="col-sm-2 col-form-label">Version</label>
                <div class="col-sm-10">
                  <input type="text" id="acversion" name="acversion" class="form-control">
                </div>
              </div>

              <div class="row mb-3">
                  <div class="col-sm-10">
                    <button type="button" class="btn btn-primary" onclick = "return createaircraft()"
                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">ADD
                    </button>
                  </div>
                </div>

                <div class="col-lg-12" id="loadaircraft">
                  </div>

              </form><!-- End General Form Elements -->

            </div>
          </div>

        </div>
  </div>
</section>

</main><!-- End #main -->
<?php
include("footer");
?>
<!-- <script src="../assets/ac.js"></script> -->
<!-- <script src="assets/js/ac.js"></script> -->