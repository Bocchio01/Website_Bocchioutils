<?php
// Load the database configuration file
include_once '../php/setting.php';
$id_torneo = $_POST["id_torneo"];

// if (isset($_POST['importSubmit'])) {
//     echo "ciao";

    // Allowed mime types
    $csvMimes = array('text/x-comma-separated-values', 'text/comma-separated-values', 'application/octet-stream', 'application/vnd.ms-excel', 'application/x-csv', 'text/x-csv', 'text/csv', 'application/csv', 'application/excel', 'application/vnd.msexcel', 'text/plain');

    // Validate whether selected file is a CSV file
    if (!empty($_FILES['file']['name']) && in_array($_FILES['file']['type'], $csvMimes)) {

        // If the file is uploaded
        if (is_uploaded_file($_FILES['file']['tmp_name'])) {

            // Open uploaded CSV file with read-only mode
            $csvFile = fopen($_FILES['file']['tmp_name'], 'r');

            // Skip the first line
            fgetcsv($csvFile);

            // Parse data from CSV file line by line
            while (($line = fgetcsv($csvFile)) !== FALSE) {
                // Get row data
                $Nome_Squadra   = $line[0];
                $Capitano  = $line[1];
                $Compagno  = $line[2];

                // Check whether member already exists in the database with the same email
                // $prevQuery = "SELECT id_squadra FROM CalcioBalilla_Squadre WHERE email = '".$line[1]."'";
                // $prevResult = $conn->query($prevQuery);

                // if($prevResult->num_rows > 0){
                //     // Update member data in the database
                //     $conn->query("UPDATE CalcioBalilla_Squadre SET name = '".$name."', phone = '".$phone."', status = '".$status."', modified = NOW() WHERE email = '".$email."'");
                // }else{
                // Insert member data in the database
                $sql = "INSERT INTO CalcioBalilla_Squadre (id_torneo, Nome_Squadra, Capitano, Compagno) VALUES ('$id_torneo', '$Nome_Squadra', '$Capitano', '$Compagno')";
                // }
                if (!$conn->query($sql)) {
                    echo $conn->error;
                }
            }

            // Close opened CSV file
            fclose($csvFile);

            $return_obj->Result->Status = 1;
        } else {
            $return_obj->Result->Status = 0;
        }
    } else {
        $return_obj->Result->Status = 'invalid_file';
    }
// }

returndata($return_obj);
