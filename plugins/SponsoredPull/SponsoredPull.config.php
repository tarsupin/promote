<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class SponsoredPull_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "api";
	public $pluginName = "SponsoredPull";
	public $title = "Sponsored Ad API";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Returns available sponsored as for the site requesting them.";
	
	public $data = array();
	
}