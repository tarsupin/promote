<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------------
------ About the SponsoredAds Plugin ------
-------------------------------------------

This plugin allows users to create, update, remove, or otherwise work with sponsored ads.

Sponsored ads are advertisements that are created for a specific site, and optionally a specific keyword that narrows down the placement of the ad on that site. The ad will ONLY be loaded on the site that is designated, and only where the keyword is loaded on the site, if one such keyword is provided.

For example, the "NFL" site would allow sponsored ads like the following:
	
	1. Site: "NFL" with no target keyword. This ad will appear anywhere on the NFL site.
	2. Site: "NFL", Target Keyword: "Green Bay". This ad will appear only within the "Green Bay" section of the NFL site.
	3. Site: "NFL", Target Keyword: "Lions". This ad will only appear within the "Lions" section of the NFL site.l
	... and so on.

Note that for these sponsored ads to function, the NFL site must have a system to receive the ads and load them appropriately.


---------------------------------------
------ Ad Tags for Sponsored Ads ------
---------------------------------------

Sponsored Ad tags are structured with the following format:

<!--UniAd:{"structure":"AdStructure", "site":"SiteHandle", "zone":"AdZoneName", "key":"TargetingKeyword", "desc":"Ad Description, such as Location on Page", "minCPM":{MIN_COSTS}}-->

These tags will appear on the page when the URL is scraped with the "ruacd=1" query string. The acronym is "Request UniAd Core Data", and is intended to avoid name collisions.


-----------------------------
------ Pulling Ad Data ------
-----------------------------

// API instructions


-------------------------------
------ Methods Available ------
-------------------------------


*/

abstract class SponsoredAds {
	
	
/****** Retrieve a sponsored ad by ID ******/
	public static function get
	(
		$adID		// <int> The ID of the sponsored ad to retrieve.
	)				// RETURNS <str:mixed> the data for the advertisement
	
	// $adData = SponsoredAds::get($adID);
	{
		return Database::selectOne("SELECT * FROM sponsored_ads WHERE id=? LIMIT 1", array($adID));
	}
	
	
/****** Get the targeting ads hash value ******/
	public static function lookupHash
	(
		$siteHandle		// <str> The site handle.
	,	$zone			// <str> The name of the ad zone.
	,	$keyword		// <str> The keyword associated with this hash.
	)					// RETURNS <str> the lookup hash value for sponsored ads.
	
	// $lookupHash = SponsoredAds::lookupHash($siteHandle, $zone, $keyword);
	{
		return Security::hash($siteHandle . $zone . $keyword, 12, 62);
	}
	
	
/****** Retrieve a list of ads by hash ******/
	public static function getAdsByHash
	(
		$lookupHash		// <str> The lookup hash to find the relevant sponsored ad slot.
	)					// RETURNS <int:int> a list of ad IDs detected from the hash, or 0 on failure.
	
	// $adList = SponsoredAds::getAdsByHash($lookupHash);
	{
		if(!$adList = Database::selectMultiple("SELECT ad_id FROM sponsored_ads_by_hash WHERE ad_hash=?", array($lookupHash)))
		{
			return array();
		}
		
		$ads = array();
		
		foreach($adList as $getAd)
		{
			$ads[] = (int) $getAd['ad_id'];
		}
		
		return $ads;
	}
	
	
/****** Retrieve a list of ads from a campaign ******/
	public static function getAdsFromCampaign
	(
		$campaignID		// <int> The ID of the campaign.
	)					// RETURNS <void>
	
	// $adList = SponsoredAds::getAdsFromCampaign($campaignID);
	{
		
	}
	
	
/****** Increase the click count for a particular sponsored ad ******/
	public static function clicked
	(
		$adID		// <int> The ID of the sponsored ad to increase the click count for.
	)				// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// SponsoredAds::clicked($adID);
	{
		return Database::query("UPDATE sponsored_ads SET recorded_clicks=recorded_clicks+1 WHERE id=? LIMIT 1", array($adID));
	}
	
	
/****** Set an ad to be live ******/
	public static function setLive
	(
		$adID			// <int> The ID of the ad to set live.
	,	$live = true	// <bool> TRUE to set the ad live, FALSE to set it as not live.
	)					// RETURNS <bool> TRUE on successful update, FALSE on failure.
	
