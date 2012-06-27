<?php

abstract class FrontController implements IController
{

    /**
     *
     * Controller Name dispatched from the application URL
     * @var string
     */
    protected $controller;

    /**
     *
     * Action Name dispatched from the application URL
     * @var string
     */
    protected $action;

    /**
     * Class constructor
     * Instantiate the object by setting the controller name
     * and the action name
     * @param string $controller
     * @param string $action
     */
    public function __construct ($controller = "index", $action = "index")
    {
        $this->controller = $controller;
        $this->action = $action;
    }

    /**
     * Builds the path to the view folder based on the controller name and the namespace
     * @return View Path
     */
    public function getViewFolder ()
    {
        return APPLICATION_PATH . DS . "views" . DS . $this->controller;
    }

    /**
     * The dispatcher method. Dispatched the controller name and
     * the action name form the application URL
     * @throws Exception if the method is not implemented
     */
    public function dispatch ()
    {
        $method = $this->action . "Action";
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    /**
     * Render the view file based on its controller
     */
    public function render ($title = "")
    {
        $document = new HtmlPage();
        ob_start();
        $document->setPageTitle($title);
        echo $document->header();
        include_once $this->getViewFolder() . DS . $this->action . '.phtml';
        echo $document->footer();
        if (SHOW_LAST_QUERIES != 0) {
            echo ApplicationBehavior::checkSqlStatementArray();
        }
        if (SHOW_SQL_WINDOW != 0) {
            echo ApplicationBehavior::showSqlWindow();
        }
        ob_flush();
    }

    protected function _loadLanguageFile ()
    {
        $langFile = APPLICATION_PATH . DS . "language" . DS . $_SESSION['lang'] . ".ini";
        $iniArray = parse_ini_file($langFile, true);
        foreach ($iniArray[ucfirst($_SESSION['lang'])] as $directive => $value) {
            define($directive, $value);
        }
    }
}
?>