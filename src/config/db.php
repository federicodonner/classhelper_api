<?php

class db
{

  // Properties - maqueta
    private $dbhost = 'localhost';
    private $dbuser = 'root';
    private $dbpass = 'root';
    private $dbname = 'amerendar_v2';

    // */
    /*
      // Properties - produccion
      private $dbhost = 'localhost';
      private $dbuser = 'amerenda_admin';
      private $dbpass = 'Amerendar!';
      private $dbname = 'amerenda_amerendar';

      // */


    public function connect()
    {
        $mysql_connect_str = "mysql:host=$this->dbhost;dbname=$this->dbname;charset=UTF8";
        $dbConnection = new PDO($mysql_connect_str, $this->dbuser, $this->dbpass);
        $dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        return $dbConnection;
    }
}
