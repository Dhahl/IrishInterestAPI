<?php

include_once(__DIR__ . '/controllers/AuthenticationController.php');

include_once(__DIR__ . '/../helper/Validation.php');

include_once(__DIR__ . '/controllers/LoginController.php');

require_once(__DIR__ . '/../../../../classes/access_user/access_user_class.php');

include_once(__DIR__ . '/UserRegistrationFactory.php');



/**

 * Class User

 *

 * This class handles everything regarding user data requests, from login, registration, etc...

 */



class User

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

     * Login user function called from request.php upon a valid triggered POST request.

     * This function checks the user validity and if valid logs in the user.

     *

     * @param $postParams - POST parameters (encrypted)

     * @param $database - Database reference

     * @throws Exception - Possible exception thrown within DateTime constructor

     */

    public static function login($postParams, $database)

    {

        self::initialize();

        $token = $postParams->token;

        $enc = $postParams->enc; //Read encrypted data



        //TODO: This is only for testing purposes. Will be removed in final version.

        if(isset($postParams->isTest) && $postParams->isTest == true){

            $enc = AuthenticationController::encryptMessage($enc);

        }

        //We have to decode the enc data

        $dec = AuthenticationController::decryptMessage($enc);

        $dec = json_encode($dec);

        $dec = json_decode($dec);

        $email = $dec->email;

        $password = $dec->password;

        $timestamp = $dec->timestamp; //This is used for validating if this request is still valid

        $now = new DateTime();

        $nowTime = $now->getTimestamp();

        if (($nowTime - $timestamp) >= 10800) {

            Validation::badRequest($token);

        } elseif (AuthenticationController::isAccountValid($email, $password, $database)) {

            LoginController::login($email, $password, $database, $token);

        }

    }



    /**

     * Register function called from request.php upon a valid triggered POST request is.

     * This function checks if the user already exists and if not it creates the user.

     *

     * @param $postParams - POST parameters (encrypted)

     * @param $database - Database reference

     */

    public static function register($postParams, $database)

    {

        self::initialize();

        $token = $postParams->token;

        $enc = $postParams->enc; //Read encrypted data



        $new_member = new UserRegistrationFactory($database);



        //We have to decode the enc data

        $enc = AuthenticationController::encryptMessage($enc);

        $dec = AuthenticationController::decryptMessage($enc);

        $dec = json_encode($dec);

        $dec = json_decode($dec);



        $firstname = $dec->firstname;

        $lastname = $dec->lastname;

        $publishername = $dec->publishername;

        $email = $dec->email;

        $telephone = $dec->telephone;

        $position = $dec->position;

        $password = $dec->password;

        $confirm = $dec->confirm;

        $userType = $dec->usertype;



        if (Validation::validateInput($firstname) && Validation::validateInput($lastname) && Validation::validateInput($publishername) && Validation::validateInput($email)

            && Validation::validateInput($telephone) && Validation::validateInput($position) && Validation::validateInput($confirm) && Validation::validateInput($userType)) {

            if (!AuthenticationController::isAccountValid($email, $password, $database)) {

                try {

                    $new_member->register_user($firstname, $password, $confirm, $firstname,

                        date("Y-m-d H:i:s"), $email, $publishername, $lastname,

                        $telephone, $position, $userType);
					

                    $respBuilder = new ResponseBuilder(new Response('Success ' . $firstname . ' ' . $password . ' ' . $confirm . ' ' . $firstname . ' ' . 

                        date("Y-m-d H:i:s") . ' ' . $email . ' ' . $publishername . ' ' . $lastname . ' ' . 

                        $telephone . ' ' . $position . ' ' . $userType .' VAR EXPORT: ' . var_export($new_member, true) , 200, $token));

                    $respBuilder->fire();

                } catch (Exception $e) {

                    $respBuilder = new ResponseBuilder(new Response('Registration error.' . ' VAR EXPORT: ' . var_export($new_member, true), 500, $token));

                    $respBuilder->fire();

                }



            } else {

                $respBuilder = new ResponseBuilder(new Response('User already registered.', 400, $token));

                $respBuilder->fire();

            }

        } else {

            Validation::badRequest($token);

        }

    }





    /**

     * This function is used to GET user favourites.

     *

     * @param $postParams - POST parameters (encrypted)

     * @param $database - Database reference

     */

    public static function getFavourites($postParams, $database){

        self::initialize();

        $token = $postParams->token;

        $enc = $postParams->enc; //Read encrypted data



        //TODO: This is only for testing purposes. Will be removed in final version.

        if(isset($postParams->isTest) && $postParams->isTest == true){

            $enc = AuthenticationController::encryptMessage($enc);

        }

        //We have to decode the enc data

        $dec = AuthenticationController::decryptMessage($enc);

        $dec = json_encode($dec);

        $dec = json_decode($dec);

        $user_id = $dec->user_id;

        $offset = $dec->offset;



        $query = "SELECT bookid FROM favourites WHERE userid = " . $user_id . " LIMIT 30 OFFSET " . $offset;



        try {

            $database->query($query);

            $queryResult = $database->loadObjectList();



            $books = array();



            //GET book by id

            foreach ($queryResult as $bookid){

                //Getting book based on book ID

                $queryBookById = SQL_GET_BOOK_BY_ID . "'" . $bookid->bookid . "'";

                $database->query($queryBookById);

                $bookQueryResult = $database->loadObjectList();

                array_push($books, $bookQueryResult[0]);

            }



            //GET author for book

            foreach($books as $queryBook){

                $getAuthorSql = SQL_GET_AUTHOR_BY_BOOK_ID;

                $getAuthorSql = str_replace("{{BOOK_ID}}", $queryBook->id, $getAuthorSql);

                $database->query($getAuthorSql);

                $authorResult = $database->loadObjectList();

                $queryBook->author = $authorResult[0]->firstname . " " . $authorResult[0]->lastname;

                $queryBook->authorid = $authorResult[0]->id;

            }







            $respBuilder = new ResponseBuilder(new Response($books, 200, $token));

            $respBuilder->fire();

        } catch (Exception $e) {

            //Exception response.

            $respBuilder = new ResponseBuilder(new Response("Error, Internal Server Error.", 500, $token));

            $respBuilder->fire();

            exit();

        }

    }



    /**

     * This function is used to add a book to user favourites.

     *

     * @param $postParams - POST parameters (encrypted)

     * @param $database - Database reference

     */

    public static function addFavourite($postParams, $database)	{

        self::initialize();

        $token = $postParams->token;

        $enc = $postParams->enc; //Read encrypted data



        //TODO: This is only for testing purposes. Will be removed in final version.

        if(isset($postParams->isTest) && $postParams->isTest == true){

            $enc = AuthenticationController::encryptMessage($enc);

        }

        //We have to decode the enc data

        $dec = AuthenticationController::decryptMessage($enc);

        $dec = json_encode($dec);

        $dec = json_decode($dec);



        $user_id = $dec->user_id;

        $book_id = $dec->book_id;

        $isCheckup = $dec->favouriteCheckup;



        //Execute query

        try {

            $sql = "SELECT * FROM favourites WHERE userid=" . $user_id . " AND bookid=" . $book_id;

            $database->query($sql);

            $queryResult = $database->loadObjectList();

            if(count($queryResult) == 0 && $isCheckup == false){

                $sql ="INSERT INTO favourites (userid, bookid) values(" . $user_id .', '. $book_id .")";

                $database->query($sql);

                $queryResult = $database->loadObjectList();

                $respBuilder = new ResponseBuilder(new Response("Success", 200, $token));

                $respBuilder->fire();

            } else if(count($queryResult) == 0 && $isCheckup == true){

                $respBuilder = new ResponseBuilder(new Response("Book is not in favourites.", 200, $token));

                $respBuilder->fire();

            } else if(count($queryResult) > 0 && $isCheckup == true){

                $respBuilder = new ResponseBuilder(new Response("Book is already in favourites.", 200, $token));

                $respBuilder->fire();

            }







        } catch (Exception $e) {

            //Exception response.

            $respBuilder = new ResponseBuilder(new Response("Error, Internal Server Error.", 500, $token));

            $respBuilder->fire();

            exit();

        }

    }



    /**

     * This function is used to remove a book from user favorites.

     *

     * @param $postParams - POST parameters (encrypted)

     * @param $database - Database reference

     */

    public static function removeFavourite($postParams, $database)	{

        self::initialize();

        $token = $postParams->token;

        $enc = $postParams->enc; //Read encrypted data



        //TODO: This is only for testing purposes. Will be removed in final version.

        if(isset($postParams->isTest) && $postParams->isTest == true){

            $enc = AuthenticationController::encryptMessage($enc);

        }

        //We have to decode the enc data

        $dec = AuthenticationController::decryptMessage($enc);

        $dec = json_encode($dec);

        $dec = json_decode($dec);



        $user_id = $dec->user_id;

        $book_id = $dec->book_id;



        try{

            $sql ="DELETE FROM favourites WHERE userid = '". $user_id ."' AND  bookid = '". $book_id ."'";

            $database->query($sql);

            $queryResult = $database->loadObjectList();



            $respBuilder = new ResponseBuilder(new Response("Success", 200, $token));

            $respBuilder->fire();

        } catch (Exception $e) {

            //Exception response.

            $respBuilder = new ResponseBuilder(new Response("Error, Internal Server Error.", 500, $token));

            $respBuilder->fire();

            exit();

        }

    }



    public static function contactUs($postParams, $database)

    {

        self::initialize();

        $token = $postParams->token;

        $enc = $postParams->enc; //Read encrypted data



        //TODO: This is only for testing purposes. Will be removed in final version.

        if (isset($postParams->isTest) && $postParams->isTest == true) {

            $enc = AuthenticationController::encryptMessage($enc);

        }

        //We have to decode the enc data

        $dec = AuthenticationController::decryptMessage($enc);

        $dec = json_encode($dec);

        $dec = json_decode($dec);



        $contactName = $dec->contactName;

        $contactEmail = $dec->contactEmail;

        $contactMessage = $dec->contactMessage;



        $header = "From: \"" . $contactName . "\" <" . $contactEmail . ">\r\n";

        $header .= "MIME-Version: 1.0\r\n";

        $header .= "Mailer: Irish Interest\r\n";

        $header .= "Content-Type: text/plain; charset=\"iso-8859-1\"\r\n";

        $header .= "Content-Transfer-Encoding: 7bit\r\n";

        $mail_address = "administrator@irishinterest.ie";

        $subject = "Contact message from " . $contactName;

        $body = $contactMessage;

        $sent = @mail($mail_address, $subject, $body, $header);



        try {

            $respBuilder = new ResponseBuilder(new Response("Success", 200, $token));

            $respBuilder->fire();

        } catch (Exception $e) {

            //Exception response.

            $respBuilder = new ResponseBuilder(new Response("Error, Internal Server Error.", 500, $token));

            $respBuilder->fire();

            exit();

        }

    }


    public static function getPrivacyPolicy($getArray){
        self::initialize();
        $path = __DIR__;
        $path = str_replace("rest/autoprovisioning/user", "/legal/PrivacyPolicy.txt", $path);
        $termsAndConditions = file_get_contents($path);
        http_response_code(200);
        echo json_encode($termsAndConditions);
    }


    public static function getTermsAndConditions($getArray){
        self::initialize();
        $path = __DIR__;
        $path = str_replace("rest/autoprovisioning/user", "/legal/TermsAndConditions.txt", $path);
        $termsAndConditions = file_get_contents($path);
        http_response_code(200);
        echo json_encode($termsAndConditions);
    }



}

