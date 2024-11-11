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
                                    <h5 class="card-title">Create a New User</h5>

                                    <form class="row g-3">
                                        <div class="col-md-6">
                                            <label for="lastname" class="form-label">Staff Id</label>
                                            <input type="text" id="staffid" name="staffid" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="station" class="form-label">Station</label>
                                            <select id="station" class="form-control" name="station">

                                            </select>

                                        </div>
                                        <div class="col-md-2">
                                            <label for="title" class="form-label">Title</label>
                                            <select id="title" class="form-control" name="title">
                                                <option value="Mr">Mr</option>
                                                <option value="Mrs">Miss</option>
                                                <option value="Mr">Mrs</option>
                                                <option value="Mr">Engr.</option>
                                                <option value="Mr">Capt.</option>
                                            </select>

                                        </div>
                                        <div class="col-md-5">
                                            <label for="lastname" class="form-label">Lastname</label>
                                            <input type="text" id="lastname" name="lastname" class="form-control">
                                        </div>
                                        <div class="col-md-5">
                                            <label for="firstname" class="form-label">Firstname</label>
                                            <input type="input" class="form-control" id="firstname" name="firstname">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="emailaddress" class="form-label">Email Address</label>
                                            <input type="input" class="form-control" id="emailaddress"
                                                name="emailaddress">
                                        </div>
                                        <div class="col-md-6">
                                            <label for="businessunit" class="form-label">Business Unit</label>
                                            <select id="businessunit" class="form-control" name="businessunit" onchange="return loaddepartment()">

                                            </select>

                                        </div>
                                        <div class="col-md-6">
                                            <label for="department" class="form-label">Department</label>
                                            <select id="department" class="form-control" name="department" onchange="return loaddeptunit()">

                                            </select>

                                        </div>
                                        <div class="col-md-6">
                                            <label for="departunit" class="form-label">Department Unit</label>
                                            <select id="departunit" class="form-control" name="departunit">

                                            </select>

                                        </div>

                                        <!-- <div class="col-md-6">
                                            <label for="userroles" class="form-label">User Role</label>
                                            <select id="userroles" class="form-control" name="userroles"></select>
                                        </div> -->
                                        <h5 class="card-title">Select User Roles</h5>
                                        <div id="rolecheck" style="margin-top: -15px;">
                                        </div>

                                        <div class="col-sm-10">
                                            <button type="button" class="btn btn-primary"
                                                onclick=" return createusers()"
                                                style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display: block;margin: 0 auto; margin-top:20px"
                                                onmouseover="this.style.backgroundColor='#000000';"
                                                onmouseout="this.style.backgroundColor='#fc7f14';">Create
                                            </button>
                                        </div>
                                </div>

                                <div class="col-lg-12" id="loadusers">
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