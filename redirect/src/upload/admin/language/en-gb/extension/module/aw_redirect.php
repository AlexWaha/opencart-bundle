<?php

// Heading
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> Redirect Manager';
$_['heading_main_title'] = 'alexwaha.com - Redirect Manager';
$_['text_menu'] = 'Redirect Manager';

// Text
$_['text_extension'] = 'Extensions';
$_['text_home'] = 'Home';
$_['text_list'] = 'Redirect Rules';
$_['text_log'] = '404 Resolver';
$_['text_settings'] = 'Settings';
$_['text_add'] = 'Add Redirect';
$_['text_edit'] = 'Edit Redirect';
$_['text_all'] = '--- All ---';
$_['text_all_stores'] = 'All Stores';
$_['text_exact'] = 'Exact';
$_['text_wildcard'] = 'Wildcard';
$_['text_enabled'] = 'Enabled';
$_['text_disabled'] = 'Disabled';
$_['text_back'] = 'Back';
$_['text_no_results'] = 'No results';
$_['text_log_info'] = 'URLs that returned 404 are logged here (deduplicated, with a hit counter). Map them to a correct URL or redirect them to the homepage.';
$_['text_pagination'] = 'Showing %d to %d of %d (%d Pages)';
$_['text_success'] = 'Success: Settings saved!';
$_['text_success_add'] = 'Success: Redirect added!';
$_['text_success_edit'] = 'Success: Redirect updated!';
$_['text_success_delete'] = 'Success: Selected items deleted!';
$_['text_success_clear'] = 'Success: 404 log cleared!';
$_['text_success_home'] = 'Success: Redirects to homepage created!';
$_['text_import_success'] = 'Success: %d redirects imported!';
$_['text_import_confirm'] = 'Import redirects from this CSV file?';
$_['text_confirm'] = 'Are you sure?';
$_['text_confirm_clear'] = 'Clear the entire 404 log?';
$_['text_confirm_home'] = 'Create 301 redirects to homepage for the selected URLs?';
$_['text_aw_support'] = '<div class="panel panel-success"><div class="panel-heading"><h3 class="panel-title"><i class="fa fa-life-ring"></i> Support</h3></div><div class="panel-body"><p>If you have any questions about the module, please contact the developer:</p><ul><li><strong>Email:</strong> <a href="mailto:support@alexwaha.com">support@alexwaha.com</a></li><li><strong>Official website:</strong> <a href="https://alexwaha.com" target="_blank">alexwaha.com</a></li></ul></div></div>';

// Tabs
$_['tab_general'] = 'General';
$_['tab_import_export'] = 'Import / Export';
$_['tab_support'] = 'Support';

// Columns
$_['column_source'] = 'Source URL';
$_['column_target'] = 'Target URL';
$_['column_type'] = 'Type';
$_['column_code'] = 'Code';
$_['column_hits'] = 'Hits';
$_['column_status'] = 'Status';
$_['column_url'] = 'Requested URL';
$_['column_last_seen'] = 'Last Seen';
$_['column_action'] = 'Action';

// Entry
$_['entry_source'] = 'Source URL';
$_['entry_target'] = 'Target URL';
$_['entry_match_type'] = 'Type';
$_['entry_match_query'] = 'Match Query String';
$_['entry_code'] = 'Redirect Code';
$_['entry_store'] = 'Store';
$_['entry_status'] = 'Status';
$_['entry_default_code'] = 'Default Code';
$_['entry_log_404'] = 'Log 404 Errors';
$_['entry_ignore'] = 'Ignore Patterns';

// Help
$_['help_source'] = 'Path to match, e.g. <code>/old-page</code>. Use <code>*</code> for a wildcard, e.g. <code>/blog/*</code>. Case- and trailing-slash-insensitive.';
$_['help_target'] = 'Relative path (<code>/new-page</code>) or absolute URL (<code>https://...</code>). Not required for code 410.';
$_['help_match_query'] = 'Include the query string in the match (e.g. <code>catalog.php?id=5</code> from another platform).';
$_['help_code'] = '301 permanent, 302 temporary, 410 gone (no redirect).';
$_['help_status'] = 'Master switch for the whole module on the storefront.';
$_['help_default_code'] = 'Pre-selected code when adding a new redirect.';
$_['help_log_404'] = 'Automatically log URLs that return a 404.';
$_['help_ignore'] = 'A 404 URL matching any of these patterns is <strong>not logged</strong> - keeps bot/scanner noise out of the resolver (does not affect redirects). One pattern per line, case-insensitive. <code>*</code> = any characters, <code>?</code> = one character; matched against the full path + query. Examples: <code>*.php</code> (php probes), <code>/wp-*</code> (WordPress probes), <code>*.env</code>, <code>/feed/*</code>.';

// Buttons
$_['button_add'] = 'Add';
$_['button_delete'] = 'Delete';
$_['button_filter'] = 'Filter';
$_['button_save'] = 'Save';
$_['button_cancel'] = 'Cancel';
$_['button_create'] = 'Redirect';
$_['button_clear'] = 'Clear Log';
$_['button_redirect_home'] = 'Redirect to Homepage';
$_['button_export'] = 'Export CSV';
$_['button_import'] = 'Import CSV';

// Import / Export
$_['text_export_description'] = 'Download all redirect rules as a CSV file.';
$_['text_import_description'] = 'Upload a CSV file to bulk-add redirect rules.';
$_['text_import_warning'] = 'New rows are appended. Existing rules are not removed.';

// Error
$_['error_permission'] = 'Warning: You do not have permission to modify the Redirect Manager module!';
$_['error_warning'] = 'Warning: Please check the form carefully for errors!';
$_['error_source'] = 'Source URL is required!';
$_['error_target'] = 'Target URL is required!';
$_['error_code'] = 'Invalid redirect code!';
$_['error_loop'] = 'Target must differ from the source (redirect loop)!';
$_['error_duplicate'] = 'A redirect with this source already exists for this store!';
$_['error_not_found'] = 'Warning: Redirect not found!';
$_['error_import_file'] = 'Warning: Please select a valid CSV file!';
$_['error_import_format'] = 'Warning: Invalid CSV - the first column must be "source"!';
