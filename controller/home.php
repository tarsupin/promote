<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// If the user is not logged in, send them to the welcome page instead
if(!Me::$loggedIn)
{
	Me::redirectLogin("/", "/welcome");
}

// Create the campaign
if(Form::submitted("unipromote-create-campaign"))
{
	FormValidate::safeword("Campaign Title", $_POST['title'], 1, 42);
	
	if(FormValidate::pass())
	{
		if($campaignID = AdCampaign::create(Me::$id, $_POST['title']))
		{
			Alert::saveSuccess("Campaign Created", "The campaign has been successfully created!");
			
			header("Location: /campaign?id=" . $campaignID); exit;
		}
		else
		{
			Alert::saveError("Campaign Failed", "There was an error while attempting to create this campaign.");
		}
	}
}

// Sanitize Values
$_POST['title'] = isset($_POST['title']) ? Sanitize::safeword($_POST['title'], "?") : "";

// Get my campaign list
$campaignList = Database::selectMultiple("SELECT c.* FROM ad_campaigns_by_user u INNER JOIN ad_campaigns c ON u.campaign_id=c.id WHERE u.uni_id=? ORDER BY c.title", array(Me::$id));

// Prepare the URL for the Ad Credit transactions
$haveFee = false;
$imageURL = "";
$minAmount = 0.00;
$maxAmount = 0.00;
$defAmount = 0.00;
$opts = array();
$custom = array();

// Retrieve the URL to run this Transaction
$transactionURL = Credits::transactionURL(Me::$id, "Get Ad Credits", "This purchase will exchange UniJoule for Ad Credits.", "GetCreditsAPI", SITE_URL, $haveFee, $imageURL, $minAmount, $maxAmount, $defAmount, $opts, $custom);

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
	.ad-campaign>ul { background-color:#eeeeee; }
	.ad-campaign>ul>li {  }
	.ad-campaign>ul>li>a { padding:6px; margin:0px; display:block; width:100%; height:100%; color:#555555 !important; }
	.ad-campaign>ul>li:hover { background-color:#cccccc; }
</style>';

echo '
<h3>My Promotion Data</h3>
<div>Ad Credits: $' . number_format(Me::$vals['credits'], 2) . ' (<a href="' . $transactionURL . '">Get More Credits</a>)</div>
';

echo '
<h3 style="margin-top:22px;">My Ad Campaigns</h3>';

// Load your campaigns
if($campaignList)
{
	echo '
	<div class="ad-campaign">
		<ul>';
	
	foreach($campaignList as $campaign)
	{
		echo '
		<li><a href="/campaign?id=' . $campaign['id'] . '"><span style="font-weight:bold;">' . $campaign['title'] . '</span><br />Has <span style="font-weight:bold">$' . number_format($campaign['credits_available'], 2) . '</span> available, spending up to <span style="font-weight:bold">$' . $campaign['max_per_day'] . '</span> per day.</a></li>';
	}
	
	echo '
		</ul>
	</div>';
}

// If you have no campaigns, indicate this
else
{
	echo '
	<p>You currently do not have any campaigns available.</p>';
}

// Display the Campaign Creation Form
echo '
<div style="margin-top:42px;">
<h3>New Ad Campaign</h3>
<form class="uniform" action="/" method="post">' . Form::prepare("unipromote-create-campaign") . '
<p>
	<input type="text" name="title" value="' . $_POST['title'] . '" style="width:95%" tabindex="10" autofocus autocomplete="off" placeholder="Choose a title . . ." maxlength="42" />
</p>
<p>
	<input type="submit" name="submit" value="Create Ad Campaign" />
</p>
</form>
</div>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");