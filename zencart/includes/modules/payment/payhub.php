<?php

class payhub{


  function PAYHUB() { 
    global $order; 
    $this->code = 'payhub'; 
    $this->title = MODULE_PAYMENT_PAYHUB_TEXT_ADMIN_TITLE; 
    $this->description = MODULE_PAYMENT_PAYHUB_TEXT_DESCRIPTION; 
    $this->sort_order = MODULE_PAYMENT_PAYHUB_SORT_ORDER; 
    $this->enabled = ((MODULE_PAYMENT_PAYHUB_STATUS == 'True') ? true : false); 

   }

  function update_status() {
    global $order, $db;

    if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYHUB_ZONE > 0) ) {
      $check_flag = false;
      $check = $db->Execute("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYHUB_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
      while (!$check->EOF) {
        if ($check->fields['zone_id'] < 1) {
          $check_flag = true;
          break;
        } elseif ($check->fields['zone_id'] == $order->billing['zone_id']) {
          $check_flag = true;
          break;
        }
        $check->MoveNext();
      }

      if ($check_flag == false) {
        $this->enabled = false;
      }
    }
  }

  function javascript_validation() {
    return false;
  }

  function selection() {
    global $order;

    for ($i=1; $i<13; $i++) {
      $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
    }

    $today = getdate();
    for ($i=$today['year']; $i < $today['year']+10; $i++) {
      $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
    }
    $onFocus = ' onfocus="methodSelect(\'pmt-' . $this->code . '\')"';

	    if (MODULE_PAYMENT_PAYHUB_USE_CVV == 'True') {
	      $selection = array('id' => $this->code,
	                         'module' => MODULE_PAYMENT_PAYHUB_TEXT_CATALOG_TITLE,
	                         'fields' => array(array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_OWNER,
	                                                 'field' => zen_draw_input_field('payhub_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'id="'.$this->code.'-cc-owner"'. $onFocus),
	                                                 'tag' => $this->code.'-cc-owner'),
	                                           array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_NUMBER,
	                                                 'field' => zen_draw_input_field('payhub_cc_number', '', 'id="'.$this->code.'-cc-number"' . $onFocus),
	                                                 'tag' => $this->code.'-cc-number'),
	                                           array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_EXPIRES,
	                                                 'field' => zen_draw_pull_down_menu('payhub_cc_expires_month', $expires_month, '', 'id="'.$this->code.'-cc-expires-month"' . $onFocus) . '&nbsp;' . zen_draw_pull_down_menu('payhub_cc_expires_year', $expires_year, '', 'id="'.$this->code.'-cc-expires-year"' . $onFocus),
	                                                 'tag' => $this->code.'-cc-expires-month'),
	                                           array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CVV,
	                                                 'field' => zen_draw_input_field('payhub_cc_cvv','', 'size="4", maxlength="4"' . ' id="'.$this->code.'-cc-cvv"' . $onFocus) . ' ' . '<a href="javascript:popupWindow(\'' . zen_href_link(FILENAME_POPUP_CVV_HELP) . '\')">' . MODULE_PAYMENT_PAYHUB_TEXT_POPUP_CVV_LINK . '</a>',
	                                                 'tag' => $this->code.'-cc-cvv')
				 ));
	    } else {
	      $selection = array('id' => $this->code,
	                         'module' => MODULE_PAYMENT_PAYHUB_TEXT_CATALOG_TITLE,
	                         'fields' => array(array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_OWNER,
	                                                 'field' => zen_draw_input_field('payhub_cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'], 'id="'.$this->code.'-cc-owner"'. $onFocus),
	                                                 'tag' => $this->code.'-cc-owner'),
	                                           array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_NUMBER,
	                                                 'field' => zen_draw_input_field('payhub_cc_number', '', 'id="'.$this->code.'-cc-number"' . $onFocus),
	                                                 'tag' => $this->code.'-cc-number'),
	                                           array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_EXPIRES,
	                                                 'field' => zen_draw_pull_down_menu('payhub_cc_expires_month', $expires_month, '', 'id="'.$this->code.'-cc-expires-month"' . $onFocus) . '&nbsp;' . zen_draw_pull_down_menu('payhub_cc_expires_year', $expires_year, '', 'id="'.$this->code.'-cc-expires-year"' . $onFocus),
	                                                 'tag' => $this->code.'-cc-expires-month')));
    }
    return $selection;
  }
  

  function pre_confirmation_check() {

  }

  function confirmation() {
    global $_POST;

    if (MODULE_PAYMENT_PAYHUB_USE_CVV == 'True') {
      $confirmation = array(//'title' => MODULE_PAYMENT_PAYHUB_TEXT_CATALOG_TITLE, // Redundant
                            'fields' => array(array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_OWNER,
                                                    'field' => $_POST['payhub_cc_owner']),
                                              array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_NUMBER,
                                                    'field' => $_POST['payhub_cc_number']),
                                              array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_EXPIRES,
                                                    'field' => strftime('%B, %Y', mktime(0,0,0,$_POST['payhub_cc_expires_month'], 1, '20' . $_POST['payhub_cc_expires_year']))),
                                              array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CVV,
                                                    'field' => $_POST['payhub_cc_cvv'])));
    } else {
      $confirmation = array(//'title' => MODULE_PAYMENT_PAYHUB_TEXT_CATALOG_TITLE, // Redundant
                            'fields' => array(array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_OWNER,
                                                    'field' => $_POST['payhub_cc_owner']),
                                              array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_NUMBER,
                                                    'field' => $_POST['payhub_cc_number']),
                                              array('title' => MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_EXPIRES,
                                                    'field' => strftime('%B, %Y', mktime(0,0,0,$_POST['payhub_cc_expires_month'], 1, '20' . $_POST['payhub_cc_expires_year'])))));
    }
    return $confirmation;
  }

	function process_button () {
    $process_button_string = zen_draw_hidden_field('cc_owner' . "\n", $_POST['payhub_cc_owner']) .
                             zen_draw_hidden_field('cc_expires' . "\n", $_POST['payhub_cc_expires_month'] . "20" . $_POST['payhub_cc_expires_year']) .
                             zen_draw_hidden_field('cc_number' . "\n", $_POST['payhub_cc_number']);
    if (MODULE_PAYMENT_PAYHUB_USE_CVV == 'True') {
      $process_button_string .= zen_draw_hidden_field('cc_cvv' . "\n", $_POST['payhub_cc_cvv']);
    }

    $process_button_string .= zen_draw_hidden_field(zen_session_name(), zen_session_id());

    return $process_button_string;


	}

  function before_process() {
  	
  	global $_POST, $response, $db, $order, $messageStack;

				$states_map = array(
					"Alabama" => 1,
				  "Alaska" => 2,
				  "Arizona" => 3,
				  "Arkansas" => 4,
				  "Army America" => 5,
				  "Army Europe" => 6,
				  "Army Pacific" => 7,
				  "California" => 8,
				  "Colorado" => 9,
				  "Connecticut" => 10,
				  "Delaware" => 11,
				  "Florida" => 12,
				  "Georgia" => 13,
				  "Hawaii" => 14,
				  "Idaho" => 15,
				  "Illinois" => 16,
				  "Indiana" => 17,
				  "Iowa" => 18,
				  "Kansas" => 19,
				  "Kentucky" => 20,
				  "Louisiana" => 21,
				  "Maine" => 22,
				  "Maryland" => 23,
				  "Massachusetts" => 24,
				  "Michigan" => 25,
				  "Minnesota" => 26,
				  "Mississippi" => 27,
				  "Missouri" => 28,
				  "Montana" => 29,
				  "Nebraska" => 30,
				  "Nevada" => 31,
				  "New Hampshire" => 32,
				  "New Jersey" => 33,
				  "New Mexico" => 34,
				  "New York" => 35,
				  "North Carolina" => 36,
				  "North Dakota" => 37,
				  "Ohio" => 38,
				  "Oklahoma" => 39,
				  "Oregon" => 41,
				  "Pennsylvania" => 42,
				  "Rhode Island" => 43,
				  "South Carolina" => 44,
				  "South Dakota" => 45,
				  "Tennessee" => 46,
				  "Texas" => 47,
				  "Utah" => 48,
				  "Vermont" => 49,
				  "Virginia" => 50,
				  "Washington" => 51,
				  "Washington D.C." => 52,
				  "West Virginia" => 53,
				  "Wisconsin" => 54,
				  "Wyoming" => 55,
				  "AL" => 1,
				  "AK" => 2,
				  "AZ" => 3,
				  "AR" => 4,
				  "CA" => 8,
				  "CO" => 9,
				  "CT" => 10,
				  "DE" => 11,
				  "FL" => 12,
				  "GA" => 13,
				  "HI" => 14,
				  "ID" => 15,
				  "IL" => 16,
				  "IN" => 17,
				  "IA" => 18,
				  "KS" => 19,
				  "KY" => 20,
				  "LA" => 21,
				  "ME" => 22,
				  "MD" => 23,
				  "MA" => 24,
				  "MI" => 25,
				  "MN" => 26,
				  "MS" => 27,
				  "MO" => 28,
				  "MT" => 29,
				  "NE" => 30,
				  "NV" => 31,
				  "NH" => 32,
				  "NJ" => 33,
				  "NM" => 34,
				  "NY" => 35,
				  "NC" => 36,
				  "ND" => 37,
				  "OH" => 38,
				  "OK" => 39,
				  "OR" => 41,
				  "PA" => 42,
				  "RI" => 43,
				  "SC" => 44,
				  "SD" => 45,
				  "TN" => 46,
				  "TX" => 47,
				  "UT" => 48,
				  "VT" => 49,
				  "VA" => 50,
				  "WA" => 51,
				  "WV" => 53,
				  "WI" => 54,
				  "WY" => 55);

		$state = ($states_map[$order->billing['state']] != NULL) ? $states_map[$order->billing['state']] : "";

    $order->info['cc_number'] = str_pad(substr($_POST['cc_number'], -4), strlen($_POST['cc_number']), "X", STR_PAD_LEFT);
    $order->info['cc_expires'] = $_POST['cc_expires'];
    $order->info['cc_type'] = $_POST['cc_type'];
    $order->info['cc_owner'] = $_POST['cc_owner'];
    

    $last_order_id = $db->Execute("select * from " . TABLE_ORDERS . " order by orders_id desc limit 1");
    $new_order_id = $last_order_id->fields['orders_id'];
    $new_order_id = ($new_order_id + 1);

    $url = 'https://vtp1.payhub.com/payhubvtws/transaction.json';

    $data = array(
    	"RECORD_FORMAT" => "CC",
    	"CARDHOLDER_ID_CODE" => "@",
    	"CARDHOLDER_ID_DATA" => "",
			"MERCHANT_NUMBER" => MODULE_PAYMENT_PAYHUB_ORGID,
			"USER_NAME" => MODULE_PAYMENT_PAYHUB_USERNAME,
			"PASSWORD" => MODULE_PAYMENT_PAYHUB_PASSWORD,
			"TERMINAL_NUMBER" => MODULE_PAYMENT_PAYHUB_TERMID,
			"TRANSACTION_CODE" => "01",
			"ACCOUNT_DATA_SOURCE" => "T",
			"CUSTOMER_DATA_FIELD" => $_POST['cc_number'],
			"CARD_EXPIRY_DATE" => $_POST['cc_expires'],
			"TRANSACTION_NOTE" => "IP: " . zen_get_ip_address() . "Purchased from: " . STORE_NAME . ", Invoice ID: " . $new_order_id, 
			"CVV_DATA" => $_POST['cc_cvv'],
			"CVV_CODE" => ($_POST['cc_cvv'] != NULL) ? "Y" : "N",
			"TRANSACTION_AMOUNT" => (number_format($order->info['total'], 2) * 100),
			"OFFLINE_APPROVAL_CODE" => "",
			"TRANSACTION_ID" => "",
			"CUSTOMER_ID" => "",
			"CUSTOMER_FIRST_NAME" => $order->billing['firstname'],
			"CUSTOMER_LAST_NAME" => $order->billing['lastname'],
			"CUSTOMER_COMPANY_NAME" => $order->billing['company'],
			"CUSTOMER_JOB_TITLE" => "",
			"CUSTOMER_EMAIL_ID" => $order->customer['email_address'],
			"CUSTOMER_WEB" => "",
			"CUSTOMER_PHONE_NUMBER" => $order->customer['telephone'],
			"CUSTOMER_PHONE_EXT" => "",
			"CUSTOMER_PHONE_TYPE" => "",
			"CUSTOMER_BILLING_ADDRESS1" => $order->billing['street_address'],
			"CUSTOMER_BILLING_ADDRESS2" => "",
			"CUSTOMER_BILLING_ADD_CITY" => $order->billing['city'],
			"CUSTOMER_BILLING_ADD_STATE" => $state,
			"CUSTOMER_BILLING_ADD_ZIP" => $order->billing['postcode'],
			"CUSTOMER_SHIPPING_ADD_NAME" => $order->delivery['firstname'] . $order->delivery['lastname'],
			"CUSTOMER_SHIPPING_ADDRESS1" => $order->delivery['street_address'],
			"CUSTOMER_SHIPPING_ADDRESS2" => "",
			"CUSTOMER_SHIPPING_ADD_CITY" => $order->delivery['city'],
			"CUSTOMER_SHIPPING_ADD_STATE" => $state,
			"CUSTOMER_SHIPPING_ADD_ZIP" => $order->delivery['postcode'],
			"TRANSACTION_IS_AUTH" => ""
    );

		unset($response);

    $data_to_send = json_encode($data);

    $ch = curl_init();

    $c_opts = array(CURLOPT_URL => $url,
                    CURLOPT_VERBOSE => 0,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => true,
                    CURLOPT_CAINFO => "",
                    CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $data_to_send);

    curl_setopt_array($ch, $c_opts);

    $raw = curl_exec($ch);

    curl_close($ch);

    $response = json_decode($raw);

    $this->auth_code = $response->APPROVAL_CODE;
    $this->transaction_id = $response->TRANSACTION_ID;

    $db_response_code = $response->RESPONSE_CODE;
    $db_response_text = $response->RESPONSE_TEXT;
    $db_transaction_id = $response->TRANSACTION_ID;
    $db_authorization_type = $response->APPROVAL_CODE;
    $db_session_id = zen_session_id();

     $db->Execute("insert into " . TABLE_AUTHORIZENET . "  (id, customer_id,order_id, response_code, response_text, authorization_type, transaction_id, sent, received, time, session_id) values ('', '" . $_SESSION['customer_id'] . "', '" . $new_order_id . "', '" . $db_response_code . "', '" . $db_response_text . "', '" . $db_authorization_type . "', '" . $db_transaction_id . "', '" . print_r($reportable_submit_data, true) . "', '" . $response_list . "', '" . $order_time . "', '" . $db_session_id . "')");
    


    if ($response->RESPONSE_CODE != '00') {
      $messageStack->add_session('checkout_payment', "Payment failed due to the following: " . $response->RESPONSE_CODE . " / " . $response->RESPONSE_TEXT, 'error');
      zen_redirect(zen_href_link(FILENAME_CHECKOUT_PAYMENT, '', 'SSL', true, false));
    }


  }

  function after_process() {
  	global $insert_id, $db;
  	var_dump("AFTER PROCESS");
    $db->Execute("insert into " . TABLE_ORDERS_STATUS_HISTORY . " (comments, orders_id, orders_status_id, date_added) values ('Credit Card payment.  AUTH: " . $this->auth_code . ". TransID: " . $this->transaction_id . ".' , '". (int)$insert_id . "','" . $this->order_status . "', now() )");
      #return false;
  }

  function get_error() {
    global $_GET;
    var_dump($response);
    var_dump($_GET);
    $error = array('title' => MODULE_PAYMENT_PAYHUB_TEXT_ERROR,
                   'error' => $response);

    return $error;
  }

  function check() {
  	#Check to see whether module is installed

    global $db;
    if (!isset($this->_check)) {
      $check_query = $db->Execute("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYHUB_STATUS'");
      $this->_check = $check_query->RecordCount();
    }
    return $this->_check;
  }

  function install() {
  	#Install the payment module and its configuration settings

    global $db;
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PayHub Module', 'MODULE_PAYMENT_PAYHUB_STATUS', 'True', 'Do you want to accept Credit Card payments via PayHub?', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Organization ID', 'MODULE_PAYMENT_PAYHUB_ORGID', '00000', 'This is your Organization ID', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Username', 'MODULE_PAYMENT_PAYHUB_USERNAME', 'api username', 'This your API Username', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Password', 'MODULE_PAYMENT_PAYHUB_PASSWORD', 'api password', 'This is your API Password', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Request CVV Number', 'MODULE_PAYMENT_PAYHUB_USE_CVV', 'True', 'Do you want to ask the customer for the card\'s CVV number', '6', '0', 'zen_cfg_select_option(array(\'True\', \'False\'), ', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('PayHub URL', 'MODULE_PAYMENT_PAYHUB_URL', 'https://vtp1.payhub.com/payhubvtws/transaction.json', 'The url to use for processing.', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Terminal ID', 'MODULE_PAYMENT_PAYHUB_TERMID', '000', 'This is your terminal ID', '6', '0', now())");
    $db->Execute("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYHUB_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'zen_cfg_pull_down_order_statuses(', 'zen_get_order_status_name', now())");


  }

  function remove() {
	  #Remove the module and all its settings
    global $db;
    $db->Execute("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
  }

    function keys() {
      return array(
      	'MODULE_PAYMENT_PAYHUB_STATUS',
      	'MODULE_PAYMENT_PAYHUB_ORGID',
      	'MODULE_PAYMENT_PAYHUB_USERNAME',
      	'MODULE_PAYMENT_PAYHUB_PASSWORD',
      	'MODULE_PAYMENT_PAYHUB_TERMID',
      	'MODULE_PAYMENT_PAYHUB_URL',
      	'MODULE_PAYMENT_PAYHUB_USE_CVV',
      	'MODULE_PAYMENT_PAYHUB_ORDER_STATUS_ID'
       );
    }

}
?>