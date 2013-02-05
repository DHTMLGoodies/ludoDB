##Overview
LudoDB is a PHP Framework for easy creation and manipulation of mySQL tables using PHP. It supports both
PDO and MysqlI. PDO with prepared queries is the default database adapter.

Support for other databases can easily be implemented by creating a new database adapter which implements
the LudoDBAdapter PHP interface.

LudoDB will soon merge with LudoJS for easy, clean and fast development of rich web applications.

To check out from command line (With Git installed on your computer):

	git clone https://github.com/DHTMLGoodies/ludoDB.git ludoDB

To try it out, open one of the url

    http://<your server name>/ludoDB/examples/mod_rewrite/index.php

This examples requires that the rewrite module(mod_rewrite) has been activated
on your Apache web server.

###License
LudoDB is open source software according to the LGPL license.

###Setup database connection.
The code to establish a connection to your database is:

```PHP
<?php
LudoDB::setHost('<host>');
LudoDB::setUser('<user>');
LudoDB::setPassword('<password>');
LudoDB::setDb('<db name>');
```

The default, and preferred database adapter is PDO. You can switch to MySqlI with this code:

```PHP
<?php
LudoDB::setConnectionType('MYSQLI');
```

LudoDB will establish a connection to your database when it needs to.

##Examples:
Here are some examples of use:

###Example: Create model:
```PHP
<?php

class Person extends LudoDBModel
{
	protected $idField = 'id';
	protected $config = array(
		'table' => 'Person',
		'columns' => array(
			'id' => 'int auto_increment not null primary key',
			'firstname' => 'varchar(32)',
			'lastname' => 'varchar(32)',
			'address' => 'varchar(64)',
			'zip' => 'varchar(5)'
		),
		'join' => array(
			array('table' => 'city', 'pk' => 'zip', 'fk' => 'zip', 'columns' => array('city'))
		)

	);

	public function __construct($id){
	 parent::__construct($id);
	}

	public function setFirstname($value){
		$this->setValue('firstname', $value);
	}

	public function setLastname($value){
		$this->setvalue('lastname', $value);
	}

	public function setZip($value){
		$this->setValue('zip', $value);
	}

	public function getFirstname(){
		return $this->getValue('firstname');
	}

	public function getLastname(){
		return $this->getValue('lastname');
	}

	public function getZip(){
		return $this->getValue('zip');
	}

	public function getCity(){
		return $this->getValue('city');
	}
}

?>
```
###Example: Create database table:
```PHP
<?php
$person = new Person();
if(!$person->exists())$person->createTable();
```
###Example: Use a model:
Create a new Person record and save it to the database:
```PHP
<?php
$person = new Person();
$person->setFirstname('John');
$person->setLastname('Wayne');
$person->commit();
?>
```
Output Person data:
```PHP
<?php
echo $person->getId();
echo $person->getFirstname();
echo $person->getLastname();
?>
```
Update lastname of Person with id=1 to "Johnson":
```PHP
<?php
$person = new Person(1);
$person->setLastname('Johnson');
$person->commit();
?>
```
Output all Person details as JSON:
```PHP
<?php
echo $person; // Call the __toString() method of Person
?>
```

You can also configure data models using JSON:

###Example: Creating a model using external JSON file:

PHP Class (Client.php)
```PHP
<?php
class Client extends LudoDBModel
{
	protected $JSONConfig = true;

	public function __construct($id){
		parent::__construct($id);
	}

}
```
####JSON file(Client.json) located in sub folder JSONConfig:
```JSON
{
	"table":"Client",
	"sql":"select * from client where id=?",
	"idField":"id",
	"columns":{
		"id":"int auto_increment not null primary key",
		"firstname":{
			"db": "varchar(32)",
			"access":"rw"
		},
		"lastname":{
			"db": "varchar(32)",
			"access": "rw"
		},
		"address":{
			"db": "varchar(64)",
			"access": "rw"
		},
		"zip":{
			"db": "varchar(5)",
			"access": "rw",
			"references" : "city(zip) on delete cascade"
		},
		"phone":{
			"class":"PhoneCollection"
		},
		"city":{
			"class":"City",
			"get":"getCity"
		}

	},
	"classes":{
		"city":{
			"fk":"zip"
		}
	}
}
```

Which gives you automatic setters and getters for lastname, firstname, address and zip.

###Example: LudoDB Request handler
LudoDB is intended for use with LudoJS Javascript framework. It acts as a router or controller.
The LudoDBRequestHandler handles requests and passes them to the correct LudoDBModel. Example:
```PHP
<?php
$request = array(
	'request' => 'Person/2/read'
);
$handler = new LudoDBRequestHandler();
echo $handler->handle($request);
```

