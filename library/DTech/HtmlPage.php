<?php

class HtmlPage extends Html
{

    function __construct ()
    {
        parent::__construct();
    }

    public function setPageTitle($title)
    {
        !empty($title) ? $this->_title .= " | " . $title : null;
    }

    public function addElement ($element, $body,
    array $attributes = array("class" => "element"))
    {
        // To be built on the go
        $slefEnclosed = array('img', 'link', 'input');
        $output = "<$element ";
        foreach ($attributes as $attribute => $value) {
            $output .= $attribute . "=\"" . $value . "\" ";
        }
        $output .= ">";
        if (is_array($body)) {
            foreach ($body as $bodyItem) {
                $output .= $bodyItem;
            }
        } else {
            $output .= $body;
        }
        $output .= "</$element>";
        return $output;
    }
}