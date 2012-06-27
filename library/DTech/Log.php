<?php

/**
 * Logging class
 * @package DTEch Framework
 * @author Eng. Mohammed Yehia
 * @copyright Dahab TEchnology 2011
 * @version 1 Saturday 9th July 2011, 4:20 pm
 */
class Log extends ModelAbstract implements IModel
{

    /**
     * Log Object Id
     * @var integer
     * @access public
     */
    public $id;

    public $username;

    public $action;

    public $disc;

    public $created;

    protected static $_tableName = "log";

    /**
     * This property is used to be called in the abstract class
     * to define the class properties that is used to save records
     * to the database
     * @access protected
     * @var array
     */
    protected static $_dbFields = array("id", "username", "action", "disc",
    "created");

    /**
     * Class Name to be called statically using late static binding
     * in the abstract class
     * @access protected
     * @var __CLASS__
     */
    protected static $_className = __CLASS__;

    /**
     * Class constructor excutes the parent
     * constructor to initialize the Database Object
     * Enter description here ...
     */
    public function __construct ()
    {
        parent::__construct();
    }

    public function __call ($name, $args)
    {
        $methodPrefix = substr($name, 0, 3);
        $methodProperty = strtolower($name[3]) . substr($name, 4);
        switch ($methodPrefix) {
            case "get":
                return $this->$methodProperty;
                break;
            case "set":
                if (count($args) == 1) {
                    $this->$methodProperty = $args[0];
                } else {
                    throw new \Exception(
                    "The Set method supports only 1 argument");
                }
                break;
            default:
                throw new \Exception("The method doesn't support this prefix");
                break;
        }
    }

    public function __toString ()
    {
        return createdToText($this->created) . " : " . $this->disc . "<br/>";
    }

    public function __get ($classProperty)
    {
        return $this->$classProperty;
    }

    public function __set ($classProperty, $propertyValue)
    {
        $this->$classProperty = $propertyValue;
    }

    /**
     * Instatiate and save a log item to the database.
     * @param string $user
     * @param string $action
     * @param string $disc
     * @return Boolean.
     */
    public static function createLog ($user, $action, $discribtion)
    {
        $log = new self();
        $log->username = $user;
        $log->action = $action;
        $log->disc = $discribtion;
        $log->created = strftime("%Y-%m-%d %H:%M:%S", time());
        if ($log->save()) {
            return true;
        }
    }

    /**
     * Writes log to a text file
     * @param string $action
     * @param string $discribtion
     * @return void
     */
    public static function logToFile ($action, $discribtion = "")
    {
        $logfile = APPLICATION_PATH . DS . "../logs" . DS . "errors.txt";
        $fileExists = file_exists($logfile) ? true : false;
        if ($fileExists) {
            $handle = fopen($logfile, 'a');
            try {
                if (! $handle) {
                    throw new Exception("Couldn't open file for writing.", 125);
                } else {
                    $timestampe = strftime("%d/%m/%Y %H:%M:%S", time());
                    $content = "{$action} @ {$timestampe} : {$discribtion}\n";
                    fwrite($handle, $content);
                    fclose($handle);
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        } else {
            echo "File Doesn't Exists";
        }
    }

    /**
     * View the Log
     * @param string $actionName
     * @return string Html Form $form;
     */
    public static function viewLog ($actionName = "Error")
    {
        // FIXME: AN Unfinished Method
        $sql = array("fields" => "action", "results" => false,
        "groupBy" => "action", "orderBy" => "action", "order" => "ASC");
        $actionGroup = self::findInDb($sql);
        if ($actionGroup) {
            $output = new HtmlPage();
            $options = array();
            foreach ($actionGroup as $action) {
                $options[] = $output->addElement("option", $action['action'],
                array("id" => "form_" . $action['action']));
            }
            $select = $output->addElement("label",
            "Please Choose a log to view", array("for" => "selectLog")) .
             $output->addElement("select", $options, array("id" => "selectLog"));
            $form = $output->addElement("form", $select,
            array("method" => "post", "action" => "", "class" => "form"));
            return $form;
        }
    }
}