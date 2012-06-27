<?php

/**
 * Final Class Database is the Application Database Processor
 * it preforms the following actions:
 * - Connection Instantiation
 * - Connection Termination
 * - Setting the Connection Encode
 * - Escapes Values before processing them to the DB
 * - Validates Quireies submitted to MySQL
 * - MySQL operations like mysql_query, mysql_fecth_arry and so on
 * @final Database Processor
 * @package DTEch Framework
 * @author Eng. Mohammed Yehia
 * @copyright Dahab TEchnology 2011
 * @version 1 Friday 8th July 2011, 11:30 pm
 */
final class Database
{

    /**
     * Singleton instance of Database Class
     * @var Object
     * @access private
     */
    private static $_dbHandler;

    /**
     * Self Object
     * @var Object Self
     * @access private
     */
    private $_connection;

    /**
     * Stores the last query
     * for Developer validation
     * if wrong syntax is used
     * @var string
     * @access public
     */
    public $lastQuery;

    /**
     * Check get_magic_quotes_active() method
     * @var boolean
     * @access private
     */
    private $magic_quotes_active;

    /**
     * Check real_escape_string_exists() method existance
     * @var boolean
     * @access private
     */
    private $real_escape_string_exists;

    /**
     * Class constructor initialized
     * the connection to the database and sets
     * magic_quotes_active and real_escape_string_exists
     * @return void
     */
    function __construct ()
    {
        $this->_connection = self::_openConnection();
        // TESTING PURPOSES: preFormatArray($this->_connection);
        $this->magic_quotes_active = get_magic_quotes_gpc();
        $this->real_escape_string_exists = function_exists(
        "mysqli_real_escape_string");
        $this->_setCharacterEncoding();
        $this->_connection->query("SET names utf8");
    }

    /**
     * Instantiate a singleton Object of the class
     * and returns it for later use
     * @return self Object
     */

    public static function getInstance()
    {
        $className = __CLASS__;
        if(null === self::$_dbHandler) {
            self::$_dbHandler = new $className;
        }
        return self::$_dbHandler;
    }

    /**
     * Opens a connection to the database based on the constants pre-defined
     * in the application config file, selects a database, sets the server character
     * encoding and quiries the databse to set name to utf8
     * @return void
     */
    private static function _openConnection ()
    {
        static $_connection = null;
        if (null === $_connection) {
            $_connection = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
            if (! $_connection) {
                die("DATABASE CONNECTION FAILED: " . $_connection->error);
            }
        }
        return $_connection;
    }

    /**
     * Closed the coonnection to the database
     * and unsets the connection property
     * @return void
     */
    public function closeConnection ()
    {
        $this->_connection->close();
        unset($this->_connection);
    }

    /**
     * Sets the Server character encoding
     * @param string $encoding
     */
    private function _setCharacterEncoding ($encoding = "utf8")
    {
        $this->_connection->set_charset($encoding);
    }

    /**
     * Sets the Server Character Encoding
     * @return void
     */
    public function characterSetName ()
    {
        $this->_connection->character_set_name();
    }

    /**
     * Retrieves a string of the current status of the MySQL server
     * @return string
     */
    public function status ()
    {
        return $this->_connection->stat();
    }

    /**
     * Escapes values being used in a SQL
     * staatment before processing it to MySQL
     * @param mixed $value
     * @return mixed $value
     */
    public function escapeValue ($value)
    {
        if ($this->real_escape_string_exists) {
            if ($this->magic_quotes_active) {
                $value = stripslashes($value);
            }
            $value = $this->_connection->real_escape_string($value);
        } else {
            if (! $this->magic_quotes_active) {
                $value = addslashes($value);
            }
        }
        return $value;
    }

    /**
     * Executes a query on the server
     * @param string $sql
     * @return Resource $result
     */
    public function query ($sql)
    {
        $this->lastQuery = $sql;
        $result = $this->_connection->query($sql);
        $this->_confirmQuery($result);
        return $result;
    }

    /**
     * Confirms the $result Resource given
     * back from mysql_query if null given
     * the function prints the last SQL statment
     * used by the application
     * @param resource $result
     * @return if fails return string $output
     */
    private function _confirmQuery ($result)
    {
        if (! $result) {
            $output = "<span>Database Query Failed</span><br/>";
            $output .= "<span>MySqli Error:</span>" . $this->_connection->error . "<br/>";
            $output .= "<span>Last Excuted Sql Statemnet is:</span> " .
             $this->lastQuery;
            $html = new HtmlPage();
            $errorText = $html->addElement("p", $output);
            $errorDiv = $html->addElement("div", $errorText,
            array("id" => "sqlErrorDiv"));
            echo $errorDiv;
            return false;
        }
    }

    /**
     * Returns the number of rows processed
     * by this connection
     * @param integer $result_set
     * @return integer
     */
    public function numRows ($resultSet)
    {
        return $resultSet->num_rows;
    }

    /**
     * Returns the number of affected
     * rows by this connection
     * @return integer
     */
    public function affectedRows ()
    {
        return $this->_connection->affected_rows;
    }

    /**
     * Returns the last inserted Id (Primary Key) value
     * produced by this connection
     * @return integer | mixed
     */
    public function insertId ()
    {
        return $this->_connection->insert_id;
    }

    /**
     * Uses mysql_fetch_array to fetch
     * the arrays from the resource given back
     * from mysql_query
     * @param array $result_set
     * @return array
     */
    public function fetchArray ($resource, $resultArrayType = MYSQLI_BOTH)
    {
        return $resource->fetch_array($resultArrayType);
    }

    /**
     * Uses mysql_fetch_Object to instantiate
     * objects from result
     * @param array $result_set the results from the query execusion
     * @param string $className the class name used for instantiation
     * @return Object instanceof $className
     */
    public function fetchObject ($resource, $className)
    {
        return $resource->fetch_object($className);
    }

    /**
     * Class Destructor disconnects from
     * the database and unsets the connection
     * property
     * @return void
     */
    public function __destruct ()
    {
        //$this->closeConnection();
    }
}