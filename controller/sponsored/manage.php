<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Make sure the user is logged in
if(!Me::$loggedIn)
{
	header("Location: /welcome"); exit;
}

// Make sure the appropriate values are sent
if(!isset($_GET['id']))
{
	header("Location: /"); exit;
}

// Make sure the ad exists and that the active user owns the ad
if(!$adData = SponsoredAds::get((int) $_GET['id']) or $adData['uni_id'] != Me::$id)
{
	Alert::saveError("Invalid Ad", "You do not have permission to access this ad.", 7);
	
	header("Location: /"); exit;
}

// Prepare Important Data
$struct = AdStructure::load($adData['structure']);
$campaignData = AdCampaign::get($adData['campaign_id']);

// Prepare the minimum bids
if(isset($struct['bid']))
{
	$bid = $struct['bid'];
}
else
{
	$bid = array(
		'all'		=> 0.45
	,	'user'		=> 2.45
	,	'premium'	=> 4.95
	);
}

// Sanitize Values
$_POST['ad_image_url'] = isset($_POST['ad_image_url']) ? Sanitize::url($_POST['ad_image_url']) : $adData['ad_image_url'];
$_POST['ad_title'] = isset($_POST['ad_title']) ? Sanitize::safeword($_POST['ad_title'], "?") : $adData['ad_title'];
$_POST['ad_body'] = isset($_POST['ad_body']) ? Sanitize::safeword($_POST['ad_body'], "?") : $adData['ad_body'];
$_POST['ad_url'] = isset($_POST['ad_url']) ? Sanitize::url($_POST['ad_url']) : $adData['ad_url'];

$_POST['name'] = isset($_POST['name']) ? Sanitize::safeword($_POST['name']) : $adData['name'];
$_POST['bid_all'] = (isset($_POST['bid_all']) and $_POST['bid_all']) ? number_format((float) $_POST['bid_all'], 2) : $adData['bid_cpm_all'];
$_POST['bid_user'] = (isset($_POST['bid_user']) and $_POST['bid_user']) ? number_format((float) $_POST['bid_user'], 2) : $adData['bid_cpm_user'];
$_POST['bid_premium'] = (isset($_POST['bid_premium']) and $_POST['bid_premium']) ? number_format((float) $_POST['bid_premium'], 2) : $adData['bid_cpm_premium'];

