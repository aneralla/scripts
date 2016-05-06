<?php

//$bypass = $_GET["secret"]; ## Secret door

//if ($bypass != "lumberjack") {
 //               header('Content-Type: application/json');
  //              $response = array('status' => 'INVALID', 'reason' => 'Unauthorized Use');
   //             echo json_encode($response);
    //                    exit;
     //           }


$json = file_get_contents('php://input');
$obj=json_decode($json,true);

try {
        // Connect to MongoDB
        $conn = new Mongo('localhost');

        // connect to test database
        $db = $conn->test;

        // a new products collection object
        $collection = $db->logs;

        $url=$obj["url"];
        $stack=$obj["stack"];
        $env=$obj["env"];
        $http_response_code=$obj["http_response_code"];
        $time=$obj["time"];

        if (($url != "") && ($http_response_code != "") && ($stack != "")) {

        // Create an array of values to insert 
        $logs = array(
                        'url' => $url,
                        'http_response_code' => $http_response_code,
                        'stack' => $stack,
                        'env' => $env, 
                        'time'  => $time
  			);

        // insert the array
        $collection->insert( $logs );

        echo 'log inserted with ID: ' . $logs['_id'] . "\n";
	echo 'log inserted with URL: ' . $logs['url'] . "\n";
	// close connection to MongoDB
        $conn->close();

       }
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
