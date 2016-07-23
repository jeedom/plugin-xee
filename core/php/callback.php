<?php

require_once dirname(__FILE__) . "/../../../../core/php/core.inc.php";
include_file('core', 'authentification', 'php');
if (!isConnect()) {
	echo 'Vous ne pouvez appeller cette page sans être connecté. Veuillez vous connecter <a href=' . network::getNetworkAccess() . '/index.php>ici</a> avant et refaire l\'opération de synchronisation';
	die();
}
log::add('xee','debug','Réponse reçue');
$token = init('code');
log::add('xee','debug',$token);
$clientid = config::byKey('clientid', 'xee', '0');
$clientsecret = config::byKey('clientsecret', 'xee', '0');
if (isset($token)) {
	$cmd =  "curl -v -X POST -u " . $clientid . ":" . $clientsecret ." -d 'grant_type=authorization_code&code=" . $token . "' https://cloud.xee.com/v3/auth/access_token";
	$return = shell_exec($cmd);
	log::add('xee','debug',$return);
	$returnencoded = json_decode($return,true);
	$access_token = $returnencoded['access_token'];
	$refresh_token = $returnencoded['refresh_token'];
	config::save('access_token', $access_token,'xee');
	config::save('refresh_token', $refresh_token,'xee');
	xee::createCars();
}
redirect(network::getNetworkAccess('external') . '/index.php?v=d&p=plugin&id=xee');
