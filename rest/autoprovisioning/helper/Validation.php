<?php

/**
 * Class Validation
 *
 * This class is used to handle the validation of API requests.
 * Validation for API key, SQL injection prevention.
 */
class Validation{

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
     * This function is used to validate API request input and detect if it contains possible SQL injection.
     *
     * @param $queryInput - the request input string
     * @return bool - the boolean return value signifying the validity of the input request
     */
    public static function  validateInput($queryInput){
        self::initialize();
        $notAllowedCommands = array(
            'DELETE',
            'TRUNCATE',
            'DROP',
            'USE'
        );

        if(preg_match('[' . implode(' |', $notAllowedCommands ) . ']i', $queryInput) == true) {
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * This function is used to validate the API key, sent with any request.
     *
     * @param $key - the API key string given with the request
     * @return bool - the boolean return value signifying the validity of the API key
     */
    public static function validateApiKey($key){
        self::initialize();
        if($key == API_KEY){
            return true;
        } else {
            return false;
        }
    }

    /**
     * This function is called upon a bad request. It returns a response with an error code 400.
     *
     * @param $token - the token sent with the request
     */
    public static function badRequest($token){
        //Illegal GET parameter, SQL injection attempt.
        $respBuilder = new ResponseBuilder(new Response("Error, Illegal request parameter.", 400, $token));
        $respBuilder->fire();
        exit();
    }


}
