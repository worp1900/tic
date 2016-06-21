<?php

$msgtyp = postOrGet('msgtyp');

$msg = null;
if($msgtyp === 'requestscan') {
	$scamtyp = postOrGet('st');
	$g = postOrGet('g');
	$p = postOrGet('p');
	
	addScanRequest($g, $p, $Benutzer['galaxie'], $Benutzer['planet'], $scamtyp);
	
	$txt = '_' . $Benutzer['galaxie'] . ':' . $Benutzer['planet'] . ' ' . $Benutzer['name'] . '_ requests Scan for *' . $g . ':' . $p .' ' . gnuser($g, $p) . '* of type *';
	$url = '';
	$attachments = array();
	
	$txt .= scanTypeName($scamtyp, false, false);
	switch($scamtyp) {
		case 0: 
			$url = 'http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=sektor';
			break;
		case 1: 
			$url = 'http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=einheit';
			break;
		case 2: 
			$url = 'http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=mili';
			break;
		case 3: 
			$url ='http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=gesch';
			break;
		case 4: 
			$url = 'http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=news&news_kampf=1&news_scan=1&news_spenden=1&news_galaxy=1&news_allianz=1&news_tausch=1';
			break;
		default: 
			$txt .= 'unknown'; 
			break;
	}
	$txt .= '*';
	
	//blocks
	$sql1 = "SET @g = '".mysql_real_escape_string($g)."', @p = '".mysql_real_escape_string($p)."';";
	$sql2 = "SELECT svs, typ, t FROM gn4scanblock WHERE g = '".$g."' AND p = '".$p."' AND suspicious IS NULL ORDER BY svs DESC LIMIT 1";
	//aprint(join("\n\n", array($sql1, $sql2)));
	tic_mysql_query($sql1, __FILE__, __LINE__);
	$res = tic_mysql_query($sql2, __FILE__, __LINE__);
	if(mysql_num_rows($res) > 0) {
		$svs = mysql_result($res, 0, 'svs');
		$typ = mysql_result($res, 0, 'typ');
		$t = mysql_result($res, 0, 't');
		$attachments[] = createSlackAttachment('', 'Block *@' . ZahlZuText($svs) . '* for type *' . scanTypeName($typ, false, false) . '* (' . date('Y-m-d H:i', $t) . ')', null, null, null, '#ff9999');
	}
	$attachments[] = createSlackAttachment('', '<' . $url . '|Make new Scan \'' . scanTypeName($scamtyp, false, false) . '\'>', null, null, null, '#99bb99');

	//send to me for testing.
	//$msg = createSlackMsg($txt, 41, 3, $attachments);
	$msg = createSlackMsg($txt, null, null, $attachments);
}

if(!is_null($msg)) {
	//aprint($msg, 'msg');
	sendToSlack($msg);
	
	$rewrite = '';
	$first = true;
	foreach($_GET as $k => $v) {
		if($k == 'action' || $k == 'msgtyp' || $k == 'st' || $k == 'g' || $k == 'p') {
			continue;
		}
		if($first) {
			$first = false;
			$rewrite .= $k.'='.$v;
		} else {
			$rewrite .= '&'.$k.'='.$v;
		}
	}
	//aprint($rewrite);
	header('Location: main.php?' . $rewrite);
	aprint('<a href="main.php?' . $rewrite . '">weiter</a>');
}
?>