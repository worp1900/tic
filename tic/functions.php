<?PHP

//addScanRequest(1, 3, 41, 3, 0);

function addScanRequest($g, $p, $req_g, $req_p, $scantyp) {
	$sql1 = "SET 	@ziel_g = '" . mysql_real_escape_string($g) . "', 
					@ziel_p = '" . mysql_real_escape_string($p) . "', 
					@req_g = '" . mysql_real_escape_string($req_g) . "', 
					@req_p = '" . mysql_real_escape_string($req_p) . "', 
					@scantyp = '" . mysql_real_escape_string($scantyp) . "',
					@t = " . time() . ";";
	
	//does an entry already exist
	$sql2 = "INSERT INTO gn4scanrequests (requester_g, requester_p, ziel_g, ziel_p, t, scantyp)
			SELECT * FROM (SELECT @req_g, @req_p, @ziel_g, @ziel_p, @t, @scantyp) tmp
			WHERE NOT EXISTS(
				SELECT * FROM gn4scanrequests 
				WHERE ziel_g = @ziel_g 
					AND ziel_p = @ziel_p 
					AND requester_g = @req_g
					AND requester_p = @req_p
					AND scantyp = @scantyp 
			)";
	//aprint(join("\n\n", array($sql1, $sql2)));
	tic_mysql_query($sql1, __FILE__, __LINE__);
	tic_mysql_query($sql2, __FILE__, __LINE__);
}

function makeRequestScanLink($g, $p, $typ, $url_add) {
	return "main.php?action=slackmsg&msgtyp=requestscan&g=" . $g . "&p=" . $p . "&st=" . $typ . "&" . $url_add;
}

function scanTypeName($type, $short = false, $htmlentities = true) {
	if($short) {
		switch($type) {
			case 0: return 'S';
			case 1: return 'E';
			case 2: return 'M';
			case 3: return 'G';
			case 4: return 'N';
			default: return '<i>u</i>';
		}
	}
	switch($type) {
		case 0: return 'Sektor';
		case 1: return 'Einheiten';
		case 2: return $htmlentities ? htmlentities('Militär') : 'Militär';
		case 3: return $htmlentities ? htmlentities('Geschütze') : 'Geschütze';
		case 4: return 'Nachrichten';
		default: return '<i>unbekannt</i>';
	}
}

//slack
function createSlackAttachment($title, $text, $fieldTitles = null, $fieldValues = null, $short = null, $color = null) {
	$d = '{';
	if(!is_null($color)) {
		$d .= '"color":"'.$color.'",';
	}
	$d .= '"title":"'.str_replace("\"", "\\\"", $title).'"';
	$d .= ',"text":"'.str_replace("\"", "\\\"", $text).'"';
	if(!is_null($short) && $short) {
		$d .= ',"short": true';
	}
	$d .= ',"mrkdwn_in": ["text", "title"]';
	if(is_array($fieldTitles)) {
		$d .= ',"fields":[';
		for($i = 0; $i < min(count($fieldTitles), count($fieldValues)); $i++) {
			$d .= '{"title":"'.str_replace("\"", "\\\"", $fieldTitles[$i]).'"';
			$d .= '"value":"'.str_replace("\"", "\\\"", $fieldValues[$i]).'"}';
		}
		$d .= ']';
	}
	$d .= '}';
	
	return $d;
}

function createSlackMsg($text, $g, $p , $attachments = null) {
	global $SQL_DBConn;
	
	$user = null;
	if(!is_null($g) || !is_null($p)) {
		$sql1 = 'SET @g = "'.$g.'", @p = "'.$p.'";';
		$sql2 = 'SELECT slack_nickname FROM gn4accounts WHERE galaxie = @g AND planet = @p';
		//aprint(join("\n\n", array($sql1, $sql2)));
		tic_mysql_query($sql1, __FILE__, __LINE__);
		$res = tic_mysql_query($sql2, __FILE__, __LINE__);
		
		if(mysql_num_rows($res) == 1) {
			$user = mysql_result($res, 0, 'slack_nickname');
		} else {
			return null;
		}
	}
	
	$d = '{';
	$d .= '"text":"'.str_replace("\"", "\\\"", $text).'"';
	if(!is_null($user)) {
		$d .= ',"channel":"@'.$user.'"';
	}
	if(is_array($attachments)) {
		$d .= ',"attachments":[';
		for($i = 0; $i < count($attachments); $i++) {
			if($i > 0) $d .= ',';
			$d .= $attachments[$i];
		}
		$d .= ']';
	}
	$d .= '}';

	return $d;
}

