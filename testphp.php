<?php
// Array of checklist items
$checklistItems = [
    "Item 1",
    "Item 2",
    "Item 3",
    "Item 4"
];

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programmatically Generated Checklist</title>
    <!-- <style>
        .checklist {
            display: flex;
            list-style-type: none;
            padding: 0;
        }

        .checklist li {
            margin-right: 20px;
        }
    </style> -->
</head>

<body>

    <form method="POST" action="submit.php">
        <label for="menuchecklist" class="col-sm-2 col-form-label">Menus</label>\r\n <ul
            style="display: flex; list-style-type: none; padding: 0;" id="menuchecklist">\r\n "<li
                style='margin-right: 20px;'><input type='checkbox' name='1' id='1'><label for='1'>Station</label></li>
            <li style='margin-right: 20px;'><input type='checkbox' name='2' id='2'><label for='2'>Aircraft
                    Registration</label></li>
            <li style='margin-right: 20px;'><input type='checkbox' name='3' id='3'><label for='3'>Charge Type</label>
            </li>
            <li style='margin-right: 20px;'><input type='checkbox' name='4' id='4'><label for='4'>Menu Setup</label>
            </li>
            <li style='margin-right: 20px;'><input type='checkbox' name='5' id='5'><label for='5'>Flight Details</label>
            </li>"\r\n
        </ul> `
        <!-- <ul class="checklist">
            <?php
            // Loop through each item and generate the HTML
            foreach ($checklistItems as $index => $item) {
                echo "<li>";
                echo "<input type='checkbox' name='item$index' id='item$index'>";
                echo "<label for='item$index'>$item</label>";
                echo "</li>";
            }
            ?>
        </ul> -->
        <button type="submit">Submit</button>
    </form>

</body>

</html>