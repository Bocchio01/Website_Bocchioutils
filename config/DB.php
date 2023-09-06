<?php

namespace App\Config;

use PDO;
use PDOException;

class DB
{
    protected $host = 'localhost';
    protected $database = 'my_bocchioutils';
    protected $username = 'root';
    protected $password = '';

    public function __construct()
    {
    }

    public function connect()
    {
        try {
            $conn_str = "mysql:host=$this->host;dbname=$this->database";
            $pdo = new PDO($conn_str, $this->username, $this->password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            die("ERROR: Could not connect. " . $e->getMessage());
        }
    }
}
