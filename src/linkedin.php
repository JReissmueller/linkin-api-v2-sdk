<?php
namespace JReissmueller\LinkedIn;

require_once dirname(__FILE__) . DIRECTORY_SEPARATOR . 'linkedin_response.php';

class LinkedIn
{
    /**
     * The url endpoint for the LinkedIn V2 API
     *
     * @var string $apiUrl
     */
    private $oauthUrl = 'https://www.linkedin.com/oauth/v2';
    /**
     * The url endpoint for the LinkedIn V2 API
     *
     * @var string $apiUrl
     */
    private $apiUrl = 'https://api.linkedin.com/v1';
    /**
     * The LinkedIn API Key
     *
     * @var string $apiKey
     */
    private $apiKey;
    /**
     * The LinkedIn API Secret
     *
     * @var string $apiSecret
     */
    private $apiSecret;
    /**
     * The access token information for the client for which to make requests
     *
     * @var array $accessToken
     */
    private $accessToken = ['access_token' => '', 'expires_in' => ''];
    /**
     * The data sent with the last request served by this API
     *
     * @var array $lastRequest
     */
    private $lastRequest = [];
    /**
     * The uri a user is redirected to after making an authorization request
     *
     * @var string $redirectUri
     */
    private $redirectUri = '';

    /**
     * Sets credentials for all future API interactions
     *
     * @param string $apiKey The LinkedIn API Key
     * @param string $apiSecret The LinkedIn API Secret
     * @param string $redirectUri The uri a user is redirected to after making an authorization request
     */
    public function __construct($apiKey, $apiSecret, $redirectUri)
    {
        $this->apiKey = $apiKey;
        $this->apiSecret = $apiSecret;
        $this->redirectUri = $redirectUri;
    }

    /**
     * Makes an API request to LinkedIn
     *
     * @param string $action The api endpoint for the request
     * @param array $data The data to send with the request
     * @param string $method The data transfer method to use
     * @return stdClass The data returned by the request
     */
    private function makeRequest($action, $data, $method, $override_url = null)
    {
        $url = ($override_url ? $override_url : $this->apiUrl) . '/' . $action;
        $ch = curl_init();

        switch (strtoupper($method)) {
            case 'GET':
            case 'DELETE':
                $url .= empty($data) ? '' : '?' . http_build_query($data);
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, 1);
            default:
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
                break;
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        $headers = [
            'Authorization: Bearer ' . $this->accessToken['access_token'],
            'Cache-Control: no-cache',
            'X-RestLi-Protocol-Version:2.0.0',
            'Content-Type: application/json'
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $this->lastRequest = ['content' => $data, 'headers' => $headers];
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            $result = json_encode((object)['error' => 'curl_error', 'error_description' => curl_error($ch)]);
        }
        curl_close($ch);

        // Return request response
        return new LinkedInResponse($result);
    }

    /**
     * Gets the access token for this API
     *
     * @param string $code The authorization code given by user app permissions approval
     * @return string The access token
     */
    public function getAccessToken($code = null)
    {
        if (!empty($this->accessToken['access_token'])) {
            return $this->accessToken;
        }

        $requestData = [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
            'client_id' => $this->apiKey,
            'client_secret' => $this->apiSecret
        ];

        $tokenReponse = $this->makeRequest('accessToken', $requestData, 'GET', $this->oauthUrl);

        if ($tokenReponse->status() == 200) {
            $this->accessToken = $tokenReponse->response();
        }

        return $tokenReponse;
    }

    /**
     * Sets the access token for this API
     *
     * @param string $token The token to be set for this API
     */
    public function setAccessToken($token)
    {
        $this->accessToken = $token;
    }


    /**
     * Gets the data from the last request made by this API
     *
     * @return array The data from the last request
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * Returns the url for a user to approve access for the app
     *
     * @param array $scope A list of scopes for which to request access
     * @return string The permission granting url
     */
    public function getPermissionUrl($scope = null)
    {
        $requestData = [
            'response_type' => 'code',
            'client_id' => $this->apiKey,
            'redirect_uri' => $this->redirectUri,
            'state' => time(),
//            'scope' => $scope
        ];

        return $this->oauthUrl . '/authorization?' . http_build_query($requestData);
    }

    /**
     * Makes a post request to the api
     *
     * @param string $action The api endpoint for the request
     * @param array $data The data to send with the request
     * @return string The access token
     */
    public function post($action, $data = [])
    {
        return $this->makeRequest($action, $data, 'POST');
    }

    /**
     * Makes a get request to the api
     *
     * @param string $action The api endpoint for the request
     * @param array $data The data to send with the request
     * @return string The access token
     */
    public function get($action, $data = [])
    {
        return $this->makeRequest($action, $data, 'GET');
    }
}