<?php
namespace JReissmueller\LinkedIn;

class LinkedInAPIResponse
{
    private $status;
    private $raw;
    private $response;
    private $errors;

    /**
     * LinkedInResponse constructor.
     *
     * @param string $apiResponse
     */
    public function __construct($apiResponse)
    {
        $this->raw = $apiResponse;
        $this->response =  json_decode($apiResponse);
        $this->status = isset($this->response->status) ? $this->response->status : 400;
        if (isset($this->response->errorCode)) {
            $this->errors = isset($this->response->message) ? $this->response->message : 'Unknown Error';
        }
    }

    /**
     * Get the status of this response
     *
     * @return string The status of this response
     */
    public function status()
    {
        return $this->status;
    }

    /**
     * Get the raw data from this response
     *
     * @return string The raw data from this response
     */
    public function raw()
    {
        return $this->raw;
    }

    /**
     * Get the data response from this response
     *
     * @return string The data response from this response
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * Get any errors from this response
     *
     * @return string The errors from this response
     */
    public function errors()
    {
        return $this->errors;
    }
}