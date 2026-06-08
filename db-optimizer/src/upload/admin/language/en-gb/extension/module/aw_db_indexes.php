<?php

/*
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

// Heading
$_['heading_title'] = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> DB Optimizer';
$_['heading_main_title'] = 'alexwaha.com - DB Optimizer';

// Breadcrumbs
$_['text_home'] = 'Home';
$_['text_extension'] = 'Extensions';
$_['text_edit'] = 'Module Settings';

// Tabs
$_['tab_general'] = 'General';
$_['tab_analysis'] = 'Analysis';
$_['tab_applied'] = 'Applied';
$_['tab_support'] = 'Support';

// General
$_['entry_status'] = 'Status';
$_['entry_min_rows'] = 'Minimum table rows';
$_['entry_max_indexes'] = 'Max new indexes per table';
$_['entry_scope'] = 'Tables scope';
$_['text_scope_all'] = 'All tables';
$_['text_scope_standard'] = 'OpenCart tables (prefix only)';
$_['text_scope_custom'] = 'Custom tables (other prefix)';
$_['text_min_rows_help'] = 'Tables with fewer rows are skipped by the heuristic (indexes give no benefit on tiny tables). Known-good OpenCart indexes are still suggested.';
$_['text_max_indexes_help'] = 'Safety cap: never suggest more than this many new indexes for a single table.';
$_['text_scope_help'] = 'Which tables the analyzer scans.';

// Analysis
$_['text_analyze_intro'] = 'Scan the database for missing indexes and optimization issues. Nothing is changed until you apply a fix.';
$_['text_analyzing'] = 'Analyzing database...';
$_['text_summary'] = 'Scanned %d tables: %d index recommendations, %d without primary key, %d MyISAM, %d fragmented.';
$_['text_recommendations'] = 'Index recommendations';
$_['text_no_recommendations'] = 'No missing indexes found. Your database looks well indexed.';
$_['text_diagnostics'] = 'Diagnostics';
$_['text_col_table'] = 'Table';
$_['text_col_column'] = 'Column';
$_['text_col_index'] = 'Index name';
$_['text_col_rows'] = 'Rows';
$_['text_col_confidence'] = 'Confidence';
$_['text_col_current_indexes'] = 'Current indexes';
$_['text_col_sql'] = 'SQL';
$_['text_col_action'] = 'Action';
$_['text_confidence_curated'] = 'Known OpenCart';
$_['text_confidence_recommended'] = 'Recommended';
$_['text_no_pk'] = 'Tables without PRIMARY KEY';
$_['text_no_pk_help'] = 'A missing primary key hurts performance and replication. Review and add one manually (auto-fix is not safe).';
$_['text_myisam'] = 'MyISAM tables';
$_['text_myisam_help'] = 'InnoDB offers row-level locking and crash recovery. Conversion rewrites the table and may take time on large tables.';
$_['text_fragmented'] = 'Fragmented tables';
$_['text_fragmented_help'] = 'OPTIMIZE TABLE reclaims free space and rebuilds indexes. It locks the table during the operation.';
$_['text_none'] = 'None found.';

// Applied
$_['text_applied_intro'] = 'Indexes created by this module (named with your DB prefix + idx_, e.g. oc_idx_). They can be safely removed at any time.';
$_['text_no_applied'] = 'No module indexes applied yet.';

// Buttons
$_['button_save'] = 'Save';
$_['button_cancel'] = 'Cancel';
$_['button_analyze'] = 'Analyze database';
$_['button_apply_selected'] = 'Apply selected';
$_['button_apply_all'] = 'Apply all recommended';
$_['button_rollback_all'] = 'Rollback all';
$_['button_drop'] = 'Drop';
$_['button_convert'] = 'Convert to InnoDB';
$_['button_convert_all'] = 'Convert all to InnoDB';
$_['button_optimize'] = 'Optimize';
$_['button_optimize_all'] = 'Optimize all';

// Confirmations
$_['text_confirm_convert'] = 'Convert this table to InnoDB? This rewrites the table and may take time on large data.';
$_['text_confirm_convert_all'] = 'Convert all %d MyISAM tables to InnoDB? This processes them one by one and may take a long time on large tables.';
$_['text_confirm_optimize'] = 'Run OPTIMIZE TABLE? The table is locked during the operation.';
$_['text_confirm_optimize_all'] = 'Optimize all %d tables? Each table is locked during its operation.';
$_['text_processing'] = 'Processing %d / %d...';
$_['text_confirm_rollback'] = 'Drop all indexes created by this module?';
$_['text_confirm_drop'] = 'Drop this index?';

// Messages
$_['text_success'] = 'Settings saved successfully!';
$_['text_apply_done'] = 'Fixes applied.';
$_['text_rollback_done'] = 'Rollback complete.';

// Support
$_['text_aw_support'] = 'Need help or a custom OpenCart module? Visit <a href="https://alexwaha.com" target="_blank">alexwaha.com</a> or contact <a href="https://t.me/alexwaha_dev" target="_blank">Telegram</a>.';

// Errors
$_['error_permission'] = 'Warning: You do not have permission to modify this module!';
$_['error_no_actions'] = 'No actions selected.';
$_['error_unknown_action'] = 'Unknown action type.';
