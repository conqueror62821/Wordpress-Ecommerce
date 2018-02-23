<?php
/**
	* WP eCommerce Nochex Payment Module
	* @author Nochex Ltd
	* @version 2.1
 	* @package wp-e-commerce
 	* @subpackage wpsc-merchants
*/
$nzshpcrt_gateways[$num] = array(
	'name' => 'Nochex Secure Online Payments',
	'api_version' => 2.1,
	'image' => 'https://ssl.nochex.com/images/carts/paynochex.gif',
	'class_name' => 'wpsc_merchant_nochex_standard',
	'wp_admin_cannot_cancel' => true,
	'display_name' => 'Nochex',
	'requirements' => array(
		/// so that you can restrict merchant modules to PHP 5, if you use PHP 5 features
		'php_version' => 4.3,
		 /// for modules that may not be present, like curl
		'extra_modules' => array()
	),

	// this may be legacy, not yet decided
	'internalname' => 'wpsc_merchant_nochex_standard',

	// All array members below here are legacy, and use the code in nochex_multiple.php
	'form' => 'form_nochex_multiple',
	'submit_function' => 'submit_nochex_multiple',
	'payment_type' => 'nochex',
	'supported_currencies' => array(
		'currency_list' =>  array('AUD', 'BRL', 'CAD', 'CHF', 'CZK', 'DKK', 'EUR', 'GBP', 'HKD', 'HUF', 'ILS', 'JPY', 'MXN', 'MYR', 'NOK', 'NZD', 'PHP', 'PLN', 'SEK', 'SGD', 'THB', 'TWD', 'USD'),
		'option_name' => 'nochex_curcode'
	)
);



/**
	* WP eCommerce Nochex Standard Merchant Class
	*
	* This is the nochex standard merchant class, it extends the base merchant class
	*
	* @package wp-e-commerce
	* @since 3.7.6
	* @subpackage wpsc-merchants
*/
class wpsc_merchant_nochex_standard extends wpsc_merchant {
  var $name = '';
  var $nochex_apc_values = array();
  
	/**
	* construct value array method, converts the data gathered by the base class code to something acceptable to the gateway
	* @access public
	*/
	function construct_value_array() {
		$this->collected_gateway_data = $this->_construct_value_array();
	}

	function convert( $amt ){
		if ( empty( $this->rate ) ) {
			$this->rate = 1;
			$nochex_currency_code = $this->get_nochex_currency_code();
			$local_currency_code = $this->get_local_currency_code();
			if( $local_currency_code != $nochex_currency_code ) {
				$curr=new CURRENCYCONVERTER();
				$this->rate = $curr->convert( 1, $nochex_currency_code, $local_currency_code );
			}
		}
		return $this->format_price( $amt * $this->rate );
	}

	function get_local_currency_code() {
		if ( empty( $this->local_currency_code ) ) {
			global $wpdb;
			$this->local_currency_code = $wpdb->get_var( $wpdb->prepare( "SELECT `code` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id` = %d  LIMIT 1", get_option( 'currency_type' ) ) );
		}

		return $this->local_currency_code;
	}

	function get_nochex_currency_code() {
		if ( empty( $this->nochex_currency_code ) ) {
			global $wpsc_gateways;
			$this->nochex_currency_code = $this->get_local_currency_code();

			if ( ! in_array( $this->nochex_currency_code, $wpsc_gateways['wpsc_merchant_nochex_standard']['supported_currencies']['currency_list'] ) )
				$this->nochex_currency_code = get_option( 'nochex_curcode', 'USD' );
		}

		return $this->nochex_currency_code;
	}

