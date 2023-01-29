<?php

include_once '_functions.php';

if (isset($_GET['submit'])) {
    $nToken = $_GET['nToken'];
    for ($i = 0; $i < $nToken; $i++) {
        echo CreateToken() . '<br>';
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Geneate Tokens</title>
</head>

<body>

    <form action="" method="get">
        <label for="nToken">Numero di token</label>
        <input type="number" name="nToken" id="nToken">
        <input type="submit" name="submit" id="submit" value="Generate">
    </form>

</body>

</html>