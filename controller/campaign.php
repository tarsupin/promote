<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure the user is logged in
if(!Me::$loggedIn)
{
	Me::redirectLogin("/campaign" . (isset($_GET['id']) ? "?id=" . ((int) $_GET['id']) : ""), "/welcome");
}

// Make sure we have the proper data sent
if(!isset($_GET['id']))
{
	header("Location: /"); exit;
}

// Get the loaded campaign, making sure that the active user owns it
if(!$campaignData = AdCampaign::get((int) $_GET['id']))
{
	Alert::saveError("Campaign Doesn't Exist", "That campaign does not exist or an error prevented it from loading.");
	
	header("Location: /"); exit;
}

// Get the list of advertisements for this campaign
$adList = Database::selectMultiple("SELECT a.* FROM sponsored_ads_by_campaign c INNER JOIN sponsored_ads a ON c.ad_id=a.id WHERE c.campaign_id=?", array($campaignData['id']));

// Run Global Script
require(APP_PATH . "/includes/global.php");

// Display the Header
require(SYS_PATH . "/controller/includes/metaheader.php");
require(SYS_PATH . "/controller/includes/header.php");

// Display Side Panel
require(SYS_PATH . "/controller/includes/side-panel.php");

// Display Content
echo '
<!-- Content -->
<div id="content">'	. Alert::display();

// Display the Page
echo '
<style>
	.ad-campaign { border:solid 1px #555555; margin-bottom:12px; }
	.ad-campaign>ul { background-color:#eeeeee; padding:6px; }
	.ad-campaign>ul>li>a { padding:6px; margin:0px; display:block; width:100%; height:100%; color:#555555 !important; }
	.ad-campaign>ul>li:hover { background-color:#cccccc; }
</style>

<div>
	<a class="button" href="/sponsored/create?campaign=' . $campaignData['id'] . '">New Sponsored Ad</a>
</div>

<h3>Campaign Data</h3>
<div>Credits Available: $' . number_format($campaignData['credits_available'], 2) . ' (<a href="/campaign-credits?id=' . $campaignData['id'] . '">Add more credits to this campaign</a>)</div>
<div>Maximum Spending per Day: $' . number_format($campaignData['max_per_day'], 2) . '</div>

<h3 style="margin-top:22px;">Campaign Ads</h3>';

// Show the available ads in this campaign
if($adList)
{
	echo '
	<div class="ad-campaign">
		<ul>';
	
	foreach($adList as $adData)
	{
		$struct = AdStructure::load($adData['structure']);
		
		// Image Advertisement
		if($struct['type'] == "image" and isset($struct['img_width']) and isset($struct['img_height']))
		{
			echo '
			<li><a href="/sponsored/manage?id=' . $adData['id'] . '">
				<h4>' . $adData['name'] . ' - ' . $struct['name'] . ' (' . $struct['img_width'] . 'x' . $struct['img_height'] . ')</h4>
				<div>Site: ' . $adData['site_handle'] . '</div>
				<div>Ad Zone: ' . $adData['zone'] . '</div>
				<div>Site Section / Keyword: ' . $adData['keyword'] . '</div>
			</a></li>';
		}
	}
	
	echo '
		</ul>
	</div>';
}

// If there are no ads available in this campaign, display this
else
{
	echo '<div>There are no ads currently assigned to this campaign.</div>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");