<?php
require_once( str_replace("inc","",dirname( __FILE__ )). 'WindowAzure'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.
'WindowsAzure'.DIRECTORY_SEPARATOR.'WindowsAzure.php');
	use WindowsAzure\Common\ServicesBuilder;
	use WindowsAzure\Common\ServiceException;
	use WindowsAzure\Table\Models\Entity;
	use WindowsAzure\Table\Models\EdmType;

function get_Connection($connectionString) {
	return ServicesBuilder::getInstance()->createTableService($connectionString);
}

function blob_get_Connection($connectionString) {
	return ServicesBuilder::getInstance()->createBlobService($connectionString);
}

function blog_create_container($con,$containername) {
  	try {
	     $con->createContainer((string)$containername);
	}
	catch(ServiceException $e){
		$code = $e->getCode();
  		  $error_message = $e->getMessage();	 
		return $code;
	}
}

function blob_create($con,$containername,$blob_name,$content) {
	try {
    		$con->createBlockBlob($containername, $blob_name, $content);
	}
	catch(ServiceException $e){
  		  $code = $e->getCode();
  		  $error_message = $e->getMessage();
			echo $code.$error_message."</br>";
  		  }
}

function create_Table($tableRestProxy,$tablename) {
		try {
		    $tableRestProxy->createTable($tablename);
		}
		catch(ServiceException $e){
		    $code = $e->getCode();
		    $error_message = $e->getMessage();	
		    return $code;
		}
}

function delete_Table($tableRestProxy,$tablename) {
	try {
		$tableRestProxy->deleteTable("$tablename");
	}
	catch(ServiceException $e){
		$code = $e->getCode();
		$error_message = $e->getMessage();
		
	}
}

function new_Entity() {
	return new Entity();
}

function add_RowKey($entity,$rowkey) {
	$entity->setRowKey($rowkey);
}

function add_PartitionKey($entity,$partitionkey) {
	$entity->setPartitionKey($partitionkey);
}

function add_Attribute($entity,$attrubutename,$attributevalue) {
	$entity->addProperty($attrubutename,  EdmType::STRING , $attributevalue);
}

function get_entity($tableRestProxy,$tablename,$filter) {
	try{
		$res=$tableRestProxy->queryEntities($tablename, $filter);
		return $res;
	}
	catch(ServiceException $e){
  		  $code = $e->getCode();
  		  $error_message = $e->getMessage();
		return "error";
  	}
}

function delete_backup($tableRestProxy,$blobRestProxy,$tablename) {
	try {
 	     $tableRestProxy->deleteTable($tablename);
	}
	catch(ServiceException $e){
		$code = $e->getCode();
		    $error_message = $e->getMessage();
		  
	}

	try {
 		    $blobRestProxy->deleteContainer($tablename);
	}
	catch(ServiceException $e){
		    $code = $e->getCode();
		    $error_message = $e->getMessage();
		   
	}
}

function update_Entity($tableRestProxy,$tablename,$partitionKey,$rowKey,$attrubuteName,$attributeValue) {

	$filter = "PartitionKey eq '".$partitionKey."' and RowKey eq '".$rowKey."'";
	$result=get_entity($tableRestProxy,$tablename,$filter);
	$entities = $result->getEntities();

	foreach($entities as $entity)
	{	
		$entity->setPropertyValue($attrubuteName,$attributeValue);
		try {
  		  $tableRestProxy->updateEntity($tablename, $entity);
		}
		catch(ServiceException $e){
 		   $code = $e->getCode();
  	   	   $error_message = $e->getMessage();
 		   
		}
	}
}

function upload_Entity($tableRestProxy,$tablename,$entity,$connectionString) {
	try{
		$tableRestProxy->insertEntity($tablename, $entity);
	}
	catch(ServiceException $e){
		$code = $e->getCode();
		$error_message = $e->getMessage();
		return $code;
	}
}

function delete_entity($tableRestProxy,$tablename, $primarykey, $rowkey){
	try {
    		$tableRestProxy->deleteEntity($tablename, $primarykey, $rowkey);
	}
	catch(ServiceException $e){
    		$code = $e->getCode();
    		$error_message = $e->getMessage();
    		
	}
}

function delete_container($blobRestProxy,$containername){
	try {
    		$blobRestProxy->deleteContainer($containername);
	}
	catch(ServiceException $e){
   		 $code = $e->getCode();
    		$error_message = $e->getMessage();
    		
	}
}
function flush_buffers(){
    ob_end_flush();
    ob_flush();
    flush();
    ob_start();
}
?>