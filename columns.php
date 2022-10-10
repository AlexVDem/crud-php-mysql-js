<?php
// Load the database configuration file
require("dbConfig.php");
//change table name settings.php
include ('settings.php');

try {
    
    $db = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUsername, $dbPassword,array(PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES utf8"));
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Gets types of columns from DB schema...
    $sql = "SELECT COLUMN_NAME, COLUMN_COMMENT, DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = '".$dbName."' AND TABLE_NAME = '".$tablename."'";
    $q = $db->prepare($sql);
    $q->execute();
    $fields = $q->fetchALL(PDO::FETCH_ASSOC);

    if ($useforeignkeys){
        $sql1 = "SELECT i.TABLE_SCHEMA, i.TABLE_NAME, i.CONSTRAINT_TYPE, i.CONSTRAINT_NAME, k.COLUMN_NAME, k.REFERENCED_TABLE_NAME, k.REFERENCED_COLUMN_NAME 
                FROM information_schema.TABLE_CONSTRAINTS i LEFT
                JOIN information_schema.KEY_COLUMN_USAGE k ON i.CONSTRAINT_NAME = k.CONSTRAINT_NAME 
                WHERE i.CONSTRAINT_TYPE = 'FOREIGN KEY' AND (i.TABLE_SCHEMA = '".$dbName."' AND i.TABLE_NAME = '".$tablename."')";
        $q1 = $db->prepare($sql1);
        $q1->execute();
        $fieldsfk = $q1->fetchALL(PDO::FETCH_ASSOC);// get fields with foreign keys
    }

    //Get data for table reference
    if ($useindexkeys && isset($indexkeyjson)){
        $indexkey = json_decode($indexkeyjson, true);
        
        foreach($indexkey as $key => $value){
            $sql2 = "SELECT ".$value['id'].", ".$value['name']." FROM ".$key."";
            $q2= $db->prepare($sql2);
            $q2->execute();
            $indexrows[$key]  = $q2->fetchALL(PDO::FETCH_ASSOC);// get fields with foreign keys
            $detstr = null;
            foreach ($indexrows[$key] as $key1 => $value1) {
                $arrind = array_keys($indexrows[$key][$key1]);
                $id = $indexrows[$key][$key1][$arrind[0]];
                $name = $indexrows[$key][$key1][$arrind[1]];
                
                $str = "{ id: ".$id.", text: '".$name."'},";
                $detstr = $detstr.$str;
                
                $detstr1[$id]=$name;
            }
            
            $indexstr[$value['target']] = $detstr; // JS parsing for dropndown list of indexed records for subsistute
            $indexstr1[$value['target']] = $detstr1;// JS parsing for indexed records for subsistute
             //logtofile($indexstr1);
        }
        $indexstrjson = json_encode($indexstr1);
    }

} catch(PDOException $e) { echo "Database error " . $e->getMessage(); $e -> resetvalue();}
    //{ field: 'list', text: 'combo', size: '150px', sortable: true, resizable: true, hidden1: true, editable: { type: 'list', items: people, filter: true }},
$stringfields = "[ ";

foreach ($fields as $key => $value) {

    if ($value['COLUMN_COMMENT'] !== ''){
        $comment = $value['COLUMN_COMMENT'];
    }else{
        $comment = $value['COLUMN_NAME'];
    }

    if ($datatype && isset($datatypejson)){
        $datatypeind = json_decode($datatypejson, true)[$value['COLUMN_NAME']];
    }else{
        $datatypeind = "text";//if data type not defined - text is default data format
    }
    
    $onefield = "{field: '".$value['COLUMN_NAME']."', "."text: '".$comment."', size: '".$columnwidth."', searchable: true, sortable: true, editable: { type: '".$datatypeind."' } },";
    
    if ($useindexkeys && isset($indexkeyjson) && !$useforeignkeys){

        $arrind = array_keys($indexstr);
        $indkey = array_search($value['COLUMN_NAME'], $arrind);

        if (!($indkey || (is_scalar($indkey) && strlen($indkey)))){//check, do we have indexed column?
        }else{
            $onefield = "{field: '".$value['COLUMN_NAME']."', "."text: '".$comment."', size: '".$columnwidth."', searchable: true, sortable: true, editable: { type: 'list', items: [".$indexstr[$value['COLUMN_NAME']]."], filter: true } },";
        }
    }
    
    if ($useforeignkeys && !$useidexkeys){
        $forkey = array_search($value['COLUMN_NAME'], array_column($fieldsfk, 'COLUMN_NAME'));
        if (!($forkey || (is_scalar($forkey) && strlen($forkey)))){
        }else{
            $onefield = "{field: '".$value['COLUMN_NAME']."', "."text: '".$comment."', size: '150px', searchable: true, sortable: true, editable: { type: 'list', items: people, filter: true } },"; 
        }
        unset($forkey);
    }

    $stringfields = $stringfields.$onefield;
    //$getindex[$indkey] = $indexstr;  
}

$stringfields = $stringfields." ]";

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