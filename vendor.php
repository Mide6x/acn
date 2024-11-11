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
              <h5 class="card-title">Vendor Details</h5>

              <!-- General Form Elements -->
              <form>
                <div class="row mb-3">
                  <label for="companyname" class="col-sm-2 col-form-label">Company Name</label>
                  <div class="col-sm-10">
                    <input type="text" id="companyname" name="companyname" class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <label id="purpose" class="col-sm-2 col-form-label">Purpose</label>
                  <div class="col-sm-10">
                    <select class="form-select" aria-label="Default select example">
                      <option selected>select</option>
                      <option value="1">Nill</option>
                      <option value="2"></option>
                      <option value="3"></option>
                    </select>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-sm-10">
                    <button type="submit" class="btn btn-primary" onclick="return createvendor()"
                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">ADD
                    </button>
                  </div>
                </div>

                <div class="col-lg-12" id="loadvendor">
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