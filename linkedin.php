<?php
namespace LinkedIn;

class LinkedIn
{
    /**
     * The url endpoint for the LinkedIn V2 API
     *
     * @var string $apiUrl
     */
    private $apiUrl = 'https://www.linkedin.com/oauth/v2';
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

    private function makeRequest($action, $data, $method)
    {
        $url = $this->apiUrl . '/' . $action;
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
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);

        $headers = [
            'Authorization: Bearer' . $this->accessToken,
            'Cache-Control: no-cache',
            'Content-Type: application/json'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $this->lastRequest = ['content' => $data, 'headers' => $headers];
        $result = curl_exec($ch);
        if (curl_errno($ch)) {
            // Handle error
        }
        curl_close($ch);


        // Return request response
        return $result;
    }

    /**
     * Gets the access token for this API
     *
     * @return string The access token
     */
    public function getAccessToken()
    {
        return $this->accessToken;
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
     *
     * @param type $scope
     */
    public function getPermissionUrl($scope = null)
    {
        if ($scope == null) {
            $scope = rawurlencode('r_fullprofile r_emailaddress w_share');
        }

        $requestData = [
            'response_type' => 'code',
            'client_id' => $this->apiKey,
            'redirect_uri' => $this->redirectUri,
            'scope' => $scope
        ];

        $url = $this->makeRequest('authorization', $requestData, 'GET');

        return $url;
    }
}