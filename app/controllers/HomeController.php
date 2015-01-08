<?php

class HomeController extends BaseController
{
    /**
     * The layout that should be used for responses.
     */
    protected $layout = 'layouts.master';

    /**
     * Welcome page.
     */
    public function showWelcome()
    {
        $this->layout->content = View::make('hello');
    }
}
