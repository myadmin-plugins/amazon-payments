<?php
	/**
	* Amazon Functionality
	*
	* Last Changed: $LastChangedDate: 2016-07-22 08:30:09 -0400 (Fri, 22 Jul 2016) $
	* @author detain
	* @copyright 2017
	* @package MyAdmin
	* @category Billing
	*/

	function amazon_obtain_profile() {
		$c = curl_init('https://api.amazon.com/auth/o2/tokeninfo?access_token='.urlencode($_REQUEST['access_token']));
		curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
		$r = curl_exec($c);
		curl_close($c);
		$d = json_decode($r);
		if ($d->aud != 'YOUR-CLIENT-ID') {
			// the access token does not belong to us
			header('HTTP/1.1 404 Not Found');
			echo 'Page not found';
			exit;
		}
		// exchange the access token for user profile
		$c = curl_init(AMAZON_SANDBOX_PROFILE_URL);
		curl_setopt($c, CURLOPT_HTTPHEADER, ['Authorization: bearer '. $_REQUEST['access_token']]);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, TRUE);
		$r = curl_exec($c);
		curl_close($c);
		$d = json_decode($r);
		echo sprintf('%s %s %s', $d->name, $d->email, $d->user_id);
	}

	/**
	 * @return string
	 */
	function amazon_addressbook_widget() {
		return '<div id="addressBookWidgetDiv">
</div>
<script>
new OffAmazonPayments.Widgets.AddressBook({
	sellerId: "'.AMAZON_SELLER_ID.'",
	amazonOrderReferenceId: amazonOrderReferenceId,
		// amazonOrderReferenceId obtained from Button widget
	onAddressSelect: function(orderReference) {
		// Replace the following code with the action that you want to perform
		// after the address is selected.
		// The amazonOrderReferenceId can be used to retrieve
		// the address details by calling the GetOrderReferenceDetails
		// operation. If rendering the AddressBook and Wallet widgets on the
		// same page, you should wait for this event before you render the
		// Wallet widget for the first time.
	},
	design: {
		size : {width:"400px", height:"260px"}
	},
	onError: function(error) {
		// your error handling code
	}
}).bind("addressBookWidgetDiv");
</script>
';
	}

	/**
	 * @return string
	 */
	function amazon_wallet_widget() {
		return '<div id="walletWidgetDiv">
</div>
<script>
new OffAmazonPayments.Widgets.Wallet({
	sellerId: "'.AMAZON_SELLER_ID.'",
	amazonOrderReferenceId: amazonOrderReferenceId,
	// amazonOrderReferenceId obtained from Button widget
	design: {
		size : {width:"400px", height:"260px"}
		},
		onPaymentSelect: function(orderReference) {
			// Replace this code with the action that you want to perform
			// after the payment method is selected.
		},
		onError: function(error) {
			// your error handling code
		}
}).bind("walletWidgetDiv");
</script>
';
	}
