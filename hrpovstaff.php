<!DOCTYPE html>
<html>

<head>
    <title>HR - Review Staff Requests</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            padding: 20px;
        }

        .request-card {
            background: #fff;
            border: 1px solid #ccc;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .approve-button,
        .decline-button {
            padding: 5px 15px;
            cursor: pointer;
        }

        .approve-button {
            background-color: #4CAF50;
            color: white;
        }

        .decline-button {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>

<body>
    <h1>HR - Review Staff Requests</h1>
    <?php
    include_once("include/config.php");

    if (!isset($revenue)) {
        $revenue = new Revenue($con);
    }

    // Fetch pending requests
    $sql = "SELECT * FROM staffrequest WHERE status = 'pending'";
    $stmt = $con->prepare($sql);
    $stmt->execute();
    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($requests as $request) {
        echo "<div class='request-card'>";
        echo "<h2>Job Title: " . htmlspecialchars($request['jdtitle']) . "</h2>";
        echo "<p>No. of Vacant Posts: " . htmlspecialchars($request['novacpost']) . "</p>";

        // Fetch sub-requests by station
        $sqlStation = "SELECT * FROM staffrequestperstation WHERE jdrequestid = ?";
        $stmtStation = $con->prepare($sqlStation);
        $stmtStation->execute([$request['jdrequestid']]);
        $subRequests = $stmtStation->fetchAll(PDO::FETCH_ASSOC);

        foreach ($subRequests as $subRequest) {
            echo "<div class='sub-request'>";
            echo "<p>Station: " . htmlspecialchars($subRequest['station']) . "</p>";
            echo "<p>Staff per Station: " . htmlspecialchars($subRequest['staffperstation']) . "</p>";
            echo "<p>Status: " . htmlspecialchars($subRequest['status']) . "</p>";
    ?>
            <div class="action-buttons">
                <form method="POST" action="hr_action.php">
                    <input type="hidden" name="subrequestid" value="<?= $subRequest['id'] ?>">
                    <button type="submit" name="action" value="approve" class="approve-button">Approve</button>
                    <button type="submit" name="action" value="decline" class="decline-button">Decline</button>
                </form>
            </div>
    <?php
            echo "</div><hr>";
        }
        echo "</div>";
    }
    ?>
</body>

</html>