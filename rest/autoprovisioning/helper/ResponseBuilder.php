<?php

/**
 * Class ResponseBuilder
 *
 * This class is used as a builder class to generate JSON responses.
 */
class ResponseBuilder
{
    private $response;

    /**
     * ResponseBuilder constructor.
     * @param $response
     */
    public function __construct($response){
        $this->response = $response;
    }

    /**
     * This function is used to send out the built response.
     */
    public function fire(){
        //Returns the response
        $resp = $this->buildResponse();
        echo json_encode($resp);
    }

    public function utf8ize($d) {
        if (is_array($d)) {
            foreach ($d as $k => $v) {
                $d[$k] = $this->utf8ize($v);
            }
        } else if (is_string ($d)) {
            return utf8_encode($d);
        }
        return $d;
    }

    /**
     * This function is used to build the response, that will be sent.
     *
     * @return array -
     */
    private function buildResponse(){
        //First we set the HTTP response code
        switch ($this->response->getResponseCode()){
            case 200:{
                // set response code - 200 OK
                http_response_code(200);
                break;
            }
            case 201: {
                // set response code - 201 Created
                http_response_code(201);
                break;
            }
            case 202: {
                // set response code - 202 Accepted
                http_response_code(202);
                break;
            }
            case 204:{
                // set response code - 202 No content
                http_response_code(204);
                break;
            }
            case 301:{
                // set response code - 301 Moved Permanently
                http_response_code(301);
                break;
            }
            case 308:{
                // set response code - 308 Permanent Redirect
                http_response_code(308);
                break;
            }
            case 400:{
                // set response code - 400 Bad Request
                http_response_code(400);
                break;
            }
            case 403:{
                // set response code - 403 Forbidden
                http_response_code(403);
                break;
            }
            case 404:{
                // set response code - 404 Not Found
                http_response_code(404);
                break;
            }
            case 500:{
                // set response code - 500 Internal Server Error
                http_response_code(500);
                break;
            }
            case 501:{
                // set response code - 501 Not Implemented
                http_response_code(501);
                break;
            }
        }

        //We create the output
        return array("response" => $this->response->getMessage(), "token"=>$this->response->getToken());
    }
}