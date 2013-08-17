<link rel='stylesheet' id='custom-style-css'  href='<?php echo plugins_url().DIRECTORY_SEPARATOR.'BackupToAzure'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'style.css'?>' type='text/css' media='all' />  

<?php
include_once(str_replace(DIRECTORY_SEPARATOR."wp-content".DIRECTORY_SEPARATOR."plugins".DIRECTORY_SEPARATOR."BackupToAzure".DIRECTORY_SEPARATOR."inc","",dirname( __FILE__ )).DIRECTORY_SEPARATOR.'wp-load.php' );

require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR.'functions.php';

$hash="===";
$username=$_POST['username'];
$key=$_POST['key'];

global $mydb;
$mydb = new wpdb(DB_USER,DB_PASSWORD,DB_NAME,DB_HOST);
$connectionString="DefaultEndpointsProtocol=http;AccountName='".$_POST["username"]."';AccountKey='".$_POST["key"]."' ";
$connection=get_Connection($connectionString);

ob_start();
flush_buffers();
	
$dbname=strtolower(str_replace("_","",DB_NAME));


$nextid=0;
$backup_log_filter= "PartitionKey eq 'backuplog'";
$log=get_entity($connection,"backuplogwordpress".$dbname,$backup_log_filter);
if($log!=="error")
{
$log_entries= $log->getEntities();



foreach($log_entries as $entity){
	$nextid=intval($entity->getProperty("srno")->getValue());
}
}
$nextid++;

$date = new DateTime();


$entry=new_entity();
add_PartitionKey($entry,"backuplog");
add_RowKey($entry,(string)date_format($date, 'dmYHis'));
add_Attribute($entry,"srno",sprintf("%05d",$nextid));
add_Attribute($entry,"time",(string)date_format($date, 'd/m/Y H:i:s'));
add_Attribute($entry,"databasestatus","pending");
add_Attribute($entry,"contentstatus","waiting");
add_Attribute($entry,"contentprogress","0");
add_Attribute($entry,"container","false");
add_Attribute($entry,"table","false");
add_Attribute($entry,"databaseprogress","0");
add_Attribute($entry,"lastentry",$date->format('dmYHis'));

upload_Entity($connection,"backuplogwordpress".$dbname,$entry,$connectionString);
$tablename=$dbname.$date->format('dmYHis');

create_table($connection,"$tablename");
$blobcon=blob_get_Connection($connectionString);
blog_create_container($blobcon,$tablename);
			
update_Entity($connection,"backuplogwordpress".$dbname,"backuplog",(string)date_format($date, 'dmYHis'),"table","true");
?>
<h4>Database Backup...</h4>
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
<div id="message<?php echo $nextid;?>" class="updated">
<script>

setInterval(function(){
$("#effect").animate({marginLeft:'100%'},($('#qwerty123').width())*1.5);
$("#effect").animate({marginLeft:'-15px'},($('#qwerty123').width())*1.9);

},3500);
</script>

<p><?php _e("<table width=100% ><tr ><td width=90%><div id='qwerty123' class='meter'><div id=effect >&nbsp;</div></div></td> <td><div id='percent123'> 0%</div></td></table>") ?></p></div>
<?php
$posts =$mydb->get_results($mydb->prepare("SHOW TABLES"));
if(!$posts)
{
?>

<script type="text/javascript">
		document.getElementById("message<?php echo $nextid; ?>").innerHTML='<p><?php _e("sorry connection error :(") ?></p>';
</script>

<?php
	delete_entity($connection,"backuplogwordpress".$dbname, "backuplog",(string)date_format($date, 'dmYHis'));
	delete_backup($connection,$blobcon,$dbname.(string)date_format($date, 'dmYHis'));

?>
		<form method="post" action="">
			<input type="hidden"  name=username id=username value="<?php echo $_POST["username"];?>" \>
			<input type="hidden" name=key id=key value="<?php echo $_POST['key'];?>" \>
			<input type="submit" name=submit value="Back Home" class="button-primary" />
		</form>
<?php 
exit();
die();
}
$no_of_tables=0;
$val=0;	
$lastval=0;
$entry_update_val=0;
foreach($posts as $row)
{
$no_of_tables++;
}	

