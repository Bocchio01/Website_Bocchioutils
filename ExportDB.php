<?php
//Inserisci qui le informazioni del database e il nome del file di backup
$mysqlDatabaseName = 'my_bocchioutils';
$mysqlUserName = 'root';
$mysqlPassword = '';
$mysqlHostName = 'localhost';
$mysqlExportPath = 'Nome-file-desiderato.sql';

//Si prega di non modificare i seguenti punti
//Esportazione del database e dell'output dello stato
$command = 'mysqldump --opt -h' . $mysqlHostName . ' -u' . $mysqlUserName . ' -p' . $mysqlPassword . ' ' . $mysqlDatabaseName . ' > ' . $mysqlExportPath;
exec($command, $output, $worked);
switch ($worked) {
    case 0:
        echo 'Il database <b>' . $mysqlDatabaseName . '</b> è stato memorizzato con successo nel seguente perscorso ' . getcwd() . '/' . $mysqlExportPath . '</b>';
        break;
    case 1:
        echo 'Si è verificato un errore durante la esportatione da <b>' . $mysqlDatabaseName . '</b> a ' . getcwd() . '/' . $mysqlExportPath . '</b>';
        break;
    case 2:
        echo 'Si è verificato un errore di esportazione, controllare le seguenti informazioni: <br/><br/><table><tr><td>MySQL Database Name:</td><td><b>' . $mysqlDatabaseName . '</b></td></tr><tr><td>MySQL User Name:</td><td><b>' . $mysqlUserName . '</b></td></tr><tr><td>MySQL Password:</td><td><b>NOTSHOWN</b></td></tr><tr><td>MySQL Host Name:</td><td><b>' . $mysqlHostName . '</b></td></tr></table>';
        break;
}
