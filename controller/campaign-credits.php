<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure the user is logged in
if(!Me::$loggedIn)
{
	Me::redirectLogin("/", "/welcome");
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

// Make sure this campaign is owned by the appropriate user
if($campaignData['uni_id'] != Me::$id)
{
	Alert::error("Illegal Campaign Access", "You are not allowed to access this ad campaign.", 8);
	
	header("Location: /"); exit;
}

// Run Form
if(Form::submitted('credits-ad-uni'))
{
	FormValidate::number_float("Credits", $_POST['credits'], 0.00, (float) number_format(Me::$vals['credits'], 2));
	
	if(FormValidate::pass())
	{
		if(AdCampaign::addCredits(Me::$id, $campaignData['id'], (float) $_POST['credits']))
		{
			Alert::saveSuccess("Credits Added", "Successfully added $" . number_format($_POST['credits'], 2) . ' ad credits to the "' . $campaignData['title'] . '" campaign.');
			
			header("Location: /"); exit;
		}
		else
		{
			Alert::error("Credits Failed", "There was an unexpected error while trying to process these credits.");
		}
	}
}

// Sanitize Data
else
{
	$_POST['credits'] = isset($_POST['credits']) ? number_format($_POST['credits'], 2) : "";
}

// Prepare the URL for the Ad Credit transactions
$haveFee = false;
$imageURL = "";
$minAmount = 0.00;
$maxAmount = 0.00;
$defAmount = 0.00;
$opts = array();
$custom = array();

// Retrieve the URL to run this Transaction
$transactionURL = Credits::transactionURL(Me::$id, "Get Ad Credits", "This purchase will exchange UniJoule for Ad Credits.", "GetCreditsAPI", SITE_URL . "/campaign-credits?id=" . $campaignData['id'], $haveFee, $imageURL, $minAmount, $maxAmount, $defAmount, $opts, $custom);

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
<h1>Assign Credits to "' . $campaignData['title'] . '" Campaign</h1>

<p>Credits in Account: $' . number_format(Me::$vals['credits'], 2) . '</p>
<p>Credits in Campaign: $' . number_format($campaignData['credits_available'], 2) . '</p>

<form class="uniform" action="/campaign-credits?id=' . $campaignData['id'] . '" method="post">' . Form::prepare('credits-ad-uni') . '
	<p>
		<strong>How many credits would you like to add to this campaign?</strong><br />
		<input type="text" name="credits" value="' . number_format($_POST['credits'], 2) . '" placeholder="" tabindex="10" autocomplete="off" maxlength="10" />
		<br /><span style="font-size:0.9em;">Note: You have $' . number_format(Me::$vals['credits'], 2) . ' of ad credits available. <a href="' . $transactionURL . '">Get more</a>.</span>
	</p>
	<p><input type="submit" name="submit" value="Add Credits" /></p>
</form>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");