foreach($posts as $row)
{
	$name='Tables_in_'.strtolower(DB_NAME);
	$table_content = $mydb->get_results($mydb->prepare("SELECT * FROM ".$row->$name." "));
	$table_count = $mydb->get_results($mydb->prepare("SELECT count(*) as count FROM ".$row->$name." "));
	
	$i=0;
	$table_data = $mydb->get_results($mydb->prepare("describe ".$row->$name." "));
	$no_of_fields=0;
	$fields;
	$field_type;
	$primary_key=NULL;
	$primary_key2=NULL;
	$no_of_rows=0;
	foreach($table_count as $crow)
	{
	$no_of_rows=$crow->count;
	}
	if($no_of_rows==0)
	{
		$val+=100/($no_of_tables);
		?>
		<script type="text/javascript">
		document.getElementById("qwerty123").style.width='<?php echo $val; ?>%';
		document.getElementById("percent123").innerHTML='<?php echo sprintf("%.2f",$val); ?>%';
		
		</script>
		<?php
		flush_buffers();
	}
	foreach($table_data as $table_row)
	{
		$fields[$no_of_fields]=$table_row->Field;
		$field_type[$no_of_fields++]=$table_row->Type;
		if($table_row->Key=="PRI")
		{
		if($primary_key=="")
		$primary_key=$table_row->Field;
		else
		$primary_key2=$table_row->Field;
		
		}
	}
	$co=0;
	foreach($table_content as $table_row)
	{
		$entry=new_entity();
		add_PartitionKey($entry,(string)$row->$name);
		if($primary_key2=="")
		add_RowKey($entry,sprintf("%05s",(string)$table_row->$primary_key));
		else
		add_RowKey($entry,sprintf("%05s",(((string)$table_row->$primary_key).":".((string)$table_row->$primary_key2))));
		
		for($j=0;$j<$no_of_fields;$j++)
		{	 
			if($field_type[$j]=="longtext")
			{
				blob_create($blobcon,$tablename,$entry->getPartitionKey().$fields[$j].$entry->getRowKey(),$table_row->$fields[$j]);
				add_attribute($entry,$fields[$j],"In Blob->".$entry->getPartitionKey().$fields[$j].$entry->getRowKey());
			}
			else
			{
				add_attribute($entry,$fields[$j],$table_row->$fields[$j]);
			}
		
		}
		$code=upload_entity($connection,$tablename,$entry,$connectionString);
		$co++;
		
		$val+=100/($no_of_rows*$no_of_tables);
		if($val-$lastval>=0.2 || $val==100)
		{
		?>
		<script type="text/javascript">
		document.getElementById("qwerty123").style.width="<?php echo $val; ?>%";
		document.getElementById("percent123").innerHTML='<?php echo sprintf("%.2f",$val); ?>%';
		
		</script>
		
		<?php
		$lastval=$val;
		}
		if($val-$entry_update_val>=1 || $val==100)
		{
update_Entity($connection,"backuplogwordpress".$dbname,"backuplog",(string)date_format($date, 'dmYHis'),"databaseprogress",(string)intval($val));
		$entry_update_val=$val;
		}
		flush_buffers();
	}
	$i++;
} 
if(sprintf("%f",$val)==sprintf("%f",100))
{
update_Entity($connection,"backuplogwordpress".$dbname,"backuplog",(string)date_format($date, 'dmYHis'),"databasestatus","successful");
update_Entity($connection,"backuplogwordpress".$dbname,"backuplog",(string)date_format($date, 'dmYHis'),"databaseprogress",(string)sprintf("%f",$val));
?>

<script type="text/javascript">
		document.getElementById("message<?php echo $nextid; ?>").innerHTML='<p><?php _e("Database Backup #".$nextid." Completed 100% at TIME :".(string)date_format(new DateTime(), 'd/m/Y H:i:s')."(Server Time)") ?></p>';
</script>
<?php
}
else
{
update_Entity($connection,"backuplogwordpress".$dbname,"backuplog",(string)date_format($date, 'dmYHis'),"databasestatus","failed");
?>
<script type="text/javascript">
		document.getElementById("message<?php echo $nextid; ?>").innerHTML='<p><?php _e("Database Backup #".$nextid." FAILED @".sprintf("%.2f",$val)."% sorry") ?></p>';
</script>
<?php
}

?>
<?php 
?>
		