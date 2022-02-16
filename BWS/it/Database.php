<?php

require_once "../_utils/_database.php";

?>

<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="author" content="Tommaso Bocchietti">
    <meta name="robots" content="noindex">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bocchio's WebSite Tables</title>
    <style>
        @import url("../../style.css");

        table {
            border-collapse: collapse;
            width: 90%;
            margin: auto;
            overflow-x: hidden;
        }

        th,
        td {
            text-align: left;
            padding: 8px;
            white-space: nowrap;
        }

        th {
            background-color: orange;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: white;
        }

        main {
            text-align: center;
            /* overflow: auto; */
            display: block !important;
        }
    </style>
</head>

<body>

    <header>
        <div>
            <h1><a href="./">Bocchio's WebSite Tables</a></h1>
            <a href="../en/"><img src="/_langflag/it.png" alt="Bandiera IT"></a>
        </div>
        <hr>
    </header>

    <main>

        <?php if ($login == 0) : ?>

            <div class="data">
                <div class="card graph">
                    <h2>Identificazione</h2>
                    <p>Per accedere a questi dati bisogna essere autorizzati.</p>
                    <form method="post">
                        <div>
                            <label for="email">Email</label>
                            <input type="email" name="email" id="email">
                            <label for="password">Password</label>
                            <input type="password" name="password" id="password">
                        </div>
                        <input type="submit" name="submit" value="Invia">
                    </form>
                </div>
            </div>

        <?php else :

            $tables = array('BWS_Users', 'BWS_Pages', 'BWS_Forum', 'BWS_Stats', 'BWS_Traduction', 'BWS_Interactions');
            foreach ($tables as $key => $table) {

                $res = Query("SELECT * FROM $table");
                echo "<table border='1'><h2>$table</h2><tr>";

                while ($fieldinfo = $res->fetch_field()) echo "<th>{$fieldinfo->name}</th>";
                echo "</tr>\n";

                while ($row = $res->fetch_array(MYSQLI_ASSOC)) {
                    echo "<tr>";
                    foreach ($row as $cell) echo "<td>$cell</td>";
                    echo "</tr>\n";
                }
                echo "</table>";
            }

        endif; ?>

    </main>

    <footer>
        <hr>
        <h2 id="copyright"></h2>
    </footer>

</body>

<script>
    document.getElementById('copyright').innerText = "Tommaso Bocchietti @ " + new Date().getFullYear();
</script>

</html>