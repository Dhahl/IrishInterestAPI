<?php

/**
 * Class Reviews
 *
 * This class handles everything regarding any review requests.
 */
class Reviews
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

    public static function get($getArray, $database)
    {
        self::initialize();

        //Initial state variables
        $query = null;
        $queryResult = null;
        $response_code = null;

        switch ($getArray['type']) {
            case REVIEW_GET_BY_BOOK_ID:
            {
                self::getByBookId($getArray, $database);
                break;
            }
            case REVIEW_GET_BY_USER_ID:
            {
                self::getByUserId($getArray, $database);
                break;
            }
            case COMMENT_GET_BY_BOOK_ID:
            {
                self::getCommentByBookId($getArray, $database);
                break;
            }
            case COMMENT_GET_BY_USER_ID:{
                self::getCommentByUserId($getArray, $database);
                break;
            }
        }
    }

    public static function getByBookId($getArray, $database)
    {
        self::initialize();

        //Initial state variables
        $query = null;
        $queryResult = null;
        $response_code = null;
        $token = $_GET['token'];

        //Selecting query based on GET parameters
        if ($getArray['bookId'] != '' && isset($getArray['bookId'])) {
            //Getting category based on ID
            if (Validation::validateInput($getArray['bookId'])) {
                $bookId = $getArray['bookId'];
                $query = SQL_GET_REVIEWS_BY_BOOK_ID;
                $query = str_replace('{{BOOK_ID}}', $bookId, $query);
            } else {
                //Illegal GET parameter, SQL injection attempt.
                Validation::badRequest($token);
            }
            //Execute query
            try {
                $database->query($query);
                $queryResult = $database->loadObjectList();
                foreach($queryResult as $review){
                    $path = __DIR__;
                    $path = str_replace("API/rest/autoprovisioning/model", "/review_text/" . $review->bookid . '_' . $review->userid . '.txt', $path);
                    $review->reviewText = file_get_contents($path);

                    $sql = 'SELECT real_name, lastname FROM users WHERE id=' . $review->userid;
                    $database->query($sql);
                    $result = $database->loadObjectList();
                    $review->authorname = $result[0]->real_name . " " . $result[0]->lastname;
                }

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

    public static function getByUserId($getArray, $database)
    {
        self::initialize();

        //Initial state variables
        $query = null;
        $queryResult = null;
        $response_code = null;
        $token = $_GET['token'];

        //Selecting query based on GET parameters
        if ($getArray['userId'] != '' && isset($getArray['userId'])) {
            //Getting category based on ID
            if (Validation::validateInput($getArray['userId'])) {
                $userId = $getArray['userId'];
                $query = SQL_GET_REVIEW_BY_USER_ID;
                $query = str_replace('{{USER_ID}}', $userId, $query);
            } else {
                //Illegal GET parameter, SQL injection attempt.
                Validation::badRequest($token);
            }
            //Execute query
            try {
                $database->query($query);
                $queryResult = $database->loadObjectList();
                foreach($queryResult as $review){
                    $path = __DIR__;
                    $path = str_replace("API/rest/autoprovisioning/model", "/review_text/" . $review->bookid . '_' . $review->userid . '.txt', $path);
                    $review->reviewText = file_get_contents($path);
                }
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

    public static function getCommentByUserId($getArray, $database){
        self::initialize();

        //Initial state variables
        $query = null;
        $queryResult = null;
        $response_code = null;
        $token = $_GET['token'];

        //Selecting query based on GET parameters
        if ($getArray['userId'] != '' && isset($getArray['userId'])) {
            //Getting category based on ID
            if (Validation::validateInput($getArray['userId'])) {
                $userId = $getArray['userId'];
                $query = SQL_GET_REVIEW_COMMENT_BY_USER_ID;
                $query = str_replace('{{USER_ID}}', $userId, $query);
            } else {
                //Illegal GET parameter, SQL injection attempt.
                Validation::badRequest($token);
            }
            //Execute query
            try {
                $database->query($query);
                $queryResult = $database->loadObjectList();
                foreach($queryResult as $comment){
                    $path = __DIR__;
                    $path = str_replace("API/rest/autoprovisioning/model", "/review_text/" . $comment->book . '_' . $comment->reviewerid . '_' . $comment->commenterid  . '_' . $comment->commentid .'.txt', $path);
                    $comment->commentText = file_get_contents($path);
                }
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

    public static function getCommentByBookId($getArray, $database){
        self::initialize();

        //Initial state variables
        $query = null;
        $queryResult = null;
        $response_code = null;
        $token = $_GET['token'];

        //Selecting query based on GET parameters
        if ($getArray['bookId'] != '' && isset($getArray['bookId'])) {
            //Getting category based on ID
            if (Validation::validateInput($getArray['bookId'])) {
                $bookId = $getArray['bookId'];
                $query = SQL_GET_REVIEW_COMMENT_BY_BOOK_ID;
                $query = str_replace('{{BOOK_ID}}', $bookId, $query);
            } else {
                //Illegal GET parameter, SQL injection attempt.
                Validation::badRequest($token);
            }
            //Execute query
            try {
                $database->query($query);
                $queryResult = $database->loadObjectList();
                foreach($queryResult as $comment){
                    $path = __DIR__;
                    $path = str_replace("API/rest/autoprovisioning/model", "/review_text/" . $comment->book . '_' . $comment->reviewerid . '_' . $comment->commenterid  . '_' . $comment->commentid .'.txt', $path);
                    $comment->commentText = file_get_contents($path);
                }
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

    public static function postComment($postParams, $database){
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

        $commenter_id = $dec->commenter_id;
        $book_id = $dec->book_id;
        $reviewer_id=$dec->reviewer_id;
        $date=$dec->date;
        $comment_text=$dec->comment_text;

        try{
            $sql =str_replace("{{USER_ID}}", $reviewer_id,SQL_GET_REVIEW_COMMENT_NEXT_ID);
            $database->query($sql);
            $queryResult = $database->loadObjectList();

            $commentid=$queryResult[0] + 1;

            $sql ="INSERT INTO reviewcomments VALUES (". $book_id .", " . $reviewer_id .", " . $commenter_id .", " ."'". $date . "', " . $commentid .")";
            $database->query($sql);
            $queryResult = $database->loadObjectList();

            $path = __DIR__;
            $path = str_replace("API/rest/autoprovisioning/model", "/review_text/" . $book_id . '_' . $reviewer_id . '_' . $commenter_id  . '_' . $commentid .'.txt', $path);

            $myfile = fopen($path, "w") or die("Unable to open file!");
            $txt = $comment_text;
            fwrite($myfile, $txt);
            fclose($myfile);

            $respBuilder = new ResponseBuilder(new Response("Success.", 200, $token));
            $respBuilder->fire();
        } catch (Exception $e) {
            //Exception response.
            $respBuilder = new ResponseBuilder(new Response("Error, Internal Server Error.", 500, $token));
            $respBuilder->fire();
            exit();
        }
    }

    public static function postReview($postParams, $database){
        //TODO
        self::initialize();
        $token = $postParams->token;
        //$enc = $postParams->enc; //Read encrypted data

        //TODO: This is only for testing purposes. Will be removed in final version.
        //if(isset($postParams->isTest) && $postParams->isTest == true){
        //    $enc = AuthenticationController::encryptMessage($enc);
        //}
        //We have to decode the enc data
        //$dec = AuthenticationController::decryptMessage($enc);
        //$dec = json_encode($dec);
        //$dec = json_decode($dec);

        $userid = $postParams->userid;
        $bookid = $postParams->bookid;
        $recommend = $postParams->recommend;
        $status = $postParams->status;
        $date = $postParams->date;
        $rating = $postParams->rating;
        $reviewText = $postParams->reviewText;

        try{
            $sql =str_replace("{{USER_ID}}", $userid,SQL_INSERT_REVIEWS_BY_BOOK_ID);
            $sql =str_replace("{{BOOK_ID}}", $bookid,$sql);
            $sql =str_replace("{{DATE}}", $date,$sql);
            $sql =str_replace("{{RECOMMEND}}", $recommend,$sql);
            $sql =str_replace("{{STATUS}}", $status,$sql);
            $sql =str_replace("{{RATING}}", $rating,$sql);

            $database->query($sql);
            $queryResult = $database->loadObjectList();

            $path = __DIR__;
            $path = str_replace("API/rest/autoprovisioning/model", "/review_text/" . $bookid . '_' . $userid .'.txt', $path);

            $myfile = fopen($path, "w") or die("Unable to open file!");
            $txt = $reviewText;
            fwrite($myfile, $txt);
            fclose($myfile);

            $respBuilder = new ResponseBuilder(new Response("Success.", 200, $token));
            $respBuilder->fire();
        } catch (Exception $e) {
            //Exception response.
            $respBuilder = new ResponseBuilder(new Response("Error, Internal Server Error.", 500, $token));
            $respBuilder->fire();
            exit();
        }
    }
}