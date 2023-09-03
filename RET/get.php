<?php
include "../_setting.php";
require_once '../vendor/autoload.php';

header("Content-Type: text/html; charset=UTF-8");

$flights = Query("SELECT f.id, f.name as flight_name, f.date, r.name as rocket_name FROM RET_Flights as f JOIN RET_Rockets as r ON f.id_Rocket = r.id ORDER BY f.date DESC");

// Function to retrieve flight data for a given flight ID
function getFlightData($flightID)
{
    // Query to retrieve flight data based on flight ID
    $result = Query("SELECT * FROM RET_Flights_Data WHERE id_Flight = $flightID");

    if ($result->num_rows > 0) {
        $flightData = [];

        // Loop through each row of flight data
        while ($row = $result->fetch_assoc()) {
            $flightData[] = $row;
        }

        return $flightData;
    } else {
        return false;
    }
}

// Function to perform analysis on flight data
function analyzeFlightData($flightData)
{
    if (empty($flightData)) {
        return false;
    }

    $maxAltitude = $minAltitude = $startingAltitude = $landingAltitude = $launchingTime = $landingTime = null;

    $velocities = [];
    $accelerations = [];

    $startingIndex = null;
    $landingIndex = null;


    $threshold = 2;
    $averageAltitudeLaunching = 0;
    $averageAltitudeLanding = 0;

    for ($i = 0; $i < count($flightData); $i++) {
        $averageAltitudeLaunching += $flightData[$i]['Altitude'];
        $averageAltitude = $averageAltitudeLaunching / ($i + 1);

        if ($flightData[$i]['Altitude'] > $averageAltitude + $threshold) {
            $startingIndex = $i - 11;
            $startingAltitude = $flightData[$startingIndex]['Altitude'];
            $launchingTime = $flightData[$startingIndex]['Time'];
            break;
        }
    }

    for ($i = count($flightData) - 1, $j = 0; $i >= 0; $i--, $j++) {
        $averageAltitudeLanding += $flightData[$i]['Altitude'];
        $averageAltitude = $averageAltitudeLanding / ($j + 1);

        if ($flightData[$i]['Altitude'] > $averageAltitude + $threshold) {
            $landingIndex = $i + 11;
            $landingAltitude = $flightData[$landingIndex]['Altitude'];
            $landingTime = $flightData[$landingIndex]['Time'];
            break;
        }
    }

    for ($i = $startingIndex; $i < $landingIndex; $i++) {

        $altitude = $flightData[$i]['Altitude'];
        $time = $flightData[$i]['Time'];

        $prevAltitude = $flightData[$i - 1]['Altitude'];
        $prevTime = $flightData[$i - 1]['Time'];

        if ($altitude > $maxAltitude || $maxAltitude === null) {
            $maxAltitude = $altitude;
        }

        $altitudes[] = $altitude;
        $velocities[] = ($altitude - $prevAltitude) / ($time - $prevTime);

        if (count($velocities) >= 2) {
            $deltaVelocity = $velocities[count($velocities) - 1] - $velocities[count($velocities) - 2];
            $deltaTime = $time - $prevTime;
            $accelerations[] = $deltaVelocity / $deltaTime;
        }
    }

    $minAltitude = $flightData[$landingIndex]['Altitude'];

    return [
        'startingIndex' => $startingIndex,
        'landingIndex' => $landingIndex,
        'MaxAltitude' => (float) $maxAltitude,
        'MinAltitude' => (float) $minAltitude,
        'StartingAltitude' => (float) $startingAltitude,
        'LandingAltitude' => (float) $landingAltitude,
        'LaunchingTime' => (float) $launchingTime,
        'LandingTime' => (float) $landingTime,
        'Altitudes' => $altitudes,
        'Velocities' => $velocities,
        'Accelerations' => $accelerations,
    ];
}

function DefaultGraph($data, $type = 'linepoints')
{
    // print_r($data);
    $plot = new PHPlot(1000, 800);
    // print_r($data[0]);
    $plot->SetFailureImage(False);
    $plot->SetPrintImage(False);
    $plot->SetDataValues($data);
    $plot->SetPlotType($type);
    $plot->SetBackgroundColor('#fdfdff');
    $plot->SetTransparentColor('#fdfdff');
    $plot->SetFontGD('x_label', 3);
    $plot->SetFontGD('y_label', 3);
    $plot->SetFontGD('generic', 3);
    $plot->SetFontGD('y_title', 4);
    $plot->SetFontGD('x_title', 4);
    $plot->SetPointShapes('dot');
    $plot->SetLineStyles('solid');

    // $plot->SetXLabelType('date');
    $plot->SetXTickLabelPos('none');
    $plot->SetXTickPos('none');
    $plot->SetLineWidths(3);
    // $plot->SetYTickIncrement(1);

    $max_y = $min_y = $data[0][1];

    foreach ($data as $d) {
        $min_y = (int) min($min_y, $d[1]);
        $max_y = (int) max($max_y, $d[1]);
    }

    $plot->SetPlotAreaWorld(null, $min_y - 5, null, $max_y + 5);

    if (count($data) >= 5) {
        $plot->SetXLabelAngle(90);
    }

    return $plot;
}

