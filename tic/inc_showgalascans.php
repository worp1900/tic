<?php
	if(!isset($xgala)) {
		if(isset($_GET['xgala']))
			$xgala = $_GET['xgala'];
		else if(isset($_POST['xgala']))
			$xgala = $_POST['xgala'];
		else
			$xgala = null;
	}

	if(!isset($xplanet)) {
		if(isset($_GET['xplanet']))
			$xplanet = $_GET['xplanet'];
		else if(isset($_POST['xplanet']))
			$xplanet = $_POST['xplanet'];
		else
			$xplanet = null;
	}

	if(!isset($_GET['displaytype']))
		$_GET['displaytype'] = 0;   // einzelner planet = 0 / gala= 1 / query = 2

	$sql='';
	$error_occured=0;
	switch($_GET['displaytype']) {
		case 1: // einzelner planet = 0 / gala= 1 / query = 2
			if ( !isset( $xgala ))
				$error_occured = 3;
			else
				$sql  = 'select *, UNIX_TIMESTAMP(STR_TO_DATE(zeit, "%H:%i %d.%m.%Y")) as t from `gn4scans` where rg='.intval($xgala).' order by rp, type';
			break;
		case 2: // einzelner planet = 0 / gala= 1 / query = 2
			if ( !isset( $_GET['qvar']) )
				$error_occured = 4;
			else if ( !isset( $_GET['qoperator'] ) )
				$error_occured = 5;
			else if ( !isset( $_GET['qval'] ) )
				$error_occured = 6;
			else {
				$tmparr = explode( ',', $_GET['qvar']);
				if ( strcmp( $_GET['qoperator'], "<" ) == 0 )
					$sortdir='ASC';
				else
					$sortdir='DESC';
				$sql = "
					SELECT gn4scans.*, UNIX_TIMESTAMP(STR_TO_DATE(gn4scans.zeit, '%H:%i %d.%m.%Y')) as t FROM `gn4scans`
					".((isset($_GET['qlimit']) && $_GET['qlimit'] > 0)?"LEFT JOIN gn4accounts ON (gn4scans.rg = gn4accounts.galaxie AND gn4scans.rp = gn4accounts.planet)":"")."
					WHERE ".$tmparr[0]." ".$_GET['qoperator']." '".$_GET['qval']."' AND type=".$tmparr[1]."
					".((isset($_GET['qlimit']) && $_GET['qlimit'] > 0)?"AND gn4accounts.name ".($_GET['qlimit'] == 1?"IS":"IS NOT")." NULL":"")."
					ORDER BY ".$tmparr[0]." ".$sortdir.", rg, rp
					LIMIT 10";
			}
			break;
		default:
			if ( !isset( $xgala ) )
				$error_occured = 1;
			else if ( !isset( $xplanet ))
				$error_occured = 2;
			else
				$sql='select *, UNIX_TIMESTAMP(STR_TO_DATE(zeit, "%H:%i %d.%m.%Y")) as t from `gn4scans` where rg='.intval($xgala).' and rp='.intval($xplanet).' order by type';
			break;
	}
	if ( $error_occured > 0){
		echo '<b><font color="#800000">Internal Error ('.$error_occured.')!!! - aborted!</font></b> <br />';
		return;
	}
?>
	<h2>Scanausgaben</h2>
	<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td>
			<table width="100%" cellspacing="3" cellpadding="0">
				<tr class="fieldnormallight">
					<td valign="top" width="30%">
						<form name="form1" method="get" action="./main.php">
							<input type="hidden" name="modul" value="showgalascans" />
							<input type="hidden" name="displaytype" value="0" />
							<table width="100%" cellspacing="0" cellpadding="3">
								<tr><td class="datatablehead">Spezieller Planet</td></tr>
								<tr><td>Gala:Planet <input type="text" name="xgala" size="4" value="<?=(isset($xgala) ? $xgala : "")?>" /> : <input type="text" name="xplanet" size="2" value="<?=(isset($xplanet) ? $xplanet : "")?>" /></td></tr>
								<tr><td align="right"><input type="submit" value="Anzeigen" /></td></tr>
							</table>
						</form>
					</td>
					<td valign="top" width="30%">
						<form name="form2" method="get" action="./main.php">
							<input type="hidden" name="modul" value="showgalascans" />
							<input type="hidden" name="displaytype" value="1" />
							<table width="100%" cellspacing="0" cellpadding="3">
								<tr><td class="datatablehead">Galaxie anzeigen</td></tr>
								<tr><td>Galaxie:<input type="button" name="Verweis" value="&lt;&lt;" onclick="self.location.href='./main.php?modul=showgalascans&amp;action=findgala&amp;displaytype=1&amp;direction=previous&amp;xgala=<?= (isset($xgala) ? $xgala : "") ?>'" /><input type="text" name="xgala" size="4" value="<?= (isset($xgala) ? $xgala : "") ?>" /><input type="button" name="Verweis" value="&gt;&gt;" onclick="self.location.href='./main.php?modul=showgalascans&amp;action=findgala&amp;displaytype=1&amp;direction=next&amp;xgala=<?= (isset($xgala) ? $xgala : "") ?>'" /></td></tr>
								<tr><td align="right"><input type="submit" value="Anzeigen" /></td></tr>
							</table>
						</form>
					</td>
				</tr>
			</table>
		</td>
		</tr>
	</table>

	<br />
<?php
if($xgala) {
	echo '<form method="post" action="main.php?modul=showgalascans&action=newstosimu"><input type="hidden" name="rg" value="'.$xgala.'"/><input type="hidden" name="rp" value="'.$xplanet.'"/><table width="100%">
		<tr class="datatablehead" style="text-align: center;">
			<td>&nbsp;Meta&nbsp;</td>
			<td>&nbsp;Allianz&nbsp;</td>
			<td>&nbsp;Galaxie&nbsp;</td>
			<td>&nbsp;Planet&nbsp;</td>
			<td>&nbsp;Name&nbsp;</td>
			<td>&nbsp;Punkte&nbsp;</td>
			<td>&nbsp;Approx. Exen <i title="Die Extraktoren werden aus Punktedifferenzen berechnet. Hier fließen Steuern nicht mit ein.">(?)</i>&nbsp;</td>
			<td>&nbsp;Approx. Fleetpkt <i title="Die Flottenpunkte werden aus den gesch&auml;tzten Extraktoren berechnet.">(?)</i>&nbsp;</td>
			<td>&nbsp;Asteroiden&nbsp;</td>
			<td>&nbsp;Timestamp&nbsp;</td>
			<td>&nbsp;Mehr&nbsp;</td>
			<td>&nbsp;</td>
		</tr>';
	//				0			1					2				3				4					5				6		7			8	9	10		11
	$sql2 = 'SELECT spieler_name, spieler_galaxie, spieler_planet, spieler_punkte, spieler_asteroiden, spieler_urlaub, exen, allianz_name, meta, t, fleetpkt, valid FROM gn_spieler2 WHERE spieler_galaxie = "' . mysql_real_escape_string($xgala) . '"';
	$res2 = tic_mysql_query($sql2, __FILE__, __LINE__);
	$num2 = mysql_num_rows($res2);
	if($num2 == 0) {
		echo '<tr class="fieldnormallight"><td colspan="11">Diese Galaxie exitiert (wahrscheinlich) nicht.</td></tr>';
	} else {
		$color = false;
		for($k = 0; $k < $num2; $k++) {
			$row = mysql_fetch_row($res2);
			//aprint($row);
			$color = !$color;
			echo'	<tr class="fieldnormal' . ($color ? 'light' : 'dark') . '" ' . ($row[5] == 1 ? 'style="background-color: darkgray" title="Urlaub"' : '') . '>';
			if($k == 0) {
				echo '<td class="fieldnormallight" valign="top" align="center" rowspan="'.$num2.'">&nbsp;'.$row[8].'&nbsp;';
				if($row[8]) echo '<br>&nbsp;<i><a href="main.php?modul=scanliste&meta='.urlencode($row[8]).'%3B">&raquo; Scanliste</a></i>&nbsp;';
				echo '</td>';
				echo '<td class="fieldnormallight" valign="top" align="center" rowspan="'.$num2.'">&nbsp;'.$row[7].'&nbsp;';
				if($row[7]) echo '<br>&nbsp;<i><a href="main.php?modul=scanliste&allianz='.urlencode($row[7]).'%3B">&raquo; Scanliste</a></i>&nbsp;';
				echo '</td>';
				echo '<td class="fieldnormallight" valign="top" align="center" rowspan="'.$num2.'">&nbsp;'.$row[1].'&nbsp;';
				echo '<br>&nbsp;<i><a href="main.php?modul=scanliste&galaxie='.$row[1].'%3B">&raquo; Scanliste</a></i>&nbsp;';
				echo '</td>';
			}
			echo '		<td align="right">&nbsp;<a href="main.php?modul=showgalascans&xgala='.$row[1].'&xplanet='.$row[2].'">&raquo; '.$row[2].'</a>&nbsp;</td>
					<td align="left">&nbsp;'.$row[0].'&nbsp;</td>
					<td align="right">&nbsp;'.ZahlZuText($row[3]).'&nbsp;</td>
					<td align="right">&nbsp;'.($row[11] ? ZahlZuText($row[6]) : '-').'&nbsp;</td>
					<td align="right">&nbsp;'.($row[11] ? ZahlZuText($row[10]) : '-').'&nbsp;</td>
					<td align="right">&nbsp;'.ZahlZuText($row[4]).'&nbsp;</td>
					<td>&nbsp;'.$row[9].'&nbsp;</td>
					<td>&nbsp;<a href="../x/player.php?name='.$row[0].'" target="_blank">&raquo; Statistik</a>&nbsp;</td>
					<td>&nbsp;<a href="#plani'.$row[2].'">&raquo; nach unten</a>&nbsp;</td>
				</tr>';
		}//for each member
	}
		echo '</table><br/><hr/><br/>';
}//galaxieanfrage

