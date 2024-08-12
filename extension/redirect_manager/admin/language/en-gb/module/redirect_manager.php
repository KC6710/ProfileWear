<?php
//==============================================================================
// Redirect Manager v2023-5-11
// 
// Author: Clear Thinking, LLC
// E-mail: johnathan@getclearthinking.com
// Website: http://www.getclearthinking.com
// 
// All code within this file is copyright Clear Thinking, LLC.
// You may not copy or reuse code within this file without written permission.
//==============================================================================

$_['version'] = 'v2023-5-11';

//------------------------------------------------------------------------------
// Heading
//------------------------------------------------------------------------------
$_['heading_title']						= 'Redirect Manager';

// Backup/Restore Settings
$_['button_backup_settings']			= 'Export CSV';
$_['text_this_will_overwrite_your']		= 'This will overwrite your previous export file. Continue?';
$_['text_backup_saved_to']				= 'Export file saved to your /system/logs/ folder on';
$_['text_view_backup']					= 'View Export File';
$_['text_download_backup_file']			= 'Download Export File';

$_['button_restore_settings']			= 'Import CSV';
$_['text_restore_from_your']			= 'Import from your:';
$_['text_automatic_backup']				= '<b>Automatic Backup</b>, created when this page was loaded:';
$_['text_manual_backup']				= '<b>Manual Backup</b>, created when "Export CSV" was clicked:';
$_['text_backup_file']					= '<b>Backup File:</b>';
$_['button_restore']					= 'Import';
$_['text_this_will_overwrite_settings']	= 'This will overwrite all current settings. Continue?';
$_['text_restoring']					= 'Importing...';
$_['error_invalid_file_data']			= 'Error: invalid file data';
$_['text_settings_restored']			= 'Import successful';

//------------------------------------------------------------------------------
// Settings
//------------------------------------------------------------------------------
$_['tab_settings_and_redirects']		= 'Setting & Redirects';

$_['heading_settings']					= 'Settings';

$_['entry_status']						= 'Status: <div class="help-text">Set the status for the extension as a whole.<br>You <strong>MUST</strong> have renamed the .htaccess.txt file to .htaccess for this extension to work with SEO URLs.</div>';
$_['entry_check_for_updates']			= 'Check For Updates: <div class="help-text">Choose whether to automatically check for updates when the extension admin panel is loaded.</div>';
$_['entry_tooltips']					= 'Tooltips: <div class="help-text">Disable to hide the tooltips that display for each setting.</div>';
$_['entry_sorting']						= 'Sort Redirects By: <div class="help-text">Choose how the list of redirects is sorted.</div>';
$_['entry_filter_from_url']				= 'Filter by From URL: <div class="help-text">Enter a full or partial From URL to filter the redirects against.</div>';
$_['entry_filter_to_url']				= 'Filter by To URL: <div class="help-text">Enter a full or partial To URL to filter the redirects against.</div>';

$_['entry_sort_and_filter']				= '';
$_['button_sort_and_filter']			= 'Sort & Filter';

//------------------------------------------------------------------------------
// Redirects
//------------------------------------------------------------------------------
$_['heading_redirects']					= 'Redirects';
$_['button_reset_all']					= 'Reset All';
$_['help_reset_all']					= 'Reset the "Times Used" value of all redirects. This cannot be undone.';
$_['button_delete_all']					= 'Delete All';
$_['help_delete_all']					= 'Delete all redirects. This cannot be undone.';

$_['column_action']						= 'Action';
$_['column_active']						= 'Active';
$_['column_redirect']					= 'Redirect';
$_['column_response_code']				= 'Response Code';
$_['column_date_start']					= 'Date Start';
$_['column_date_end']					= 'Date End';
$_['column_times_used']					= 'Times Used';

$_['text_from_url']						= 'From URL:';
$_['text_to_url']						= 'To URL:';
$_['text_moved_permanently']			= '301 Moved Permanently';
$_['text_found']						= '302 Found';
$_['text_temporary_redirect']			= '307 Temporary Redirect';

$_['help_table_active']					= 'Select "No" to temporarily disable the redirect';
$_['help_table_from_url']				= 'Enter URLs with http:// or https:// at the beginning. Use an asterisk (the * symbol) to include a wildcard. This will match 0 or more characters for that part of the URL.'; 
$_['help_table_to_url']					= 'Enter URLs with http:// or https:// at the beginning. If using * wildcards in the From URL, you can enter * in the To URL to redirect using the part matched by the wildcard(s) in the From URL.'; 
$_['help_table_response_code']			= 'Choose the response code given when accessing the From URL. If you do not know what these mean, leave this setting at the default "301" code.';
$_['help_table_date_start']				= 'Leave blank or enter 0000-00-00 to have no date restriction';
$_['help_table_date_end']				= 'Leave blank or enter 0000-00-00 to have no date restriction';

$_['placeholder_date_format']			= 'YYYY-MM-DD';

$_['button_add_row']					= 'Add Redirect';

