<?php

include "../../_setting.php";
include "../../_lib/shuttle-export-master/dumper.php";
require_once "../../_isAdmin.php";

if ($login) {

    $links = array('BWS_' . date("Y-m-d") . '_' . CreateToken(7) . '.sql', 'NOT_BWS_' . date("Y-m-d") . '_' . CreateToken(7) . '.sql');

    $dumper = Shuttle_Dumper::create(array(
        'host' => 'localhost',
        'username' => 'root',
        'password' => '',
        'db_name' => 'my_bocchioutils',
    ));

    // $dumper->dump($links[0], 'BWS_');
    $dumper->dump('../../_dumpDB/' . $links[0], 'BWS_');


    // $dumper = Shuttle_Dumper::create(array(
    //     'host' => 'localhost',
    //     'username' => 'root',
    //     'password' => '',
    //     'db_name' => 'my_bocchioutils',
    //     'exclude_tables' => array(
    //         'BWS_Pages',
    //         'BWS_Users',
    //         'BWS_Interactions',
    //         'BWS_Forum',
    //         'BWS_Traduction',
    //         'BWS_Stats',
    //     ),
    // ));

    // $dumper->dump($links[1]);

    foreach ($links as $n => $link) $links[$n] = UTILS_SITE . "/" . "_dumpDB/" . $link;

    $message = render('../template/en/DumpDB.php', array('link' => $links[0]));

    mail('tommaso.bocchietti@gmail.com', "Bocchio's WebSite - DB dump", $message, $headers);
}
