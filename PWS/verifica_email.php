<?php

include "setting.php";

if (isset($_GET["token"])) {
    $token = $_GET["token"];
 
    if (Query($conn, "SELECT verified FROM PWS_Users WHERE (token, verified) = ('$token', 0)", $return_obj)->num_rows) {
        Query($conn, "UPDATE PWS_Users SET verified=1 WHERE token='$token'", $return_obj);
        echo ("Succesfully verified. Redirect to main Bocchio's WebSite...");
    } else {
        echo ("You have already verified your email succesfully. Redirect to main Bocchio's WebSite...");
    }
} else {
    echo "Token not valid";
}

$conn->close();
header("refresh:2;url=https://bocchionuxt.netlify.app/");
