#Demo of nested collections

##Configuration
Open index.php in an editor and set the correct database connection details:

```PHP
LudoDB::setDb("PHPUnit");
LudoDB::setUser("root");
LudoDB::setPassword("administrator");
LudoDB::setHost("127.0.0.1");
```

##Run in your web browser
The demo is best viewed using a browser with a JSON view addon/plugin
installed.

URL:
```
http://<path to your local server>/path/to/ludoDB/examples/cities/index.php
```

##Classes for the demo
This demo contains 3 LudoDBModel classes and 3 LudoDBCollection classes.

LudoDBModel's:
* __DemoCountry__ : Class representing countries
* __DemoState__: Class representing states/counties
* __DemoCity__: Class representing cities

The model classes are configured with default data which will
be inserted into the database when you first open the index.php
file in your web browser.

LudoDBCollection's:
* __DemoCountries__: Collection of all countries
* __DemoStates__: Collection of all states/counties.
* __DemoCitites__: Collection of all citiees.

In the DemoCountries collection, countries are merged with states:

```PHP
protected $config = array(
    "sql" => "select * from demo_country order by name",
    "childKey" => "states/counties",
    "merge" => array(
        array(
            "class" => "DemoStates",
            "fk" => "country",
            "pk" => "id"
        )
    )
);
```

In DemoStates, states are merged with cities.

```PHP
protected $config = array(
    "sql" => "select * from demo_state order by name",
    "model" => "DemoState",
    "childKey" => "cities",
    "hideForeignKeys" => true,
    "merge" => array(
        array(
            "class" => "DemoCities",
            "fk" => "state",
            "pk" => "id"
        )
    )
);
```

In index.php, we're creating a new LudoDBRequest handler and asks it
to handle the request "DemoCountries/read":

```PHP
$handler = new LudoDBRequestHandler();
echo $handler->handle("DemoCountries/read");
```

It's also possible to output data without using the LudoDBRequestHandler:

```PHP
$countries = new DemoCountries();
echo $countries; // trigger the __toString method
```

or

```PHP
$countries = new DemoCountries();
var_dump($countries->read()); // PHP Array
```

Only three database queries are executed on the server to output
the tree.

