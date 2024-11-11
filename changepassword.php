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
              <h5 class="card-title" style="font-weight: 600;">Change Password</h5>

              <!-- General Form Elements -->
              <form>
                <div class="row mb-3">
                  <label for="passwordemail" class="col-sm-2 col-form-label">Old Password</label>
                  <div class="col-sm-10">
                    <input type="text" id="oldpassword" name="oldpassword" class="form-control">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="newpassword" class="col-sm-2 col-form-label">New Password</label>
                  <div class="col-sm-10">
                    <input type="text" id="newpassword" name="newpassword"class="form-control">
                  </div>
                </div>
                <div class="row mb-3">
                    <label for="confirmpassword" class="col-sm-2 col-form-label">Confirm Password</label>
                    <div class="col-sm-10">
                      <input type="text" id="confirmpassword" name="confirmpassword" class="form-control">
                    </div>
                  </div>
                
                <div class="row mb-3">
                  <div class="col-sm-10">
                    <button type="submit" class="btn btn-primary"
                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">Submit
                    </button>
                  </div>
                </div>

                <!-- <div class="col-lg-12" id="loadstation">
                  </div> -->

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
