<?php
require_once("exceptions.php");

class Entity {
    private static $deleteQuery = 'DELETE FROM "%1$s" WHERE %1$s_id=?';
    private static $insertQuery = 'INSERT INTO "%1$s" (%2$s) VALUES (%3$s)';
    private static $listQuery   = 'SELECT * FROM "%s"';
    private static $selectQuery = 'SELECT * FROM "%1$s" WHERE %1$s_id=?';
    private static $updateQuery = 'UPDATE "%1$s" SET %2$s WHERE %1$s_id=?';
    private static $childrenQuery = 'SELECT * FROM "%1$s" WHERE %2$s_id=?';
    private static $siblingQuery = 'SELECT * FROM "%1$s" NATURAL JOIN "%2$s" WHERE %3$s_id=?';

    private static $db = null;

    public $fields   = [];
    private $loaded   = false;
    private $modified = false;
    private $id       = null;
    private $class    = null;
    private $table    = null;

    public function __construct($id=null) {
        self::initDatabase();

        $this->id = $id;
        $this->class = get_class($this);
        $this->table = lcfirst($this->class);
    }

    public function __get($name) {
        if ( in_array($name, $this->columns) && !$this->modified ) {
            $this->load();
            
            return $this->getColumn($name);
        }
        if ( in_array($name, $this->parents) ) {
            if ( !$this->modified ) {
               $this->load(); 
            }            
            return $this->getParent($name);
        }
        if ( isset($this->children[$name]) ) {         
            return $this->getChildren($this->children[$name]);
        }
        if ( isset($this->siblings[$name]) ) {         
            return $this->getSiblings($this->siblings[$name]);
        }
        throw new AttributeException();
    }

    public function __set($name, $value) {
        if (  in_array($name, $this->parents) ) {
            $this->setParent($name, $value);
        } else if ( in_array($name, $this->columns) ) {
            $this->setColumn($name, $value);
            $this->modified = true;
        } else {
            throw new AttributeException();
        }
    }

    private static function getObjects($query, $name, $args=null) {
        $objects = array();
        $query->execute($args);
        $rows = $query->fetchall(PDO::FETCH_ASSOC);

        foreach ( $rows as $row) {
           $object = new $name($row[lcfirst($name) . '_id']);

           $object->fields = $row;
           $object->loaded = true;

           $objects[] = $object;
        }

        return $objects;
    }

    public function delete() {
        if ( $this->id === null ) {
            throw new InvalidOperationException();            
        }
        $query = self::$db->prepare(sprintf(self::$deleteQuery, $this->table));
        $this->execute($query, array($this->id));
    }

    public function getColumn($name) {
        return $this->fields[$this->table . '_' . $name];
    }


    public function getChildren($name) {
        $query = self::$db->prepare(sprintf(self::$childrenQuery, lcfirst($name), $this->table));

        return self::getObjects($query, $name, array($this->id));
    }

    public function getParent($name) {
        $cls = ucfirst($name);
        $obj = new $cls($this->fields[$name . '_id']);

        return $obj;
    }

    public function getSiblings($name) {
        if ( strcasecmp($this->table, $name) <= 0 ) {
            $relationTable = $this->table . '__' . lcfirst($name);
        } else {
            $relationTable = lcfirst($name) . '__' . $this->table;
        }
        
        $query = self::$db->prepare(sprintf(self::$siblingQuery, 
            lcfirst($name),
            $relationTable,
            $this->table)
        );
        return self::getObjects($query, $name, array($this->id));
    }

    public function save() {
        if ( isset($this->id) ) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    public function setColumn($name, $value) {
        $this->fields[$this->table . '_' . $name] = $value;
    }


    public function setParent($name, $parent) {
        if ( $parent instanceof Entity ) {
            $this->fields[$name . '_id'] = $parent->id;
        } else {
            $this->fields[$name . '_id'] = $parent;
        }
    }

    public static function all() {
        self::initDatabase();

        $class = get_called_class();
        $query = self::$db->prepare(sprintf(self::$listQuery, lcfirst($class)));

        return self::getObjects($query, $class);
    }

    public static function setDatabase(PDO $db) {
        self::$db = $db;
    }

    private function execute($query, $args=null) {
        try {
            $query->execute($args);
        } catch (Exception $e) {
            self::$db->rollBack();
        }
    }

    private function insert() {
        $args = array();
        $columnsName = '';

        foreach ( $this->fields as $key => $value ) {
            $value = str_replace('\'', "''", $value);

            if ( $columnsName == '' ) {
                $columnsName .= $key;
            } else {
                $columnsName .= ', ' . $key;;
            }

            $args[] = $value;
        }

        $statement = sprintf(self::$insertQuery,
            $this->table,
            $columnsName,
            implode(', ', array_fill(0, count($args), "?"))
        );
        $query = self::$db->prepare($statement);
        $this->execute($query, $args);
        $table = $this->table . '_' . $this->table . '_id_seq';
        $this->id = self::$db->lastInsertId($table);
    }

    private function load() {
        if ( $this->id === null ) {
            throw new InvalidOperationException();            
        }
        if ( !$this->loaded ) { 
            $statement = sprintf(self::$selectQuery, $this->table);
            $query = self::$db->prepare($statement);
            
            $this->execute($query, array($this->id));
            $this->fields = $query->fetch(PDO::FETCH_ASSOC);
            $this->loaded = true;
        }
    }

    public function update() {
        $columns = '';
        $args = array();

        foreach ( $this->fields as $key => $value ) {
            $value = str_replace('\'', "''", $value);

            if ( $columns == '' ) {
               $columns .= $key . '=?'; 
            } else {
                $columns .= ', ' . $key . '=?';
            }
            $args[] = $value;
        }
        $args[] = $this->id;
        $statement = sprintf(self::$updateQuery, $this->table, $columns);
        $query = self::$db->prepare($statement);
        $this->execute($query, $args);
    }

    private static function initDatabase() {
        if ( self::$db === null ) {
            throw new DatabaseException();
        }
        self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
}
