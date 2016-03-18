<?php
include("functions.php");

$url = 'http://192.168.1.109/sofiev4/api/public/sendForageStatusSynchro';
sendRequest($url, $method = 'POST');
exit;

