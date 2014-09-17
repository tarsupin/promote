<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class GetCreditsAPI_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "api";
	public $pluginName = "GetCreditsAPI";
	public $title = "Transaction API";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "A transaction API to accept transactions from the UniJoule site.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if successfully installed, FALSE if not.
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		//$pass = DatabaseAdmin::columnsExist("network_info", array("site_handle", "auth_id", "uni_id", "category", "description"));
		
		return true;
	}
	
}