##Overview
LudoDB is a PHP Database and Web Service framework especially designed for Web applications using JSON for communication
between server and client. It makes development of MySql tables easy using JSON config files.

LudoDB supports both PDO and MysqlI. PDO with prepared queries is the default database adapter.

Support for other databases can easily be implemented by creating a new database adapter which implements
the LudoDBAdapter PHP interface.

LudoDB will soon merge with LudoJS for easy, clean and fast development of rich web applications.

To check out from command line (With Git installed on your computer):

	git clone https://github.com/DHTMLGoodies/ludoDB.git ludoDB

To try it out, open the url

    http://<your server name>/ludoDB/examples/mod_rewrite/index.php

This examples requires that the rewrite module(mod_rewrite) has been activated
on your Apache web server.

###License
LudoDB is open source software according to the LGPL license.


###Website
http://www.ludodb.com

###Documentation
http://www.ludodb.com/ludodb/docs

###Classes
This is an overview of the most important PHP classes in ludoDB:

* __LudoDBObject__: Abstract base class for LudoDBModel and LudoDBCollection
* __LudoDBModel__: Abstract class you extend to represent a database table, example: "Person"
* __LudoDBCollection__: Abstract class used to present collection of data rows
from the database, example: "People". ps! You can specify nested classes in
your LudoDBModel config.
* __LudoDBTreeCollection__: Abstract class used to present collection of data in
tree format.
* __LudoDBRequestHandler__: Class handling POST/GET requests and returning data
in JSON format.
* __LudoDBService__:Interface for classes/resources available for the LudoDBRequestHandler.
* __LudoDBProfiler__: LudoDBService class implementing XHPROF profiling. During development, you
can use this class to profile your PHP code. Example: profile the request Person/1/read, you can
call this service: ```http://hostname/LudoDBProfiler/Person/1/read/profile```.

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

###Example: LudoDBRequestHandler
A LudoDBRequestHandler is used to handle requests from a web page and pass them to the correct
LudoDBService(interface). You typically created an instance of this class in a PHP controller, i.e.
the connection point between the GUI of your web application and the server framework.

Example:
```PHP
<?php
$request = array(
	'request' => 'Person/2/read'
);
$handler = new LudoDBRequestHandler();
echo $handler->handle($request);
```

Will show you the values for person where ID is set to 2. The __request__ attribute contains tokens separated
by a slash. The first token("Person") is the name of a resource/Class. The last token("read") is the name of a service method
implemented by the Resource class. The arguments in between, here "2" are arguments sent to the the
constructor when an instance of the resource is created.

For a request like above, the following will
be done internally:

```PHP
$person = new Person(2);
return $person->read();
```

The handler will output response in JSON format:

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

* __"data"__ contains values sent to the service method "save".

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

if(isset($_POST['data'])){
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

###Public methods
Here are some of the public methods for LudoDBModel and LudoDBCollection:

* read - Return database values as array
* save(array $data) - Saving new data to the database
* delete - Delete record

###Protected methods:
Here are some of the protected methods which is good to know about:

* setValue($column, $value) - Store internal value
* getValue($column) - Return column value
* commit() - Commit changes to the database

###JSON config specification.

####LudoDBModel config
This is an example of JSON config for a LudoDBModel class called "Game":

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
        },
        "creator": {
            "db": "varchar(128)",
            "default": "NA"
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
    "static": {
        "type": "database"
    }
    "indexes": ["database_id"]
}
```

* __table__: Name of database table
* __sql__: sql to execute when object is created. Question mark is used as a placeholder
for the arguments passed to the constructor.
* __columns__: Configuration of columns
* __id__: "int auto_increment not null primary key" is example of the most simple configuration. It's
the same as writing ```"id": { "db": "int auto_increment not null primary key" }```
* __db__: : Column specification
* __access__: "w" for write access, and "r" for read access. Is this column public or private.
"w" makes the column writable via the save method. "r" makes the column readable from the
read and getValues() method. "rw" makes it both readable and writable. You can still modify and
read the value of the column internally using setValue and getValue.
* __alias__: Public name of column if different than the name of the column in the database. One
example is a chess move where you have columns like "from" and "to", i.e. the name of
squares on a chess board. "from" is not a good column name for a database, but a good
public name. The config may the look like this:
```
"from_square":{
   "db": "varchar(2)",
   "alias": "from"
}
```
The read method will then return "from" as column name instead of "from_square". The save
method will support both "from" and "to_square" and do the mapping when saving the column
value to the database.
* __references__: Specifies constraint, example: "references database(id) on delete cascade",
* __default__: The default property specifies the default value for this column in the database.
* __class__: Name of external/child LudoDBObject class.
* __fk__: Name of column to use when instantiating external class, example: "id". In the
example above, the sql for "Moves" may be like this : "select * from moves where game_id=?"
where "id" of this game will be inserted at the placeholder question mark.
* __static__: Optional array of additional static properties not stored in the database,
example: { "type": "country" }. This is useful in tree collection where you might want
to distinguish between different type of rows, example "city" and "country".
* __data__: Either an array of default data which are inserted when the table is created or
a string specifying the path to a JSON file with the default data, example: game.data.json.
LudoDB looks for the file inside the JSONConfig sub folder.
* __indexes__: Array of indexed columns.


###LudoDBCollection
The config for LudoDBCollection is much simpler. You have three available properties, __sql__, __model__
and __groupBy__

Example: Class called "City":

```JSON
{
    "sql": "select city.id,city.name,state.state from city,state where city.state_id = state.id and state.country=?",
    "model": "City",
    "groupBy": "state"
}
```

* __model__: Name of a LudoDBModel class representing each row in the collection.
When a model property is set, LudoDBCollection will read values from the LudoDBModel
for each returned row. This will make you able to hide read-only rows and return alias keys names
for rows.
* __groupBy__: Name of column in the result set. groupBy will returned rows grouped by given column.

Example:

```JSON
{
    "Texas":[
        { "id" : 1, "name": "Houston" },
        { "id" : 2, "name": "Austin" }
    ],
    "California":[
        {  "id": 3, "name": "San Fransisco" },
        {  "id": 4, "name": "California" }
    ]
}

