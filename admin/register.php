<?php
include "header";
include "sidebar";
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
                                    <h5 class="card-title">Create User Account</h5>

                                    <form class="row g-3">
                                        <div class="col-md-6">
                                            <label for="staffid" class="form-label">Staff ID</label>
                                            <input type="text" id="staffid" name="staffid" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Title" class="form-label">Title</label>
                                            <select type="input" class="form-control" id="title" name="title"></select>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="lastname" class="form-label">Lastname</label>
                                            <input type="text" id="lastname" class="form-control" name="lastname"></input>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="firstname" class="form-label">Firstname</label>
                                            <input type="input" id="firstname" class="form-control"
                                                name="firstname"></input>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="emailaddress" class="form-label">Email Address</label>
                                            <input type="input" id="emailaddress" class="form-control"
                                                name="emailaddress"></input>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="urole" class="form-label">Role:</label>
                                            <select id="urole" class="form-control" name="urole"></select>
                                        </div>
                                        <!-- <div class="row mb-12"> -->
                                            <div class="col-md-12">
                                                <button type="button" class="btn btn-primary"
                                                    onclick=" return createflightdetails()"
                                                    style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                                                    onmouseover="this.style.backgroundColor='#000000';"
                                                    onmouseout="this.style.backgroundColor='#fc7f14';">Register User
                                                </button>
                                            </div>
                                        <!-- </div> -->

                                        <div class="col-lg-12" id="loadregisteredusers">
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
include "footer";
?>