// Run the Form
if(Form::submitted("unipromote-edit-spons-ad"))
{
	FormValidate::safeword("Ad Name", $_POST['name'], 3, 32);
	FormValidate::url("Ad URL", $_POST['ad_url'], 7, 100);
	
	// If this ad requires title text, make sure it's loaded
	if($struct['require']['title'])
	{
		FormValidate::safeword("Title Text", $_POST['ad_title'], 3, 42, "?");
	}
	
	// If this ad requires body text, make sure it's loaded
	if($struct['require']['body'])
	{
		FormValidate::safeword("Body Text", $_POST['ad_body'], 3, 200, "?");
	}
	
	// Prepare a check to see if at least one of the bids were used
	$madeBid = false;
	
	// Check Traffic Bid
	if($_POST['bid_all'])
	{
		$madeBid = true;
		
		if((float) $_POST['bid_all'] < $bid['all'])
		{
			Alert::error("Sponsored Traffic Bid", "Sponsored Traffic Bid must be at least " . $bid['all'] . " or 0 (to set as inactive)");
		}
	}
	
	// Check User Bid
	if($_POST['bid_user'])
	{
		$madeBid = true;
		
		if((float) $_POST['bid_user'] < $bid['user'])
		{
			Alert::error("Targeted User Bid", "Targeted User Bid must be at least " . $bid['user'] . " or 0 (to set as inactive)");
		}
	}
	
	// Check Premium Bid
	if($_POST['bid_premium'])
	{
		$madeBid = true;
		
		if((float) $_POST['bid_premium'] < $bid['premium'])
		{
			Alert::error("Premium Bid", "Premium Bid must be at least " . $bid['premium'] . " or 0 (to set as inactive)");
		}
	}
	
	// Upload an image, if applicable
	if($struct['require']['image_url'] and $_FILES['image']['tmp_name'] and FormValidate::pass())
	{
		// Initialize the image upload plugin
		$imageUpload = new ImageUpload($_FILES['image']);
		
		// Set your image requirements
		$imageUpload->minHeight = $struct['img_height'];
		$imageUpload->maxHeight = $struct['img_height'];
		$imageUpload->minWidth = $struct['img_width'];
		$imageUpload->maxWidth = $struct['img_width'];
		
		$imageUpload->maxFilesize = 1024 * 3000;			// 3 megabytes max
		$imageUpload->saveMode = Upload::MODE_OVERWRITE;
		
		// Set the image directory
		$srcData = Upload::fileBucketData($adData['id'], 10000);	// Change to an actual integer
		$bucketDir = '/assets/spons/' . $srcData['main_directory'] . '/' . $srcData['second_directory'];
		$imageDir = CONF_PATH . $bucketDir;
		
		// Save the image to a chosen path
		if($imageUpload->validate())
		{
			$image = new Image($imageUpload->tempPath, $imageUpload->width, $imageUpload->height, $imageUpload->extension);
			
			if(FormValidate::pass())
			{
				// Prepare the filename for this image
				$imageUpload->filename = $adData['id'];
				
				// Save the original image
				$image->save($imageDir . "/" . $imageUpload->filename . ".jpg");
				
				$_POST['ad_image_url'] = SITE_URL . $bucketDir . '/' . $imageUpload->filename . '.jpg';
			}
		}
	}
	
	// Prepare "live" testing
	$liveTest = false;
	
	if(!isset($_POST['set_inactive']) and (isset($_POST['set_live']) or $adData['is_live']))
	{
		$liveTest = true;
	}
	
	// Run checks that only need to be considered if the ad is live (or about to be)
	if($liveTest)
	{
		if($struct['require']['image_url'])
		{
			// Make sure the image URL is valid
			FormValidate::url("Image URL", $_POST['ad_image_url'], 3, 100);
		}
		
		// Make sure a bid is set if you're live
		if(!$madeBid)
		{
			Alert::error("Bid Required", "There must be a bid assigned to this ad.");
		}
	}
	
	// Run the update
	if(FormValidate::pass())
	{
		if(SponsoredAds::update($adData['id'], $_POST['name'], $_POST['ad_image_url'], $_POST['ad_title'], $_POST['ad_body'], $_POST['ad_url'], $_POST['bid_all'], $_POST['bid_user'], $_POST['bid_premium']))
		{
			// If you're setting the ad to live
			if(isset($_POST['set_live']))
			{
				if(SponsoredAds::setLive($adData['id'], true))
				{
					$adData['is_live'] = 1;
				}
				else
				{
					Alert::error("Live Failed", "There was an issue while trying to set this ad live.");
				}
			}
			
			// If you're setting the ad to inactive
			else if(isset($_POST['set_inactive']))
			{
				if(SponsoredAds::setLive($adData['id'], false))
				{
					$adData['is_live'] = 0;
				}
				else
				{
					Alert::error("Inactive Failed", "There was an issue while trying to set this ad as inactive. Please try again.");
				}
			}
			
			// Make the final check to see if every pass was successful
			if(FormValidate::pass())
			{
				Alert::success("Ad Updated", "This sponsored ad has been updated with your changes.");
			}
		}
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

// Display the Advertisement Creation Form
echo '
<h3><a href="/campaign?id=' . $campaignData['id'] . '">' . $campaignData['title'] . '</a> &gt; ' . $_POST['name'] . '</h3>

<form class="uniform" action="/sponsored/manage?id=' . $_GET['id'] . '" method="post" enctype="multipart/form-data">' . Form::prepare("unipromote-edit-spons-ad");

// Display the Advertisement Size
if($struct['type'] == "image")
{
	if($_POST['ad_image_url'])
	{
		echo '
		<div><img src="' . $_POST['ad_image_url'] . '" style="width:' . $struct['img_width'] . '; height:' . $struct['img_height'] . 'px;  margin-bottom:22px;" /></div>';
	}
	else
	{
		echo '
		<div style="background-color:#cccccc; border:dashed 1px #333333; margin-bottom:22px; text-align:center; vertical-align:middle; line-height:' . $struct['height'] . 'px; width:' . $struct['width'] . 'px; height:' . $struct['height'] . 'px;">' . $struct['width'] . ' x ' . $struct['height'] . ' Image</div>';
	}
}
else if($struct['type'] == "text")
{
	if($_POST['ad_title'] and $_POST['ad_body'])
	{
		echo '<a href="' . $adData['ad_url'] . '" rel="nofollow" style="display:block; width:' . $struct['width'] . 'px; height:' . $struct['height'] . 'px;"><span style="display:block; color:#555555 !important; width:' . $struct['width'] . 'px; height:' . $struct['height'] . 'px; overflow:hidden; background-color:#ffffff; border:solid 1px #888888; border-radius:2px; line-height:120%; position:relative;"><div style="padding:5px;"><div style="color:#333333; font-weight:bold; margin-bottom:4px;">' . $_POST['ad_title'] . '</div><div style="color:#555555; font-size:14px;">' . $_POST['ad_body'] . '</div><div style="position:absolute; right:5px; bottom:2px; text-align:right; font-size:12px;">Follow this URL</div></div></span></a>
		
		<div style="font-size:0.8em; margin-top:8px; margin-bottom:22px;">* Note: Ad may be styled slightly differently on live site.</div>';
	}
	else
	{
		echo '
		<div style="background-color:#cccccc; border:dashed 1px #333333; margin-bottom:22px; text-align:center; vertical-align:middle; line-height:' . $struct['height'] . 'px; width:' . $struct['width'] . 'px; height:' . $struct['height'] . 'px;">' . $struct['width'] . ' x ' . $struct['height'] . ' Text Box</div>';
	}
}

// Your Ad Image / Text
echo '
<p>
	<strong>The URL that your Ad directs to:</strong><br />
	<input type="text" name="ad_url" value="' . $_POST['ad_url'] . '" style="width:95%" />
</p>';

// If this ad requires an image, provide the form for it here
if($struct['type'] == "image")
{
	echo '
	<p>
		<strong>Upload a new image for your ad:</strong><br />
		<input type="file" name="image" value="" tabindex="30" />
	</p>';
}

// If this ad requires title text, set the option here
if($struct['require']['title'])
{
	echo '
	<p>
		<strong>Your Ad Title Text:</strong><br />
		<input type="text" name="ad_title" value="' . $_POST['ad_title'] . '" style="width:95%" maxlength="' . $struct['title_len'] . '" />
	</p>';
}

// If this ad requires body text, set the option here
if($struct['require']['body'])
{
	echo '
	<p>
		<strong>Your Ad Body Text:</strong><br />
		<input type="text" name="ad_body" value="' . $_POST['ad_body'] . '" style="width:95%" maxlength="' . $struct['body_len'] . '" />
	</p>';
}

// If this ad is live, announce it here
if($adData['is_live'])
{
	echo '
	<p>
		<strong style="color:green;">Your Ad is currently LIVE</strong><br />
		<input type="submit" name="set_inactive" value="Make This Ad INACTIVE" />
	</p>';
}

// If this ad isn't live, display it here
else
{
	echo '
	<p>
		<strong style="color:#ee0000;">Your Ad is currently INACTIVE</strong><br />
		<input type="submit" name="set_live" value="Make This Ad LIVE" />
	</p>';
}

// Display the details of this advertisement
echo '
<div style="background-color:#eeeeee; padding:12px; margin-bottom:22px;">
	<h3>Details of this Advertisement</h3>
	<div style="margin-bottom:22px;">
		<strong>The Ad Details:</strong>
		<div style="margin-top:0px; width:95%; background-color:white; padding:6px 8px 6px 8px; border:solid 1px #bbbbbb;">' . $struct['desc'] . '</div>
	</div>
	<p>
		<strong>Source URL used to locate this advertisement:</strong><br />
		<input type="text" name="url" value="' . $adData['reference_url'] . '" style="width:95%" readonly />
	</p>
	<p>
		<strong>Site that this advertisement will run on:</strong><br />
		<input type="text" name="site" value="' . $adData['site_handle'] . '" style="width:95%" readonly />
	</p>
	<p>
		<strong>Specific targeting keyword for the site:</strong><br />
		<input type="text" name="site_targ_key" value="' . $adData['keyword'] . '" style="width:95%" readonly />
	</p>
	<p>
		<strong>The Ad Zone being promoted on:</strong><br />
		<input type="text" name="zone" value="' . $adData['zone'] . '" style="width:95%" readonly />
	</p>
</div>
<p>
	<strong>Advertisement Name:</strong><br />
	<input type="text" name="name" value="' . $_POST['name'] . '" style="width:95%" />
</p>';

// Your Advertising Bids
echo '
<p>
	<strong>Your maximum bid for sponsored traffic:</strong><br />
	$ <input type="text" name="bid_all" value="' . $_POST['bid_all'] . '" style="width:70px;" placeholder="' . $bid['all'] . '" /> per 1000 views
</p>
<p>
	<strong>Your maximum bid for targeting sponsored users (<a href="/faq/audiences#premiumbid">Learn More</a>):</strong><br />
	$ <input type="text" name="bid_user" value="' . $_POST['bid_user'] . '" style="width:70px;" placeholder="' . $bid['user'] . '" /> per 1000 views
</p>
<p>
	<strong>Your maximum bid for premium traffic (<a href="/faq/audiences#premiumbid">Learn More</a>):</strong><br />
	$ <input type="text" name="bid_premium" value="' . $_POST['bid_premium'] . '" style="width:70px;" placeholder="' . $bid['premium'] . '" /> per 1000 views
</p>
<p>
	<input type="submit" name="submit" value="Update Ad" />
</p>
</form>';

echo '
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/footer.php");