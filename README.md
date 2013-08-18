Backup-Plugin
=============

You can take Backup of Your Wordpress Website To Windows Azure Storage. In which all your files will be stored in Blob Storage and Database to Table Storage.

How to use: 
Step 1: install this plugin like any other plugin of wordpress or u can use FTP to upload it directly to plugins folder of your web directory.  
Step 2:After Activating the plugin you will find a “Backup To Azure” tab on the left side menu on the dashboard.  
Step 3:On that tab you need to enter your wordpress credentials (we are not storing your credentials , they are just transferred from one page to another by HTML POST method ). 
Step 4: Once your credential are verified you can see the logs of your previous backup. You may choose to delete any or all of them.   
Step 5:On clicking “Start New Backup” the backup schedule will start running it will first take database backup and then content backup.  

The backend
Database backup
We have executed only 3 type of queries :Show table , Describe table , and select * from Table To fetch the database and upload it to azure table storage;

Content Backup
To create a content backup every file in of the wp-content  folder of your web directory will be opened only in read mode ,so feel safe about your data :);

Progress Bar 
To create a progress bar we flushed a JavaScript code to the browser .Some IIS server do not support the flushing , they need to be configured and the link is provided in the plugin itself;


It was our first Wordpress – Azure Project , any  complaint, suggestion or complement are hearty appreciated .Please contact us at backuptoazure@gmail.com :)

We are working on its extentions...

Thank you.
Backup to Azure Team 
about.me/backuptoazure
