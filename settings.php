<?php

$tablename = 'employee'; // name of the main database table
$tabletitle = "'Employees list'"; // title of the main database table (must be a '' string)
$limit = 1000; //limit of records amount in table
$dateformat = "'yyyy-mm-dd'";
$columnwidth = "150px"; // Default columns width in pixels

// Names of connected database tables if it use foreingkeys. You can't use in togeter with '$useidexkeys'
$useforeignkeys = false;

$useindexkeys = true;// use indexed tables?
// Names of connected database tables if it use simple index. You can't use it togeter with '$useforeignkeys'
//index structure, be careful with JSON format! See example:
/*$indexkeyjson = '{"tablename1": {"target": "name_index_field1", "id": "id_field1", "name": "name_field1"}, 
				  "tablename2": {"target": "name_index_field2", "id": "id_field_2", "name": "name_field_2"}}';*/
$indexkeyjson = '{"companies": 
					{ "target": "resp_company", 
					  "id": "comp_id",
					  "name": "comp_name" }, 
				  "departments": 
				  	{ "target": "resp_department",
				  	  "id": "dep_id",
				  	  "name": "dep_name" }
				}';

$datatype = true;

// datatype format: {"field name (in database)": "data type"};
$datatypejson ='{"id":				"int",
				"resp_first_name":	"text",
				"resp_last_name":	"text",
				"resp_mobile":		"text",
				"resp_phone":		"text",
				"resp_email":		"email",
				"birthday":			"date",
				"resp_company":		"int",
				"resp_department":	"int",
				"resp_indextoken":	"int"}';

?>