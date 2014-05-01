<?php
namespace Pkj\AutomationAI;


use Monolog\Logger;

class DB {

    private $pdo;
    private $logger;
    
    private $checkQueueStatement;
    private $deleteStatement;
    private $settingExistsStatement;
    private $settingUpdateStatement;
    private $settingInsertStatement;
    
    private $eventExistsStatement;
    private $eventInsertStatement;
    

    public $settings = array();

    public function __construct ($dsn, $username, $password, $driver_options, Logger $logger) {
        $this->dsn = getenv('PDO_DSN') ?: $dsn;
        $this->username = $username;
        $this->password = $password;
        $this->driver_options = $driver_options;
        $this->logger = $logger;
        $this->connect();
        

    }
    
    
    public function connect () {
        $this->pdo = new \PDO($this->dsn, $this->username, $this->password, array_merge(
            $this->driver_options, array( \PDO::ATTR_PERSISTENT => true)));
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        $query = $this->pdo->prepare("set session wait_timeout=10000,interactive_timeout=10000,net_read_timeout=10000");
        $query->execute();






        $this->checkQueueStatement = $this->pdo->prepare("SELECT * FROM eventqueue ORDER BY issued_ts ASC");
        $this->deleteStatement = $this->pdo->prepare("DELETE FROM eventqueue WHERE id=:id");


        $this->getSettingsStatement = $this->pdo->prepare("SELECT * FROM settings");

        $this->settingExistsStatement = $this->pdo->prepare("SELECT * FROM settings WHERE `key`=:k");

        $this->settingUpdateStatement = $this->pdo->prepare("UPDATE settings SET `value`=:v WHERE `key`=:k");

        $this->settingInsertStatement = $this->pdo->prepare("INSERT INTO settings VALUES (:k, :v)");

        
        $this->eventExistsStatement = $this->pdo->prepare("SELECT * FROM eventqueue WHERE `event`=:k");
        $this->eventInsertStatement = $this->pdo->prepare("INSERT INTO eventqueue (`event`, `args`) VALUES (:k, :v)");
        
        
    }

    public function getSetting($key) {
        return $this->settings[$key];
    }

    public function updateSetting ($key,$val) {
        $query = $this->settingExistsStatement;
        $query->bindParam(':k', $key, \PDO::PARAM_STR);
        $query->execute();
        if ($query->rowCount()) {
            $do = $this->settingUpdateStatement;
        } else {
            $do = $this->settingInsertStatement;
        }
        $do->bindParam(':k', $key, \PDO::PARAM_STR);
        $do->bindParam(':v', $val, \PDO::PARAM_STR);
        return $do->execute();
    }
    
    
    /**
     * Returns false IF event is already queued...
     * 
     * @param unknown_type $key
     * @param unknown_type $args
     * @return boolean
     */
    public function addEvent($key, $args = array()) {
    	$query = $this->eventExistsStatement;
    	$query->bindParam(':k', $key, \PDO::PARAM_STR);
    	$query->execute();
    	if ($query->rowCount()) {
    		return false;
    	} else {
    		$do = $this->eventInsertStatement;
    	}
        $val = json_encode($args);
    	
    	$do->bindParam(':k', $key, \PDO::PARAM_STR);
    	$do->bindParam(':v', $val, \PDO::PARAM_STR);
    	return $do->execute();
    }
    
    
    
    public function checkQueue (callable $settingCallback, callable $eventCallback) {

        try {
            $this->getSettingsStatement->execute();
            $settings = $this->getSettingsStatement->fetchAll(\PDO::FETCH_ASSOC);
            $newsettings = array();
            foreach($settings as $row) {
                $newsettings[$row['key']] = substr($row['value'], 0, 1) == '{' ? json_decode($row['value']) : $row['value'];
            }
            if (empty($this->settings)) {
                $diff = array();
            } else {
                $diff = $this->arrayRecursiveDiff($newsettings, $this->settings);
            }
            if (!empty($diff)) {
                $this->logger->addDebug("Settings from DB changed, diff is", array('diff' => $diff));
                foreach($diff as $k => $v) {
                    $settingCallback("setting:$k:change", array($v));
                }
            }
            $this->settings = $newsettings;

            $query = $this->checkQueueStatement;
            $query->execute();
            $rows = $query->fetchAll(\PDO::FETCH_ASSOC);
            foreach($rows as $row) {
            	$this->logger->addDebug("Caught event {$row['event']}.", $row);

                $args = $row['args'] ? json_decode($row['args'], true) : array();
                $eventCallback($row['event'], $args);

                $id = $row['id'];
                $this->deleteStatement->bindParam(':id', $id, \PDO::PARAM_INT);
                $this->deleteStatement->execute();
            }
        } catch (\PDOException $e) {
            $this->logger->addCritical($e->getMessage());
            $this->connect();
        }
    }

    public function arrayRecursiveDiff($aArray1, $aArray2) {
        $aReturn = array();

        foreach ($aArray1 as $mKey => $mValue) {
            if (array_key_exists($mKey, $aArray2)) {
                if (is_array($mValue)) {
                    $aRecursiveDiff = $this->arrayRecursiveDiff($mValue, $aArray2[$mKey]);
                    if (count($aRecursiveDiff)) { $aReturn[$mKey] = $aRecursiveDiff; }
                } else {
                    if ($mValue != $aArray2[$mKey]) {
                        $aReturn[$mKey] = $mValue;
                    }
                }
            } else {
                $aReturn[$mKey] = $mValue;
            }
        }
        return $aReturn;
    }

} 