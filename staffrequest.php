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
    $_SESSION['username'] = 'adewole.o@acn.aero';
    $_SESSION['staffid'] = 'O2024011';
    $_SESSION['stnames'] = 'Adewole Olumide';
    $_SESSION['deptunitcode'] = 'ICT';

    include_once("include/config.php");

    if (!isset($revenue)) {
        $revenue = new Revenue($con);
    }

    // Check if $revenue was successfully created
    if (!$revenue) {
        echo "Revenue object not found.";
        exit;
    }

    $jobtitletbl = $revenue->getJobTitles();
    $stations = $revenue->getStations();
    $stafftype = $revenue->getStaffType();

    $randomRequestID = str_pad(rand(100000, 999999), 6, "0", STR_PAD_LEFT);
    ?>
    <main id="main" class="main">
        <section class="section">
            <form id="staffRequestForm" method="POST" action="parameter/parameter.php">
                <h2>Staff Request Self Service</h2>
                <p>RequestID: 2024<?= $randomRequestID ?></p>

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

                <h3>Staff Request Per Station</h3>

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

                <!-- Hidden Request ID and User ID -->
                <input type="hidden" name="jdrequestid" value="<?= $randomRequestID ?>">
                <input type="hidden" name="createdby" value="12345"> <!-- Hardcoded User ID -->

                <div class="button-container">
                    <input type="submit" name="submit_request" value="Submit">
                    <button type="button" class="small-button" onclick="saveAsDraft()">Add</button>
                </div>
            </form>
        </section>
    </main>

    <script>
        // Save form as Draft by submitting with draft status
        function saveAsDraft() {
            // Append a hidden field to indicate draft status
            var form = document.getElementById('staffRequestForm');
            var draftField = document.createElement('input');
            draftField.type = 'hidden';
            draftField.name = 'status';
            draftField.value = 'draft';
            form.appendChild(draftField);

            form.submit();
        }

        // Optionally, you can have logic for "Submit" to add 'pending' status too
        document.querySelector('input[name="submit_request"]').addEventListener('click', function() {
            var form = document.getElementById('staffRequestForm');
            var statusField = document.createElement('input');
            statusField.type = 'hidden';
            statusField.name = 'status';
            statusField.value = 'pending';
            form.appendChild(statusField);
        });
    </script>

</body>

</html>