	// SponsoredAds::setLive($adID, [$live]);
	{
		// Get the current value
		$sponData = Database::selectOne("SELECT is_live, site_handle, zone, keyword FROM sponsored_ads WHERE id=? LIMIT 1", array($adID));
		
		$currentLive = (bool) $sponData['is_live'];
		
		// Make sure the entry was located
		if($sponData === false)
		{
			return false;
		}
		
		// Check if the live value matches
		if($currentLive == $live)
		{
			return true;
		}
		
		// Prepare for the appropriate updates
		$lookupHash = SponsoredAds::lookupHash($sponData['site_handle'], $sponData['zone'], $sponData['keyword']);
		
		Database::startTransaction();
		
		// If we're updating the ad to be live
		if($live)
		{
			if($pass = Database::query("UPDATE sponsored_ads SET is_live=? WHERE id=? LIMIT 1", array(1, $adID)))
			{
				$pass = Database::query("REPLACE INTO sponsored_ads_by_hash (ad_hash, ad_id) VALUES (?, ?)", array($lookupHash, $adID));
			}
		}
		
		// If we're setting the ad to be not-live
		else
		{
			if($pass = Database::query("UPDATE sponsored_ads SET is_live=? WHERE id=? LIMIT 1", array(0, $adID)))
			{
				$pass = Database::query("DELETE FROM sponsored_ads_by_hash WHERE ad_hash=? AND ad_id=? LIMIT 1", array($lookupHash, $adID));
			}
		}
		
		return Database::endTransaction($pass);
	}
	
	
/****** Update a Sponsored Ad ******/
	public static function update
	(
		$adID			// <int> The ID of the advertisement.
	,	$name			// <str> The name of this ad.
	,	$imageURL		// <str> The image URL of the ad.
	,	$titleText		// <str> The title text to apply to the ad.
	,	$bodyText		// <str> The body text to apply to the ad.
	,	$linkURL		// <str> The URL to visit when the link is clicked.
	,	$bidAll			// <float> The bid for standard traffic.
	,	$bidUser		// <float> The bid for traffic that targets regular users.
	,	$bidPremium		// <float> The bid for traffic that targets premium users.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// SponsoredAds::update($adID, $name, $imageURL, $titleText, $bodyText, $linkURL, $bidAll, $bidUser, $bidPremium);
	{
		return Database::query("UPDATE sponsored_ads SET name=?, ad_url=?, ad_image_url=?, ad_title=?, ad_body=?, bid_cpm_all=?, bid_cpm_user=?, bid_cpm_premium=? WHERE id=? LIMIT 1", array($name, $linkURL, $imageURL, $titleText, $bodyText, $bidAll, $bidUser, $bidPremium, $adID));
	}
	
	
/****** Create a Sponsored Ad ******/
	public static function create
	(
		$structure		// <str> The type of ad structure.
	,	$campaignID		// <int> The ID of the campaign to assign this ad to.
	,	$uniID			// <int> The UniID to assign as the owner of this ad.
	,	$name			// <str> The name of this ad.
	,	$referenceURL	// <str> The URL that was scraped to identify this ad.
	,	$siteHandle		// <str> The site handle that this sponsored ad is being tracked at.
	,	$zone			// <str> The zone that this ad is assigned to.
	,	$keyword		// <str> The targeted keyword that this ad is assigned to.
	)					// RETURNS <int> The ID of the advertisement created, or 0 on failure.
	
	// $adID = SponsoredAds::create($structure, $campaignID, $uniID, $name, $referenceURL, $siteHandle, $zone, $keyword);
	{
		Database::startTransaction();
		
		// Create the advertisement
		if($pass = Database::query("INSERT INTO sponsored_ads (campaign_id, uni_id, name, reference_url, structure, site_handle, zone, keyword) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", array($campaignID, $uniID, $name, $referenceURL, $structure, $siteHandle, $zone, $keyword)))
		{
			$adID = Database::$lastID;
			
			$pass = Database::query("INSERT INTO sponsored_ads_by_campaign (campaign_id, ad_id) VALUES (?, ?)", array($campaignID, $adID));
		}
		
		if(Database::endTransaction($pass))
		{
			return $adID;
		}
		
		return 0;
	}
	
	
/****** Create an advertisement ******/
	public static function create_TeslaAction
	(
		$adData			// <str> The basic advertising data to use for this ad's initial creation
	,	$campaignID		// <int> The ID of the campaign to add this targeted ad to.
	,	$url			// <str> The URL that this advertisement is being created for
	)					// RETURNS <void>
	
	// /action/SponsoredAds/create?param[0]={AD_DATA}&param[1]={CAMPAIGN_ID}&param[1]={URL}
	{
		// Make sure the user is logged in to use this action
		if(!Me::$loggedIn) { return; }
		
		// Extract the appropriate data
		if(!$adData = json_decode(Decrypt::run("promo-uni", $adData), true))
		{
			Alert::saveError("Ad Error", "The data provided for this sponsored ad was formatted improperly.");
			return;
		}
		
		// Make sure the campaign exists and that the active user owns the campaign
		if(!$campaignData = AdCampaign::get($campaignID) or $campaignData['uni_id'] != Me::$id)
		{
			Alert::saveError("Campaign Issue", "You do not have permission to modify that campaign.", 7);
			return;
		}
		
		// Make sure all of the values exist
		if(isset($adData['structure']) and isset($adData['site']) and isset($adData['zone']))
		{
			// If no keyword was set, it applies to the entire site
			if(!isset($adData['key']))
			{
				$adData['key'] = "";
			}
			
			// Create the sponsored ad
			if($adID = self::create($adData['structure'], $campaignData['id'], Me::$id, "Untitled Sponsored Ad", $url, $adData['site'], $adData['zone'], $adData['key']))
			{
				Alert::saveSuccess("Ad Success", "You have successfully created a new sponsored ad.");
				
				// Load the editing page for this sponsored ad
				$_GET['return'] = "/sponsored/manage?id=" . $adID;
			}
			else
			{
				Alert::saveError("Ad Error", "There was an unexpected error while attempting to create your sponsored ad.");
			}
		}
		else
		{
			Alert::saveError("Ad Error", "Some of the required information was unavailable.");
		}
	}
	
}
