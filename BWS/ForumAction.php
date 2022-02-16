<?php

switch ($_POST["action"]) {


    case 'ForumDeletePost':
        $id_post = $RCV->selected_post->id_post;
        Query("DELETE FROM BWS_Forum WHERE id_post = $id_post ");
        ForumGetPost($RCV->url);
        break;


    case 'ForumModifyPost':
        $id_post = $RCV->selected_post->id_post;
        $message = $conn->real_escape_string(str_replace("\n", "<br />", $RCV->selected_post->message));

        Query("UPDATE BWS_Forum SET message='$message' WHERE id_post = $id_post ");
        ForumGetPost($RCV->url);
        break;


    case 'ForumAwnserPost':
        $refer = $RCV->selected_post->refer;
        $message = $conn->real_escape_string(str_replace("\n", "<br />", $RCV->new_post));
        $id_user = $RCV->id_user;

        $id_page = Query("SELECT id_page FROM BWS_Forum WHERE id_post=$refer limit 1")->fetch_array(MYSQLI_ASSOC)['id_page'];

        Query("INSERT INTO BWS_Forum (id_page, id_user, message, refer) VALUES ($id_page, $id_user, '$message', $refer)");
        ForumGetPost($RCV->url);
        break;


    case 'ForumNewPost':
        $message = $conn->real_escape_string(str_replace("\n", "<br />", $RCV->new_post));
        $id_user = $RCV->id_user;

        list($id_page, $lang, $url) =  GetIdLang($RCV->url);

        Query("INSERT INTO BWS_Forum (id_page,id_user, message) VALUES ($id_page,$id_user,'$message')");
        Query("UPDATE BWS_Forum SET refer = id_post where id_post = LAST_INSERT_ID()");
        ForumGetPost($RCV->url);
        break;


    case 'ForumGetPost':
        ForumGetPost($RCV->url);
        break;
}
