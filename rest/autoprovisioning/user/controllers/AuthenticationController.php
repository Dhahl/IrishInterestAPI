<?php
include_once (__DIR__ . '/../../helper/Validation.php');
include_once (__DIR__ . '/../helper/JWT.php');

/**
 * Class AuthenticationController
 *
 * This class is used for any authentication or validation requests regarding the API.
 */


class AuthenticationController
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
     * This method decrypts post parameters and returns them unencrypted.
     *
     * @param $enc - encrypted post parameters
     * @return array - returns array of unencrypted post parameters
     */
    public static function decryptPostParams($enc)
    {
        self::initialize();
        return (array)JWT::decode($enc, USER_APP_SIGNATURE);
    }

    /**
     * This method encrypts a given string with server signature.
     *
     * @param $message - the string that has to be encrypted
     * @return string - the encrypted output string
     */
    public static function encryptMessage($message)
    {
        self::initialize();
        return JWT::encode($message, SERVER_SIGNATURE);
    }

    /**
     * This method decrypts a given string with the server signature.
     *
     * @param $message - the string that has to be decrypted
     * @return object - the decrypted object
     */
    public static function decryptMessage($message){
        self::initialize();
        return JWT::decode($message, SERVER_SIGNATURE);
    }

    /**
     * This method creates a user token used for login access.
     *
     * @param $userId - the user id
     * @param $timestamp - the timestamp when the token is created
     * @return string - encrypted generated unique token
     */
    public static function createUserToken($userId, $timestamp)
    {
        self::initialize();
        return self::encryptMessage($userId . $timestamp);
    }

    /**
     * This method verifies user token. If it is valid it returns true else false.
     *
     * @param $userToken - encrypted user token
     * @return boolean - boolean value representing the validity of the user token
     * @throws Exception - possible exception thrown from within DateTime constructor
     */
    public static function verifyUserToken($userToken)
    {
        self::initialize();
        $isValid = false;

        $userTokenDecrypted = self::decryptMessage($userToken);
        $userTokenDecrypted = json_encode($userTokenDecrypted);
        $userTokenDecrypted = json_decode($userTokenDecrypted);

        $date = new DateTime();
        if(($date->getTimestamp() - $userTokenDecrypted->timestamp) < 604800){
            $isValid = true;
        }
        return $isValid;
    }

    /**
     * This method is used to verify if a user account is valid. Essentially we are checking if the user exists.
     * If the user does exists return true if not return false.
     *
     * @param $email - user email
     * @param $password - user password
     * @param $database - database reference
     * @return boolean - returns a boolean value, true if user exists, false if the user does not exist
     */
    public static function isAccountValid($email, $password, $database)
    {
        self::initialize();
        $isValid = false;

        if ($email != "" && $password != "" && Validation::validateInput($email) && Validation::validateInput($password)) {
            try {
                $query = SQL_COUNT_USER_BY_EMAIL_AND_PASSWORD;
                $query = str_replace("{{USER_EMAIL}}", $email, $query);
                $query = str_replace("{{USER_PASSWORD}}", md5($password), $query);

                $database->query($query);
                $queryResult = $database->loadObject();
                if ($queryResult->test == 1) {
                    $isValid = true;
                }
            } catch (Exception $e) {
                //Exception response.
                $respBuilder = new ResponseBuilder(new Response("Error, Internal Server Error.", 500, $_GET['token']));
                $respBuilder->fire();
                exit();
            }
        }
        return $isValid;
    }
}