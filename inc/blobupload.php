<link rel='stylesheet' id='custom-style-css'  href='<?php echo plugins_url().DIRECTORY_SEPARATOR.'BackupToAzure'.DIRECTORY_SEPARATOR.'css'.DIRECTORY_SEPARATOR.'style.css'?>' type='text/css' media='all' />  

<h4>Content Backup...</h4>
<div id="contentbackup" class="updated">
<script>

setInterval(function(){
$("#effect").animate({marginLeft:'100%'},($('#contentprogress').width())*1.5);
$("#effect").animate({marginLeft:'-15px'},($('#contentprogress').width())*1.9);

},3500);
</script>
<p><?php _e("<table width=100% ><tr ><td width=90% ><div id='contentprogress' class='meter'><div id=effect >&nbsp;</div></div></td> <td><div id='contentprogresspercent' > 0%</div></td></table>") ?></p></div>

<div id="contentbackupdetails" class="updated"><p><?php _e("<div id='contentprogressdata' style='color:green;'>&nbsp;</div>") ?></p></div>

<?php
update_Entity($connection,"backuplogwordpress".$dbname,"backuplog",(string)date_format($date, 'dmYHis'),"contentstatus","pending");

	ob_end_flush();
	ob_start();
	$blobcon1=blob_get_Connection($connectionString);
	
	$containername=$dbname.$date->format('dmYHis')."content";
	
	blog_create_container($blobcon1,$containername);
	update_Entity($connection,"backuplogwordpress".$dbname,"backuplog",(string)date_format($date, 'dmYHis'),"container","true");

	$path=ABSPATH.'wp-content';
	static $i=1;
	function upload_dir_contents_count($path)
	{
		$count=0;
		if ($handle = opendir("$path")) { 
	   	 while (false !== ($entry = readdir($handle))) {
	        
			if($entry!="." && $entry!="..")
			{
				if(is_dir($path.DIRECTORY_SEPARATOR.$entry))
				{
					$count+=upload_dir_contents_count($path.DIRECTORY_SEPARATOR.$entry);
				}
				else
				{
					$count++;
				}
			}
			
		}
	    closedir($handle);
	}
	return $count;
	}
	function upload_dir_contents($path,$blobcon1,$connection,$containername,$count,$current,$lastval,$date,$dbname)
	{
		
		if ($handle = opendir("$path")) { 
	   	 while (false !== ($entry = readdir($handle))) {
	        
			if($entry!="." && $entry!="..")
			{
				if(is_dir($path.DIRECTORY_SEPARATOR.$entry))
				{
						$current=upload_dir_contents($path.DIRECTORY_SEPARATOR.$entry,$blobcon1,$connection,$containername,$count,$current,$lastval,$date,$dbname);
				}
				else
				{
					
				$lastval = uploadfile($path.DIRECTORY_SEPARATOR.$entry,$blobcon1,$connection,$containername,$count,$current,$lastval,$date,$dbname);
				$current++;
				}
			}
			
		}
	    closedir($handle);
	}
	return $current; 
	}	
	function uploadfile($entry,$blobcon1,$connection,$containername,$count,$current,$lastval,$date,$dbname)
	{
		$entry1=str_replace(ABSPATH,"",$entry);
		?>
		<script type="text/javascript">

		document.getElementById("contentprogress").style.width='<?php echo intval($current*100/$count); ?>%';
		document.getElementById("contentprogressdata").innerHTML='<?php echo "uploading..".$current." of ".$count." size=".(filesize($entry)/1000)."KB </br> File:".$entry1; ?>';
		document.getElementById("contentprogresspercent").innerHTML='<?php echo sprintf("%.2f",($current*100/$count)); ?>%';
		
		</script>
		<?php
		ob_end_flush();
		ob_start();
		if(filesize($entry)>100000000)
		{
		echo ("<div class=updated ><p>sorry file ".$entry1." is too large for us to handle :( We recommend a manual Backup </p></div>");
		return $lastval;
		}
		$content1 = fopen("$entry", "r");
		
				
		blob_create($blobcon1,$containername,str_replace("\\","/",$entry1),$content1);

		if(($current*100/$count)-$lastval>=0.5 || $current*100/$count==100)
		{
update_Entity($connection,"backuplogwordpress".$dbname,"backuplog",(string)date_format($date, 'dmYHis'),"contentprogress",(string)sprintf("%.2f",($current*100/$count)));
		$lastval=($current*100/$count);
		} 
	
		
		return $lastval;
	}
	$count=upload_dir_contents_count($path);
	$current=1;
	$lastval=1;
	$current=upload_dir_contents($path,$blobcon1,$connection,$containername,$count,$current,$lastval,$date,$dbname);

if($current==$count+1)
	{	update_Entity($connection,"backuplogwordpress".$dbname,"backuplog",(string)date_format($date, 'dmYHis'),"contentstatus","successful");
?>
<script type="text/javascript">
		document.getElementById("contentbackup").innerHTML='<p><?php _e("Content Backup #".$nextid." Completed 100% at TIME :".(string)date_format(new DateTime(), 'd/m/Y H:i:s')."(Server Time)") ?></p>';
</script>

<?php	
}
	else
	{
update_Entity($connection,"backuplogwordpress".$dbname,"backuplog",(string)date_format($date, 'dmYHis'),"contentstatus","failed");
?>
<script type="text/javascript">
		document.getElementById("contentbackup").innerHTML='<p><?php echo("Content Backup #".$nextid." FAILED @".sprintf("%.2f",$lastval)."% sorry"); ?></p>';
</script>
<?php	
}
		ob_end_flush();
		ob_start();
		
 ?>
<script type="text/javascript">
		document.getElementById("contentbackupdetails").innerHTML="<p><?php _e('Backup #'.$nextid.' Complete.') ?></p>";
</script>