```

###LudoDBTreeCollection
The LudoDBTreeCollection class is used to present rows from __one__ table in
tree format. It extends the LudoDBCollection class.

In the config of the class, you'll need to  specify three properties:

* __fk__: name of __foreign key__ column, i.e. column refering to parent key
* __pk__: name of the __primary key__, the column used to identify parents.
* __childKey__: Children will be placed inside an array with this key.
* __merge__: Array of other LudoDBCollection objects to merge into the tree.
* __hideForeignKeys__: true to hide foreign keys in merged collections.

Example:

__PHP Class Nodes__

```PHP
class Nodes extends LudoDBTreeCollection implements LudoDBService
{
    protected $JSONConfig = true;

    // Validate arguments sent to constructor
    public function validateArguments($service, $arguments){
        return count($arguments) === 0;
    }
    // Validate data sent to service method
    public function validateServiceData($service, $arguments){
        return true;
    }

    public function getValidServices(){
        return array('read');
    }

    public function shouldCache($service){
        return $service === "read;
    }
}

```

__JSONConfig/Nodes.json__

```JSON
{
    "sql" : "select * from node order by parent,id",
    "fk": "parent",
    "pk": "id",
    "childKey": "children",
    "hideForeignKeys" : true,
    "merge" : [
        {
            "class" : "LeafNode",
            "fk" : "parent_node_id",
            "pk" : "id"
        }

    ]
}
```

__PHP Class LeafNode__

```PHP
<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 09.02.13
 * Time: 14:15
 */
class LeafNode extends LudoDBModel
{
    protected $config = array(
        "table" => "leaf_node",
        "columns" => array(
            "id" => "int auto_increment not null primary key",
            "name" => array(
                "db" => "varchar(32)",
                "access" => "rw"
            ),
            "parent_node_id" => array(
                "db" => "int",
                "access" => "rw",
                "references" => "test_node(id) on delete cascade"
            )
        ),
        "static" => array(
            "type" => "leaf"
        )
    );
}
```

__PHP Class LeafNodes returning a list of LeafNode rows:__

```PHP
<?php
class LeafNodes extends LudoDBCollection
{
    protected $config = array(
        "sql" => "select * from leaf_node order by id",
        "model" => "LeafNode"
    );
}
```

The following code:

```PHP
<?php
$obj = new Nodes();
echo $obj;
```

Will return something like:

```JSON
[
    {
        "id":"1",
        "title":"Node 1",
        "children":[
            {
                "id":"3",
                "title":"Node 1.1",
                "children":[
                    {
                        "id":"6",
                        "title":"Node 1.1.1"
                    },
                    {
                        "id":"7",
                        "title":"Node 1.1.2"
                    },
                    {
                        "id":"100",
                        "title": "Leaf node",
                        "type":"Leaf"
                    }
                ]
            },
            {
                "id":"4",
                "title":"Node 1.2"
            },
            {
                "id":"5",
                "title":"Node 1.3",
                "children":[
                    {
                        "id":"8",
                        "title":"Node 1.1.2.1",
                        "parent":"5"
                    },
                    {
                        "id":"9",
                        "title":"Node 1.1.2.2",
                        "parent":"5"
                    }
                ]
            }
        ]
    },
    {
        "id":"2",
        "title":"Node 2",
        "parent":null,
        "children":[
            {
                "id":"13",
                "title":"Node 2.1",
                "parent":"2"
            },
            {
                "id":"14",
                "title":"Node 2.2",
                "parent":"2"
            }
        ]
    }
]
```

___Implement with LudoJS___
LudoDBObject classes(LudoDBModel and LudoDBCollection) can be configured to output config objects in JSON format for the
LudoJS Javascript framework.

This is done by specifying a ludoJS object in the configuration of your columns.

Example

```PHP
<?php
/**
 * Comment pending.
 * User: Alf Magne Kalleland
 * Date: 13.04.13
 * Time: 16:37
 */
