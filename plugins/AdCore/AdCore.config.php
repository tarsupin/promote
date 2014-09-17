<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class AdCore_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "official";
	public $pluginName = "AdCore";
	public $title = "Advertising System";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides tools and mechanisms for working with advertisements.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		DatabaseAdmin::addColumn("users", "credits", "float(10,2) unsigned not null", 0.00);
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if installed, FALSE if not
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		return DatabaseAdmin::columnsExist("users", array("credits"));
	}
	
}