Will give you the values for person where ID is set to 2. The handler will output response in JSON format:

###Example: JSON response from LudoDBRequestHandler
```Javascript
{
	"success": true,
	"message": "",
	"response": {
		"firstname": "Anna",
		"lastname": "Westwood"
	}
}
```

* Classes available for the LudoDBRequestHandler needs to implement the LudoDBService interface.

###Example: Save data, using request handler.

For save, you can use code like this:

```PHP
<?php
/** Assuming that $_POST['request'] is
	array(
		'request' => 'Person/1/save',
		'data' => array(
			'firstname' => 'Mike'
		)
	);
*/
$handler = new LudoDBRequestHandler();
echo $handler->handle($_POST['request']);
```

Which will set first name of person with ID 1 to Mike.

##Request handler using Apache mod_rewrite

The request handler can also be configured using Apache mod_rewrite. The request attribute in the example above is then no longer needed.
instead, the request is defined in the url. Examples:

	http://localhost/Person/1/read
	http://localhost/Store/1/products

The last example may return a list of all products in Store where ID is 1.

Here's an example of a Router.php file for mod_rewrite requests.
```PHP
<?php

require_once(__DIR__."/autoload.php");

LudoDB::setUser('myDbUser');
LudoDB::setPassword('myDbPassword');
LudoDB::setHost('localhost');
LudoDB::setDb('myDb');

LudoDB::enableLogging();

$request = $_GET['request'];

if(isset($_POST['request'])){
	$request['data'] = $_POST['data'];
}

$handler = new LudoDBRequestHandler();
echo $handler->handle($request);
```

For this to work, the mod_rewrite module must be enabled in httpd.conf. You will also need an .htaccess file in the
same folder as router.php. Example:

```
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^([a-zA-Z0-9\/_]+)/?$ router.php?request=$1 [QSA]

RewriteCond %{THE_REQUEST} router\.php
RewriteRule ^router\.php - [F]
```

Requests for

```
http://myServer/Game/1/read
```

will then be redirected to router.php with "/Game/1/read" as the $_GET['request'] param.

###JSON config specification.
This is an example of the available properties for a JSON config file for LudoDBModel
and LudoDBCollection classes

```JSON
{
    "table": "game",
    "sql": "select * from game where id=?",
    "columns": {
        "id": "int auto_increment not null primary key",
        "database_id" :{
            "db": "int",
            "access": "rw",
            "references": "database(id) on delete cascade",
            "alias": "database"
        }
        "Moves": {
            "class": "Moves",
            "fk": "id"
        }
    },
    "data": [
        { "id": "1", "database_id": "1"},
        { "id": "2", "database_id": "1"},
        { "id": "3", "database_id": "1"}
    ],
    "indexes": ["database_id"]
}
```

* table: Name of database table
* sql: sql to execute when object is created. Question mark is used as a placeholder
for the arguments passed to the constructor.
* columns: Configuration of columns
* "id": "int auto_increment not null primary key" is example of the most simple configuration. It's
the same as writing "id": { "db": "int auto_increment not null primary key" }
* db : Column specification
* access: "w" for write access, and "r" for read access. Is this column public or private.
"w" makes the column writable via the save method. "r" makes the column readable from the
read and getValues() method. "rw" makes it both readable and writable. You can still modify and
read the value of the column internally using setValue and getValue.
* alias: Public name of column if different than the name of the column in the database. One
example is a chess move where you have columns like "from" and "to", i.e. the name of
squares on a chess board. "from" is not a good column name for a database, but a good
public name. The config may the look like this:
```JSON
"from_square":{
   "db": "varchar(2)",
   "alias": "from"
}
```
The read method will then return "from" as column name instead of "from_square". The save
method will support both "from" and "to_square" and do the mapping when saving the column
value to the database.
* references: Specifies constraint, example: "references database(id) on delete cascade",
* "class": Name of external/child LudoDBObject class.
* "fk": Name of column to use when instantiating external class, example: "id". In the
example above, the sql for "Moves" may be like this : "select * from moves where game_id=?"
where "id" of this game will be inserted at the placeholder question mark.
* data: Either an array of default data which are inserted when the table is created or
a string specifying the path to a JSON file with the default data, example: game.data.json.
LudoDB looks for the file inside the JSONConfig sub folder.
* indexes: Array of indexed columns.

For LudoDBCollection, you also have a "model" property which is the name of a LudoDBModel
class, example:

```JSON
{
    "sql": "select * from moves where game_id=?",
    "model": "Moves"
}


