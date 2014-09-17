<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------
------ About this API ------
----------------------------

This API returns any available sponsored ads from the ad system that fit the necessary criteria for this API call.


------------------------------
------ Calling this API ------
------------------------------
	
	// Prepare the API packet that will identify which sponsored ads to pull
	$packet = array(
		'site'			=> SITE_HANDLE		// The site handle that is requesting sponsored ads.
	,	'zone'			=> $zone			// The ad zone that ads are being requested for.
	,	'keyword'		=> $keyword			// The keyword associated with the ad to check for.
	,	'audience'		=> $audience		// The audience type to retrieve ads for ("all", "user", "premium", etc).
	);
	
	$response = Connect::to("promote", "SponsoredPull", $packet);
	
	
[ Possible Responses ]
	{List of ads from the original site}

*/

class SponsoredPull extends API {
	
	
/****** API Variables ******/
	public $isPrivate = true;			// <bool> TRUE if this API is private (requires an API Key), FALSE if not.
	public $encryptType = "fast";		// <str> The encryption algorithm to use for response, or "" for no encryption.
	public $allowedSites = array();		// <int:str> the sites to allow the API to connect with. Default is all sites.
	public $microCredits = 20;			// <int> The cost in microcredits (1/10000 of a credit) to access this API.
	public $minClearance = 4;			// <int> The minimum clearance level required to use this API.
	
	
/****** Run the API ******/
	public function runAPI (
	)					// RETURNS <int:[int:mixed]> an array of users and the site / connections count.
	
	// $this->runAPI()
	{
		// Make sure you sent the date
		if(!isset($this->data['site']) or !isset($this->data['zone']) or !isset($this->data['audience']))
		{
			return array();
		}
		
		// Make sure the audience is selected properly
		$audience = Sanitize::variable($this->data['audience']);
		
		if(!in_array($audience, array("all", "user", "premium")))
		{
			return array();
		}
		
		// Prepare Values
		$availableAds = array();
		$keyword = isset($this->data['keyword']) ? Sanitize::variable($this->data['keyword']) : "";
		
		// If the keyword is specific (not empty), attempt to gather ads from this list
		if($keyword)
		{
			$hash = SponsoredAds::lookupHash($this->data['site'], $this->data['zone'], $keyword);
			
			$availableAds = SponsoredAds::getAdsByHash($hash);
		}
		
		// If we have less than three ads available, we need to search empty keywords as backup
		if(count($availableAds) < 3)
		{
			$hash = SponsoredAds::lookupHash($this->data['site'], $this->data['zone'], "");
			
			$availableAds = array_merge($availableAds, SponsoredAds::getAdsByHash($hash));
		}
		
		// Prepare SQL Values
		list($sqlWhere, $sqlArray) = Database::sqlFilters(array("id" => $availableAds));
		
		/*
			Criteria used to decide:
			1. Which ones have the highest CPM
			2. The selected audience to pull
		*/
		
		// Get the full list of potential ads and their values
		$adVals = Database::selectMultiple("SELECT id, campaign_id, keyword, ad_url, ad_image_url, ad_title, ad_body, bid_cpm_" . $audience . " FROM sponsored_ads WHERE " . $sqlWhere . " AND bid_cpm_" . $audience . " > 0 ORDER BY bid_cpm_" . $audience . " DESC", $sqlArray);
		
		// Retrieve "user" audiences if premium could not be located
		if(count($adVals) == 0 and $audience == "premium")
		{
			$audience = "user";
			
			$adVals = Database::selectMultiple("SELECT id, campaign_id, keyword, ad_url, ad_image_url, ad_title, ad_body, bid_cpm_" . audience . " FROM sponsored_ads WHERE " . $sqlWhere . " AND bid_cpm_" . $audience . " > 0 ORDER BY bid_cpm_" . $audience . " DESC", $sqlArray);
		}
		
		// Retrieve "all" audiences if "user" could not be located
		if(count($adVals) == 0 and $audience == "user")
		{
			$audience = "all";
			
			$adVals = Database::selectMultiple("SELECT id, campaign_id, keyword, ad_url, ad_image_url, ad_title, ad_body, bid_cpm_" . $audience . " FROM sponsored_ads WHERE " . $sqlWhere . " AND bid_cpm_" . $audience . " > 0 ORDER BY bid_cpm_" . $audience . " DESC", $sqlArray);
		}
		
		// Cycle through the ads provided and gather campaign data
		// Campaign will set whether or not there is money for the ad
		// Also set the number of views that are allowed to send
		$sponsoredAds = array();
		
		foreach($adVals as $ad)
		{
			// Recognize Integers
			$ad['id'] = (int) $ad['id'];
			$ad['campaign_id'] = (int) $ad['campaign_id'];
			$ad['bid_cpm_' . $audience] = (int) $ad['bid_cpm_' . $audience];
			
			// Gather the campaign data
			if(!$campaignData = AdCampaign::get($ad['campaign_id']))
			{
				continue;
			}
			
			// Make sure you have enough credits available in the ad campaign
			if($campaignData['credits_available'] <= 0)
			{
				continue;
			}
			
			// Check what today's limits will provide
			// (MAX PER DAY)
			
			// Determine values that we can share / remove
			$views = 0;
			
			if($ad['bid_cpm_' . $audience] * 1000 > $campaignData['credits_available'])
			{
				$views = 1000;
			}
			else
			{
				$views = ceil($campaignData['credits_available'] / $ad['bid_cpm_' . $audience]);
			}
			
			// Subtract the credits available
			$campaignData['credits_available'] = max(0, $campaignData['credits_available'] - ($ad['bid_cpm_' . $audience] * $views / 1000));
			
			// Update the campaign data
			if(!Database::query("UPDATE ad_campaigns SET credits_available=? WHERE id=? LIMIT 1", array($campaignData['credits_available'], $campaignData['id'])))
			{
				continue;
			}
			
			// The ad is successful, so we can add it to the list of ads to return
			$ad['views_remaining'] = $views;
			$ad['bid_cpm'] = $ad['bid_cpm_' . $audience];
			
			$sponsoredAds[$ad['id']] = $ad;
		}
		
		return $sponsoredAds;
	}
	
}
