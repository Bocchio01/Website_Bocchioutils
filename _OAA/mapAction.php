<?php

switch ($_POST["action"]) {

    case 'AddMap':
        $name = $RCV->name;
        $scale = $RCV->scale;
        $equidistance = $RCV->equidistance;
        $grivation = $RCV->grivation;
        $geographic_coordinates = json_encode($RCV->geographic_coordinates);
        $export_boundaries = json_encode($RCV->export_boundaries);
        $notes = $RCV->notes;
        // $author = $RCV->author;
        $author = 1;
        $private = $RCV->private;
        $map_file = $RCV->map_file;
        $imp_file = $RCV->imp_file;
        $pdf_file = $RCV->pdf_file;
        $gif_file = $RCV->gif_file;

        $result = Query("INSERT
        INTO OAA_Maps (name, scale, equidistance, grivation, geographic_coordinates, export_boundaries, notes, author, private, map_file, imp_file, pdf_file, gif_file)
        VALUES ('$name', '$scale', '$equidistance', '$grivation', '$geographic_coordinates', '$export_boundaries', '$notes', '$author', '$private', '$map_file', '$imp_file', '$pdf_file', '$gif_file')");


        $result = Query("INSERT INTO OAA_Maps (name, scale, equidistance, geographic_coordinates, notes, private, author) VALUES ('$name', '$scale', '$equidistance', '$geographic_coordinates', '$notes', '$private', '$author')");

        if ($result) {
            $result = Query("SELECT * FROM OAA_Maps WHERE id_map = '$conn->insert_id'");
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $return_obj->Data = $row;
        } else {
            die(returndata(1, "Error while adding the map"));
        }

        break;

    case 'EditMap':
        // $id_map = $RCV->id_map;
        $name = $RCV->name;
        $scale = $RCV->scale;
        $equidistance = $RCV->equidistance;
        $geographic_coordinates = $RCV->geographic_coordinates;
        $notes = $RCV->notes;
        $author = $RCV->author;
        $private = $RCV->private;

        $result = Query("UPDATE OAA_Maps SET
        name='$name',
        scale='$scale',
        equidistance='$equidistance',
        geographic_coordinates='$geographic_coordinates',
        notes='$notes',
        private='$private',
        author='$author'
        WHERE name = '$name'");

        if ($result) {
            $result = Query("SELECT * FROM OAA_Maps WHERE name = '$name'");
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $return_obj->Data = $row;
        } else {
            die(returndata(1, "Error while editing the map"));
        }

    case 'DeleteMap':
        $id_map = $RCV->id_map;

        $result = Query("DELETE FROM OAA_Maps WHERE id_map = '$id_map'");

        if ($result) {
            $return_obj->Data->id_map = $id_map;
        } else {
            die(returndata(1, "Error while deleting the map"));
        }

        break;

    case 'GetMaps':
        $result = Query("SELECT * FROM OAA_Maps");

        if ($result->num_rows) {
            $return_obj->Data = array();
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                $return_obj->Data[$row['name']] = $row;
            }
        } else {
            die(returndata(1, "No maps found"));
        }

        break;

    case 'GetMap':
        $id_map = $RCV->id_map;

        $result = Query("SELECT * FROM OAA_Maps WHERE id_map = '$id_map'");

        if ($result->num_rows) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            $return_obj->Data = $row;
        } else {
            die(returndata(1, "No map found"));
        }

        break;


    default:

        break;
}
