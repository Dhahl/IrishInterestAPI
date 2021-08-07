<?php
/**
 * This PHP file contains all constants that the API needs to function.
 */

//SECURITY CONSTANTS
define('API_KEY', 'testApiKey'); //The key for the API
define('TOKEN', 'testTkn'); //This token is only for testing purposes
define('SERVER_SIGNATURE', '38735FAA2F49335E665B76EBAD6C1AE3C9532B25FB2989AD31B5BACE83'); //This is used for verifying, encoding and decoding sensitive information sent from server
define('USER_APP_SIGNATURE', '08d6b25183e0191def96ac96fe1fd127'); //This is used for decoding sensitive information sent from devices, it is currently used in login and registration

//GET values
define('CATEGORIES', 'categories');
define('AUTHORS', 'authors');
define('BOOKS', 'books');
define('REVIEWS', 'reviews');
define('TERMS_AND_CONDITIONS', 'termsAndConditions');
define('PRIVACY_POLICY', 'privacyPolicy');
define('MISC', 'misc');

//POST values
define('USER', 'user');

//USER post actions
define('REGISTER', 'userRegister');
define('LOGIN', 'userLogin');
define('ADD_FAVOURITE', 'addFavourite');
define('GET_FAVOURITES', 'getFavourites');
define('REMOVE_FAVOURITE', 'removeFavourite');
define('CONTACT_US', 'contactUs');

//AUTHOR search types
define('AUTHOR_GET_ALL', 'getAll');
define('AUTHORS_COUNT', 'count');
define('AUTHOR_GET_BY_ID', 'getById');
define('AUTHOR_GET_BY_NAME', 'byName');
define('AUTHOR_GET_BY_BOOK_ID', 'byBookId');

//REVIEW/COMMENT search types
define('REVIEW_GET_BY_BOOK_ID', 'reviewByBookId');
define('REVIEW_GET_BY_USER_ID', 'reviewByUserId');
define('COMMENT_GET_BY_USER_ID', 'commentByUserId');
define('COMMENT_GET_BY_BOOK_ID', 'commentByBookId');
define('POST_REVIEW', 'postReview');
define('POST_COMMENT', 'postComment');

//MISC
define('MISC_COUNT_ALL_BOOKS', 'getBookCount');

/**
 * SQL statements for all models
 */
//CATEGORY - SQL STATEMENTS
define('SQL_GET_CATEGORY_BY_ID', 'SELECT id, Name as name FROM categories where id = ');
define('SQL_GET_CATEGORY_ALL', 'SELECT categories.id, TRIM(categories.Name) as name, COUNT(publications.id) as books 
								FROM categories
								LEFT JOIN publications ON categories.id = publications.categoryid
								GROUP BY categories.id
								HAVING books > 0
								ORDER BY name');

//BOOK - SQL STATEMENTS
define('SQL_GET_BOOK_BY_ID', 'SELECT * FROM publications WHERE id = ');
define('SQL_GET_BOOK_BY_SEARCH',  "MATCH (title, author) against ( {{AGAINST}} IN BOOLEAN MODE) * 5 +
				MATCH(title, author, genre, area, synopsis) AGAINST( {{AGAINST}} IN BOOLEAN MODE) AS score
				from publications WHERE MATCH(title, author, genre, area, synopsis) AGAINST( {{AGAINST}} IN BOOLEAN MODE)
				AND publications.image <> '' ORDER BY score DESC");


//AUTHOR - SQL STATEMENTS4
define('SQL_GET_AUTHOR_BY_ID', 'SELECT auth.* FROM authors AS auth where auth.id = {{AUTHOR_ID}}');
define('SQL_GET_AUTHOR_BY_FIRSTNAME_AND_LASTNAME', 'Select auths.* FROM authors AS auths WHERE auths.authors.firstname =' . "'" . '{{FIRST_NAME}}  ' . "'" .'  AND auths.authors.lastname = ' . "'" . '{{LAST_NAME}}' . "'" . ' LIMIT 30 OFFSET {{OFFSET}}');
define('SQL_GET_AUTHOR_BY_FIRSTNAME', 'Select * FROM authors WHERE authors.firstname = ' . "'" . '{{FIRST_NAME}}' . "'" . ' LIMIT 30 OFFSET {{OFFSET}}');
define('SQL_GET_AUTHOR_BY_LASTNAME', 'Select * FROM authors WHERE authors.lastname = ' . "'" . '{{LAST_NAME}}' . "'" . ' LIMIT 30 OFFSET {{OFFSET}}');
define('SQL_GET_AUTHOR_BY_QUERY', 'Select * FROM authors WHERE ' . '{{ENTIRE_QUERY}}' . 'LIMIT 30 OFFSET {{OFFSET}}');
define('SQL_GET_AUTHOR_BY_QUERY_WORD_MATCH_ADDITIONAL', 'authors.firstname LIKE ' . "'" . '%{{QUERY}}%' . "'" . ' OR authors.lastname LIKE ' . "'" . '%{{QUERY}}%' . "'" . '{{QUERY_OR}} ' );

define('SQL_GET_AUTHOR_BY_BOOK_ID', 'SELECT author_x_book.*, authors.* 
        			            FROM author_x_book LEFT JOIN authors 
        			            ON  author_x_book.authorid = authors.id
        			            WHERE  author_x_book.bookid = {{BOOK_ID}}');

//REVIEWS - SQL STATEMENTS
define('SQL_GET_REVIEWS_BY_BOOK_ID', 'SELECT * FROM reviews WHERE bookid = {{BOOK_ID}} ORDER BY date DESC');
define('SQL_GET_REVIEW_COMMENT_BY_BOOK_ID', 'SELECT * FROM reviewcomments WHERE book = {{BOOK_ID}} ORDER BY date DESC');
define('SQL_GET_REVIEW_BY_USER_ID', 'SELECT * FROM reviews WHERE userid = {{USER_ID}} ORDER BY date DESC');
define('SQL_GET_REVIEW_COMMENT_BY_USER_ID', 'SELECT * FROM reviewcomments WHERE commenterid = {{USER_ID}} ORDER BY date DESC');
define('SQL_GET_REVIEW_COMMENT_NEXT_ID', 'SELECT commentid FROM reviewcomments WHERE reviewerid = {{USER_ID}} ORDER BY date DESC');
define('SQL_INSERT_REVIEWS_BY_BOOK_ID', 'INSERT INTO reviews (bookid, userid, date, rating, recommend, status) VALUES ({{BOOK_ID}}, {{USER_ID}}, {{DATE}}, {{RATING}}, {{RECOMMEND}}, {{STATUS}})');

//USER - SQL STATEMENTS
define('SQL_COUNT_USER_BY_EMAIL_AND_PASSWORD', 'SELECT COUNT(*) AS test FROM users WHERE email = ' . "'" . '{{USER_EMAIL}}' . "'" . ' AND pw = ' . "'" . '{{USER_PASSWORD}}' . "'");
define('SQL_GET_USER_BY_EMAIL_AND_PASSWORD', 'SELECT * FROM users WHERE email = ' . "'" . '{{USER_EMAIL}}' . "'" . ' AND pw = ' . "'" . '{{USER_PASSWORD}}' . "'");

//MISC - SQL STATEMENTS
define('SQL_COUNT_ALL_BOOKS', 'SELECT COUNT(*) AS NumberOfAllBooks FROM publications');