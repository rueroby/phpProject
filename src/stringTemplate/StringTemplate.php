<?php
namespace stringTemplate;

/**
$attr$                                 <= replace with attr.toString()
$attr.property$                        <= replace with property of attribute (or empty string if missing)
$attr.(expr)$                          <= value of expr is the 'property' name ... this is a property lookup
$multi-valued-attr$                    <= concatenation of toString() invoked on each element
$multi-valued-attr;separator=expr$     <= concatenation of toString() invoked on each element separated by expr
$template(argList)$                    <=
$attr:template(argList)$               <=
$attr:{argName|_anonymous_template}$   <=
$if(!attr)$ $template()$ $endif$       <=
**/
class StringTemplate { 
    protected $template;

    public function __construct($tpl){
        $this->template = $tpl;
    }

    public function compile($ary){
        foreach($ary as $key=>$value){
            $this->template = preg_replace("/<%= ". $key . " %>/", $ary[$key], $this->template);
        }
    }
    
    public function __toString(){
        return $this->template;
    }
}