	/**
	* construct value array method, converts the data gathered by the base class code to something acceptable to the gateway
	* @access private
	* @param boolean $aggregate Whether to aggregate the cart data or not. Defaults to false.
	* @return array $nochex_vars The nochex vars
	*/
	function _construct_value_array() {
		global $wpdb;
		$nochex_vars = array();
		$add_tax = ! wpsc_tax_isincluded();

		
		$notify_url = $this->cart_data['notification_url'];
			$notify_url = add_query_arg('gateway', 'wpsc_merchant_nochex_standard', $notify_url);
			$notify_url = apply_filters('wpsc_nochex_standard_notify_url', $notify_url);
	/*			
	

		
		$nochex_billing = get_option('nochex_billing');
		
		
		// APC data
			
			
			$callback_url = $notify_url;

	
			$free_shipping = false;
			if ( isset( $_SESSION['coupon_numbers'] ) ) {
				$coupon = new wpsc_coupons( $_SESSION['coupon_numbers'] );
				$free_shipping = $coupon->is_percentage == '2';
			}

			if ( $this->cart_data['has_discounts'] && $free_shipping ){
				$handling = 0;
			}else{
			
				$handling = $this->cart_data['base_shipping'];
			}
			
			$nochex_postage = get_option('nochex_postage');
			
			// Stick the cart item values together here
			//$i = 1;
			
			$nochex_description = get_option('nochex_xml');
		
			if ($nochex_description == 1) {
			$xmlCollect = '<items>';
			
			foreach ($this->cart_items as $cart_row) {
				$xmlCollect .= '<item><id>'. $cart_row['product_id'] .'</id><name>'. $cart_row['name'] .'</name><description>'. $cart_row['name']  .'</description><quantity>'. $cart_row['quantity'] .'</quantity><price>'. $this->convert($cart_row['price']) .'</price></item>';
				
			}
			
			$xmlCollect .= '</items>';
			}*/
			
			if(get_option('nochex_test') == 1){
			$nochex_test = "100";
			}else{
			$nochex_test = "";
			}
			
			if ($nochex_billing == 1) {
			$hide_billing_details = 'true';			
			}else{
			$hide_billing_details = 'false';
			}
			
			
			$free_shipping = false;
			if ( isset( $_SESSION['coupon_numbers'] ) ) {
				$coupon = new wpsc_coupons( $_SESSION['coupon_numbers'] );
				$free_shipping = $coupon->is_percentage == '2';
			}

			if ( $this->cart_data['has_discounts'] && $free_shipping ){
				$handling = 0;
			}else{
			
				$handling = $this->cart_data['base_shipping'];
			}
			
			if($nochex_postage == 1){
			// Set base shipping
			$postage = $handling;
			$amount = $this->cart_data['total_price'] - $handling;
			
			}else{
			$amount = $this->cart_data['total_price'];
			}
			
			
			$description = "";
			$xmlCollection = "<items>";
			
			
			foreach ($this->cart_items as $cart_row) {
				$xmlCollection .= '<item><id>'. $cart_row['product_id'] .'</id><name>'. $cart_row['name'] .'</name><description>'. $cart_row['name']  .'</description><quantity>'. $cart_row['quantity'] .'</quantity><price>'. $this->convert($cart_row['price']) .'</price></item>';
				$description .= "Product: " . $cart_row['name'] . ", Quantity: " . $cart_row['quantity'] . ", Amount:" . $this->convert($cart_row['price']);
			}
			$description .= "";
			$xmlCollection .= '</items>';
			
			if (get_option('nochex_xml') == 1) {
			
			$description = "Order created for: ".$this->cart_data['session_id'];
			
			}else{
			
			$xmlCollection = "";
			
			}
			
			return '<form action="https://secure.nochex.com/default.aspx" method="post" id="nochex_payment_form">
				<input type="hidden" name="merchant_id" value="'.get_option('nochex_merchant_id').'" />
				<input type="hidden" name="amount" value="'.number_format($amount, 2, '.', '').'" />
				<input type="hidden" name="postage" value="'.number_format($postage, 2, '.', '').'" />
				<input type="hidden" name="description" value="'.$description .'" />
				<input type="hidden" name="xml_item_collection" value="'.$xmlCollection.'" />
				<input type="hidden" name="order_id" value="'.$this->cart_data['session_id'].'" />
				<input type="hidden" name="billing_fullname" value="'.$this->cart_data['billing_address']['first_name'].' '.$this->cart_data['billing_address']['last_name'].'" />
				<input type="hidden" name="billing_address" value="'.$this->cart_data['billing_address']['address'].'" />
				<input type="hidden" name="billing_city" value="'.$this->cart_data['billing_address']['city'].'" />
				<input type="hidden" name="billing_postcode" value="'.$this->cart_data['billing_address']['post_code'].'" />
				<input type="hidden" name="delivery_fullname" value="'.$this->cart_data['shipping_address']['first_name'].' '.$this->cart_data['shipping_address']['last_name'].'" />
				<input type="hidden" name="delivery_address" value="'.$this->cart_data['shipping_address']['address'].'" />
				<input type="hidden" name="delivery_city" value="'.$this->cart_data['shipping_address']['city'].'" />
				<input type="hidden" name="delivery_postcode" value="'.$this->cart_data['shipping_address']['post_code'].'" />
				<input type="hidden" name="email_address" value="'.$this->cart_data['email_address'].'" />
				<input type="hidden" name="customer_phone_number" value="'.$this->cart_data['billing_address']['phone'].'" />				
				<input type="hidden" name="success_url" value="'.add_query_arg('sessionid', $this->cart_data['session_id'], $this->cart_data['transaction_results_url']).'" />
				<input type="hidden" name="callback_url" value="'.$notify_url.'" />				
				<input type="hidden" name="cancel_url" value="'.$this->cart_data['transaction_results_url'].'" />
				<input type="hidden" name="test_transaction" value="'.$nochex_test.'" />
				<input type="hidden" name="hide_billing_details" value="'.$hide_billing_details.'" />
				<input type="hidden" name="test_success_url" value="'.add_query_arg('sessionid', $this->cart_data['session_id'], $this->cart_data['transaction_results_url']).'" />
				<input type="submit" class="button-alt" id="submit_nochex_payment_form" value="Continue to Payment" /> 
				</form> 
			<script type="text/javascript">
		
					document.getElementById("nochex_payment_form").submit();
			</script>';
		
	
		
		
		 
	}

