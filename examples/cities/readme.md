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

This should give you this countent:

```JSON
{"success":true, "message":"", "code":200, "response":[
    {
        "id":"1",
        "name":"Norway",
        "states/counties":[
            {
                "id":"2",
                "name":"Hordaland",
                "country":"1",
                "cities":[
                    {
                        "id":"4",
                        "name":"Bergen"
                    }
                ]
            },
            {
                "id":"1",
                "name":"Rogaland",
                "country":"1",
                "cities":[
                    {
                        "id":"3",
                        "name":"Haugesund"
                    },
                    {
                        "id":"2",
                        "name":"Sandnes"
                    },
                    {
                        "id":"1",
                        "name":"Stavanger"
                    }
                ]
            }
        ]
    },
    {
        "id":"2",
        "name":"United States",
        "states/counties":[
            {
                "id":"4",
                "name":"California",
                "country":"2",
                "cities":[
                    {
                        "id":"8",
                        "name":"Los Angeles"
                    },
                    {
                        "id":"9",
                        "name":"San Diego"
                    },
                    {
                        "id":"7",
                        "name":"San Fransisco"
                    }
                ]
            },
            {
                "id":"3",
                "name":"Texas",
                "country":"2",
                "cities":[
                    {
                        "id":"6",
                        "name":"Austin"
                    },
                    {
                        "id":"5",
                        "name":"Houston"
                    }
                ]
            }
        ]
    }
], "log":{
    "time":0.02137303352356,
    "queries":3
}}
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

When using  LudoDBRequest handler, we'll have to implement the __LudoDBService__ interface. It contains
three methods:

* __getValidServices__: Static method returning array of available services, example: array('read');
* __validateService__: Method returning true if passed service and arguments ar valid.
* __cacheEnabled__: Returns true when the Request Handler is allowed to look into LudoDBCache for
values. This is very useful if you have collections with lot's of expensive database queries.

This is the DemoCountries class:
```PHP
class DemoCountries extends LudoDBCollection implements LudoDBService
{
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

    public function getValidServices(){
        return array("read");
    }

    public function validateService($service, $arguments){
        return count($arguments) === 0;
    }

    public function cacheEnabled(){
        return false;
    }
}
```

It's also possible to output data without using a LudoDBRequestHandler:

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

