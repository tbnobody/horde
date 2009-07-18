<?php
/**
 * Copyright 2008-2009 The Horde Project (http://www.horde.org/)
 *
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 * @category Horde
 * @package  Horde_Oauth
 */

/**
 * OAuth consumer class
 *
 * @author   Chuck Hagenbuch <chuck@horde.org>
 * @license  http://opensource.org/licenses/bsd-license.php BSD
 * @category Horde
 * @package  Horde_Oauth
 */
class Horde_Oauth_Consumer
{
    protected $_config;

    /**
     * Const'r for consumer.
     *
     * @param array $config  Configuration values:
     *  <pre>
     *    'key'               - Consumer key
     *    'secret'            - Consumer secret
     *    'requestTokenUrl'   - The request token URL
     *    'authorizeTokenUrl' - The authorize URL
     *    'signatureMethod    - Horde_Oauth_SignatureMethod object
     *  </pre>
     *
     * @return Horde_Oauth_Consumer
     */
    public function __construct($config)
    {
        // Check for required config
        if (!is_array($config) || empty($config['key']) || empty($config['secret']) ||
            empty($config['requestTokenUrl']) || empty($config['authorizeTokenUrl']) ||
            empty($config['signatureMethod'])) {

            throw new InvalidArgumentException('Missing a required parameter in Horde_Oauth_Consumer::__construct');
        }
        $this->_config = $config;
    }

    public function __get($name)
    {
        return isset($this->_config[$name]) ? $this->_config[$name] : null;
    }

    /**
     * Obtain a request token
     *
     * @param array $params  Parameter array
     *
     * @return Horde_Oauth_Token  A OAuth request token
     */
    public function getRequestToken($params = array())
    {
        $params['oauth_consumer_key'] = $this->key;

        $request = new Horde_Oauth_Request($this->requestTokenUrl, $params);
        $request->sign($this->signatureMethod, $this);

        $client = new Horde_Http_Client;

        $response = $client->post(
            $this->requestTokenUrl,
            $request->buildHttpQuery()
        );
        return Horde_Oauth_Token::fromString($response->getBody());
    }

    /**
     * Get the user authorization url
     *
     * @param Horde_Oauth_Token $token  A OAuth token
     *
     * @return string The user authorization url string
     */
    public function getUserAuthorizationUrl($token)
    {
        return $this->authorizeTokenUrl . '?oauth_token=' . urlencode($token->key) . '&oauth_callback=' . urlencode($this->callbackUrl);
    }

    /**
     *
     * @param Horde_Oauth_Token $token
     * @param $params
     *
     * @return unknown_type
     */
    public function getAccessToken($token, $params = array())
    {
        $params['oauth_consumer_key'] = $this->key;
        $params['oauth_token'] = $token->key;

        $request = new Horde_Oauth_Request($this->accessTokenUrl, $params);
        $request->sign($this->signatureMethod, $this, $token);

        $client = new Horde_Http_Client;
        $response = $client->post(
            $this->accessTokenUrl,
            $request->buildHttpQuery()
        );
        return Horde_Oauth_Token::fromString($response->getBody());
    }

}
