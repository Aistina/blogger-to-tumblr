<?php

use Tumblr\API\Client as Tumblr;

class MigrationController extends BaseController
{
    const TUMBLR_BASE = 'https://www.tumblr.com/';
    const TUMBLR_OAUTH_AUTHORIZE = 'https://www.tumblr.com/oauth/authorize?oauth_token=';

    /**
     * The layout that should be used for responses.
     */
    protected $layout = 'layouts.master';

    /**
     * Step 1: Please connect to Blogger.
     */
    public function showStep1()
    {
        $google = $this->getGoogleClient();

        $this->layout->content = View::make('steps.step1')->with('next', $google->createAuthUrl());
    }

    /**
     * Step 2: Please connect to Tumblr.
     * - Handles login response from Blogger.
     */
    public function showStep2()
    {
        if (($response = $this->handleBloggerLogin()) !== null) {
            return $response;
        }

        $tumblr = $this->getTumblrClient();

        $this->layout->content = View::make('steps.step2')->with('next', $this->createTumblrAuthUrl($tumblr));
    }

    /**
     * Step 2: Select the posts you want to port.
     * - Handles login response from Tumblr.
     */
    public function showStep3()
    {
        if (($response = $this->handleTumblrLogin()) !== null) {
            return $response;
        }

    }

    /**
     * Authenticate against Google API.
     *
     * @return Response|null
     */
    protected function handleBloggerLogin()
    {
        $google = $this->getGoogleClient();

        if (Input::has('code')) {
            $code = Input::get('code');

            $google->authenticate($code);

            Session::put('google.token', $google->getAccessToken());

            // Strip off GET params
            return Redirect::to(URL::current());
        }

        if ($google->getAccessToken() === null) {
            // Back to step 1.
            return Redirect::to(URL::to('step1'));
        }
    }

    /**
     * Authenticate against Tumblr API.
     *
     * @return Response|null
     */
    protected function handleTumblrLogin()
    {
        $tumblr = $this->getTumblrClient();

        if (Input::has('oauth_verifier')) {
            $code = Input::get('oauth_verifier');

            // Verify the token we got.
            $requestHandler = $tumblr->getRequestHandler();
            $response = $requestHandler->request('POST', 'oauth/access_token', array('oauth_verifier' => $code));

            $out = $result = $response->body;
            $data = array();
            parse_str($out, $data);

            // Handle errors.
            if ($response->status != 200) {
                throw new RuntimeException($this->formatTumblrError($data));
            }

            // Excellent, save the token & secret.
            Session::put('tumblr.oauthtoken', $data['oauth_token']);
            Session::put('tumblr.oauthtokensecret', $data['oauth_token_secret']);

            // Strip off GET params
            return Redirect::to(URL::current());
        }

        if ($tumblr->getAccessToken() === null) {
            // Back to step 2.
            return Redirect::to(URL::to('step2'));
        }
    }

    /**
     * ==================================================
     * GOOGLE API FUNCTIONS
     * ==================================================
     */

    /**
     * Get a Google Client instance.
     *
     * @return Google_Client
     */
    protected function getGoogleClient()
    {
        // Set up the client.
        $client = new Google_Client;
        $client->setClientId(Config::get('google.clientid'));
        $client->setClientSecret(Config::get('google.clientsecret'));
        $client->setRedirectUri(URL::to('step2'));

        // Add the required scopes.
        $client->addScope(Google_Service_Blogger::BLOGGER_READONLY);

        // If we already have an access token, set it.
        if ($token = Session::get('google.token')) {
            $client->setAccessToken($token);
        }

        return $client;
    }

    /**
     * ==================================================
     * GOOGLE API FUNCTIONS
     * ==================================================
     */

    /**
     * Get a Tumblr Client instance.
     *
     * @return Tumblr
     */
    protected function getTumblrClient()
    {
        // Set up the client.
        $client = new Tumblr(Config::get('tumblr.consumerkey'), Config::get('tumblr.consumersecret'));

        $requestHandler = $client->getRequestHandler();
        $requestHandler->setBaseUrl(static::TUMBLR_BASE);

        // If we already have a token & token secret, set it.
        if (($token = Session::get('tumblr.oauthtoken')) && ($secret = Session::get('tumblr.oauthtokensecret'))) {
            $client->setToken($token, $secret);
        }

        return $client;
    }

    /**
     * Create an authorization URL for Tumblr.
     *
     * @param  Tumblr $client
     * @return string
     */
    protected function createTumblrAuthUrl(Tumblr $client)
    {
        $requestHandler = $client->getRequestHandler();

        // Probably want to clear any existing token & secret.
        $requestHandler->setToken(null, null);

        // Request a token.
        $response = $requestHandler->request('POST', 'oauth/request_token', array());

        // Extract the oauth_token.
        $out = $result = $response->body;
        $data = array();
        parse_str($out, $data);

        // Handle errors.
        if ($response->status != 200) {
            throw new RuntimeException($this->formatTumblrError($data));
        }

        // Save the token & token secret.
        Session::put('tumblr.oauthtoken', $data['oauth_token']);
        Session::put('tumblr.oauthtokensecret', $data['oauth_token_secret']);

        return static::TUMBLR_OAUTH_AUTHORIZE . $data['oauth_token'];
    }

    /**
     * Formats an error to be readable, e.g. tumblr_oauth_error to Tumblr OAuth error.
     *
     * @param  string|array $text
     * @return string
     */
    protected function formatTumblrError($text)
    {
        if (is_array($text)) {
            $text = head(array_keys($text));
        }

        return ucfirst(
            str_replace(
                array(
                    '_',
                    'tumblr',
                    'oauth',
                ),
                array(
                    ' ',
                    'Tumblr',
                    'OAuth',
                ),
                $text
            )
        ) . '.';
    }
}
