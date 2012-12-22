<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
spl_autoload_register(
    function($class) {
        static $classes = null;
        if ($classes === null) {
            $classes = array(
                'alltests' => '/Tests/AllTests.php',
                'car' => '/Tests/classes/Car.php',
                'carcollection' => '/Tests/classes/CarCollection.php',
                'carproperties' => '/Tests/classes/CarProperties.php',
                'carproperty' => '/Tests/classes/CarProperty.php',
                'city' => '/Tests/classes/City.php',
                'collectiontest' => '/Tests/CollectionTest.php',
                'country' => '/Tests/classes/Country.php',
                'dbtest' => '/Tests/DBTest.php',
                'findertest' => '/Tests/FinderTest.php',
                'game' => '/Tests/classes/Game.php',
                'jsontest' => '/Tests/JSONTest.php',
                'ludodb' => '/LudoDB.php',
                'ludodbcollection' => '/LudoDbCollection.php',
                'ludodbiterator' => '/LudoDbIterator.php',
                'ludodbobject' => '/LudoDBObject.php',
                'ludodbtable' => '/LudoDbTable.php',
                'ludofinder' => '/LudoFinder.php',
                'ludosql' => '/LudoSQL.php',
                'metadata' => '/Tests/classes/Metadata.php',
                'metadatavalue' => '/Tests/classes/MetadataValue.php',
                'person' => '/Tests/classes/Person.php',
                'phone' => '/Tests/classes/Phone.php',
                'phonecollection' => '/Tests/classes/PhoneCollection.php',
                'sqltest' => '/Tests/SQLTest.php',
                'testbase' => '/Tests/TestBase.php',
                'testtable' => '/Tests/classes/TestTable.php'
            );
        }
        $cn = strtolower($class);
        if (isset($classes[$cn])) {
            require __DIR__ . $classes[$cn];
        }
    }
);
// @codeCoverageIgnoreEnd