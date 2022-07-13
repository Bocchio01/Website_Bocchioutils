<?php

include_once "../../_setting.php";
require_once "../../_isAdmin.php";

if ($login) {
    $host = HOST_URL;
    $users = Query("SELECT nickname, email, lang FROM BWS_Users WHERE newsletter = 1");

    $new_pages = Query("SELECT CONCAT(p.name, ' - (', p.type, ')') as name_link , CONCAT('$host', t.it) as it, CONCAT('$host', t.en) as en FROM BWS_Pages AS p JOIN BWS_Translations AS t WHERE p.id_page = t.id_page AND YEAR(p.creation_date) = YEAR(CURRENT_DATE()) AND MONTH(p.creation_date) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND p.type != 'Undefined' AND p.type != 'Index' ORDER BY p.id_page");
    $updated_pages = Query("SELECT CONCAT(p.name, ' - (', p.type, ')') as name_link , CONCAT('$host', t.it) as it, CONCAT('$host', t.en) as en FROM BWS_Pages AS p JOIN BWS_Translations AS t WHERE p.id_page = t.id_page AND YEAR(p.last_modify) = YEAR(CURRENT_DATE()) AND MONTH(p.last_modify) = MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND MONTH(p.creation_date) != MONTH(CURRENT_DATE() - INTERVAL 1 MONTH) AND p.type != 'Undefined' AND p.type != 'Index' ORDER BY p.id_page");

    foreach ($lang as $l) {
        $msg_new[$l] = "";
        $msg_updated[$l] = "";
    }

    echo "New pages:\n";
    while ($row = $new_pages->fetch_array(MYSQLI_ASSOC)) {
        echo "\t" . $row['name_link'] . "\n";
        foreach ($lang as $l) $msg_new[$l] .= "<li><a href='$row[$l]'>$row[name_link]</a></li>";
    }

    echo "\nUpdated pages:\n";
    while ($row = $updated_pages->fetch_array(MYSQLI_ASSOC)) {
        echo "\t" . $row['name_link'] . "\n";
        foreach ($lang as $l) $msg_updated[$l] .= "<li><a href='$row[$l]'>$row[name_link]</a></li>";
    }


    if (($new_pages->num_rows || $updated_pages->num_rows) && $users->num_rows) {
        foreach ($lang as $l) $message[$l] = render('../template/' . $l . '/Newsletter.php', array('msg_new' => "<ul>" . $msg_new[$l] . "</ul>", 'msg_updated' => "<ul>" . $msg_updated[$l] . "</ul>"));

        echo "\nEmail address:\n";
        while ($user = $users->fetch_array(MYSQLI_ASSOC)) {
            echo "\t" . $user['email'] . "\n";
            mail($user['email'], $subject . " - Newsletter", str_replace("#Nickname#", $user['nickname'], $message[$user['lang']]), $headers);
        }
    }
}
