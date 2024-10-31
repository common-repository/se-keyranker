<?php
/*
Copyright 2011   Curious Conception
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/
?>
<?php

	if( !defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
		exit();
	 
	delete_option('sekr-segoogle');
	delete_option('sekr-settings');
	delete_option('sekr-kwlist');
	delete_option('sekr-tmpdata');
	
	wp_clear_scheduled_hook('sekr_autosave_cron');
	wp_clear_scheduled_hook('sekr_refresh_cron');
	
	global $wpdb;
	$tname = $wpdb->prefix."sekr_userdata";
	$wpdb->query("DROP TABLE IF EXISTS $tname");
	
?>