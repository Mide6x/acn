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
              <h5 class="card-title">Aviation charge Type</h5>

                <div class="row mb-3">
                  <label for="chargetype" id="chargetype" class="col-sm-2 col-form-label">Charge Type</label>
                  <div class="col-sm-10">
                    <select class="form-select" aria-label="Default select example">
                      <option selected>select</option>
                      <option value="1">NCAA</option>
                      <option value="2"></option>
                      <option value="3"></option>
                    </select>
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="body" id="body" class="col-sm-2 col-form-label">Body</label>
                  <div class="col-sm-10">
                    <select class="form-select" aria-label="Default select example">
                      <option selected>select</option>
                      <option value="1">Nacho</option>
                      <option value="2"></option>
                      <option value="3"></option>
                    </select>
                  </div>
                </div>

                <div class="row mb-3">
                  <label for="name" class="col-sm-2 col-form-label">Name</label>
                  <div class="col-sm-10">
                    <input type="text" id="name" name="name"class="form-control">
                  </div>
                </div>

                <div class="row mb-3">
                  <legend class="col-form-label col-sm-2 pt-0">If applicable</legend>
                  <div class="col-sm-10">

                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="checkseason" id="checkseason" checked>
                      <label class="form-check-label" for="gridCheck1">
                        Season
                      </label>
                    </div>

                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" name="checkaircraft"id="checkaircraft" checked>
                      <label class="form-check-label" for="gridCheck2">
                       Aircraft
                      </label>
                    </div>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-sm-10">
                    <button type="button" class="btn btn-primary" onclick="return createaviationtype()"
                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">ADD
                    </button>
                  </div>
                </div>

                <div class="col-lg-12" id="loadaviationchargestype">
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




<!-- <script>
  const form = document.querySelector('form');
  const nameInput = document.querySelector('#name');
  const seasonInput = document.querySelector('#season');
  const aircraftTypeInput = document.querySelector('#aircrafttype');
  const chargeTypeSelect = document.querySelector('select[name="chargeType"]');
  const bodySelect = document.querySelector('select[name="body"]');

  form.addEventListener('submit', (e) => {
    e.preventDefault();

    const name = nameInput.value.trim();
    const season = seasonInput.value.trim();
    const aircraftType = aircraftTypeInput.value.trim();
    const chargeType = chargeTypeSelect.value;
    const body = bodySelect.value;

    if (name === '') {
      alert('Please enter a name');
      return;
    }

    if (season === '') {
      alert('Please enter a season');
      return;
    }

    if (aircraftType === '') {
      alert('Please enter an aircraft type');
      return;
    }

    if (chargeType === 'select') {
      alert('Please select a charge type');
      return;
    }

    if (body === 'select') {
      alert('Please select a body');
      return;
    }

    // If all fields are valid, submit the form
    createaviationtype();
  });
</script> -->