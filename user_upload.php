#!/usr/bin/php
<?php

$opts = getopt('u:p:h:', ['file:','create_table::','dry_run::','help']);

//check for --file directive and it's value, if --create_table directive is not set
if ((!isset($opts['file']) && !isset($opts['create_table'])) || (isset($opts['file']) && !isset($opts['create_table']) && empty($opts['file'])))
  usage('File', 'file is required');

//check for database setting directives and values for -u -p -h
if ((!isset($opts['u']) || !isset($opts['p']) || !isset($opts['h'])) || (empty($opts['u']) || empty($opts['p']) || empty($opts['h'])))
  usage('Database directives', 'Database directives are required');
  
//output the help directive and exit
if (isset($opts['help']))
  usage('help', 'File directives');

//run the process
processCSV($opts);

function processCSV($opts){

  /*
   include the config file
   use the config file to change:
   1. database name
   2. database table name
   3. if csv file uses headers 
  */

  require_once __DIR__.'/config.php';

  $file = __DIR__ ."/".$opts['file'];
  //does the csv file contain a header row
  $createTable = isset($opts['create_table']);
  $dryRun = isset($opts['dry_run']);
  $dbUser = $opts['u'];
  $dbPass = $opts['p'];
  $dbPass = "";
  $dbHost = $opts['h'] ?? 'localhost';
  
  //connect to the database
  try {
    $connection = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
 
    // did we connected?
    if (mysqli_connect_errno()) {
      printf("Connect failed: %s\n", mysqli_connect_error());

      if (mysqli_connect_errno() === 1049){
        //create the database
        $connection = mysqli_connect($dbHost, $dbUser, $dbPass);
        echo "creating database " . $dbName . PHP_EOL;
        $sql = "CREATE DATABASE $dbName;";
        if (!mysqli_query($connection,$sql)){
          printf("Error message: %s\n", mysqli_error($connection));
          exit;  
        }
        $sql = "USE $dbName;";
        if (!mysqli_query($connection,$sql)){
          printf("Error message: %s\n", mysqli_error($connection));
          exit;  
        }
        //create the db table
        createTable($connection,$dbTable);
      } else {
        echo "Error: Unable to connect to MySQL." . PHP_EOL;
        echo "Debugging errno: " . mysqli_connect_errno() . PHP_EOL;
        echo "Debugging error: " . mysqli_connect_error() . PHP_EOL;
        exit;  
      }
    }

    //is create_table directive set?
    if ($createTable){
      echo "Drop and create the database table" . PHP_EOL;
      $sql = "DROP TABLE IF EXISTS `$dbTable`;";
      if (!mysqli_query($connection,$sql)){
        // an error eoccurred
        printf("Error message: %s\n", mysqli_error($connection));
      }

      createTable($connection,$dbTable);
      exit("Exit - create_table directive");
    } 

    if (($h = fopen($file, "r")) !== FALSE) {
      //if the csv file has a header row, skip the first line of the file
      if ($hasHeader) fgetcsv($h, 1000, ",");
      $dryRunOutput = 0;
      while (($data = fgetcsv($h, 1000, ",")) !== FALSE) {		
        
        //set and clean name, surname, email
        $name = mysqli_real_escape_string($connection, ucfirst(strtolower(trim($data[0]))));;
        $surname = mysqli_real_escape_string($connection, ucfirst(strtolower(trim($data[1]))));;
        $email = mysqli_real_escape_string($connection, strtolower(trim($data[2])));;
        //validate the email address and insert
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
          
          $sql = "insert into $dbTable (`name`, `surname`, `email`) values ('$name', '$surname', '$email')";

          if (!$dryRun){
            if (!mysqli_query($connection,$sql)){
              printf("Error message: %s\n", mysqli_error($connection));
            }
          } else {
            if (!$dryRunOutput) echo "This is a dry run, database will not be altered" . PHP_EOL;
            echo "Row details - $name $surname $email" . "\n";
            $dryRunOutput = 1;
          }
        } else {
          printf("Invalid email address: $email\n");
        }        

      }
      // Close the file
      fclose($h);
    } else {
      exit("Cannot open the csv file");
    }
 
    mysqli_close($connection);

  } catch (Exception $e) {
    exit('Error: '.$e->getMessage());
  }
}

function createTable($connection,$dbTable){
  
  $sql = "CREATE TABLE `$dbTable` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `name` VARCHAR(50) NOT NULL,
    `surname` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) NOT NULL,
    UNIQUE KEY unique_email (`email`)
    );";

  if (!mysqli_query($connection,$sql)){
    exit("Error message: %s\n". mysqli_error($connection));
  }

}

function usage($name, $message = NULL){
  
    $message = NULL != $message ? $message.PHP_EOL : $message;
    
    $usage = <<<_USAGE_

    Process a CSV file and parse file data to be inserted into a MySQL database.
   
    OPTIONS:
        --file [csv file name] – this is the name of the CSV to be parsed
        --create_table – this will cause the MySQL users table to be built (and no further action will be taken)
        --dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered
        -u – MySQL username
        -p – MySQL password
        -h – MySQL host


_USAGE_;

if ($name === 'help') exit($usage);
else exit($message);

}
