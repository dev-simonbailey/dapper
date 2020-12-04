# dapper

dapper takes a MySQL database as an input and then retrieves all the tables,
gets the columns and schemas for those tables and creates classes for them
in classes->tables. It was created so that the MySQL database doesn't need to be
accessed by devs working on code - protects the MySQL database from potential
erroneous manipulation.

The default Properties are:

  $table    <string> to hold the table name

  $columns  <array> to hold the column names

  $check    <boolean> used by the checkColumns method

  $messages <array> to hold error messages from checkColumns method

  $schema   <array <array>...> to hold the schema data for the table

The default methods are:

  getColumns() Returns an array containing the column names

  getSchema()  Returns an array of the schema data

  checkColumns()  accepts an array of column names as input, checks if they
                  are valid, if they are will return a string containing the
                  column names passed in, otherwise will return a string of
                  the columns that are not valid.

To use, navigate to the dapper folder and open the dapper.php file,
fill in the credentials of the database you want to connect to, in the top
section, then either run in browser or via the command line (php dapper.php)

All the classes are created in a dir called classes/tables, as shown below.

<your project>

    |-classes

        |-tables

            |-<table classes>

    |-dapper

        |-dapper.php

        |-classes

            |-dapper.DB.class.php

    |-index.php

The structure of Dapper is the dapper dir, which contains a script (dapper.php)
and a dir called classes, which contains a class (dapper.DB.class.php) which is
a bundled version of the MeekroDB (https://meekro.com/quickstart.php)
that connects to the MySQL database specified.

Usage example:
echo "Insert Table Name ==> ";

$yourVarName = new Tables\Tablename\tablename;

//This will echo out all the columns in the table

foreach ($yourVarName->getColumns() as $column) {

 echo $column.", ";

}

// This will var_dump the schema data.

var_dump($yourVarName->getSchema());

// This will check the column names are valid and return any that are not

echo $yourVarName->checkColumns(array("id","name","age"));


Feel free to build on top of the default methods, but be aware that re-running
the dapper.php script will reset all alterations made - unless you modify dapper.php
to include your new methods (recommended).

You could, for example, add a method to the table class that allows you to
build a MySQL query, but check the validity of the column names being passed.

Trouble Shooting:

Fails to connect to database:
    make sure the credentials are correct

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU Lesser General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU Lesser General Public License for more details.

You should have received a copy of the GNU Lesser General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
