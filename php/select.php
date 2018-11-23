<?php

$datetimeUTC = gmdate("Y-m-d H:i:s");

if(isset($_REQUEST))
{

    error_reporting(E_ALL && ~E_NOTICE && E_COMPILE_ERROR);

    /*
    ***UNCOMMENT AND SET SRC WHEN TESTING***
    
    $config_php_file = 'HIDDEN';
    $breach_log_file = 'HIDDEN';
    $error_log_file = 'HIDDEN';
    */

    $config = require $config_php_file;

    $ip = $_SERVER["REMOTE_ADDR"];
    $script_filename = $_SERVER["SCRIPT_FILENAME"];
    $requestType = $_SERVER['REQUEST_METHOD'];

    //If this file was not called by an AJAX request
    if($requestType != "POST") {
        
        //Log access information in the file breach.log
        $access =  "[$datetimeUTC UTC] $ip - select.php DIRECT $script_filename";
        file_put_contents($breach_log_file, $access . PHP_EOL, FILE_APPEND);

        //Exit the program with an access denied message
        die("ACCESS DENIED!  You are not permitted to access this page!  Your IP Address has been logged.  Further attempts to access this page will result in a permanent ban from accessing any resources on this domain.");

    }

    $database = $config['database'];

    $servername = $database['servername'];
    $username = $database['username'];
    $password = $database['password'];
    $dbname = $database['dbname'];

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {

        error_log("[$datetimeUTC UTC] Connection failed: " . $conn->connect_error . "\n", 3, $error_log_file);
        die("Connection failed: " . $conn->connect_error);

    }

    $ages = array();
    $wpm_sum = 0;
    $comprehension_sum = 0.00;
    $ewpm_sum = 0;

    $data = array();

    $sql = "SELECT age, wpm, comprehension, ewpm FROM v1";
    $result = $conn->query($sql);
    $rows = $result->num_rows;

    if ($result->num_rows > 0) {
        
        while($row = $result->fetch_assoc()) {
            
            array_push($ages, $row["age"]);
            $wpm_sum += $row["wpm"];
            $comprehension_sum += $row["comprehension"];
            $ewpm_sum += $row["ewpm"];

        }

    } else {
        error_log("[$datetimeUTC UTC] Error: " . $sql . $conn->error . "\n", 3, $error_log_file);
    }

    $conn->close();

    sort($ages);
    $ages_count = array_count_values($ages); 
    $age = array_search(max($ages_count), $ages_count);
    array_push($data, $age);

    $wpm = floor( $wpm_sum / $rows );
    array_push($data, $wpm);

    $comprehension = number_format( (float)($comprehension_sum / $rows ), 2, '.', '');
    array_push($data, $comprehension);

    $ewpm = floor( $ewpm_sum / $rows );
    array_push($data, $ewpm);

    echo $rows . " ";

    for ($i = 0; $i < count($data); $i++) {

        if ($i != count($data) - 1){
            echo $data[$i] . " ";
        } else {
            echo $data[$i];
        }
    }

    error_reporting(E_ALL && ~E_NOTICE && E_COMPILE_ERROR);

} else {
    error_log("[$datetimeUTC UTC] Indirect request made\n", 3, $error_log_file);
    die("ACCESS DENIED!  Indirect request made.");
}

?>