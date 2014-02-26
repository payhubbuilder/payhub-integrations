=== PayHub Checkout Module ===
Contributors: payhub
Website: http://payhub.com
Tags: payment, gateway, credit card
Stable Version for this Module: 2.1 
License: GNU

== Description ==
This is a payment module for Tomato Cart v1.1.x  Installing this module will allow you to accept credit cards using PayHub Checkout.  An active PayHub account is required to use this module.  Please contact us to setup an account if you don't already have one.  Our contact information is at the bottom of this readme. 

== Release Notes ==

= v2.1 =
* First official release!
* Allows customers to pay by credit card directly through the Tomato Cart payment page using PayHub Checkout. 
* It is recommended that you use this plugin with the latest 1.1.x release, which is 1.1.8.6 as of the time of this writing.

== Installation and Configuration ==
Tomato Cart doesn't seem to have a way to add a new payment module to modules that it comes pre-bundled with so you will have to copy the PayHub Checkout files to the appropriate place on your server. So, assuming you have Tomato Cart installed and configured...

If for some reason you got this readme without the actual module, then you can download the module from http://developer.payhub.com.

Install:
* Unzip the PayHub Checkout module anywhere on your harddrive that you know how to get to (e.g. your desktop).
* Using your favorite FTP client, upload the following, unzipped files to the corresponding directories in the Tomato Cart installation:
	* admin/includes/modules/payment/payhub_checkout.php
	* includes/languages/en_US/modules/payment/payhub_checkout.xml
	* includes/modules/payment/payhub_checkout.php
  Example: "admin/includes/modules/payment/payhub_checkout.php" should be copied into the "<tomato cart root dir>/admin/includes/modules/payment/" directory on your server.
  Note:  Tomato Cart also has a File Manager that you can use to upload files to your server, but prior to 1.1.8.6 there is a bug that makes the uploaded files world-writable, which is a major security flaw, so don't do this unless you are using version 1.1.8.6.
* Refresh the payment modules page in Tomato Cart admin.
* Log into your Tomato Cart admin interface.
* Click the Start button in the admin interface and select Modules -> Payment Modules.
* Scroll down and you should see PayHub Checkout in the list. 
* Click on the green and white icon to the right to install the module.
* Click on the edit icon and proceed to configure the module as follows:
  * "Do you want to accept Credit Cards through PayHub?" - set this to "True"
  * Enter your Organization ID, API Username, API Password, and Terminal ID.  See the section "How to find your API credentials" below.
  * Set the "Transaction Mode" to "Test", so that you can test the module in a non-live environment.  IMPORTANT: WHEN YOU ARE READY TO GO LIVE, YOU MUST SET THIS TO "Live", OR TRANSACTIONS PROCESSED WILL NOT BE PAID TO YOU!
  * Select all "Credit Cards" that you accept.  (Note: You can enable more card types through Start -> Definitions -> Credit Cards in the admin interface.)
  * Leave the "Payment Zone" at the default, unless you have some specific need that requires you to change it.
  * Leave the "Set Order Status" option at the default, unless you have some specific need that requires you to change it.
  * Leave the "Set Order of Display" option at the default, unless you have some specific need that requires you to change it.
  * Leave the "cURL PRogram Location" option at the default, unless you have some specific need that requires you to change it.
* Once you are done configuring our module, click on the Save button.

You should now see the "Credit Card (Powered by PayHub)" in the Payment Information step during checkout.

== How to find your API credentials ==
1. Log into PayHub's Virtual Terminal
2. Click on Admin
3. Under General heading, click on 3rd Party API.
4. Copy down your Username, Password, and Terminal Id.  Please note the username and password is case sensitive.

== Notes on Testing ==
You should run the module in test mode and try both successful and failed transactions before making it live. You can find test data to use here: http://developer.payhub.com/api#api-howtotest.

***ONCE YOU ARE DONE TESTING, MAKE SURE TO CHANGE THE "TRANSACTION MODE" TO "live" IN THE MODULE CONFIGURATION AREA.  IF YOU DO NOT DO THIS THEN TRANSACTIONS PROCESSED THROUGH THIS ADD-ON WILL NOT BE PAID TO YOU!***

== Notes on Security ==
This plugin requires validation of the host SSL certificate for PayHub servers.  This is important as it greatly reduces the chance of a successful "man in the middle" attack.

If you go through the installation and everything works fine, then you don't have to worry about the rest of this section.  If you are experiencing a problem where you receive a blank error when trying to process cards and the transaction never actually processes then read on...

Since our plugin uses cURL (http://curl.haxx.se/) to send transaction requests, you need to make sure that cURL knows where to find the CA certificate with which to validate our API SSL certs.  This is generally not a problem with hosted setups, but if you have built out your own server then you may find that this is a problem because newer versions of cURL don't include a CA bundle by default.  In this case, if you are using PHP 5.3.7 or greater you can:

*download http://curl.haxx.se/ca/cacert.pem and save it somewhere on your server.
*update php.ini -- add curl.cainfo = "PATH_TO/cacert.pem"

This solutions was shamelessly borrowed from the Stack Overflow post: http://stackoverflow.com/questions/6400300/php-curl-https-causing-exception-ssl-certificate-problem-verify-that-the-ca-cer.  Gotta love Stack Overflow ;^).

Alternatively, you can dig into the PayHub plugin itself and add the following key/value pair to the $c_opts array: CURLOPT_CAINFO => "payth/to/ca-bundle.pem".  See http://us2.php.net/manual/en/book.curl.php for more info.

== Getting Support from PayHub ==
Please contact us at wecare@payhub.com or 415-306-9476 for support.