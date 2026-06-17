<?php

/**
 * @author  Alexander Vakhovski (AlexWaha)
 * @link    https://alexwaha.com
 * @email   support@alexwaha.com
 * @license GPLv3
 */

$_['heading_title']         = '<span style="color:#0937cc;">alexwaha.com</span> - <i class="fa fa-puzzle-piece"></i> Sitemap';
$_['heading_main_title']    = 'alexwaha.com - Sitemap';
$_['text_edit']             = 'Module Settings';

$_['text_home']             = 'Home';
$_['text_extension']        = 'Extensions';
$_['text_success']          = 'Success: Sitemap settings have been saved!';
$_['text_enabled']          = 'Enabled';
$_['text_disabled']         = 'Disabled';
$_['text_mode_dynamic']     = 'Dynamic (on request)';
$_['text_mode_static']      = 'Static (cron, sharded)';
$_['text_threshold_warning'] = 'You have %count% products which exceeds the recommended limit of %threshold% for Dynamic mode. Switch to Static mode and generate the sitemap via cron to avoid timeouts.';

$_['button_save']           = 'Save';
$_['button_cancel']         = 'Back';

$_['tab_general']           = 'General';
$_['tab_providers']         = 'Providers';
$_['tab_cron']              = 'Cron & robots.txt';
$_['tab_support']           = 'Support';

$_['entry_status']          = 'Status';
$_['entry_mode']            = 'Generation mode';
$_['entry_folder']          = 'Output folder';
$_['entry_shard_size']      = 'URLs per shard';
$_['entry_product_images']  = 'Product images';
$_['entry_languages']       = 'Languages';
$_['entry_cache_enabled']   = 'Cache (dynamic mode)';
$_['entry_cache_ttl']       = 'Cache lifetime, sec';
$_['entry_product_threshold'] = 'Dynamic mode product limit';
$_['entry_cron_command']    = 'Cron command';
$_['entry_sitemap_url']     = 'Sitemap URL';
$_['entry_robots_line']     = 'robots.txt line';
$_['entry_rewrite']         = 'Server rewrite (dynamic mode)';

$_['column_provider']       = 'Provider';
$_['column_code']           = 'Code';
$_['column_status']         = 'Status';

$_['help_mode']             = 'Dynamic builds a single sitemap on each request (small catalogs). Static writes sharded files to disk via cron (large catalogs).';
$_['help_folder']           = 'Public folder at the store root where static files are written (letters, digits, dash, underscore).';
$_['help_shard_size']       = 'Maximum number of URLs per sitemap file in static mode (100-50000).';
$_['help_product_images']   = 'Include product images. Only images already present in image/cache are added; images are never resized during generation.';
$_['help_languages']        = 'Languages to generate. Leave all unchecked to generate every store language.';
$_['help_cache_enabled']    = 'Cache the generated XML in dynamic mode and serve it until the lifetime expires.';
$_['help_cache_ttl']        = 'How long the dynamic cache stays valid, in seconds.';
$_['help_product_threshold'] = 'Show a warning when the product count exceeds this value in Dynamic mode.';
$_['help_providers']        = 'Each provider contributes a section to the sitemap. Drop a new file into catalog/controller/extension/aw_sitemap/provider/ to add your own entities.';
$_['help_cron_command']     = 'Add this command to cron (e.g. once a day) to regenerate static sitemap files.';
$_['help_sitemap_url']      = 'Public URL of the generated sitemap index file.';
$_['help_robots_line']      = 'Copy this line into your robots.txt so search engines discover the sitemap.';
$_['help_rewrite']          = 'In Dynamic mode there is no physical file, so the sitemap is served through OpenCart. Add one of these rewrite rules to your server config to expose it at the clean URL above. Static mode writes a real file and needs no rewrite.';

$_['error_permission']      = 'Warning: You do not have permission to modify this module!';
$_['error_warning']         = 'Warning: Please check the form carefully for errors!';
$_['error_folder']          = 'Folder must be 2-64 characters: letters, digits, dash, underscore.';
$_['error_shard_size']      = 'URLs per shard must be between 100 and 50000.';
$_['error_cache_ttl']       = 'Cache lifetime must be 0 or greater.';
