der('Content-type: application/json');

try {
    // a new MongoDB connection
    $conn = new Mongo('localhost');

    // connect to test database
    $db = $conn->test;

    // a new logs collection object
    $collection = $db->logs;

    // fetch all logs documents
    $cursor = $collection->find();

    // How many results found
    $num_docs = $cursor->count();

    if( $num_docs > 0 )
    {
        // loop over the results
        foreach ($cursor as $obj)
        {
            echo 'url: ' . $obj['url'] . "\n";
            echo 'http_response_code: ' . $obj['http_response_code'] . "\n";
            echo 'Stack: ' . $obj['stack'] . "\n";
	    echo 'Time: ' . $obj['time'] . "\n";
            echo "\n";
        }
    }
    else
    {
        // if no logs are found, we show this message
        echo "No logs found \n";
    }

    // close the connection to MongoDB 
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
