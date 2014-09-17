<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

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

// Display the Advertisement Creation Form
echo '
<form class="uniform" action="/create-promo" method="post">' . Form::prepare("unipromote-create-ad") . '

<div style="background-color:#eeeeee; padding:12px; margin-bottom:22px;">
	<h3>Details of this Advertisement</h3>
	<p>
		<strong>Source URL used to load this advertisement:</strong><br />
		<input type="text" name="url" value="" style="width:95%" readonly />
	</p>
	<p>
		<strong>Site that this advertisement will run on:</strong><br />
		<input type="text" name="site" value="" style="width:95%" readonly />
	</p>
	<p>
		<strong>Specific targeting keyword for the site:</strong><br />
		<input type="text" name="site_keyword" value="" style="width:95%" readonly />
	</p>
	<p>
		<strong>The Ad Zone being promoted on:</strong><br />
		<input type="text" name="zone" value="" style="width:95%" readonly />
	</p>
</div>

<p>
	<strong>Campaign:</strong><br />
	{ select here }
</p>
<p>
	<strong>Title of this Promotion:</strong><br />
	<input type="text" name="title" value="" style="width:95%" />
</p>
<p>
	<strong>Your maximum bid:</strong><br />
	$<input type="text" name="bid_cpm_all" value="" style="width:70px;" placeholder="0.45" /> per 1000 views
</p>
<p>
	<strong>Your maximum bid for targeting to active users (<a href="/faq/audiences#premiumbid">Learn More</a>):</strong><br />
	$<input type="text" name="bid_cpm_user" value="" style="width:70px;" placeholder="1.45" /> per 1000 views
</p>
<p>
	<strong>Your maximum bid for premium traffic (<a href="/faq/audiences#premiumbid">Learn More</a>):</strong><br />
	$<input type="text" name="bid_cpm_user" value="" style="width:70px;" placeholder="2.45" /> per 1000 views
</p>
<p>
<p>
	<strong>Your bid for premium traffic (<a href="/faq/audiences#premiumbid">Learn More</a>):</strong><br />
	$<input type="text" name="bid_cpm_user" value="" style="width:70px;" placeholder="2.45" /> per 1000 views
</p>
	<input type="submit" name="submit" value="Run Ad Lookup" />
</p>
</form>';

// Database::query("INSERT INTO ads_target_promos (campaign_id, uni_id, title, reference_url, audience_type, bid_cpm, ad_targ_keyword, ad_zone, ad_type, ad_data) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", array($campaignID);

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");