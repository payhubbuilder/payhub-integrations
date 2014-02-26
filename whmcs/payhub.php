<?php
/*
=== PayHub Module For WHMCS ===
Contributors: EJ Costiniano
Website: http://payhub.com
Tags: payment, gateway, credit card, visa, mastercard, discover, transaction, receipts, american express
Requires at least: 5 or Later
License: GNU
version 1.0


== Description ==
PayHub's gateway module for WHMCS.  This module allows WHMCS to process credit card payments through PayHub.  Please note, a PayHub account is required through Central Payment to process transactions.


* How to find your API credentials
 - Log into PayHub's Virtual Terminal
 - Click on Admin
 - Under General heading, click on 3rd Party API.
 - Copy down your Username, Password, and Terminal ID.  Please note the username and password is case sensitive.


If you have any questions you can contact payhub at:
(415) 306-9476 from 8AM - 5PM PST M-F
or email us at wecare@payhub.com

To setup a central payment account, please send an email to wecare@payhub.com.
*/





function PayHub_config() {
    $configarray = array(
     "FriendlyName" => array("Type" => "System", "Value"=>"PayHub",),
     "instructions" => array("FriendlyName" => "PayHub API Credentials", "Description" => "Please log into your PayHub account to get your API credentials at https://vtp1.payhub.com. Once logged in, click on Admin, then click on the 3rd Party API link.", ),
     "Organization_ID" => array("FriendlyName" => "Organization ID", "Type" => "text", "Size" => "20", ),
     "API_username" => array("FriendlyName" => "API Username", "Type" => "text", "Size" => "20", ),
     "API_password" => array("FriendlyName" => "API Password", "Type" => "text", "Size" => "20", ),
     "Terminal_ID" => array("FriendlyName" => "Terminal ID", "Type" => "text", "Size" => "20", )
    );

	return $configarray;
}

function states($state) {
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
              "WY" => 55
              );
  $state = $states_map[$state];
  return $state;
}

function payhub_capture($params) {

  $data_to_send = array(
   "MERCHANT_NUMBER" => $params['Organization_ID'],
   "USER_NAME" => $params['API_username'],
   "PASSWORD" => $params['API_password'],
   "TERMINAL_NUMBER" => $params['Terminal_ID'],
   "TRANSACTION_CODE" => "01",
   "RECORD_FORMAT" => "CC",
   "CARDHOLDER_ID_CODE" => "@",
   "CARD_HOLDER_ID_DATA" => "",
   "ACCOUNT_DATA_SOURCE" => "T",
   "CUSTOMER_DATA_FIELD" => $params['cardnum'],
   "CARD_EXPIRY_DATE" => substr_replace($params['cardexp'], "20", 2, 0),
   "TRANSACTION_NOTE" => $params['invoiceid'], 
   "CVV_DATA" => $params['cccvv'],
   "CVV_CODE" => ($params['cccvv'] == "") ? "N" : "Y",
   "TRANSACTION_AMOUNT" => $params['amount'] * 100,
   "OFFLINE_APPROVAL_CODE" => "",
   "TRANSACTION_ID" => "",
   "CUSTOMER_ID" => "",
   "CUSTOMER_FIRST_NAME" => $params['clientdetails']['firstname'],
   "CUSTOMER_LAST_NAME" => $params['clientdetails']['lastname'],
   "CUSTOMER_COMPANY_NAME" => "",
   "CUSTOMER_JOB_TITLE" => "",
   "CUSTOMER_EMAIL_ID" => $params['clientdetails']['email'],
   "CUSTOMER_WEB" => "",
   "CUSTOMER_PHONE_NUMBER" => $params['clientdetails']['phonenumber'],
   "CUSTOMER_PHONE_EXT" => "",
   "CUSTOMER_PHONE_TYPE" => "",
   "CUSTOMER_BILLING_ADDRESS1" => $params['clientdetails']['address1'],
   "CUSTOMER_BILLING_ADDRESS2" => $params['clientdetails']['address2'],
   "CUSTOMER_BILLING_ADD_CITY" => $params['clientdetails']['city'],
   "CUSTOMER_BILLING_ADD_STATE" => states($params['clientdetails']['state']),
   "CUSTOMER_BILLING_ADD_ZIP" => $params['clientdetails']['postcode'],
   "CUSTOMER_SHIPPING_ADD_NAME" => "",
   "CUSTOMER_SHIPPING_ADDRESS1" => "",
   "CUSTOMER_SHIPPING_ADDRESS2" => "",
   "CUSTOMER_SHIPPING_ADD_CITY" => "",
   "CUSTOMER_SHIPPING_ADD_STATE" => "",
   "CUSTOMER_SHIPPING_ADD_ZIP" => "",
   "TRANSACTION_IS_AUTH" => ""
		);

  
	# Perform Transaction Here & Generate $results Array, eg:
  $ch = curl_init();

  $c_opts = array(CURLOPT_URL => "http://payhubvtws.payhub.cloudbees.net/transaction.json",
                  CURLOPT_VERBOSE => false,
                  CURLOPT_SSL_VERIFYHOST => 0,
                  CURLOPT_SSL_VERIFYPEER => true,
                  CURLOPT_CAINFO => "",
                  CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
                  CURLOPT_RETURNTRANSFER => true,
                  CURLOPT_POST => true,
                  CURLOPT_POSTFIELDS => json_encode($data_to_send));

  curl_setopt_array($ch, $c_opts);

  $results = curl_exec($ch);

  curl_close($ch);

  $response = json_decode($results)->RESPONSE_TEXT;
  $trans_id = json_decode($results)->TRANSACTION_ID;

  //var_dump($response);
  //var_dump($trans_id);

	# Return Results
	if ($response=="SUCCESS") {
		return array("status"=>"success","transid"=>$trans_id,"rawdata"=>$results);
	} elseif ($gatewayresult=="declined") {
        return array("status"=>"declined","rawdata"=>$results);
    } else {
		return array("status"=>"Error","rawdata"=>$results);
	}

}

function payhub_refund($params) {

  $response = "failed";
  $rawdata = "Please contact PayHub Support if you are trying to do a refund at (415) 306-9476.  We will eventually add refunds to this module.";

  if ($response=="SUCCESS") {
    return array("status"=>"success","transid"=>$trans_id,"rawdata"=>$rawdata);
  } elseif ($response!="SUCCESS") {
        return array("status"=>"declined","rawdata"=>$rawdata);
  } else {
    return array("status"=>"error","rawdata"=>$rawdata);
  }

}

?>