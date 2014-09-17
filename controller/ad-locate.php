<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Prepare Values
$_POST['url'] = isset($_POST['url']) ? Sanitize::url($_POST['url']) : '';

if(!$_POST['url'] and isset($_GET['url']))
{
	$_POST['url'] = Sanitize::url($_GET['url']);
}

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
	
	// If none are available, return to the URL lookup page.
	if(!$adList)
	{
		Alert::saveError("No Ads Available", "There are no ad placements available at that URL.");
	}
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

// Display the URL Ad Lookup Form
echo '
<h2>Ad Lookup</h2>
<p>This system allows you to easily look up advertisement options on a specific site or page.</p>

<form class="uniform" action="/ad-locate" method="post">' . Form::prepare("unipromote-url-scrape-locate") . '
<p>
	<strong>Enter the URL to identify advertisements on:</strong><br />
	<input type="text" name="url" value="' . $_POST['url'] . '" style="width:95%" placeholder="example: http://nfl.unifaction.com/49ers/" />
</p>
<p>
	<input type="submit" name="submit" value="Run Ad Lookup" />
</p>
</form>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");