	/**
	* submit method, sends the received data to the payment gateway
	* @access public
	*/
	function submit() {
		/*$name_value_pairs = array();
		foreach ($this->collected_gateway_data as $key => $value) {
			$name_value_pairs[] = $key . '=' . urlencode($value);
		}
		$gateway_values =  implode('&', $name_value_pairs);

		$redirect = "https://secure.nochex.com/?".$gateway_values;
		// URLs up to 2083 characters long are short enough for an HTTP GET in all browsers.
		// Longer URLs require us to send aggregate cart data to Nochex short of losing data.
		if (strlen($redirect) > 2083) {
			$name_value_pairs = array();
			foreach($this->_construct_value_array(true) as $key => $value) {
				$name_value_pairs[]= $key . '=' . urlencode($value);
			}
			$gateway_values =  implode('&', $name_value_pairs);

			$redirect = "https://secure.nochex.com/?".$gateway_values;
		}*/
		
		
		 echo $this->_construct_value_array();
		
	
	}


	/**
	* parse_gateway_notification method, receives data from the payment gateway
	* @access private
	*/
	function parse_gateway_notification() {
		/// Nochex first expects the APC variables to be returned to it within 30 seconds, so we do this first.
		$nochex_url = "https://www.nochex.com/apcnet/apc.aspx";
		$received_values = array();
  		$received_values += stripslashes_deep ($_POST);
		
		$postvars = http_build_query($_POST);
		// Curl code to post variables back
		$ch = curl_init(); // Initialise the curl tranfer
curl_setopt($ch, CURLOPT_URL, $nochex_url);
curl_setopt($ch, CURLOPT_VERBOSE, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postvars); // Set POST fields
curl_setopt($ch, CURLOPT_HTTPHEADER, "Host: www.nochex.com");
curl_setopt($ch, CURLOPT_POSTFIELDSIZE, 0); 
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 60); // set connection time out variable - 60 seconds	
$response = curl_exec($ch); // Post back
curl_close($ch);
		
