<?php

 class DB {

    private $connection;

    public function getConnection(){

        $this->connection = null;
    try{
        $this->connection = new PDO("mysql:Server=".DB_SERVER.";dbname=".DB_DATABASE,DB_USERNAME,DB_PASSWORD);
    }catch(PDOException $exception){
        echo "Connection failed: " . $exception->getMessage();
    }

    return $this->connection;
    }
 }

?>