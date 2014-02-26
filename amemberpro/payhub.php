<?php
/*

Contributors: EJ Costiniano
Website: http://payhub.com
Tags: payment, gateway, credit card
Requires at least: 4.0.0
License: GNU

*/

	class AM_Paysystem_Payhub extends Am_Paysystem_CreditCard
	{
    const PLUGIN_STATUS = self::STATUS_PRODUCTION;
    const PLUGIN_REVISION = '1.0.0';

    protected $defaultTitle = 'PayHub';
    protected $defaultDescription = 'Pay by credit card';

    const GATEWAY_URL = "https://vtp1.payhub.com/payhubvtws/transaction.json";
    const GATEWAY_URL_TEST = "https://demo.payhub.com/payhubvtws/transaction.json";

    function storesCcInfo(){
        return false;
    }

    public function getGateway()
    {
        return $this->getConfig('testing') ? self::GATEWAY_URL_TEST : self::GATEWAY_URL;
    }

    public function _initSetupForm(Am_Form_Setup $form)
    {
      $form->addText('orgid', 'size=40')->setLabel('Organization ID:  ')->addRule('required');
      $form->addText('api_username', 'size=40')->setLabel('API Username:  ')->addRule('required');
      $form->addText('api_password', 'size=40')->setLabel('API Password:  ')->addRule('required');
      $form->addText('termid', 'size=40')->setLabel('Terminal ID:  ')->addRule('required');

      $form->addAdvCheckbox("testing")->setLabel("Test Mode Enabled?");
    }

    public function getRecurringType()
    {
        return self::REPORTS_CRONREBILL;
    }
    public function getSupportedCurrencies()
    {
        return array('USD');
    }

    public function _doBill(Invoice $invoice, $doFirst, CcRecord $cc, Am_Paysystem_Result $result)
    {

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

				//$u = $invoice->getUser();

				$state = ($states_map[$cc->cc_state] != NULL) ? $states_map[$cc->cc_state] : "";
				$zip = preg_match('/[a-zA-Z\-\s]/', $cc->cc_zip, $matches) ? "10001" : $cc->cc_zip;
				$expire_date = $cc->getExpire('%1$02d') . "20" . $cc->getExpire('%2$02d');

				
	       $trans = array(
				'RECORD_FORMAT' => "CC",
				'CARDHOLDER_ID_CODE' => "@",
				'CARDHOLDER_ID_DATA' => "",
				'MERCHANT_NUMBER' => $this->getConfig('orgid'),
				'USER_NAME' => $this->getConfig('api_username'),
				'PASSWORD' => $this->getConfig('api_password'),
				'TERMINAL_NUMBER' => $this->getConfig('termid'),
				'TRANSACTION_CODE' => "01",
				'ACCOUNT_DATA_SOURCE'=> "T",
				'CUSTOMER_FIRST_NAME' => $cc->cc_name_f,
				'CUSTOMER_LAST_NAME' => $cc->cc_name_l,
				'CUSTOMER_BILLING_ADDRESS1' => $cc->cc_street,
				'CUSTOMER_BILLING_ADDRESS2' => $cc->cc_country,
				'CUSTOMER_BILLING_ADD_CITY' => $cc->cc_city,
				'CUSTOMER_BILLING_ADD_STATE' => $state,
				'CUSTOMER_BILLING_ADD_ZIP' => $zip,
				'CUSTOMER_SHIPPING_ADD_NAME' => $cc->cc_name_f . $cc->cc_name_l,
				'CUSTOMER_SHIPPING_ADDRESS1' => $cc->cc_street,
				'CUSTOMER_SHIPPING_ADDRESS2' => $state,
				'CUSTOMER_SHIPPING_ADD_CITY' => $cc->cc_city,
				'CUSTOMER_SHIPPING_ADD_STATE' => $state,
				'CUSTOMER_SHIPPING_ADD_ZIP' => $zip,
				'CUSTOMER_EMAIL_ID' => $cc->email,
				'TRANSACTION_AMOUNT' => str_replace('.', '', $invoice->first_total),
				'CUSTOMER_DATA_FIELD' => $cc->cc_number,
				'CARD_EXPIRY_DATE' => $expire_date,
				'CVV_DATA' => $cc->getCvv(),
				'CVV_CODE' => ($cc->getCvv() != NULL) ? "Y" : "N",
				'TRANSACTION_ID' => ""         
	        );


		  	$request = new Am_HttpRequest($this->getGateway(), Am_HttpRequest::METHOD_POST);
		  	$request->setBody(json_encode($trans));
		  	$request->setHeader('Content-type', 'application/json');
		  	
	    //$request = $request->send();

	    $tr = new Am_Paysystem_Transaction_CreditCard_Payhub($this, $invoice, $request, $doFirst);

	    $tr->run($result);



  	}

    function processRefund(InvoicePayment $payment, Am_Paysystem_Result $result, $amount)
    	{
    		
        $ref_trans = array(
			'MERCHANT_NUMBER' => $this->getConfig('orgid'),
			'USER_NAME' => $this->getConfig('api_username'),
			'PASSWORD' => $this->getConfig('api_password'),
			'TERMINAL_NUMBER' => $this->getConfig('termid'),
        	'TRANSACTION_CODE' => '02',
        	'TRANSACTION_AMOUNT' => intval($amount * 100),
        	'TRANSACTION_ID' => $payment->receipt_id,
        	'RECORD_FORMAT' => 'CC'
        );

        
        $request = new Am_HttpRequest($this->getGateway(), Am_HttpRequest::METHOD_POST);
		  	$request->setBody(json_encode($ref_trans));
		  	$request->setHeader('Content-type', 'application/json');


		  	$raw = $request->send();

		  	$result->response = json_decode($raw->getBody());
		  	var_dump($result->response);

		  	if($result->response->RESPONSE_CODE == "4074"){
		  		$ref_trans['TRANSACTION_CODE'] = '03';
			  	$request->setBody(json_encode($ref_trans));
					$raw = $request->send();

			  	$result->response = json_decode($raw->getBody());

		  	}

		  	

		  	if($result->response->RESPONSE_CODE == "00" or $result->response->RESPONSE_CODE == "4076"){

		  		$result->setFailed("Refund Failed: " . $result->response->RESPONSE_TEXT);

		  		#$trans = new Am_Paysystem_Transaction_Manual($this);
				 	#$trans->setAmount($amount);
				  #$trans->setReceiptId('PayHub Refund ID: ' . $payment->receipt_id);
		  		#$result->setSuccess($trans);
		  		#$this->processValidated();
		  	} else {

		  			$result->setFailed("Refund Failed: " . $result->response->RESPONSE_TEXT);
		  	}
   	 }
  	public function processValidated()
	   {
	      
	   }
	}

	class Am_Paysystem_Transaction_CreditCard_Payhub extends Am_Paysystem_Transaction_CreditCard
	{

    	public function run(Am_Paysystem_Result $result) {
    		
    		$this->response = $this->request->send();

    		$this->response = $this->parseResponse($this->response);
    		var_dump($this->response);

    		if($this->response->RESPONSE_CODE != "00"){
    			$result->setFailed("Payment Failed: " . $this->response->AVS_RESULT_CODE);
    			return;
    		} else {
    			$result->setFailed("Payment Failed: " . $this->response->AVS_RESULT_CODE);
    			#$result->setSuccess($this);
    			#$this->processValidated();
    		}

    	}

	    public function validate()
	    {
	    	print_r("validate");
	    }

	    public function parseResponse()
	    {
	    	
	    	$this->vars = $this->response->getBody();
	    	return json_decode($this->vars);

	    }

	    public function getUniqId()
	    {
	    	print_r("get unique ID");
	      return $this->response->TRANSACTION_ID;
	    }
	    public function processValidated()
	    {
	    	print_r("get process Validated");
	      $this->invoice->addPayment($this);
	    }
	    public function validateStatus()
	    {
	    		print_r("validate status");
	        return true;
	    }

	}


?>
