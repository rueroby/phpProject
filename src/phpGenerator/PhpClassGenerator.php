<?php
namespace phpGenerator;

use dbTable\DBTable;
use dbTable\ForeignKey;
use dbTable\TableColumn;


/**
 * This class constructs an instance capable of generating a Php class
 * given a database (sqlite v3) schema for one of more tables. Each Php class
 * that is generated is maps to a db table.
 */
class PhpClassGenerator {
    protected $filename;
    
    public function __construct($filename){
        $this->filename = $filename;
    }
    
    public function phpName($str){
        $parts = explode("_", $str);
        if (count($parts) > 1){
            for($i=1; $i < count($parts); $i++){
                $parts[$i] = ucfirst($parts[$i]);
            }
        }
        return join($parts);
    }
    
    
    public function publicSave($columns){
        $str = "\n\tpublic function save(){\n";
        $str .= "\t\tif (\$db = SqlitePDO::open(self::DATABASE))\n";
        $str .= "\t\t{\n";
        $str .= "\t\t\t\$stmt = 'CREATE TABLE IF NOT EXISTS ';\n";
        $str .= "\t\t\t\$stmt .= self::TABLE_NAME;\n";
        
        $create = "";
        if ($columns){
            $create .= "(";
            $comma = false;
            foreach($columns as $col){
                if ($comma) $create .= ", ";
                $create .= $col->getColumnName();
                $create .= " ".$col->getColumnType();
                if ($col->isPrimaryKey()){
                    $create .= " PRIMARY KEY";
                }
                
                if ($col->isAutoincrement()){
                    $create .= " AUTOINCREMENT";
                }
                $comma = true;
            }
            $create .= ")";
        }
        $str .= "\t\t\t\$stmt .= \"".$create."\";\n";
        $str .= "\t\t\t\$ok = \$db->exec(\$stmt);\n";
        $str .= "\t\t\tif (\$ok === false){\n";
        $str .= "\t\t\t\tprint_r(\"SQL stmt: \" . \$stmt);\n";
        $str .= "\t\t\t\tprint_r(\$db->errorInfo());\n";
        $str .= "\t\t\t\texit();\n";
        $str .= "\t\t\t}\n";
        
        $str .= "\t\t\tif (!\$this->contactInfoId){\n";
        $str .= "\t\t\t\t\$stmt = \"INSERT INTO \".self::TABLE_NAME;\n";
        $str .= "\t\t\t\t\$stmt .= \" VALUES (NULL\";\n";
        for($i = 1; $i < count($columns); $i++){
            if ($i == 1){
                $str .= "\t\t\t\t\$stmt .= \", '\". \$this->".$columns[$i]->getPhpName().";\n";
            }else{
                $str .= "\t\t\t\t\$stmt .= \"', '\". \$this->".$columns[$i]->getPhpName().";\n";
            }
        }
        
        
        $str .= "\t\t\t\t\$stmt .= \"');\";\n";
        $str .= "\t\t\t\t\$count = \$db->exec(\$stmt);\n";
        $str .= "\t\t\t\tif (\$count === false){\n";
        $str .= "\t\t\t\t\tprint_r('Exec stmt: '.\$stmt);\n";
        $str .= "\t\t\t\t\tprint_r(\$db->errorInfo());\n";
        $str .= "\t\t\t\t}\n";
        $str .= "\t\t\t}\n";
        $str .= "\t\t\telse { // this is an update\n";
        $str .= "\t\t\t\t\$stmt = \"UPDATE \".self::TABLE_NAME.\" SET \". ";
        for($i = 1; $i < count($columns); $i++){
            if ($i == 1 ){
                $str .= "self::COLUMN_".strtoupper($columns[$i]->getColumnName())." .\"='\". \$this->".$columns[$i]->getPhpName().";\n";
            }else {
                $str .= "\t\t\t\t\$stmt .= \"', \". self::COLUMN_".strtoupper($columns[$i]->getColumnName())." .\"='\". \$this->".$columns[$i]->getPhpName().";\n";
            }
        }
        
        
        $keyName = $columns[0]->getColumnName();
        $str .= "\t\t\t\t\$stmt .= \"' WHERE \". self::COLUMN_".strtoupper($keyName).".\"=\".\$this->".$this->phpName($keyName).";\n";
        $str .= "\t\t\t\t\$stmt .= \";\";\n";
        
        $str .= "\t\t\t\t\$count = \$db->exec(\$stmt);\n";
        $str .= "\t\t\t\tif (\$count === false){\n";
        $str .= "\t\t\t\t\tprint_r('Exec stmt: '.\$stmt);\n";
        $str .= "\t\t\t\t\tprint_r(\$db->errorInfo());\n";
        $str .= "\t\t\t\t}\n";
        $str .= "\t\t\t}\n";
        $str .= "\t\t}\n";
        $str .= "\t\telse {\n";
        $str .= "\t\t\tprint_r(\$db->errorInfo());\n";
        $str .= "\t\t}\n";
        $str .= "\t}\n";
        
        return $str;
    }
    
