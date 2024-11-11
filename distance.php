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
              <h5 class="card-title">Distance</h5>

              <!-- Multi Columns Form -->
                <form class="row g-3">

                    <!-- <div class="col-md-6">
                        <label for="from" class="form-label">From</label>
                        <input type="text" class="form-control" id="from">
                        </div>
                        <div class="col-md-6">
                        <label for="to" class="form-label">To</label>
                        <input type="text" class="form-control" id="to">
                    </div> -->

                    <div class="col-md-6">
        <label for="from" class="form-label">From</label>
        <select class="form-select" aria-label="Default select example"  id="from">
            <option value="">Select</option>
            <option value="Location1">Location1</option>
            <option value="Location2">Location2</option>
            <option value="Location3">Location3</option>
            <!-- Add more options as needed -->
        </select>
    </div>
    <div class="col-md-6">
        <label for="to" class="form-label">To</label>
        <select class="form-select" aria-label="Default select example"  id="to">
            <option value="">Select</option>
            <option value="Location1">Location1</option>
            <option value="Location2">Location2</option>
            <option value="Location3">Location3</option>
            <!-- Add more options as needed -->
        </select>
    </div>



                    <div class="col-md-6">
                        <label for="distance" class="form-label">Distance</label>
                        <input type="text" class="form-control" id="distancekm" readonly>
                        </div>
                        <div class="col-md-6">
                        <label for="distance" class="form-label">Distance</label>
                        <input type="text" class="form-control" id="distancemiles" readonly>
                    </div>

                    <div class="row mb-3">
                  <div class="col-sm-10">
                    <button type="button" class="btn btn-primary" onclick="return createdistance()"
                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px" 
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';"> ADD
                    </button>
                  </div>
                </div>

                <div class="col-lg-12" id="loaddistance">
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
