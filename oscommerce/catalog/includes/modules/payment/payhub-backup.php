<?php
/*
  $Id$

  osCommerce, Open Source E-Commerce Solutions
  http://www.oscommerce.com

  Copyright (c) 2008 osCommerce
*/

  class PAYHUB {
    var $code, $title, $description, $enabled;

// class constructor
    function payhub() {
      global $order;

      $this->code = 'payhub';
      $this->title = MODULE_PAYMENT_PAYHUB_TEXT_TITLE;
      $this->public_title = MODULE_PAYMENT_PAYHUB_TEXT_PUBLIC_TITLE;
      $this->description = MODULE_PAYMENT_PAYHUB_TEXT_DESCRIPTION;
      $this->sort_order = MODULE_PAYMENT_PAYHUB_SORT_ORDER;
      $this->enabled = ((MODULE_PAYMENT_PAYHUB_STATUS == 'True') ? true : false);

      if ((int)MODULE_PAYMENT_PAYHUB_ORDER_STATUS_ID > 0) {
        $this->order_status = MODULE_PAYMENT_PAYHUB_ORDER_STATUS_ID;
      }

      if (is_object($order)) $this->update_status();
    }

// class methods
    function update_status() {
      global $order;

      if ( ($this->enabled == true) && ((int)MODULE_PAYMENT_PAYHUB_ZONE > 0) ) {
        $check_flag = false;
        $check_query = tep_db_query("select zone_id from " . TABLE_ZONES_TO_GEO_ZONES . " where geo_zone_id = '" . MODULE_PAYMENT_PAYHUB_ZONE . "' and zone_country_id = '" . $order->billing['country']['id'] . "' order by zone_id");
        while ($check = tep_db_fetch_array($check_query)) {
          if ($check['zone_id'] < 1) {
            $check_flag = true;
            break;
          } elseif ($check['zone_id'] == $order->billing['zone_id']) {
            $check_flag = true;
            break;
          }
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
      return array('id' => $this->code,
                   'module' => $this->public_title);
    }

    function pre_confirmation_check() {
      return false;
    }

    function confirmation() {
      global $order;

      for ($i=1; $i<13; $i++) {
        $expires_month[] = array('id' => sprintf('%02d', $i), 'text' => strftime('%B',mktime(0,0,0,$i,1,2000)));
      }

      $today = getdate(); 
      for ($i=$today['year']; $i < $today['year']+10; $i++) {
        $expires_year[] = array('id' => strftime('%y',mktime(0,0,0,1,1,$i)), 'text' => strftime('%Y',mktime(0,0,0,1,1,$i)));
      }

      $confirmation = array('fields' => array(array('title' => MODULE_PAYMENT_PAYHUB_CREDIT_CARD_OWNER,
                                                    'field' => tep_draw_input_field('cc_owner', $order->billing['firstname'] . ' ' . $order->billing['lastname'])),
                                              array('title' => MODULE_PAYMENT_PAYHUB_CREDIT_CARD_NUMBER,
                                                    'field' => tep_draw_input_field('cc_number_nh-dns')),
                                              array('title' => MODULE_PAYMENT_PAYHUB_CREDIT_CARD_EXPIRES,
                                                    'field' => tep_draw_pull_down_menu('cc_expires_month', $expires_month) . '&nbsp;' . tep_draw_pull_down_menu('cc_expires_year', $expires_year)),
                                              array('title' => MODULE_PAYMENT_PAYHUB_CREDIT_CARD_CVC,
                                                    'field' => tep_draw_input_field('cc_cvc_nh-dns', '', 'size="5" maxlength="4"'))));


      return $confirmation;
    }

    function process_button() {
      return false;
    }

    function before_process() {
      global $HTTP_POST_VARS, $customer_id, $order, $sendto, $currency;

      $states_map = array(
          "Alabama" => 1, "Alaska" => 2, "Arizona" => 3, "Arkansas" => 4,  "Army America" => 5, "Army Europe" => 6, "Army Pacific" => 7, "California" => 8, "Colorado" => 9, "Connecticut" => 10,  "Delaware" => 11, "Florida" => 12, "Georgia" => 13, "Hawaii" => 14, "Idaho" => 15, "Illinois" => 16, "Indiana" => 17,  "Iowa" => 18, "Kansas" => 19, "Kentucky" => 20, "Louisiana" => 21, "Maine" => 22, "Maryland" => 23,  "Massachusetts" => 24, "Michigan" => 25, "Minnesota" => 26,
          "Mississippi" => 27, "Missouri" => 28, "Montana" => 29, "Nebraska" => 30, "Nevada" => 31, "New Hampshire" => 32, "New Jersey" => 33, "New Mexico" => 34, "New York" => 35, "North Carolina" => 36, "North Dakota" => 37, "Ohio" => 38, "Oklahoma" => 39, "Oregon" => 41, "Pennsylvania" => 42, "Rhode Island" => 43, "South Carolina" => 44, "South Dakota" => 45,
          "Tennessee" => 46, "Texas" => 47, "Utah" => 48, "Vermont" => 49, "Virginia" => 50, "Washington" => 51, "Washington D.C." => 52, "West Virginia" => 53, "Wisconsin" => 54, "Wyoming" => 55, "AL" => 1, "AK" => 2, "AZ" => 3, "AR" => 4, "CA" => 8, "CO" => 9, "CT" => 10, "DE" => 11, "FL" => 12, "GA" => 13, "HI" => 14, "ID" => 15, "IL" => 16, "IN" => 17, "IA" => 18, "KS" => 19, "KY" => 20, "LA" => 21, "ME" => 22, "MD" => 23, "MA" => 24, "MI" => 25, "MN" => 26, "MS" => 27, "MO" => 28, "MT" => 29, "NE" => 30,  "NV" => 31, "NH" => 32, "NJ" => 33, "NM" => 34, "NY" => 35, "NC" => 36, "ND" => 37, "OH" => 38, "OK" => 39, "OR" => 41, "PA" => 42,
          "RI" => 43, "SC" => 44, "SD" => 45, "TN" => 46, "TX" => 47, "UT" => 48, "VT" => 49, "VA" => 50, "WA" => 51, "WV" => 53, "WI" => 54,  "WY" => 55);

      $state = ($states_map[substr($order->billing['state'], 0, 40)] != NULL) ? $states_map[substr($order->billing['state'], 0, 40)] : "";
      $cvv = substr($HTTP_POST_VARS['cc_cvc_nh-dns'], 0, 4);
      $amount = round(($order->info['total'] * 100), 0);

      $params = array('RECORD_FORMAT' => "CC",
                      'CARDHOLDER_ID_CODE' => "@",
                      'CARDHOLDER_ID_DATA' => "",
                      'MERCHANT_NUMBER' => substr(MODULE_PAYMENT_PAYHUB_ORGID, 0, 15),
                      'USER_NAME' => substr(MODULE_PAYMENT_PAYHUB_API_USERNAME, 0, 15),
                      'PASSWORD' => substr(MODULE_PAYMENT_PAYHUB_API_PASSWORD, 0, 15),
                      'TERMINAL_NUMBER' => substr(MODULE_PAYMENT_PAYHUB_TERMID, 0, 15),
                      'TRANSACTION_CODE' => '01',
                      'ACCOUNT_DATA_SOURCE' => 'T',
                      'CUSTOMER_FIRST_NAME' => substr($order->billing['firstname'], 0, 50),
                      'CUSTOMER_LAST_NAME' => substr($order->billing['lastname'], 0, 50),
                      'CUSTOMER_BILLING_ADDRESS1' => substr($order->billing['company'], 0, 50),
                      'CUSTOMER_BILLING_ADDRESS2' => substr($order->billing['street_address'], 0, 60),
                      'CUSTOMER_BILLING_ADD_CITY' => substr($order->billing['city'], 0, 40),
                      'CUSTOMER_BILLING_ADD_STATE' => $state,
                      'CUSTOMER_BILLING_ADD_ZIP' => substr($order->billing['postcode'], 0, 20),
                      'CUSTOMER_PHONE_NUMBER' => substr($order->customer['telephone'], 0, 25),
                      'CUSTOMER_EMAIL_ID' => substr($order->customer['email_address'], 0, 255),
                      'CUSTOMER_SHIPPING_ADD_NAME' => $order->delivery['firstname'] . " " . $order->delivery['lastname'],
                      'CUSTOMER_SHIPPING_ADDRESS1' => $order->delivery['street_address'],
                      'CUSTOMER_SHIPPING_ADDRESS2' => "",
                      'CUSTOMER_SHIPPING_ADD_CITY' => $order->delivery['city'],
                      'CUSTOMER_SHIPPING_ADD_STATE' => ($states_map[substr($order->delivery['state'], 0, 40)] != NULL) ? $states_map[substr($order->delivery['state'], 0, 40)] : "",
                      'CUSTOMER_SHIPPING_ADD_ZIP' => $order->delivery['postcode'],
                      'TRANSACTION_NOTE' => substr(STORE_NAME, 0, 255),
                      'TRANSACTION_AMOUNT' => $amount,
                      'CUSTOMER_DATA_FIELD' => substr($HTTP_POST_VARS['cc_number_nh-dns'], 0, 22),
                      'CARD_EXPIRY_DATE' => $HTTP_POST_VARS['cc_expires_month'] . "20" . $HTTP_POST_VARS['cc_expires_year'],
                      'CVV_DATA' => $cvv,
                      'CVV_CODE' => ($cvv != NULL) ? "Y" : "N"
                      );

      switch (MODULE_PAYMENT_PAYHUB_TESTMODE) {
        case 'Live':
          $gateway_url = 'https://vtp1.payhub.com/payhubvtws/transaction.json';
          break;

        default:
          $params['MERCHANT_NUMBER'] = "10027";
          $params['USER_NAME'] = "ND783kdniI";
          $params['PASSWORD'] = "yTV7Ctc3v2";
          $params['TERMINAL_NUMBER'] = "43";
          $gateway_url = 'https://sandbox.payhub.com/payhubvtws/transaction.json';
      }



      $post_string = json_encode($params);

      $res_string = $this->sendTransactionToGateway($gateway_url, $post_string);
      $response = json_decode($res_string);
      #$response = preg_replace('/\//', '', $response);

      if ($response->RESPONSE_CODE != "00") {

        switch ($response->RESPONSE_CODE) {
          case '0021':
            $error = 'invalid_expiration_date';
            break;

          case '05':
            $error = 'declined';
            break;

          case 'EB':
            $error = 'declined';
            break;

          case 'N7':
            $error = 'cvc';
            break;

          case '82':
            $error = 'cvc';
            break;

          case 'EC':
            $error = 'cvc';
            break;

          case 'EA':
            $error = 'card_number';
            break;

          case '51':
            $error = 'funds';
            break;

          default:
            $error = json_encode($response) . $gateway_url; #'general';
            break;
        }
      }

      if ($error != false) {
        tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code.'&error='.urlencode($error), 'NONSSL', true, false));
        #tep_redirect(tep_href_link(FILENAME_CHECKOUT_PAYMENT, 'payment_error=' . $this->code.'&error='.urlencode($error) . $response->RESPONSE_CODE . $response->RESPONSE_TEXT, 'NONSSL', true, false));
      }
    }

    function after_process() {
      return false;
    }

    function get_error() {
      global $HTTP_GET_VARS;

      switch ($HTTP_GET_VARS['error']) {
        case 'invalid_expiration_date':
          $error_message = MODULE_PAYMENT_PAYHUB_ERROR_INVALID_EXP_DATE . '  (' . $HTTP_GET_VARS['error'] . ')';
          break;

        case 'expired':
          $error_message = MODULE_PAYMENT_PAYHUB_ERROR_EXPIRED . '  (' . $HTTP_GET_VARS['error'] . ')';
          break;

        case 'declined':
          $error_message = MODULE_PAYMENT_PAYHUB_ERROR_DECLINED . '  (' . $HTTP_GET_VARS['error'] . ')';
          break;

        case 'funds':
          $error_message = MODULE_PAYMENT_PAYHUB_ERROR_FUNDS . '  (' . $HTTP_GET_VARS['error'] . ')';
          break;

        case 'card_number':
          $error_message = MODULE_PAYMENT_PAYHUB_ERROR_CARD . '  (' . $HTTP_GET_VARS['error'] . ')';
          break;

        case 'cvc':
          $error_message = MODULE_PAYMENT_PAYHUB_ERROR_CVC . '  (' . $HTTP_GET_VARS['error'] . ')';
          break;

        default:
          $error_message = MODULE_PAYMENT_PAYHUB_ERROR_GENERAL . '  (Server Response:  ' . $HTTP_GET_VARS['error'] . ')';
          break;
      }

      #$error_message = $error_message . $HTTP_GET_VARS['error'];

      $error = array('title' => MODULE_PAYMENT_PAYHUB_ERROR_TITLE,
                     'error' => $error_message);


      return $error;
    }

    function check() {
      if (!isset($this->_check)) {
        $check_query = tep_db_query("select configuration_value from " . TABLE_CONFIGURATION . " where configuration_key = 'MODULE_PAYMENT_PAYHUB_STATUS'");
        $this->_check = tep_db_num_rows($check_query);
      }
      return $this->_check;
    }

    function install() {
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Enable PayHub Credit Card Module', 'MODULE_PAYMENT_PAYHUB_STATUS', 'False', 'Do you want to accept Credit Cards through PayHub?', '6', '0', 'tep_cfg_select_option(array(\'True\', \'False\'), ', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Organization ID', 'MODULE_PAYMENT_PAYHUB_ORGID', '', 'The Organization ID for your PayHub account', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Username', 'MODULE_PAYMENT_PAYHUB_API_USERNAME', '', 'PayHub API Username generated for your account', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('API Password', 'MODULE_PAYMENT_PAYHUB_API_PASSWORD', '', 'PayHub API Password generated for your account', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Terminal ID', 'MODULE_PAYMENT_PAYHUB_TERMID', '', 'PayHub Terminal ID generated for your account', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('Sort order of display.', 'MODULE_PAYMENT_PAYHUB_SORT_ORDER', '0', 'Sort order of display. Lowest is displayed first.', '6', '0', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, use_function, set_function, date_added) values ('Payment Zone', 'MODULE_PAYMENT_PAYHUB_ZONE', '0', 'If a zone is selected, only enable this payment method for that zone.', '6', '2', 'tep_get_zone_class_title', 'tep_cfg_pull_down_zone_classes(', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, use_function, date_added) values ('Set Order Status', 'MODULE_PAYMENT_PAYHUB_ORDER_STATUS_ID', '0', 'Set the status of orders made with this payment module to this value', '6', '0', 'tep_cfg_pull_down_order_statuses(', 'tep_get_order_status_name', now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, date_added) values ('cURL Program Location', 'MODULE_PAYMENT_PAYHUB_CURL', '/usr/bin/curl', 'The location to the cURL program application.', '6', '0' , now())");
      tep_db_query("insert into " . TABLE_CONFIGURATION . " (configuration_title, configuration_key, configuration_value, configuration_description, configuration_group_id, sort_order, set_function, date_added) values ('Transaction Mode', 'MODULE_PAYMENT_PAYHUB_TESTMODE', 'Test', 'Transaction mode used for processing orders', '6', '0', 'tep_cfg_select_option(array(\'Test\', \'Live\'), ', now())");
    }

    function remove() {
      tep_db_query("delete from " . TABLE_CONFIGURATION . " where configuration_key in ('" . implode("', '", $this->keys()) . "')");
    }

    function keys() {
      return array('MODULE_PAYMENT_PAYHUB_STATUS',
                   'MODULE_PAYMENT_PAYHUB_ORGID',
                   'MODULE_PAYMENT_PAYHUB_API_USERNAME',
                   'MODULE_PAYMENT_PAYHUB_API_PASSWORD',
                   'MODULE_PAYMENT_PAYHUB_TERMID',
                   'MODULE_PAYMENT_PAYHUB_TESTMODE',
                   'MODULE_PAYMENT_PAYHUB_ZONE',
                   'MODULE_PAYMENT_PAYHUB_ORDER_STATUS_ID',
                   'MODULE_PAYMENT_PAYHUB_SORT_ORDER',
                   'MODULE_PAYMENT_PAYHUB_CURL');
    }


    function sendTransactionToGateway($url, $parameters) {

    $ch = curl_init();

    $c_opts = array(CURLOPT_URL => $url,
                    CURLOPT_VERBOSE => 0,
                    CURLOPT_SSL_VERIFYHOST => 0,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_CAINFO => "",
                    CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $parameters);

    curl_setopt_array($ch, $c_opts);

    $raw = curl_exec($ch);

    curl_close($ch);

    $raw = preg_replace('/\//', '', $raw);

    return $raw;
    }

// format prices without currency formatting
    function format_raw($number, $currency_code = '', $currency_value = '') {
      global $currencies, $currency;

      if (empty($currency_code) || !$this->is_set($currency_code)) {
        $currency_code = $currency;
      }

      if (empty($currency_value) || !is_numeric($currency_value)) {
        $currency_value = $currencies->currencies[$currency_code]['value'];
      }

      return number_format(tep_round($number * $currency_value, $currencies->currencies[$currency_code]['decimal_places']), $currencies->currencies[$currency_code]['decimal_places'], '.', '');
    }
  }
?>
