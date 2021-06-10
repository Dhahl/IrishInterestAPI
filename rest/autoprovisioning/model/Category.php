<?php

/**
 * Class Category
 *
 * This class handles everything regarding any category requests.
 */
class Category{

    public static $initialized = false;

    /**
     * Initialize method. Basic method we use for static initialization.
     */
    private static function initialize(){
        if(self::$initialized){
            return;
        }
        self::$initialized = true;
    }

    /**
     * Get function for Category model. This function is called from request.php. When a valid request is triggered this function filters through the
     * request type and other GET parameters and builds an appropriate SQL query.
     * The function checks all GET parameters for inappropriate strings (SQL injection), if the GET parameter contains inappropriate strings the function
     * calls Validation::badRequest, which notifies the GET requester.
     * The SQL query is executed at the end, if the SQL query throws an exception the function echoes a response with the HTTP code 500.
     *
     * @param $getArray - GET parameters array
     * @param $database - Database reference
     */
    public static function get($getArray, $database)
    {
        self::initialize();

        //Initial state variables
        $query = null;
        $queryResult = null;
        $response_code = null;
        $token = $_GET['token'];

        //Selecting query based on GET parameters
        if ($getArray['id'] != '' && isset($getArray['id'])) {
            //Getting category based on ID
            if (Validation::validateInput($getArray['id'])) {
                $query = SQL_GET_CATEGORY_BY_ID . $getArray['id'];
            } else {
                //Illegal GET parameter, SQL injection attempt.
                Validation::badRequest($token);
            }
        } else {
            //Getting all categories as list
            $query = SQL_GET_CATEGORY_ALL;
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
