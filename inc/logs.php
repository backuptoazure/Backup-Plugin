<script type="text/javascript" src='<?php echo plugins_url().DIRECTORY_SEPARATOR.'BackupToAzure'.DIRECTORY_SEPARATOR.'js'.DIRECTORY_SEPARATOR.'form.js'?>'></script>
<?php

if(isset($_POST['create'])){	
	include(dirname( __FILE__ ) .DIRECTORY_SEPARATOR.'databaseupload.php');
	include(dirname( __FILE__ ) . DIRECTORY_SEPARATOR.'blobupload.php');
?>
</br>	
		<form method="post" action="">
			<input type="hidden"  name=username id=username value="<?php echo $_POST["username"];?>" \>
			<input type="hidden" name=key id=key value="<?php echo $_POST['key'];?>" \>
			<input type="submit" name=submit value="Back Home" class="button-primary" />
		</form>
		<?php
}
else
{
?>
		<table><tr><td>
			<form method="post" action="">
				<input type="hidden"  name=username id=username value="<?php echo $_POST["username"];?>" \>
				<input type="hidden" name=key id=key value="<?php echo $_POST['key'];?>" \>
				<input type="submit" name=create value="Start New Backup" class="button-primary" onclick="new_start()" />
			</form>
			</td><td></td><td></td><td></td><td></td><td>
			<form method="post" action="" onsubmit="return deleteallConfirm()">
					<input type="hidden"  name=username id=username value="<?php echo $_POST["username"];?>" \>
					<input type="hidden" name=key id=key value="<?php echo $_POST['key'];?>"\>
					<input type="submit" name=deleteall value="Delete All Backups" class="button-primary" />
			</form>
		</td></tr></table>
		
<?php

	require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR.'functions.php';
	$connectionString="DefaultEndpointsProtocol=http;AccountName=".$_POST["username"].";AccountKey=".$_POST["key"];
	$connection1=get_Connection($connectionString);
	$blobcon1=blob_get_Connection($connectionString);
	$dbname=strtolower(str_replace("_","",DB_NAME));
			
if(isset($_POST['delete']))
{ 	
	delete_entity($connection1,"backuplogwordpress".$dbname, "backuplog",(string)$_POST['delid']);
	delete_backup($connection1,$blobcon1,$dbname.$_POST['delid']); 
	delete_container($blobcon1,$dbname.$_POST['delid']."content");	
}

if(isset($_POST['deleteall']))
{
	
    $backup_log_filter1= "PartitionKey eq 'backuplog'";
	$log1=get_entity($connection1,"backuplogwordpress".$dbname,$backup_log_filter1);
	if($log1!="error")
	{
		$log_entries1= $log1->getEntities();
		foreach($log_entries1 as $entity1){
				 
					delete_entity($connection1,"backuplogwordpress".$dbname, "backuplog", (string)$entity1->getRowKey());
					delete_backup($connection1,$blobcon1,$dbname.(string)$entity1->getRowKey()); 
					delete_container($blobcon1,$dbname.(string)$entity1->getRowKey()."content");
				
		}	
	}  echo "</br></br>" ?>
	<table class='wp-list-table widefat fixed logs' cellspacing="0">
	<thead>	<tr>
		<th scope='col' id='log' class='manage-column column-log sortable desc'  style=""><a href="#"><span>Backup Date/Time</span><span class="sorting-indicator"></span></a></th><th scope='col'  class='manage-column column-status'  style="">Database Progress</th><th scope='col'  class='manage-column column-status'  style="">Content Progress</th><th scope='col'  class='manage-column column-runtime'  style=""></th>
	</tr> </thead>

	<tfoot> <tr>
		<th scope='col'  class='manage-column column-log sortable desc'  style=""><a href="#"><span>Backup Date/Time</span><span class="sorting-indicator"></span></a></th><th scope='col'  class='manage-column column-status'  style="">Database Progress</th>
		<th scope='col'  class='manage-column column-status'  style="">Content Progress</th><th scope='col'  class='manage-column column-runtime'  style=""></th>
	</tr> </tfoot>	
	
<tr><th scope='col'  class='manage-column column-status'  style=''>No Logs</th><th></th><th></th><th></th></tr></table>
<?php
}
else
{
?>
</br></br>
<table class="wp-list-table widefat fixed logs" cellspacing="0">
	<thead>	
	<tr>
		<th scope='col' id='log' class='manage-column column-log sortable desc'  style=""><a href="#"><span>Backup Date/Time</span><span class="sorting-indicator"></span></a></th><th scope='col'  class='manage-column column-status'  style="">Database Progress</th><th scope='col'  class='manage-column column-status'  style="">Content Progress</th><th scope='col'  class='manage-column column-runtime'  style=""></th>
	</tr>
	</thead>

	<tfoot>
	<tr>
		<th scope='col'  class='manage-column column-log sortable desc'  style=""><a href="#"><span>Backup Date/Time</span><span class="sorting-indicator"></span></a></th><th scope='col'  class='manage-column column-status'  style="">Database Progress</th>
		<th scope='col'  class='manage-column column-status'  style="">Content Progress</th><th scope='col'  class='manage-column column-runtime'  style=""></th>
	</tr>
	</tfoot>	
<?php
	
    $backup_log_filter1= "PartitionKey eq 'backuplog'";
	$log1=get_entity($connection1,"backuplogwordpress".$dbname,$backup_log_filter1); 
	if($log1!="error")
	{
		$log_entries1= $log1->getEntities(); 
		$count=0;
		foreach($log_entries1 as $entity1){  
		?>
<tr>
	<th scope='col'  class='manage-column column-log sortable desc'  style=""><a href="#"><span><?php echo $entity1->getProperty("time")->getValue(); ?></span><span class="sorting-indicator"></span></a></th><th scope='col'  class='manage-column column-status'  style=""><?php echo $entity1->getProperty("databasestatus")->getValue(); ?></th>
	<th scope='col'  class='manage-column column-status'  style=""><?php echo $entity1->getProperty("contentstatus")->getValue(); ?></th><th scope='col'  class='manage-column column-runtime'  style="">
	<form method=post action="" onsubmit="return deleteConfirm()">
		<input type="hidden"  name=delid id=delid value='<?php echo $entity1->getRowKey(); ?>' \>
		<input type="hidden"  name=username id=username value="<?php echo $_POST["username"];?>" \>
		<input type="hidden" name=key id=key value="<?php echo $_POST['key'];?>" \>
		<input type="submit" name="delete" value="Delete" class="button-primary" />	
	</form>
</th></tr>
<?php $count++;
 
	}//end foreach

	 if($count==0)
	{
?>
	<tr><th scope='col'  class='manage-column column-status'  style="">No Logs</th><th></th><th></th><th></th></tr>
<?php
	}
	echo '<input type="hidden"  name=nextid id=nextid value='.$count.' \>';
		
 } 
else
{
?>
<tr><th scope='col'  class='manage-column column-status'  style="">No Logs</th><th></th><th></th><th></th></tr>
<input type="hidden"  name=nextid id=nextid value=0 \>
<?php
}
}	
?>
</table>
<?php
}
echo "</br></br><p style='color:red;position:absolute;'>* On some versions of windows IIS server progress bar might not work. please follow this <a href='http://stackoverflow.com/questions/7178514/php-flush-stopped-flushing-in-iis7-5' target='_new'>Link</a> to configure your machine.</p>";
?>