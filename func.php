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
global $SE_garray;
global $wpdb;

function popkeylist() {
	if (get_option('sekr-kwlist') == null) {
		return "<b>List is currently empty.</b>";
	} else if (get_option('sekr-kwlist') != null) {
		$kwlistval = get_option('sekr-kwlist');
		$ii = 1;
		$strkwlistnew = "";
		while (strpos($kwlistval, ";") != false) {
			$strTemp = substr($kwlistval, 0, strpos($kwlistval, ";"));
			$strkwlistnew .= '<div class="sekr_kwitem"><form method="post" action="?page=sekr_keymanage&key='.$ii.'">Key'.$ii.': <input type="text" size="45" id="keyitem'.$ii.'" name="keyitem'.$ii.'" value="'.$strTemp.'" readonly><input type="submit" class="button-primary" name="del_kw" id="del_kw" value="X"></form></div>';
			$ii++;
			$kwlistval = substr($kwlistval, strpos($kwlistval, ";")+1);
		}
		return $strkwlistnew;
	}
}

function popsettings($strSet) {
	if (get_option('sekr-settings') == null) {
	} else {
		$setlistval = get_option('sekr-settings');
		if ($strSet == "pagination") {
			$setlistval = substr($setlistval, 0, 1);
		} else if ($strSet == "autosave") {
			$setlistval = substr($setlistval, 1, 1);
		}
		if ($setlistval == "1") {
			$setlistval = "checked";
		}
		return $setlistval;
	}
}

function popSE($curSE, $SE_array) {
	$ii = 1;
	$iii = 0;
	$output = "";
	if ($curSE == 1) {
		$opname = 'sekr-segoogle';
	}
	foreach ($SE_array as $key => $val) {
		if ($ii == 1) {
			$output .= "<tr>";
		} else if ($ii == 5) {
			$output .= "</tr>";
			$ii = 1;
		}
		$curOpt = substr(get_option($opname), $iii, 1);
		if ($curOpt == 1) {
			$curOpt = "checked";
		} else {
			$curOpt = "";
		}
		$output .= '<td><input type="checkbox" name="'.str_replace(".", "_", $val).'" '.$curOpt.'> '.$val.'</td>';
		$ii++;
		$iii++;
	}
	return $output;
}

function loadregions($strRegion, $SE_array) {
	$i = 0;
	if ($strRegion == 1) {
		$opname = 'sekr-segoogle';
	}
	$rval = get_option($opname);
	if ($rval == null || strpos($rval, "1") === false) {
		return "<i>None</i>";
	} else {
		$strDoms = "<i>";
		while ($i <= strlen($rval)) {
			if (substr($rval, $i, 1) == 1) {
				foreach ($SE_array as $skey => $sval) {
					if ($skey == $i) {
						$strDoms .= $sval.", ";
					}
				}
			}
			$i++;
		}
		$strDoms = substr($strDoms, 0, strlen($strDoms)-2);
		$strDoms .= "</i>";
		return $strDoms;
	}
}

function setupSEKRData($wpdb, $tname) {
	$sql = "CREATE TABLE " . $tname . " (id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY, keyword VARCHAR(200) NOT NULL, selocal VARCHAR(200) NOT NULL, date DATETIME NOT NULL, rank VARCHAR(20) NOT NULL);";
	require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	dbDelta($sql);
}

