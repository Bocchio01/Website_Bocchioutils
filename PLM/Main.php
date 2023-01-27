<?php

include_once "../_setting.php";


if (empty($_SERVER['HTTP_ORIGIN'])) {
    header('Content-Type: text/html; charset=utf-8');
} else {
    if (isset($_POST['data'])) $RCV = json_decode($_POST['data']);
    else {
        $RCV = (object) $_POST;
    }
}
$return_obj->Data->RCV = $RCV;


if (!empty($_POST)) {
    include_once "AlumnoAction.php";
    include_once "ProfessorAction.php";

    switch ($_POST["action"]) {

        case 'GetDetails':

            $nameTable = $RCV->nameTable;
            $id = (int) $RCV->id;

            $result = Query("SELECT Z.*
            FROM $nameTable as Z
            -- JOIN PLM_Alumni as A ON A.id_alunno = Z.id_alunno
            -- JOIN BWS_Users as U ON U.id_user = Z.id_professor
            WHERE id = $id LIMIT 1");

            if ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                if ($row['id_professor']) {
                    $row['name_professor'] = Query("SELECT Nickname FROM BWS_Users WHERE id_user = $row[id_professor]")->fetch_array(MYSQLI_ASSOC)['Nickname'];
                }
                if ($row['id_alumno']) {
                    $row['name_alumno'] = Query("SELECT name FROM PLM_Alumni WHERE id_alumno = $row[id_alumno]")->fetch_array(MYSQLI_ASSOC)['name'];
                }
                $return_obj->Data = $row;
            } else {
                die(returndata(1, "The $nameTable doesn't exist."));
            }

            break;

        default:
            break;
    }

    $conn->close();
    returndata(0, "Connection with MySQL database closed");
}

?>

<?php if (empty($_SERVER['HTTP_ORIGIN'])) : ?>
    <html>

    <form name="postform" action="" method="post" enctype="multipart/form-data">
        <table class="postarea" id="postarea">
            <tbody>
                <tr>
                    <td>Action:</td>
                    <td><input type="text" name="action"></td>
                </tr>
                <tr>
                    <td>id_professor:</td>
                    <td><input type="number" name="id_professor"></td>
                </tr>
                <tr>
                    <td>id_alumno:</td>
                    <td><input type="number" name="id_alumno"></td>
                </tr>
                <tr>
                    <td></td>
                    <td><input type="submit" value="Submit"></td>
                </tr>
            </tbody>
        </table>
    </form>

    </html>
<?php endif; ?>