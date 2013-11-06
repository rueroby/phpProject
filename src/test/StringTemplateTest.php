<?php
namespace test;

use stringTemplate\StringTemplate;

class StringTemplateTest extends \PHPUnit_Framework_TestCase {
    public function testCreatingStringTemplateInstance(){
        $template = new StringTemplate("<h1>Hello, <%= name %> <%= lastName %></h1>");
        
        $this->assertTrue($template instanceof StringTemplate);
        
        $params = array("name" => "Rudy", "lastName" => "Robinson");
        $template->compile($params);
        
        echo $template;
        $this->assertTrue(true, "result: " . $template);
    }
}
?>