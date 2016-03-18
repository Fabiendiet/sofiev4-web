<?php
require_once('Class/class_db.php');

/**
* Location of the database server
*/
$_ENV['databaseserver'] = "";
/**
* Port of the database server
*/
$_ENV['databaseport'] = 0;
/**
* Name of the database
*/
$_ENV['database'] = "";
/**
* Type of the database
*/
$_ENV['databasetype'] = "";
/**
* User name of the database
*/
$_ENV['databaseuser'] = "";
/**
* Password of the database
*/
$_ENV['databasepwd'] = "";
/**
* Workspace of the database, only for Oracle
*/
$_ENV['databaseworkspace'] = "";


$_ENV['log'] = "";
$_ENV['error_log'] = "";
$_ENV['error'] = 0;
$_ENV['index'] = 0;
$_ENV['flag']=false;
$_ENV['sd'] = "";

	$kannel_sendsms_uri = "";
	
	$script_directory = "";
	
	$gw_uri = "";
	
	$conf = "xml/config.xml";
	
	if(!file_exists($conf))
    {
         echo "The read of the configuration file has been stopped by sofie_alert_script (Xml Error).";
         exit;
    }
	
	// Loading of the xml config file.
	$_ENV['log'] .= "<".date("d-m-Y")." - ".date("H:i:s")."> Loading xml config file : ".$conf."\r\n";

	if($xmlConfig = simplexml_load_file($conf))
	{
		$CONFIG = $xmlConfig -> CONFIG;

		$_ENV['log'] .= "<".date("d-m-Y")." - ".date("H:i:s")."> Xml config file Loaded!\r\n";
	        
		$_ENV['databaseserver'] = $CONFIG->LOCATION;

	    	$_ENV['databaseport'] = $CONFIG->DATABASE_PORT;

	    	$_ENV['database'] = $CONFIG ->DATABASE;

	    	$_ENV['databasetype'] = $CONFIG->DATABASETYPE;

		$_ENV['databaseuser'] = $CONFIG->USER_NAME;

		$_ENV['databasepwd'] = $CONFIG->PASSWORD;
		
		$_ENV['sd'] = $CONFIG->SCRIPT_DIRECTORY;
		
		$_ENV['gw_uri_base'] = $CONFIG->GW_URI_BASE;
			
		$_ENV['gw_username'] = $CONFIG->GW_USERNAME;
			
		$_ENV['gw_password'] = $CONFIG->GW_PASSWORD;
			
		$_ENV['gw_smsc'] = $CONFIG->GW_SMSC;
			
		$_ENV['sms_from'] = $CONFIG->SMS_FROM;
			
		$_ENV['sms_to'] = $CONFIG->SMS_TO;
			
		$_ENV['sms_content'] = $CONFIG->SMS_CONTENT;
			
		$_ENV['active_log'] = $CONFIG->ACTIVE_LOG;

		$slh = (string)$CONFIG->UNIX_SYSTEM;
	
		$_ENV['active_log'] = strtoupper($_ENV['active_log']);		
	
		//var_dump($CONFIG);

	}
	else
	{
		$_ENV['log'] .= "<".date("d-m-Y")." - ".date("H:i:s")."> Error: Xml config file Loading Fail!!\r\n";
	}
	
	$script_directory = $_ENV['sd'];
	
	$kannel_sendsms_uri = $_ENV['gw_uri_base'];

	$_ENV['filename_loaded'] = array();
	
    	$_ENV['folder_loaded'] = array();
		
        if ($slh == '')
        {
                echo "You need to specify your Os system in the configuration file. sofie_alert_script has been locked!\r\n";
                CreateLockFile($_ENV['sid'], "1", $wbai);
        }

        if ($slh == "true")
        {
                $_ENV['slh'] = "/";
                $_ENV['osname'] = "UNIX";
        }
        else
        {
                $_ENV['slh'] = "\\";
                $_ENV['osname'] = "WINDOWS";
        }

        if(file_exists($script_directory."sofie_alert_script.lck"))
        {
                echo "An error has been found while the last batch. sofie_alert_script stopped!\r\n Please check you configuration file\r\n You have to delete the lock file in sofie_alert_script folder to restart this application\r\n";
                exit;
        }
		
    /*$lock_file = $_ENV['sd']."sofie_alert_script.lck";
		
    $lock_file_opened = fopen($lock_file, "a");
		
    fwrite($lock_file_opened, $errorInfo);
		
    fclose($lock_file_opened);*/


	$_ENV['log'] .= "<".date("d-m-Y")." - ".date("H:i:s")."> Target OS : ".$_ENV['osname']."\r\n";
	
	$log = $_ENV['log'];
	
    Function_log($log, $script_directory);

	if($_ENV['db'] = new dbquery())
	{
		$_ENV['log'] .="<".date("d-m-Y")." - ".date("H:i:s")."> Connecting to Mysql database...\r\n";
		$_ENV['db']->connect();
		$_ENV['log'] .="<".date("d-m-Y")." - ".date("H:i:s")."> Connection to Mysql database Performed!\r\n";
		$log=$_ENV['log'];
        	Function_log($log, $script_directory);
	}
	else
	{
		$_ENV['log'] .="<".date("d-m-Y")." - ".date("H:i:s")."> *****ERROR***** Unable to create dbquery() object! End of application.\r\n";
       		$log=$_ENV['log'];
		// $script_directory = $_ENV['sd'];
        	Function_log($log, $script_directory);
        	exit();

	}



