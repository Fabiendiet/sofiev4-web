<?php

function sendRequest($url, $method = 'POST')
{
	$curl = curl_init($url);
	
	if($method == 'POST')
	{
		curl_setopt($curl,CURLOPT_POST, 1);
		//curl_setopt($curl,CURLOPT_POSTFIELDS, $paramString);
	}
	
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		
	$curl_response = curl_exec($curl);
	$info = curl_getinfo($curl);
	
	curl_close($curl);
}