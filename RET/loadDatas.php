<?php

include_once "../_setting.php";

header("Content-Type: text/html; charset=UTF-8");

$flights = Query("SELECT f.id, f.name as Flight_name, f.date, r.name as Rocket_name
FROM RET_Flights as f JOIN RET_Rockets as r WHERE f.id_Rocket= r.id
ORDER BY f.date ASC");

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["csv_file"]) && isset($_POST["submit"])) {
    $flight_id = $_POST["flight_id"];

    $file_name = $_FILES["csv_file"]["name"];
    $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);

    if (strtolower($file_extension) == "csv") {
        $csvData = array_map(function ($line) {
            return explode(";", $line);
        }, file($_FILES["csv_file"]["tmp_name"]));

        array_shift($csvData);

        foreach ($csvData as $row) {
            Query("INSERT INTO RET_Flights_Data (id_Flight, Time, Temperature, Pressure, Altitude, AccX, AccY, AccZ)
                    VALUES ($flight_id, $row[1] / 1000, $row[2], $row[3], $row[4], $row[5], $row[6], $row[7])");
        }

        $return_obj->Log[] = "CSV data has been successfully uploaded and saved to the database.";
    } else {
        $return_obj->Log[] = "Only CSV files are allowed.";
    }
}

$conn->close();
returndata(0, "Connection with MySQL database closed");
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Tommaso Bocchietti">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <title>Upload CSV and Save to Database</title>
    <style>
        @import url("../style.css");

        .graph {
            max-width: 600px;
        }

        .graph>img {
            width: 300px;
            max-width: 100%;
        }

        @media (max-width: 800px) {
            .graph>img {
                width: 200px;
            }
        }
    </style>
</head>

<body>


    <h2>Upload a CSV file</h2>
    <form method="POST" enctype="multipart/form-data" style="width: 300px;">

        <br>
        <label>Select a flight to associate data with</label>
        <select name="flight_id">
            <?php

            if ($flights->num_rows > 0) {
                while ($row = $flights->fetch_assoc()) {
                    echo "<option value=$row[id]>$row[Flight_name] - $row[Rocket_name] ($row[date])</option>";
                }
            } else {
                header("Location: createFlight.php");
            }

            ?>
        </select>

        <br>
        <label>Choose CSV File</label>
        <input type="file" name="csv_file" accept=".csv" required>

        <br>
        <br>
        <input type="submit" name="submit" value="Upload and Save">
    </form>
</body>

</html>