function sofie_sms_processing($dbObject,$gw_uri,$etat)
{
	$gw_uri_base = $gw_uri;
	$stid = $_ENV['db']->query("SELECT * FROM ".$dbObject);
	
	// Prepare IN parameter
 	
	$send_state = "";

	if($stid->num_rows > 0) 
	{
	    $posts = array();
	
	    while($row=$_ENV['db']->fetch_array())
		{
			$posts[]=$row;
		}


	    foreach($posts as $row)
		{
			$row['Content'];
		}	
	    foreach($posts as $row)
		{	
			$gw_uri_params = "";

			$sofie_NUMERO=$row['RECEIVER'];//Numero de telephone
			$sofie_SMS_CONTENT=$row['CONTENT'];
			
			eval( "\$sms_content = \"$sofie_SMS_CONTENT\";" );
			
			echo $gw_uri_params .= "username=".$_ENV['gw_username']."&password=".$_ENV['gw_password']."&smsc=".$_ENV['gw_smsc']."&from=".$_ENV['sms_from']."&to=".$sofie_NUMERO."&text=".urlencode($sms_content);
			
			$local_gw_uri = $gw_uri_base.$gw_uri_params ;			

			$send_state = file_get_contents($local_gw_uri,false);			

			//$send_state = intval($send_state);

			echo "==(".$send_state.")==\r\n";		

			$_ENV['log'] .="<".date("d-m-Y")." - ".date("H:i:s")."> [SENDER:".$_ENV['sms_from']."] [RECEIVER:".$sofie_NUMERO."] [SMS STATUS : ".$send_state."] [CONTENU SMS:".urldecode($sms_content)."]\r\n";
			
			$script_directory = $_ENV['sd'];
			
			Function_log( $_ENV['log'], $script_directory);
			
			if($send_state == "0: Accepted for delivery"){	
			//if(!empty($send_state)){	
						
						echo $UpdateQuery = "UPDATE t_sendsms SET STATUS=".$etat." WHERE SMS_ID=".$row['SMS_ID'];
						$UpdateResult = $_ENV['db']->query($UpdateQuery);
						
						$_ENV['log'] .="<".date("d-m-Y")." - ".date("H:i:s")."> SQL :[$UpdateQuery].\r\n";
				        
						//Function_log($log, $script_directory);
						$sofie_SMS_CONTENT = "";
			}
			else {
	    		// No rows returned
		
				$stid = null;

				$send_state = "";
				$sofie_SMS_CONTENT = "";
				//exit;
				//continue;
			}

                                              	//$_ENV['log'] .="<".date("d-m-Y")." - ".date("H:i:s")."> Exception reçue : ',  $e->getMessage().\r\n";

                                                $log=$_ENV['log'];

                                                $script_directory = $_ENV['sd'];

                                                Function_log($log, $script_directory);	
	
	    }
	}
}

function CreateLockFile($directory, $errorInfo,$wbai)
{
//Create a log file in the application folder 
	$lock_file = $_ENV['sd']."sofie_alert_script.lck";
	if (file_exists($lock_file))
	{
		unlink ($lock_file);
	}
	
	$lock_file_opened = fopen($lock_file, "a");
	fwrite($lock_file_opened, $errorInfo); 
	fclose($lock_file_opened);
	
	if (!is_dir($directory.$_ENV['slh']."failed".$_ENV['slh']))
	{
		mkdir ($directory.$_ENV['slh']."failed".$_ENV['slh'], 0777);
	}
	if (!is_dir($directory.$_ENV['slh']."failed".$_ENV['slh'].$wbai.$_ENV['slh']))
	{
		mkdir ($directory.$_ENV['slh']."failed".$_ENV['slh'].$wbai.$_ENV['slh'], 0777);
	}
	for ($fo = 0; $fo <= count($_ENV['folder_loaded']); $fo++)
	{
		if (!is_dir($directory.$_ENV['slh']."failed".$_ENV['slh'].$wbai.$_ENV['slh'].$_ENV['folder_loaded'][$fo].$_ENV['slh']))
		{
			mkdir ($directory.$_ENV['slh']."failed".$_ENV['slh'].$wbai.$_ENV['slh'].$_ENV['folder_loaded'][$fo].$_ENV['slh'], 0777);
		}
	}

}

function Function_tar_log($file_name)
{
	if(filesize($file_name) > 1048576)
	{
		//$_ENV['index']+=1;
		return 1;
	}
	return 0;
}

function  Function_log($eventInfo, $script_directory)
{
	if($_ENV['active_log']=="TRUE")
	{
		//Create a log file in the application folder 
			$log_file = $script_directory."log.txt";
		if(file_exists($log_file))
	    {	
			if(filesize($log_file) > 52428800)
		    {
				$out_put=shell_exec('rm '.$log_file);
		    }
		}
		
		$log_file_opened = fopen($log_file, "a+");
		fwrite($log_file_opened, $eventInfo); 
		fclose($log_file_opened);
		
	}

}

function  Function_error_log($eventInfo, $script_directory)
{
//Create a log file in the application folder
        $log_file = $script_directory."error_log.txt";
        $log_file_opened = fopen($log_file, "a+");
        fwrite($log_file_opened, $eventInfo);
        fclose($log_file_opened);
}


	$_ENV['log'] = ""; 
	
	$log = $_ENV['log'];

    	Function_log($log, $script_directory);

	$dbObject='v_sms_to_send';
	
	sofie_sms_processing($dbObject,$kannel_sendsms_uri,0);
	
	$_ENV['log'] .= "<".date("d-m-Y")." - ".date("H:i:s")."> End of application\r\n";	

	if (file_exists($lock_file))
	{
		unlink ($lock_file);
	}	
?>
