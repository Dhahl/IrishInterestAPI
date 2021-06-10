<?php
class Misc
{
    public static $initialized = false;

    private static function initialize()
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
    }


    public static function get($getArray, $database)
    {
        self::initialize();

        //Initial state variables
        $query = null;
        $queryResult = null;
        $response_code = null;
        $token = $_GET['token'];

        switch ($getArray['type']){
            case MISC_COUNT_ALL_BOOKS:{
                $query = SQL_COUNT_ALL_BOOKS;
            }
        }

        //Execute query
        try {
            $database->query($query);
            $queryResult = $database->loadObjectList();
            $respBuilder = new ResponseBuilder(new Response($queryResult, 200, $token));
            $respBuilder->fire();
        } catch (Exception $e) {
            //Exception response.
            $respBuilder = new ResponseBuilder(new Response("Error, Internal Server Error.", 500, $token));
            $respBuilder->fire();
            exit();
        }
    }
}