if(isset($_GET['displaytype']) && $_GET['displaytype'] === 'news') {

	$newsid = null;
	if(isset($_GET['newsid']) && $_GET['newsid']) {
		$newsid = $_GET['newsid'];
	}

	$rg = $xgala;
	$rp = $xplanet;
	$sql = "select id, t, genauigkeit, erfasser_svs, erfasser_g, erfasser_p from gn4scans_news where ziel_g = '" . mysql_real_escape_string($rg) . "' and ziel_p = '" . mysql_real_escape_string($rp) . "'".($newsid ? ' AND id="'.mysql_real_escape_string($newsid).'"' : '')." ORDER BY t DESC LIMIT 1";
	//aprint($sql);
	$res_news = tic_mysql_query($sql);
	$num_news = mysql_num_rows($res_news);

	if($num_news == 0) {
		echo '<font color="#800000" size="-1"><b>Sorry - Keine News-Scans vorhanden.</b></font>';
	} else {
?>
		<table width="100%">
			<tr>
				<td colspan="15" class="datatablehead">Nachrichten - <?php echo $rg.':'.$rp.' - '.$rname.' ('.gnuser($rg, $rp).')'; ?> - <a href="javascript:history.back();">zur&uuml;ck</a></td>
			</tr>
<?php
		$copystr = '';

		for($k = 0; $k < $num_news; $k++) {
			$id = mysql_result($res_news, $k, 'id' );
			$t = mysql_result($res_news, $k, 't' );
			$gen = mysql_result($res_news, $k, 'genauigkeit' );
			$svs = mysql_result($res_news, $k, 'erfasser_svs' );
			$g = mysql_result($res_news, $k, 'erfasser_g' );
			$p = mysql_result($res_news, $k, 'erfasser_p' );

			if($k == 0) {
				$copystr .= '*News* (' . $svs . 'SVS, ' . $gen . '%, ' . date('H:i d.m.Y', $t) . ') - *' . trim($rname) . ' ' . $rg . ':' . $rp . '* - https://gntic.de/tic/main.php?modul=showgalascans&xgala=' . $rg . '&xplanet=' . $rp . '&displaytype=news&newsid=' . $id;
				$copystr .= '```';
			}

			$output = '';
			$output .= '<tr class="datatablehead">
				<td><b>Newsscan: </b></td>
				<td><b>' . date('Y-m-d H:i', $t) . '</b></td>
				<td><b>' . $gen . '%</b></td>
				<td><b>' . ZahlZuText($svs) . ' SVS</b> - <a  title="Bezahle ' . ZahlZuText(round(8000 * $scanbezahlungfaktor, 0)) . ' Kristall"href="http://www.galaxy-network.net/game/rohstoffe.php?transfer1=' . $g . '&transfer2=' . $p . '&summe=' . round(8000 * $scanbezahlungfaktor, 0) . '&transfer_typ=Kristall&spenden_grund=Scanbezahlung" target="_blank">$ ' . $g . ':' . $p . '</a></td>
				<td>&nbsp;Sim&nbsp;</td>
			</tr>';

			$highlight = array('Verteidigung', 'Angriff', 'Rückzug', 'Artilleriebeschuss', 'Artilleriesysteme');

			$sql = "SELECT id, t, typ, inhalt FROM gn4scans_news_entries WHERE news_id = " . $id . " ORDER BY id ASC";
			$res_news_entries = tic_mysql_query($sql);
			$num_news_entries = mysql_num_rows($res_news_entries);

			$color = true;

			if($num_news_entries == 0) {
				if($id < 155) {
					echo '<tr><td colspan="4">Sorry, aufgrund eines technischen Fehlers sind diese Eintr&auml;ge permanent nicht verf&uuml;bar.</td></tr>';
				} else {
				echo '<tr><td colspan="4">Sorry, keine Eintr&auml;ge gefunden.</td></tr>';
				}
			}

			$fleets = array();
			$fleets_rf = array();
			for($j = 0; $k < $num_news_entries; $k++) {
				$id = mysql_result($res_news_entries, $k, 'id' );
				$t = mysql_result($res_news_entries, $k, 't' );
				$typ = mysql_result($res_news_entries, $k, 'typ' );
				$inhalt = mysql_result($res_news_entries, $k, 'inhalt' );
				$age = ceil((time()-$t)/60);

				//GET SIMULATION RELEVANT FLEET INFORMATION
				$please_add_selection = false;
				$please_add_selection_checked = false;
				$thresh = 450*60;
				if(!in_array_contains(array('Verteidigungsbericht', 'Angriffsbericht'), $typ) && in_array_contains(array('Verteidigung', 'Angriff', 'Rückzug'), $typ)) {
					$please_add_selection = true;
					if(time() - $t < $thresh) {
						$please_add_selection_checked = true;
					}
				}

				//CONVERT REPORT
				if(in_array_contains(array('Angriffsbericht', 'Verteidigungsbericht', 'Artilleriebeschuss', 'Artilleriesysteme'), $typ)) {
					//convert content
					$inhalt = str_replace("\r", "\n", $inhalt);
					$rows = explode("\n\n", trim($inhalt));
					if(count($rows) == 1)
						$rows = explode("\n", trim($inhalt));
					//aprint($rows, 'rows');
					$inhalt = '';
					$inhalt .= '<table border="1">';
					foreach($rows as $r) {
						$inhalt .= '<tr>';
						$columns = explode("\n", trim($r));
						if(count($columns) == 1) {
							$columns = explode("\t", trim($r));
						}
						//aprint($columns, 'columns');
						foreach($columns as $c) {
							$inhalt .= '<td>'.$c.'</td>';
						}
						$inhalt .= '</tr>';
					}
					$inhalt .= '<tr></tr>';
					$inhalt .= '</table>';

					//aprint($inhalt);
				}

				//HIGHLIGHT FLEETS
				if(in_array_contains($highlight, $typ)) {
					$output .= $color ? '<tr bgcolor="#ededdd">' : '<tr bgcolor="#dcdccc">';
				} else {
					$output .= $color ? '<tr class="fieldnormallight">' : '<tr class="fieldnormaldark">';
				}

				//CREATE COPY-STRING
				if(in_array_contains($highlight, $typ) && time() - $t < (2*450+5*15)*60) {
					if(!in_array_contains(array('Artilleriebeschuss', 'Verteidigungsbericht', 'Angriffsbericht'), $typ))
						$copystr .= "\n" . ZahlZuText(round((time()-$t)/60, 0)) . 'min   ' . $typ . ': ' . $inhalt;
					else
						$copystr .= "\n" . ZahlZuText(round((time()-$t)/60, 0)) . 'min   ' . $typ . ': *snip*';
				}

				//MAKE CONTENT PRETTIERT
				//@^(\d+):(\d+).+?Flotte (\d).+wird in (\d+:\d+|\d+ Minuten|\d+ Ticks)@mg
				$inhalt = preg_replace('@^(?:Kommandant! )?(\\d+):(\\d+).([\\w-\.äöü]+)@', '<a href="main.php?modul=showgalascans&xgala=${1}&xplanet=${2}"><b>&raquo; ${1}:${2} ${3}</b></a>', $inhalt, -1);
				$inhalt = preg_replace('@^(?:Kommandant! )?([\\w-\.äöü]+).(\\d+):(\\d+)@', '<a href="main.php?modul=showgalascans&xgala=${2}&xplanet=${3}"><b>&raquo; ${2}:${3} ${1}</b></a>', $inhalt, -1);
				$inhalt = preg_replace('@([\\w-\.äöü]+).\((\\d+):(\\d+)\)@', '<a href="main.php?modul=showgalascans&xgala=${2}&xplanet=${3}"><b>&raquo; ${2}:${3} ${1}</b></a>', $inhalt, -1);
				$inhalt = preg_replace('@(Als Grund gab)|(Euch wurden)|(Dem Transfer)@', "\n$1$2$3", $inhalt, -1);
				$inhalt = preg_replace('@[\\t ]+@', ' ', $inhalt, -1);

				//ACTUAL HTML OUTPUT
				$output .= '<td valign="top">';
				if($t > 0) {
					$output .= date('Y-m-d H:i', $t);
					$output .= ' - ';
					$output .= ZahlZuText($age);
					$output .= 'min';
				} else {
					$output .= '<i>?</i>';
				}

				$output .= '</td>
						<td valign="top" align="left">' . $typ . '</td>
						<td valign="top" align="left" colspan="2"><pre style="font-size: 8pt;">' . $inhalt . '</pre></td>';
				if($please_add_selection) {
					$output .= '<td>&nbsp;<input type="checkbox" ' . ($please_add_selection_checked ? ' checked="checked"' : '') . ' name="news['.$id.']" />&nbsp;</td>';
				}
				$output .= '</tr>';

				$color = !$color;
			}

			mysql_free_result($res_news_entries);
		}

		$copystr .= "```";

		echo '<tr>
				<td class="fieldnormaldark" style="font-weight: bold;">&nbsp;Copy for:&nbsp;</td>
				<td class="fieldnormallight">&nbsp;' . createCopyLink('Slack',  $copystr) . '&nbsp;</td>
				<td colspan="3" class="fieldnormallight" align="right">&nbsp;<input type="submit" value="&raquo; Zur Simulation" />&nbsp;</td>
			</tr>';

		$output .= '<tr class="datatablehead">
				<td colspan="4">&nbsp;</td>
			</tr>
			</table>';

		echo $output;
	}//num news > 0
	echo '</form>';
	mysql_free_result($res_news);

} else {
	//echo "sql=".$sql;
	$SQL_Result = tic_mysql_query( $sql, $SQL_DBConn );
	$count =  mysql_num_rows($SQL_Result);
	if ( $count == 0 ) {
		$sql = "SELECT * FROM gn4scanblock WHERE g = '" . mysql_real_escape_string($_GET['xgala']) . "' AND p = '" . mysql_real_escape_string($_GET['xplanet']) . "' ORDER BY t DESC LIMIT 1";

		$SQL_Result = tic_mysql_query($sql, $SQL_DBConn);
		if(mysql_num_rows($SQL_Result) > 0) {
			$svs = mysql_result($SQL_Result, 0, 'svs');
			$type = mysql_result($SQL_Result, 0, 'typ');

			switch($type) {
				case 0:
					$type = 'Sektor';
					break;
				case 1:
					$type = 'Einheiten';
					break;
				case 2:
					$type = 'Milit&auml;r';
					break;
				case 3:
					$type = 'Gesch&uuml;schtze';
					break;
				case 4:
					$type = 'Nachrichten';
					break;
				default:
					$type = '<i>unbekannt</i>';
			}

			echo '<b><font color="red">Scanblock ' . $type . ' mit ' . ZahlZuText($svs) . ' SVS!</font></b><br/>';
		}

		echo '<font color="#800000" size="-1"><b>Sorry - Keine Scans vorhanden. Vielleicht im <a href="main.php?auto&modul=scanarchiv&xgala='.$_GET['xgala'].'&xplanet='.$_GET['xplanet'].'">&raquo; Archiv</a>.</b></font>';
	} else {
		// all
		$svs_s = '-';
		$svs_g = '-';
		$svs_e = '-';
		$svs_m = '-';
		// sektor
		$pts = '-'; $me  = '-'; $ke  = '-'; $sgen='-'; $szeit='-'; $s='-'; $d='-'; $a='-';
		// unit init
		$ja   = '-'; $bo   = '-'; $fr   = '-'; $ze   = '-'; $kr   = '-'; $sl   = '-'; $tr   = '-'; $ka   = '-'; $ca   = '-'; $ugen='-'; $uzeit='-';
		// mili init
		$ja0  = '-'; $bo0  = '-'; $fr0  = '-'; $ze0  = '-'; $kr0  = '-'; $sl0  = '-'; $tr0  = '-'; $ka0  = '-'; $ca0  = '-'; $mgen='-'; $mzeit='-';
		$ja1  = '-'; $bo1  = '-'; $fr1  = '-'; $ze1  = '-'; $kr1  = '-'; $sl1  = '-'; $tr1  = '-'; $ka1  = '-'; $ca1  = '-';
		$ja2  = '-'; $bo2  = '-'; $fr2  = '-'; $ze2  = '-'; $kr2  = '-'; $sl2  = '-'; $tr2  = '-'; $ka2  = '-'; $ca2  = '-';
		// gscan
		$lo = '-'; $lr = '-'; $mr = '-'; $sr = '-'; $aj = '-'; $ggen='-'; $gzeit='-';
		$rscans = '';

		for ( $i=0; $i<$count; $i++ ) {
			if ( $i<($count-1) )
				$rpnext = mysql_result($SQL_Result, $i+1, 'rp' );
			else
				$rpnext = 999;

			$type = mysql_result($SQL_Result, $i, 'type' );
			$rp = mysql_result($SQL_Result, $i, 'rp' );
			$rg = mysql_result($SQL_Result, $i, 'rg' );
			$rname = gnuser($rg, $rp);
			$rscans .= sprintf( "%d ", $type );
//echo '<br />type='.$type.' - ';
			switch( $type ) {   // scan-type
				case 0: // sektor
					$sek_p = mysql_result($SQL_Result, $i, 'p' );
					$sek_g = mysql_result($SQL_Result, $i, 'g' );
					$szeit	= mysql_result($SQL_Result, $i, 'zeit' );
					$sgen	= mysql_result($SQL_Result, $i, 'gen' );
					$pts	= mysql_result($SQL_Result, $i, 'pts' );
					$me	= mysql_result($SQL_Result, $i, 'me' );
					$ke	= mysql_result($SQL_Result, $i, 'ke' );
					$s	= mysql_result($SQL_Result, $i, 's' );
					$d	= mysql_result($SQL_Result, $i, 'd' );
					$a	= mysql_result($SQL_Result, $i, 'a' );
					$svs_s  = mysql_result($SQL_Result, $i, 'erfasser_svs');
					$st = mysql_result($SQL_Result, $i, 't');
					break;
				case 1: // unit
					$ein_p = mysql_result($SQL_Result, $i, 'p' );
					$ein_g = mysql_result($SQL_Result, $i, 'g' );
					$uzeit	= mysql_result($SQL_Result, $i, 'zeit' );
					$ugen	= mysql_result($SQL_Result, $i, 'gen' );
					$ja	= mysql_result($SQL_Result, $i, 'sfj' );
					$bo	= mysql_result($SQL_Result, $i, 'sfb' );
					$fr	= mysql_result($SQL_Result, $i, 'sff' );
					$ze	= mysql_result($SQL_Result, $i, 'sfz' );
					$kr	= mysql_result($SQL_Result, $i, 'sfkr' );
					$sl	= mysql_result($SQL_Result, $i, 'sfsa' );
					$tr	= mysql_result($SQL_Result, $i, 'sft' );
					$ka	= mysql_result($SQL_Result, $i, 'sfka' );
					$ca	= mysql_result($SQL_Result, $i, 'sfsu' );
					$svs_e  = mysql_result($SQL_Result, $i, 'erfasser_svs');
					$ut = mysql_result($SQL_Result, $i, 't');
					break;
				case 2: // mili-scan
					$mil_p = mysql_result($SQL_Result, $i, 'p' );
					$mil_g = mysql_result($SQL_Result, $i, 'g' );
					$mzeit	= mysql_result($SQL_Result, $i, 'zeit' );
					$mgen	= mysql_result($SQL_Result, $i, 'gen' );
					$ja0	= mysql_result($SQL_Result, $i, 'sf0j' );
					$bo0	= mysql_result($SQL_Result, $i, 'sf0b' );
					$fr0	= mysql_result($SQL_Result, $i, 'sf0f' );
					$ze0	= mysql_result($SQL_Result, $i, 'sf0z' );
					$kr0	= mysql_result($SQL_Result, $i, 'sf0kr' );
					$sl0	= mysql_result($SQL_Result, $i, 'sf0sa' );
					$tr0	= mysql_result($SQL_Result, $i, 'sf0t' );
					$ka0	= mysql_result($SQL_Result, $i, 'sf0ka' );
					$ca0	= mysql_result($SQL_Result, $i, 'sf0su' );
					$ja1	= mysql_result($SQL_Result, $i, 'sf1j' );
					$bo1	= mysql_result($SQL_Result, $i, 'sf1b' );
					$fr1	= mysql_result($SQL_Result, $i, 'sf1f' );
					$ze1	= mysql_result($SQL_Result, $i, 'sf1z' );
					$kr1	= mysql_result($SQL_Result, $i, 'sf1kr' );
					$sl1	= mysql_result($SQL_Result, $i, 'sf1sa' );
					$tr1	= mysql_result($SQL_Result, $i, 'sf1t' );
					$ka1	= mysql_result($SQL_Result, $i, 'sf1ka' );
					$ca1	= mysql_result($SQL_Result, $i, 'sf1su' );
					$ja2	= mysql_result($SQL_Result, $i, 'sf2j' );
					$bo2	= mysql_result($SQL_Result, $i, 'sf2b' );
					$fr2	= mysql_result($SQL_Result, $i, 'sf2f' );
					$ze2	= mysql_result($SQL_Result, $i, 'sf2z' );
					$kr2	= mysql_result($SQL_Result, $i, 'sf2kr' );
					$sl2	= mysql_result($SQL_Result, $i, 'sf2sa' );
					$tr2	= mysql_result($SQL_Result, $i, 'sf2t' );
					$ka2	= mysql_result($SQL_Result, $i, 'sf2ka' );
					$ca2	= mysql_result($SQL_Result, $i, 'sf2su' );
					$svs_m  = mysql_result($SQL_Result, $i, 'erfasser_svs');
					$ziel1 =  mysql_result($SQL_Result, $i, 'ziel1');
					$ziel2 =  mysql_result($SQL_Result, $i, 'ziel2');
					$status1 = mysql_result($SQL_Result, $i, 'status1');
					$status2 = mysql_result($SQL_Result, $i, 'status2');
					$mt = mysql_result($SQL_Result, $i, 't');
					break;
				case 3: // geschtz
					$ges_p = mysql_result($SQL_Result, $i, 'p' );
					$ges_g = mysql_result($SQL_Result, $i, 'g' );
					$gzeit	= mysql_result($SQL_Result, $i, 'zeit' );
					$ggen	= mysql_result($SQL_Result, $i, 'gen' );
					$lo	= mysql_result($SQL_Result, $i, 'glo' );
					$lr	= mysql_result($SQL_Result, $i, 'glr' );
					$mr	= mysql_result($SQL_Result, $i, 'gmr' );
					$sr	= mysql_result($SQL_Result, $i, 'gsr' );
					$aj	= mysql_result($SQL_Result, $i, 'ga' );
					$svs_g  = mysql_result($SQL_Result, $i, 'erfasser_svs');
					$gt = mysql_result($SQL_Result, $i, 't');
					break;
				default:
					echo '????huh?!??? - Ohooooh';
					break;
			}

			if ( $rpnext != $rp ) {
//num archiv

	echo '	<a name="plani'.$rp.'"></a>';
?>
	<table width="100%">
		<tr class="datatablehead">
			<td colspan="15" align="center"><?php echo $rg.':'.$rp.' - '.$rname.' ('.getscannames($rscans).')'; ?> - <a href="https://gntic.de/x/player.php?name=<?=$rname;?>" target="_blank">&raquo; Punkteverlauf</a> - <a href="main.php?modul=kampf&preticks=1&flotten=1&ticks=5&g[0]=<?=$rg;?>&p[0]=<?=$rp;?>&typ[0]=d&g[1]=<?=$Benutzer['galaxie'];?>&p[1]=<?=$Benutzer['planet'];?>&f[1]=0&typ[1]=a&referenz=eintragen&compute=Berechnen">&raquo; Simulieren</a> - <a href="main.php?auto&modul=scanarchiv&xgala=<?php echo $rg; ?>&xplanet=<?php echo $rp; ?>">&raquo;  Archiv</a></td>
		</tr>
		<tr>
			<td class="fieldnormaldark"><b>Punkte</b></td>
			<td class="fieldnormaldark"><b>MetExen</b></td>
			<td class="fieldnormaldark"><b>KrisExen</b></td>
			<td class="fieldnormaldark"><b>Schiffe</b></td>
			<td class="fieldnormaldark"><b>Defensiv</b></td>
			<td colspan="2" bgcolor="#dbdbbb"><b>Copy for IRC</b></td>
			<td colspan="2" bgcolor="#dbdbbb"><b>Copy for Slack</b></td>
			<td class="fieldnormaldark"><b>Genauigkeit</b></td>
			<td class="fieldnormaldark"><b>SVS</b></td>
			<td class="fieldnormaldark"><b>Datum</b></td>
			<td class="fieldnormaldark" title="Ein angemessener Aufschlag von <?=round(($scanbezahlungfaktor-1)*100, 0);?>% ist das Brot des Scanners.">$</td>
			<td class="fieldnormaldark" title="F&uuml;r einen erfolgreichen Scan werden SV/SB ben&ouml;tigt:&#013;* Sektor 1-1.5&#013;* Einheiten/Gesch&uuml;tze 1.5-2.0&#013;* Milit&auml;r/News 2.0-2.5"><b>Scanblocks</b><i>(?)</i></td>
			<td class="fieldnormaldark"><b>News</b></td>
		</tr>
		<tr>
			<td class="fieldnormallight"><?php echo ($pts != '-') ? number_format($pts, 0, ',', '.') : $pts; ?></td>
			<td class="fieldnormallight"><?php echo $me; ?></td>
			<td class="fieldnormallight"><?php echo $ke; ?></td>
			<td class="fieldnormallight"><?php echo $s; ?></td>
			<td class="fieldnormallight"><?php echo $d; ?></td>
<?php
	$sektor = '00,10Sektorscan (01,10 '.$sgen.'%00,10 ) '.$rname.' (01,10'.$rg.':'.$rp.'00,10)'."\n";
	$sektor = $sektor.	'00,01Punkte: 07,01'.(($pts != '-') ? number_format($pts, 0, ',', '.') : $pts).' 00,01Astros: 07,01'.$a."\n";
	$sektor = $sektor.	'00,01Schiffe: 07,01'.$s.' 00,01Gesch\FCtze: 07,01'.$d."\n";
	$sektor = $sektor.	'00,01Metall-Exen: 07,01'.$me.' 00,01Kristall-Exen: 07,01'.$ke."\n";
	$sektor = $sektor.	'00,01Datum: 07,01'.$szeit;

	$sektor_slack = '*Sektor* ('.$svs_s.'SVS, ' . $sgen . '%, ' . $szeit . ') - *'.$rname.' '.$rg.':'.$rp.'* - https://gntic.de/tic/main.php?modul=showgalascans&xgala=' . $rg . '&xplanet=' . $rp . "\n";
	$sektor_slack .= '```Punkte:  ' . nformat($pts) . "\n".'Exen:    M ' . nformat($me) . ', K ' . nformat($ke) . '; Sum ' . nformat($me + $ke) . "\n".'Schiffe: ' . nformat($s) . "\n".'Deff:    ' . nformat($d) . '```';
?>
			<td bgcolor="#fdfddd" colspan="2"><?php echo createCopyLink('Sektor', $sektor);?></td>
			<td bgcolor="#fdfddd" colspan="2"><?php echo createCopyLink('Sektor', $sektor_slack);?></td>
			<td class="fieldnormallight"><?=$sgen;?></td>
			<td class="fieldnormallight"><?=$svs_s;?></td>
			<td class="fieldnormallight"><?=$szeit;?></td>
			<td class="fieldnormallight"><a  title="Bezahle <?=ZahlZuText(round(2000 * $scanbezahlungfaktor, 0));?> Kristall"href="http://www.galaxy-network.net/game/rohstoffe.php?transfer1=<?=$sek_g;?>&transfer2=<?=$sek_p;?>&summe=<?=round(2000 * $scanbezahlungfaktor, 0);?>&transfer_typ=Kristall&spenden_grund=Scanbezahlung" target="_blank"><?=$sek_g ? $sek_g.':'.$sek_p : '';?></a></td>
			<td class="fieldnormallight" rowspan="9" valign="top" align="left" style="font-size: 8pt"><pre>
<?php
	$sql_block = "SELECT * FROM gn4scanblock WHERE g = '" . mysql_real_escape_string($rg) . "' AND p = '" . mysql_real_escape_string($rp) . "' AND suspicious IS NULL ORDER BY t DESC LIMIT 3";
	$res_blocks = tic_mysql_query($sql_block);
	$num_blocks = mysql_num_rows($res_blocks);
	//aprint($sql_block);
	if($num_blocks == 0) {
		echo '-';
	}

	for($j = 0; $j < $num_blocks; $j++) {
		$t = mysql_result($res_blocks, $j, 't' );
		$svs = mysql_result($res_blocks, $j, 'svs' );
		if(!$svs) $svs = '?';
		$type = mysql_result($res_blocks, $j, 'typ' );
		$sbg = mysql_result($res_blocks, $j, 'sg' );
		$sbp = mysql_result($res_blocks, $j, 'sp' );

		echo date('Y-m-d H:i', $t) . ":\n  <b>" . $svs . "</b> SVS\n  Typ ";
		switch($type) {
			case 0:
				echo 'S';
				echo ' <a title="Bezahle '.ZahlZuText(round(2000 * $scanbezahlungfaktor, 0)).' Kristall" href="http://www.galaxy-network.net/game/rohstoffe.php?transfer1='.$sbg.'&transfer2='.$sbp.'&summe='.round(2000 * $scanbezahlungfaktor, 0).'&transfer_typ=Kristall&spenden_grund=Scanbezahlung" target="_blank">$ '.$sbg.':'.$sbp.'</a>';
				break;
			case 1:
				echo 'E';
				echo ' <a title="Bezahle '.ZahlZuText(round(4000 * $scanbezahlungfaktor, 0)).' Kristall" href="http://www.galaxy-network.net/game/rohstoffe.php?transfer1='.$sbg.'&transfer2='.$sbp.'&summe='.round(4000 * $scanbezahlungfaktor, 0).'&transfer_typ=Kristall&spenden_grund=Scanbezahlung" target="_blank">$ '.$sbg.':'.$sbp.'</a>';
				break;
			case 2:
				echo 'M';
				echo ' <a title="Bezahle '.ZahlZuText(round(8000 * $scanbezahlungfaktor, 0)).' Kristall" href="http://www.galaxy-network.net/game/rohstoffe.php?transfer1='.$sbg.'&transfer2='.$sbp.'&summe='.round(8000 * $scanbezahlungfaktor, 0).'&transfer_typ=Kristall&spenden_grund=Scanbezahlung" target="_blank">$ '.$sbg.':'.$sbp.'</a>';
				break;
			case 3:
				echo 'G';
				echo ' <a title="Bezahle '.ZahlZuText(round(4000 * $scanbezahlungfaktor, 0)).' Kristall" href="http://www.galaxy-network.net/game/rohstoffe.php?transfer1='.$sbg.'&transfer2='.$sbp.'&summe='.round(4000 * $scanbezahlungfaktor, 0).'&transfer_typ=Kristall&spenden_grund=Scanbezahlung" target="_blank">$ '.$sbg.':'.$sbp.'</a>';
				break;
			case 4:
				echo 'N';
				echo ' <a title="Bezahle '.ZahlZuText(round(8000 * $scanbezahlungfaktor, 0)).' Kristall" href="http://www.galaxy-network.net/game/rohstoffe.php?transfer1='.$sbg.'&transfer2='.$sbp.'&summe='.round(8000 * $scanbezahlungfaktor, 0).'&transfer_typ=Kristall&spenden_grund=Scanbezahlung" target="_blank">$ '.$sbg.':'.$sbp.'</a>';
				break;
			default:
			break;
		}
		echo "\n\n";
	}
	echo '</pre>';
	mysql_free_result($res_blocks);
?>
			</td>
			<td class="fieldnormallight" rowspan="9" valign="top" align="left" style="font-size: 8pt">
<?php
	$sql = "select * from gn4scans_news where ziel_g = '" . mysql_real_escape_string($rg) . "' and ziel_p = '" . mysql_real_escape_string($rp) . "' order by t desc";
	$res_news = tic_mysql_query($sql);
	$num_news = mysql_num_rows($res_news);

	if($num_news == 0) {
		echo '-';
	} else {
		for($j = 0; $j < $num_news; $j++) {
			$t = mysql_result($res_news, $j, 't' );
			$newsid = mysql_result($res_news, $j, 'id');
			$n_g = mysql_result($res_news, $j, 'erfasser_g');
			$n_p = mysql_result($res_news, $j, 'erfasser_p');
			echo '<a href="main.php?modul=showgalascans&xgala=' . $rg . '&xplanet=' . $rp . '&displaytype=news&newsid='.$newsid.'">' . date('Y-m-d H:i', $t) . '</a> <a title="Bezahle '.ZahlZuText(round(8000 * $scanbezahlungfaktor, 0)).' Kristall" href="http://www.galaxy-network.net/game/rohstoffe.php?transfer1='.$n_g.'&transfer2='.$n_p.'&summe='.round(8000 * $scanbezahlungfaktor, 0).'&transfer_typ=Kristall&spenden_grund=Scanbezahlung" target="_blank">$ '.$n_g.':'.$n_p.'</a><br/>';
		}
	}
	mysql_free_result($res_news);
?>
			</td>
		</tr>
		<tr>
			<td class="fieldnormaldark"><b>LO</b></td>
			<td class="fieldnormaldark"><b>LR</b></td>
			<td class="fieldnormaldark"><b>MR</b></td>
			<td class="fieldnormaldark"><b>SR</b></td>
			<td class="fieldnormaldark"><b>AJ</b></td>
<?php
	$gscan = '00,10Geschützscan (01,10 '.$ggen.'%00,10 ) - '.$rname.' (01,10'.$rg.':'.$rp.'00,10)'."\n";
	$gscan = $gscan.	'00,01Rubium: 07,01'.$lo.' 00,01Pulsar: 07,01'.$lr.' 00,01Coon: 07,01'.$mr."\n";
	$gscan = $gscan.	'00,01Centurion: 07,01'.$sr.' 00,01Horus: 07,01'.$aj."\n";
	$gscan = $gscan.	'00,01Datum: 07,01'.$gzeit;

	$clepkill = 0;
	$clepkill += floor($aj * 0.32);
	$clepkill += floor($lo * 1.28);
	$gscan_slack = '*Geschütze* ('.$svs_g.'SVS, ' . $ggen . '%, ' . $gzeit . ') - *'.$rname.' '.$rg.':'.$rp.'* - https://gntic.de/tic/main.php?modul=showgalascans&xgala=' . $rg . '&xplanet=' . $rp . "\n";
	$gscan_slack .= '```LO:  ' .  nformat($lo, 7) . '	  LR:  ' . nformat($lr, 7) . "\n".'MR:  ' . nformat($mr, 7) . '	  SR:  ' . nformat($sr, 7) . "\n".'AJ:  ' . nformat($aj, 7) . '	  Max Clepkill: ' . nformat($clepkill, 7) . '```';
?>
			<td bgcolor="#fdfddd" colspan="2"><?php echo createCopyLink('Gesch&uuml;tze', $gscan);?></td>
			<td bgcolor="#fdfddd" colspan="2"><?php echo createCopyLink('Gesch&uuml;tze', $gscan_slack);?></td>
			<td class="fieldnormaldark"><b>Genauigkeit</b></td>
			<td class="fieldnormaldark"><b>SVS</b></td>
			<td class="fieldnormaldark"><b>Datum</b></td>
			<td class="fieldnormaldark" title="Ein angemessener Aufschlag von <?=round(($scanbezahlungfaktor-1)*100, 0);?>% ist das Brot des Scanners.">$</td>
		</tr>
		<tr>
			<td class="fieldnormallight"><?php echo $lo; ?></td>
			<td class="fieldnormallight"><?php echo $lr; ?></td>
			<td class="fieldnormallight"><?php echo $mr; ?></td>
			<td class="fieldnormallight"><?php echo $sr; ?></td>
			<td class="fieldnormallight"><?php echo $aj; ?></td>
<?php
	$MiliH = '00,10Milit\E4rscan (01,10 '.$mgen.'%00,10 ) '.$rname.' (01,10'.$rg.':'.$rp.'00,10)'."\n";
	$Orbit = 		'00,1Orbit: 07,01'.$ja0.' 00,1Leo 07,01'.$bo0.' 00,1Aquilae 07,01'.$fr0.' 00,1Fornax 07,01'.$ze0.' 00,1Draco 07,01'.$kr0.' 00,1Goron 07,01'.$sl0.' 00,1Pentalin 07,01'.$tr0.' 00,1Zenit 07,01'.$ka0.' 00,1Cleptor 07,01'.$ca0.' 00,1Cancri'."\n";
	$Flotte1 = 		'00,01Flotte1: 07,01'.$ja1.' 00,01Leo 07,01'.$bo1.' 00,01Aquilae 07,01'.$fr1.' 00,01Fornax 07,01'.$ze1.' 00,01Draco 07,01'.$kr1.' 00,01Goron 07,01'.$sl1.' 00,01Pentalin 07,01'.$tr1.' 00,01Zenit 07,01'.$ka1.' 00,01Cleptor 07,01'.$ca1.' 00,01Cancri'."\n";
	$Flotte2 = 		'00,01Flotte2: 07,01'.$ja2.' 00,01Leo 07,01'.$bo2.' 00,01Aquilae 07,01'.$fr2.' 00,01Fornax 07,01'.$ze2.' 00,01Draco 07,01'.$kr2.' 00,01Goron 07,01'.$sl2.' 00,01Pentalin 07,01'.$tr2.' 00,01Zenit 07,01'.$ka2.' 00,01Cleptor 07,01'.$ca2.' 00,01Cancri'."\n";
	$MiliF = 		'00,01Datum: 07,01'.$mzeit;

	$mili_slack = '*Militär* (' . $svs_m . 'SVS, ' . $mgen . '%, ' . $mzeit . ') - *'.$rname.' '.$rg.':'.$rp.'* - https://gntic.de/tic/main.php?modul=showgalascans&xgala=' . $rg . '&xplanet=' . $rp . "\n";
	$mili_slack .= '```       Orbit  Flotte 1  Flotte 2'."\n".'Jä   ' . nformat($ja0, 7) . '   ' . nformat($ja1, 7) . '   ' . nformat($ja2, 7) . "\n".'Bo:  ' . nformat($bo0, 7) . '   ' . nformat($bo1, 7) . '   ' . nformat($bo2, 7) . "\n".'Fr:  ' . nformat($fr0, 7) . '   ' . nformat($fr1, 7) . '   ' . nformat($fr2, 7) . "\n".'Ze:  ' . nformat($ze0, 7) . '   ' . nformat($ze1, 7) . '   ' . nformat($ze2, 7) . "\n".'Kr:  ' . nformat($kr0, 7) . '   ' . nformat($kr1, 7) . '   ' . nformat($kr2, 7) . "\n".'Sc:  ' . nformat($sl0, 7) . '   ' . nformat($sl1, 7) . '   ' . nformat($sl2, 7) . "\n".'Tr:  ' . nformat($tr0, 7) . '   ' . nformat($tr1, 7) . '   ' . nformat($tr2, 7) . "\n".'Cl:  ' . nformat($ka0, 7) . '   ' . nformat($ka1, 7) . '   ' . nformat($ka2, 7) . "\n".'Ca:  ' . nformat($ca0, 7) . '   ' . nformat($ca1, 7) . '   ' . nformat($ca2, 7) . '```';

	if($mzeit === '-') {
		$mili_slack = '*Einheiten* (' . $svs_e . 'SVS, ' . $ugen . '%, ' . $uzeit . ') - *'.$rname.' '.$rg.':'.$rp.'* - https://gntic.de/tic/main.php?modul=showgalascans&xgala=' . $rg . '&xplanet=' . $rp . "\n";
		$mili_slack .= '```Jäger    ' . nformat($ja, 7) . '	  Bomber:     ' . nformat($bo, 7) . "\n".'Fregs:   ' . nformat($fr, 7) . '	  Zerries:    ' . nformat($ze, 7) . "\n".'Kreuzer: ' . nformat($kr, 7) . '	  Schlachter: ' . nformat($sl, 7) . "\n".'Träger:  '   .nformat( $tr, 7) . '	  Cleps:      ' . nformat($ka, 7) . "\n".'Cancs:   ' . nformat($ca, 7) . '```';
	}

?>
			<td bgcolor="#fdfddd" colspan="2"><?php echo createCopyLink('Milit&auml;r', $MiliH.$Orbit.$Flotte1.$Flotte2.$MiliF);?></td>
			<td bgcolor="#fdfddd" colspan="2"><?php echo createCopyLink('Milit&auml;r', $mili_slack);?></td>
			<td class="fieldnormallight"><?php echo $ggen; ?></td>
			<td class="fieldnormallight"><?=$svs_g;?></td>
			<td class="fieldnormallight"><?php echo $gzeit; ?></td>
			<td class="fieldnormallight"><a title="Bezahle <?=ZahlZuText(round(4000 * $scanbezahlungfaktor, 0));?> Kristall" href="http://www.galaxy-network.net/game/rohstoffe.php?transfer1=<?=$ges_g;?>&transfer2=<?=$ges_p;?>&summe=<?=round(4000 * $scanbezahlungfaktor, 0);?>&transfer_typ=Kristall&spenden_grund=Scanbezahlung" target="_blank"><?=$ges_g ? $ges_g.':'.$ges_p : '';?></a></td>
		</tr>
		<tr>
			<td class="fieldnormaldark"><b>J&auml;ger</b></td>
			<td class="fieldnormaldark"><b>Bomber</b></td>
			<td class="fieldnormaldark"><b>Fregs</b></td>
			<td class="fieldnormaldark"><b>Zerries</b></td>
			<td class="fieldnormaldark"><b>Kreuzer</b></td>
			<td class="fieldnormaldark"><b>Schlachter</b></td>
			<td class="fieldnormaldark"><b>Tr&auml;ger</b></td>
			<td class="fieldnormaldark"><b>Kleps</b></td>
			<td class="fieldnormaldark"><b>Schutzies</b></td>
			<td class="fieldnormaldark"><b>Genauigkeit</b></td>
			<td class="fieldnormaldark"><b>SVS</b></td>
			<td class="fieldnormaldark"><b>Datum</b></td>
			<td class="fieldnormaldark" title="Ein angemessener Aufschlag von <?=round(($scanbezahlungfaktor-1)*100, 0);?>% ist das Brot des Scanners.">$</td>
		</tr>
		<tr bgcolor="#ddddfd">
			<td><b><?php echo $ja; ?></b></td>
			<td><b><?php echo $bo; ?></b></td>
			<td><b><?php echo $fr; ?></b></td>
			<td><b><?php echo $ze; ?></b></td>
			<td><b><?php echo $kr; ?></b></td>
			<td><b><?php echo $sl; ?></b></td>
			<td><b><?php echo $tr; ?></b></td>
			<td><b><?php echo $ka; ?></b></td>
			<td><b><?php echo $ca; ?></b></td>
			<td><b><?php echo $ugen; ?></b></td>
			<td><b><?=$svs_e;?></td>
			<td><b><?php echo $uzeit; ?></b></td>
			<td class="fieldnormallight"><a  title="Bezahle <?=ZahlZuText(round(4000 * $scanbezahlungfaktor, 0));?> Kristall" href="http://www.galaxy-network.net/game/rohstoffe.php?transfer1=<?=$ein_g;?>&transfer2=<?=$ein_p;?>&summe=<?=round(4000 * $scanbezahlungfaktor, 0);?>&transfer_typ=Kristall&spenden_grund=Scanbezahlung" target="_blank"><?=$ein_g ? $ein_g.':'.$ein_p : '';?></a></td>
		</tr>
		<tr class="fieldnormallight">
			<td><?php echo $ja0; ?></td>
			<td><?php echo $bo0; ?></td>
			<td><?php echo $fr0; ?></td>
			<td><?php echo $ze0; ?></td>
			<td><?php echo $kr0; ?></td>
			<td><?php echo $sl0; ?></td>
			<td><?php echo $tr0; ?></td>
			<td><?php echo $ka0; ?></td>
			<td><?php echo $ca0; ?></td>
			<td rowspan="3"><?php echo $mgen; ?></td>
			<td rowspan="3"><?=$svs_m;?></td>
			<td rowspan="3"><?php echo $mzeit; ?></td>
			<td rowspan="4"><a title="Bezahle <?=ZahlZuText(round(8000 * $scanbezahlungfaktor, 0));?> Kristall" href="http://www.galaxy-network.net/game/rohstoffe.php?transfer1=<?=$mil_g;?>&transfer2=<?=$mil_p;?>&summe=<?=round(8000 * $scanbezahlungfaktor, 0);?>&transfer_typ=Kristall&spenden_grund=Scanbezahlung" target="_blank"><?=$mil_g ? $mil_g.':'.$mil_p : '';?></a></td>
		</tr>
		<tr class="fieldnormallight">
			<td><?php echo $ja1; ?></td>
			<td><?php echo $bo1; ?></td>
			<td><?php echo $fr1; ?></td>
			<td><?php echo $ze1; ?></td>
			<td><?php echo $kr1; ?></td>
			<td><?php echo $sl1; ?></td>
			<td><?php echo $tr1; ?></td>
			<td><?php echo $ka1; ?></td>
			<td><?php echo $ca1; ?></td>
		</tr>
		<tr class="fieldnormallight">
			<td><?php echo $ja2; ?></td>
			<td><?php echo $bo2; ?></td>
			<td><?php echo $fr2; ?></td>
			<td><?php echo $ze2; ?></td>
			<td><?php echo $kr2; ?></td>
			<td><?php echo $sl2; ?></td>
			<td><?php echo $tr2; ?></td>
			<td><?php echo $ka2; ?></td>
			<td><?php echo $ca2; ?></td>
		</tr>
<?php
	if(!empty($ziel1)) {
		$ziel1_res = tic_mysql_query("SELECT spieler_galaxie, spieler_planet FROM gn_spieler2 WHERE spieler_name = '" . trim($ziel1) . "'", __FILE__, __LINE__);
		if(mysql_num_rows($ziel1_res) == 1) {
			$g = mysql_result($ziel1_res, 0, 'spieler_galaxie');
			$p = mysql_result($ziel1_res, 0, 'spieler_planet');
			$ziel1 = '<a href="main.php?modul=showgalascans&xgala='.$g.'&xplanet='.$p.'">&raquo; '.$g.':'.$p.' ' . $ziel1 . '</a>';
		}
		mysql_free_result($ziel1_res);
	}
	if(!empty($ziel2)) {
		$ziel2_res = tic_mysql_query("SELECT spieler_galaxie, spieler_planet FROM gn_spieler2 WHERE spieler_name = '" . trim($ziel2) . "'", __FILE__, __LINE__);
		if(mysql_num_rows($ziel2_res) == 1) {
			$g = mysql_result($ziel2_res, 0, 'spieler_galaxie');
			$p = mysql_result($ziel2_res, 0, 'spieler_planet');
			$ziel2 = '<a href="main.php?modul=showgalascans&xgala='.$g.'&xplanet='.$p.'">&raquo; '.$g.':'.$p.' ' . $ziel2 . '</a>';
		}
		mysql_free_result($ziel2_res);
	}
?>
		<tr class="fieldnormallight">
			<td class="fieldnormaldark" colspan="2"><b>Flotte 1:</b></td>
			<td colspan="2"><?php
				switch($status1) {
					case '0': echo 'Im Orbit'; break;
					case '1': echo 'Angriff'; break;
					case '2': echo 'Verteidigung'; break;
					case '3': echo 'R&uuml;ckflug'; break;
					default: echo '<i>unknown</i>'; break;
				}
			?></td>
			<td colspan="2"><?=$ziel1;?></td>
			<td class="fieldnormaldark" colspan="2"><b>Flotte 2:</b></td>
			<td colspan="2"><?php
				switch($status2) {
					case '0': echo 'Im Orbit'; break;
					case '1': echo 'Angriff'; break;
					case '2': echo 'Verteidigung'; break;
					case '3': echo 'R&uuml;ckflug'; break;
					default: echo '<i>unknown</i>'; break;
				}
			?></td>
			<td colspan="2"><?=$ziel2;?></td>
		</tr>
		<tr class="datatablehead">
			<td colspan="15" align="right">
				&nbsp;Slack-Scanrequest:&nbsp;&nbsp; 
				<a href="<?=makeRequestScanLink($rg, $rp, 0, 'modul=showgalascans&xgala='.$rg.'&xplanet='.$rp.'&displaytype='.$_GET['displaytype'].'#plani' . $rp);?>">&raquo; Sek</a> | 
				<a href="<?=makeRequestScanLink($rg, $rp, 3, 'modul=showgalascans&xgala='.$rg.'&xplanet='.$rp.'&displaytype='.$_GET['displaytype'].'#plani' . $rp);?>">&raquo; Gesch</a> | 
				<a href="<?=makeRequestScanLink($rg, $rp, 1, 'modul=showgalascans&xgala='.$rg.'&xplanet='.$rp.'&displaytype='.$_GET['displaytype'].'#plani' . $rp);?>">&raquo; Einh</a> | 
				<a href="<?=makeRequestScanLink($rg, $rp, 2, 'modul=showgalascans&xgala='.$rg.'&xplanet='.$rp.'&displaytype='.$_GET['displaytype'].'#plani' . $rp);?>">&raquo; Mili</a> | 
				<a href="<?=makeRequestScanLink($rg, $rp, 4, 'modul=showgalascans&xgala='.$rg.'&xplanet='.$rp.'&displaytype='.$_GET['displaytype'].'#plani' . $rp);?>">&raquo; News</a>&nbsp;
			</td>
		</tr>
	</table>
<?php
				// all
				$svs_s = '-';
				$svs_g = '-';
				$svs_e = '-';
				$svs_m = '-';
				// sektor
				$pts = '-'; $me  = '-'; $ke  = '-'; $sgen='-'; $szeit='-'; $s='-'; $d='-'; $a='-';
				// unit init
				$ja   = '-'; $bo   = '-'; $fr   = '-'; $ze   = '-'; $kr   = '-'; $sl   = '-'; $tr   = '-'; $ka   = '-'; $ca   = '-'; $ugen='-'; $uzeit='-';
				// mili init
				$ja0  = '-'; $bo0  = '-'; $fr0  = '-'; $ze0  = '-'; $kr0  = '-'; $sl0  = '-'; $tr0  = '-'; $ka0  = '-'; $ca0  = '-'; $mgen='-'; $mzeit='-';
				$ja1  = '-'; $bo1  = '-'; $fr1  = '-'; $ze1  = '-'; $kr1  = '-'; $sl1  = '-'; $tr1  = '-'; $ka1  = '-'; $ca1  = '-';
				$ja2  = '-'; $bo2  = '-'; $fr2  = '-'; $ze2  = '-'; $kr2  = '-'; $sl2  = '-'; $tr2  = '-'; $ka2  = '-'; $ca2  = '-';
				// gscan
				$lo = '-'; $lr = '-'; $mr = '-'; $sr = '-'; $aj = '-'; $ggen='-'; $gzeit='-';
				$rscans = '';
			} // end of if (rp != rpnext)
		} // end of for
	} // end of else


	if($xgala && $xplanet) {
		//single entry
		?>
				<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
				<script type="text/javascript">
					google.charts.load('current', {'packages':['line', 'corechart']});
					google.charts.setOnLoadCallback(drawChart);

				function drawChart() {

					var data = new google.visualization.DataTable();
					data.addColumn('datetime', 'Datum');
					data.addColumn('number', 'Punkte');
					data.addColumn('number', "Extraktoren\n(ggf. ungenau)");
					<?php /* data.addColumn('number', 'Scans'); */ ?>

					data.addRows([
			<?php
			//$name = (isset($_POST['name'])) ? $_POST['name'] : ( (isset($_GET['name'])) ? $_GET['name'] : '');
			$sqlX = "SELECT spieler_name, spieler_punkte, spieler_asteroiden, unix_timestamp(t) AS unix_t FROM gn_spieler WHERE UNIX_TIMESTAMP(t) > UNIX_TIMESTAMP(NOW())-60*60*24*7 AND spieler_galaxie = '" . $xgala . "' AND spieler_planet = '" . $xplanet . "' ORDER BY t ASC";
			//aprint($sqlX);
			$resX = tic_mysql_query($sqlX, __FILE__, __LINE__);
			$numX = mysql_num_rows($resX);

			$resY = array();
			for($m = 0; $m < $numX; $m++) {
				$resY[$m] = array(
					'spieler_asteroiden' => mysql_result($resX, $m, 'spieler_asteroiden'),
					'spieler_punkte' => mysql_result($resX, $m, 'spieler_punkte'),
					'unix_t' => mysql_result($resX, $m, 'unix_t')
					);
			}
			//aprint(resY);
			$exen = 0;
			for($m = 0; $m < $numX; $m++) {
				$resY[$m]['extractors'] = 0;
				if($m > 0) {
					$prev = $resY[$m-1];
					$extractors = 0;
					$tickdiff = floor(($resY[$m]['unix_t'] - $prev['unix_t']) / 60 / 15);

					if($tickdiff > 0) {
						$extractors = round((($resY[$m]['spieler_punkte'] - $prev['spieler_punkte']) * 10 - 20000) / 50 / $tickdiff, 0);
						if($extractors > 0 && $extractors <= 20 * $resY[$m]['spieler_asteroiden']) {
							$resY[$m]['extractors'] = $extractors;
							$exen = $extractors;
						}
					}
				}

				//if($res[$i]['extractors'] >= 0) {
					$time = getDate($resY[$m]['unix_t']);
					//echo '			[new Date(' . $time['year'] . ', ' . $time['mon'] . ',	' . $time['mday'] . ',	' . $time['hours'] . ',	' . $time['minutes'] . ',	0, 0), ' . $res[$m]['spieler_punkte'] . ',	' . $res[$m]['extractors'] . ' ],' . "\n";
					echo '			[new Date(' . ($resY[$m]['unix_t'] * 1000) . '), ' . $resY[$m]['spieler_punkte'] . ',	' . $exen . '],' . "\n";
				//}
			}

			/*
			//aprint(array($st, $gt, $ut, $mt));
			if($st > 0) {
				echo '			[new Date('.($st*1000).'), null, null, 0],'."\n";
				echo '			[new Date('.($st*1000).'), null, null, 1],'."\n";
				echo '			[new Date('.($st*1000).'), null, null, null],'."\n";
			}
			if($gt > 0) {
				echo '			[new Date('.($gt*1000).'), null, null, 0],'."\n";
				echo '			[new Date('.($gt*1000).'), null, null, 1],'."\n";
				echo '			[new Date('.($gt*1000).'), null, null, null],'."\n";
			}
			if($ut > 0) {
				echo '			[new Date('.($ut*1000).'), null, null, 0],'."\n";
				echo '			[new Date('.($ut*1000).'), null, null, 1],'."\n";
				echo '			[new Date('.($ut*1000).'), null, null, null],'."\n";
			}
			if($mt > 0) {
				echo '			[new Date('.($mt*1000).'), null, null, 0]'."\n";
				echo '			[new Date('.($mt*1000).'), null, null, 1]'."\n";
			}
			* */

			?>
					]);

					var options = {
						chart: {
							title: 'Punkteverlauf: <?=mysql_result($resX, 0, 'spieler_name')?>'
						},
						width: "90%",
						height: 350,
						series: {
							0: { axis: 'pkt' },
							1: { axis: 'num' }
						},
						axes: {
							// Adds titles to each axis.
							y: {
								pkt: {label: 'Punkte'},
								num: {label: '#Exen'}
							},
						},
					};

					var chart = new google.charts.Line(document.getElementById('chart'));
					chart.draw(data, options);
				}

				</script>
				<br/>
				<center><div id="chart" style="width: 90%; height: 350px"></div></center>
		<?php
	}

}//if displaytype news
?>
