<?php

include "../BWS/_setting.php";

$sql = "CREATE TABLE IF NOT EXISTS CalcioBalilla_Tornei (
    id_torneo int NOT NULL AUTO_INCREMENT,
    Creatore int NOT NULL,
    nome_torneo varchar(127) NOT NULL,

    PRIMARY KEY (id_torneo),
    UNIQUE (nome_torneo),
    FOREIGN KEY (Creatore) REFERENCES BWS_Users(id_user) ON DELETE NO ACTION ON UPDATE CASCADE

    ) ENGINE=InnoDB";

if (!$conn->query($sql)) {
    die($conn->error);
}
echo "CalcioBalilla_Tornei \t-> Status 0\n";

$sql = "CREATE TABLE IF NOT EXISTS CalcioBalilla_Squadre (
    id_squadra int NOT NULL AUTO_INCREMENT,
    id_torneo int NOT NULL,
    Nome_Squadra varchar(127) NOT NULL,
    Capitano varchar(127),
    Compagno varchar(127),
    Partite_giocate int DEFAULT 0,
    Percentuale_Vincite int DEFAULT 0,

    PRIMARY KEY (id_squadra),
    INDEX(Nome_Squadra),
    FOREIGN KEY (id_torneo) REFERENCES CalcioBalilla_Tornei(id_torneo) ON DELETE NO ACTION ON UPDATE CASCADE

    ) ENGINE=InnoDB";

if (!$conn->query($sql)) {
    die($conn->error);
}
echo "CalcioBalilla_Squadre \t-> Status 0\n";


$sql = "CREATE TABLE IF NOT EXISTS CalcioBalilla_Tabellone (
    id_Partita int NOT NULL AUTO_INCREMENT,
    id_torneo int NOT NULL,

    Fase varchar(127) NOT NULL,
    Numero_Sfida int NOT NULL,
    Squadra_1 int DEFAULT NULL,
    Squadra_2 int DEFAULT NULL,
    Squadra_Vincitrice int DEFAULT NULL,
    Punteggio varchar(127) DEFAULT NULL,

    PRIMARY KEY (id_Partita),
    FOREIGN KEY (id_torneo) REFERENCES CalcioBalilla_Tornei(id_torneo) ON DELETE NO ACTION ON UPDATE CASCADE,
    FOREIGN KEY (Squadra_1) REFERENCES CalcioBalilla_Squadre(id_squadra) ON DELETE NO ACTION ON UPDATE CASCADE,
    FOREIGN KEY (Squadra_2) REFERENCES CalcioBalilla_Squadre(id_squadra) ON DELETE NO ACTION ON UPDATE CASCADE,
    FOREIGN KEY (Squadra_Vincitrice) REFERENCES CalcioBalilla_Squadre(id_squadra) ON DELETE NO ACTION ON UPDATE CASCADE

    ) ENGINE=InnoDB";

if (!$conn->query($sql)) {
    die($conn->error);
}
echo "CalcioBalilla_Tabellone \t-> Status 0\n";
