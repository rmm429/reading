<?php

$datetimeUTC = gmdate("Y-m-d H:i:s");

if(isset($_REQUEST))
{

    error_reporting(E_ALL && ~E_NOTICE && E_COMPILE_ERROR);

    /*
    ***UNCOMMENT AND SET SRC WHEN TESTING***
    
    $breach_log_file = 'HIDDEN';
    $$blocked_file = 'HIDDEN';
    $error_log_file = 'HIDDEN';
    */

    $ip = $_SERVER["REMOTE_ADDR"];
    $script_filename = $_SERVER["SCRIPT_FILENAME"];
    $requestType = $_SERVER['REQUEST_METHOD'];

    $ip_log = array();

    $breach_log = file($breach_log_file);

    foreach($breach_log as $line_num => $line) {

        //26 junk characters, IP begins on character 27
        $ip_end = strpos($line, ' ', 26);

        $ip_cur = substr($line, 26, $ip_end - 26);
        array_push($ip_log, $ip_cur);

    }

    $ip_dups = array_count_values($ip_log);

    //Create an array of blocked IP Addresses
    $blocked = array();

    foreach ($ip_dups as $i => $d) {
        
        if ($d >= 3) {
            array_push($blocked, $i);
        }

    }

    //Erase the old backup file
    unlink($blocked_file);

    //Store each blocked IP Address in a backup file
    foreach ($blocked as $b) {
        file_put_contents($blocked_file, $b . PHP_EOL, FILE_APPEND);
    }

    //If this file was accessed by a blocked IP Address
    if ( in_array($ip, $blocked) ) {

        //Log access information in the breach log
        $access =  "[$datetimeUTC UTC] $ip BLOCKED IP - config.php $script_filename";
        file_put_contents($breach_log_file, $access . PHP_EOL, FILE_APPEND);

        //Exit the program with an access denied message
        die("ACCESS DENIED!  You are permanently banned from accessing any resources on this domain!");

    }

    //If this file was not called by an AJAX request
    if ($requestType != "POST") {

        //Log access information in the file breach.log
        $access =  "[$datetimeUTC UTC] $ip - config.php DIRECT $script_filename";
        file_put_contents($breach_log_file, $access . PHP_EOL, FILE_APPEND);

        //Exit the program with an access denied message
        die("ACCESS DENIED!  You are not permitted to access this page!  Your IP Address has been logged.  Further attempts to access this page will result in a permanent ban from accessing any resources on this domain.");
        
    }

    return [

        'blocked' => $blocked,

        'database' => [

            /*
            ***UNCOMMENT AND SET SRC WHEN TESTING***

            'servername' => 'HIDDEN',
            'username' => 'HIDDEN',
            'password' => 'HIDDEN',
            'dbname' => 'HIDDEN',
            'options' => []
            */

        ]

    ];

    error_reporting(E_ALL && ~E_NOTICE);

} else {
    error_log("[$datetimeUTC UTC] Indirect request made\n", 3, $error_log_file);
    die("ACCESS DENIED!  Indirect request made.");
}

?>