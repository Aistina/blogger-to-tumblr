<?php

class MigrationController extends BaseController
{
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
        if ($this->handleBloggerLogin() === false) {
            return;
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

        return $client;
    }
}
