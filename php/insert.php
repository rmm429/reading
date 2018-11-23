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
        $access =  "[$datetimeUTC UTC]  $ip - insert.php DIRECT $script_filename";
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

    $level=$_POST['level'];
    $age=$_POST['age'];
    $words=$_POST['words'];
    $time=$_POST['time'];
    $wpm=$_POST['wpm'];
    $comp=$_POST['percentCorrect'];
    $ewpm=$_POST['ewpm'];

    if ( empty($level) && empty($age) && empty($words) && empty($time) && empty($wpm) && empty($comp) && empty($ewpm) ) {

        $access =  "[$datetimeUTC UTC]  $ip - insert.php EMPTY $script_filename";
        file_put_contents($breach_log_file, $access . PHP_EOL, FILE_APPEND);

        die("ACCESS DENIED!  You are not permitted to access this page!  Your IP Address has been logged.  Further attempts to access this page will result in a permanent ban from accessing any resources on this domain.");

    }

    $sql = "INSERT INTO v1(datetimeUTC, level, age, words, time, wpm, comprehension, ewpm) VALUES ('$datetimeUTC', '$level', $age, $words, $time, $wpm, $comp, $ewpm)";

    if ($conn->query($sql) !== TRUE) {
        error_log("[$datetimeUTC UTC] Error: " . $sql . $conn->error . "\n", 3, $error_log_file);
    }

    $conn->close();

    error_reporting(E_ALL && ~E_NOTICE && E_COMPILE_ERROR);

} else {
    error_log("[$datetimeUTC UTC] Indirect request made\n", 3, $error_log_file);
    die("ACCESS DENIED!  Indirect request made.");
}

?>