class LudoJSPerson extends LudoDBModel implements LudoDBService
{
    protected $config = array(
        'table' => 'LudoJSPerson',
        'columns' => array(
            'id' => array(
                'db' => 'int auto_increment not null primary key',
                'ludoJS' => array(
                    'type' => 'form.Hidden'
                )
            ),
            'lastname' => array(
                'db' => 'varchar(32)',
                'ludoJS' => array(
                    'type' => 'form.Text',
                    'order' => 2
                ),
                "access" => "rw"
            ),
            'firstname' => array(
                'db' => 'varchar(32)',
                'ludoJS' => array(
                    'type' => 'form.Text',
                    'order' => 1
                ),
                "access" => "rw"
            ),
            "country" => array(
                "db" => "int",
                "references" => "LudoJSCountry(id)",
                "ludoJS" => array(
                    'valueKey' => 'id',
                    'textKey' => 'name',
                    'type' => 'form.Select',
                    'order' => '4',
                    'dataSource' => 'LudoJSCountries'
                ),
                "access" => "rw"
            ),
            "address" => array(
                "db" => "varchar(4000)",
                "ludoJS" => array(
                    'type' => 'form.Textarea',
                    'order' => 3,
                    'layout' => array(
                        'weight' => 1
                    )
                ),
                "access" => "rw"
            )
        ),
        "static" => array(
            "type" => array(
                "value" => "person",
                "ludoJS" => array(
                    'type' => 'form.Hidden'
                ),
                "access" => "rw"
            )
        ),
        "data" => array(
            array("firstname" => "John", "lastname" => "Johnson", "country" => 131, "address" => "Main street 99")
        )
    );

    public function validateArguments($service, $arguments){
        return true;
    }

    public function validateServiceData($service, $data){
        return true;
    }

}
```

The ludoJS configuration supports all the properties defined in LudoJS.

The most important is "type" which defines which kind of ludoJS view to show for this column, example: "form.Text" for a
form text input field.

In the example above, you also have a select box which is populated with countries. This is done by

* Defining a dataSource to the name of a LudoDBCollection, i.e. "dataSource": "LudoJSCountries"

The LudoJSCountries class looks like this:

```PHP
class LudoJSCountries extends LudoDBCollection implements LudoDBService
{
    protected $config = array(
        "sql" => "select * from LudoJSCountry order by name"
    );

    public function validateArguments($service, $arguments){
        return empty($arguments);
    }

    public function validateServiceData($service, $data){
        return empty($data);
    }
}
```

This is an example of how you configure this in LudoJS:

```Javascript
<script type="text/javascript">
    var w = new ludo.Window({
        title:'LudoDB Integration',
        layout:{
            'width':500, height:400
        },
        children:[
            {
                'layout':{
                    type:'linear',
                    orientation:'vertical'
                },
                'ludoDB':{
                    'resource':'LudoJSPerson',
                    'arguments':1,
                    'url':'../ludoDB/router.php'
                }
            }
        ],
        buttons:[
            { type:'form.SubmitButton', value:'Save' },
            { type:'form.CancelButton', value:'Cancel' }
        ]
    });

</script>
```

Notice the ludoDB config object. It refers to the _LudoJSPerson_ resource and loads instance with id 1.

_April, 13th, 2013_: LudoJS integration is currently a work in progress. A lot more features will be added. An example

