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
              <h5 class="card-title">Call sign</h5>

              <!-- General Form Elements -->
              <form>
                <div class="row mb-3">
                  <label for="callname" class="col-sm-2 col-form-label">Name</label>
                  <div class="col-sm-10">
                    <input type="text" id="callname" name="callname" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="abbv" class="col-sm-2 col-form-label">ABBV</label>
                  <div class="col-sm-10">
                    <input type="text" id="abbv" name="abbv"class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="cst" class="col-sm-2 col-form-label">Call Sign Type</label>
                  <div class="col-sm-10">
                    <input type="text" id="cst" name="cst"class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-sm-10">
                    <button type="submit" class="btn btn-primary" onclick="return createcallsign()"
                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">ADD
                    </button>
                  </div>
                </div>

                <div class="col-lg-12" id="loadcallsign">
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
<script src="assets\js\ac.js"></script>