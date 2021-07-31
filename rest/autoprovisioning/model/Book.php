<?php

/**
 * Class Book
 *
 * This class handles everything regarding book requests.
 */

class Book
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
     * Get function for Book model. This function is called from request.php. When a valid request is triggered this function filters through the
     * request type and other GET parameters and builds an appropriate SQL query.
     * The function checks all GET parameters for inappropriate strings (SQL injection), if the GET parameter contains inappropriate strings the function
     * calls Validation::badRequest, which notifies the GET requester.
     * The SQL query is executed at the end, if the SQL query throws an exception the function echoes a response with the HTTP code 500.
     *
     * @param $getArray - GET parameters array
     * @param $database - Database reference
     * @throws Exception - Possible exception thrown from within DateTime constructor
     */
    public static function get($getArray, $database)
    {
        self::initialize();
        //Initial state variables
        $query = null;
        $queryResult = null;
        $token = $_GET['token'];

        switch ($getArray['type']) {
            case BOOK_GET_ALL_BY_CATEGORY: {
                    //Gets all books from a category, has pagination
                    $categoryId = (int) $getArray['categoryId'];
                    $offset = (int) $getArray['offset'];

                    //Getting books based on category ID
                    if (Validation::validateInput($offset) && Validation::validateInput($categoryId)) {
                        try {
                            $q = "SELECT TRIM(author) as author, CAST(authorid AS UNSIGNED) as authorid, id, image, title FROM publications WHERE categoryid = ${categoryId} LIMIT 30 OFFSET ${offset}";
                            $database->query($q);
                            http_response_code(200);
                            echo '[' . join(",", array_map('json_encode', $database->loadObjectList())) . ']';
                        } catch (Exception $e) {
                            $respBuilder = new ResponseBuilder(new Response("Error, Internal Server Error: ${e}.", 500, $token));
                            $respBuilder->fire();
                        } finally {
                            return;
                        }
                    } else {
                        //Illegal GET parameter, SQL injection attempt.
                        Validation::badRequest($token);
                    }
                    break;
                }
            case BOOK_GET_ALL_BY_AUTHOR_ID: {
                    //Gets all books from an author, has pagination
                    $authorID = (int) $getArray['authorID'];
                    $offset = (int) $getArray['offset'];

                    //Getting books based on category ID
                    if (Validation::validateInput($offset) && Validation::validateInput($authorID)) {
                        try {
                            $q = "SELECT DISTINCT publications.id, publications.image, title, publications.published,
                                        authors.id as authorid, TRIM(authors.firstname) as firstname, TRIM(authors.lastname) as lastname
                                        FROM publications
                                        INNER JOIN author_x_book on publications.id = author_x_book.bookid 
                                        INNER JOIN authors on authors.id = author_x_book.authorid
                                        WHERE authors.id = ${authorID} LIMIT 30 OFFSET ${offset}";
                            $database->query($q);
                            http_response_code(200);
                            echo '[' . join(",", array_map('json_encode', $database->loadObjectList())) . ']';
                        } catch (Exception $e) {
                            $respBuilder = new ResponseBuilder(new Response("Error, Internal Server Error: ${e}.", 500, $token));
                            $respBuilder->fire();
                        } finally {
                            return;
                        }
                    } else {
                        //Illegal GET parameter, SQL injection attempt.
                        Validation::badRequest($token);
                    }
                }
                case BOOK_GET_LATEST2: {
                        //Gets all books from featured, has pagination
                        $offset = (int) $getArray['offset'];
                        if (Validation::validateInput($offset)) {
                            try {
                                $q = "SELECT DISTINCT publications.id, publications.image, title, publications.published,
                                        authors.id as authorid, TRIM(authors.firstname) as firstname, TRIM(authors.lastname) as lastname
                                        FROM publications
                                        INNER JOIN author_x_book on publications.id = author_x_book.bookid 
                                        INNER JOIN authors on authors.id = author_x_book.authorid
                                        WHERE published < DATE_ADD(CURDATE(), INTERVAL 1 DAY) 
                                    ORDER BY published DESC LIMIT 30 OFFSET ${offset}";
                                $database->query($q);
                                http_response_code(200);
                                echo $database->loadJsonObjectList();
                            } catch (Exception $e) {
                                $respBuilder = new ResponseBuilder(new Response("Error, Internal Server Error: ${e}.", 500, $token));
                                $respBuilder->fire();
                            } finally {
                                return;
                            }
                        } else {
                            //Illegal GET parameter, SQL injection attempt.
                            Validation::badRequest($token);
                        }
                        break;
                    }
            case BOOK_GET_LATEST: {
                    //Gets all books from featured, has pagination
                    $offset = $getArray['offset'];
                    $tomorrowDt = new DateTime('tomorrow');
                    $tomorrow = $tomorrowDt->format('Y-m-d');

                    if (Validation::validateInput($offset)) {
                        //$q = "SELECT author, authorid, id, image, title FROM publications WHERE published < DATE_ADD(CURDATE(),INTERVAL 1 DAY) ORDER BY published DESC LIMIT 30 OFFSET 0";
                        $query = SQL_GET_BOOK_ALL_LATEST . "'" . $tomorrow . "'" . ' ORDER BY published DESC ' . ' LIMIT ' . 30 . ' OFFSET ' . $offset;
                    } else {
                        //Illegal GET parameter, SQL injection attempt.
                        Validation::badRequest($token);
                    }
                    break;
            }
            case BOOK_GET_BY_ID2: {
                $bookId = (int) $getArray['bookId'];
                if (Validation::validateInput($bookId)) {
                    try {
                        $q = "SELECT publications.id, publications.image, publications.title, publications.synopsis,
                            categoryid, publisher, genre, language, hardback, paperback, ebook, isbn, isbn13, vendor, vendorurl, pages,
                            authors.id as authorid, TRIM(authors.firstname) as firstname, TRIM(authors.lastname) as lastname
                            FROM publications
                            INNER JOIN author_x_book on publications.id = author_x_book.bookid 
                            INNER JOIN authors on authors.id = author_x_book.authorid
                            WHERE publications.id = ${bookId}";
                        $database->query($q);
                        http_response_code(200);
                        echo $database->loadJsonObject();
                    } catch (Exception $e) {
                        //Exception response.
                        $respBuilder = new ResponseBuilder(new Response("Error, Internal Server Error.", 500, $token));
                        $respBuilder->fire();
                    } finally {
                        return;
                    }
                } else {
                    Validation::badRequest($token);
                }
            }
            case BOOK_GET_BY_SEARCH: {
                    //Returns results matching the search input, has pagination
                    $search = $getArray['search'];
                    $offset = $getArray['offset'];

                    //Getting books based on search parameters
                    if (Validation::validateInput($search) && Validation::validateInput($offset)) {
                        $query = Book::parseSearchInput($search, $offset);
                    } else {
                        //Illegal GET parameter, SQL injection attempt.
                        Validation::badRequest($token);
                    }
                    break;
                }
            case BOOK_GET_TOP_SEARCHED_BOOKS: {
                    $offset = $getArray['offset'];
                    //Getting books based on search parameters
                    if (Validation::validateInput($offset)) {
                        $query2 = SQL_GET_BOOK_TOP_SEARCHES_COUNT;
                        $database->query($query2);
                        $queryResult = $database->loadObjectList();
                        $books = array();
                        foreach ($queryResult as $bookWrapper) {
                            $query3 = "SELECT * FROM publications WHERE id =  '" . $bookWrapper->bookid . "'";
                            $database->query($query3);
                            $book = $database->loadObjectList();
                            $books[] = $book[0];
                        }
                        foreach ($books as $queryBook) {
                            $getAuthorSql = SQL_GET_AUTHOR_BY_BOOK_ID;
                            $getAuthorSql = str_replace("{{BOOK_ID}}", $queryBook->id, $getAuthorSql);
                            $database->query($getAuthorSql);
                            $authorResult = $database->loadObjectList();
                            $queryBook->author = $authorResult[0]->firstname . " " . $authorResult[0]->lastname;
                            $queryBook->authorid = $authorResult[0]->id;
                            $queryBook->synopsis = mb_convert_encoding($queryBook->synopsis, "SJIS");

                            $asinValuesSql = SQL_GET_BOOK_ASIN_VALUES . $queryBook->id;
                            $database->query($asinValuesSql);
                            $asinValuesResult = $database->loadObjectList();
                            $queryBook->UK_ASIN = $asinValuesResult[0]->ukASIN;
                            $queryBook->US_ASIN = $asinValuesResult[0]->usASIN;
                        }
                        $respBuilder = new ResponseBuilder(new Response($books, 200, $token));
                        $respBuilder->fire();
                        exit();
                    } else {
                        //Illegal GET parameter, SQL injection attempt.
                        Validation::badRequest($token);
                    }
                    break;
                }
            case BOOK_GET_COMING_SOON: {
                    //Gets all books coming soon
                    $offset = $getArray['offset'];
                    $date = new DateTime('tomorrow');
                    $date = $date->format('Y-m-d');

                    if (Validation::validateInput($offset)) {
                        $query = SQL_GET_BOOK_COMING_SOON . "'" . $date . "'" . ' ORDER BY published ASC ' . ' LIMIT ' . 30 . ' OFFSET ' . $offset;
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

            //GET author for book
            foreach ($queryResult as $queryBook) {
                $getAuthorSql = SQL_GET_AUTHOR_BY_BOOK_ID;
                $getAuthorSql = str_replace("{{BOOK_ID}}", $queryBook->id, $getAuthorSql);
                $database->query($getAuthorSql);
                $authorResult = $database->loadObjectList();
                $queryBook->author = $authorResult[0]->firstname . " " . $authorResult[0]->lastname;
                $queryBook->authorid = $authorResult[0]->id;
                $queryBook->synopsis = mb_convert_encoding($queryBook->synopsis, "SJIS");

                $asinValuesSql = SQL_GET_BOOK_ASIN_VALUES . $queryBook->id;
                $database->query($asinValuesSql);
                $asinValuesResult = $database->loadObjectList();
                $queryBook->UK_ASIN = $asinValuesResult[0]->ukASIN;
                $queryBook->US_ASIN = $asinValuesResult[0]->usASIN;
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

    /**
     * This function parses the search input and returns the SQL statement needed for the search to be executed.
     *
     * @param $str - The search (key words) input string
     * @param $offset - Offset value, used for pagination
     * @return string - SQL query string
     */
    public static function parseSearchInput($str, $offset)
    {
        //var_dump($str);
        $str = str_replace("'", "\'", $str);
        $str = str_replace("\\'", "\'", $str);
        $str = str_replace('"', '\"', $str);
        $tokes = array(Book::tokenizeQuoted($str));
        foreach ($tokes[0][0] as $idx => $val) {
            $val = str_replace(' ', ' +', $val);
            $val = ' "+' . $val . '"';
            for ($count = 1; $count > 0; str_replace('+ +', '+', $val, $count)) {
                echo $count;
            }
            $tokes[0][0][$idx]    = $val;
        }
        $against = implode(' ', $tokes[0][0]);
        $against .=     ' "' . implode(' ', $tokes[0][1]) . '"';
        $searchStr = '';
        if ($str != "") {
            $searchStr    = str_replace("{{AGAINST}}", $against, SQL_GET_BOOK_BY_SEARCH);
        }
        $sql = "SELECT *, " . $searchStr . ' LIMIT ' . 30 . ' OFFSET ' . $offset;
        return $sql;
    }

    /**
     * Tokenize input string.
     *
     * @param $string - Input parameter string
     * @param string $quotationMarks - Quotation marks literal
     * @return array - Return array
     */
    public static function tokenizeQuoted($string, $quotationMarks = '"\'')
    {
        $tokens = array(array(), array());
        for ($nextToken = strtok($string, ' '); $nextToken !== false; $nextToken = strtok(' ')) {
            if (strpos($quotationMarks, $nextToken[0]) !== false) {
                if (strpos($quotationMarks, $nextToken[strlen($nextToken) - 1]) !== false) {
                    $tokens[0][] = substr($nextToken, 1, -1);
                } else {
                    $tokens[0][] = substr($nextToken, 1) . ' ' . strtok($nextToken[0]);
                }
            } else {
                $tokens[1][] = $nextToken;
            }
        }
        return $tokens;
    }
}
