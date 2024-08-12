<?php

// Translations shared by two or more payment methods

// Text
$_['text_payment']            = 'Payment';
$_['text_extension']	      = 'Extensions';
$_['text_success']            = 'Success: You have modified the payment module!';
$_['entry_yes']               = 'Yes';
$_['entry_no']                = 'No';

// General
$_['entry_geo_zone']          = 'Geo Zone:';
$_['entry_status']            = 'Status:';
$_['entry_sort_order']        = 'Sort Order:';
$_['entry_payment_description']   = 'Description in checkout:';
$_['entry_shipping_billing']   = 'Shipping same as billing:';
$_['entry_shipping_billing_text']   = 'On get address in checkout we always overwrite the billing address, this setting also overwrites shipping address. Important! This should be set to yes if your contract with Svea does not tell otherwise:';
$_['entry_version_text']           = 'Version';
$_['entry_version'] = getModuleVersion();
if(getNewVersionAvailable())
{
    $_['entry_version_info']      = 'There is a new version available. Click to download.';
}
else
{
    $_['entry_version_info']      = 'You have the latest version of the module. Click here to go to the documentation.';
}
$_['entry_module_repo'] = 'https://github.com/sveawebpay/opencart-module/';

// Authentication
$_['entry_username']          = 'Username:';
$_['entry_password']          = 'Password:';
$_['entry_clientno']          = 'Client Id:';
$_['entry_testmode']          = 'Test Mode:';
$_['entry_merchant_id']  = 'Merchant Id:';
$_['entry_sw']           = 'Secret:';

$_['entry_auto_deliver']      = 'Auto deliver order:';
$_['entry_auto_deliver_text'] = 'If enabled the order will automatically be delivered when creating an order. If disabled, deliver the order from Opencart admin.';
$_['entry_post'] = 'Post';
$_['entry_email'] = 'Email';
$_['entry_product'] = 'Product Price Widget:';
$_['entry_distribution_type'] = 'Method of invoice distribution to end-customers:';
$_['entry_hide_svea_comments'] = "Hide Svea comments:";
$_['entry_hide_svea_comments_tooltip'] = "Hides any comments that is added by the module on the order history so that only the customer and admin comments are visible. Does not apply to previously administrated orders. Read readme.md for more information.";

// Order statuses
$_['entry_deliver_status']                              = 'Deliver order statuses';
$_['entry_deliver_status_tooltip']                      = 'Setting the order status of a order to one of the order statuses in the list will make the module send a deliver order request to Svea. If the previous status was a deliver order status, no request will be sent.';
$_['entry_cancel_credit_status']                        = 'Cancel/credit order statuses';
$_['entry_cancel_credit_status_tooltip']                = 'Setting the order status of a order to one of the order statuses in the list will make the module send a cancel or credit order request to Svea(depending on if the order is previously delivered or not). If the previous status was a cancel/credit order status, no request will be sent.';

// Error
$_['error_permission']        = 'Warning: You do not have permission to modify the payment module!';
$_['error_validation_shared_status']            = 'Error: The list of deliver order statuses and the list of cancel/credit order statuses cannot share the same statuses. Please remove the following statuses from one of the lists: ';
$_['error_validation_deliver_status_empty']     = 'Error: The list of deliver order statuses may not be empty!';
$_['error_validation_cancel_credit_status_empty']     = 'Error: The list of cancel/credit order statuses may not be empty!';