<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure the URL was provided
if(isset($_GET['d']))
{
	// Get the URL to visit
	if($promoData = Decrypt::run("adURL", $_GET['d']))
	{
		if($promoData = json_decode($promoData, true) and is_array($promoData) and count($promoData) == 3)
		{
			// Extract the data
			list($adID, $url, $uniID) = $promoData;
			
			// Track the user that clicked this advertisement
			if(Me::$id)
			{
				Database::query("REPLACE INTO `sponsored_ads_clicked` (uni_id, ad_id, date_clicked) VALUES (?, ?, ?)", array($uniID, $adID, time()));
			}
			
			// If the user was not logged in (set to 0), then track the IP
			else
			{
				
			}
			
			// Increase the number of clicks on the advertisement
			SponsoredAds::clicked($adID);
			
			// Go to the designated URL
			header("Location: " . $url); exit;
		}
	}
}


// Redirect to the welcome page if everything failed
header("Location: /welcome"); exit;