		$received_values['response'] = $response;
		
		$debug_filename = dirname(__FILE__). "/nochex_debug.txt";
		$debug_mode = get_option('nochex_debug');
		switch($debug_mode){
		// Off
		case 0:
			default:
			break;
		
		// Log File
		case 1:
			$msg = date('l jS F Y g:ia') . " ==> APC " . $response . ", Return Values:" . "\n";
			foreach ($received_values as $key => $value) {
				$msg .= "[" . $key . "] = "  . $value . "\n";
			}
			$msg .= "\n";
			file_put_contents($debug_filename, $msg, FILE_APPEND);
			break;
		}
		
		if( 'AUTHORISED' == $response ) {
			$this->nochex_apc_values = $received_values;
			$this->session_id = $received_values['order_id'];

		} else {
			exit("APC Request Failure");
		}
		
	}

	/**
	* process_gateway_notification method, receives data from the payment gateway
	* @access public
	*/
	function process_gateway_notification() {
		$status = false;
		switch ( $this->nochex_apc_values['response'] ) {
			case 'AUTHORISED':
				$status = 3;
				break;
			case 'DECLINED':
				$status = 6;
				break;
		}

		do_action( 'wpsc_nochex_standard_apc', $this->nochex_apc_values, $this );
		$nochex_email = strtolower( get_option( 'nochex_merchant_id' ) );
		if ( $status )
		$this->set_transaction_details( $this->nochex_apc_values['order_id'], $status );
		if ( in_array( $status, array( 2, 3 ) ) )
		transaction_results($this->cart_data['session_id'],false);

	}



	function format_price($price, $nochex_currency_code = null) {
		if (!isset($nochex_currency_code)) {
			$nochex_currency_code = get_option('nochex_curcode');
		}
		switch($nochex_currency_code) {
			case "JPY":
			$decimal_places = 0;
			break;

			case "HUF":
			$decimal_places = 0;

			default:
			$decimal_places = 2;
			break;
		}
		$price = number_format(sprintf("%01.2f",$price),$decimal_places,'.','');
		return $price;
	}
}


/**
 * submit_nochex_multiple function.
 *
 * Use this for now, but it will eventually be replaced with a better form API for gateways
 * @access public
 * @return void
 */
function submit_nochex_multiple(){
  if(isset($_POST['nochex_merchant_id'])) {
    update_option('nochex_merchant_id', $_POST['nochex_merchant_id']);
	}

  if(isset($_POST['nochex_curcode'])) {
    update_option('nochex_curcode', $_POST['nochex_curcode']);
	}
	
  if(isset($_POST['nochex_billing'])) {
    update_option('nochex_billing', (int)$_POST['nochex_billing']);
	}

  if(isset($_POST['nochex_test'])) {
    update_option('nochex_test', (int)$_POST['nochex_test']);
	}
	
  if(isset($_POST['nochex_order_status'])) {
    update_option('nochex_order_status', $_POST['nochex_order_status']);
	}

  if(isset($_POST['nochex_xml'])) {
    update_option('nochex_xml', $_POST['nochex_xml']);
	}
	
	  if(isset($_POST['nochex_postage'])) {
    update_option('nochex_postage', $_POST['nochex_postage']);
	}

  if(isset($_POST['nochex_debug'])) {
    update_option('nochex_debug', (int)$_POST['nochex_debug']);
	// Create debug file if one does not exist - <wordpress_dir>/wp-content/wp-e-commerce/wpsc-merchants/nochex_debug.txt
	$debug_filename = dirname(__FILE__). "/nochex_debug.txt";
	if ((int)$_POST['nochex_debug'] == 1)
	{
		if (!file_exists($debug_filename))
			{
			$debug_handle = fopen($debug_filename, 'w') or die("can't open file");
			fclose($debug_handle);
			file_put_contents($debug_filename, "Nochex Log File\n", FILE_APPEND);
			}
	}
	}
	
  if (!isset($_POST['nochex_form'])) $_POST['nochex_form'] = array();
  foreach((array)$_POST['nochex_form'] as $form => $value) {
    update_option(('nochex_form_'.$form), $value);
  }

  return true;
}



