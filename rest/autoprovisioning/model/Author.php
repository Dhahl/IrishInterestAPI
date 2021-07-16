<?php

/**
 * Class Author
 *
 * This class handles everything regarding any author requests.
 */
class Author
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
     * Get function for Author model. This function is called from request.php. When a valid request is triggered this function filters through the
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

        switch ($getArray['type']) {
            case AUTHOR_GET_ALL:
            {
                //Gets all authors, has pagination
                $offset = $getArray['offset'];

                //Getting all authors
                if (Validation::validateInput($offset)) {
                    $query = str_replace('{{OFFSET}}', $offset, SQL_GET_AUTHOR_ALL);
                } else {
                    //Illegal GET parameter, SQL injection attempt.
                    Validation::badRequest($token);
                }
                break;
            }
            case AUTHOR_GET_BY_ID:
            {
                //Gets author by ID
                $authorId = $getArray['authorId'];

                if (Validation::validateInput($authorId)) {
                    $query = str_replace('{{AUTHOR_ID}}', $authorId, SQL_GET_AUTHOR_BY_ID);
                } else {
                    //Illegal GET parameter, SQL injection attempt.
                    Validation::badRequest($token);
                }
                break;
            }
            case AUTHOR_GET_BY_NAME:
            {
                //Gets author by first name or last name or both
                $firstName = null;
                $lastName = null;
                $offset = $getArray['offset'];

                if (isset($getArray['firstName']) && isset($getArray['lastName'])) {
                    //Searching by both firstName and lastName
                    $firstName = $getArray['firstName'];
                    $lastName = $getArray['lastName'];
                    if (Validation::validateInput($firstName) && Validation::validateInput($lastName)) {
                        $query = SQL_GET_AUTHOR_BY_FIRSTNAME_AND_LASTNAME;
                        $query = str_replace('{{FIRST_NAME}}', $firstName, $query);
                        $query = str_replace('{{LAST_NAME}}', $lastName, $query);
                        $query = str_replace('{{OFFSET}}', $offset, $query);
                    } else {
                        Validation::badRequest($token);
                    }
                } elseif (isset($getArray['firstName']) && !isset($getArray['lastName'])) {
                    //Searching only by first name
                    $firstName = $getArray['firstName'];
                    if (Validation::validateInput($firstName)) {
                        $query = SQL_GET_AUTHOR_BY_FIRSTNAME;
                        $query = str_replace('{{FIRST_NAME}}', $firstName, $query);
                        $query = str_replace('{{OFFSET}}', $offset, $query);
                    } else {
                        Validation::badRequest($token);
                    }
                } elseif (!isset($getArray['firstName']) && isset($getArray['lastName'])) {
                    //Searching only by last name
                    $lastName = $getArray['lastName'];
                    if (Validation::validateInput($lastName)) {
                        $query = SQL_GET_AUTHOR_BY_LASTNAME;
                        $query = str_replace('{{LAST_NAME}}', $lastName, $query);
                        $query = str_replace('{{OFFSET}}', $offset, $query);
                    } else {
                        Validation::badRequest($token);
                    }
                } elseif (isset($getArray['query'])) {
                    $queryStr = $getArray['query'];
                    if (Validation::validateInput($query)) {
                        $query = SQL_GET_AUTHOR_BY_QUERY;
                        $entireQuery = ' authors.firstname = \'{{FIRST_NAME}}\' AND authors.lastname = \'{{LAST_NAME}}\' OR authors.firstname = \'{{LAST_NAME}}\' AND authors.lastname = \'{{FIRST_NAME}}\' ';
                        $queryStrSplit1 = explode(' ', $queryStr);
                        for ($i = 0; $i < count($queryStrSplit1); $i++) {
                            if ($i == 0) {
                                $entireQuery = str_replace('{{FIRST_NAME}}', $queryStrSplit1[$i], $entireQuery);
                            } else {
                                $entireQuery = str_replace('{{LAST_NAME}}', $queryStrSplit1[$i], $entireQuery);
                            }
                        }
                        if (count($queryStrSplit1) == 2) {
                            $query = str_replace('{{ENTIRE_QUERY}}', $entireQuery, $query);
                            $query = str_replace('{{OFFSET}}', $offset, $query);
                            $database->query($query);
                            $queryResult = $database->loadObjectList();
                        }
                        if (count($queryResult) == 0) {
                            $query = SQL_GET_AUTHOR_BY_QUERY;
                            $queryStrSplit = explode(' ', $queryStr);
                            $fullQuery = "";
                            for ($i = 0; $i < count($queryStrSplit); $i++) {
                                $additionalQuery = SQL_GET_AUTHOR_BY_QUERY_WORD_MATCH_ADDITIONAL;
                                $additionalQuery = str_replace('{{QUERY}}', $queryStrSplit[$i], $additionalQuery);
                                if (count($queryStrSplit) - 1 != $i) {
                                    $additionalQuery = str_replace('{{QUERY_OR}}', ' OR ', $additionalQuery);
                                } else {
                                    $additionalQuery = str_replace('{{QUERY_OR}}', '', $additionalQuery);
                                }
                                $fullQuery = $fullQuery . ' ' . $additionalQuery;
                            }
                            $query = str_replace('{{ENTIRE_QUERY}}', $fullQuery, $query);
                            $query = str_replace('{{OFFSET}}', $offset, $query);
                        }
                    }
                }
                break;
            }
            case AUTHOR_GET_BY_BOOK_ID:
            {
                $bookId = $getArray['bookId'];

                if (Validation::validateInput($bookId)) {
                    $query = str_replace('{{BOOK_ID}}', $bookId, SQL_GET_AUTHOR_BY_BOOK_ID);
                } else {
                    //Illegal GET parameter, SQL injection attempt.
                    Validation::badRequest($token);
                }
                break;
            }
        }

        //Execute query
        try {
            $database->query($query);
            $queryResult = $database->loadObjectList();

            //var_dump($queryResult);
            //GET author for book
            foreach($queryResult as $queryAuthor){
                $queryAuthor->profile = mb_convert_encoding($queryAuthor->profile, "SJIS");
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
