<?php
/*
Plugin Name: SE KeyRanker Free!
Plugin URI: http://www.sekeyranker.com/
Description: Track your Google ranking positions based on your select keywords. Be sure to read the Help/About section after installation!
Version: 1.2.1
Author: Curious Conception
License: GNU General Public License, version 2

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
# Open Export File
if ($_POST['open_expf'] == "Open") {
	if (isset($_REQUEST['key'])) {
		$ii = $_REQUEST['key'];
		$openval = $_POST["keyitem".$ii];
		$file = ABSPATH.'/wp-content/plugins/'.plugin_basename( dirname( __FILE__ )) .'/'.$openval;
		header('Cache-Control: no-cache, must-revalidate');
		header('Pragma: no-cache');
	    header("Content-Type: application/force-download");
		header('Content-Length: ' . filesize($file));
	    header('Content-Disposition: attachment; filename='.basename($file));
		$fn = fopen($file, "rb");
		fpassthru($fn);
	}
}

# Define domains
$SE_garray = array("google.com", "google.ac", "google.ad", "google.ae", "google.com.af", "google.com.ag", "google.com.ai", "google.am", "google.it.ao", "google.com.ar", "google.as", "google.at", "google.com.au", "google.az", "google.ba", "google.com.bd", "google.be", "google.bf", "google.bg", "google.bi", "google.bj", "google.com.bn", "google.com.bo", "google.com.br", "google.bs", "google.co.bw", "google.com.by", "google.com.bz", "google.ca", "google.cc", "google.cd", 
"google.cf", "google.cg", "google.ch", "google.ci", "google.co.ck", "google.cl", "google.cm", "google.cn", "google.com.co", "google.co.cr", "google.com.cu", "google.cz", "google.de", "google.dj", "google.dk", "google.dm", "google.com.do", "google.dz", "google.com.ec", "google.ee", "google.com.eg", "google.es", "google.com.et", "google.fi", "google.com.fj", "google.fm", "google.fr", "google.ga", "google.gd", "google.ge", "google.gf", "google.gg", "google.com.gh", "google.com.gi", 
"google.gl", "google.gm", "google.gp", "google.gr", "google.com.gt", "google.gy", "google.com.hk", "google.hn", "google.hr", "google.ht", "google.hu", "google.co.id", "google.ie", "google.co.il", "google.im", "google.co.in", "google.io", "google.is", "google.it", "google.je", "google.com.jm", "google.jo", "google.co.jp", "google.co.ke", "google.com.kh", "google.ki", "google.kg", "google.co.kr", "google.com.kw", "google.kz", "google.la", "google.com.lb", "google.com.lc", 
"google.li", "google.lk", "google.co.ls", "google.lt", "google.lu", "google.lv", "google.com.ly", "google.co.ma", "google.md", "google.me", "google.mg", "google.mk", "google.ml", "google.mn", "google.ms", "google.com.mt", "google.mu", "google.mv", "google.mw", "google.com.mx", "google.com.my", "google.co.mz", "google.com.na", "google.ne", "google.com.nf", "google.com.ng", "google.com.ni", "google.nl", "google.no", "google.com.np", "google.nr", "google.nu", "google.co.nz", 
"google.com.om", "google.com.pa", "google.com.pe", "google.com.ph", "google.com.pk", "google.pl", "google.pn", "google.com.pr", "google.ps", "google.pt", "google.com.py", "google.com.qa", "google.ro", "google.ru", "google.rw", "google.com.sa", "google.com.sb", "google.sc", "google.se", "google.com.sg", "google.sh", "google.si", "google.sk", "google.com.sl", "google.sn", "google.sm", "google.st", "google.com.sv", "google.td", "google.tg", "google.co.th", "google.com.tj", 
"google.tk", "google.tl", "google.tm", "google.to", "google.com.tr", "google.tt", "google.com.tw", "google.co.tz", "google.com.ua", "google.co.ug", "google.co.uk", "google.us", "google.com.uy", "google.co.uz", "google.com.vc", "google.co.ve", "google.vg", "google.co.vi", "google.com.vn", "google.vu", "google.ws", "google.rs", "google.co.za", "google.co.zm", "google.co.zw");

## Dashboard add
function sekeyranker_add() {
	wp_add_dashboard_widget('se-keyranker', 'SE KeyRanker', 'sekeyranker_display');	
}

## Dashboard display
function sekeyranker_display() {
	echo '<div class="wrap">';
	keyrankbuild();
	echo '<div class="sekr_ftr">
	<div style="float:left; margin-right: 10px; margin-top: 4px;"><form method="post" action="?prcs=2"><input type="submit" class="button-primary" name="submit_sekrrefresh" id="submit_sekrrefresh" value="Refresh"></form></div>
	<div class="sekr_funcmenu"><a href="admin.php?page=sekr_keymanage">Keyword Management</a> | <a href="admin.php?page=sekr_settings">Settings</a> | <a href="admin.php?page=sekr_help">Help/About</a></div>
	<div class="sekr_datamenu"><a href="admin.php?page=sekr_savedata">Save Data</a> | <a href="admin.php?page=sekr_viewdata">View Data</a> | <a href="admin.php?page=sekr_exportdata">Export Data</a></div></div>
	</div>';
	
	# Refresh Cron
	if (!wp_next_scheduled('sekr_refresh_cron')) {
		wp_schedule_event( time(), 'weekly', 'sekr_refresh_cron' );
	}
	# Auto-Save Cron
	if (substr(get_option('sekr-settings'), 1, 1) == 1) {
		if (!wp_next_scheduled('sekr_autosave_cron')) {
			wp_schedule_event( time(), 'weekly', 'sekr_autosave_cron' );
		}
	} else {
		wp_clear_scheduled_hook('sekr_autosave_cron');
	}
}

## Cron Functions
function sekr_autosave() {
	$func = 7; include("func.php");
}

function sekr_refresh() {
	$prcs = 2; include("keyrank.php");
}

## Menu build
function sekr_menu() {
	add_menu_page(__('SE KeyRanker','se-keyrank-menu'), __('SE KeyRanker','se-keyrank-menu'), 'manage_options', 'sekr_keymanage', 'keymanage');
	add_submenu_page('sekr_keymanage', __('Keyword Management','se-keyrank-keymanage'), __('Keyword Management','se-keyrank-keymanage'), 'manage_options', 'sekr_keymanage', 'keymanage');
	add_submenu_page('sekr_keymanage', __('Settings','se-keyrank-settings'), __('Settings','se-keyrank-settings'), 'manage_options', 'sekr_settings', 'settings');
	add_submenu_page('sekr_keymanage', __('Help/About','se-keyrank-help'), __('Help/About','se-keyrank-help'), 'manage_options', 'sekr_help', 'help');
	add_submenu_page('sekr_keymanage', __('Save Data','se-keyrank-savedata'), __('Save Data','se-keyrank-savedata'), 'manage_options', 'sekr_savedata', 'savedata');
	add_submenu_page('sekr_keymanage', __('View Data','se-keyrank-viewdata'), __('View Data','se-keyrank-viewdata'), 'manage_options', 'sekr_viewdata', 'viewdata');
	add_submenu_page('sekr_keymanage', __('Export Data','se-keyrank-exportdata'), __('Export Data','se-keyrank-exportdata'), 'manage_options', 'sekr_exportdata', 'exportdata');
}

## Functions
function keymanage() {
	$func = 1; include("func.php");
}
function settings() {
	$func = 2; include("func.php");
}
function help() {
	$func = 3; include("func.php");
}
function savedata() {
	$func = 4; include("func.php");
}
function viewdata() {
	$func = 5; include("func.php");
}
function exportdata() {
	$func = 6; include("func.php");
}
function keyrankbuild() {
	$prcs = 1; include("keyrank.php");
}

## Hooks
if (is_admin == true) {
	add_action('admin_menu', 'sekr_menu');
	add_action('wp_dashboard_setup', 'sekeyranker_add');
	add_action('sekr_autosave_cron', 'sekr_autosave');
	add_action('sekr_refresh_cron', 'sekr_refresh');
	wp_register_style('sekr_css', get_bloginfo('url').'/wp-content/plugins/'.plugin_basename( dirname( __FILE__ )) .'/styling.css');
	wp_enqueue_style('sekr_css');
}
?>