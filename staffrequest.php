<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Request</title>
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

    // Fetch job titles, stations, and staff types after confirming $revenue is initialized
    $jobtitletbl = $revenue->getjobtitletbl();
    $stations = $revenue->getStations();
    $stafftype = $revenue->getStaffType();
    ?>

    <main id="main" class="main">
        <section class="section">
            <form id="staffRequestForm" method="POST" action="parameter/parameter.php">
                <!-- Staff Request Self Service -->
                <h2>Staff Request Self Service</h2>

                <!-- Job Title -->
                <label for="jdtitle">Job Title:</label>
                <select id="jdtitle" name="jdtitle" required>
                    <option value="">Select Job Title</option>
                    <?php foreach ($jobtitletbl as $title): ?>
                        <option value="<?= htmlspecialchars($title['jdtitle']) ?>"><?= htmlspecialchars($title['jdtitle']) ?></option>
                    <?php endforeach; ?>
                </select><br>

                <!-- No. of Vacant Posts -->
                <label for="novacpost">No. of Vacant Posts:</label>
                <input type="number" id="novacpost" name="novacpost" required><br>

                <!-- Reason (If same position) -->
                <label for="reason">Reason (If same position):</label>
                <textarea id="reason" name="reason" required></textarea><br>

                <h3>Job Specification</h3>

                <!-- Educational Qualification -->
                <label for="eduqualification">Educational Qualification:</label>
                <input type="text" id="eduqualification" name="eduqualification" required><br>

                <!-- Professional Qualification -->
                <label for="proqualification">Professional Qualification:</label>
                <input type="text" id="proqualification" name="proqualification" required><br>

                <h3>KEY COMPETENCIES REQUIREMENTS</h3>

                <!-- Functional/Technical Skills -->
                <label for="fuctiontech">Functional/Technical Skills:</label>
                <textarea id="fuctiontech" name="fuctiontech" required></textarea><br>

                <!-- Managerial Skills -->
                <label for="managerial">Managerial Skills:</label>
                <textarea id="managerial" name="managerial" required></textarea><br>

                <!-- Behavioral Skills -->
                <label for="behavioural">Behavioral Skills:</label>
                <textarea id="behavioural" name="behavioural" required></textarea><br>

                <h3>Key Success Factor</h3>

                <!-- Key Result Area for Unit/Departments -->
                <label for="keyresult">Key Result Area for Unit/Departments:</label>
                <input type="text" id="keyresult" name="keyresult" required><br>

                <!-- Employee Deliverables -->
                <label for="empdeliveries">Employee Deliverables:</label>
                <input type="text" id="empdeliveries" name="empdeliveries" required><br>

                <!-- Key Success Factor -->
                <label for="keysuccess">Key Success Factor:</label>
                <input type="text" id="keysuccess" name="keysuccess" required><br>

                <!-- Staff Request Per Station -->
                <h2>Staff Request Per Station</h2>

                <!-- Station -->
                <label for="station">Station:</label>
                <select id="station" name="station" required>
                    <option value="">Select Station</option>
                    <?php foreach ($stations as $station): ?>
                        <option value="<?= htmlspecialchars($station['stationcode']) ?>"><?= htmlspecialchars($station['stationname']) ?></option>
                    <?php endforeach; ?>
                </select><br>

                <!-- Employment Type -->
                <label for="employmenttype">Employment Type:</label>
                <select id="employmenttype" name="employmenttype" required>
                    <option value="">Select Employment Type</option>
                    <?php foreach ($stafftype as $type): ?>
                        <option value="<?= htmlspecialchars($type['stafftype']) ?>"><?= htmlspecialchars($type['stafftype']) ?></option>
                    <?php endforeach; ?>
                </select><br>

                <!-- Staff per Station -->
                <label for="staffperstation">Staff per Station:</label>
                <input type="number" id="staffperstation" name="staffperstation" required><br>

                <!-- Hidden Request ID -->
                <input type="hidden" name="jdrequestid" value="<?= uniqid('jdreq_') ?>">

                <!-- Submit Button -->
                <input type="submit" value="Submit">
            </form>

            <!-- Link to Job title Creation Page -->
            <div class="button-container">
                <a href="jobtitle.php">
                    <button class="small-button">Create Job Title</button>
                </a>
            </div>
        </section>
    </main>

</body>

</html>