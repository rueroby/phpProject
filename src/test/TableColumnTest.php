<?php
namespace test;

use dbTable\TableColumn;

class TableColumnTest extends \PHPUnit_Framework_TestCase {
    /**
     * This method provides test data for creating TableColumns of various types;
     * it is used by testCreationOfVariousColumnTypes()
     */
    public function providerColumnTestData() {
        return array(
            array("field_one", "varchar (120)", "fieldOne"),
            array("field_two", "varchar(120)", "fieldTwo"), 
            array("field_three", "varchar (120)", "fieldThree"), 
            array("fieldFour", "datetime", "fieldFour"), 
            array("field_five_dot_more_text", "varchar (120)", "fieldFiveDotMoreText"), 
            array("field_flag", "boolean", "fieldFlag") 
        );
    }
    
    public function testNewTableColumnCreatesNewInstance(){
        //<column name="row_id" primaryKey="true" type="int" autoincrement="true"/>
        $col = new TableColumn("row_id", "int");
        $col->setPrimaryKey(true);
        $col->setAutoincrement(true);
        
        $this->assertTrue($col instanceof TableColumn);
        
        return $col;
    }
    
    /**
     * @depends testNewTableColumnCreatesNewInstance
     */
    public function testInstanceSettings($obj){
        // cont'd: <column name="row_id" primaryKey="true" type="int" autoincrement="true"/>
        if ($obj){
            $this->assertEquals($obj->getColumnName(), "row_id");
            $this->assertEquals($obj->getColumnType(), "int");
            $this->assertEquals($obj->getPhpName(), "rowId");
            $this->assertTrue($obj->isPrimaryKey());
            $this->assertTrue($obj->isAutoincrement());
        }        
        else {
            $this->assertTrue(false, "Not expected that TableColumn instance is null.");
        }
    }
    
    /**
     * @param string - column name
     * @param string - column type
     * @param string - generated Php name
     *
     * @dataProvider providerColumnTestData
     */
    public function testCreationOfVariousColumnTypes($colname, $type, $phpName){
        $col = new TableColumn($colname, $type);
        
        if ($col){
            $this->assertEquals($col->getColumnName(), $colname);
            $this->assertEquals($col->getColumnType(), $type);
            $this->assertEquals($col->getPhpName(), $phpName);
            $this->assertFalse($col->isPrimaryKey());
            $this->assertFalse($col->isAutoincrement());
        }
    }
    
}
?>