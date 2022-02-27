<?php

include_once "../../_setting.php";
require_once "../../_isAdmin.php";

if ($login) {
    $host = HOST_URL;
    $users = Query("SELECT nickname, email, lang FROM BWS_Users WHERE newsletter = 1");

    $new_pages = Query("SELECT CONCAT(p.name, ' - (', p.type, ')') as name_link , CONCAT('$host', t.it) as it, CONCAT('$host', t.en) as en FROM BWS_Pages AS p JOIN BWS_Translations AS t WHERE p.id_page = t.id_page AND YEAR(p.creation_date) = YEAR(CURRENT_DATE()) AND MONTH(p.creation_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND p.type != 'Undefined' AND p.type != 'Index' ORDER BY p.id_page");

    if ($new_pages->num_rows && $users->num_rows) {
        foreach ($lang as $l) $message[$l] = "<ul>";

        while ($row = $new_pages->fetch_array(MYSQLI_ASSOC)) {
            echo $row['name_link'] . "\n";
            foreach ($lang as $l) $message[$l] .= "<li><a href='$row[$l]'>$row[name_link]</a></li>";
        }

        foreach ($lang as $l) $message[$l] = render('../template/' . $l . '/Newsletter.php', array('msg' => $message[$l] . "</ul>"));

        while ($user = $users->fetch_array(MYSQLI_ASSOC)) {
            echo $user['email'] . "\n";
            mail($user['email'], $subject . " - Newsletter", str_replace("#Nickname#", $user['nickname'], $message[$user['lang']]), $headers);
        }
    }
}