// Get flight ID from user input (you can replace this with your input method)
$flightID = isset($_GET['flightID']) ? intval($_GET['flightID']) : 0;

// Retrieve flight data
$flightData = getFlightData($flightID);

if ($flightData !== false) {
    // Perform analysis on flight data
    $analysisResult = analyzeFlightData($flightData);

    if ($analysisResult !== false) {
        // Return analysis results (you can customize the format as needed)
        // $return_obj->Data = $analysisResult;

        $data = [];
        for ($i = 0; $i < count($analysisResult['Altitudes']); $i++) {
            $data[] = array($flightData[$i + $analysisResult['startingIndex']]['Time'] - $flightData[10 + $analysisResult['startingIndex']]['Time'],  $analysisResult['Altitudes'][$i]);
        }
        $graph[0] = DefaultGraph($data);
        $graph[0]->SetYTitle('Altitudine (m m.s.l.)');
        $graph[0]->DrawGraph();

        $data = [];
        for ($i = 0; $i < count($analysisResult['Velocities']); $i++) {
            $data[] = array($flightData[$i + $analysisResult['startingIndex']]['Time'] - $flightData[10 + $analysisResult['startingIndex']]['Time'],  $analysisResult['Velocities'][$i]);
        }
        $graph[1] = DefaultGraph($data);
        $graph[1]->SetYTitle('Velocita\' (m/s)');
        $graph[1]->DrawGraph();

        $data = [];
        for ($i = 0; $i < count($analysisResult['Accelerations']); $i++) {
            $data[] = array($flightData[$i + $analysisResult['startingIndex']]['Time'] - $flightData[10 + $analysisResult['startingIndex']]['Time'],  $analysisResult['Accelerations'][$i]);
        }
        $graph[2] = DefaultGraph($data);
        $graph[2]->SetYTitle('Accelerazione (m/s^2)');
        $graph[2]->DrawGraph();
    } else {
        returndata(1, "Failed to analyze flight data.");
        // exit();
    }
} else {
    returndata(1, "Flight data not found.");
    // exit();
}

$conn->close();
// returndata(0, "Connection with MySQL database closed");


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
    <title>Flight Analysis</title>
    <style>
        @import url("../style.css");

        body {
            width: 80%;
            margin: auto;
        }

        .graph {
            /* max-width: 600px; */
        }

        .graph>img {
            /* width: 300px; */
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
    <h2>Flight Analysis</h2>

    <h3>Select data set</h3>
    <form>
        <label for="flightID">Flight:</label>
        <select name="flightID">
            <?php foreach ($flights as $flight) : ?>
                <option value="<?= $flight['id']; ?>" <?= ($flight['id'] == $flightID) ? 'selected' : ''; ?>>
                    <?= $flight['flight_name']; ?> - <?= $flight['rocket_name']; ?> (<?= $flight['date']; ?>)
                </option>
            <?php endforeach; ?>
        </select>

        <input type="submit" value="Analyze">
    </form>

    <?php if (isset($analysisResult['Altitudes'])) : ?>

        <h3>Flight Stats</h3>
        <p>
            Dislivello positivo: <?php echo $analysisResult['MaxAltitude'] - $analysisResult['StartingAltitude']; ?> metri<br>
            Durata volo: <?php echo $analysisResult['LandingTime'] - $analysisResult['LaunchingTime'] - 2 * 11; ?> secondi.<br>
        </p>
        <p>
            Il razzo ha raggiunto un'altitudine massima di <?php echo $analysisResult['MaxAltitude']; ?> metri, partendo da un'altitudine di <?php echo $analysisResult['StartingAltitude']; ?> metri e atterrando a un'altitudine di <?php echo $analysisResult['LandingAltitude']; ?> metri.<br>
            La velocità massima raggiunta dal razzo è stata di <?php echo max($analysisResult['Velocities']); ?> metri al secondo.<br>
            L'accelerazione massima raggiunta dal razzo è stata di <?php echo max($analysisResult['Accelerations']); ?> metri al secondo quadrato.<br>
        </p>

        <h3>Flight Graphs</h3>
        <div class="data">
            <?php foreach ($graph as $g) : ?>
                <div class="card graph">
                    <img src="<?php echo $g->EncodeImage() ?>">
                </div>
            <?php endforeach; ?>
        </div>

        <h3>Flight Data</h3>
        <table>
            <tr>
                <th>Time</th>
                <th>Altitude</th>
                <th>Velocity</th>
                <th>Acceleration</th>
            </tr>
            <?php for ($i = 0; $i < count($analysisResult['Altitudes']) - 1; $i++) : ?>
                <tr>
                    <td><?php echo $flightData[$i + $analysisResult['startingIndex']]['Time'] - $flightData[10 + $analysisResult['startingIndex']]['Time']; ?></td>
                    <td><?php echo $analysisResult['Altitudes'][$i]; ?></td>
                    <td><?php echo $analysisResult['Velocities'][$i]; ?></td>
                    <td><?php echo $analysisResult['Accelerations'][$i]; ?></td>
                </tr>
            <?php endfor; ?>
        </table>

    <?php endif; ?>

</body>

</html>