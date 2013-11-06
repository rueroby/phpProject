<?php
namespace dbTable;

class DBTable {
    protected $tableName;       
    protected $columns;
    protected $foreignKeys;
    
    public function __construct($tblName){
        $this->tableName = $tblName;
    }
    
    public function getTableName(){ return $this->tableName; }
    
    public function getColumns(){ 
        if (!$this->columns){
            $this->columns = array();
        }
        return $this->columns; 
    }
    
    public function getForeignKeys()
    { 
        if (!$this->foreignKeys){
            $this->foreignKeys = array();
        }
        return $this->foreignKeys; 
    }
    
    public function setTableName($value){ $this->tableName = $value; }
    public function setColumns($value){ $this->columns = $value; }
    public function setForeignKeys($value){ $this->foreignKeys = $value; }
    
    public function addColumn($column){
        if (!$this->columns){
            $this->columns = array();
        }
        
        if ($column instanceof TableColumn){
            $this->columns[] = $column;
        }
    }
    
    public function getPrimaryKeyColumn(){
        if ($this->columns){
            foreach($this->columns as $col){
                if ($col->isPrimaryKey()){
                    return $col;
                }
            }
        }
        
        return null;
    }
}
?>