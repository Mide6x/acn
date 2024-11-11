<?php
include "header";
include "sidebar";
?>

  <main id="main" class="main">
    <div class="pagetitle">
      <h1>Profile</h1>
      <section class="section profile row">
        <div id="profilePage">
          <div class="row">
            <div class="col-xl-12">
              <div class="card">
                <div class="card-body">
                  <div class="container mt-5">
                    <!-- Nav tabs -->
                    <ul class="nav nav-tabs" id="myTab" role="tablist">
                      <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="true">Profile</button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="nok-tab" data-bs-toggle="tab" data-bs-target="#nok" type="button" role="tab" aria-controls="nok" aria-selected="false">Next of Kin</button>
                      </li>
                      <li class="nav-item" role="presentation">
                        <button class="nav-link" id="professional-tab" data-bs-toggle="tab" data-bs-target="#professional" type="button" role="tab" aria-controls="professional" aria-selected="false">Professional</button>
                      </li>
                    </ul>

                    <!-- Tab content -->
                    <div class="tab-content" id="myTabContent">
                      <!-- Profile Tab -->
                      <div class="tab-pane fade show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <form class="row g-3" id="profileForm" style="margin:10px">
                          <div class="row mb-3">
                            <div class="col-md-6">
                              <label for="staffid" class="form-label">Staff ID</label>
                              <input type="text" class="form-control" id="staffid" name="staffid" readonly>
                            </div>
                            <div class="col-md-6">
                              <label for="title" class="form-label">Title</label>
                              <select class="form-select" id="title" name="title">
                                <option value="">Select</option>
                                <option value="Capt.">Capt.</option>
                                <option value="Mr">Mr</option>
                                <option value="Mrs">Mrs</option>
                                <option value="Miss">Miss</option>
                              </select>
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-md-6">
                              <label for="sname" class="form-label">Surname</label>
                              <input type="text" class="form-control" id="sname" name="sname">
                            </div>
                            <div class="col-md-6">
                              <label for="fname" class="form-label">First Name</label>
                              <input type="text" class="form-control" id="fname" name="fname">
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-md-6">
                              <label for="mname" class="form-label">Middle Name</label>
                              <input type="text" class="form-control" id="mname" name="mname">
                            </div>
                            <div class="col-md-6">
                              <label for="gender" class="form-label">Gender</label>
                              <select class="form-select" id="gender" name="gender">
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                              </select>
                            </div>
                          </div>
                          <!-- Add remaining profile form fields here -->
                          <div class="row mb-3">
                            <div class="col-sm-10 text-center">
                              <button type="button" class="btn btn-primary" onclick="return createprofile()"
                                style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px"
                                onmouseover="this.style.backgroundColor='#000000';"
                                onmouseout="this.style.backgroundColor='#fc7f14';">
                                NEXT
                              </button>
                            </div>
                          </div>
                        </form>
                      </div>

                      <!-- Next of Kin Tab -->
                      <div class="tab-pane fade" id="nok" role="tabpanel" aria-labelledby="nok-tab">
                        <form id="nokForm">
                          <div class="row mb-3">
                            <div class="col-md-6">
                              <label for="noksname" class="form-label">Next of Kin Surname</label>
                              <input type="text" class="form-control" id="noksname" name="noksname">
                            </div>
                            <div class="col-md-6">
                              <label for="nokfname" class="form-label">First Name</label>
                              <input type="text" class="form-control" id="nokfname" name="nokfname">
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-md-6">
                              <label for="nokgender" class="form-label">Gender</label>
                              <select class="form-select" id="nokgender" name="nokgender">
                                <option value="">Select</option>
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                              </select>
                            </div>
                            <div class="col-md-6">
                              <label for="nokrelationship" class="form-label">Relationship</label>
                              <input type="text" class="form-control" id="nokrelationship" name="nokrelationship">
                            </div>
                          </div>
                          <!-- Add remaining Next of Kin form fields here -->
                          <div class="row mb-3">
                            <div class="col-sm-12 text-center">
                              <button type="button" class="btn btn-primary" onclick="submitNokForm()"
                                style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px"
                                onmouseover="this.style.backgroundColor='#000000';"
                                onmouseout="this.style.backgroundColor='#fc7f14';">
                                SUBMIT
                              </button>
                            </div>
                          </div>
                        </form>
                      </div>

                      <!-- Professional Tab -->
                      <div class="tab-pane fade" id="professional" role="tabpanel" aria-labelledby="professional-tab">
                        <form id="professionalForm">
                          <div class="row mb-3">
                            <div class="col-md-6">
                              <label for="profession" class="form-label">Profession</label>
                              <input type="text" class="form-control" id="profession" name="profession">
                            </div>
                            <div class="col-md-6">
                              <label for="company" class="form-label">Company Name</label>
                              <input type="text" class="form-control" id="company" name="company">
                            </div>
                          </div>
                          <div class="row mb-3">
                            <div class="col-md-6">
                              <label for="designation" class="form-label">Designation</label>
                              <input type="text" class="form-control" id="designation" name="designation">
                            </div>
                            <div class="col-md-6">
                              <label for="experience" class="form-label">Years of Experience</label>
                              <input type="number" class="form-control" id="experience" name="experience">
                            </div>
                          </div>
                          <!-- Add remaining Professional form fields here -->
                          <div class="row mb-3">
                            <div class="col-sm-12 text-center">
                              <button type="button" class="btn btn-primary" onclick="submitProfessionalForm()"
                                style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px"
                                onmouseover="this.style.backgroundColor='#000000';"
                                onmouseout="this.style.backgroundColor='#fc7f14';">
                                SUBMIT
                              </button>
                            </div>
                          </div>
                        </form>
                      </div>

                    </div>
                    <!-- End of Tabs -->

                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>


<?php include "footer"; ?>

</html>