//------------------------------------------------------------------------------
// 404 URLs
//------------------------------------------------------------------------------
$_['tab_page_not_found_404_urls']		= '"Page Not Found" 404 URLs';
$_['heading_page_not_found_404_urls']	= '"Page Not Found" 404 URLs';

$_['entry_record_404s']					= 'Record 404 URLs: <div class="help-text">Choose whether to record a 404 "page not found" error every time it occurs on the front-end. Note that the Redirect Manager "Status" setting must be set to Enabled for this feature to function.</div>';
$_['entry_ignore_ips']					= 'Ignore IP Addresses: <div class="help-text">To filter out bots or other scrapers, enter IP addresses that will NOT be recorded if they visit a 404 URL. Enter as a single IP address (e.g. 111.222.123.255) or as a range (e.g. 111.222.0.0-111.222.255.255), one per line.</div>';
$_['entry_ignore_user_agents']			= 'Ignore User Agents: <div class="help-text">To filter out bots or other scrapers, enter browser User Agents that will NOT be recorded if they visit a 404 URL. Enter one User Agent string per line, exactly as it appears in the "Browser User Agent" column below (e.g. by copying and pasting it).</div>';

$_['column_date_time']					= 'Date & Time';
$_['column_url']						= 'URL';
$_['column_ip']							= 'IP Address';
$_['column_user_agent']					= 'Browser User Agent';

$_['button_show_all_404_urls']			= 'Show All 404 URLs';
$_['help_show_all_404_urls']			= 'By default, only 100 URLs are shown, to speed up page loading. Use this to display all recorded 404 URLs.<br><br>Note: This may take a while to load if there are hundreds or thousands of them recorded.';

$_['button_show_last_100_urls']			= 'Show Last 100 URLs';
$_['button_download_404_list']			= 'Download 404 List';

$_['help_you_will_need_to_reload']		= 'Note: You will need to reload the page to see the added redirect in the Settings & Redirects tab.';
$_['help_add_redirect_delete_404']		= 'Warning: This will permanently delete the 404 record for this URL. Continue?';

//------------------------------------------------------------------------------
// Standard Text
//------------------------------------------------------------------------------
$_['contact_url']						= 'https://www.getclearthinking.com/contact?store_url=' . str_replace('www.', '', $_SERVER['HTTP_HOST']) . '&version=' . VERSION;
$_['copyright']							= '<hr><div class="text-center" style="margin: 15px">' . $_['heading_title'] . ' (' . $_['version'] . ') &copy; <a target="_blank" href="' . $_['contact_url'] . '">Clear Thinking, LLC</a></div>';

$_['standard_autosaving_enabled']		= 'Auto-Saving Enabled';
$_['standard_confirm']					= 'This operation cannot be undone. Continue?';
$_['standard_error']					= '<strong>Error:</strong> You do not have permission to modify ' . $_['heading_title'] . '!';
$_['standard_max_input_vars']			= '<strong>Warning:</strong> The number of settings is close to your <code>max_input_vars</code> server value. You should enable auto-saving to avoid losing any data.';
$_['standard_please_wait']				= 'Please wait...';
$_['standard_saved']					= 'Saved!';
$_['standard_saving']					= 'Saving...';
$_['standard_select']					= '--- Select ---';
$_['standard_success']					= 'Success!';
$_['standard_testing_mode']				= "Your log is too large to open! If you need to archive it, you can download it using the button above.\n\nTo start a new log, (1) click the Clear Log button, (2) reload the admin panel page, then (3) run your test again.";

$_['standard_check_for_updates']		= 'Check For Updates';
$_['standard_contact_clear_thinking']	= 'Contact Clear Thinking';
$_['standard_error_checking']			= 'There was an error checking for the latest version.';
$_['standard_using_latest']				= 'You are using the latest version';
$_['standard_new_version']				= 'A new version is available!';
$_['standard_your_version']				= 'Your Version:';
$_['standard_latest_version']			= 'Latest Version:';
$_['standard_release_notes']			= 'View release notes';
$_['standard_continue']					= 'Continue';
$_['standard_update_warning']			= '<ul><li>Before updating, it is highly recommended to <b>back up your website files</b>.</li><br><li>To update, enter your license key below and click "Update". A license comes with 1 year of free updates, so you may not qualify for the latest version if you are beyond that period. If that is the case, you will be notified after attempting to update.</li><br><li>Updating the extension will <b>overwrite all current extension files</b>. If you have made modifications to any files, make sure you back up your edits before updating.</li><br><li>If any issues occur during or after updating, download and reinstall the extension manually.</li><br><li>If you have lost your license key or download link, you can retrieve them <a target="_blank" href="https://www.getclearthinking.com/downloads/license">on this page</a>.</li></ul><br>';
$_['standard_update']					= 'Update';
$_['standard_updating']					= 'Updating...';
$_['standard_license_key']				= 'License Key:';

$_['standard_module']					= 'Modules';
$_['standard_shipping']					= 'Shipping';
$_['standard_payment']					= 'Payments';
$_['standard_total']					= 'Order Totals';
$_['standard_feed']						= 'Feeds';
?>