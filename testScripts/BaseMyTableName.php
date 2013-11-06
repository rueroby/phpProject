<?php
class BaseMyTableName{
	const DATABASE = 'myDB';
	const TABLE_NAME = 'myTableName';
	const COLUMN_ROW_ID = "row_id";
	const COLUMN_FIELD_ONE = "field_one";
	const COLUMN_FIELD_TWO = "field_two";
	const COLUMN_FIELD_THREE = "field_three";
	const COLUMN_FIELDFOUR = "fieldFour";
	const COLUMN_FIELD_FIVE_DOT_MORE_TEXT = "field_five_dot_more_text";
	const COLUMN_FIELD_FLAG = "field_flag";

	protected rowId;
	protected fieldOne;
	protected fieldTwo;
	protected fieldThree;
	protected fieldFour;
	protected fieldFiveDotMoreText;
	protected fieldFlag;


	public function getRowId(){
		return $this->rowId;
	}

	public function getFieldOne(){
		return $this->fieldOne;
	}

	public function getFieldTwo(){
		return $this->fieldTwo;
	}

	public function getFieldThree(){
		return $this->fieldThree;
	}

	public function getFieldFour(){
		return $this->fieldFour;
	}

	public function getFieldFiveDotMoreText(){
		return $this->fieldFiveDotMoreText;
	}

	public function getFieldFlag(){
		return $this->fieldFlag;
	}


	public function setRowId($value){
		$this->rowId = $value;
	}

	public function setFieldOne($value){
		if ($value instanceof String && strlen($value) <= 120){
			$this->fieldOne = $value;
		}else {
			$this->fieldOne = substr($value, 0, 120);	// truncate the string value
		}
	}

	public function setFieldTwo($value){
		if ($value instanceof String && strlen($value) <= 120){
			$this->fieldTwo = $value;
		}else {
			$this->fieldTwo = substr($value, 0, 120);	// truncate the string value
		}
	}

	public function setFieldThree($value){
		if ($value instanceof String && strlen($value) <= 120){
			$this->fieldThree = $value;
		}else {
			$this->fieldThree = substr($value, 0, 120);	// truncate the string value
		}
	}

	public function setFieldFour($value){
		$this->fieldFour = $value;
	}

	public function setFieldFiveDotMoreText($value){
		if ($value instanceof String && strlen($value) <= 120){
			$this->fieldFiveDotMoreText = $value;
		}else {
			$this->fieldFiveDotMoreText = substr($value, 0, 120);	// truncate the string value
		}
	}

	public function setFieldFlag($value){
		$this->fieldFlag = $value;
	}

	public function save(){
		if ($db = SqlitePDO::open(self::DATABASE))
		{
			$stmt = 'CREATE TABLE IF NOT EXISTS ';
			$stmt .= self::TABLE_NAME;
			$stmt .= "(row_id int PRIMARY KEY AUTOINCREMENT, field_one varchar (120), field_two varchar (120), field_three varchar (120), fieldFour datetime, field_five_dot_more_text varchar (120), field_flag boolean)";
			$ok = $db->exec($stmt);
			if ($ok === false){
				print_r("SQL stmt: " . $stmt);
				print_r($db->errorInfo());
				exit();
			}
			if (!$this->contactInfoId){
				$stmt = "INSERT INTO ".self::TABLE_NAME;
				$stmt .= " VALUES (NULL";
				$stmt .= ", '". $this->fieldOne;
				$stmt .= "', '". $this->fieldTwo;
				$stmt .= "', '". $this->fieldThree;
				$stmt .= "', '". $this->fieldFour;
				$stmt .= "', '". $this->fieldFiveDotMoreText;
				$stmt .= "', '". $this->fieldFlag;
				$stmt .= "');";
				$count = $db->exec($stmt);
				if ($count === false){
					print_r('Exec stmt: '.$stmt);
					print_r($db->errorInfo());
				}
			}
			else { // this is an update
				$stmt = "UPDATE ".self::TABLE_NAME." SET ". self::COLUMN_FIELD_ONE ."='". $this->fieldOne;
				$stmt .= "', ". self::COLUMN_FIELD_TWO ."='". $this->fieldTwo;
				$stmt .= "', ". self::COLUMN_FIELD_THREE ."='". $this->fieldThree;
				$stmt .= "', ". self::COLUMN_FIELDFOUR ."='". $this->fieldFour;
				$stmt .= "', ". self::COLUMN_FIELD_FIVE_DOT_MORE_TEXT ."='". $this->fieldFiveDotMoreText;
				$stmt .= "', ". self::COLUMN_FIELD_FLAG ."='". $this->fieldFlag;
				$stmt .= "' WHERE ". self::COLUMN_ROW_ID."=".$this->rowId;
				$stmt .= ";";
				$count = $db->exec($stmt);
				if ($count === false){
					print_r('Exec stmt: '.$stmt);
					print_r($db->errorInfo());
				}
			}
		}
		else {
			print_r($db->errorInfo());
		}
	}


	public function delete(){
		if (is_int($this->contactInfoId)){
			if ($db = SqlitePDO::open(self::DATABASE))
			{
				$stmt = "DELETE FROM ".self::TABLE_NAME." WHERE ".self::COLUMN_ROW_ID."=".$this->rowId . ";";
				$count = $db->exec($stmt, $this->err_msg);
				if ($count === false){
					print_r($db->errorInfo());
				}
			}
		}
	}

}
