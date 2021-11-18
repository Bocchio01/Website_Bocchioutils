<?php

include "setting.php";

$email = $_GET["email"];

$sql = "UPDATE Utenti SET verificato='1' WHERE email='$email'";

if($conn->query($sql)) {
    echo "Status request 1";
};

$conn->close();

