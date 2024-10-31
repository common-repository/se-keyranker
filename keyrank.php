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

function pullDomains($strOpt, $strSE) {
	$i = 0;
	$tmparray = array();
	while ($i <= strlen($strOpt)) {
		if (substr($strOpt, $i, 1) == 1) {
			foreach ($strSE as $skey => $sval) {
				if ($skey == $i) {
					array_push($tmparray, $sval);
				}
			}
		}
		$i++;
	}
	return $tmparray;
}

function searchnfo($seurl, $findme) {
	$strOut = "";
	$mysite = $_SERVER['HTTP_HOST'];
	set_time_limit(60); 
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_VERBOSE, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
	curl_setopt($ch, CURLOPT_URL, $seurl);
	$strResult = curl_exec($ch);
	curl_close($ch);

	if (strpos($strResult, "http://sorry.google.com") !== false) {
		echo "<div style='color: red; padding: 5px;'><p>Your results have been temporary blocked by Google.</p>
		<p>Please wait 20 minutes before trying to refresh results again.</p>
		<p>For information on why this possibly happened, please visit SE KeyRankers <a href='admin.php?page=sekr_help#blocked'>Help/About section</a>.</p></div>";
		$strOut = "!!!googlehasblockedresults!!!";
	}
	
	if (strpos($strResult, $findme.$mysite) !== false) {
		$ctp = 0;
		$curpos = 0;
		while (strpos($strResult, $findme, $curpos) !== false) {
			$ctp++;
			$curpos = strpos($strResult, $findme, $curpos);
			if (substr($strResult, $curpos, strlen($findme.$mysite)) === $findme.$mysite) {
				break;
			}
			$curpos += strlen($findme);
		}
		$strOut = $ctp;
	} else {
		$strOut = "N/A";
	}

	return $strOut;
}

function getrank($seval, $kwval) {
	$strOut = "";
	$kwval = urlencode($kwval);
	
	if (strpos($seval, "google") !== false) {
		if ($seval == "google.us") {
			$seurl = "http://www.google.com/search?&hl=en&gl=us&num=100&q=".$kwval."&sa=N";
		} else if ($seval == "google.com") {
			$seurl = "http://www.google.com/search?&hl=en&gl=com&num=100&q=".$kwval."&sa=N";
		} else {
			$seurl = "http://www.".$seval."/search?&hl=en&num=100&q=".$kwval."&sa=N";
		}
		$findme = '<a href="/url?q=http://';
		$strOut = searchnfo($seurl, $findme);
	}
	
	$kwtmpdata = get_option('sekr-tmpdata');
	if ($kwtmpdata != null) {
		$oldrank = strip_tags(gettmpdata($kwtmpdata, $seval, $kwval));
		if ($oldrank == "N/A" && $strVal != "N/A") {
			$strVal = "<span id='rg'>".$strVal."</span>";
		} else if ($oldrank == $strOut) {
			$strOut = "<span>".$strOut."</span>";
		} else if ($oldrank > $strOut) {
			$strOut = "<span id='rg'>".$strOut."</span>";
		} else if ($oldrank < $strOut) {
			$strOut = "<span id='rl'>".$strOut."</span>";
		}
	}

	return $strOut;
}

function gettmpdata($kwtmpdata, $seval, $kwval) {
	while (strpos($kwtmpdata, "[") !== false) {
		$datakey = substr($kwtmpdata, strpos($kwtmpdata, "[")+1, strpos($kwtmpdata, ";")-1);
		$kwtmpdata = substr($kwtmpdata, strpos($kwtmpdata, ";")+1);
		$datase = substr($kwtmpdata, 0, strpos($kwtmpdata, ";"));
		$kwtmpdata = substr($kwtmpdata, strpos($kwtmpdata, ";")+1);
		$datarank = substr($kwtmpdata, 0, strpos($kwtmpdata, "]"));
		$kwtmpdata = substr($kwtmpdata, strpos($kwtmpdata, "]")+1);

		if ($datakey == $kwval && $datase == $seval) {
			break(1);
		}
	}
	return $datarank;
}


if (is_admin == true) {
	wp_enqueue_style('sekr_css');
	if (isset($_REQUEST['prcs'])) {
		$prcs = $_REQUEST['prcs'];
	}
	
	if (get_option('sekr-kwlist') == null) {
		echo 'No keywords added yet, please <a href="admin.php?page=sekr_keymanage">click here</a> to add.';
	} else {
		$kwlist = get_option('sekr-kwlist');
		$kwl_array = array();
		while (strpos($kwlist, ";") != false) {
			array_push($kwl_array, substr($kwlist, 0, strpos($kwlist, ";")));
			$kwlist = substr($kwlist, strpos($kwlist, ";")+1);
		}
		$searray = array();
		$searray = pullDomains(get_option('sekr-segoogle'), $SE_garray);
		
		$sekr_gr_array = array();
		foreach ($kwl_array as $kwkey => $kwval) {
			$keyno = $kwkey + 1;
			echo '<div class="kr_wrap"><div class="kr_title">Key '.$keyno.': <span>'.$kwval.'</span></div>';
			echo '<table class="kr_se">';
			if ($searray == null) {
				echo '<tr><td colspan="2"><div style="padding: 5px 0px 5px 0px; color: red; font-size: 10px;">Localization not set!</div></td></tr>';
			} else {
				foreach ($searray as $seval) {
					$kwtmpdata = get_option('sekr-tmpdata');
					if ($kwtmpdata == null) {
						$prcs = 2;
					}
					if ($prcs == 1) {
						$yourranking = gettmpdata($kwtmpdata, $seval, $kwval);
						echo '<tr><td width="170">'.$seval.'</td><td>: '.$yourranking.'</td></tr>';
					} else if ($prcs == 2) {
						$yourranking = getrank($seval, $kwval);
						if (strpos($yourranking, "!!!googlehasblockedresults!!!") !== false) {
							echo '</table></div>';
							break(2);
						} else {
							echo '<tr><td width="170">'.$seval.'</td><td>: '.$yourranking.'</td></tr>';
							array_push($sekr_gr_array, $kwval.";".$seval.";".$yourranking);
						}
					}
				}
			}
			echo '</table></div>';
		}

		if ($prcs == 2) {
			$strCtmpd = "";
			foreach ($sekr_gr_array as $tmpval) {
				$strCtmpd .= "[".$tmpval."]";
			}
			add_option('sekr-tmpdata', $strCtmpd, ' ', 'no');
			update_option('sekr-tmpdata', $strCtmpd);
		}
	}
}
?>