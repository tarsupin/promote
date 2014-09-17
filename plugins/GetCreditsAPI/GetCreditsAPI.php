<?php if(!defined("CONF_PATH")) { die("No direct script access allowed."); } /*

----------------------------------------
------ About this Transaction API ------
----------------------------------------

This API is used by any site that is trying to coordinate a transaction with the UniJoule site.


-------------------------------------
------ The Transaction Process ------
-------------------------------------

1. The first step in a transaction begins when a site needs to process a UniJoule payment for something with the server:

2. The site prepares information to send to the UniJoule site, which includes:
	$uniID		The UniID of the person who will be paying UniJoule (and receiving something on the site).
	$title		The title of the transaction.
	$desc		The description of what is being purchased or exchanged.
	$api		The API to respond to (so that the site can properly record the results of the payment).
	$returnURL	The URL to return to.
	$imageURL	An image to show for the transaction, if desired (is optional).
	$minAmount	The minimum number of UniJoule for the transaction.
	$maxAmount	The maximum number of UniJoule for the transaction.
	$defAmount	The default number of UniJoule for the transaction.
	$opts		The dropdown list of options you can select from, if there are fixed amounts (through a string-key array).
	$custom		Any custom data to apply so that you can confirm what is being purchased when returned.
	
Note that if the "minimum" and "maximum" amounts are equal (and above 0.00), then it will set a fixed amount.

The final call will look something like this:	
	
	$url = Credits::transactionURL($uniID, $title, $desc, $api, $returnURL, $imageURL, $minAmount, $maxAmount, $defAmount, $opts, $custom);

3. Once the URL has been acquired, the user is redirected to that URL.
	
	Example: http://unijoule.com/transaction/amount?d={DATA}&slg={UNI_ID}
	
4. The user submits the amount of UniJoule that they want to spend on the transaction.

5. Once the user submits the transaction, this API interprets the data that was sent, and uses it to process the intended changes on this site.

	See "Calling this API" for more details on how the transaction page works.

-------------------------------------------------
------ Example of Setting up a Transaction ------
-------------------------------------------------

// Prepare the necessary values
$uniID = Me::$id;
$title = "Get Ad Credits";
$desc = "This purchase will exchange UniJoule for Ad Credits.";
$api = "GetCreditsAPI";
$returnURL = SITE_URL . "/get-credits";
$haveFee = false;
$imageURL = "";
$minAmount = 0.00;
$maxAmount = 0.00;
$defAmount = 0.00;
$opts = array();
$custom = array("campaign_id" => $campaignID);

// Retrieve the URL to run this Transaction
$url = Credits::transactionURL(
		$uniID				// <int> The UniID of the user to setup the transaction for.
	,	$title				// <str> The title of the transaction.
	,	$desc				// <str> The description of what this particular transaction is purchasing.
	,	$api				// <str> The API that the transaction will submit back to.
	,	$returnURL			// <str> The URL to return to after the transaction is completed.
	,	$haveFee			// <bool> TRUE if there is a standard fee for this transaction, FALSE if not.
	,	$imageURL			// <str> The image to show for the transaction, if wanted.
	,	$minAmount			// <float> The minimum amount that can be spent on the transaction.
	,	$maxAmount			// <float> The maximum amount that can be spent on the transaction (0.00 is unlimited).
	,	$defAmount			// <float> The default amount for this transaction (0.00 is no default).
	,	$opts				// <int:float> An array of default values that you can select between.
	,	$custom				// <str:mixed> A string array of custom data to return back.
);

// Load the Transaction
header("Location: " . $url); exit;


---------------------------------------------------
------ The API Response by the UniJoule Site ------
---------------------------------------------------
Once the Transaction has been submitted on the UniJoule site, it will call the API that you designated with the $api value. When it does, it will return an API call like this:
	
	
	// Prepare the Transaction Packet
	// Note that the "custom" field is used to track data between the exchanges.
	$packet = array(
		"uni_id"			=> $user['uni_id']		// UniID of the person purchasing something
	,	"amount"			=> 12.34				// Amount of UniJoule that was confirmed in the exchange
	,	"custom"			=> array()				// Custom data that was sent with the transaction
	);
	
	// Run the origin site's API
	$response = Connect::to($siteHandle, $designatedAPI, $packet);
	
	
[ Possible Responses ]
	TRUE if the API processed as expected.
	FALSE on failure.
	
*/

class GetCreditsAPI extends API {
	
	
/****** API Variables ******/
	public $isPrivate = true;			// <bool> TRUE if this API is private (requires an API Key), FALSE if not.
	public $encryptType = "";			// <str> The encryption algorithm to use for response, or "" for no encryption.
	public $allowedSites = array("unijoule");		// <int:str> the sites to allow the API to connect with. Default is all sites.
	public $microCredits = 1000;		// <int> The cost in microcredits (1/10000 of a credit) to access this API.
	public $minClearance = 8;			// <int> The minimum clearance level required to use this API.
	
	
/****** Run the API ******/
	public function runAPI (
	)					// RETURNS <bool> TRUE if the exchange passed successfully, FALSE on failure.
	
	// $this->runAPI()
	{
		// Make sure the appropriate values were provided
		if(!isset($this->data['uni_id']) or !isset($this->data['amount']))
		{
			return false;
		}
		
		// Custom Data
		// $customData = isset($this->data['custom']) ? $this->data['custom'] : array();
		
		// Run all desired tasks based on the UniJoule amount and custom data provided
		
		// Add credits to the user's account
		return AdCore::addCredits((int) $this->data['uni_id'], (float) $this->data['amount']);
	}
	
}
