<?php
define('DACCESS', 1);
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

// Includes
require_once '../../libraries/Database.php';
require_once '../../includes/config.php';
require_once '../../includes/defines.php';
require_once '../../includes/routes.php';

class Core{
    public $database;

    function __construct()
    {
        $this->database = new Database();
    }
}





