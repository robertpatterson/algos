<?php

// rdp 3/31/2013
// database

define('ENVIRONMENT', 'i502_jos1');
define('ENVIRONMENT2', 'psychopharm');
define('PHASE', 'algo_dev');

$fileDir = 'live';
define('FILEDIR', $fileDir);

class DatabaseConnection{
    // algo database connection
    public static function algo(){
        $connect = mysqli_connect('localhost', 'i502_jos1', 's5a9m6r1aP',PHASE);
        if(!$connect){
            echo "Could not connect to mysql";
            die('Could not connect... ' . mysqli_error());
        } else {
            return $connect;
        }
    }
    
    public static function psychopharm(){
        $connect = mysqli_connect('localhost', 'i502_jos1', 's5a9m6r1aP',PHASE);
        //$connect = mysqli_connect('localhost', 'bob', 's5a9m6r1aP','psychopharm');
        if(!$connect){
            die('Could not connect... ' . mysqli_error());
        } else {
            //			mysqli_select_db(ENVIRONMENT2 . '_' . PHASE);
            //			echo "Connected successfully to psychopharm\n\n";
            return $connect;
        }
    }
    
}
?>