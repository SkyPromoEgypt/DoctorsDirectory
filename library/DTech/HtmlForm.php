<?php

class HtmlForm extends HtmlPage
{

    private $_method = "post";
    private $_action = "";
    private $_encType;
    private $_options;

    function __construct (array $options = null)
    {
        parent::__construct();
        if($options && !empty($options)) {
            $this->_options = $options;
        }
    }

    public function createForm($body)
    {
        // TODO: Finish the HtmlForm Class
        // TODO: Create a Photo Class
        // TODO: Create a simple Mail Class
        $form = self::addElement("form", $body, $this->_options);
        return $form;
    }
}