<?php
namespace dbTable;

class ForeignKey {
    protected $foreignTable;
    protected $foreignRef;
    protected $localRef;
    
    public function getForeignTableName(){ return $this->foreignTable; }
    public function getForeignReference(){ return $this->foreignRef; }
    public function getLocalReference(){return $this->localRef; }
    
    public function setForeignTableName($value){ $this->foreignTable = $value; }
    public function setForeignReference($value){ $this->foreignRef = $value; }
    public function setLocalReference($value){$this->localRef = $value; }
}