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
              <h5 class="card-title">Create Roles</h5>

              <!-- General Form Elements -->
              <form id ="menuForm">
                <div class="row mb-3">
                  <label for="menuname" class="col-sm-2 col-form-label">Role Name</label>
                  <div class="col-sm-10">
                    <input type="text" id="rolename" name="rolename" class="form-control">
                  </div>
                </div>
                <div class="row mb-3" id ="menucheck">
                 
                </div>
                
                <div class="row mb-3">
                  <div class="col-sm-10">
                    <button type="button" class="btn btn-primary" onclick="return createroles()"
                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display:block; margin: 0 auto; margin-top:20px"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">Create
                    </button>
                  </div>
                </div>
                <div class="row mb-3" id ="loadroles">
                 
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
