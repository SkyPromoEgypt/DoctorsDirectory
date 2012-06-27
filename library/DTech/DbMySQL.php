<?php

/**
 * Final Class Database is the Application Database Processor
 * using old fashion mysql functions (doesn't provide statements and transactions)
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
final class DbMySql
{

    /**
     * Database Connection Handler
     * @var DB Connection
     * @access private
     */
    private $connection;

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
    public function __construct ()
    {
        $this->openConnection();
        $this->magic_quotes_active = get_magic_quotes_gpc();
        $this->real_escape_string_exists = function_exists(
        "mysql_real_escape_string");
    }

    /**
     * Opens a connection to the database based on the constants pre-defined
     * in the application config file, selects a database, sets the server character
     * encoding and quiries the databse to set name to utf8
     * @return void
     */
    public function openConnection ()
    {
        $this->connection = mysql_connect(DB_SERVER, DB_USER, DB_PASS);
        if (! $this->connection) {
            die("DATABASE CONNECTION FAILED: " . mysql_error());
        } else {
            $this->_setCharacterEncoding();
            $db_select = mysql_select_db(DB_NAME, $this->connection);
            mysql_query("set names utf8");
            if (! $db_select) {
                die("DATABASE SELECTION FAILED: " . mysql_error());
            }
        }
    }

    /**
     * Closed the coonnection to the database
     * and unsets the connection property
     * @return void
     */
    public function closeConnection ()
    {
        if (isset($this->connection)) {
            mysql_close($this->connection);
            unset($this->connection);
        }
    }

    /**
     * Quiries the database for a specific
     * SQL statment
     * @param string $sql
     * @return resource
     */
    public function query ($sql)
    {
        $this->lastQuery = $sql;
        $result = mysql_query($sql, $this->connection);
        $this->confirmQuery($result);
        return $result;
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
            $value = mysql_real_escape_string($value);
        } else {
            if (! $this->magic_quotes_active) {
                $value = addslashes($value);
            }
        }
        return $value;
    }

    /**
     * Uses mysql_fetch_array to fetch
     * the arrays from the resource given back
     * from mysql_query
     * @param array $result_set
     * @return array
     */
    public function fetchArray ($resultSet, $resultArrayType = false)
    {
        return $resultArrayType ? mysql_fetch_array($resultSet,
        $resultArrayType) : mysql_fetch_array($resultSet);
    }

    /**
     * Uses mysql_fetch_Object to instantiate
     * objects from result
     * @param array $result_set the results from the query execusion
     * @param string $className the class name used for instantiation
     * @return Object instanceof $className
     */
    public function fetchObject ($resultSet, $className)
    {
        return mysql_fetch_object($resultSet, $className);
    }

    /**
     * Returns the number of rows processed
     * by this connection
     * @param integer $result_set
     * @return integer
     */
    public function numRows ($resultSet)
    {
        return mysql_num_rows($resultSet);
    }

    /**
     * Returns the last inserted Id (Primary Key) value
     * produced by this connection
     * @return integer | mixed
     */
    public function insertId ()
    {
        return mysql_insert_id($this->connection);
    }

    /**
     * Returns the number of affected
     * rows by this connection
     * @return integer
     */
    public function affectedRows ()
    {
        return mysql_affected_rows($this->connection);
    }

    /**
     * Confirms the $result Resource given
     * back from mysql_query if null given
     * the function prints the last SQL statment
     * used by the application
     * @param resource $result
     * @return if fails return string $output
     */
    private function confirmQuery ($result)
    {
        if (! $result) {
            $output = "<span>Database Query Failed</span><br/>";
            $output .= "<span>MySql Error:</span>" . mysql_error() . "<br/>";
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
     * List all the databases on the Server
     * @return array $databases
     */
    public function listDbs ()
    {
        $databases = array();
        $results = mysql_list_dbs();
        for ($i = 0; $row = $this->fetchArray($results, MYSQL_ASSOC); $i ++) {
            $databases[] = $row['Database'];
        }
        return $databases;
    }

    /**
     * Sets the Server character encoding
     * @param string $encoding
     */
    private function _setCharacterEncoding ($encoding = "utf8")
    {
        mysql_set_charset($encoding);
    }

    /**
     * Retrieves a string of the current status of the MySQL server
     * @return string
     */
    public function status ()
    {
        return mysql_stat();
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