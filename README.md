##Overview
LudoDB is a PHP Framework for easy creation and manipulation of mySQL tables using PHP. It supports both
PDO and MysqlI. PDO with prepared queries is the default database adapter.

Support for other databases can easily be implemented by creating a new database adapter which implements
the LudoDBAdapter PHP interface.

LudoDB will soon merge with LudoJS for easy, clean and fast development of rich web applications.

To check out from command line (With Git installed on your computer):

	git clone https://github.com/DHTMLGoodies/ludoDB.git

###Setup database connection.
The code to establish a connection to your database is:

	<?php
	LudoDB::setHost('<host>');
	LudoDB::setUser('<user>');
	LudoDB::setPassword('<password>');
	LudoDB::setDb('<db name>');

The default, and preferred database adapter is PDO. You can switch to MySqlI with this code:

	<?php
	LudoDB::setConnectionType('MYSQLI');

LudoDB will establish a connection to your database when it needs to.

##Examples:
Here are some examples of use:

###Example: Create database table

###Example: Create model:

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
###Example: Create database table:
	<?php
	$person = new Person();
	if(!$person->exists())$person->createTable();

###Example: Use a model:

	<?php
	$person = new Person();
	$person->setFirstname('John');
	$person->setLastname('Wayne');
	$person->commit();
	?>

For creating a new Person record and save it to the database

	<?php
	echo $person->getId();
	echo $person->getFirstname();
	echo $person->getLastname();
	?>

Will output data for this record.

	<?php
	$person = new Person(1);
	$person->setLastname('Johnson');
	$person->commit();
	?>

Will update lastname in db for person with id=1

	<?php
	echo $person; // Call the __toString() method of Person
	?>

will output person data in JSON format.

You can also configure the database in json files:

###Example: Creating a model using external JSON file:

PHP Class (Client.php)

	<?php
	class Client extends LudoDBModel
	{
		protected $JSONConfig = true;

		public function __construct($id){
		 	parent::__construct($id);
		}

	}

####JSON file(Client.json) located in sub folder JSONConfig:

	{
		"table":"Client",
		"idField":"id",
		"constructBy":"id",
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
				"access": "rw"
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

Which gives you automatic setters and getters for lastname, firstname, address and zip.

###Example: LudoDB Request handler
LudoDB is intended for use with LudoJS Javascript framework. It acts as a router or controller.
The LudoDBRequestHandler handles requests and passes them to the correct LudoDBModel. Example:
	<?php
	$request = array(
		'request' => 'Person/2/read'
	);
	$handler = new LudoDBRequestHandler();
	echo $handler->handle($request);

Will give you the values for person where ID is set to 2. The handler will output response in JSON format:

###Example: JSON response from LudoDBRequestHandler
	{
		"success": true,
		"message": "",
		"response": {
			"firstname": "Anna",
			"lastname": "Westwood"
		}
	}

###Example: Save data, using request handler.

For save, you can use code like this:

	<?php
	/** Assuming that $_POST['request'] is
		array(
			'request' => 'Person/1/save',
			'data' => array(
				'firstname' => 'Mike'
			)
		);

	$handler = new LudoDBRequestHandler();
	echo $handler->handle($_POST['request']);

Which will set first name of person with ID 1 to Mike
Support for handling requests using Apache mod_rewrite will be added soon. The "request" property in the example
above will then no longer be needed. Instead, the request is specified in requested url. Examples:

	http://localhost/Person/1/read
	http://localhost/Store/1/products

The last example may return a list of all products in Store where ID is 1.

