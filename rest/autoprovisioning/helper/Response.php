<?php

/**
 * Class Response
 *
 * This class is a normal POPO (plain old php object) for building API responses.
 */
class Response
{
    private $message;
    private $responseCode;
    private $token;

    /**
     * Response constructor.
     * @param $message
     * @param $responseCode
     * @param $token
     */
    public function __construct($message, $responseCode, $token){
        $this->message = $message;
        $this->responseCode = $responseCode;
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return mixed
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }
}