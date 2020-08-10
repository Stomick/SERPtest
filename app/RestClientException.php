<?php


namespace App;


class RestClientException extends \Exception
{
    protected $http_code;

    public function __construct($message, $code = 0, $http_code, Exception $previous = null)
    {
        $this->http_code = $http_code;
        parent::__construct($message, $code, $previous);
    }

    public function getHttpCode()
    {
        return $this->http_code;
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message} (HTTP status code: {$this->http_code})\n";
    }

}
