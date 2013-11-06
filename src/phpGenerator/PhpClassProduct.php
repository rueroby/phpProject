<?php
namespace phpGenerator;

use dbTable\DBTable;
use dbTable\ForeignKey;
use dbTable\TableColumn;

class PhpClassProduct {
    protected $className;
    protected $extendsClassName;
    protected $constants;
    protected $properties;
    protected $methods;
    protected $template;
    protected $tbl; // dbTable\DBTable
    
    public function __construct($tbl, $className){
        if ($tbl instanceof DBTable){
            $this->tbl = $tbl;
        }else {
            $this->tbl = null;
        }
        
        $this->className = $className;
    }
    
    public function addProperty($name, $type, $readonly = false){
        if (!$this->properties){
            $this->properties = array();
        }
        
        $this->properties[] = new PhpClassProperty($name, $type, $readonly);
    }
    
    public function addConst($const){
        if (!$this->constants){
            $this->constants = array();
        }
        
        $this->constants[] = $const;
    }
    
    public function addMethod($name, $method){
        if (!$this->methods){
            $this->methods = array();
        }
        
        $this->methods[$name] = $method;
    }
    
    public function __toString(){
        $this->setupProperties();
        
        $str = "<?php\n";
        $str .= "class ".$this->className;
        if ($this->extendsClassName){
            $str .= " extends ".$this->extendsClassName;
        }
        $str .= "{\n";
        
        // write constants
        if ($this->constants){
            foreach($this->constants as $const){
                $str .= $const;
            }
        }
        
        $str .= "\n";
        
        // write protected properties
        foreach($this->properties as $prop){
            $str .= "\tprotected ".$prop->getName().";\n";
        }
        
        $str .= "\n";
        
        // write class property accessors
        foreach($this->properties as $prop){
            $str .= $prop->generateGetAccessor();
        }
        
        $str .= "\n";

        foreach($this->properties as $prop){
            $str .= $prop->generateSetAccessor();
        }
        
        // write methods
        if ($this->methods){
            foreach($this->methods as $key=>$value){
                $str .= $value;
                $str .= "\n";
            }
        }
        
        
        $str .= "}\n";
        
        return $str;
    }
    
    private function setupProperties(){
        $tblColumns = $this->tbl->getColumns();
        foreach($tblColumns as $col){
            $this->addProperty($col->getPhpName(), $col->getColumnType());
        }
    }
}