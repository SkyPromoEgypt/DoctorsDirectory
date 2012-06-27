<?php
/**
 * Application Bootstraper Class is responsible
 * for initializing the application by setting
 * the App configuration from the config file, settings
 * the PHP directives based on the appropriate environment
 * settins and setting the include path for the DTech Framework
 * @package DTEch Framework
 * @author Eng. Mohammed Yehia
 * @copyright Dahab TEchnology 2011
 * @version 1 Sunday 17th July 2011, 7:30 am
 */
final class DTech_Application_Bootstrap
{

    /**
     * Application Confirguration
     * file path
     * @access private
     * @var string
     */
    private $_appConfig;

    /**
     * Application Environment used
     * to determine the way the application
     * acts in different environments
     * @access private
     * @var string
     */
    private $_appEnvironment;

    /**
     * XXController Object
     * @access private
     * @var Object XXController
     */
    private $_frontController;

    /**
     * The Application interface language
     * @access private
     * @var string
     */
    private $_interfaceLanguage = "en-us";

    /**
     * Class constructor setting the
     * following application configurations:
     * - Application Environment
     * - Application Configuraton file path
     * - Application Directives
     * - PHP Directives
     * - PHP Include Paths
     * - Session Instialization
     */
    function __construct ($config, $environment)
    {
        $this->_setAppConfig($config);
        $this->_setAppEnvironment($environment);
        $this->_setAppDirectives();
        $this->_setPHPDirectives();
        $this->_setIncludePaths();
        $this->_callHelpers();
        $session = new Session();
        $_SESSION['lang'] = 'english';
    }

    /**
     * Set the application environment
     * @param string $environment
     * @return void
     */
    private function _setAppEnvironment ($environment)
    {
        $this->_appEnvironment = $environment;
    }

    /**
     * Set the application configuration
     * file path
     * @param string $config
     * @return void
     */
    private function _setAppConfig ($config)
    {
        $this->_appConfig = $config;
    }

    /**
     * Setting the PHP Directives by
     * parsing the application.ini file
     * and sets the defined directives
     * @return void
     */
    private function _setPHPDirectives ()
    {
        $iniArray = parse_ini_file($this->_appConfig, true);
        foreach ($iniArray[$this->_appEnvironment] as $directive => $value) {
            ini_set($directive, $value);
        }
    }

    /**
     * Settings the include path for
     * the application to be able to
     * call classes automatically
     * @return void
     */
    private function _setIncludePaths ()
    {
        define('MODELS_PATH', realpath("../application/models"));
        define('CONTROLLERS_PATH', realpath("../application/controllers"));
        define('LIBRARY_PATH', realpath("../library/DTech"));
        $paths = array(get_include_path(), APPLICATION_PATH, MODELS_PATH,
        CONTROLLERS_PATH, LIBRARY_PATH);
        set_include_path(implode(PATH_SEPARATOR, $paths));
    }

    /**
     * Setting the Application Directives by
     * parsing the application.ini file
     * and sets the defined directives
     * @return void
     */
    private function _setAppDirectives ()
    {
        $iniArray = parse_ini_file($this->_appConfig, true);
        foreach ($iniArray["application"] as $directive => $value) {
            define($directive, $value);
        }
    }

    /**
     * Bootstraping the Application by loading the proper ControllerClass
     * and Action required for rendering the view
     * @return void
     */
    public function bootstrap ()
    {
        $this->_loadProperControllerAction();
        $this->_frontController->dispatch();
    }

    /**
     * Formats the URI and extracts
     * Controller/Action names
     * @param boolean $action if set to true will get the
     * action name
     * @return string $uri[index]
     */
    private function _setControllerAction ($getAction = false)
    {
        $uri = strtolower($_SERVER['REQUEST_URI']);
        $uri = str_replace(SITENAME, "", $uri);
        $uri = explode("/", $uri);
        $controller = $uri[1];
        $action = $uri[2];
        preg_match_all("/([a-z]+)(\\?|\\/(.*))?/", $action, $matches);
        $action = $matches[1][0];
        return $getAction ? $action : $controller;
    }

    /**
     * Extracts the Controller name
     * and the Action name from the URI
     * and instantiate the appropriate controller
     * @return void
     */
    private function _loadProperControllerAction ()
    {
        $controllerName = $this->_setControllerAction() ? $this->_setControllerAction() : "index";
        $actionName = $this->_setControllerAction(true) ? $this->_setControllerAction(
        true) : "index";
        $controllerClassToLoad = $this->_isControllerExists($controllerName);
        if ($controllerClassToLoad &&
         $this->_isActionExists($controllerClassToLoad, $actionName)) {
            if (! $this->_frontController instanceof $controllerClassToLoad &&
             $this->_frontController !== false) {
                $this->_frontController = new $controllerClassToLoad(
                $controllerName, $actionName);
            }
        } else {
            $this->_frontController = new IndexController("index", "notfound");
        }
    }

    /**
     * Creates an array of the application controllers and test the $controllerName
     * against this array if exists it returns the proper controllerClass to the _loadProperControllerAction
     * to instantiate an object of it
     * @param string $controllerName
     * @return string $controllerClassToLoad | boolean (false)
     */
    private function _isControllerExists ($controllerName)
    {
        $controllerToLookFor = strtolower($controllerName);
        $appControllers = array();
        if (is_dir(APPLICATION_PATH . DS . "controllers")) {
            $handle = opendir(APPLICATION_PATH . DS . "controllers");
            if ($handle) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        $appControllers[strtolower($file)] = $file;
                    }
                }
            } else {
                throw new Exception(
                "Cannot Open Controllers Folder, Please check the folder permissions.");
            }
        } else {
            throw new Exception("Controllers Folder Doesn't Exist");
        }
        if (! empty($appControllers)) {
            if (array_key_exists($controllerToLookFor . "controller.php",
            $appControllers)) {
                // extracts .php extension from the file name
                // and return the controller class name
                preg_match_all("/(.*)\\.php/",
                $appControllers[$controllerToLookFor . "controller.php"],
                $matches);
                $controllerClassToLoad = $matches[1][0];
                return $controllerClassToLoad;
            }
        } else {
            throw new Exception(
            "Currently you don't have any controllers to instantiate");
        }
        return false;
    }

    /**
     * Checks if the the action name method is
     * a method of the controller name class or not
     * @param string $controllerName
     * @param string $actionName
     * @return boolean
     */
    private function _isActionExists ($controllerName, $actionName)
    {
        $actionName .= "Action";
        return (method_exists($controllerName, $actionName)) ? true : false;
    }

    /**
     * Load Helpers
     * @return void
     */
    private function _callHelpers ()
    {
        if (is_dir(APPLICATION_PATH . DS . "helpers")) {
            $handle = opendir(APPLICATION_PATH . DS . "helpers");
            if ($handle) {
                while (false !== ($file = readdir($handle))) {
                    if ($file != "." && $file != "..") {
                        require_once (APPLICATION_PATH . DS . "helpers" . DS .
                         $file);
                    }
                }
            } else {
                throw new Exception("Cannot open helpers directory.");
            }
        } else {
            throw new Exception("Helpers Folder if not a folder.");
        }
    }
}