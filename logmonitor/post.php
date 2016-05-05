<?php
try {
        // Connect to MongoDB
        $conn = new Mongo('localhost');

        // connect to test database
        $db = $conn->test;

        // a new products collection object
        $collection = $db->logs;

        // Create an array of values to insert 
        $logs = array(
                        'url' => '/test.html',
                        'http_response_code' => '500',
                        'stack' => 'mediaservice'
                        );

        // insert the array
        $collection->insert( $logs );

        echo 'log inserted with ID: ' . $logs['_id'] . "\n";

        // close connection to MongoDB
        $conn->close();

}
catch ( MongoConnectionException $e )
{
        // if there was an error, we catch and display the problem here
        echo $e->getMessage();
}
catch ( MongoException $e )
{
        echo $e->getMessage();
}

?>
