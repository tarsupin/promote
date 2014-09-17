<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); }

// Check the link
if($linkData = Link::getData("give-credits"))
{
	// Load User
	if($userData = User::get($linkData[0]))
	{
		// Add credits to the user's account
		if(Database::query("UPDATE users SET credits=credits+? WHERE uni_id=? LIMIT 1", array($linkData[1], $userData['uni_id'])))
		{
			Alert::saveSuccess("Credits Granted", "You have successfully sent $" . $linkData[1] . " to " . $userData['display_name'] . ".");
			
			header("Location: /admin"); exit;
		}
	}
}

// Prepare Values
$_POST['handle'] = isset($_POST['handle']) ? Sanitize::variable($_POST['handle']) : '';
$_POST['credits'] = isset($_POST['credits']) ? number_format($_POST['credits'], 2) : 0.00;

// Run Header
require(SYS_PATH . "/controller/includes/admin_header.php");

// Display the user's credits (if available)
if($_POST['handle'])
{
	$uniID = User::getIDByHandle($_POST['handle']);
	
	if($userData = Database::selectOne("SELECT credits, handle, display_name FROM users WHERE uni_id=? LIMIT 1", array($uniID)))
	{
		echo '
		<div style="margin-bottom:22px;">
			<h3>Are you sure you want to give $' . $_POST['credits'] . ' to ' . $userData['display_name'] . ' (@' . $userData['handle'] . ')?</h3>
			<div>' . $userData['display_name'] . ' currently has $' . $userData['credits'] . ' in their account.</div>
			<div><a class="button" href="/admin/AdCore/Give Ad Credits?' . Link::prepareData("give-credits", $uniID, $_POST['credits']) . '">Confirm Free Ad Credits</a></div>
		</div>';
	}
}

// Display Category Table
echo '
<div style="margin-bottom:22px; font-size:0.85em;">
<h3>Give Credits</h3>
<form class="uniform" action="/admin/AdCore/Give Ad Credits" method="post">' . Form::prepare('ad-credits-core') . '
	<p>User Handle: <input type="text" name="handle" value="' . $_POST['handle'] . '" /></p>
	<p>Credits: $ <input type="text" name="credits" value="' . $_POST['credits'] . '" /></p>
	<p><input type="submit" name="submit" value="Give Credits to User" /></p>
</form>
</div>';

// Display the Footer
require(SYS_PATH . "/controller/includes/admin_footer.php");