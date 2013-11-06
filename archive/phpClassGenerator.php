#!/usr/bin/php -q
<?php
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
        
    class TableColumn {
        protected $name; // actual table column name
        protected $type;
        protected $phpName;
        protected $isPrimaryKey;
        protected $isAutoincrement;
        
        public function __construct($name, $type){
            $this->name = $name;
            $this->type = $type;
            $this->isPrimaryKey = false;
            $this->isAutoincrement = false;
        }
        
        public function getColumnName(){ return $this->name; }
        public function getColumnType(){ return $this->type; }
        public function getPhpName(){ return $this->phpName; }
        public function isPrimaryKey() { return $this->isPrimaryKey; }
        public function isAutoincrement(){ return $this->isAutoincrement; }
        
        public function setColumnName($value){ $this->name = $value; }
        public function setColumnType($value){ $this->type = $value; }
        public function setPhpName($value){ $this->phpName = $value; }
        public function setPrimaryKey($value){ $this->isPrimaryKey = ($value == true)? true:false; }
        public function setAutoincrement($value){ $this->isAutoincrement = ($value == true)? true:false; }
    }
    
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
    
    function phpName($str){
        $parts = explode("_", $str);
        if (count($parts) > 1){
            for($i=1; $i < count($parts); $i++){
                $parts[$i] = ucfirst($parts[$i]);
            }
        }
        return join($parts);
    }
    
    function publicGet($phpName){
        $str = "\tpublic function get".ucfirst($phpName)."(){\n";
        $str .= "\t\treturn \$this->".$phpName.";\n";
        $str .= "\t}\n";
        
        return $str;
    }

    function publicSet($phpName){
        $str = "\tpublic function set".ucfirst($phpName)."(\$value){\n";
        $str .= "\t\t\$this->".$phpName." = \$value;\n";
        $str .= "\t}\n";
        
        return $str;
    }
    
    function publicSave($columns){
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
        $str .= "\t\t\t\t\$stmt .= \"' WHERE \". self::COLUMN_".strtoupper($keyName).".\"=\".\$this->".phpName($keyName).";\n";
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
    
    function publicDelete($colName){
        $str = "\n\tpublic function delete(){\n";
        $str .= "\t\tif (is_int(\$this->contactInfoId)){\n";
        $str .= "\t\t\tif (\$db = SqlitePDO::open(self::DATABASE))\n";
        $str .= "\t\t\t{\n";
        $str .= "\t\t\t\t\$stmt = \"DELETE FROM \".self::TABLE_NAME.\" WHERE \".self::COLUMN_".strtoupper($colName).".\"=\".\$this->".phpName($colName)." . \";\";\n";
        $str .= "\t\t\t\t\$count = \$db->exec(\$stmt, \$this->err_msg);\n";
        $str .= "\t\t\t\tif (\$count === false){\n";
        $str .= "\t\t\t\t\tprint_r(\$db->errorInfo());\n";
        $str .= "\t\t\t\t}\n";
        $str .= "\t\t\t}\n";
        $str .= "\t\t}\n";
        $str .= "\t}\n";
        
        return $str;
    }
    
    $schema = simplexml_load_file("testSchema.xml"); // load the simple schema
    
    $dbName = $schema['name'];
    
    // this script will output a php class file
    $str = "<?php\n";
    foreach($schema as $tblInfo){
        $tbl = new DBTable($tblInfo['name']);
        
        //var_dump($tbl);exit();
        // output template data
        $className = "Base". ucfirst(phpName($tbl->getTableName())); // php class name
        $str .= "class " . $className . "\n";
        $str .= "{" . "\n";
        $str .= "\tconst DATABASE = ".$dbName . ";" . "\n";
        $str .= "\tconst TABLE_NAME = " . $tbl->getTableName() . ";" . "\n";
        
        $protectedFields = array();
        foreach($tblInfo as $colInfo){
            $col = new TableColumn($colInfo['name'], $colInfo['type']);
            $col->setPhpName(phpName($col->getColumnName()));
            if ($colInfo['primaryKey']){
                $col->setPrimaryKey(true);
            }
            if ($colInfo['autoincrement']){
                $col->setAutoincrement(true);
            }
            $str .= "\tconst COLUMN_". strtoupper($col->getColumnName()) . " = \"" .$col->getColumnName() . "\";" . "\n";            
            $protectedFields[] = $col->getPhpName();
            
            $tbl->addColumn($col);
        }
        
        $str .= "\n";
        for($i=0; $i < count($protectedFields); $i++){
            $str .= "\tprotected $".$protectedFields[$i].";" . "\n";
        }

        $str .= "\n";
        for($i=0; $i < count($protectedFields); $i++){
            $str .= publicGet($protectedFields[$i]);
        }
        
        $str .= "\n";
        for($i=0; $i < count($protectedFields); $i++){
            $str .= publicSet($protectedFields[$i]);
        }
        
        $tblColumns = $tbl->getColumns();
        if ($tblColumns){
            $str .= publicSave($tblColumns);
        }
        
        $primaryKeyCol = $tbl->getPrimaryKeyColumn();
        if ($primaryKeyCol){
            $str .= publicDelete($primaryKeyCol->getColumnName());
        }
        
        
        $str .= "}" . "\n";
        
        file_put_contents($className . ".php", $str); // writes the php class file
    }
