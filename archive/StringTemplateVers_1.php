<?php
class StringTemplate { // version 0 of this class is in /Users/rudy/bin/console.php
    // This version 1 attempts to create a chainable object
    // !!! not sure if that is what I really want
    protected $string;
    protected $output;
    protected $separator;

    public function __construct($str, $sep = ' '){
        $this->string = $str; // original template
        $this->output = $str; // output after a modification has been applied ... all modifications are applied to this string
        $this->separator = $sep;
    }

    // gets string value at $index of the original template string
    public function getIndex($index){
        $fields = explode($this->separator, $this->string);
        return $fields[$index];
    }

    // returns the original template string
    public function getTemplate(){
        return $this->string;
    }
    
    public function setTemplate($value){
        $this->string = $value;
    }

    public function replaceKeyWithValue($key, $value){
        $this->output = str_replace('<'.$key.'>', $value, $this->output);
        return $this;
    }
    
    public function replaceKeyWithArrayValueAtIndex($key, $array, $index){
        $this->output = str_replace('<'.$key.'>', $array[$index], $this->output);
        return $this;
    }
    
    // This method creates a new string
    public function applyArrayValuesToKey($array, $key, $index = 0){
        $temp = array();
        for($i = $index; $i < count($array); $i++){
            $temp[] = str_replace('<'.$key.'>', $array[$i], $this->output);
        }
        $this->output = join($this->separator, $temp);
        return $this;
    }
    
    public function __toString(){
        return $this->output;
    }
}