    public function publicDelete($colName){
        $str = "\n\tpublic function delete(){\n";
        $str .= "\t\tif (is_int(\$this->contactInfoId)){\n";
        $str .= "\t\t\tif (\$db = SqlitePDO::open(self::DATABASE))\n";
        $str .= "\t\t\t{\n";
        $str .= "\t\t\t\t\$stmt = \"DELETE FROM \".self::TABLE_NAME.\" WHERE \".self::COLUMN_".strtoupper($colName).".\"=\".\$this->".$this->phpName($colName)." . \";\";\n";
        $str .= "\t\t\t\t\$count = \$db->exec(\$stmt, \$this->err_msg);\n";
        $str .= "\t\t\t\tif (\$count === false){\n";
        $str .= "\t\t\t\t\tprint_r(\$db->errorInfo());\n";
        $str .= "\t\t\t\t}\n";
        $str .= "\t\t\t}\n";
        $str .= "\t\t}\n";
        $str .= "\t}\n";
        
        return $str;
    }
    
    public function generate(){
        $schema = simplexml_load_file($this->filename); // load the simple schema
    
        $dbName = $schema['name'];
        
        foreach($schema as $tblInfo){
            $tbl = new DBTable($tblInfo['name']);
            $className = "Base". ucfirst($this->phpName($tbl->getTableName())); // php class name         
    
            // build a PhpClassProduct instance
            $prod = new PhpClassProduct($tbl, $className);
            
            $prod->addConst("\tconst DATABASE = '".$dbName . "';" . "\n");
            $prod->addConst("\tconst TABLE_NAME = '" . $tbl->getTableName() . "';" . "\n");
            
            $protectedFields = array();
            foreach($tblInfo as $colInfo){
                $col = new TableColumn($colInfo['name'], $colInfo['type']);
                if ($colInfo['primaryKey']){
                    $col->setPrimaryKey(true);
                }
                if ($colInfo['autoincrement']){
                    $col->setAutoincrement(true);
                }
                $prod->addConst("\tconst COLUMN_". strtoupper($col->getColumnName()) . " = \"" .$col->getColumnName() . "\";" . "\n");            
                //$protectedFields[] = $col->getPhpName();
                
                $tbl->addColumn($col);
            }
    
            $tblColumns = $tbl->getColumns();
            if ($tblColumns){
                $prod->addMethod("save", $this->publicSave($tblColumns));
            }
            
            $primaryKeyCol = $tbl->getPrimaryKeyColumn();
            if ($primaryKeyCol){
                $prod->addMethod("delete", $this->publicDelete($primaryKeyCol->getColumnName()));
            }            
            
            file_put_contents($className . ".php", $prod); // writes the php class file
        }
    } 
}

    
        
    

    

    
    
