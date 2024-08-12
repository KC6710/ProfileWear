<?php
// Heading
$_['heading_title']				    = 'Svea Checkout';
$_['text_extension']                = 'Extension';

// Misc
$_['text_success']				    = 'Success: You have modified the Svea Checkout module!';
$_['text_edit']					    = 'Edit Svea Checkout';

// Tabs
$_['tab_general']				    = 'General';
$_['tab_authorization']			    = 'Authorization';
$_['tab_checkout_page_settings']    = 'Checkout page-settings';
$_['tab_iframe_settings']           = 'Iframe-settings';
$_['tab_debug_settings']            = 'Debug';
$_['tab_order_statuses']            = 'Order statuses';

// General
$_['entry_status']				    = 'Status';
$_['entry_status_tooltip']          = 'Enable/Disable Svea Checkout';
$_['text_show_widget_on_product_page'] = 'Show product price widget on product page';
$_['text_show_widget_on_product_page_tooltip'] = 'The lowest price of the campaign available for part payment will be displayed on the product page. Using this option require you to have VQMod installed.';
$_['text_hide_svea_comments'] = 'Hide Svea comments';
$_['text_hide_svea_comments_tooltip'] = 'Hides any comments that is added by the module on the order history so that only the customer and admin comments are visible. Does not apply to previously administrated orders. Read readme.md for more information.';

// Authorization
$_['entry_checkout_default_country']= 'Default checkout country';
$_['entry_checkout_default_country_tooltip']   = 'If a customer selects a country which is not one of the ones below then this checkout will be loaded';
$_['entry_test_mode']			    = 'Test mode';
$_['entry_test_mode_tooltip']       = 'If enabled the test environment will be used instead of the production environment.';

$_['entry_sweden']                  = 'Sweden';
$_['entry_norway']                  = 'Norway';
$_['entry_finland']                 = 'Finland';
$_['entry_denmark']                 = 'Denmark';
$_['entry_germany']                 = 'Germany';

$_['entry_stage_environment']       = 'Stage/test credentials:';
$_['entry_prod_environment']        = 'Live/Production credentials:';
$_['entry_checkout_merchant_id']    = 'Merchant Id:';
$_['entry_checkout_secret']		    = 'Secret Word:';

// Checkout page settings
$_['entry_status_checkout']		    = 'Show option to go to default checkout on checkout page';
$_['entry_status_checkout_tooltip'] = 'If enabled, there will be a link on the checkout page which takes the customer to the default Opencart checkout. This can be used if you have more payment methods than SCO';
$_['text_show_voucher_on_checkout']	= 'Show Voucher on Checkout page';
$_['text_show_voucher_on_checkout_tooltip']	= 'If set to \'Yes\', customers will be able to enter vouchers on the checkout page';
$_['text_show_coupons_on_checkout']	= 'Show Coupon on Checkout page';
$_['text_show_coupons_on_checkout_tooltip']	= 'If set to \'Yes\', customers will be able to enter coupons on the checkout page';
$_['text_show_order_comment_on_checkout']   = 'Show Message on Checkout page';
$_['text_show_order_comment_on_checkout_tooltip']   = 'If set to \'Yes\', customers will be able to enter their own messages on their orders on the checkout page';
$_['text_gather_newsletter_consent'] = 'Gather newsletter consent';
$_['text_gather_newsletter_consent_tooltip'] = 'If enabled a checkbox with the text \'Subscribe to newsletter?\' will appear on the checkout page. If the user clicks the box, we will gather their consent in the database which can then be used to import email-addresses into newsletter modules.';
$_['text_download_newsletter_list'] = 'Download newsletter list';
$_['text_newsletter_consent_list'] = 'Newsletter consent list';
$_['text_close'] = 'Close';
$_['text_copy_all_to_clipboard'] = 'Copy all to clipboard';
$_['text_error_fetching_newsletter_consent_list'] = 'Database query returned no result, this might be because no one has subscribed to the newsletter';

// Iframe settings
$_['entry_shop_terms_uri']          = 'Shop terms';
$_['entry_shop_terms_uri_tooltip']  = 'Link to your shops terms & conditions, the link is sent to Svea and displayed at the bottom of the iframe. If the field is empty, the module will fetch the default terms & conditions page.';
$_['text_iframe_hide_not_you']	    = 'Hide "Not you?"';
$_['text_iframe_hide_not_you_tooltip']      = 'If set to \'Yes\' the \'Not you?\' option in the iframe will be hidden.';
$_['text_iframe_hide_anonymous']	= 'Hide anonymous flow';
$_['text_iframe_hide_anonymous_tooltip']    = 'If set to \'Yes\' the anonymous flow in the iframe will be hidden.';
$_['text_iframe_hide_change_address'] = 'Hide "Change address"-option';
$_['text_iframe_hide_change_address_tooltip'] = 'If set to \'Yes\' the customer won\'t be able to change their address in the iframe.';
$_['text_force_flow'] = 'Force B2B or B2C flow';
$_['text_force_flow_tooltip'] = 'If enabled the B2B or B2C flow will be forced. If B2B flow is forced only company customers will be able to finalize purchases and vice-versa.';
$_['text_require_electronic_id_authentication'] = 'Require electronic id authentication';
$_['text_require_electronic_id_authentication_tooltip'] = 'If enabled all orders will require electronic id authentication by the end-customer.';


// Debug settings
$_['text_debug_warning']                                = 'Warning! Do not change any settings here unless you know what you\'re doing';
$_['text_debug_create_order_on_success_page']           = 'Create order on success page';
$_['text_debug_create_order_on_success_page_tooltip']   = 'Opencart order is created when the customer lands on success page (default:yes)';
$_['text_debug_create_order_on_received_push']          = 'Create order on received push';
$_['text_debug_create_order_on_received_push_tooltip']  = 'Opencart order is created when a callback from Svea is received (default:yes)';
$_['text_debug_simulate_push']                          = 'Simulate push';
$_['text_debug_simulate_push_tooltip']                  = 'If an order hasn\'t been created in Opencart but the order exists in Svea\'s admin, you can enter the checkoutOrderId in the field here and the module will try to create the order in Opencart';
$_['text_debug_simulate_push_button']                   = 'Send push';
$_['text_debug_simulate_push_sent']                     = 'Push was sent!';
$_['text_debug_simulate_push_error']                    = 'An error occurred while receiving the push, check the logs for more information';

// Order statuses
$_['entry_deliver_status']                              = 'Deliver order statuses';
$_['entry_deliver_status_tooltip']                      = 'Setting the order status of a order to one of the order statuses in the list will make the module send a deliver order request to Svea. If the previous status was a deliver order status, no request will be sent.';
$_['entry_cancel_credit_status']                        = 'Cancel/credit order statuses';
$_['entry_cancel_credit_status_tooltip']                = 'Setting the order status of a order to one of the order statuses in the list will make the module send a cancel or credit order request to Svea(depending on if the order is previously delivered or not). If the previous status was a cancel/credit order status, no request will be sent.';

// Error
$_['error_permission']			                = 'Error: You do not have permission to modify the Svea Checkout module!';
$_['error_authorization_data']                  = 'Error: To enable this module you need to add a Checkout merchant id and a Checkout Secret word in the authorization section!';
$_['error_validation_shared_status']            = 'Error: The list of deliver order statuses and the list of cancel/credit order statuses cannot share the same statuses. Please remove the following statuses from one of the lists: ';
$_['error_validation_deliver_status_empty']     = 'Error: The list of deliver order statuses may not be empty!';
$_['error_validation_cancel_credit_status_empty']     = 'Error: The list of cancel/credit order statuses may not be empty!';
