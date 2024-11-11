<?php
include("header");
include("sidebar");
?>
<main id="main" class="main">

    <div class="container mt-5">
        <!-- Nav tabs -->
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="tab1" data-toggle="tab" href="#content1" role="tab">Tab 1</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab2" data-toggle="tab" href="#content2" role="tab">Tab 2</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="tab3" data-toggle="tab" href="#content3" role="tab">Tab 3</a>
            </li>
        </ul>

        <!-- Tab panes -->
        <div class="tab-content">
            <div class="tab-pane fade show active" id="content1" role="tabpanel">
                <section class="section">
                    <div class="row">
                        <section class="section">
                            <div class="row">
                                <div class="col-lg-12">

                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title">Station Details</h5>

                                            <!-- General Form Elements -->
                                            <form>
                                                <div class="row mb-3">
                                                    <label for="stationname"
                                                        class="col-sm-2 col-form-label">Station</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" id="stationname" name="stationname"
                                                            class="form-control">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <label for="stationcode" class="col-sm-2 col-form-label">Station
                                                        Code</label>
                                                    <div class="col-sm-10">
                                                        <input type="text" id="stationcode" name="stationcode"
                                                            class="form-control">
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <label for="stationtype" class="col-sm-2 col-form-label">Station
                                                        type</label>
                                                    <div class="col-sm-10">
                                                        <select class='form-control text-xs' name='stationtype'
                                                            id='stationtype'>
                                                            <option value="">Select Station Type</option>
                                                            <option value="Domestic">Domestic</option>
                                                            <option value="Regional">Regional</option>
                                                            <option Value="International">International</option>
                                                        </select>

                                                    </div>
                                                </div>
                                                <div class="row mb-3">

                                                    <label for="operationtype" class="col-sm-2 col-form-label">Operation
                                                        type</label>
                                                    <div class="col-sm-10">
                                                        <select class='form-control text-xs' name='operationtype'
                                                            id='operationtype'>
                                                            <option value="">Select Operation's Type</option>
                                                            <option value="Fixed">Fixed</option>
                                                            <option value="Rotary">Rotary</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="row mb-3">
                                                    <div class="col-sm-10">
                                                        <button type="button" class="btn btn-primary"
                                                            onclick="return createstation()"
                                                            style="background-color: #fc7f14; border: #fc7f14; padding: 10px 30px;display:block; margin: 0 auto; margin-top:20px"
                                                            onmouseover="this.style.backgroundColor='#000000';"
                                                            onmouseout="this.style.backgroundColor='#fc7f14';">ADD
                                                        </button>
                                                        <button type="button" class="btn btn-primary next-tab" onclick ="return nexttabs()">Next</button>

                                                    </div>
                                                </div>

                                                <div class="col-lg-12" id="loadstation">

                                                </div>

                                            </form><!-- End General Form Elements -->

                                        </div>
                                    </div>

                                </div>
                            </div>
                        </section>
                    </div>
                    <div class="tab-pane fade" id="content2" role="tabpanel">
                        <h3>Content 2</h3>
                        <p>This is the content of Tab 2.</p>
                        <button class="btn btn-primary next-tab">Next</button>
                    </div>
                    <div class="tab-pane fade" id="content3" role="tabpanel">
                        <h3>Content 3</h3>
                        <p>This is the content of Tab 3.</p>
                        <!-- No Next button since this is the last tab -->
                    </div>
            </div>
        </div>

        <!-- Bootstrap JS and dependencies -->

</main><!-- End #main -->
<?php
include("footer");
?>