<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

class SponsoredAds_config {
	
	
/****** Plugin Variables ******/
	public $pluginType = "official";
	public $pluginName = "SponsoredAds";
	public $title = "Sponsored Ads Handler";
	public $version = 1.0;
	public $author = "Brint Paris";
	public $license = "UniFaction License";
	public $website = "http://unifaction.com";
	public $description = "Provides tools for working with Sponsored Ads.";
	
	public $data = array();
	
	
/****** Install this plugin ******/
	public function install (
	)			// <bool> RETURNS TRUE on success, FALSE on failure.
	
	// $plugin->install();
	{
		/*
			`is_live` is whether or not the ad is inactive or live.
			
			`name` is the name you've assigned to the advertisement
			`reference_url` is the url that you used to originally build the advertisement.
			
			`structure` indicates what the ad style is (such as an image of 300x300)
			`site_handle` is the site handle where the ad resides.
			`zone` is the name of the ad that you're placing this ad into
			`keyword` is the sponsored keyword for this ad, which helps group it for target audiences
			
			`bid_cpm_all` is the maximum amount you're willing to spend per 1000 views on non-targeted traffic.
			`bid_cpm_user` is the maximum amount you're willing to spend per 1000 views on user traffic.
			`bid_cpm_premium` is the maximum amount you're willing to spend per 1000 views of premium traffic.
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `sponsored_ads`
		(
			`id`					int(10)			unsigned	NOT NULL	AUTO_INCREMENT,
			
			`campaign_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			`is_live`				tinyint(1)		unsigned	NOT NULL	DEFAULT '0',
			
			`name`					varchar(32)					NOT NULL	DEFAULT '',
			`reference_url`			varchar(100)				NOT NULL	DEFAULT '',
			
			`structure`				varchar(22)					NOT NULL	DEFAULT '',
			`site_handle`			varchar(22)					NOT NULL	DEFAULT '',
			`zone`					varchar(22)					NOT NULL	DEFAULT '',
			`keyword`				varchar(22)					NOT NULL	DEFAULT '',
			
			`ad_url`				varchar(100)				NOT NULL	DEFAULT '',
			`ad_image_url`			varchar(100)				NOT NULL	DEFAULT '',
			`ad_title`				varchar(42)					NOT NULL	DEFAULT '',
			`ad_body`				varchar(200)				NOT NULL	DEFAULT '',
			
			`bid_cpm_all`			float(4,2)		unsigned	NOT NULL	DEFAULT '0.00',
			`bid_cpm_user`			float(4,2)		unsigned	NOT NULL	DEFAULT '0.00',
			`bid_cpm_premium`		float(4,2)		unsigned	NOT NULL	DEFAULT '0.00',
			
			`max_unique_views`		tinyint(3)		unsigned	NOT NULL	DEFAULT '0',
			
			`recorded_views`		mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			`recorded_clicks`		mediumint(8)	unsigned	NOT NULL	DEFAULT '0',
			
			PRIMARY KEY (`id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (id) PARTITIONS 31;
		");
		
		/*
			When a site advertisement needs to fill new ads, it needs to know which slots to pull from. This hash
			table can be used to point to the appropriate slots.
			
			The lookup hash value is determined by the following algorithm:
			
			$hash = Security::hash($siteHandle . $zone . $keyword, 12, 62);
		*/
		Database::exec("
		CREATE TABLE IF NOT EXISTS `sponsored_ads_by_hash`
		(
			`ad_hash`				varchar(12)					NOT NULL	DEFAULT '',
			`ad_id`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`ad_hash`, `ad_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (ad_hash) PARTITIONS 31;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `sponsored_ads_by_campaign`
		(
			`campaign_id`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			`ad_id`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`campaign_id`, `ad_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (campaign_id) PARTITIONS 31;
		");
		
		Database::exec("
		CREATE TABLE IF NOT EXISTS `sponsored_ads_clicked`
		(
			`uni_id`				int(10)			unsigned	NOT NULL	DEFAULT '0',
			`ad_id`					int(10)			unsigned	NOT NULL	DEFAULT '0',
			`date_clicked`			int(10)			unsigned	NOT NULL	DEFAULT '0',
			
			UNIQUE (`uni_id`, `ad_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8 PARTITION BY KEY (uni_id) PARTITIONS 11;
		");
		
		return $this->isInstalled();
	}
	
	
/****** Check if the plugin was successfully installed ******/
	public static function isInstalled (
	)			// <bool> TRUE if installed, FALSE if not
	
	// $plugin->isInstalled();
	{
		// Make sure the newly installed tables exist
		$pass1 = DatabaseAdmin::columnsExist("sponsored_ads", array("campaign_id", "uni_id"));
		$pass2 = DatabaseAdmin::columnsExist("sponsored_ads_by_hash", array("ad_hash", "ad_id"));
		$pass3 = DatabaseAdmin::columnsExist("sponsored_ads_by_campaign", array("campaign_id", "ad_id"));
		$pass4 = DatabaseAdmin::columnsExist("sponsored_ads_clicked", array("ad_id", "uni_id"));
		
		return ($pass1 and $pass2 and $pass3 and $pass4);
	}
	
}
