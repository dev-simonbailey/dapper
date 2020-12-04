<?php
/**
 * dapper
 *
 * dapper takes a MySQL database as an input and then retrieves all the tables,
 * gets the columns and schemas for those tables and creates classes for them
 * in classes->tables. It was created so that the MySQL database doesn't need to be
 * accessed by devs working on code - protects the MySQL database from potential
 * erroneous manipulation.
 *
 * The default Properties are:
 *  $table    <string> to hold the table name
 *  $columns  <array> to hold the column names
 *  $check    <boolean> used by the checkColumns method
 *  $messages <array> to hold error messages from checkColumns method
 *  $schema   <array<array>...> to hold the schema data for the table
 *
 * The default methods are:
 *  getColumns() Returns an array containing the column names
 *  getSchema()  Returns an array of the schema data
 *  checkColumns()  accepts an array of column names as input, checks if they
 *                  are valid, if they are will return true, otherwise will
 *                  return a string of the columns that are not valid.
 *
 * To use, navigate to the dapper folder and open the dapper.php file,
 * fill in the credentials of the database you want to conenct to, in the top
 * section, then either run in browser or via the command line (php dapper.php)
 *
 * All the classes are created in a dir called classes/tables, as shown below.
 *
 * <your project>
 *                |-classes
 *                        |-tables
 *                                |-<table classes>
 *                |-dapper
 *                        |-dapper.php
 *                        |-classes
 *                                 |-dapper.DB.class.php
 *                |-index.php
 *
 * The structure of Dapper is the dapper dir, which contains a script (dapper.php)
 * and a dir called classes, which contains a class (dapper.DB.class.php) which is
 * a bundled version of the MeekroDB (https://meekro.com/quickstart.php)
 * that connects to the MySQL database specified.
 *
 * Usage example:
 * echo "Insert Table Name ==> ";
 * $yourVarName = new Tables\Tablename\tablename;
 * //This will echo out all the columns in the table
 * foreach ($yourVarName->getColumns() as $column) {
 *  echo $column.", ";
 * }
 * // This will var_dump the schema data.
 * var_dump($yourVarName->getSchema());
 *
 * // This will check the column names are valid and return any that are not
 * echo $yourVarName->checkColumns(array("id","name","age"));
 *
 * Feel free to build on top of the default methods, but be aware that re-running
 * the mapper.php script will reset all alterations made - unless you modify mapper.php
 * to include your new methods (recommended).
 *
 * You could, for example, add a method to the table class that allows you to
 * build a MySQL query, but check the validity of the column names being passed.
 *
 * Trouble Shooting:
 *
 * Fails to connect to database:
 *  make sure the credentials are correct
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */


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
require(__DIR__."/classes/mapper.DB.class.php");
$DB = new MeekroDB($host, $user, $password, $dbName, $port, $encoding);
echo "MAPPING STARTED\n";
$db_tables = $DB->tableList();
foreach ($db_tables as $table) {
  echo "PROCESSING TABLE ==> ".$table."\n";
  $columns = $DB->columnList($table);
  if(!file_exists(__DIR__."/../classes/tables/".$table."/".$table.".php")){
    mkdir(__DIR__."/../classes/tables/".$table, 0777, true);
  }
  $file = fopen(__DIR__."/../classes/tables/".$table."/".$table.".php","w");
  $classString = '<?php'."\n";
  $classString.= "\t".'declare(strict_types = 1);'."\n\n";
  $classString.= "\t".'namespace Tables\\'.ucwords($table).';'."\n\n";
  $classString.= "\t".'class '.$table.' implements '.$table.'Interface {'."\n\n";
  $classString.= "\t\t".'private $table = "follow";'."\n\n";
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
  fwrite($file,$classString);
  fclose($file);
  $file = fopen(__DIR__."/../classes/tables/".$table."/".$table."Interface.php","w");
  $interfaceString = '<?php'."\n\n";
  $interfaceString.= "\t\t".'declare(strict_types = 1);'."\n\n";
  $interfaceString.= "\t\t".'namespace Tables\\'.ucwords($table).';'."\n\n";
  $interfaceString.= "\t\t".'interface '.$table.'Interface {'."\n\n";
  $interfaceString.= "\t\t\t".'public function getColumns(): array;'."\n\n";
  $interfaceString.= "\t\t\t".'public function getSchema(): array;'."\n\n";
  $interfaceString.= "\t\t\t".'public function checkColumns(array $cols): string;'."\n\n";
  $interfaceString.= "\t\t".'}'."\n\n";
  fwrite($file,$interfaceString);
  fclose($file);
}
echo "MAPPING COMPLETE";
