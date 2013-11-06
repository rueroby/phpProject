<?php
namespace dbTable;

class TableColumn {
    protected $name; // actual table column name
    protected $type;
    protected $phpName;
    protected $isPrimaryKey;
    protected $isAutoincrement;
    
    protected $dbTypes = array("boolean", "int", "varchar", "datetime");
    protected $sqlite2Types = array("VARCHAR", "NVARCHAR", "TEXT", "INTEGER", "FLOAT", "BOOLEAN", "CLOB", "BLOB", "TIMESTAMP","NUMERIC", "VARYING CHARACTER", "NATIONAL VARYING CHARACTER");
    protected $sqlite3Types = array("NULL", "INTEGER", "REAL", "TEXT", "BLOB");

    private function phpName($str){
        $parts = explode("_", $str);
        if (count($parts) > 1){
            for($i=1; $i < count($parts); $i++){
                $parts[$i] = ucfirst($parts[$i]);
            }
        }
        return join($parts);
    }

    private function isValidType($type){
        for($i = 0; $i < count($this->dbTypes); $i++){
//            if ($type == $this->dbTypes[$i]) return true;
            if (strncmp($type, $this->dbTypes[$i], strlen($this->dbTypes[$i])) == 0) return true;
        }
        
        return false;
    }
    
    public function __construct($name, $type){
        $this->setColumnName($name);
        $this->setColumnType($type);
        $this->isPrimaryKey = false;
        $this->isAutoincrement = false;
    }
    
    public function getColumnName(){ return $this->name; }
    public function getColumnType(){ return $this->type; }
    public function getPhpName(){ return $this->phpName; }
    public function isPrimaryKey() { return $this->isPrimaryKey; }
    public function isAutoincrement(){ return $this->isAutoincrement; }
    
    public function setColumnName($value){ 
        $this->name = $value; 
        $this->phpName = $this->phpName($this->name);
    }
    
    public function setColumnType($value){ 
        
        if (!$this->isValidType($value)){
            throw new \Exception("Exception in TableColumn - Unknown or invalid type: ".$value);
        }
        
        $this->type = $value; 
    }
    
    public function setPrimaryKey($value){ $this->isPrimaryKey = ($value == true)? true:false; }
    public function setAutoincrement($value){ $this->isAutoincrement = ($value == true)? true:false; }
}
?>