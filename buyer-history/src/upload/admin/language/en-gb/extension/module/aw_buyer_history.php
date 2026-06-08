<?php

$_['heading_title']      = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> Buyer History';
$_['heading_main_title'] = 'alexwaha.com - Buyer History';

$_['text_extension']     = 'Extensions';
$_['text_edit']          = 'Module Settings';
$_['text_success']       = 'Success: Settings saved!';
$_['text_home']          = 'Home';
$_['text_yes']           = 'Yes';
$_['text_no']            = 'No';
$_['text_preview']       = 'Preview';

$_['tab_general']        = 'General';
$_['tab_thresholds']     = 'Thresholds & Colors';
$_['tab_statuses']       = 'Status Colors';
$_['tab_import_export']  = 'Import / Export';
$_['button_export']               = 'Export';
$_['button_import']               = 'Import';
$_['text_import_export_title']    = 'Import / Export settings';
$_['text_import_export_info']     = 'Export your current module settings to a JSON file, or import a previously saved configuration.';
$_['text_loading']                = 'Loading...';
$_['text_export_description']     = 'Download the current configuration as a JSON file.';
$_['text_import_description']     = 'Select a previously exported JSON file to restore settings.';
$_['text_import_warning']         = '<strong>Warning!</strong> Import will overwrite all current settings. This cannot be undone.';
$_['text_import_success']         = 'Settings imported successfully! Page will reload...';
$_['error_import_failed']         = 'Import error: %s';
$_['error_import_read_file']      = 'Unable to read uploaded file';
$_['error_import_file']           = 'Please select a file to import';
$_['help_status_colors'] = 'Assign a colour to each order status. On Customer history page status badges in the expanded row use this colour. Empty value = no colour.';
$_['tab_duplicates']     = 'Duplicates';
$_['tab_display']        = 'Display';
$_['tab_support']        = 'Support';

$_['column_history']     = 'History';
$_['column_duplicates']  = 'Duplicates';

$_['entry_status']           = 'Status';
$_['entry_match_guests']     = 'Match guests by email + phone';
$_['help_match_guests']      = 'Identify repeat guest customers (customer_id=0) by normalized email + telephone.';
$_['entry_tracked_statuses'] = 'Tracked order statuses';
$_['help_tracked_statuses']  = 'Tick statuses that should be counted in the totals and breakdown. Unticked statuses are ignored.';

$_['entry_threshold_mid']    = 'Mid tier threshold (orders)';
$_['entry_threshold_high']   = 'High tier threshold (orders)';
$_['entry_color_low']        = 'Badge: low tier';
$_['entry_color_mid']        = 'Badge: mid tier';
$_['entry_color_high']       = 'Badge: high tier';
$_['entry_color_bg']         = 'Background';
$_['entry_color_text']       = 'Text';

$_['entry_duplicates_enabled'] = 'Enable duplicates detection';
$_['entry_duplicate_window']   = 'Time window';
$_['help_duplicate_window']    = 'Other orders by the same customer within this window are flagged as duplicates.';
$_['entry_duplicate_custom_value'] = 'Custom value';
$_['entry_duplicate_custom_unit']  = 'Custom unit';
$_['text_unit_minutes']        = 'minutes';
$_['text_unit_hours']          = 'hours';
$_['text_unit_days']           = 'days';
$_['text_preset_1h']           = '1h';
$_['text_preset_3h']           = '3h';
$_['text_preset_6h']           = '6h';
$_['text_preset_12h']          = '12h';
$_['text_preset_24h']          = '24h';
$_['text_preset_48h']          = '48h';
$_['text_preset_72h']          = '72h';
$_['text_preset_7d']           = '7 days';
$_['text_preset_custom']       = 'Custom';
$_['entry_duplicate_min']      = 'Min total in window to flag';
$_['help_duplicate_min']       = 'Including the current row. 2 = at least one duplicate exists.';
$_['entry_color_dup']          = 'Badge: duplicate';
$_['entry_duplicate_target']   = 'Link target';
$_['entry_duplicate_max']      = 'Max numbers shown';
$_['help_duplicate_max']       = 'Extra duplicates collapse into a "+N" tooltip.';
$_['text_link_self']           = 'Same window';
$_['text_link_blank']          = 'New tab';

$_['entry_show_history']     = 'Column "Order history"';
$_['entry_show_duplicates']  = 'Column "Duplicates"';

$_['button_save']        = 'Save';
$_['button_cancel']      = 'Cancel';

$_['text_tooltip_total']     = 'Total';
$_['text_tooltip_breakdown'] = 'By status';
$_['text_more']              = 'more';

// Report page
$_['report_menu_label']        = 'Customer history';
$_['report_heading']           = 'Customer history';
$_['report_column_customer']   = 'Customer';
$_['report_column_total']      = 'Orders';
$_['report_column_total_amount'] = 'Total spent';
$_['report_column_avg']        = 'Avg order';
$_['report_column_first']      = 'First order';
$_['report_column_last']       = 'Last order';
$_['report_column_dup']        = 'Duplicates';
$_['report_filter_title']      = 'Filter';
$_['report_filter_search']     = 'Search (email / phone / name)';
$_['report_filter_tier']       = 'Tier';
$_['report_filter_tier_any']   = 'Any tier';
$_['report_filter_tier_low']   = 'Low';
$_['report_filter_tier_mid']   = 'Mid';
$_['report_filter_tier_high']  = 'High';
$_['report_filter_duplicates'] = 'With duplicates only';
$_['report_text_loading']      = 'Loading orders...';
$_['report_text_no_results']   = 'No data';
$_['report_text_has_duplicates'] = 'Has duplicates in window';
$_['report_rows_order']        = 'Order #';
$_['report_rows_status']       = 'Status';
$_['report_rows_items']        = 'Items';
$_['report_rows_total']        = 'Total';
$_['report_rows_date']         = 'Date';
$_['report_rows_action']       = 'Action';
$_['button_settings']          = 'Module settings';
$_['button_apply']             = 'Apply';
$_['button_clear']             = 'Clear';
$_['text_pagination']          = 'Showing %d to %d of %d (%d Pages)';
$_['datetime_format']          = 'Y-m-d H:i';

$_['error_permission']   = 'Warning: You do not have permission to modify this module!';
