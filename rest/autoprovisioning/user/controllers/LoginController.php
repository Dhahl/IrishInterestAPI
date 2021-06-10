<?php

/**
 * Class LoginController
 *
 * This class handles everything regarding login requests.
 */

class LoginController
{
    public static $initialized = false;

    /**
     * Initialize method. Basic method we use for static initialization.
     */
    private static function initialize()
    {
        if (self::$initialized) {
            return;
        }
        self::$initialized = true;
    }

    /**
     * Login function called from User.php upon successful user validation. The function uses the provided data to login the user.
     *
     * @param $email - User email address
     * @param $password - User password
     * @param $database - Database reference
     * @param $token - Device request token
     */
    public static function login($email, $password, $database, $token)
    {
        self::initialize();

        $query = SQL_GET_USER_BY_EMAIL_AND_PASSWORD;
        $query = str_replace("{{USER_EMAIL}}", $email, $query);
        $query = str_replace("{{USER_PASSWORD}}", md5($password), $query);

        try {
            //Execute the query
            $database->query($query);
            $queryResult = $database->loadObjectList();

            //Encrypt data
            $enc = new stdClass();
            $enc->user = $queryResult;
            $date = new DateTime();
            $enc->userToken = AuthenticationController::createUserToken($queryResult->userId, $date->getTimestamp());
            $enc = AuthenticationController::encryptMessage($enc);

            //$enc = AuthenticationController::decryptMessage($enc);
            $respBuilder = new ResponseBuilder(new Response($enc, 200, $token));
            $respBuilder->fire();
        } catch (Exception $e) {
            //Exception response.
            $respBuilder = new ResponseBuilder(new Response("Error, Internal Server Error." . $e, 500, $token));
            $respBuilder->fire();
            exit();
        }

    }
}