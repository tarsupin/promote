<?php

/****** Preparation ******/
define("CONF_PATH",		dirname(__FILE__));
define("SYS_PATH", 		dirname(CONF_PATH) . "/system");

// Load phpTesla
require(SYS_PATH . "/phpTesla.php");

// Initialize and Test Active User's Behavior
Me::initialize();

// Determine which page you should point to, then load it
require(SYS_PATH . "/routes.php");

/****** Dynamic URLs ******
// If a page hasn't loaded yet, check if there is a dynamic load
if($url[0] != '')
{
	$userData = Database::selectOne("SELECT id FROM users WHERE username=? LIMIT 1", array($url[0]));
	
	if(isset($userData['id']))
	{
		require(APP_PATH . '/profile.php'); exit;
	}
}
//*/

/****** 404 Page ******/
// If the routes.php file or dynamic URLs didn't load a page (and thus exit the scripts), run a 404 page.
require(SYS_PATH . "/controller/404.php");