###This is an example of LudoDBRequest handler using Apache mod_rewrite.
This example only works when mod_rewrite is enabled on your Apache httpd server.

Support for PDO MySql should also be enabled/installed.

###Configuration

Open Router.php

Change the DB config variables:

    LudoDB::setUser('root');
    LudoDB::setPassword('administrator');
    LudoDB::setHost('127.0.0.1');
    LudoDB::setDb('PHPUnit');

To configure connection to a MySql database.

Open the url to the mod_rewrite folder in your browser and add "Book/2" to the url. Example

    http://localhost/ludoDB/examples/mod_rewrite/Book/2

* Tip! For a nice view in your browser(Firefox or Chrome), install a JSON view plugin/addon.
