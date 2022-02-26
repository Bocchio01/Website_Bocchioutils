<?php

include "../php/_setting.php";

$id_torneo = $_POST["id_torneo"];
// $id_torneo = 2;


$sql = "DELETE FROM CalcioBalilla_Tabellone WHERE id_torneo = '$id_torneo'";
if (!$conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}

$sql = "SELECT * FROM CalcioBalilla_Squadre WHERE id_torneo = '$id_torneo' ORDER BY RAND()";
if (!$result = $conn->query($sql)) {
    $return_obj->MySQL_err[] = $conn->error;
    die(returndata($return_obj));
}

$i = 2;
$n = 0;
if ($result->num_rows) {
    while ($result->num_rows - $i > 0) {
        $i *= 2;
        $n++;
    }
    $i /= 2;


    // i = 4 e squadre = 6
    $n_qualifiche = ($result->num_rows) - $i;
    for ($j = 1; $j <= $n_qualifiche; $j++) {
        $row = array();
        for ($k = 0; $k < 2; $k++) {
            $row[] = $result->fetch_array(MYSQLI_ASSOC)['Nome_Squadra'];
        }
        // print_r($row);
        $sql = "INSERT INTO CalcioBalilla_Tabellone (id_torneo, Fase, Numero_Sfida, Squadra_1, Squadra_2) VALUES ('$id_torneo','Fase_0','$j','$row[0]','$row[1]')";
        if (!$conn->query($sql)) {
            $return_obj->MySQL_err[] = $conn->error;
            die(returndata($return_obj));
        }
    }


    //Adesso ho sistemato le qualifiche, fatte queste il tabellone ha il numero corretto
    for ($m = 1; $m <= $n; $m++) {
        for ($j = 1; $j <= ($result->num_rows - $n_qualifiche) / pow(2, $m); $j++) {
            $row = array();
            for ($k = 0; $k < 2; $k++) {
                if (!($get = $result->fetch_array(MYSQLI_ASSOC)['Nome_Squadra']) == "") {
                    echo $get . " ";
                    $row[] = $get;
                } else {
                    $row[] = NULL;
                }
            }
            echo print_r($row) . "<br>";
            $sql = "INSERT INTO CalcioBalilla_Tabellone (id_torneo, Fase, Numero_Sfida, Squadra_1, Squadra_2) VALUES ('$id_torneo','Fase_$m','$j'," . var_export($row[0], true) . "," . var_export($row[1], true) . ")";
            if (!$conn->query($sql)) {
                $return_obj->MySQL_err[] = $conn->error;
                die(returndata($return_obj));
            }
        }
    }
}

$return_obj->Result->Status = 1;
returndata($return_obj);
