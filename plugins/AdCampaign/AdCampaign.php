<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-----------------------------------------
------ About the AdCampaign Plugin ------
-----------------------------------------

This plugin allows users to create, modify, and work with advertising campaigns on the ad site.


-------------------------------
------ Methods Available ------
-------------------------------


*/

abstract class AdCampaign {
	
	
/****** Create an Ad Campaign ******/
	public static function create
	(
		$uniID		// <int> The UniID of the user creating this campaign.
	,	$title		// <str> The title of the campaign.
	)				// RETURNS <int> The ID of the campaign created, or 0 on failure.
	
	// $campaignID = AdCampaign::create($uniID, $title);
	{
		Database::startTransaction();
		
		// Create the campaign
		if($pass = Database::query("INSERT INTO ad_campaigns (uni_id, title) VALUES (?, ?)", array($uniID, $title)))
		{
			$campaignID = Database::$lastID;
			
			$pass = Database::query("INSERT INTO ad_campaigns_by_user (uni_id, campaign_id) VALUES (?, ?)", array($uniID, $campaignID));
		}
		
		if(Database::endTransaction($pass))
		{
			return $campaignID;
		}
		
		return 0;
	}
	
	
/****** Retrieve an Ad Campaign ******/
	public static function get
	(
		$campaignID		// <int> The ID of the campaign to retrieve.
	)					// RETURNS <str:mixed> The data for the campaign.
	
	// $campaignData = AdCampaign::get($campaignID);
	{
		return Database::selectOne("SELECT * FROM ad_campaigns WHERE id=? LIMIT 1", array($campaignID));
	}
	
	
/****** Add Credits to an Advertising Campaign ******/
	public static function addCredits
	(
		$uniID			// <int> The UniID running this campaign.
	,	$campaignID		// <int> The ID of the campaign to retrieve.
	,	$credits		// <float> The number of credits to add to the campaign.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// AdCampaign::addCredits($uniID, $campaignID, $credits);
	{
		Database::startTransaction();
		
		if($pass = Database::query("UPDATE users SET credits=credits-? WHERE uni_id=? LIMIT 1", array($credits, $uniID)))
		{
			$pass = Database::query("UPDATE ad_campaigns SET credits_available=credits_available+? WHERE id=? AND uni_id=? LIMIT 1", array($credits, $campaignID, $uniID));
		}
		
		return Database::endTransaction($pass);
	}
	
}
