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
              <h5 class="card-title">Create Notification</h5>

              <!-- General Form Elements -->
              <form>
                <div class="row mb-3">
                    <label for="notifytype" class="col-sm-2 col-form-label">Notification Type</label>
                    <div class="col-sm-10">
                        <select class='form-control text-xs' name='notifytype' id='notifytype'>
                            <option value ="">Select Notification Type</option>
                            <option value ="Authentication">Authentication</option>
                            <option value ="Profile">Profile</option>
                        </select>
                    </div>
                  </div>
                <div class="row mb-3">
                  <label for="emailsubject" class="col-sm-2 col-form-label">Email Subject</label>
                  <div class="col-sm-10">
                    <input type="text" id="emailsubject" name="emailsubject" class="form-control">
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="emailbody" class="col-sm-2 col-form-label">Email Body(HTML)</label>
                  <div class="col-sm-10">
                    <textarea id="emailbody" name="emailbody" rows="20" cols="100" class="form-control"></textarea>
                  </div>
                </div>
              
                <div class="row mb-3">
                  <div class="col-sm-10">
                    <button type="button" class="btn btn-primary" onclick="return createnotify()"
                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display:block; margin: 0 auto; margin-top:20px"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">Create</button>
                    </button>
                  </div>
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