function sendToSlack($postdata) {
	global $slack_send_token_url;
	
	$url = $slack_send_token_url;
	$post_data = $postdata;

	$c = curl_init();
	curl_setopt($c, CURLOPT_URL, $url);
	curl_setopt($c, CURLOPT_POST, 1);
	curl_setopt($c, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($c, CURLOPT_RETURNTRANSFER, true);

	return curl_exec($c);
}

//mgmt
function postOrGet($name) {
	if(isset($_POST[$name]) && !empty($_POST[$name]))
		return $_POST[$name];
	if(isset($_GET[$name]))
		return $_GET[$name];
	return null;
}

function makeNewsPrettier($inhalt) {
	//@^(\d+):(\d+).+?Flotte (\d).+wird in (\d+:\d+|\d+ Minuten|\d+ Ticks)@mg
	$inhalt = preg_replace('@^(?:Kommandant! )?(\\d+):(\\d+).([\\w-\.äöü]+)@', '<a href="main.php?modul=showgalascans&xgala=${1}&xplanet=${2}"><b>&raquo; ${1}:${2} ${3}</b></a>', $inhalt, -1);
	$inhalt = preg_replace('@^(?:Kommandant! )?([\\w-\.äöü]+).(\\d+):(\\d+)@', '<a href="main.php?modul=showgalascans&xgala=${2}&xplanet=${3}"><b>&raquo; ${2}:${3} ${1}</b></a>', $inhalt, -1);
	$inhalt = preg_replace('@([\\w-\.äöü]+).\((\\d+):(\\d+)\)@', '<a href="main.php?modul=showgalascans&xgala=${2}&xplanet=${3}"><b>&raquo; ${2}:${3} ${1}</b></a>', $inhalt, -1);
	$inhalt = preg_replace('@(Als Grund gab)|(Euch wurden)|(Dem Transfer)@', "\n$1$2$3", $inhalt, -1);
	$inhalt = preg_replace('@[\\t ]+@', ' ', $inhalt, -1);
	return $inhalt;
}

//convert matching output to same tick based time
function getTimeInTicksSimple($data) {
	if(strlen($data['ticks']) > 0) {
		//ticks
		return $data['ticks'];
	} elseif(strlen($data['minuten']) > 0) {
		//Min
		return ceil($data['minuten'] / 15.0 + 0.001);
	} elseif(strlen($data['xstunden']) > 0) {
		//HH:MM:SS
		return ceil(($data['xstunden'] * 60 * 60 + $data['xminuten'] * 60 + $data['xsekunden']) / 60.0 / 15.0 + 0.001);
	} elseif(strlen($data['ystunden']) > 0) {
		//HH:MM
		return ceil(($data['ystunden'] * 60 + $data['yminuten']) / 15.0 + 0.001);
	} elseif(strlen($data['ystunden']) > 0) {
		//HH Std
		return ceil(($data['stunden'] * 60) / 15.0 + 0.001);
	}
	return -1;
}


function addShortUrl($url) {
	global $SQL_DBConn, $pfadzumtick;

	//does thid link exist?
	$sql = 'SELECT uuid FROM gn4shorturls WHERE url LIKE "'.mysql_real_escape_string($url).'"';
	$res = tic_mysql_query($sql);
	$num = mysql_num_rows($res);
	if($num > 0) {
		$id = mysql_result($res, 0, 'uuid');
		tic_mysql_query('UPDATE gn4shorturls SET t = UNIX_TIMESTAMP(NOW()) WHERE uuid = "'.$id.'"');
		return $pfadzumtick . 'main.php?modul=short&id='.$id;
	}

	//nope, create new.
	$id = uniqid(null, true);
	$sql = 'INSERT INTO gn4shorturls (uuid, url, t) VALUES ("'.$id.'", "'.$url.'", UNIX_TIMESTAMP(NOW()))';
	$res = tic_mysql_query($sql, __FILE__, __LINE__);
	return $pfadzumtick . 'main.php?modul=short&id='.$id;
}

function createCopyLink($linktext, $copycontent, $linkattributes = null) {
	$id = 'x'. substr(md5(rand()), 0, 16);
	$r = '';
	$r .= '<textarea id="' . $id . '" style="width: 1px; height: 1px; border: none;">' . $copycontent . '</textarea>';
	$r .= '<a href="#" ' . $linkattributes . ' class="btn" data-clipboard-target="#' . $id . '">' . $linktext . '</a>';

	return $r;
}

function in_array_contains($haystack, $needle) {
	if(!is_array($haystack) || !$needle)
		return false;

	foreach($haystack as $v) {
		$v = mb_convert_encoding($v, 'UTF-8', "auto");
		$needle = mb_convert_encoding($needle, 'UTF-8', "auto");
		if ($v == substr($needle, 0, strlen($v))) {
			return true;
		}
	}

	return false;
}

function xformat($var) {
	return $var;
}
function nformat($number, $totalLen = 0) {
	if(!is_numeric($number))
		return '';
	$out = $number = ZahlZuText($number);
	$lenNum = strlen($out);

	for($lenNum; $lenNum < $totalLen; $lenNum++) {
		$out = " " . $out;
	}

	return $out;
}

function getscannames( $scantype ) {
	$sn = explode( ' ', $scantype );
	$res = '';
	$snarr = array( 'Sektor', 'Einheiten', 'Milit&auml;r', 'Gesch&uuml;tze' );
	for ( $j=0; $j< count( $sn )-1; $j++ ) {
		$idx = $sn[$j];
		if ( $j < count( $sn )-2 )
			$res .= $snarr[ $idx ].' / ';
		else
			$res .= $snarr[ $idx ];
	}
	return $res;
}


function print_r_tree($data)
{
    // capture the output of print_r
    $out = print_r($data, true);

    // replace something like '[element] => <newline> (' with <a href="javascript:toggleDisplay('...');">...</a><div id="..." style="display: none;">
    $out = preg_replace('/([ \t]*)(\[[^\]]+\][ \t]*\=\>[ \t]*[a-z0-9 \t_]+)\n[ \t]*\(/iUe',"'\\1<a href=\"javascript:toggleDisplay(\''.(\$id = substr(md5(rand().'\\0'), 0, 7)).'\');\">\\2</a><div id=\"'.\$id.'\" style=\"display: none;\">'", $out);

    // replace ')' on its own on a new line (surrounded by whitespace is ok) with '</div>
    $out = preg_replace('/^\s*\)\s*$/m', '</div>', $out);

    // print the javascript function toggleDisplay() and then the transformed output
    echo '<script language="Javascript">function toggleDisplay(id) { document.getElementById(id).style.display = (document.getElementById(id).style.display == "block") ? "none" : "block"; }</script>'."\n$out";
}

function aprint($val, $txt = null) {
	echo '<code style="text-align: left; font-size: 8pt;"><pre>';
	if($txt != null) echo '<b>' . $txt . ':</b> ';
	//print_r_tree($val);
	print_r($val);
	echo '</pre></code><br><hr>';
}

function getKampfSimuLinksForTarget($rg, $rp, $linkName) {
	//deffer hat orbbash?
	list($pretick_einschalten) = mysql_fetch_row(tic_mysql_query("SELECT s.glr + s.gmr + s.gsr > 0 FROM gn4scans s WHERE s.type = 3 AND s.rg = '".$rg."' AND s.rp = '".$rp."'"));
	
	//modus=1 att; modus=2 deff
	$sql = 'SELECT
			angreifer_galaxie g,
			angreifer_planet p,
			flugzeit,
			flottennr,
			floor((ankunft - (SELECT MIN(ankunft) FROM gn4flottenbewegungen WHERE ankunft > UNIX_TIMESTAMP(NOW()) AND (verteidiger_galaxie = "'.$rg.'" and verteidiger_planet = "'.$rp.'") AND modus IN (1))) / (15*60)) as tick,
			IF(modus = 1, "a", "d") typ
		FROM gn4flottenbewegungen
		WHERE ankunft > UNIX_TIMESTAMP(NOW()) - flugzeit * 15 * 60 AND (verteidiger_galaxie = "'.$rg.'" and verteidiger_planet = "'.$rp.'") AND modus IN (1, 2)';
	//echo $sql;
	$res = tic_mysql_query($sql) or die(tic_mysql_error(__FILE__,__LINE__));
	$num = mysql_num_rows($res);

	$link = '';

	/*
	//home fleet
	$sql = 'SELECT
			angreifer_galaxie g,
			angreifer_planet p,
			flugzeit,
			flottennr,
			floor((ruckflug_ende - (SELECT MIN(ankunft) FROM gn4flottenbewegungen WHERE ankunft > UNIX_TIMESTAMP(NOW()) AND (verteidiger_galaxie = "'.$rg.'" and verteidiger_planet = "'.$rp.'") AND modus IN (1, 2))) / (15*60)) as tick,
			"d"
			FROM gn4flottenbewegungen WHERE angreifer_galaxie = "' . $rg . '" AND angreifer_planet = "' . $rp . '" ORDER BY flottennr';
	//aprint($sql);
	$res2 = tic_mysql_query($sql) or die(tic_mysql_error(__FILE__,__LINE__));
	$num2 = mysql_num_rows($res2);
	$offset = 0;
	if($num2 > 0) {
		$home_fleets = array(
			0 => 0,
			1 => 0,
			2 => 0
		);

		for($i = 0; $i < $num2; $i++) {
			$f = mysql_result($res2, $i, "flottennr");
			$ankunft = mysql_result($res2, $i, "tick") + 1;
			if($f == 0) {
				//uncertain
				$home_fleets[$offset] = $ankunft;
			} else {
				$home_fleets[$f] = $ankunft;
			}
		}
		for($i = 0; $i < count($home_fleets); $i++) {
			$f = ($i == 0) ? 3 : $i;

			$link .= '&g['.($i).']='.$rg.'&p['.($i).']='.$rp.'&typ['.($i).']=d&f['.($i).']='.$f.'&ankunft['.($i).']='.$home_fleets[$i];
			$offset++;
		}
	} else {
		$link .= '&g[0]='.$rg.'&rp[0]='.$rp;
	}
	*/
	
	//home fleet - quick & dirty
	$offset = 1;
	$sql = 'SELECT (SELECT count(1) FROM gn4flottenbewegungen WHERE angreifer_galaxie = "' . $rg . '" AND angreifer_planet = "' . $rp . '" AND flottennr = 1) as f1, (SELECT count(1) FROM gn4flottenbewegungen WHERE angreifer_galaxie = "' . $rg . '" AND angreifer_planet = "' . $rp . '" AND flottennr = 2) as f2';
	$res2 = tic_mysql_query($sql) or die(tic_mysql_error(__FILE__,__LINE__));
	
	list($f1OnTour, $f2OnTour) = mysql_fetch_row($res2);
	
	$link .= '&g[0]='.$rg.'&p[0]='.$rp.'&typ[0]=d&f[0]=3';
	
	if(!$f1OnTour) {
		$link .= '&g['.($offset).']='.$rg.'&p['.($offset).']='.$rp.'&typ['.($offset).']=d&f['.($offset).']=1';
		$offset++;
	}
	if(!$f2OnTour) {
		$link .= '&g['.($offset).']='.$rg.'&p['.($offset).']='.$rp.'&typ['.($offset).']=d&f['.($offset).']=2';
		$offset++;
	}

	//deffer & atter
	$ticks = 0;
	for($i = 0; $i < $num; $i++) {
		$f = mysql_result($res, $i, "flottennr");
		$g = mysql_result($res, $i, "g");
		$p = mysql_result($res, $i, "p");
		$typ = mysql_result($res, $i, "typ");
		
		$tmptick = mysql_result($res, $i, "tick");
		$ankunft = ($tmptick < 0 ? 0 : $tmptick) + 1;
		
		$dauer = mysql_result($res, $i, "flugzeit");
		$link .= '&g['.($i+$offset).']='.$g.'&p['.($i+$offset).']='.$p.'&typ['.($i+$offset).']='.$typ.'&f['.($i+$offset).']='.$f.'&ankunft['.($i+$offset).']='.$ankunft.'&aufenthalt['.($i+$offset).']='.$dauer;

		$ticks = ($typ == 'a' && ($ankunft + $dauer > $ticks)) ? $ankunft + $dauer -1 : $ticks;
	}

	return '<a href="main.php?modul=kampf&referenz=eintragen&compute=Berechnen&preticks='.$pretick_einschalten.'&ticks='.$ticks.'&num_flotten='.($num + $offset - 1).$link.'#FIGHT">'.$linkName.'</a>'; //oben wäre andrer anker.
}

function GetScans($SQL_DBConn, $galaxie, $planet) {
	$scan_type[0] = 'S';
	$scan_type[1] = 'E';
	$scan_type[2] = 'M';
	$scan_type[3] = 'G';
	$scan_type[4] = 'N';

	$datumx = date('d.m.Y');

	$SQL_Result = tic_mysql_query('SELECT zeit, type FROM `gn4scans` WHERE rg="'.$galaxie.'" AND rp="'.$planet.'" ORDER BY type;') or die(tic_mysql_error(__FILE__,__LINE__));
	//echo "Scan: ".'SELECT * FROM `gn4scans` WHERE rg="'.$galaxie.'" AND rp="'.$planet.'" ORDER BY type;<br />';
	$SQL_Num = mysql_num_rows($SQL_Result);
	if ($SQL_Num == 0)
		return '[-]';
	else {
		$tmp_result = '[';
		for ($n = 0; $n < $SQL_Num; $n++)
		{
			$tmp_result = $tmp_result.$scan_type[mysql_result($SQL_Result, $n, 'type')];
		}
		$tmp_result = $tmp_result.']';
	//    echo "Scan=>$tmp_result<br />";
		return $tmp_result;
	}
	return null;
}

function GetScans2($SQL_DBConn, $galaxie, $planet) {
	$scan_type[0] = 'S';
	$scan_type[1] = 'E';
	$scan_type[2] = 'M';
	$scan_type[3] = 'G';
	$scan_type[4] = 'N';

	$datumx = date('d.m.Y');

	$SQL_Result = tic_mysql_query('SELECT zeit, type FROM `gn4scans` WHERE rg="'.$galaxie.'" AND rp="'.$planet.'" ORDER BY type', __FILE__,__LINE__);
	//echo "Scan: ".'SELECT * FROM `gn4scans` WHERE rg="'.$galaxie.'" AND rp="'.$planet.'" ORDER BY type;<br />';
	$SQL_Num = mysql_num_rows($SQL_Result);
	if ($SQL_Num == 0)
		return '[-]';
	else {
		$tmp_result = '[';
		for ($n = 0; $n < $SQL_Num; $n++)
		{
		   if ($datumx == substr(mysql_result($SQL_Result, $n, 'zeit'),-10)) {
			  $fc1 = "";
			  $fc2 = "";
		   } else {
			  $fc1 = "<FONT COLOR=#FF887F>";
			  $fc2 = "</FONT>";
		   }

		   $tmp_result = $tmp_result.$fc1.$scan_type[mysql_result($SQL_Result, $n, 'type')].$fc2;
		}
		$tmp_result = $tmp_result.']';
	//    echo "Scan=>$tmp_result<br />";
		return $tmp_result;
	}
	return null;
}

function GetUserInfos($id) {
	global $SQL_DBConn;
	$SQL = 'SELECT galaxie, planet, name FROM `gn4accounts` WHERE id ="'.$id.'";';
	$SQL_Result = tic_mysql_query($SQL) or die(tic_mysql_error(__FILE__,__LINE__));
	$SQL_Num = mysql_num_rows($SQL_Result);

	if ($SQL_Num == 0) {
	  return '???';
	}

	$tmp_result = mysql_result($SQL_Result, 0, 'galaxie').':'.mysql_result($SQL_Result, 0, 'planet').' '.mysql_result($SQL_Result, 0, 'name');
	return $tmp_result;
}

function GetUserPts($id) {
	global $SQL_DBConn;
	$SQL= 'SELECT s.pts FROM `gn4accounts` a JOIN gn4scans s ON s.type=0 AND s.rg=a.galaxie AND s.rp=a.planet WHERE a.id ="'.$id.'";';
	$SQL_Result = tic_mysql_query($SQL, __FILE__,__LINE__);
	$SQL_Num = mysql_num_rows($SQL_Result);
	if ($SQL_Num == 0) {
		return 0;
	}

	return mysql_result($SQL_Result, 0, 'pts');
}

function AttPlanerRights($Allianz, $Meta, $Super, $Rechte, $UserMeta, $UserAllianz) {
	if ($Super == 1 && $Rechte == 3) {
		return  true;
	}

	if ($Meta = $UserMeta && $Rechte >= 2) {
		return  true;

	if($Allianz == $UserAllianz && $Rechte >= 1) {
		return  true;
	}

	return  false;
}
// end
}

function LogAction($text, $type = LOG_SYSTEM)
{
	global $Benutzer;
	global $_SERVER;
	tic_mysql_query("INSERT INTO `gn4log` (type, ticid, name, accid, rang, allianz, zeit, aktion, ip) VALUES (".$type.", '".$Benutzer['ticid']."', '".$Benutzer['name']."', '".$Benutzer['id']."', '".$Benutzer['rang']."', '".$Benutzer['allianz']."', '".date("d.m.Y H:i")."', '".addcslashes($text, "\000\x00\n\r'\"\x1a")."', md5('".$_SERVER['REMOTE_ADDR'] ."'))", __FILE__, __LINE__);
}

function ZahlZuText($zahl, $decimals = 0)
{
	if(is_null($zahl))
		return '-';
	return number_format($zahl, $decimals, ',', '.');
}

function TextZuZahl($text)
{
	$zahl = str_replace(',', '', $text);
	$zahl = str_replace('.', '', $zahl);
	return intval($zahl);
}

function CountScans($id)
{
	$SQL_Result = tic_mysql_query('SELECT COUNT(id) FROM `gn4accounts` WHERE id="'.$id.'"', __FILE__,__LINE__);
	$count = mysql_fetch_row($SQL_Result);
	if($count[0])
	{
		tic_mysql_query('UPDATE `gn4accounts` SET scans = scans+1 WHERE id="'.$id.'"', __FILE__,__LINE__);
	}
}

function getime4display( $time_in_min )
{
	global $Benutzer;
	global $displayflag;
	global $Ticks;
	if ($time_in_min < 0)
		$time_in_min=0;
	if (!isset($displayflag))
	{
		$displayflag=0;
		$SQL_Result3 = tic_mysql_query('SELECT zeitformat FROM `gn4accounts` WHERE id="'.$Benutzer['id'].'"') or die(tic_mysql_error(__FILE__,__LINE__));
		$displayflag =  mysql_result($SQL_Result3, 0, 'zeitformat' );
	}
	switch( $displayflag )
	{

		case 1:     // std:min
			$result_std = sprintf("%02d", intval($time_in_min / 60));
			$result_min = sprintf("%02d", intval($time_in_min % 60));
			$result = $result_std.':'.$result_min;
			break;
		case 2:     // ticks
			$result = (int)($time_in_min / $Ticks['lange']);
			break;
	   default:
			$result=$time_in_min;
	   break;


	}
	return $result;
}

function addgnuser($gala, $planet, $name, $kommentare="")
{
	if ($name != "" && is_numeric($planet) && $planet != '' && is_numeric($gala)&& $gala != '')
	{
//            tic_mysql_query("DELETE FROM gn4gnuser WHERE name='".$name."'") or die(tic_mysql_error(__FILE__,__LINE__));
//            tic_mysql_query("DELETE FROM gn4gnuser WHERE gala='".$gala."' AND planet='".$planet."'") or die(tic_mysql_error(__FILE__,__LINE__));
//            tic_mysql_query("INSERT INTO gn4gnuser (gala, planet, name, kommentare, erfasst) VALUES ('".$gala."', '".$planet."', '".$name."', '".$kommentare."', '".time()."')") or die(tic_mysql_error(__FILE__,__LINE__));
	}
}

function gnuser($gala, $planet)
{
	if($gala != "" && $planet != "" && is_numeric($planet)&& is_numeric($gala))
	{
		$SQL_Result = tic_mysql_query('SELECT name FROM `gn4gnuser` WHERE gala="'.$gala.'" AND planet="'.$planet.'"', __FILE__,__LINE__);
		if($user = mysql_fetch_row($SQL_Result))
			return $user[0];

		$SQL_Result = tic_mysql_query('SELECT name FROM `gn4accounts` WHERE galaxie="'.$gala.'" AND planet="'.$planet.'"', __FILE__,__LINE__);
		if($user = mysql_fetch_row($SQL_Result))
			return $user[0];
	}
	return "Unknown?";
}

function eta($time1, $time2 = null)
{
	global $Ticks;
	if($time2 === null)
	{
		$time2 = $time1;
		$time1 = time();
	}
	$eta = ceil((($time2-$time1)/60)/$Ticks['lange']);
	if($eta < 0)
		$eta = 0;
	return $eta;
}

function count_querys($inc = true)
{
	static $querys = 0;
	if($inc)
		$querys++;
	return $querys;
}

function tic_mysql_query($query, $file = null, $line = null)
{
	$GLOBALS['last_sql_query'] = $query;
	$query_result = mysql_query($query, $GLOBALS['SQL_DBConn']);
	if(!$query_result && $file != null)
	{
		die(tic_mysql_error($file, $line));
	}
	count_querys();
	return $query_result;
}

function tic_mysql_error($file = null, $line = null, $log = true)
{
	$re = "<div style=\"text-align:left\"><ul><b>Mysql Fehler".($file != "" ? " in ".$file."(".$line.")" : "").":</b>".($GLOBALS['last_sql_query'] ? "\n<li><b>Query:</b> ".$GLOBALS['last_sql_query']."</li>\n" : "")."<li><b>Fehlermeldung:</b> ".mysql_errno()." - ".mysql_error()."</li>\n</ul></div></body></html>";
	if($log)
		LogAction("<div style=\"text-align:left\"><ul><b>Mysql Fehler".($file != "" ? " in ".$file."(".$line.")" : "").":</b>".($GLOBALS['last_sql_query'] ? "\n<li><b>Query:</b> ".$GLOBALS['last_sql_query']."</li>\n" : "")."<li><b>Fehlermeldung:</b> ".mysql_errno()." - ".mysql_error()."</li>\n</ul></div>", LOG_ERROR);
	return $re;

}

function ConvertDatumToDB($Text) {
	if (strlen($Text) == 10)
		return substr($Text,6,4)."-".substr($Text,3,2)."-".substr($Text,0,2);

	return substr($Text,6,2)."-".substr($Text,3,2)."-".substr($Text,0,2);
}

function ConvertDatumToText($Text) {
	return substr($Text,8,2).".".substr($Text,5,2).".".substr($Text,0,4);
}

function printselect($nr) {
	// ausgabe der Funktion im der Suchseite fuer Ziele
	echo '<td><center><select name=fkt'.$nr.'><option>=</option><option><=</option><option>>=</option></select></center></td>';
}

function OnMouseFlotte($galaxie, $planet, $punkte, $stype) {
	global $ATTOVERALL, $SF, $DF, $PIC, $EF;

	$SQL = "SELECT * FROM gn4scans WHERE rg=".$galaxie." and rp=".$planet." order by type ASC, id DESC;";
	$SQL_Result = tic_mysql_query($SQL) or die(tic_mysql_error(__FILE__,__LINE__));
	$SQL_Num = mysql_num_rows($SQL_Result);

	for ($i=0;$i<15;$i++) {
		$d[$i]="?";
		$sx[$i] = "?";
		$xzeit[$i] = "?";
	}
	$uzeit="";
	$ugen="";
	$gzeit="";
	$ggen ="";

	//blocks
	$sql = "SELECT t, svs, typ FROM gn4scanblock WHERE g='".$galaxie."' AND p='".$planet."' ORDER BY t DESC limit 5";
	$res = tic_mysql_query($sql, $SQL_DBConn);
	$num = mysql_num_rows($res);

	//iterate others
	for ($i = 0; $i < $SQL_Num; $i++) {
		$type = mysql_result($SQL_Result, $i, 'type' );
		if ($punkte >= 0) {
			switch( $type ) {   // scan-type
				case 0:
					$uzeit  = mysql_result($SQL_Result, $i, 'zeit' );
					$ugen   = mysql_result($SQL_Result, $i, 'gen' );
					$xzeit[0] = "<b>S-Scan: ".$uzeit." ".$ugen."%:</b><br>";
					$sx[0] = mysql_result($SQL_Result, $i, 'me' );
					$sx[1] = mysql_result($SQL_Result, $i, 'ke' );
					$sx[2] = round (mysql_result($SQL_Result, $i, 'pts' ) / 1000000,3)." M";

					if ($punkte != 0) {
						if ((mysql_result($SQL_Result, $i, 'pts' ) * $ATTOVERALL) >= $punkte ) {
							$sx[2] .= "  <= Ziel angreifbar";
						} else {
							$sx[2] .= "  (Ziel nicht angreibar; MIN=".(round($punkte / $ATTOVERALL/ 1000000,3))." M)";
						}
					}
					$sx[3] = mysql_result($SQL_Result, $i, 's' );
					$sx[4] = mysql_result($SQL_Result, $i, 'd');
				case 1: // Einheiten
					if ($stype == "") {
						$uzeit  = mysql_result($SQL_Result, $i, 'zeit' );
						$ugen   = mysql_result($SQL_Result, $i, 'gen' );
						$xzeit[1] = "<b>E-Scan: ".$uzeit." ".$ugen."%:</b><br>";
						$d[0]     = mysql_result($SQL_Result, $i, 'sfj' );
						$d[1]     = mysql_result($SQL_Result, $i, 'sfb' );
						$d[2]     = mysql_result($SQL_Result, $i, 'sff' );
						$d[3]     = mysql_result($SQL_Result, $i, 'sfz' );
						$d[4]     = mysql_result($SQL_Result, $i, 'sfkr' );
						$d[5]     = mysql_result($SQL_Result, $i, 'sfsa' );
						$d[6]     = mysql_result($SQL_Result, $i, 'sft' );
						$d[8]     = mysql_result($SQL_Result, $i, 'sfka' );
						$d[9]     = mysql_result($SQL_Result, $i, 'sfsu' );
					}

				case 2: // MilitaerScan
					if ($stype == "M") {
						$uzeit  = mysql_result($SQL_Result, $i, 'zeit' );
						$ugen   = mysql_result($SQL_Result, $i, 'gen' );
						$xzeit[1] = "<b>M-Scan: ".$uzeit." ".$ugen."%:</b><br>";
						$d[0]     = mysql_result($SQL_Result, $i, 'sf1j' )." : ".mysql_result($SQL_Result, $i, 'sf2j' ) ;
						$d[1]     = mysql_result($SQL_Result, $i, 'sf1b' )." : ".mysql_result($SQL_Result, $i, 'sf2b' ) ;
						$d[2]     = mysql_result($SQL_Result, $i, 'sf1f' )." : ".mysql_result($SQL_Result, $i, 'sf2f' ) ;
						$d[3]     = mysql_result($SQL_Result, $i, 'sf1z' )." : ".mysql_result($SQL_Result, $i, 'sf2z') ;
						$d[4]     = mysql_result($SQL_Result, $i, 'sf1kr' )." : ".mysql_result($SQL_Result, $i, 'sf2kr' ) ;
						$d[5]     = mysql_result($SQL_Result, $i, 'sf1sa' )." : ".mysql_result($SQL_Result, $i, 'sf2sa' ) ;
						$d[6]     = mysql_result($SQL_Result, $i, 'sf1t' )." : ".mysql_result($SQL_Result, $i, 'sf2t' ) ;
						$d[8]     = mysql_result($SQL_Result, $i, 'sf1ka' )." : ".mysql_result($SQL_Result, $i, 'sf2ka' ) ;
						$d[9]     = mysql_result($SQL_Result, $i, 'sf1su' )." : ".mysql_result($SQL_Result, $i, 'sf2su' ) ;
					} elseif ($stype == "1" or $stype=="2") {
						$uzeit  = mysql_result($SQL_Result, $i, 'zeit' );
						$ugen   = mysql_result($SQL_Result, $i, 'gen' );
						$xzeit[1] = "<b>Flotte Nr.".$stype.": ".$uzeit." ".$ugen."%:</b><br>";
						$d[0]     = mysql_result($SQL_Result, $i, 'sf'.$stype.'j' );
						$d[1]     = mysql_result($SQL_Result, $i, 'sf'.$stype.'b' );
						$d[2]     = mysql_result($SQL_Result, $i, 'sf'.$stype.'f' );
						$d[3]     = mysql_result($SQL_Result, $i, 'sf'.$stype.'z' );
						$d[4]     = mysql_result($SQL_Result, $i, 'sf'.$stype.'kr' );
						$d[5]     = mysql_result($SQL_Result, $i, 'sf'.$stype.'sa' );
						$d[6]     = mysql_result($SQL_Result, $i, 'sf'.$stype.'t' );
						$d[8]     = mysql_result($SQL_Result, $i, 'sf'.$stype.'ka' );
						$d[9]     = mysql_result($SQL_Result, $i, 'sf'.$stype.'su' );
					}

				case 3: // geschuetz
					$uzeit  = mysql_result($SQL_Result, $i, 'zeit' );
					$ugen   = mysql_result($SQL_Result, $i, 'gen' );
					$xzeit[3] = "<b>G-Scan: ".$uzeit." ".$ugen."%:</b><br>";
					$d[10]     = mysql_result($SQL_Result, $i, 'glo' );
					$d[11]     = mysql_result($SQL_Result, $i, 'glr' );
					$d[12]     = mysql_result($SQL_Result, $i, 'gmr' );
					$d[13]     = mysql_result($SQL_Result, $i, 'gsr' );
					$d[14]     = mysql_result($SQL_Result, $i, 'ga' );
			}//switch
		} else {
			if ($type == 2 and ($punkte == -1 or $punkte == -2)) {
				// Flottenstatus 1 anzeigen:
				$flnr = $punkte * -1;
				$uzeit  = mysql_result($SQL_Result, $i, 'zeit' );
				$ugen   = mysql_result($SQL_Result, $i, 'gen' );
				$xzeit[1] = "<b>Flotte Nr.".$flnr.": ".$uzeit." ".$ugen."%:</b><br>";
				$d[0]     = mysql_result($SQL_Result, $i, 'sf'.$flnr.'j' );
				$d[1]     = mysql_result($SQL_Result, $i, 'sf'.$flnr.'b' );
				$d[2]     = mysql_result($SQL_Result, $i, 'sf'.$flnr.'f' );
				$d[3]     = mysql_result($SQL_Result, $i, 'sf'.$flnr.'z' );
				$d[4]     = mysql_result($SQL_Result, $i, 'sf'.$flnr.'kr' );
				$d[5]     = mysql_result($SQL_Result, $i, 'sf'.$flnr.'sa' );
				$d[6]     = mysql_result($SQL_Result, $i, 'sf'.$flnr.'t' );
				$d[8]     = mysql_result($SQL_Result, $i, 'sf'.$flnr.'ka' );
				$d[9]     = mysql_result($SQL_Result, $i, 'sf'.$flnr.'su' );
			}
		}
	}//for SQL_Result scans

	$output = "";

	//blocks
	if($num > 0) {
		$output .= '<b>Scanblocks:</b><br/>';
		for ($k = 0; $k < $num; $k++) {
			$typ;
			switch(mysql_result($res, $k, 'typ')) {
				case 0:
					$typ = 'Sektor'; break;
				case 1:
					$typ = 'Einheiten'; break;
				case 2:
					$typ = 'Milit&auml;r'; break;
				case 3:
					$typ = 'Gesch&uuml;tze'; break;
				case 4:
					$typ = 'Nachrichten'; break;
				default:
					$typ = '<i>unknown</i>'; break;
			}

			$entry = array(
			'svs' => mysql_result($res, $k, 'svs'),
			't' => mysql_result($res, $k, 't'),
			'typ' => $typ
			);
			$output .= $entry['svs'] . ' SVS, ' . $entry['typ'] . ' (' . date('H:i d.m.Y', $entry['t']) . ')<br/>';
		}
	}

	if  ($xzeit[0] != '?') {
		$output .= $xzeit[0];
		for ($i=0; $i<5; $i++) {
			$output = $output.$EF[$i].": ".$sx[$i]." <br>";
		}
	}

	if  ($xzeit[1] != '?') {
		$output .= $xzeit[1];
		for ($i=0; $i<10; $i++) {
			if ($i != 7) {
				$output = $output.$SF[$i].": ".$d[$i]." <br>";
			}
		}
	}

	if  ($xzeit[3] != '?') {
		$output .= $xzeit[3];
		for ($i=10 ;$i<15; $i++) {
			$r = $i-10;
			$output = $output.$DF[$r].": ".$d[$i]." <br>";
		}
	}

	//latest news (if available)
	$res = tic_mysql_query("select n.genauigkeit gen, n.t newsfrom, e.t, e.typ, e.inhalt
							from gn4scans_news n
							left join gn4scans_news_entries e on e.news_id = n.id
							where n.ziel_g = '".$galaxie."' and n.ziel_p = '".$planet."' and n.t = (select max(t) from gn4scans_news where n.ziel_g = 48 and n.ziel_p = 1)
							order by t desc");
	$num = mysql_num_rows($res);
	if($num > 0) {
		$gen = mysql_result($res, 0, 'gen');
		$tfrom = mysql_result($res, 0, 'newsfrom');
		$output .= '<b>Latest News: '.date('H:i d.m.Y', $tfrom).' '.$gen.'%</b><br/>';
		for($i = 0; $i < $num; $i++) {
			$t = mysql_result($res, $i, 't');
			$typ = mysql_result($res, $i, 'typ');
			$inthalt = mysql_result($res, $i, 'inhalt');
			if(time() - $t < 7*60*60 && in_array_contains(array('Verteidigung', 'Angriff', 'Rückzug', 'Artilleriebeschuss', 'Artilleriesysteme'), $typ)) {
				$output .= date('Y-m-d H:i', $t) . ' '. $typ .'<br>';
			}
		}
	}

	if ($output != '') {
		$output .= '<br>';
	} else {
		$output .= 'No Scans!';
	}

	return $output;
}


function del_attplanlfd($lfd) {
	$SQL = 'DELETE FROM gn4attflotten WHERE lfd ='.$lfd.';';
	$SQL_Result = tic_mysql_query($SQL) or die(tic_mysql_error(__FILE__,__LINE__));

	$SQL = 'DELETE FROM gn4attplanung WHERE lfd ='.$lfd.';';
	$SQL_Result = tic_mysql_query($SQL) or die(tic_mysql_error(__FILE__,__LINE__));
	// echo 'ATT-Ziel Nr. '.$lfd.' geloescht!';
}

function InfoText($Text) {
	$txt = ' onmouseover="return overlib(\''.$Text.'\');" onmouseout="return nd();" ';
	return  $txt;
}

function Get_Scan3($SQL_DBConn,$v_gala,$v_plan, $help, $punkte) {
	$output = OnMouseFlotte($v_gala, $v_plan,$punkte,"");
	$refa ='<a href="./main.php?modul=showgalascans&xgala='.$v_gala.'&xplanet='.$v_plan.'" ';
	$output = $refa.InfoText($output).">".GetScans2($SQL_DBConn, $v_gala, $v_plan)."</a>";
	return $output;
}
function Get_Scan4($SQL_DBConn,$v_gala,$v_plan, $help, $punkte,$flnr) {
	$output = OnMouseFlotte($v_gala, $v_plan,$punkte,$flnr);
	$refa ='<a href="./main.php?modul=showgalascans&xgala='.$v_gala.'&xplanet='.$v_plan.'" ';
	$output = $refa.InfoText($output).">".GetScans2($SQL_DBConn, $v_gala, $v_plan)."</a>";
	return $output;
}


function Get_ScanID($id, $help, $punkte) {
	global $SQL_DBConn;
	$SQL = 'SELECT galaxie, planet, name FROM `gn4accounts` WHERE id ="'.$id.'";';
	$SQL_Result = tic_mysql_query($SQL) or die(tic_mysql_error(__FILE__,__LINE__));
	$SQL_Num = mysql_num_rows($SQL_Result);

	if ($SQL_Num == 0)
		return '???';
	$v_gala = mysql_result($SQL_Result, 0, 'galaxie');
	$v_plan = mysql_result($SQL_Result, 0, 'planet');
	$tmp_result = $v_gala.':'.$v_plan.' '.mysql_result($SQL_Result, 0, 'name');

	$output = OnMouseFlotte($v_gala, $v_plan, $punkte,"");
	$refa ='<a href="./main.php?modul=showgalascans&xgala='.$v_gala.'&xplanet='.$v_plan.'" ';
	$output = $refa.InfoText($output).">".GetScans2($SQL_DBConn, $v_gala, $v_plan)."</a>";
	return $tmp_result.$output;
}

function Get_FlottenNr($id, $help, $flnr) {
      global $SQL_DBConn;
      $SQL = 'SELECT galaxie, planet, FROM `gn4accounts` WHERE id ="'.$id.'";';
		  $SQL_Result = tic_mysql_query($SQL) or die(tic_mysql_error(__FILE__,__LINE__));
		  $SQL_Num = mysql_num_rows($SQL_Result);
		  if ($SQL_Num == 0)
			  return '???';
		  else {
       $v_gala = mysql_result($SQL_Result, 0, 'galaxie');
       $v_plan = mysql_result($SQL_Result, 0, 'planet');

       $output = OnMouseFlotte($v_gala, $v_plan, $flnr*-1,"");
       $refa ='<a href="./main.php?modul=showgalascans&xgala='.$v_gala.'&xplanet='.$v_plan.'" ';
       $output = $refa.InfoText($output).">FL#".$flnr."</a>";
       return $output;
		  }
}



function GetAllianzName($id) {
      global $SQL_DBConn;
      $SQL = 'SELECT tag FROM `gn4accounts` u JOIN gn4allianzen a ON a.id = u.allianz WHERE u.id ="'.$id.'";';
	  $SQL_Result = tic_mysql_query($SQL) or die(tic_mysql_error(__FILE__,__LINE__));

	  if(mysql_num_rows($SQL_Result) == 0) {
		  return '';
	  }
	  return mysql_result($SQL_Result, 0, "tag");
}
include('functions2.php');

?>
