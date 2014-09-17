<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class AdCampaign_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "official";
	public $pluginName = "AdCampaign";
	public $title = "Advertising Campaign";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides tools to handle advertising campaigns.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		Database::exec("
		CREATE TABLE IF NOT EXISTS `ad_campaigns`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`title`					varchar(42)					NOT NULL	DEFAULT '',
			
			`credits_available`		float(10,2)		unsigned	NOT NULL	DEFAULT '0.00',
			`max_per_day`			float(10,2)		unsigned	NOT NULL	DEFAULT '0.00',
			
			`date_start`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`date_end`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (id) PARTITIONS 3;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `ad_campaigns_by_user`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`campaign_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`, `campaign_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (uni_id) PARTITIONS 5;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if installed, FALSE if not
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("ad_campaigns", array("uni_id", "title"));
		$pass2 = DatabaseAdmin::columnsExist("ad_campaigns_by_user", array("uni_id", "campaign_id"));
		
		return ($pass1 and $pass2);
	}
	
}