<?php
/*
This file creates the connection to the database by creating
a PDO object

*/

function connectDB()
{
    $servername="db";
    $user="hp";
    $pass="hp1234";
    $dbname="hp";
    
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $user, $pass);
    
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
    return null;
}


?>