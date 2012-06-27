<?php

class Session
{

    /**
     * Expiration (Time for the session to remain alive)
     * @access expiration
     * @var int
     */
    private $_tableName = "sessions";

    /**
     * Database Object
     * @access protected
     * @var Object
     */
    private $_database;

    /**
     * Class constructor sets the session_set_save_handler to use
     * the defined method to handle session
     * data in the database and starts the session
     * @return void
     */
    function __construct ()
    {
        $this->_database =& Database::getInstance();
        $this->garbageCollector();
        session_set_save_handler(array($this, 'openSession'),
        array($this, 'closeSession'), array($this, 'readSession'),
        array($this, 'writeSession'), array($this, 'destroySession'),
        array($this, 'garbageCollector'));
        session_start();
    }

    /**
     * Check if the session id exists in the session table
     * @param string $SID
     * @return boolean
     */
    private function _isSessionIdExists ($SID)
    {
        $sql = "SELECT * FROM " . $this->_tableName;
        $sql .= " WHERE SID = '" . $this->_database->escapeValue($SID);
        $sql .= "' LIMIT 1";
        $result = $this->_database->query($sql);
        return ($this->_database->numRows($result) == 1) ? true : false;
    }

    /**
     * session_open equivalent
     * @param string $session_path
     * @param string $session_name
     */
    public function openSession ($session_path, $session_name)
    {
        return true;
    }

    /**
     * session_close equivalent
     */
    public function closeSession ()
    {
        return true;
    }

    /**
     * session_read equivalent
     * @param string $SID
     */
    public function readSession ($SID)
    {
        $sql = "SELECT value FROM " . $this->_tableName;
        $sql .= " WHERE SID = '" . $this->_database->escapeValue($SID);
        $sql .= "' AND expiration > " . time();
        $result = $this->_database->query($sql);
        if ($this->_database->numRows($result)) {
            $data = $this->_database->fetchArray($result);
            $data = $data['value'];
            return $data;
        } else {
            return "";
        }
    }

    /**
     * session_write equivalent
     * @param string $SID
     * @param string $value
     */
    public function writeSession ($SID, $value)
    {
        $lifeTime = get_cfg_var("session.gc_maxlifetime");
        $expiration = time() + $lifeTime;
        if ($this->_isSessionIdExists($SID)) {
            $sql = "UPDATE " . $this->_tableName . " SET expiration = '" .
             $this->_database->escapeValue($expiration) . "', value = '" .
             $this->_database->escapeValue($value) . "' WHERE SID = '" .
             $this->_database->escapeValue($SID) . "' AND expiration >" . time();
            $result = $this->_database->query($sql);
        } else {
            $sql = "INSERT INTO " . $this->_tableName;
            $sql .= " VALUES('" . $this->_database->escapeValue($SID) . "', '" .
             $this->_database->escapeValue($expiration) . "', '" .
             $this->_database->escapeValue($value) . "')";
            $result = $this->_database->query($sql);
        }
    }

    /**
     * session_destroy equivalent
     * @param string $SID
     */
    public function destroySession ($SID)
    {
        $sql = "DELETE FROM " . $this->_tableName . " WHERE SID = '" .
         $this->_database->escapeValue($SID) . "'";
        $result = $this->_database->query($sql);
        unset($_SESSION);
        session_destroy();
    }

    /**
     * session_garbage_collect equivalent
     * @param string $session_path
     * @param string $session_name
     */
    public function garbageCollector ()
    {
        $lifetime = get_cfg_var("session.gc_maxlifetime");
        $difference = time() - $lifetime;
        $sql = "DELETE FROM " . $this->_tableName . " WHERE expiration < " .
         $difference;
        $result = $this->_database->query($sql);
        return $this->_database->affectedRows();
    }
}