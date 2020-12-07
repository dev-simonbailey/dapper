<?php

// Fill in the details below with the credentials of the database you want to map.
// *****************************************************************************
// NOTE: Database you want to map
$dbName = '<Your_Database_Name>';
// NOTE: Database Username
$user = '<Your_Database_Username>';
// NOTE: Database password
$password = '<Your_Database_Password>';
// NOTE: Database Host
$host = '<Your_Database_Host>';
// NOTE: Database Port - Leave null if using default
$port = null;
// NOTE: Database encoding
$encoding = 'latin1';
// *****************************************************************************



// NOTE: Nothing needs modifying below this line, but feel free to adapt it to your needs.
$displayFile = fopen(__DIR__."/display/index.php","w");
$displayString = '<?php'."\n";
$displayString.= 'require(__DIR__."/autoloader.php");'."\n";
fwrite($displayFile,$displayString);
require(__DIR__."/classes/dapper.DB.class.php");
$DB = new MeekroDB($host, $user, $password, $dbName, $port, $encoding);
echo "MAPPING STARTED\n";
$db_tables = $DB->tableList();
foreach ($db_tables as $table) {
  echo "PROCESSING TABLE ==> ".$table."\n";
  $columns = $DB->columnList($table);
  if(!file_exists(__DIR__."/../classes/Tables/".$table."/".$table.".php")){
    mkdir(__DIR__."/../classes/Tables/".$table, 0777, true);
  }
  $classFile = fopen(__DIR__."/../classes/Tables/".$table."/".$table.".php","w");
  $classString = '<?php'."\n";
  $classString.= "\t".'declare(strict_types = 1);'."\n\n";
  $classString.= "\t".'namespace Tables\\'.$table.';'."\n\n";
  $classString.= "\t".'class '.$table.' implements '.$table.'Interface {'."\n\n";
  $classString.= "\t\t".'private $table = "'.$table.'";'."\n\n";
  $columns_array = '';
  foreach ($columns as $column) {
    $columns_array.= '"'.$column.'",';
    $follow_schema = $DB->query("DESCRIBE ".$table);
    $arrayString = 'private $'.'schema = array ('."\n";
    foreach ($follow_schema as $schema) {
      $arrayString .= "\t\t\t".'array (';
      foreach ($schema as $key => $value) {
        if(empty($value)){
          $arrayString .= '"'.$key.'"=>"NONE",';
        } else {
          $arrayString .= '"'.$key.'"=>"'.$value.'",';
        }
      }
      $arrayString = rtrim($arrayString,",")."),\n";
    }
  }
  $columns_array = rtrim($columns_array,",");
  $classString.= "\t\t".'private $columns = ['.$columns_array.'];'."\n\n";
  $classString.= "\t\t".'private $check = false;'."\n\n";
  $classString.= "\t\t".'private $messages = [];'."\n\n";
  $classString.= "\t\t".rtrim($arrayString,",")."\t\t".');'."\n\n";
  $classString.= "\t\t".'public function getColumns(): array{'."\n";
  $classString.= "\t\t\t".'return $this->columns;'."\n";
  $classString.= "\t\t".'}'."\n\n";
  $classString.= "\t\t".'public function getSchema(): array{'."\n";
  $classString.= "\t\t\t".'return $this->schema;'."\n";
  $classString.= "\t\t".'}'."\n\n";
  $classString.= "\t\t".'public function checkColumns(array $cols): string {'."\n";
  $classString.= "\t\t\t".'foreach ($cols as $col) {'."\n";
  $classString.= "\t\t\t\t".'if(in_array($col,$this->columns)){'."\n";
  $classString.= "\t\t\t\t\t".'$this->check = true;'."\n";
  $classString.= "\t\t\t\t".'} else {'."\n";
  $classString.= "\t\t\t\t\t".'$this->check = false;'."\n";
  $classString.= "\t\t\t\t\t".'array_push($this->messages,$col);'."\n";
  $classString.= "\t\t\t\t".'}'."\n";
  $classString.= "\t\t\t".'}'."\n";
  $classString.= "\t\t\t".'if(!$this->check){'."\n";
  $classString.= "\t\t\t\t".'$errString = "The following are not valid columns<br />";'."\n";
  $classString.= "\t\t\t\t".'foreach ($this->messages as $message) {'."\n";
  $classString.= "\t\t\t\t\t".'$errString .= $message."<br />";'."\n";
  $classString.= "\t\t\t\t\t".'}'."\n";
  $classString.= "\t\t\t\t".'return $errString;'."\n";
  $classString.= "\t\t\t\t".'} else {'."\n";
  $classString.= "\t\t\t\t\t".'return "All Columns Valid";'."\n";
  $classString.= "\t\t\t\t".'}'."\n";
  $classString.= "\t\t\t".'}'."\n";
  $classString.= "\t\t".'}'."\n";
  fwrite($classFile,$classString);
  fclose($classFile);
  $interfaceFile = fopen(__DIR__."/../classes/Tables/".$table."/".$table."Interface.php","w");
  $interfaceString = '<?php'."\n\n";
  $interfaceString.= "\t\t".'declare(strict_types = 1);'."\n\n";
  $interfaceString.= "\t\t".'namespace Tables\\'.$table.';'."\n\n";
  $interfaceString.= "\t\t".'interface '.$table.'Interface {'."\n\n";
  $interfaceString.= "\t\t\t".'public function getColumns(): array;'."\n\n";
  $interfaceString.= "\t\t\t".'public function getSchema(): array;'."\n\n";
  $interfaceString.= "\t\t\t".'public function checkColumns(array $cols): string;'."\n\n";
  $interfaceString.= "\t\t".'}'."\n\n";
  fwrite($interfaceFile,$interfaceString);
  fclose($interfaceFile);
  $displayString = '$'.$table.' = new Tables\\'.$table.'\\'.$table.';'."\n";
  $displayString.= 'echo "'.$table.' ==> COLUMN NAMES: <br />";'."\n";
  $displayString.= 'var_dump($'.$table.'->getColumns());'."\n";
  $displayString.= "echo '<br /><br />';\n";
  $displayString.= 'echo "'.$table.' ==> COLUMN SCHEMA: <br />";'."\n";
  $displayString.= 'var_dump($'.$table.'->getSchema());'."\n";
  $displayString.= 'echo "<br /><br />";'."\n";
  fwrite($displayFile,$displayString);
}
fclose($displayFile);
echo "MAPPING COMPLETE";
