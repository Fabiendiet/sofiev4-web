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



function sofie_alert_processing($typeAlert,$msg_ref,$gw_uri,$etat=0,$part_of_content=0)
{
	$gw_uri_base = $gw_uri;

	$dbObject='';
	$sql0="SELECT f_getUniteDelais() AS UNITE_DELAI";
	$result0=$_ENV['db']->query($sql0);	
	$unitedelai=$result0->fetch_object();

	if ($unitedelai->UNITE_DELAI=='jour')

		$dbObject = 'v_forage_'.$typeAlert.'_jour';
	
	else if ($unitedelai->UNITE_DELAI=='heure')

		$dbObject = 'v_forage_'.$typeAlert.'_heure';

	else if ($unitedelai->UNITE_DELAI=='minute')

		$dbObject = 'v_forage_'.$typeAlert.'_minute';

	else if ($unitedelai->UNITE_DELAI=='second')
		
		$dbObject = 'v_forage_'.$typeAlert.'_second';
	
	$stid = $_ENV['db']->query("SELECT * FROM ".$dbObject);
	// Prepare IN parameter
	//echo "SELECT * FROM ".$dbObject."\n";
	$stid->num_rows."\n"; 
	if($stid->num_rows > 0) 
	{
	    $posts = array();
		
	    while($row=$_ENV['db']->fetch_array())
            {
		$posts[]=$row;
	    }
	    //while($row = $_ENV['db']->fetch_array())
	    foreach($posts as $row)
	    {		
			$gw_uri_params = "";
	
			$sql1="SELECT f_getInfosOuvrageByCodeOuvrage(".$row['CODE_OUVRAGE'].") AS INFOS_OUVRAGE";
			$sql2="SELECT f_getAlertMessages('".$msg_ref."') AS ALERT_MESSAGE";			

			//$_ENV['log'] .="<".date("d-m-Y")." - ".date("H:i:s")."> SQL1 : ',  $sql1.\r\n";
			$result1=$_ENV['db']->query($sql1);
			$result2=$_ENV['db']->query($sql2);
			$label1=$result1->fetch_object();
			$label2=$result2->fetch_object();
			$sofie_NUMERO=$row["NumAppel"];//Numero de telephone
			$sofie_INFOS_OUVRAGE=$label1->INFOS_OUVRAGE;
			$sofie_ALERT_MESSAGE=$label2->ALERT_MESSAGE;

			if(empty($sofie_NUMERO)) continue;

			eval( "\$sms_content = \"$sofie_ALERT_MESSAGE\";" );
			
			$_ENV['log'] .="<".date("d-m-Y")." - ".date("H:i:s")."> SMS STATUS : [".$send_state." CONTENU SMS: ".urldecode($sms_content)."]\r\n";			
			$script_directory = $_ENV['sd'];
			
			Function_log( $_ENV['log'], $script_directory);
			
			$UpdateQuery = "UPDATE t_panne SET ALERT=".$etat." WHERE IDOuvrage=f_getIDOuvrageByCodeOuvrage(".$row['CODE_OUVRAGE'].") AND ((ALERT=1) OR (ALERT=0))";
						
			$UpdateResult = $_ENV['db']->query($UpdateQuery);
						
			$_ENV['log'] .="<".date("d-m-Y")." - ".date("H:i:s")."> SQL : [$UpdateQuery]\r\n";
						
			echo $UpdateQuery2 = "INSERT INTO `t_notification` (DateHeureNotif,MotifNotif,IDPanne,IDNumAppel)VALUES(NOW(),6,f_getPanneIdByPanneTicket(".$row['NumPanne']."),f_getIDNumAppelAgentByIDAgent(".$row['IDAgent']."))";

                        $UpdateResult2 = $_ENV['db']->query($UpdateQuery2);

			echo $UpdateQuery3 = "INSERT INTO `t_sendsms` (SMS_DATE,SENDER,RECEIVER,CONTENT,ORIGIN,STATUS)VALUES(NOW(),'".$_ENV['sms_from']."','".$sofie_NUMERO."','".urldecode($sms_content)."',1,1)";

			$UpdateResult3 = $_ENV['db']->query($UpdateQuery3);

$_ENV['log'] .="<".date("d-m-Y")." - ".date("H:i:s")."> SQL : [$UpdateQuery3]\r\n";						
	
			$_ENV['log'] .="<".date("d-m-Y")." - ".date("H:i:s")."> SQL : [$UpdateQuery2]\r\n";
				       	
			$log=$_ENV['log'];
						
			$script_directory = $_ENV['sd'];
				        
			Function_log($log, $script_directory);
						
			$sofie_ALERT_MESSAGE= "";		

	
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
			if(filesize($log_file) > 5242880)
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
	
	$typeAlert1='hors_delai_prise_charge';
	
	$typeAlert2='hors_delai_reparation';

	sofie_alert_processing($typeAlert1,"ALERT_HD_PRISE_CDE",$kannel_sendsms_uri,1);
	
	sofie_alert_processing($typeAlert2,"ALERT_HD_REPARATION",$kannel_sendsms_uri,2);

	$_ENV['log'] .= "<".date("d-m-Y")." - ".date("H:i:s")."> End of application\r\n";	

	if (file_exists($lock_file))
	{
		unlink ($lock_file);
	}	
?>
