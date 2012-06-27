<?php

/**
 * Abstract Class ModelAbstract is a Database Abstraction
 * Class and Helper
 * @abstract Model Abstract
 * @package DTEch Framework
 * @author Eng. Mohammed Yehia
 * @copyright Dahab TEchnology 2011
 * @version 1 Friday 8th July 2011, 11:30 pm
 */
abstract class ModelAbstract
{
    /**
     * The Class Database Table Name
     * @access protected
     * @var string
     */
    protected static $_tableName;

    /**
     * This property is used to be called in the abstract class
     * to define the class properties that is used to save records
     * to the database
     * @access protected
     * @var array
     */
    protected static $_dbFields = array();

    /**
     * Class Name to be called statically using late static binding
     * in the abstract class
     * @access protected
     * @var __CLASS__
     */
    protected static $_className;

    /**
     * Database Object
     * @access protected
     * @var Object
     */
    protected static $_database;

    /**
     * The last Excuted Sql Statements
     * @access protected
     * @var array
     */
    protected static $_sqlStatements = array();

    /**
     * Class constructor get an instance of
     * the database object handler
     * @return void
     */
    function __construct()
    {
        self::$_database =& Database::getInstance();
    }

    /**
     * Finds records in Database that matches the gived
     * Sql Array so that the function calls a private method
     * to build the SQL Statement from this array and processes
     * it to get back the results in your choice of format.
     * @param array $sql
     * @example  array ("items"     => "dbField, SUM(dbField)",
     * "condition" => array("dbField" => "value),
     * "groupBy"   => "dbField",
     * "OrderBy"   => "dbField",
     * "order"     => "ASC",
     * "limit"     => 30,
     * "results"	=> false
     * );
     * The results by default set to true unless you set it
     * to false and this is to switch between an array of
     * instantiated objects result or an array or arrays result
     * or you can simply type the sql statment directly and the function will still do the
     * job for you like array ("sql" => "SELECT * FROM table WHERE id = 1");
     * @return array $resultsArray | boolean false
     */
    public static function findInDb (array $sql)
    {
        self::$_database =& Database::getInstance();
        $query = self::_buildSqlStatement($sql);
        $results = (false === $sql['results']) ? false : true;
        $resultArray = self::_excuteQuery($query, $results);
        if ($sql['limit'] && $sql['limit'] == 1) {
            return ! empty($resultArray) ? array_shift($resultArray) : false;
        }
        return ! empty($resultArray) ? $resultArray : false;
    }

    /**
     * Find the objects by a specific sql query
     * @param string $sql
     * @param boolean $instantiation
     * @return ArrayObject
     */
    private static function _excuteQuery ($sql = "", $instantiation = true)
    {
        $resultsSet = self::$_database->query($sql);
        $objectArray = array();
        for ($i = 0; $row = self::$_database->fetchArray($resultsSet); $i ++) {
            if ($instantiation) {
                $objectArray[] = static::_instantiate($row);
            } else {
                $objectArray[] = $row;
            }
        }
        return ! empty($objectArray) ? $objectArray : false;
    }

    /**
     * Build a valid SQL Statement
     * @param array $sql
     * @throws Exception mixed exceptions
     * @return string $query
     */
    private static function _buildSqlStatement (array $sql)
    {
        if ($sql['sql']) {
            $query = $sql['sql'];
        } else {
            if (! $sql['fields']) {
                throw new Exception(
                "You have to suuply one Database field or more to build the Sql Statment.");
            } else {
                if (is_array($sql['fields'])) {
                    $itemsToQueryFor = implode(", ", $sql['fields']);
                } else {
                    $itemsToQueryFor = $sql['fields'];
                }
            }
            if ($sql['condition']) {
                if (! is_array($sql['condition'])) {
                    throw new Exception(
                    "Conditions must be set in array format.");
                } else {
                    $condition = " WHERE ";
                    $pairs = array();
                    foreach ($sql['condition'] as $key => $value) {
                        if (property_exists(static::$_className, $key)) {
                            if (is_array($value)) {
                                foreach ($value as $operator => $theValue) {
                                    $pairs[] = $key . " " . $operator . " " . self::$_database->escapeValue(
                                    $theValue);
                                }
                            } else {
                                $pairs[] = $key . " = '" . self::$_database->escapeValue(
                                $value) . "'";
                            }
                        } else {
                            throw new Exception(
                            "$key is not a Class " . __CLASS__ . " attribute");
                        }
                    }
                    $condition .= join(" AND ", $pairs);
                }
            }
            if ($sql['groupBy']) {
                $groupByItems = "GROUP BY (" . $sql['groupBy'] . ")";
            }
            if ($sql['orderBy']) {
                $orderCondition = "ORDER BY (" . $sql['orderBy'] . ") " .
                 $sql['order'];
            }
            if ($sql['limit']) {
                $limitCondition = "LIMIT " . $sql['limit'];
            }
            $query = "SELECT " . $itemsToQueryFor . " FROM " .
             static::$_tableName;
            if ($condition) {
                $query .= " " . $condition . " ";
            }
            if ($groupByItems) {
                $query .= " " . $groupByItems . " ";
            }
            if ($orderCondition) {
                $query .= " " . $orderCondition . " ";
            }
            if ($limitCondition) {
                $query .= " " . $limitCondition . " ";
            }
        }
        self::$_sqlStatements[] = $query;
        return $query;
    }