/**
 * form_nochex_multiple function.
 *
 * Use this for now, but it will eventually be replaced with a better form API for gateways
 * @access public
 * @return void
 */
function form_nochex_multiple() {
  global $wpdb, $wpsc_gateways, $wpsc_purchlog_statuses;


  $output = "
  <tr><td colspan='2'><br /></td></tr>
  <tr>
      <td width='150'>" . __( 'Merchant ID / Email Address:', 'wpsc' ) . "
      </td>
      <td>
      <input type='text' size='40' value='".get_option('nochex_merchant_id')."' name='nochex_merchant_id' />
  	<br /><small>
  	" . __( 'This is your Nochex email address.', 'wpsc' ) . "
  	</small>
  	</td>
  </tr>
  <tr><td colspan='2'><br /></td></tr>";

	$nochex_order = get_option('nochex_order_status');
	$statuses = '';
	for ($i = 0; $i <= count($wpsc_purchlog_statuses); $i++){
		if ($nochex_order == '') {
			if ($i == 2) {
				$statuses .= "<option selected value='" . $wpsc_purchlog_statuses[$i]['order'] . "'>" . $wpsc_purchlog_statuses[$i]['label'] . "</option>";
			}
			else
			{
				$statuses .= "<option value='" . $wpsc_purchlog_statuses[$i]['order'] . "'>" . $wpsc_purchlog_statuses[$i]['label'] . "</option>";
			}
		}
		else
		{
			if ($i+1 == $nochex_order){
				$statuses .= "<option selected value='" . $wpsc_purchlog_statuses[$i]['order'] . "'>" . $wpsc_purchlog_statuses[$i]['label'] . "</option>";
			}
			else
			{
				$statuses .= "<option value='" . $wpsc_purchlog_statuses[$i]['order'] . "'>" . $wpsc_purchlog_statuses[$i]['label'] . "</option>";
			}
		}
	}
	

	$output .= "
	<tr>
     <td style='padding-bottom: 0px;'>Order Status Success:
     </td>
     <td style='padding-bottom: 0px;'>
	  <select name='nochex_order_status' style='width:150px'>
      ".$statuses."
      </select>
	<br />
  	<small>Select the order status once an order has been paid for.</small>
  	</td>
  </tr>
  <tr><td colspan='2'><br /></td></tr>";
  
  
	$nochex_billing = get_option('nochex_billing');
	$nochex_billing1 = "";
	$nochex_billing2 = "";
	switch($nochex_billing){
		case 1:
		$nochex_billing1 = "checked='checked'";
		break;

		case 0:
		default:
		$nochex_billing2 = "checked='checked'";
		break;

	}
	
	$output .= "
  <tr>
     <td style='padding-bottom: 0px;'>Hide Billing Details:
     </td>
     <td style='padding-bottom: 0px;'>
       <input type='radio' value='1' name='nochex_billing' id='nochex_billing1' ".$nochex_billing1." /> <label for='nochex_billing1'>".__('Yes', 'wpsc')."</label> &nbsp;
       <input type='radio' value='0' name='nochex_billing' id='nochex_billing2' ".$nochex_billing2." /> <label for='nochex_billing2'>".__('No', 'wpsc')."</label>
	<br />
  	<small>Billing details will be hidden when a customer is sent to Nochex to pay.</small>
  	</td>
  </tr>
  <tr><td colspan='2'><br /></td></tr>";
  
  
	$nochex_postage = get_option('nochex_postage');
	$nochex_postage1 = "";
	$nochex_postage2 = "";
	switch($nochex_postage){
		case 1:
		$nochex_postage1 = "checked='checked'";
		break;

		case 0:
		default:
		$nochex_postage2 = "checked='checked'";
		break;

	}
	
	$output .= "
  <tr>
     <td style='padding-bottom: 0px;'>Postage:
     </td>
     <td style='padding-bottom: 0px;'>
       <input type='radio' value='1' name='nochex_postage' id='nochex_postage1' ".$nochex_postage1." /> <label for='nochex_postage1'>".__('Yes', 'wpsc')."</label> &nbsp;
       <input type='radio' value='0' name='nochex_postage' id='nochex_postage2' ".$nochex_postage2." /> <label for='nochex_postage2'>".__('No', 'wpsc')."</label>
	<br />
  	<small>Display Postage on your Nochex Payment Page.</small>
  	</td>
  </tr>
  <tr><td colspan='2'><br /></td></tr>";
  
	$nochex_xml = get_option('nochex_xml');
	$nochex_xml1 = "";
	$nochex_xml2 = "";
	switch($nochex_xml){
		case 1:
		$nochex_xml1 = "checked='checked'";
		break;

		case 0:
		default:
		$nochex_xml2 = "checked='checked'";
		break;

	}
	
	$output .= "
  <tr>
     <td style='padding-bottom: 0px;'>Product Details Information:
     </td>
     <td style='padding-bottom: 0px;'>
       <input type='radio' value='1' name='nochex_xml' id='nochex_xml1' ".$nochex_xml1." /> <label for='nochex_xml1'>".__('Yes', 'wpsc')."</label> &nbsp;
       <input type='radio' value='0' name='nochex_xml' id='nochex_xml2' ".$nochex_xml2." /> <label for='nochex_xml2'>".__('No', 'wpsc')."</label>
	<br />
  	<small>Display detailed product information on your Nochex payment page.</small>
  	</td>
  </tr>
  <tr><td colspan='2'><br /></td></tr>";
  
  
	$nochex_test = get_option('nochex_test');
	$nochex_test1 = "";
	$nochex_test2 = "";
	switch($nochex_test){
		case 1:
		$nochex_test1 = "checked='checked'";
		break;

		case 0:
		default:
		$nochex_test2 = "checked='checked'";
		break;

	}
	
	$output .= "
  
  
  
  <tr>
     <td style='padding-bottom: 0px;'>Test Mode:
     </td>
     <td style='padding-bottom: 0px;'>
       <input type='radio' value='1' name='nochex_test' id='nochex_test1' ".$nochex_test1." /> <label for='nochex_test1'>".__('Yes', 'wpsc')."</label> &nbsp;
       <input type='radio' value='0' name='nochex_test' id='nochex_test2' ".$nochex_test2." /> <label for='nochex_test2'>".__('No', 'wpsc')."</label>
	<br />
  	<small>No real money will be received when in test mode.</small>
  	</td>
  </tr>
  <tr><td colspan='2'><br /></td></tr>";
  
  
  $nochex_debug = get_option('nochex_debug');
	$nochex_debug1 = "";
	$nochex_debug2 = "";
	switch($nochex_debug){
		case 1:
		$nochex_debug2 = "checked='checked'";
		break;

		case 0:
		default:
		$nochex_debug1 = "checked='checked'";
		break;

	}
	
	$output .= "
  <tr>
     <td style='padding-bottom: 0px;'>Debug Mode:
     </td>
     <td style='padding-bottom: 0px;'>
	   <input type='radio' value='1' name='nochex_debug' id='nochex_debug2' ".$nochex_debug2." /> <label for='nochex_debug2'>".__('Yes', 'wpsc')."</label> &nbsp;
	   <input type='radio' value='0' name='nochex_debug' id='nochex_debug1' ".$nochex_debug1." /> <label for='nochex_debug1'>".__('No', 'wpsc')."</label>
	<br />
  	<small>If debugging on, log file location: <wordpress_dir>/wp-content/wp-e-commerce/wpsc-merchants/nochex_debug.txt.</small>
  	</td>
  </tr>\n";




	$store_currency_data = $wpdb->get_row( $wpdb->prepare( "SELECT `code`, `currency` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `id` IN (%d)", get_option( 'currency_type' ) ), ARRAY_A);
	$current_currency = get_option('nochex_curcode');
	if(($current_currency == '') && in_array($store_currency_data['code'], $wpsc_gateways['wpsc_merchant_nochex_standard']['supported_currencies']['currency_list'])) {
		update_option('nochex_curcode', $store_currency_data['code']);
		$current_currency = $store_currency_data['code'];
	}

	if($current_currency != $store_currency_data['code']) {
		$output .= "
  <tr>
      <td colspan='2'><strong class='form_group'>" . __( 'Currency Converter', 'wpsc' ) . "</td>
  </tr>
  <tr>
		<td colspan='2'>".sprintf(__('Your website uses <strong>%s</strong>. This currency is not supported by Nochex, please  select a currency using the drop down menu below. Buyers on your site will still pay in your local currency however we will send the order through to Nochex using the currency you choose below.', 'wpsc'), $store_currency_data['currency'])."</td>
		</tr>\n";

		$output .= "    <tr>\n";



		$output .= "    <td>Select Currency:</td>\n";
		$output .= "          <td>\n";
		$output .= "            <select name='nochex_curcode'>\n";

		$nochex_currency_list = array_map( 'esc_sql', $wpsc_gateways['wpsc_merchant_nochex_standard']['supported_currencies']['currency_list'] );

		$currency_list = $wpdb->get_results("SELECT DISTINCT `code`, `currency` FROM `".WPSC_TABLE_CURRENCY_LIST."` WHERE `code` IN ('".implode("','",$nochex_currency_list)."')", ARRAY_A);

		foreach($currency_list as $currency_item) {
			$selected_currency = '';
			if($current_currency == $currency_item['code']) {
				$selected_currency = "selected='selected'";
			}
			$output .= "<option ".$selected_currency." value='{$currency_item['code']}'>{$currency_item['currency']}</option>";
		}
		$output .= "            </select> \n";
		$output .= "          </td>\n";
		$output .= "       </tr>\n";
	}


$output .= "
  <tr class='update_gateway' >
		<td colspan='2'>
			<div class='submit'>
			<input type='submit' value='".__('Update &raquo;', 'wpsc')."' name='updateoption'/>
		</div>
		</td>
	</tr>
<!--
	<tr class='firstrowth'>
		<td style='border-bottom: medium none;' colspan='2'>
			<strong class='form_group'>Forms Sent to Gateway</strong>
		</td>
	</tr>

    <tr>
      <td>
      First Name Field
      </td>
      <td>
      <select name='nochex_form[first_name]'>
      ".nzshpcrt_form_field_list(get_option('nochex_form_first_name'))."
      </select>
      </td>
  </tr>
    <tr>
      <td>
      Last Name Field
      </td>
      <td>
      <select name='nochex_form[last_name]'>
      ".nzshpcrt_form_field_list(get_option('nochex_form_last_name'))."
      </select>
      </td>
  </tr>
    <tr>
      <td>
      Address Field
      </td>
      <td>
      <select name='nochex_form[address]'>
      ".nzshpcrt_form_field_list(get_option('nochex_form_address'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      City Field
      </td>
      <td>
      <select name='nochex_form[city]'>
      ".nzshpcrt_form_field_list(get_option('nochex_form_city'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      State Field
      </td>
      <td>
      <select name='nochex_form[state]'>
      ".nzshpcrt_form_field_list(get_option('nochex_form_state'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      Postal code/Zip code Field
      </td>
      <td>
      <select name='nochex_form[post_code]'>
      ".nzshpcrt_form_field_list(get_option('nochex_form_post_code'))."
      </select>
      </td>
  </tr>
  <tr>
      <td>
      Country Field
      </td>
      <td>
      <select name='nochex_form[country]'>
      ".nzshpcrt_form_field_list(get_option('nochex_form_country'))."
      </select>
      </td>
  </tr>
-->
  ";

  return $output;
}
?>
