<?php
/*
* Plugin Name: BackupToAzure
* Plugin URI: http://about.me/backuptoazure
* Description: You can take backup of your Wordpress website to Windows Azure
* Version: 1.0
* Author: Backup To Azure Team
* Author URI: http://about.me/backuptoazure
* License: GPL2
* Slug: backuptoazure
*/
			
/**  Copyright (C) 2013-2014  BackupToAzure Team  (email : backuptoazure@gmail.com)

*    This program is free software; you can redistribute it and/or modify
*    it under the terms of the GNU General Public License, version 2, as 
*    published by the Free Software Foundation.
*
*    This program is distributed in the hope that it will be useful,
*    but WITHOUT ANY WARRANTY; without even the implied warranty of
*    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*    GNU General Public License for more details.
*
*    You should have received a copy of the GNU General Public License
*    along with this program; if not, write to the Free Software
*    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
**/

if (!class_exists("BackupToAzure")) {

		// Don't activate on anything less than PHP 5.3.1 or WordPress 3.2
		if ( version_compare( PHP_VERSION, '5.3.1', '<' ) || version_compare( get_bloginfo( 'version' ), '3.2', '<' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
			deactivate_plugins( basename( __FILE__ ) );
			if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'activate' || $_GET['action'] == 'error_scrape' ) )
				die( __( 'BackupInAzure requires PHP version 5.3.1 or greater and WordPress 3.2 or greater.', 'backuptoazure' ) );
		}	

	class BackupToAzure{	
			public function __construct()  { 
			}
		}
}

if (class_exists("BackupToAzure")) {
    $backup_plugin = new BackupToAzure();
}	

class MenuAction {
	public function __construct() {
         	add_action( 'admin_menu', array( $this, 'register_my_admin_menu' ) );
    	}

	function register_my_admin_menu(){
    		add_menu_page( 'Backup To Azure', 'Backup To Azure', 'manage_options', 'backup_to_azure', 'my_admin_menu_page'); 
	}
}

class ActivateHook {
     static function install() {        
     }
}
	
if (isset($backup_plugin)) {

	define( 'MY_PLUGIN_PATH', dirname( __FILE__ ) );
	register_activation_hook( __FILE__, array('ActivateHook', 'install') );
	new MenuAction();
	
	function my_admin_menu_page()
	{	
		echo "</br>";
		if(!isset($_POST['submit']) && !isset($_POST['create']) && !isset($_POST['delete']) && !isset($_POST['deleteall']))
		{
?>
		<table><tr><td><img src='<?php echo plugins_url().DIRECTORY_SEPARATOR.'BackupToAzure'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'wplogo.png'?>' width="50px" height="50px"></td><td><h1>Backup To Windows Azure</h1></td></tr></table></br></br>
			<form name="Backup_In_Azure" id="Backup_In_Azure" method="post" action="">
				<span>Account Name :</span> <input type="text"  name=username id=username  \></br><br/>
				<span>Account Key :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><input type="text" name=key id=key \></br><br/>
				<?php $dbname=str_replace("_","",DB_NAME);?>
				<input type="submit" name=submit value="Submit" class="button-primary" />
			</form>
<?php

echo "<p style='color:red; position:absolute; bottom:-250px;'>Disclaimer : It is suggested to use https for better security of your credentials.</p>";

		}
		else if($_POST['username']=='' || $_POST['key']=='')
		{
?>
		<table><tr><td><img src='<?php echo plugins_url().DIRECTORY_SEPARATOR.'BackupToAzure'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'wplogo.png'?>' width="50px" height="50px"></td><td><h1>Backup To Windows Azure</h1></td></tr></table></br>
		
			<div id="message" class="updated"><p><?php _e('Please fill all credentials.') ?></p></div></br></br>
			
			<form name="Backup_In_Azure" id="Backup_In_Azure" method="post" action="">
				<span>Account Name :</span> <input type="text"  name=username id=username autocomplete=off \></br><br/>
				<span>Account Key :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><input type="text" name=key id=key autocomplete=off \></br><br/>
				<?php $dbname=strtolower(str_replace("_","",DB_NAME));?>
				<input type="submit" name=submit value="Backup" class="button-primary" />
			</form>
<?php
		}
		else 
		{
			$username=$_POST['username'];
			$key=$_POST['key'];
			require_once dirname( __FILE__ ) . DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'functions.php';
			$dbname=strtolower(str_replace("_","",DB_NAME));
			$connectionString="DefaultEndpointsProtocol=http;AccountName='".$_POST["username"]."';AccountKey='".$_POST["key"]."' ";
			$connection=get_Connection($connectionString);
			
			$characters = '0123456789abcdefghijklmnopqrstuvwxyz';
    		$string1='';
			for ($p = 0; $p < 30; $p++) {
        			$string1 .= $characters[mt_rand(0, strlen($characters)-1)];
   			}
			
			$test=create_table($connection,$string1.$dbname);
			if($test==303||$test==403)
			{
?>
			<table><tr><td><img src='<?php echo plugins_url().DIRECTORY_SEPARATOR.'BackupToAzure'.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'wplogo.png'?>' width="50px" height="50px"></td><td><h1>Backup To Windows Azure</h1></td></tr></table></br>
				
				<div id="message" class="updated"><p><?php _e('Invalid Credentials...Please Enter Correct Ones...') ?></p></div></br></br>
				
			<form name="Backup_In_Azure" id="Backup_In_Azure" method="post" action="">
				<span>Account Name :</span> <input type="text"  name=username id=username autocomplete=off \></br><br/>
				<span>Account Key :&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span><input type="text" name=key id=key autocomplete=off \></br><br/>
				<?php $dbname=strtolower(str_replace("_","",DB_NAME));?>
				<input type="submit" name=submit value="Backup" class="button-primary" />
			</form>	
<?php	
		}
		else
		{
			delete_table($connection,$string1.$dbname);		
			$dbname=strtolower(str_replace("_","",DB_NAME));
			create_table($connection,"backuplogwordpress".$dbname);
			include(MY_PLUGIN_PATH .DIRECTORY_SEPARATOR.'inc'.DIRECTORY_SEPARATOR.'logs.php');
		}
		
	}
}

//deactivation hook
register_deactivation_hook( __FILE__, 'Backup_Plugin_Deactivated');

	function Backup_Plugin_Deactivated()
	{
		delete_option('Backup in Azure');
	}
} 
?>