<?php
// Be adviced - SQL database MUST have 'userid' INT, AUTO_INCREMENT field as first column
// All othed field MUST have default values ('null' at least)

// NEW! convert $_REQUEST superglobal variable from JSON to array to read cmd: instruction
$incoming = file_get_contents('php://input');
$_REQUEST = json_decode($incoming, true);
//$_REQUEST = file_get_contents('php://input');
require("w2ui/w2db.php");
require("w2ui/w2lib.php");
require("dbConfig.php");
// put your data table name from "dbname' base in settings.php
include ('settings.php');

// get Primary key (table must have primary key)
try {
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword,array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES utf8"));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    $sql = "SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$dbName."' AND (TABLE_NAME = '".$tablename."' AND COLUMN_KEY = 'PRI')";
    $q = $db->prepare($sql);
    $q->execute();
    $keyfield1 = $q->fetchALL(PDO::FETCH_ASSOC);     
} catch(PDOException $e) { echo "Database error " . $e->getMessage(); $e -> resetvalue(); }

$db = new dbConnection("mysql");
$db->connect($dbHost, $dbUsername, $dbPassword, $dbName, $dbPort);

$keyfield = $keyfield1[0]['COLUMN_NAME'];

if (isset($_REQUEST['action'])){
    $doaction = $_REQUEST['action'];
}else{
    if (isset($_REQUEST['cmd'])){
        $doaction = $_REQUEST['cmd'];
    }else{
        $doaction = 'get';
    }
}

switch ($doaction) {
    case 'get':
        if (array_key_exists('recid', $_REQUEST)){  // if true , then is a 'get-record' only one record with recid
            $sql = "SELECT * FROM ".$tablename;
            $res = $w2grid->getRecord($sql);
        }else{        
            $sql  = "SELECT * FROM ".$tablename." WHERE ~search~ ORDER BY ~sort~";
            $res = $w2grid->getRecords($sql, null, $_REQUEST);}  
        $w2grid->outputJSON($res);
        return $w2grid;   
        break;

    case 'newempty':
        // add new empty record
        $res = $w2grid->newrecord($tablename, $_REQUEST);
        $w2grid->outputJSON($res);
        break;

    case 'delete':
        
        $res = $w2grid->deleteRecords($tablename, $keyfield, $_REQUEST);
        $w2grid->outputJSON($res);
        break;

    case 'save':
        //change 'id' to file index
        if (isset($_REQUEST['changes'])){
            //for number of records
            
            foreach ($_REQUEST['changes'] as $value) {
                foreach ($value as $key => $cell){
                    if (isset($cell['id'])){    
                        $value[$key] = $cell['id'];
                    }   
                }

                $res = $w2grid->saveChanges($tablename, $keyfield, $value);
            }
            $w2grid->outputJSON($res);
            unset($value);
        }else{
            $res = $w2grid->saveRecord($tablename, $keyfield, $_REQUEST);
            $w2grid->outputJSON($res);
        }
        break;

    case 'nocookies':
        //if user log out
        $res = Array();
        $res['status']  = 'error';
        $res['message'] = 'No data, user loged out.';
        $res['postData']= $_REQUEST;
        $w2grid->outputJSON($res);
        break;

    default:
        $res = Array();
        $res['status']  = 'error';
        $res['message'] = 'Command "'.$doaction.'" is not recognized.';
        $res['postData']= $_REQUEST;
        $w2grid->outputJSON($res);
        break;
}

function logtofile($var)
{
        //---------------LOG----------------
          $name = print_r($var, JSON_UNESCAPED_UNICODE);
          $logFileName = "/var/www/html/carnet/log/antares.log"; 
          $message = "\nНачали запись\n"; 
          $message .= $name . "\n - обновлен" . date('d.m.Y H:i:s') . "\n"; 
          file_put_contents($logFileName, $message, FILE_APPEND);
          //---------------LOG----------------
}

?>