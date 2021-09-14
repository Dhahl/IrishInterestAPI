<?php

header("Access-Control-Allow-Origin: *");

header("Content-Type: application/json; charset=UTF-8");

header("Access-Control-Allow-Methods: POST, GET");



include_once '../config/Core.php';

include_once '../config/RestDefines.php';

include_once 'autoprovisioning/helper/Validation.php';

include_once 'autoprovisioning/model/Category.php';

include_once 'autoprovisioning/model/Author.php';

include_once 'autoprovisioning/user/User.php';

include_once 'autoprovisioning/model/Book.php';

include_once 'autoprovisioning/helper/Response.php';

include_once 'autoprovisioning/helper/ResponseBuilder.php';

include_once 'autoprovisioning/model/Reviews.php';

include_once 'autoprovisioning/model/Misc.php';



/**

 * This file is the heart of the IrishInterest API.

 * Each and every request goes through here.

 */



/**

 * API key validation.

 * Calls Validation::validateApiKey which returns a boolean value representing the validity of the given API key.

 */

if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    if ($_GET['apiKey'] == '' || $_GET['apiKey'] == null || !Validation::validateApiKey($_GET['apiKey'])) {

        //Bad token

        $respBuilder = new ResponseBuilder(new Response("Error, Illegal GET parameter.", 400, null));

        $respBuilder->fire();

        exit();

    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $bodyJson = file_get_contents('php://input');

    $bodyJson = json_decode($bodyJson);

    if ($bodyJson->apiKey == '' || $bodyJson->apiKey == null || !Validation::validateApiKey($bodyJson->apiKey)) {

        //Bad token

        $respBuilder = new ResponseBuilder(new Response("Error, Illegal GET parameter.", 400, null));

        $respBuilder->fire();

        exit();

    }

}



/**

 * Core creation, we ensure database access.

 */

$core = new Core();



if ($_SERVER['REQUEST_METHOD'] === 'GET') {

    /**

     * GET requests

     * Selecting the right GET request based on given GET "value".

     */

    //  Make sure we decode strings in UTF-8, otherwise json_decode will fail on non latin1 chars
    $core->database->query("SET NAMES 'utf8'");


    switch ($_GET['value']) {

        case CATEGORIES:

        {

            //Category request

            Category::get($_GET, $core->database);

            break;

        }

        case AUTHORS:

        {

            //Authors request

            Author::get($_GET, $core->database);

            break;

        }

        case BOOKS:

        {

            //Books request

            Book::get($_GET, $core->database);

            break;

        }

        case REVIEWS:

        {

            //Reviews request

            Reviews::get($_GET, $core->database);

            break;

        }

        case PRIVACY_POLICY:

        {

            //Privacy policy request

            User::getPrivacyPolicy($_GET);

            break;

        }

        case TERMS_AND_CONDITIONS:

        {

            //Terms and conditions request

            User::getTermsAndConditions($_GET);

            break;

        }

	case MISC:

	{

            Misc::get($_GET, $core->database);

            break;

        }

    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /**

     * POST requests

     * Selecting the right POST request based on given POST "action".

     */

    $bodyJson = file_get_contents('php://input');

    $bodyJson = json_decode($bodyJson);

    switch ($bodyJson->action) {

        case REGISTER:

        {

            //User register action

            User::register($bodyJson, $core->database);

            break;

        }

        case LOGIN:

        {

            //User login action

            User::login($bodyJson, $core->database);

            break;

        }

        case GET_FAVOURITES:

        {

            User::getFavourites($bodyJson, $core->database);

            break;

        }

        case ADD_FAVOURITE:

        {

            User::addFavourite($bodyJson, $core->database);

            break;

        }

        case REMOVE_FAVOURITE:

        {

            User::removeFavourite($bodyJson, $core->database);

            break;

        }

        case POST_COMMENT:{

            Reviews::postComment($bodyJson, $core->database);

            break;

        }

        case POST_REVIEW:{

            Reviews::postReview($bodyJson, $core->database);

            break;

        }

        case CONTACT_US:{
            User::contactUs($bodyJson);
            break;
        }

    }

}





