    /**
     * Testing purposes print the built query
     * for review and check errors if exists
     * @return string $div;
     */
    public static function queryForReview ()
    {
        $queryDiv = new HtmlPage();
        $headerText = ucfirst(APPLICATION_ENV) .
         " Mode | The Last Excuted Sql Statement/s:";
        $header = $queryDiv->addElement("h1", $headerText);
        $queryText = "";
        foreach (self::$_sqlStatements as $query) {
            $queryText .= $queryDiv->addElement("p", $query);
        }
        $div = $queryDiv->addElement("div", array($header, $queryText),
        array("class" => "sqlStatement"));
        return $div;
    }

    /**
     * Instantiate an object of a specific class based on
     * the pre-defined attributes array dbFields
     * @param array $record
     * @return Object
     */
    private static function _instantiate ($record)
    {
        $object = new static::$_className();
        foreach ($record as $attribute => $value) {
            if ($object->_hasAttribute($attribute)) {
                $object->$attribute = $value;
            }
        }
        return $object;
    }

    /**
     * Checks wither an attribute exists on a class or not
     * @return boolean
     */
    private function _hasAttribute ($attribute)
    {
        $objectVars = $this->_attributes();
        return array_key_exists($attribute, $objectVars);
    }

    /**
     * Create an un-sanitized array of the class attributes based on the
     * DB Fields to avoid using all of the class properties
     * @return array
     */
    protected function _attributes ()
    {
        $attributes = array();
        foreach (static::$_dbFields as $field) {
            if (property_exists($this, $field)) {
                $attributes[$field] = $this->$field;
            }
        }
        return $attributes;
    }

    /**
     * Create a sanitized array of the class attributes based on the
     * DB Fields to avoid using all of the class properties
     * @return array
     */
    protected function _sanitizedAttributes ()
    {
        $cleanAttributes = array();
        foreach ($this->_attributes() as $key => $value) {
            $cleanAttributes[$key] = self::$_database->escapeValue($value);
        }
        return $cleanAttributes;
    }

    /**
     * Saves the object to the database based on the id attribute
     * @return boolean
     */
    public function save ()
    {
        return isset($this->id) ? $this->update() : $this->create();
    }

    /**
     * Creates an object and save it to the database
     * @return boolean
     */
    public function create ()
    {
        $attributes = $this->_sanitizedAttributes();
        $sql = "INSERT INTO " . static::$_tableName . " (";
        $sql .= join(", ", array_keys($attributes));
        $sql .= ") VALUES ('";
        $sql .= join("', '", array_values($attributes));
        $sql .= "')";
        if (self::$_database->query($sql)) {
            $this->id = self::$_database->insertId();
            return true;
        } else {
            return false;
        }
    }

    /**
     * Updates the object and save it to the database
     * @return boolean
     */
    public function update ()
    {
        $attributes = $this->_sanitizedAttributes();
        $attribute_pairs = array();
        foreach ($attributes as $attribute => $value) {
            $attribute_pairs[] = "{$attribute} = '{$value}'";
        }
        $sql = "UPDATE " . static::$_tableName . " SET ";
        $sql .= join(", ", $attribute_pairs);
        $sql .= " WHERE id = " . self::$_database->escapeValue($this->id);
        self::$_database->query($sql);
        return (self::$_database->affectedRows() == 1) ? true : false;
    }

    /**
     * Deletes the object from the database
     * @return boolean
     */
    public function delete ()
    {
        $sql = "DELETE FROM " . static::$_tableName;
        $sql .= " WHERE id = " . self::$_database->escapeValue($this->id);
        $sql .= " LIMIT 1";
        self::$_database->query($sql);
        return (self::$_database->affectedRows() == 1) ? true : false;
    }

    /**
     * Truncates (Empty) a table
     * @return boolean
     */
    public function truncate ()
    {
        $sql = "TRUNCATE TABLE " . static::$_tableName;
        return (self::$_database->query($sql)) ? true : false;
    }
}