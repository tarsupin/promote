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
if(!$campaignData = AdCampaign::get($_GET['campaign']) or $campaignData['uni_id'] != Me::$id)
{
	Alert::saveError("Invalid Campaign", "You do not have permission to access that campaign.", 7);
	
	header("Location: /"); exit;
}

// Prepare Values
$_POST['url'] = isset($_POST['url']) ? Sanitize::url($_POST['url']) : '';

if(!$_POST['url'] and isset($_GET['url']))
{
	$_POST['url'] = Sanitize::url($_GET['url']);
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
<form class="uniform" action="/sponsored/locate?campaign=' . $_GET['campaign'] . '" method="post">' . Form::prepare("unipromote-url-scrape") . '
<p>
	<strong>Enter the URL that you would like to advertise on:</strong><br />
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