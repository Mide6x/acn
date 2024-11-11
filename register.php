<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <title>Bootstrap Tabs Example</title>
  <link href="assets/img/favicon.png" rel="icon">
  <link href="assets/img/apple-touch-icon.png" rel="apple-touch-icon">

  <!-- Google Fonts -->
  <link href="https://fonts.gstatic.com" rel="preconnect">
  <link
    href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700,700i|Nunito:300,300i,400,400i,600,600i,700,700i|Poppins:300,300i,400,400i,500,500i,600,600i,700,700i"
    rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/boxicons/css/boxicons.min.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.snow.css" rel="stylesheet">
  <link href="assets/vendor/quill/quill.bubble.css" rel="stylesheet">
  <link href="assets/vendor/remixicon/remixicon.css" rel="stylesheet">
  <link href="assets/vendor/simple-datatables/style.css" rel="stylesheet">
  <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

  <!-- Template Main CSS File -->
  <link href="assets/css/style.css" rel="stylesheet">
  <script src="assets/js/ac.js"></script>
</head>

<body class="toggle-sidebar">
  <header id="header" class="header fixed-top d-flex align-items-center">

    <div class="d-flex align-items-center justify-content-between">
      <a href="index.html" class="logo d-flex align-items-center">
        <img src="https://flyaero.com/asset/img/logo.png" style="width: 150px; height: 100px;" alt="">
        <span class="d-none d-lg-block"></span>
      </a>
      <!-- <i class="bi bi-list toggle-sidebar-btn"></i> -->
    </div><!-- End Logo -->

    <div class="search-bar">
      <form class="search-form d-flex align-items-center" method="POST" action="#">
        <span id="pagetitle" style="font-weight: bold; font-size: larger;"></span>
      </form>
    </div><!-- End Search Bar -->

    <nav class="header-nav ms-auto">
      <ul class="d-flex align-items-center">

        <li class="nav-item d-block d-lg-none">
          <a class="nav-link nav-icon search-bar-toggle " href="#">
            <i class="bi bi-search"></i>
          </a>
        </li><!-- End Search Icon-->

        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-bell"></i>
            <span class="badge bg-primary badge-number">4</span>
          </a><!-- End Notification Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow notifications">
            <li class="dropdown-header">
              You have 4 new notifications
              <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-exclamation-circle text-warning"></i>
              <div>
                <h4>Lorem Ipsum</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>30 min. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-x-circle text-danger"></i>
              <div>
                <h4>Atque rerum nesciunt</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>1 hr. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-check-circle text-success"></i>
              <div>
                <h4>Sit rerum fuga</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>2 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="notification-item">
              <i class="bi bi-info-circle text-primary"></i>
              <div>
                <h4>Dicta reprehenderit</h4>
                <p>Quae dolorem earum veritatis oditseno</p>
                <p>4 hrs. ago</p>
              </div>
            </li>

            <li>
              <hr class="dropdown-divider">
            </li>
            <li class="dropdown-footer">
              <a href="#">Show all notifications</a>
            </li>

          </ul><!-- End Notification Dropdown Items -->

        </li><!-- End Notification Nav -->

        <li class="nav-item dropdown">

          <a class="nav-link nav-icon" href="#" data-bs-toggle="dropdown">
            <i class="bi bi-chat-left-text"></i>
            <span class="badge bg-success badge-number">3</span>
          </a><!-- End Messages Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow messages">
            <li class="dropdown-header">
              You have 3 new messages
              <a href="#"><span class="badge rounded-pill bg-primary p-2 ms-2">View all</span></a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-1.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>Maria Hudson</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>4 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-2.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>Anna Nelson</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>6 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="message-item">
              <a href="#">
                <img src="assets/img/messages-3.jpg" alt="" class="rounded-circle">
                <div>
                  <h4>David Muldon</h4>
                  <p>Velit asperiores et ducimus soluta repudiandae labore officia est ut...</p>
                  <p>8 hrs. ago</p>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li class="dropdown-footer">
              <a href="#">Show all messages</a>
            </li>

          </ul><!-- End Messages Dropdown Items -->

        </li><!-- End Messages Nav -->

        <li class="nav-item dropdown pe-3">

          <a class="nav-link nav-profile d-flex align-items-center pe-0" href="#" data-bs-toggle="dropdown">
            <img src="assets/img/profile-img.jpg" alt="Profile" class="rounded-circle">
            <span class="d-none d-md-block dropdown-toggle ps-2">K. Anderson</span>
          </a><!-- End Profile Iamge Icon -->

          <ul class="dropdown-menu dropdown-menu-end dropdown-menu-arrow profile">
            <li class="dropdown-header">
              <h6>Kevin Anderson</h6>
              <span>Web Designer</span>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-person"></i>
                <span>My Profile</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="users-profile.html">
                <i class="bi bi-gear"></i>
                <span>Account Settings</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="pages-faq.html">
                <i class="bi bi-question-circle"></i>
                <span>Need Help?</span>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>

            <li>
              <a class="dropdown-item d-flex align-items-center" href="#">
                <i class="bi bi-box-arrow-right"></i>
                <span>Sign Out</span>
              </a>
            </li>

          </ul><!-- End Profile Dropdown Items -->
        </li><!-- End Profile Nav -->

      </ul>
    </nav><!-- End Icons Navigation -->

  </header>
  <main id="main" class="main">
    <section class="section profile">
      <div class="card">
        <div class="card-body" style="margin-top: 1rem !important;">
          <span style="font-size: large;font-weight: bold; margin-right:0.5rem">PROFILE
        </div>
      </div>
      <form id="biodata" style="display:none;">
        <div class="row">

          <div class="col-xl-2">
            <div class="card">
              <div class="card-body" style="margin-top: 1rem !important; text-align:center;">
                <label for="uploadgovid" class="form-label">Upload Picture</label>
                <img src="assets/img/dimage.png" alt="Thumbnail" style="width: 150px; height:auto;border: 0.5px solid #ddd; border-radius:4px; padding: 4px; box-shadow:2px 2px 8px rgba(0,0,0,0.1)">
                <p></p>
                <input class="form-control" type="file" id="uploadgovid" name="uploadgovid">
              </div>
            </div>
          </div>
          <div class="col-xl-10">
            <div class="card">
              <div class="card-body" style="margin-top: 1rem !important;">
                <hr style="border: 1px solid black;">
                </span><span style="margin-top: 1rem !important;font-size: 15px;font-weight: bold;">Personal Information</span>
                <hr style="border: 1px solid black;">

                <div class="row mb-3" style="margin-top: 2rem !important;">
                  <div class="col-md-6">
                    <label for="staffid" class="form-label">Staff ID</label>
                    <input type="text" class="form-control" id="staffid" name="staffid">
                  </div>
                  <div class="col-md-6">
                    <label for="btitle" class="form-label">Title</label>
                    <select class="form-select" id="btitle" name="btitle">
                      <option selected disabled value>Select</option>
                      <option value="Capt.">Capt.</option>
                      <option value="Mr">Mr</option>
                      <option value="Mrs">Mrs</option>
                      <option value="Miss">Miss</option>
                    </select>
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="bsurname" class="form-label">Surname</label>
                    <input type="text" class="form-control" id="bsurname" name="bsurname">
                  </div>
                  <div class="col-md-6">
                    <label for="bfirstname" class="form-label">First Name</label>
                    <input type="text" class="form-control" id="bfirstname" name="bfirstname">
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="bmiddlename" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="bmiddlename" name="bmiddlename">
                  </div>
                  <div class="col-md-6">
                    <label for="bmaidenname" class="form-label">Maiden Name</label>
                    <input type="text" class="form-control" id="bmaidenname" name="bmaidenname">
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="bgender" class="form-label">Gender</label>
                    <select class="form-select" id="bgender" name="bgender">
                      <option selected disabled value>Select</option>
                      <option value="Male">Male</option>
                      <option value="Female">Female</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="bdob" class="form-label">Date Of Birth</label>
                    <input type="date" class="form-control" id="bdob" name="bdob">
                  </div>

                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="bemailaddress" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="bemailaddress" name="bemailaddress">
                  </div>
                  <div class="col-md-6">
                    <label for="bphonenumber" class="form-label">Phone Number</label>
                    <input type="tel" class="form-control" id="bphonenumber" name="bphonenumber">
                  </div>

                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="bmstatus" class="form-label">Marital Status</label>
                    <select class="form-select" id="bmstatus" name="bmstatus">
                      <option selected disabled value>Select</option>
                      <option value="Single">Single</option>
                      <option value="Married">Married</option>
                      <option value="Divorced">Divorced</option>
                      <option value="Separated">Separated</option>
                      <option value="Widowed">Widowed</option>
                      <option value="Single Parent">Single Parent</option>
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="breligion" class="form-label">Religion</label>
                    <select class="form-select" id="breligion" name="breligion">
                      <option selected disabled value>Select</option>
                      <option value="Christian">Christian</option>
                      <option value="Muslim">Muslim</option>
                      <option value="Others">Others</option>
                    </select>
                  </div>

                </div>

                <div class="row mb-3">

                  <div class="col-md-6">
                    <label for="bnationality" class="form-label">Nationality</label>
                    <select class="form-select" id="bnationality" name="bnationality" onchange="return loadstate()">
                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="bstateoforigin" class="form-label">State of Origin</label>
                    <select class="form-select" id="bstateoforigin" name="bstateoforigin" onchange="return loadlga()">
                    </select>
                  </div>

                </div>

                <div class="row mb-3">

                  <div class="col-md-6">
                    <label for="blga" class="form-label">LGA</label>
                    <select class="form-select" id="blga" name="blga">

                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="blanguage" class="form-label">Language</label>
                    <input type="text" class="form-control" id="blanguage" name="blanguage">
                  </div>
                </div>

                <div style="margin-bottom: 2rem !important;"></div>
                <hr style="border: 1px solid black;">
                <span style="font-size: 15px;font-weight: bold;">Contact Information</span>
                <hr style="border: 1px solid black;">
                <div class="row mb-3">

                  <div class="col-md-6">
                    <label for="bcountryofres" class="form-label">Country of Residence</label>
                    <select class="form-select" id="bcountryofres" name="bcountryofres" onchange="return loadstate()">

                    </select>
                  </div>
                  <div class="col-md-6">
                    <label for="bstateofres" class="form-label">State of Residence</label>
                    <select class="form-select" id="bstateofres" name="bstateofres">
                    </select>
                  </div>
                </div>
                <div class="row mb-3">
                  <label for="braddress" class="col-md-6 form-label">Residential Address</label>
                  <div class="col-md-12">
                    <textarea class="form-control" style="height: 100px" id="braddress" name="braddress"></textarea>
                  </div>
                </div>



                <!-- <div class="row mb-3">
                  <div class="col-md-4">
                    <label for="bgovname" class="form-label">Gov. Recognized ID</label>
                    <select class="form-select" id="bgovname" name="bgovname">
                      <option selected disabled value="">Select</option>
                      <option value="nin">National Identity Card (NIN)</option>
                      <option value="pvc">Permanent Voter's Card (PVC)</option>
                      <option value="License">Driver's License</option>
                      <option value="License">Driver's License</option>
                    </select>
                  </div>
                  <div class="col-md-4">
                    <label for="bgovidno" class="form-label">ID Number</label>
                    <input type="text" class="form-control" id="bgovidno" name="bgovidno">
                  </div>
                  <div class="col-md-4">
                    <label for="uploadgovid" class="form-label">Upload ID</label>
                    <input class="form-control" type="file" id="uploadgovid" name="uploadgovid">
                  </div>
                </div> -->

                <div class="row mb-3" style="margin-top: 40px;">
                  <div class="col-sm-12" style="text-align: center;">
                    <!-- <button type="button" class="btn btn-primary" onclick="savechange()"
                      style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px"
                      onmouseover="this.style.backgroundColor='#000000';"
                      onmouseout="this.style.backgroundColor='#fc7f14';">
                      SAVE CHANGE
                    </button> -->
                    <button type="button" class="btn btn-primary" onclick="return createbiodata()"
                      style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px"
                      onmouseover="this.style.backgroundColor='#000000';"
                      onmouseout="this.style.backgroundColor='#fc7f14';">
                      NEXT
                    </button>
                  </div>
                </div>
      </form>
    </section>
    <section class="section profile">
      <form id="familydata" style="display: none;">

        <div class="card">
          <div class="card-body" style="margin-top: 1rem !important;">
            <hr style="border: 1px solid black;">
            </span><span style="margin-top: 1rem !important;font-size: 15px;font-weight: bold;">Family Data (Note: You are only allowed to add your Spouse and 4 Children)</span>
            <hr style="border: 1px solid black;">
            <h4 style="margin-bottom: 20px"></h4>
            <div class="row mb-3">

              <div class="col-md-6">
                <label for="fname" class="form-label">Surname</label>
                <input type="text" class="form-control" id="fsname" name="fsname">
              </div>
              <div class="col-md-6">
                <label for="fname" class="form-label">Firstname</label>
                <input type="text" class="form-control" id="ffname" name="ffname">
              </div>

            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="frelationship" class="form-label">Relationship</label>
                <select class="form-select" id="frelationship" name="frelationship">
                  <option selected disabled value="">Select</option>
                  <option value="Husband">Husband</option>
                  <option value="Wife">Wife</option>
                  <option value="Son">Son</option>
                  <option value="Daughter">Daughter</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="fdob" class="form-label">Date Of Birth</label>
                <input type="date" class="form-control" id="fdob" name="fdob">
              </div>

            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="foccupation" class="form-label">Occupation</label>
                <input type="text" class="form-control" id="foccupation" name="foccupation">
              </div>
              <div class="col-md-6">
                <label for="fphonenumber" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="fphonenumber" name="fphonenumber">
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-sm-12" style="text-align: center;">
                <button type="button" class="btn btn-primary" onclick="createfamilydata()"
                  style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px"
                  onmouseover="this.style.backgroundColor='#000000';"
                  onmouseout="this.style.backgroundColor='#fc7f14';">
                  Add Family
                </button>
              </div>
            </div>
            <div id="familytable">
              <table></table>
            </div>
            <div class="row mb-3" style="margin-top: 70px;">
              <div class="col-sm-12 d-flex justify-content-between">
                <button type="button" class="btn btn-primary" onclick="return completefamdata()"
                  style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px"
                  onmouseover="this.style.backgroundColor='#000000';"
                  onmouseout="this.style.backgroundColor='#fc7f14';">
                  NEXT
                </button>
              </div>
            </div>
          </div>
        </div>

      </form>
    </section>
    <section class="section profile">
      <form id="nokdata" style="display: None;">
        <div class="card">
          <div class="card-body" style="margin-top: 1rem !important;">

            <hr style="border: 1px solid black;">
            <span style="margin-top: 1rem !important;font-size: 15px;font-weight: bold;">Next Of Kin</span>
            <hr style="border: 1px solid black;">
            <h4 style="margin-bottom: 20px"></h4>

            <div class="row mb-3">
              <div class="col-md-6">
                <label for="nname" class="form-label">Surname</label>
                <input type="text" class="form-control" id="nsname" name="nsname">
              </div>
              <div class="col-md-6">
                <label for="nname" class="form-label">Firstname</label>
                <input type="text" class="form-control" id="nfname" name="nfname">
              </div>

            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="nrelationship" class="form-label">Relationship</label>
                <input type="text" class="form-control" id="nrelationship" name="nrelationship">
              </div>
              <div class="col-md-6">
                <label for="nphonenumber" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="nphonenumber" name="nphonenumber">
              </div>
            </div>
            <div class="row mb-3">
              <label for="nraddress" class="col-md-6 form-label">Residential Address</label>
              <div class="col-md-12">
                <textarea class="form-control" style="height: 100px" id="nraddress" name="nraddress"></textarea>
              </div>
            </div>
            <div style="margin-bottom: 2rem !important;"></div>
            <hr style="border: 1px solid black;">
            </span><span style="margin-top: 1rem !important;font-size: 15px;font-weight: bold;">Beneficiary</span>
            <hr style="border: 1px solid black;">
            <h4 style="margin-bottom: 20px"></h4>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="benname" class="form-label">Surname</label>
                <input type="text" class="form-control" id="bensname" name="bensname">
              </div>
              <div class="col-md-6">
                <label for="benname" class="form-label">Firstname</label>
                <input type="text" class="form-control" id="benfname" name="benfname">
              </div>

            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="benrelationship" class="form-label">Relationship</label>
                <input type="text" class="form-control" id="benrelationship" name="benrelationship">
              </div>
              <div class="col-md-6">
                <label for="benphonenumber" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="benphonenumber" name="benphonenumber">
              </div>
            </div>
            <div class="row mb-3">
              <label for="benaddress" class="form-label">Residential Address</label>
              <div class="col-md-12">
                <textarea class="form-control" style="height: 100px" id="benaddress" name="benaddress"></textarea>
              </div>
            </div>
            <div style="margin-bottom: 2rem !important;"></div>
            <hr style="border: 1px solid black;">
            </span><span style="margin-top: 1rem !important;font-size: 15px;font-weight: bold;">Pension/Tax Details</span>
            <hr style="border: 1px solid black;">
            <h4 style="margin-bottom: 20px"></h4>
            <div class="row mb-3">
              <div class="col-md-4">
                <label for="porganisation" class="form-label">Organisation(Pension)</label>
                <select class="form-select" id="porganisation" name="porganisation">
                  <option selected disabled value="">Select</option>
                  <option value="H">H</option>
                </select>
              </div>
              <div class="col-md-4">
                <label for="pid" class="form-label">Pension ID</label>
                <input type="text" class="form-control" id="pid" name="pid">
              </div>
              <div class="col-md-4">
                <label for="tid" class="form-label">Tax ID</label>
                <input type="text" class="form-control" id="tid" name="tid">
              </div>
            </div>

      </form>
      <div class="row mb-3" style="margin-top: 70px; text-align:right">
        <div class="col-md-12" style="text-align:right">
          <button type="button" class="btn btn-primary" onclick="return createnok()"
            style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px"
            onmouseover="this.style.backgroundColor='#000000';"
            onmouseout="this.style.backgroundColor='#fc7f14';">
            NEXT
          </button>
        </div>
      </div>
      </div>
      </div>
    </section>
    <section class="section profile">
      <form id="employmentdata" style="display: none;">
        <div class="card">
          <div class="card-body" style="margin-top: 1rem !important;">
            <hr style="border: 1px solid black;">
            <span style="margin-top: 1rem !important;font-size: 15px;font-weight: bold;">Employment History</span>
            <hr style="border: 1px solid black;">
            <h4 style="margin-bottom: 20px"></h4>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="emname" class="form-label">Organisation</label>
                <input type="text" class="form-control" id="emname" name="emname">
              </div>
              <div class="col-md-6">
                <label for="emdesignation" class="form-label">Designation</label>
                <input type="text" class="form-control" id="emdesignation" name="emdesignation">
              </div>
            </div>

            <!-- <div class="row mb-3">
              <label for="emaddress" class="col-md-6 col-form-label">Address</label>
              <div class="col-md-12">
                <textarea class="form-control" style="height: 100px" id="emaddress" name="emaddress"></textarea>
              </div>
            </div> -->

            <div class="row mb-3">
              <div class="col-md-6">
                <label for="emfdate" class="form-label">From</label>
                <input type="date" class="form-control" id="emfdate" name="emfdate">
              </div>
              <div class="col-md-6">
                <label for="emtdate" class="form-label">To</label>
                <input type="date" class="form-control" id="emtdate" name="emtdate">
              </div>
            </div>

            <div class="row mb-3" style="margin-top: 20px;">
              <div class="col-sm-12" style="text-align: center;">
                <button type="button" class="btn btn-primary" onclick="return createmp()"
                  style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px"
                  onmouseover="this.style.backgroundColor='#000000';"
                  onmouseout="this.style.backgroundColor='#fc7f14';">
                  Add Employment
                </button>
              </div>
              <!-- <div id="employmenttable"></div> -->
              <div style="margin-bottom: 2rem !important;"></div>
              <hr style="border: 1px solid black;">
              <span style="margin-bottom: 1rem !important;font-size: 15px;font-weight: bold;">Educational Information</span>
              <hr style="border: 1px solid black;">
              <h4 style="margin-bottom: 20px"></h4>
              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="edtype" class="form-label">Education Type</label>
                  <select class="form-select" id="edtype" name="edtype">
                    <option selected disabled value="">Select</option>
                    <option value="A">A</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="edinstitution" class="form-label">Name of institution</label>
                  <input type="text" class="form-control" id="edinstitution" name="edinstitution">
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="edfdate" class="form-label">From</label>
                  <input type="date" class="form-control" id="edfdate" name="edfdate">
                </div>
                <div class="col-md-6">
                  <label for="edtdate" class="form-label">To</label>
                  <input type="date" class="form-control" id="edtdate" name="edtdate">
                </div>
              </div>

              <div class="row mb-3">
                <div class="col-md-6">
                  <label for="eddegree" class="form-label">Degree Type</label>
                  <select class="form-select" id="eddegree" name="eddegree">
                    <option selected disabled value="">Select</option>
                    <option value="A">A</option>
                  </select>
                </div>
                <div class="col-md-6">
                  <label for="edgrade" class="form-label">Grade</label>
                  <input type="text" class="form-control" id="edgrade" name="edgrade">
                </div>
              </div>

              <div class="row mb-3" style="margin-top: 20px;">
                <div class="col-sm-12" style="text-align: center;">
                  <button type="button" class="btn btn-primary" onclick="return createmp()"
                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">
                    NEXT
                  </button>
                </div>
              </div>
      </form>
      </div>
      </div>
    </section>
    <section class="section profile">
      <form id="certificatetdata" style="display: block;">
        <div class="card">
          <div class="card-body" style="margin-top: 1rem !important;">
            <hr style="border: 1px solid black;">
            </span><span style="margin-top: 1rem !important;font-size: 15px;font-weight: bold;">Certification</span>
            <hr style="border: 1px solid black;">
            <h4 style="margin-bottom: 20px"></h4>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="cerinstitution" class="form-label">Name of institution</label>
                <input type="text" class="form-control" id="cerinstitution" name="cerinstitution">
              </div>
              <div class="col-md-6">
                <label for="cercourse" class="form-label">Course</label>
                <input type="text" class="form-control" id="cercourse" name="cercourse">
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="cerdate" class="form-label">Date issued</label>
                <input type="date" class="form-control" id="cerdate" name="cerdate">
              </div>
              <div class="col-md-6">
                <label for="cerexpiry" class="form-label">Expiry Date (if applicable)</label>
                <input type="date" class="form-control" id="cerexpiry" name="cerexpiry">
              </div>
            </div>
            <div class="row mb-3">
              <div class="col-md-6">
                <label for="cerupload" class="form-label">Upload Certificate</label>
                <input class="form-control" type="file" id="cerupload" name="cerupload">
              </div>
            </div>
            <div class="row mb-3" style="margin-top: 20px;">
              <div class="col-sm-12" style="text-align: center;">
                <button type="button" class="btn btn-primary" onclick="return createcertificate()"
                  style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px"
                  onmouseover="this.style.backgroundColor='#000000';"
                  onmouseout="this.style.backgroundColor='#fc7f14';">
                  Add Certificate
                </button>
              </div>
              <div id="certificatetable"></div>
              <div style="margin-bottom: 2rem !important;"></div>
              <hr style="border: 1px solid black;">
              </span><span style="margin-bottom: 1rem !important;font-size: 15px;font-weight: bold;">Training Information</span>
              <hr style="border: 1px solid black;">
              <h4 style="margin-bottom: 20px"></h4>
              <form id="trainingdetail" style="display: none;">
                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="traname" class="form-label">Name of institution</label>
                    <input type="text" class="form-control" id="traname" name="traname">
                  </div>
              
                  <div class="col-md-6">
                    <label for="tracourse" class="form-label">Course</label>
                    <input type="text" class="form-control" id="tracourse" name="tracourse">
                  </div>
                </div>

                <div class="row mb-3">
                  <div class="col-md-6">
                    <label for="tradate" class="form-label">Date</label>
                    <input type="date" class="form-control" id="tradate" name="tradate">
                  </div>
                  <div class="col-md-6">
                    <label for="traupload" class="form-label">Upload Certificate</label>
                    <input class="form-control" type="file" id="traupload" name="traupload">
                  </div>
                </div>
              </form>
              <div class="row mb-3" style="margin-top: 20px;">
                <div class="col-sm-12" style="text-align: center;">
                  <button type="button" class="btn btn-primary" onclick="return createmp()"
                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px"
                    onmouseover="this.style.backgroundColor='#000000';"
                    onmouseout="this.style.backgroundColor='#fc7f14';">
                    NEXT
                  </button>
                </div>
              </div>
      </form>
      </div>
      </div>
    </section>
  </main> <!-- End main -->
  <?php
  include "footer";
  ?>