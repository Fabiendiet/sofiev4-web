<?php
/**** Generation de code d'initialisation pour les profil SVI ****/

ini_set('max_execution_time', 0);

try 
{
	$db = new PDO('mysql:host=192.168.1.112;dbname=db_sofie', 'sofie', 'poiuyt', array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
}
catch (Exception $e)
{
	die('Erreur : ' . $e->getMessage());
}
	$region = 1;
	$profile = 1;
	$status = 'N';
	
	for($i=0; $i < 1000; $i++)
	{
		$random = mt_rand(1000,9999);
		$code = $profile . $region . $random;
		$sql = "select code from t_code where code = " . $code;
		$lines = $db->query($sql);
		$allCode = $lines->fetchAll();
		
		if(count($allCode) == 0)
		{		
			$request = "INSERT INTO t_code(code, status, profile) VALUES(" .$code . ", '" . $status . "', " . $profile . ")";
			$db->exec($request);
		}		
	}
exit;