if (is_admin == true) {
	if ($_POST['submit_kw'] == "Submit Keyword") {
		$opname = 'sekr-kwlist';
		$val = stripslashes($_POST['addkey']);
		if (get_option($opname) != null) {
			$curval = get_option($opname);
			$fnit = 1;
			$iii = 0;
			while ($fnit !== false) {
				$fnit = strpos($curval, ";", $ios);
				$ios = $fnit+1;
				$iii++;
			}
			if ($iii > 3) {
				return "<b>Sorry, too many keywords, please remove one.</b><hr>";
			}
			$val = $curval.$val.";";
			update_option($opname, $val);
		} else {
			add_option($opname, $val.";", ' ', 'no');
			if (get_option($opname) == null) {
				update_option($opname, $val.";");
			}
		}
	}
	
	if ($_POST['del_kw'] == "X") {
		if (isset($_REQUEST['key'])) {
			$ii = $_REQUEST['key'];
			$sekrkwlist = get_option('sekr-kwlist');
			$kwdelval = stripslashes($_POST["keyitem".$ii]);
			$sekrkwlist = substr($sekrkwlist, 0, strpos($sekrkwlist, $kwdelval)).substr($sekrkwlist, strpos($sekrkwlist, $kwdelval)+strlen($kwdelval)+1);
			update_option('sekr-kwlist', $sekrkwlist);
			
			$tname = $wpdb->prefix."sekr_userdata";
			$wpdb->query("DELETE FROM $tname WHERE keyword = '$kwdelval'");
		}
	}
	
	if ($_POST['del_expf'] == "X") {
		if (isset($_REQUEST['key'])) {
			$ii = $_REQUEST['key'];
			$delval = $_POST["keyitem".$ii];
			unlink(ABSPATH.'/wp-content/plugins/'.plugin_basename( dirname( __FILE__ )) .'/'.$delval);
		}
	}
	
	if ($_POST['submit_sekrsettings'] == "Save Settings") {
		$opname = 'sekr-settings';
		$finalval = "";
		$settings_array = array($_POST['pagination'], $_POST['autosave']);
		foreach ($settings_array as $key => $val) {
			if ($val == "") {
				$finalval .= 0;
			} else {
				$finalval .= 1;
			}
		}
		if (get_option($opname) != null) {
			update_option($opname, $finalval);
		} else {
			add_option($opname, $finalval, ' ', 'no');
			if (get_option($opname) == null) {
				update_option($opname, $finalval);
			}
		}
		
		echo "<p><b>Status:</b> Settings Saved</p><hr>";
	}
	
	if ($_POST['submit_sekrglsave'] == "Save Localization") {
		$se_array = array();
		if ($_POST['submit_sekrglsave'] == "Save Localization") {
			$opname = 'sekr-segoogle';
			$curarray = $SE_garray;
		}
		
		foreach ($curarray as $key => $val) {
			array_push($se_array, $_POST[str_replace(".", "_", $val)]);
		}
		
		$finalval = "";
		foreach ($se_array as $key => $val) {
			if ($val == "") {
				$finalval .= 0;
			} else {
				$finalval .= 1;
			}
		}
		if (get_option($opname) != null) {
			update_option($opname, $finalval);
		} else {
			add_option($opname, $finalval, ' ', 'no');
			if (get_option($opname) == null) {
				update_option($opname, $finalval);
			}
		}
	}
	
	if ($_POST['submit_sekrdsave'] == "Save Current Data" || $func == 7) {
		$tname = $wpdb->prefix."sekr_userdata";
		if ($wpdb->get_var("SHOW TABLES LIKE '$tname'") != $tname) {
			setupSEKRData($wpdb, $tname);
		}
		$kwtmpdata = get_option('sekr-tmpdata');
		if (!$kwtmpdata) {
			if ($func != 7) {
				echo "<p><b>Status:</b> Cannot save. No data available.</p><hr>";
			}
		} else {
			while (strpos($kwtmpdata, "[") !== false) {
				$datakey = substr($kwtmpdata, strpos($kwtmpdata, "[")+1, strpos($kwtmpdata, ";")-1);
				$kwtmpdata = substr($kwtmpdata, strpos($kwtmpdata, ";")+1);
				$datase = substr($kwtmpdata, 0, strpos($kwtmpdata, ";"));
				$kwtmpdata = substr($kwtmpdata, strpos($kwtmpdata, ";")+1);
				$datarank = strip_tags(substr($kwtmpdata, 0, strpos($kwtmpdata, "]")));
				$cdate = date("Y-m-d")." 00:00:00";
				$kwtmpdata = substr($kwtmpdata, strpos($kwtmpdata, "]")+1);

				if ($wpdb->get_var("SELECT date FROM $tname WHERE keyword = '$datakey' AND date = '$cdate' AND selocal = '$datase'") != $cdate) {
					$wpdb->insert($tname, array('keyword' => $datakey, 'selocal' => $datase, 'date' => $cdate, 'rank' => $datarank));
				}
			}
			if ($func != 7) {
				echo "<p><b>Status:</b> Saving complete</p><hr>";
			}
		}
	}
	
	if ($_POST['submit_sekrexport'] == "Export as CSV") {
		$dlm = ";";
		$eol = "\r\n";
		$curpos = 0;
		$fdone = "";
		$avg = 0;
		$priorsel = "";
		$priorkw = "";
		
		$tname = $wpdb->prefix."sekr_userdata";
		$data1s = $wpdb->get_results("SELECT * FROM $tname ORDER BY keyword, selocal");
		foreach ($data1s as $data1) {
			if ($priorkw != $data1->keyword) {
				$data2s = $wpdb->get_results("SELECT * FROM $tname WHERE keyword = '$data1->keyword' ORDER BY keyword, selocal");
				foreach ($data2s as $data2) {
					if ($priorsel != $data2->selocal) {
						$rankings = "";
						$fline = "";
						$iCnt = 0;
						$iTotal = 0;
						$data3s = $wpdb->get_results("SELECT * FROM $tname WHERE keyword = '$data1->keyword' AND selocal = '$data2->selocal' ORDER BY date DESC");
						foreach ($data3s as $data3) {
							if ($data3->rank != "N/A") {
								$iTotal += $data3->rank;
							} else {
								$iTotal += 100;
							}
							$iCnt++;
							if ($iTotal == 100) {
								$avg = "N/A";
							} else {
								$avg = $iTotal/$iCnt;
							}
							$rankings .= substr($data3->date, 0, 10)."(".str_replace(" ", "", $data3->rank).");";
						}
						$rankings = substr($rankings, 0, -1);
						$fline = $data1->keyword.";".$data2->selocal.";".$avg.";".$rankings;
						$fdone .= $fline.$eol;
					}
					$priorsel = $data2->selocal;
				}
			}
			$priorkw = $data1->keyword;
			$priorsel = "";
		}

		$newf = ABSPATH.'/wp-content/plugins/'.plugin_basename( dirname( __FILE__ )) .'/se_keyranker_export('.date("d-m-Y").').csv';
		if(!is_file($newf)) {
			fclose(fopen($newf,"x"));
			fopen($newf, 'r');
			$fhdl = fopen($newf, 'w');
			fwrite($fhdl, $fdone);
			fclose($fhdl);
		} else {
			echo "Error: File already exists for this daily export, please delete from list first.";
		}
	}
	
	wp_enqueue_style('sekr_css');
	if ($func == 1) {
		echo '<div class="wrap"><h2>SE KeyRanker Free! - Keyword Management</h2><div class="sekr_km">
			<div class="sekr_km_left"><h3>Keyword List:</h3><p>';
			echo popkeylist();
			echo '</p></div><div class="sekr_km_right"><form method="post" action="">';
			$curval = get_option('sekr-kwlist');
			$fnit = 1;
			$iii = 0;
			while ($fnit !== false) {
				$fnit = strpos($curval, ";", $ios);
				$ios = $fnit+1;
				$iii++;
			}
			if ($iii > 3) {
				echo 'Add one keyword phrase at a time, no symbols:<input type="text" size="40" id="addkey" name="addkey"><br /><input type="submit" class="button-primary" name="submit_kw" id="submit_kw" value="Submit Keyword" disabled>';
			} else {
				echo 'Add one keyword phrase at a time, no symbols:<input type="text" size="40" id="addkey" name="addkey"><br /><input type="submit" class="button-primary" name="submit_kw" id="submit_kw" value="Submit Keyword">';
			}
			echo '</form></div>
		</div></div>';
	} else if ($func == 2) {
		if ($_POST['submit_sekrgooglelocal'] == "Localization") {
			echo '<div class="wrap"><h2>SE KeyRanker Free! - Google Localization Selection</h2><form method="post" action="">
			<div class="sekrlocalt"><table class="wp-list-table widefat plugins">';
			echo popSE(1, $SE_garray);
			echo '</table><p><input type="submit" class="button-primary" name="submit_sekrglsave" id="submit_sekrglsave" value="Save Localization"></p></form></div></div>';
		} else {
		echo '<div class="wrap"><h2>SE KeyRanker Free! - Settings</h2><form method="post" action="">
		<div class="sekr_settings_gs"><h3>General Settings</h3>
		<table class="wp-list-table widefat plugins">
		<tr><td width="100">Auto Save: </td><td><input name="autosave" type="checkbox" '.popsettings("autosave").'> <i>Weekly automatic saving of results.</i></td></tr>
		</table></div>
		<div class="sekr_settings_sel"><h3>Search Engine and Localization Settings</h3>
		<table class="wp-list-table widefat plugins">
		<tr><td class="sekr_sel_title1">Google: <input type="checkbox" name="google" checked disabled>
		<div class="sekr_sel_item">Regions: <br />'.loadregions(1, $SE_garray).'</div>
		<p><input type="submit" class="button-primary" name="submit_sekrgooglelocal" id="submit_sekrgooglelocal" value="Localization"></p>
		</td>
		
		</table></div>
		<p><input type="submit" class="button-primary" name="submit_sekrsettings" id="submit_sekrsettings" value="Save Settings"></p>
		</form></div>';
		}
	}  else if ($func == 3) {
		echo "<div class='wrap'><h2>SE KeyRanker Free! - Help/About</h2>
		<div class='ha_abo' id='sekr-ha'><h3>About</h3><div>
		<p><span>Name: </span>SE KeyRanker Free! ";
		echo '<input type="button" class="button-primary" name="submit_sekrupgrade" id="submit_sekrupgrade" value="Upgrade to Pro Now!" onclick="javascript:window.open(\'http://www.sekeyranker.com\')">';
		echo "</p>
		<p><span>Website: </span><a href='http://www.sekeyranker.com' target='_blank'>www.sekeyranker.com</a></p>
		<p><span>Version: </span>1.2.1</p>
		<p><span>Supported Wordpress Versions: </span>2.9.2 -> 3.1.3</p>
		<p>Track your Google ranking positions based on your select keywords via your wordpress dashboard! Simply select Google in the SE KeyRanker Settings and then further select the localization.
		Optionally you may even record and export your data for statistic and performance tracking.</p>
		</div></div>
		<div class='ha_abo' id='sekr-ha'><h3>Usage Instructions</h3>
		<div><p><ol>
		<li>Click on menu title SE KeyRanker on the left in your wordpress admin.</li>
		<li>The Keyword Management will open, simply add your keyword phrases and submit. Please note that you can click on the X next to each keyword to delete them.</li>
		<li>In the SE KeyRanker menu selection, click on settings.</li>
		<li>Click Localization within the Google box.</li>
		<li>Tick the boxes of the Localization options you wish to use and then click save. We recommend .com to start with.</li>
		<li>Click on your dashboard and click Refresh to see your results in the SE KeyRanker box!</li>
		</ol>
		<p><div style='font-weight: bold; color: red; font-size: 11px;'>Please note that SE KeyRanker auto-updates your results weekly and that the Refresh button is only if you require interim results or have made adjustments.</div></p>
		</p></div></div>
		<div class='ha_faq' id='sekr-ha'><h3>FAQ</h3>
		<div><p><span>Question:</span> What is the number next to the localization?</p>
		<p><span>Answer:</span> The number displayed is the position your site returned in the search rankings for your specific keyword.</p></div>
		<div><p><span>Question:</span> I changed a setting but see no change?</p>
		<p><span>Answer:</span> After adjusting any settings you'll need to click the refresh button found under your results on the dashboard.</p></div>
		<div><p><span>Question:</span> My site placement is different in my browser than to SE KeyRankers results, what's broken?</p>
		<p><span>Answer:</span> Nothing is broken. Aside from localization selection, where your site is hosted determines what data centre will be used to return the results. Therefore you may notice a slight difference in results when using other methods to check your rankings.</p></div>
		<div><p><span>Question:</span> Why are my results displayed as N/A?</p>
		<p><span>Answer:</span> If your site isn't within the first 100 results for your select keyword the result returns N/A - Not Available.  N/A can also be displayed if you've added a keyword and haven't refreshed for your first result. Also please note that if you record your data and view your keyword average, N/A recordings are ignored.</p></div>
		<div><p><span>Question:</span> How many localization domains can I add at once?</p>
		<p><span>Answer:</span> No limit is currently placed on how many domains can be placed but it's recommended to use localization settings sparingly to avoid long load times or being <a href='#blocked'>blocked by Google for too many searches.</a></p></div>
		<div><p><span>Question:</span> How many keywords can I add?</p>
		<p><span>Answer:</span> SE KeyRanker Free! is currently limited to only three keywords.</p></div>
		<div><p><span>Question:</span> What happens to my saved data if I delete a keyword?</p>
		<p><span>Answer:</span> Deleting a keyword deletes all recorded data for that phrase. Be sure to export before removing a keyword!</p></div>
		<div><p><span>Question:</span> Help! It tells me Google has blocked me! What's happening?</p>
		<p><span>Answer:</span> Please see the section below for indepth information on this subject.</p></div>
		</div>
		<div class='ha_abo' id='sekr-ha'><a name='blocked'><h3>Blocked by Google</h3></a>
		<p>In the event your results have been blocked by Google, please bear in mind this is not a limitation of the plugin but a security measure by Google that is unavoidable.</p>
		<p>The reasons you might have been temporary blocked can be any of the following:
		<ul><li>* Too many localizations selected.</li>
		<li>* You've clicked refresh too often.</li>
		<li>* You have too many sites hosted on the same ip address all using SE KeyRanker to refresh within a short timeframe.</li></ul>
		</p><p>Please be aware that there is no long term penality for this temporary block but we recommend the following:
		<ol><li>Avoid more than 4 Google Localizations at a time.</li>
		<li>Don't click refresh repeatedly non-stop, your results won't differ second to second.</li>
		<li>If you have many sites on the same ip domain, install SE KeyRanker at different intervals to spread out your auto-refresh timer.</li>
		</ol>
		<div></div>
		</div>
		<div class='ha_abo' id='sekr-ha'><h3>Changelog</h3>
		<div>
		<p><b>1.2.1</b>
		<ul><li>* Rank change is now indicated by red for loss, green for gain, black for no change.</li>
		</p>
		<p><b>1.2 - <span style='color: red'>Important Notice: Delete all old data before upgrading to 1.2. Data saving has been reformatted!</span></b> 
		<ul><li>* Drastic performance increase in result returning.</li>
		<li>* Help/About section updated with crucial information.</li>
		<li>* Auto-Refresh and Auto-Save set from daily to weekly.</li>
		<li>* Results returned now as a single number representing your site position.</li>
		<li>* Added notification if your search results have been temporary blocked by Google (see Help/About for explanation).</li></ul></p>
		<p><b>1.1.5</b>
		<ul><li>* Fixed the major Save Data/Refresh bug mentioned in 1.1.4 properly this time. We apologize for the inconvenience!</li>
		<li>* Fixed View Data not displaying results for those keywords that have any N/A localizations.</li>
		<li>* Save Data/Auto Save only saved first localization result per keyword, this is now fixed.</li></ul></p>
		<p><b>1.1.4</b>
		<ul><li>* Adjusted code to skip having to select and save Google as your search engine choice (since there's only one anyway)</li>
		<li>* Fixed major bug causing temp data not to save thus rendering Save Data and Refresh useless.</li>
		<li>* Fixed bug causing files not to save when attempting to Export Data with a download manager.</li></ul></p>
		<p><b>1.1.3</b>
		<ul><li>* Fixed major bug with crashing when loading results for the first time.</li></ul></p>
		<p><b>1.1.2</b>
		<ul><li>* Updated Help/About with usage instructions and website link.</li></ul></p>
		<p><b>1.1</b>
		<ul><li>* Added caching for performance improvement.</li>
		<li>* Added refresh button for manual reloading.</li>
		<li>* Export now works correctly.</li></ul></p></div></div>
		<div class='ha_uni' id='sekr-ha'><h3>Uninstalling</h3>
		<p>Please Note: Uninstalling will delete all SE KeyRanker files, data entries and recorded statistic data. Export data before uninstall if you wish to save results!</p></div></div>
		";
	}  else if ($func == 4) {
		echo '<div class="wrap"><h2>SE KeyRanker Free! - Save Data</h2>
		<p>Saving your key ranking results helps track your progress and provides vital statistics to grade your SEO improvements against. For daily data saving we recommend using SE KeyRankers auto-save setting.</p>
		<p><b>Last save date:</b> ';
		
		$tname = $wpdb->prefix."sekr_userdata";
		if ($wpdb->get_var("SHOW TABLES LIKE '$tname'") != $tname) {
			echo "none";
		} else {
			$lastdate = $wpdb->get_var($wpdb->prepare("SELECT date FROM $tname ORDER by date DESC"));
			if ($lastdate == null) {
				echo "none";
			} else {
				echo substr($lastdate, 0, 10);
			}
		}
		echo '</p><form method="post" action=""><input type="submit" class="button-primary" name="submit_sekrdsave" id="submit_sekrdsave" value="Save Current Data"></form>
		</div>';
	}  else if ($func == 5) {
		echo '<div class="wrap"><h2>SE KeyRanker Free! - View Data</h2>
		<p>View each of your keywords lifetime data for each search engine ranking as well as its ranking average score.</p><div class="sekr-dlayout">';
		$tname = $wpdb->prefix."sekr_userdata";
		if (isset($_REQUEST['sekrkw'])) {
			$sekrkw = urldecode($_REQUEST['sekrkw']);
			$sekrse = $_REQUEST['sekrse'];
			$ctitle = "";
			echo "<h3>Collective Data for Keyword: ".$sekrkw."</h3>";
			$ldata = $wpdb->get_results("SELECT * FROM $tname WHERE keyword = '$sekrkw' AND selocal = '$sekrse' ORDER BY date DESC");
			foreach ($ldata as $lkey => $lval) {
				if (substr($lval->date, 0, 7) != $ctitle) {
					echo "<div class='sekrldit'>".substr($lval->date, 0, 7)."</div>";
				}
				echo "<div class='sekrldi'>".substr($lval->date, 5, 5).": ".$lval->rank."</div>";
				$ctitle = substr($lval->date, 0, 7);
			}
		} else {
			if ($wpdb->get_var("SHOW TABLES LIKE '$tname'") != $tname || $wpdb->get_var("SELECT COUNT(*) FROM $tname") == 0) {
				echo "<b>No recorded data currently available</b>";
			} else {
				$alldata = $wpdb->get_results("SELECT DISTINCT keyword FROM $tname ORDER BY keyword");
				foreach ($alldata as $dkey => $dval) {
					$dkey += 1;
					$tmplocal = "";
					$tmpkey = "";
					$avgcalc = 0;
					$iCnt = 0;
					$iTotal = 0;
					echo "<div class='sekr-ditem'><div><b>Key".$dkey.":</b> ".$dval->keyword."</div>";
					echo "<div><b>View Data:</b><br />";
					$sdata = $wpdb->get_results("SELECT * FROM $tname WHERE keyword = '$dval->keyword' ORDER BY selocal");
					foreach ($sdata as $skey => $sval) {
						if ($tmplocal == "") {
						} else if ($sval->selocal != $tmplocal && $dval->keyword != $tmpkey) {
							if ($avgcalc != 100) {
								echo "<a href='?page=sekr_viewdata&sekrkw=".$skw."&sekrse=".$slc."'>".$slc."</a> (Avg: ".$avgcalc.")<br />";
							} else {
								echo "<a href='?page=sekr_viewdata&sekrkw=".$skw."&sekrse=".$slc."'>".$slc."</a> (Avg: N/A)<br />";
							}
							$iCnt = 0;
							$iTotal = 0;
						}
						$skw = urlencode($dval->keyword);
						$slc = $sval->selocal;
						if ($sval->rank != "N/A") {
							$iTotal += $sval->rank;
						} else {
							$iTotal += 100;
						}
						$iCnt++;
						$avgcalc = $iTotal/$iCnt;
						$tmplocal = $sval->selocal;
						$tmpkeu = $dval->keyword;
					}
					if ($sval->rank != "N/A") {
						echo "<a href='?page=sekr_viewdata&sekrkw=".$skw."&sekrse=".$slc."'>".$slc."</a> (Avg: ".$avgcalc.")<br />";
					} else {
						echo "<a href='?page=sekr_viewdata&sekrkw=".$skw."&sekrse=".$slc."'>".$slc."</a> (Avg: N/A)<br />";
					}
					echo "</div></div>";
				}
			}
		}
		echo '</div></div>';
	}  else if ($func == 6) {
		echo '<div class="wrap"><h2>SE KeyRanker Free! - Export Data</h2>
		<p>Export all current data recorded for your keywords and their rankings. Currently SE KeyRanker only exports data in CSV format.</p>
		<b>File list:</b>';
		$ii = 1;
		foreach (glob(ABSPATH.'/wp-content/plugins/'.plugin_basename( dirname( __FILE__ )) .'/*.csv') as $fname) {
			echo '<form method="post" action="?page=sekr_exportdata&key='.$ii.'"><div style="padding: 3px"><input type="text" size="45" id="keyitem'.$ii.'" name="keyitem'.$ii.'" value="'.substr("$fname", strpos("$fname", "se_keyranker_export")).'" readonly><input type="submit" class="button-primary" name="open_expf" id="open_expf" value="Open"> <input type="submit" class="button-primary" name="del_expf" id="del_expf" value="X"></div>';
			$ii++;
		}
		echo '<br /><br /><form method="post" action=""><input type="submit" class="button-primary" name="submit_sekrexport" id="submit_sekrexport" value="Export as CSV"></form>
		</div>';
	}
}
?>