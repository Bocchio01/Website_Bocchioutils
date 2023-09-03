<?php

include_once "../_setting.php";

header("Content-Type: text/html; charset=UTF-8");

$rockets = Query("SELECT id, name FROM RET_Rockets");

if (isset($_POST['submit'])) {
    $flight_name = $_POST['flight_name'];
    $rocket_id = $_POST['rocket_id'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $motor_name = $_POST['motor_name'];
    $motor_mass = $_POST['motor_mass'];

    Query("INSERT INTO RET_Flights (id_Rocket, name, date, motor_name, motor_mass)
            VALUES ($rocket_id, '$flight_name', '$date $time', '$motor_name', $motor_mass)");

    $return_obj->Log[] = "Flight correctly created.";
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
    <title>Create flight</title>
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
    <h2>Create or select a flight</h2>
    <form method="POST" action="createFlight.php" style="width: 300px;">

        <label>Insert a name for the flight</label>
        <input type="text" name="flight_name" placeholder="Flight name" required>

        <br>
        <label>Select a rocket to associate the flight with</label>
        <select name="rocket_id">
            <option value="" disabled selected>Select a rocket</option>
            <?php

            if ($rockets->num_rows > 0) {
                while ($row = $rockets->fetch_assoc()) {
                    echo "<option value=$row[id]>$row[name]</option>";
                }
            } else {
                echo "No rockets found";
            }

            ?>
        </select>

        <br>
        <label>Insert data fo the flight</label>
        <input type="date" name="date" required>

        <br>
        <label>Set time of launch</label>
        <input type="time" name="time" required>

        <br>
        <label>Set motor name</label>
        <input type="text" name="motor_name" placeholder="Motor name" required>

        <br>
        <label>Set motor mass</label>
        <input type="number" name="motor_mass" placeholder="Motor mass" required>

        <br>
        <br>
        <input type="submit" name="submit" value="Create">
    </form>

</body>

</html>