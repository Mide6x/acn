<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script type="text/javascript" src="assets/js/ac.js"></script>
    <title>Job Title Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .main {
            max-width: 800px;
            margin: 20px auto;
            background-color: white;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .section h2,
        .section h3 {
            color: #fc7f14;
        }

        label {
            font-weight: bold;
            display: block;
            margin: 10px 0 5px;
        }

        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 8px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        textarea {
            height: 100px;
        }

        input[type="submit"] {
            background-color: #fc7f14;
            color: white;
            border: none;
            padding: 10px 30px;
            cursor: pointer;
            border-radius: 4px;
        }

        input[type="submit"]:hover {
            background-color: #e05e00;
        }

        .form-container {
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .button-container {
            margin-top: 20px;
            text-align: center;
        }

        .small-button {
            background-color: #1E90FF;
            color: white;
            padding: 10px 20px;
            border: none;
            cursor: pointer;
            border-radius: 4px;
        }

        .small-button:hover {
            background-color: #4682B4;
        }
    </style>
</head>

<body>
    <?php
    include_once("include/config.php");


    if (!isset($revenue)) {
        $revenue = new Revenue($con);
    }

    // Check if $revenue was successfully created
    if (!$revenue) {
        echo "Revenue object not found.";
        exit; // Stop further execution if $revenue is not set
    }
    $departmentunit = $revenue->getDepartmentUnit();
    ?>

    <main id="main" class="main">
        <section class="section">
            <form id="jobTitleForm" method="POST" action="parameter/parameter.php">
                <h2>Job Title Setup</h2>

                <!-- Departmental Unit -->
                <label for="jddepartmentunit">Departmental Unit:</label>
                <select id="jddepartmentunit" name="jddepartmentunit" required>
                    <option value="">Select Departmental Unit</option>
                    <?php foreach ($departmentunit as $unit): ?>
                        <option value="<?= htmlspecialchars($unit['deptunitcode']) ?>">
                            <?= htmlspecialchars($unit['deptunitname']) ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <!-- Job Title -->
                <label for="jdtitle">Job Title:</label>
                <input type="text" id="jdtitle" name="jdtitle" required><br>

                <!-- Job Description -->
                <label for="jddescription">Job Description:</label>
                <textarea id="jddescription" name="jddescription" required></textarea><br>

                <!-- Working Relationship -->
                <label for="workrelation">Working Relationship:</label>
                <textarea id="workrelation" name="workrelation" required></textarea><br>

                <!-- Position -->
                <label for="jdposition">Position:</label>
                <select id="jdposition" name="jdposition" required>
                    <option value="">Select Position</option>
                    <?php
                    if (isset($_POST['jddepartmentunit'])) {
                        $deptunitcode = $_POST['jddepartmentunit'];
                        $positions = $revenue->getPositionsByDepartment($deptunitcode);
                        foreach ($positions as $position) {
                            echo "<option value='" . $position['id'] . "'>" . htmlspecialchars($position['poname']) . "</option>";
                        }
                    }
                    ?>
                    <!-- Job Condition -->
                    <label for="jdcondition">Job Condition:</label>
                    <input type="text" id="jdcondition" name="jdcondition" required><br>

                    <!-- Age Bracket -->
                    <label for="agebracket">Age Bracket:</label>
                    <input type="text" id="agebracket" name="agebracket" required><br>

                    <!-- Personality Specification -->
                    <label for="personspec">Personality Specification:</label>
                    <select id="personspec" name="personspec" required>
                        <option value="">Select Personality</option>
                        <option value="spec1">Specification 1</option>
                        <option value="spec2">Specification 2</option>
                    </select><br>

                    <!-- Job Status -->
                    <label for="jdstatus">Job Status:</label>
                    <input type="text" id="jdstatus" name="jdstatus" value="Active" readonly><br>

                    <!-- Created By -->
                    <label for="createdby">Created By:</label>
                    <input type="text" id="createdby" name="createdby" value="admin@example.com" readonly><br>

                    <!-- Date Created -->
                    <label for="dandt">Date Created:</label>
                    <input type="text" id="dandt" name="dandt" value="<?= date('Y-m-d H:i:s'); ?>" readonly><br>

                    <!-- Submit Button -->
                    <input type="submit" value="Submit Job Title">
            </form>

            <!-- Link to Staff Request Page -->
            <div class="button-container">
                <a href="staffrequest.php">
                    <button class="small-button">Go to Staff Request</button>
                </a>
            </div>
        </section>
    </main>

</body>

</html>