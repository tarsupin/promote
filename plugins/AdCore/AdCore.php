<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

-------------------------------------
------ About the AdCore Plugin ------
-------------------------------------

This plugin allows users to create, update, remove, or otherwise work with ads.


-------------------------------
------ Retrieving UniAds ------
-------------------------------

This system attempts to use a very simple mechanism for loading ads. All you need to do is enter the desired URL that you would like to run an advertisement on, and the page will return a list of options for you.

To manage this system, the system needs to scrape the page for advertising data, which is returned as HTML comments that can be clearly identified. These ad values appear something like this:
	
	<!--UniAd:{"structure":"AdStructure", "site":"SiteHandle", "zone":"AdZoneName", "key":"TargetingKeyword", "desc":"Ad Description, such as Location on Page", "minCPM":{MIN_COSTS}}-->
	
In order to scrape these comments, the appropriate query string must be used to request them. This is done by calling "ruacd=1", or "Request UniAd Core Data". The strange acronym is to avoid any name collisions.




Future Ad Data to consider:
$campaign
$type
$title
$blurb
$image
$imageRatio
$url
$timeStart
$timeEnd
$gender (0, 1, 2)
$ageMin
$ageMax
$incomeMin
$incomeMax
$race
$ethnicity
$personalityGroup
$gpsTarget
$gpsDistance
$bidCPM
$bidTagCPM
$bidAudienceCPM
$bidUserMult			// This value indicates how much more to pay for active users of the site
$bidUniqueMult			// This value indicates how much more to pay for unique views (six impressions per day)

-------------------------------
------ Methods Available ------
-------------------------------


*/

abstract class AdCore {
	
	
/****** Plugin Variables ******/
	public static $adTagPrefix = "<!--UniAd:";
	
	
/****** Scrub a URL for UniFaction Ads ******/
	public static function scrapePageForAds
	(
		$url		// <str> The URL to scrub for UniFaciton advertisement zones.
	)				// RETURNS <void>
	
	// $adList = AdCore::scrapePageForAds($url);
	{
		// Prepare the URL so that it can locate UniAds
		$parsedURL = URL::parse($url);
		
		$loadURL = $parsedURL['scheme'] . '://' . $parsedURL['baseDomain'] . (isset($parsedURL['path']) ? '/' . $parsedURL['path'] : '') . "?ruacd=1";
		
		// Get the HTML from the URL used
		if(!$html = file_get_contents($loadURL))
		{	
			return;
		}
		
		// Prepare Values
		$ads = array();
		$pos = 0;
		$pos2 = 0;
		
		// Scan for anything with <!--UniAd:
		$scanLen = strlen(self::$adTagPrefix);
		
		while(($pos = strpos($html, "<!--UniAd:", $pos + $scanLen)) !== false)
		{
			$pos2 = strpos($html, "-->", $pos);
			
			// Retrieve the inner advertising data
			if($inner = substr($html, $pos + $scanLen, $pos2 - $pos - $scanLen))
			{
				$ads[] = json_decode($inner, true);
			}
		}
		
		return $ads;
	}
	
	
/****** Add Ad Credits to the Account ******/
	public static function addCredits
	(
		$uniID			// <int> The UniID to add credits to.
	,	$credits		// <float> The number of credits to add to the campaign.
	)					// RETURNS <bool> TRUE on success, FALSE on failure.
	
	// AdCore::addCredits($uniID, $credits);
	{
		return Database::query("UPDATE users SET credits=credits+? WHERE uni_id=? LIMIT 1", array($credits, $uniID));
	}
	
}
