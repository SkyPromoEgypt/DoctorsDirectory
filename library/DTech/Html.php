<?php

abstract class Html
{

    protected $_htmlDocType;

    protected $_encoding;

    protected $_title = APPLICATION_NAME;

    protected $_metaTags;

    protected $_minifyPath;

    protected $_css;

    protected $_javascript;

    function __construct (
    array $docType = array("version" => "xhtml", "type" => "transitional"), $encoding = 'utf-8', $metaTags = false, $robots = false)
    {
        $this->_htmlDocType = $docType;
        $this->_encoding = $encoding;
        if (false !== $metaTags) {
            if (is_array($metaTags)) {
                foreach ($metaTags as $name => $content) {
                    $this->_metaTags[] = "<meta name=\"$name\" content=\"$content\" />";
                }
            }
        }
        $iniArray = parse_ini_file(APPLICATION_PATH . "/config/application.ini",
        true);
        $minifyPath = $iniArray["application"]["MINIFY_PATH"];
        $this->_minifyPath = $minifyPath;
        $this->_loadCss();
        $this->_loadJavascript();
    }

    public function header ()
    {
        $htmlTags = array(
        "html4" => array(
        "transitional" => htmlspecialchars(
        "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Transitional//EN\" \"http://www.w3.org/TR/html4/loose.dtd\">\n<html>"),
        "strict" => htmlspecialchars(
        "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Strict//EN\" \"http://www.w3.org/TR/html4/strict.dtd\">\n<html>"),
        "frameset" => htmlspecialchars(
        "<!DOCTYPE html PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\" \"http://www.w3.org/TR/html4/frameset.dtd\">\n<html>")),
        "html5" => array("html5" => htmlspecialchars("<!DOCTYPE html>\n<html>")),
        "xhtml" => array(
        "transitional" => htmlspecialchars(
        "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">"),
        "strict" => htmlspecialchars(
        "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">"),
        "frameset" => htmlspecialchars(
        "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Frameset//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd\">\n<html xmlns=\"http://www.w3.org/1999/xhtml\">")));
        if ($this->_htmlDocType['version'] == "html5") {
            $encodingMeta = "<meta charset=\"$this->_encoding\">";
        } else {
            $encodingMeta = "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=$this->_encoding\">";
        }
        // Open Start Tag based on the HTML Document Type
        $output = $htmlTags[$this->_htmlDocType["version"]][$this->_htmlDocType["type"]] .
         "\n";
        $output .= "<head>\n";
        $output .= $encodingMeta . "\n";

        $faviconFile = APPLICATION_PATH . DS . "../public/images" . DS . "favicon.ico";

        if(file_exists($faviconFile)) {
            $output .= "<link rel=\"icon\" href=\"" . SITENAME . DS . IMAGES_PATH . DS . "favicon.ico\" type=\"image/x-icon\" />";
            $output .= "<link rel=\"shortcut icon\" href=\"" . SITENAME . DS . IMAGES_PATH . DS . "favicon.ico\" type=\"image/x-icon\" />";
        }

        $output .= "<title>" . $this->_title . "</title>";

        $cssLink = "<link type=\"text/css\" rel=\"stylesheet\" href=\"" .
         $this->_minifyPath . "b=css&amp;f=";
        $cssLink .= implode(",", $this->_css);
        $cssLink .= "\" />";
        $output .= $cssLink . "\n";
        $javascriptLink = "<script type=\"text/javascript\" src=\"" .
         $this->_minifyPath . "b=js&amp;f=";
        $javascriptLink .= implode(",", $this->_javascript);
        $javascriptLink .= "\"></script>";
        $output .= $javascriptLink . "\n";
        $output .= "</head>\n\n<body>\n";
        return htmlspecialchars_decode($output);
    }

    private function _loadCss ()
    {
        $cssDir = APPLICATION_PATH.DS."../public".DS.CSS_PATH;
        if (is_dir($cssDir)) {
            $handle = opendir($cssDir);
            if ($handle) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        $this->_css[] = $file;
                    }
                }
            }
        }
    }

    private function _loadJavascript ()
    {
        $cssDir = APPLICATION_PATH.DS."../public".DS.JAVASCRIPT_PATH;
        if (is_dir($cssDir)) {
            $handle = opendir($cssDir);
            if ($handle) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        $this->_javascript[] = $file;
                    }
                }
            }
        }
    }

    public function footer ()
    {
        $output = "</body>\n</html>";
        return $output;
    }
}