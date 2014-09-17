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

echo '
<h1>Advertise on UniFaction</h1>
<p>UniFaction offers highly targeted advertising that specifically targets users that are interested in your company\'s content. Ads can be targeted by individual sites, pages, and premium traffic to maximize your ROI.</p>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");