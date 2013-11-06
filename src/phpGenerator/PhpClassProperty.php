<?php
namespace phpGenerator;

class PhpClassProperty {
    protected $name;
    protected $type;
    protected $readonly;
    protected $vCharSize;
    
    public function __construct($name, $type, $readonly){
        $vCharSize = 0;
        
        $this->setName($name);
        $this->setPropertyType($type);
        $this->setReadOnly($readonly);
    }

    public function getName(){
        return $this->name;
    }
    
    public function getPropertyType(){
        return $this->type;
    }
    
    public function getReadOnly(){
        return $this->readOnly;
    }

    public function setName($value){
        $this->name = $value;
    }

    public function setPropertyType($value){
        if (strncmp($value, "varchar", strlen("varchar")) == 0){
            $this->type = "varchar";
            $sizeStr = substr(strstr($value, "("), 1);
            $pos = strpos($sizeStr, ")");
            
            if ($pos > 0){
                $sizeStr = substr($sizeStr, 0, $pos);
                $this->vCharSize = intval($sizeStr);
            }
            return;
        }
        $this->type = $value;
    }

    public function setReadOnly($value){
        $this->readOnly = $value == true;
    }
    
    public function generateGetAccessor(){
        $str = "\n\tpublic function get".ucfirst($this->name)."(){\n";
        $str .= "\t\treturn \$this->".$this->name.";\n";
        $str .= "\t}\n";
        
        return $str;
    }
    
    public function generateSetAccessor(){
        $str = "\n\tpublic function set".ucfirst($this->name)."(\$value){\n";
        if ($this->type == "varchar"){
            $str .= "\t\tif (\$value instanceof String && strlen(\$value) <= ". $this->vCharSize ."){\n";
            $str .= "\t\t\t\$this->".$this->name." = \$value;\n";
            $str .= "\t\t}else {\n";
            $str .= "\t\t\t\$this->".$this->name." = substr(\$value, 0, ". $this->vCharSize .");\t// truncate the string value\n"; // truncate the string value
            $str .= "\t\t}\n";
        }else {
            $str .= "\t\t\$this->".$this->name." = \$value;\n";
        }
        $str .= "\t}\n";
        
        return $str;
    }
}