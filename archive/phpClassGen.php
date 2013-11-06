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
        
        public function getColumnName(){ return $this->name; }
        public function getColumnType(){ return $this->type; }
        public function getPhpName(){ return $this->phpName; }
        
        public function setColumnName($value){ $this->name = $value; }
        public function setColumnType($value){ $this->type = $value; }
        public function setPhpName($value){ $this->phpName = $value; }
    }
    
    class DBTable {
        protected $tableName;       
        protected $columns;
        protected $foreignKeys;
        
        public function getTableName(){ return $this->tableName; }
        public function getColumns(){ 
            if (!$columns){
                $columns = array();
            }
            return $this->columns; 
        }
        public function getForeignKeys()
        { 
            if (!$foreignKeys){
                $foreignKeys = array();
            }
            return $this->foreignKeys; 
        }
        
        public function setTableName($value){ $this->tableName = $value; }
        public function setColumns($value){ $this->columns = $value; }
        public function setForeignKeys($value){ $this->foreignKeys = $value; }
        
        public function addColumn($column){
            if (!$columns){
                $columns = array();
            }
            
            if ($column instanceof TableColumn){
                $columns[] = $column;
            }
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
    
    function publicDelete($colName){
        $str = "\tpublic function delete(){\n";
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
        $tblName = $tblInfo['name'];
        
        // output template data
        $className = "Base". ucfirst(phpName($tblName)); // php class name
        $str .= "class " . $className . "\n";
        $str .= "{" . "\n";
        $str .= "\tconst DATABASE = ".$dbName . ";" . "\n";
        $str .= "\tconst TABLE_NAME = " . $tblName . ";" . "\n";
        
        $protectedFields = array();
        foreach($tblInfo as $colInfo){
            $colName = $colInfo['name'];
            $str .= "\tconst COLUMN_". strtoupper($colName) . " = \"" .$colName . "\";" . "\n";
            $protectedFields[] = phpName($colName);
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
        
        foreach($tblInfo as $colInfo){
            if ($colInfo['primaryKey'] == 'true'){
                $colName = $colInfo['name'];
                $str .= publicDelete($colName);
            }
        }
        
        
        $str .= "}" . "\n";
        
        file_put_contents($className . ".php", $str); // writes the php class file
    }
