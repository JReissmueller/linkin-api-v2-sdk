<?php
namespace JReissmueller\LinkedIn;

class LinkedInResponse
{
    private $status;
    private $raw;
    private $response;
    private $errors;
    private $headers;

    /**
     * LinkedInResponse constructor.
     *
     * @param string $apiResponse
     */
    public function __construct($apiResponse)
    {
        $responseData = explode("\n", $apiResponse);
        var_dump($responseData);
        $this->raw = $responseData[count($responseData) - 1];
        $this->headers = array_slice($responseData, -1);
        $response = json_decode($this->raw);
        if (!isset($response->error)) {
            $this->status = 200;
            $this->response = $response;
        } else {
            $this->errors = isset($response->error_description) ? $response->error_description : 'Unknown Error';
            $this->response = $response;
            $this->status = 403;
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

    /**
     * Get the headers returned with this response
     *
     * @return string The headers returned with this response
     */
    public function headers()
    {
        return $this->headers;
    }
}
