<?php

include "../PWS/setting.php";
if (isset($_POST['data'])) $RCV = json_decode($_POST['data']);


$fasi_torneo = [
    0 => "Finale",
    1 => "Semi-Finale",
    2 => "Quarti di Finale",
    3 => "Ottavi di Finale",
    4 => "Sedicesimi di Finale",
];

switch ($_POST["action"]) {

    case 'RequestTornei':
        GetAllData($conn, 'CalcioBalilla_Tornei', $return_obj);
        break;

    case 'RequestSquadre':
        $id_torneo = $RCV->id_torneo;

        if (($result = Query($conn, "SELECT * FROM CalcioBalilla_Squadre WHERE id_torneo = $id_torneo", $return_obj))->num_rows) {
            $return_obj->Data = array();
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) $return_obj->Data[] = $row;
        } else $return_obj->Log[] = "Nella tabella selezionata non ci sono dati";

        break;

    case 'RequestTabellone':
        $id_torneo = $RCV->id_torneo;

        if (($result = Query($conn, "SELECT * FROM CalcioBalilla_Tabellone WHERE id_torneo = $id_torneo", $return_obj))->num_rows) {
            $return_obj->Data = array();
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $row['nome_Squadra_1'] = Query($conn, "SELECT Nome_Squadra FROM CalcioBalilla_Squadre WHERE id_squadra ='" . $row['Squadra_1'] . "'", $return_obj)->fetch_array(MYSQLI_ASSOC)['Nome_Squadra'];
                $row['nome_Squadra_2'] = Query($conn, "SELECT Nome_Squadra FROM CalcioBalilla_Squadre WHERE id_squadra ='" . $row['Squadra_2'] . "'", $return_obj)->fetch_array(MYSQLI_ASSOC)['Nome_Squadra'];
            }
        } else $return_obj->Log[] = "Nella tabella selezionata non ci sono dati";

        break;


    case 'UploadData':
        $id_torneo = $RCV->id_torneo;

        $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');
        if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $csvMimes) && is_uploaded_file($_FILES['file']['tmp_name'])) {
            $csvFile = fopen($_FILES['file']['tmp_name'], 'r');
            fgetcsv($csvFile);
            while (($line = fgetcsv($csvFile)) !== FALSE) {

                $Nome_Squadra   = $line[0];
                $Capitano  = $line[1];
                $Compagno  = $line[2];

                Query($conn, "INSERT INTO CalcioBalilla_Squadre (id_torneo, Nome_Squadra, Capitano, Compagno) VALUES ('$id_torneo', '$Nome_Squadra', '$Capitano', '$Compagno')", $return_obj);
            }

            fclose($csvFile);
        } else die(returndata($return_obj, 1, 'Invalid_file'));
        break;

    case 'CreateTorneo':
        $id_user = $RCV->id_user;
        $nome_torneo = $RCV->nome_torneo;

        Query($conn, "INSERT INTO CalcioBalilla_Tornei (Creatore, nome_torneo) VALUES ('$id_user','$nome_torneo')", $return_obj);
        break;

    case 'CreateTabellone':
        $id_torneo = $RCV->id_torneo;

        Query($conn, "DELETE FROM CalcioBalilla_Tabellone WHERE id_torneo = $id_torneo", $return_obj);

        $i = 2;
        $n = 1;
        if (($result = Query($conn, "SELECT * FROM CalcioBalilla_Squadre WHERE id_torneo = '$id_torneo' ORDER BY RAND()", $return_obj))->num_rows) {

            while ($result->num_rows - 2 * $i > 0) {
                $i *= 2;
                $n++;
            }

            $n_qualifiche = ($result->num_rows) - $i;
            for ($j = 1; $j <= $n_qualifiche; $j++) {
                $row = array($result->fetch_array(MYSQLI_ASSOC)['id_squadra'], $result->fetch_array(MYSQLI_ASSOC)['id_squadra']);
                Query($conn, "INSERT INTO CalcioBalilla_Tabellone (id_torneo, Fase, Numero_Sfida, Squadra_1, Squadra_2) VALUES ('$id_torneo','Qualifiche','$j','$row[0]','$row[1]')", $return_obj);
            }

            for ($m = 1; $m <= $n; $m++) {
                $fase = ($fasi_torneo[$n - $m] ? $fasi_torneo[$n - $m] : "Fase" . $m);
                for ($j = 1; $j <= ($result->num_rows - $n_qualifiche) / pow(2, $m); $j++) {
                    $row = array($result->fetch_array(MYSQLI_ASSOC)['id_squadra'], $result->fetch_array(MYSQLI_ASSOC)['id_squadra']);
                    Query($conn, "INSERT INTO CalcioBalilla_Tabellone (id_torneo, Fase, Numero_Sfida, Squadra_1, Squadra_2) VALUES ('$id_torneo','$fase','$j'," . var_export($row[0], true) . "," . var_export($row[1], true) . ")", $return_obj);
                }
            }
        }
        break;

    case 'set_result':
        // print_r($_POST);
        // $id_torneo = $_POST['id_torneo'];
        $winner = $_POST['winner'];
        $punteggio = $_POST['punteggio'];
        $id_partita = $_POST['id_partita'];
        $num_partita = $_POST['num_partita'];

        $sql = "UPDATE CalcioBalilla_Tabellone SET Squadra_Vincitrice = '$winner', Punteggio = '$punteggio' WHERE id_Partita = '$id_partita'";
        Query($conn, $sql, $return_obj);

        $fase = Query($conn, "SELECT Fase FROM CalcioBalilla_Tabellone WHERE id_Partita = '$id_partita'", $return_obj)->fetch_array(MYSQLI_ASSOC)['Fase'];

        $cont = ceil($num_partita / 2);

        $key = array_search($fase, $fasi_torneo);

        if ($key != 0) {
            $fase = $fasi_torneo[$key - 1];
            # code...
        }
        // echo ($fase);

        $sql = "SELECT * FROM CalcioBalilla_Tabellone WHERE Fase = '$fase'";


        if ($result = Query($conn, $sql, $return_obj)) {
            if ($result->num_rows) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {

                    if ($row['Numero_Sfida'] == $cont) {
                        if ($num_partita % 2 == 0) {
                            $sql = "UPDATE CalcioBalilla_Tabellone SET Squadra_2 = '$winner' WHERE id_Partita = '$row[id_Partita]'";
                        } else {
                            $sql = "UPDATE CalcioBalilla_Tabellone SET Squadra_1 = '$winner' WHERE id_Partita = '$row[id_Partita]'";
                        }
                        Query($conn, $sql, $return_obj);
                    }

                    // if ($row['Squadra_1'] == null) {
                    //     $cont--;
                    // }
                    // if ($cont == 0) {
                    //     $sql = "UPDATE CalcioBalilla_Tabellone SET Squadra_1 = '$winner' WHERE id_Partita = '$row[id_Partita]'";
                    //     Query($conn, $sql, $return_obj);
                    //     break;
                    // }
                    // if ($row['Squadra_2'] == null) {
                    //     $cont--;
                    // }
                    // if ($cont == -1) {
                    //     $sql = "UPDATE CalcioBalilla_Tabellone SET Squadra_2 = '$winner' WHERE id_Partita = '$row[id_Partita]'";
                    //     Query($conn, $sql, $return_obj);
                    //     break;
                    // }
                }
            }
        };

        break;
    default:
        break;
}

$conn->close();
returndata($return_obj, 0, "Connection with MySQL database closed");
