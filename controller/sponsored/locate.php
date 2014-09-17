<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure the user is logged in
if(!Me::$loggedIn)
{
	header("Location: /welcome"); exit;
}

// Make sure the appropriate values are sent
if(!isset($_GET['campaign']))
{
	header("Location: /"); exit;
}

// Make sure the campaign exists and that the active user owns the campaign
if(!$campaignData = AdCampaign::get((int) $_GET['campaign']) or $campaignData['uni_id'] != Me::$id)
{
	Alert::saveError("Invalid Campaign", "You do not have permission to access that campaign.", 7);
	
	header("Location: /"); exit;
}

// Prepare Values
$_POST['url'] = isset($_POST['url']) ? Sanitize::url($_POST['url']) : '';

// Run the URL Form
$adList = array();

if(Form::submitted("unipromote-url-scrape"))
{
	// Retrieve a list of Ads
	$adList = AdCore::scrapePageForAds($_POST['url']);
	
	// Cycle through each ad and retrieve the related slot
	/*
	foreach($adList as $key => $ad)
	{
		$lookupHash = SponsoredAds::lookupHash($ad['site'], $ad['zone'], $ad['key']);
	}
	*/
}

// If none are available, return to the URL lookup page.
if(!$adList)
{
	Alert::saveError("No Ads Available", "There are no ad placements available at that URL.");
	
	header("Location: /sponsored/create?campaign=" . $campaignData['id'] . "&url=" . Sanitize::url($_POST['url'])); exit;
}

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

echo '
<style>
	.ad-available-disp { margin-top:12px; background-color:#dddddd; }
	.ad-available-disp p { color:#555555; margin-bottom:0px; }
	
	.ad-available-disp li { padding:6px; }
	.ad-available-disp li a { margin:0px; display:block; width:100%; height:100%; }
	
	.ad-available-disp li:hover { background-color:#cccccc; }
</style>

<h3>Available Ads</h3>
<p>There ' . ((count($adList) == 1) ? 'is one ad' : 'are ' . Number::toWord(count($adList)) . ' ads') . ' available on <strong>' . $_POST['url'] . '</strong></p>';

// Loop through each and and display it
foreach($adList as $adData)
{
	// Display the Ad
	echo '
	<ul class="ad-available-disp">';
	
	$struct = AdStructure::load($adData['structure']);
	
	// Image Advertisement
	if($struct['type'] == "image")
	{
		// Make sure the image advertisement is properly formatted
		if(isset($struct['img_width']) and isset($struct['img_height']))
		{
			echo '
			<li><a href="/action/SponsoredAds/create?param[0]=' . urlencode(Encrypt::run("promo-uni", json_encode($adData), "open")) . '&param[1]=' . $campaignData['id'] . '&param[2]=' . urlencode($_POST['url']) . '&return=' . urlencode("/") . '">
				<h4>' . $struct['name'] . ' (' . $struct['img_width'] . 'x' . $struct['img_height'] . '): ' . $adData['zone'] . '</h4>
				<p>' . $adData['desc'] . '</p>
				<p>This ad ' . (isset($adData['key']) ? 'only targets pages categorized under the "<span style="font-weight:bold">' . $adData['key'] . '</span>" keyword.' : 'will be displayed on the entire site, regardless of page categorization.') . '</p>
			</a></li>';
		}
	}
	
	// Text Advertisement
	if($struct['type'] == "text")
	{
		echo '
		<li><a href="/action/SponsoredAds/create?param[0]=' . urlencode(Encrypt::run("promo-uni", json_encode($adData), "open")) . '&param[1]=' . $campaignData['id'] . '&param[2]=' . urlencode($_POST['url']) . '&return=' . urlencode("/") . '">
			<h4>' . $struct['name'] . ' (' . $struct['width'] . 'x' . $struct['height'] . '): ' . $adData['zone'] . '</h4>
			<p>' . $adData['desc'] . '</p>
			<p>This ad ' . (isset($adData['key']) ? 'only targets pages categorized under the "<span style="font-weight:bold">' . $adData['key'] . '</span>" keyword.' : 'will be displayed on the entire site, regardless of page categorization.') . '</p>
		</a></li>';
	}
	
	echo '
	</ul>';
}

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");