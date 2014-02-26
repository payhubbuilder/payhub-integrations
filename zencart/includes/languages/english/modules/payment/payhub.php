<?php
/**
 * PayHub Payment Module v1.0.0 by EJ Costiniano
 * based on Authorize.net "AIM" code created by Eric Stamper - 01/30/2004 Released under GPL
 *
 */


// Admin Configuration Items
  define('MODULE_PAYMENT_PAYHUB_TEXT_ADMIN_TITLE', 'PayHub'); // Payment option title as displayed in the admin
  define('MODULE_PAYMENT_PAYHUB_TEXT_DESCRIPTION', (defined('MODULE_PAYMENT_PAYHUB_TESTMODE') && MODULE_PAYMENT_PAYHUB_TESTMODE == 'Production' ? '' : 'When using PayHub in TEST mode, please contact PayHub for demo account login information.<br />'));

// Catalog Items
  define('MODULE_PAYMENT_PAYHUB_TEXT_CATALOG_TITLE', 'Credit Card');  // Payment option title as displayed to the customer
  define('MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_TYPE', 'Credit Card Type:');
  define('MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_OWNER', 'Credit Card Owner:');
  define('MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_NUMBER', 'Credit Card Number:');
  define('MODULE_PAYMENT_PAYHUB_TEXT_CREDIT_CARD_EXPIRES', 'Credit Card Expiry Date:');
  define('MODULE_PAYMENT_PAYHUB_TEXT_CVV', 'CVV Number:');
  define('MODULE_PAYMENT_PAYHUB_TEXT_POPUP_CVV_LINK', 'What\'s this?');
  define('MODULE_PAYMENT_PAYHUB_TEXT_JS_CC_OWNER', '* The owner\'s name of the credit card must be at least ' . CC_OWNER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_PAYHUB_TEXT_JS_CC_NUMBER', '* The credit card number must be at least ' . CC_NUMBER_MIN_LENGTH . ' characters.\n');
  define('MODULE_PAYMENT_PAYHUB_TEXT_JS_CC_CVV', '* The 3 or 4 digit CVV number must be entered from the back of the credit card.\n');
  define('MODULE_PAYMENT_PAYHUB_TEXT_DECLINED_MESSAGE', 'Your credit card could not be authorized for this reason. Please correct any information and try again or contact us for further assistance.');
  define('MODULE_PAYMENT_PAYHUB_TEXT_ERROR', 'Credit Card Error!');
?>