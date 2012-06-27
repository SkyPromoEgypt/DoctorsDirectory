<?php

class IndexController extends FrontController
{

    public function IndexAction ()
    {
        $this->render("Welcome to Home Page");
    }

    public function CreateAction ()
    {
        $this->_loadLanguageFile();
        $this->render("Create a new poll");
    }

    public function TestAction ()
    {
        $this->render("Testing Purposes");
    }

    public function NotFoundAction ()
    {
        $this->render("Sorry, this page was not found");
    }
}