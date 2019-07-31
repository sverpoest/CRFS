<?php

class Database
{
    
    const SERVERNAME = "localhost";
    const USERNAME = "id799805_main";
    const PASSWORD = "icqicq";
    const DBNAME = "id799805_main";
    
    private $conn;
    
    public function __construct() {
        // Create connection
        $this->conn = new mysqli(self::SERVERNAME, self::USERNAME, self::PASSWORD, self::DBNAME);
        // Check connection
        if ($this->conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 
    }
    
    public function __destruct() {
        $this->conn->close();
    }
    
    public function query($sql)
    {
        return $this->conn->query($sql);
    }
    
    public function prepare($sql)
    {
        return $this->conn->prepare($sql);
    }
    
    public function getInsertedId()
    {
        return mysqli_insert_id($this->conn);
    }
}
?>