<?php

function ForumGetPost($conn, $url, $return_obj)
{
    $result = Query($conn, "SELECT id_page, forum FROM PWS_Pages WHERE url='$url' limit 1", $return_obj);
    if ($result->num_rows) {
        $row = $result->fetch_array(MYSQLI_ASSOC);
        if ($row['forum']) {
            $return_obj->Data->isForum = 1;

            $posts = Query($conn, "SELECT * FROM PWS_Forum WHERE id_page=" . $row['id_page'] . " ORDER BY refer, id_post", $return_obj);
            while ($row = $posts->fetch_array(MYSQLI_ASSOC)) {
                $row += Query($conn, "SELECT PWS_Users.nickname, PWS_Users.avatar FROM PWS_Users, PWS_Forum WHERE PWS_Users.id_user = " . $row['id_user'], $return_obj)->fetch_array(MYSQLI_ASSOC);
                $return_obj->Data->Posts[] = $row;
            }
        } else {
            $return_obj->Data->isForum = 0;
        }
    } else $return_obj->Data->isForum = 0;
}

switch ($_POST["action"]) {


    case 'ForumDeletePost':
        $id_post = $RCV->selected_post->id_post;
        Query($conn, "DELETE FROM PWS_Forum WHERE id_post = $id_post ", $return_obj);
        ForumGetPost($conn, $RCV->url, $return_obj);
        break;


    case 'ForumModifyPost':
        $id_post = $RCV->selected_post->id_post;
        $message = $conn->real_escape_string(str_replace("\n", "<br />", $RCV->selected_post->message));

        Query($conn, "UPDATE PWS_Forum SET message='$message' WHERE id_post = $id_post ", $return_obj);
        ForumGetPost($conn, $RCV->url, $return_obj);
        break;


    case 'ForumAwnserPost':
        $refer = $RCV->selected_post->refer;
        $message = $conn->real_escape_string(str_replace("\n", "<br />", $RCV->new_post));
        $nickname = $RCV->nickname;

        $id_user = Query($conn, "SELECT id_user FROM PWS_Users WHERE nickname='$nickname' limit 1", $return_obj)->fetch_array(MYSQLI_ASSOC)['id_user'];
        $id_page = Query($conn, "SELECT id_page FROM PWS_Forum WHERE id_post=$refer limit 1", $return_obj)->fetch_array(MYSQLI_ASSOC)['id_page'];

        Query($conn, "INSERT INTO PWS_Forum (id_page,id_user, message, refer) VALUES ($id_page,$id_user,'$message', $refer)", $return_obj);
        ForumGetPost($conn, $RCV->url, $return_obj);
        break;


    case 'ForumNewPost':
        $message = $conn->real_escape_string(str_replace("\n", "<br />", $RCV->new_post));
        $nickname = $RCV->nickname;
        $url = $RCV->url;

        $id_page = Query($conn, "SELECT id_page FROM PWS_Pages WHERE url='$url' limit 1", $return_obj)->fetch_array(MYSQLI_ASSOC)['id_page'];
        $id_user = Query($conn, "SELECT id_user FROM PWS_Users WHERE nickname='$nickname' limit 1", $return_obj)->fetch_array(MYSQLI_ASSOC)['id_user'];


        Query($conn, "INSERT INTO PWS_Forum (id_page,id_user, message) VALUES ($id_page,$id_user,'$message')", $return_obj);
        Query($conn, "UPDATE PWS_Forum SET refer = id_post where id_post = LAST_INSERT_ID()", $return_obj);
        ForumGetPost($conn, $RCV->url, $return_obj);
        break;


    case 'ForumGetPost':
        ForumGetPost($conn, $RCV->url, $return_obj);
        break;
}
