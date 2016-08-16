<?php
$hours = 36;
?>
<center>
<h2>Scanliste</h2>

<?php
	$to_be_scanned = array();
	$to_be_scanned_meta = array();

	$where = ' (0 ';

	if($_GET['koords']) {
		$koords = explode(';', $_GET['koords']);
		foreach($koords as $v) {
			if(strlen($v) > 0) {
				$k = explode(':', $v);
				$where .= ' OR (spieler_galaxie = "'.mysql_real_escape_string($k[0]).'" AND spieler_planet = "'.mysql_real_escape_string($k[1]).'")';
			}
		}
	}

	if($_GET['meta']) {
		$metas = explode(';', $_GET['meta']);

		foreach($metas as $v) {
			$v = trim($v);
			$v = str_replace('?', '_', $v);
			if(strlen($v) > 0) {
				$where .= ' OR meta LIKE "'.mysql_real_escape_string($v).'"';
			}
		}
	}

	if($_GET['allianz']) {
		$allies = explode(';', $_GET['allianz']);

		foreach($allies as $v) {
			$v = trim($v);
			$v = str_replace('?', '_', $v);
			if(strlen($v) > 0) {
				$where .= ' OR allianz_name LIKE "'.mysql_real_escape_string($v).'"';
			}
		}
	}

	if($_GET['galaxie']) {
		$galas = explode(';', $_GET['galaxie']);

		foreach($galas as $v) {
			$v = trim($v);
			if(strlen($v) > 0) {
				$where .= ' OR spieler_galaxie = "'.mysql_real_escape_string($v).'"';
			}
		}
	}

	$where .= ')';

	$sql = "SELECT spieler_galaxie g, spieler_planet p FROM gn_spieler2 WHERE " . $where;

	$res = tic_mysql_query($sql) or tic_mysql_error(__FILE__, __LINE__);
	$num = mysql_num_rows($res);

	for($i = 0; $i < $num; $i++) {
		$g = mysql_result($res, $i, 'g');
		$p = mysql_result($res, $i, 'p');
		$key = $g . ':' . $p;
		if(!in_array($key, $to_be_scanned))
			$to_be_scanned[] = $key;
	}

//aprint($to_be_scanned);

	?>
<form action="main.php" method="get">
<input type="hidden" name="modul" value="scanliste"/>
<table width="100%">
	<tr class="datatablehead">
		<td colspan="4">&nbsp;Metas&nbsp;</td>
	</tr>
	<tr class="fieldnormallight">
		<td>
			<select id="metaselect">
<?php
	$sql = "SELECT DISTINCT meta m FROM gn_spieler2 ORDER BY m";
	$res = tic_mysql_query($sql) or tic_mysql_error(__FILE__, __LINE__);
	$num = mysql_num_rows($res);
	for($i = 0; $i < $num; $i++) {
		$meta = mysql_result($res, $i, 'm');
		echo '<option value="'.$meta.'">'.$meta.'</option>';
	}
?>
			</select>
		</td>
		<td align="center"><input type="button" value=">>" onclick="javascript:document.getElementById('meta').value += (document.getElementById('metaselect').value + ';')"></td>
		<td>
			<textarea id="meta" name="meta"><?=$_GET['meta']?></textarea>
		</td>
		<td align="center"><input type="button" onclick="javascript:document.getElementById('meta').value=''" value="del"/></td>
	</tr>
	<tr class="datatablehead">
		<td colspan="4">&nbsp;Allianzen&nbsp;</td>
	</tr>
	<tr class="fieldnormallight">
		<td>
			<select id="allyselect">
<?php
	$sql = "SELECT DISTINCT allianz_name a, meta m FROM gn_spieler2 ORDER BY a";
	$res = tic_mysql_query($sql) or tic_mysql_error(__FILE__, __LINE__);
	$num = mysql_num_rows($res);
	for($i = 0; $i < $num; $i++) {
		$meta = mysql_result($res, $i, 'm');
		$ally = mysql_result($res, $i, 'a');
		echo '<option value="'.$ally.'">'.$ally.($meta ? ' - '.$meta : '').'</option>';
	}
?>
			</select>
		</td>
		<td align="center"><input type="button" value=">>" onclick="javascript:document.getElementById('allianz').value += (document.getElementById('allyselect').value + ';')"></td>
		<td>
			<textarea id="allianz" name="allianz"><?=$_GET['allianz']?></textarea>
		</td>
		<td align="center"><input type="button" onclick="javascript:document.getElementById('allianz').value=''" value="del"/></td>
	</tr>
	<tr class="datatablehead">
		<td colspan="4">&nbsp;Galaxien&nbsp;</td>
	</tr>
	<tr class="fieldnormallight">
		<td>
			<select id="galselect">
<?php
	$sql = "SELECT DISTINCT spieler_galaxie g, allianz_name a FROM gn_spieler2 ORDER BY g";
	$res = tic_mysql_query($sql) or tic_mysql_error(__FILE__, __LINE__);
	$num = mysql_num_rows($res);
	for($i = 0; $i < $num; $i++) {
		$gal = mysql_result($res, $i, 'g');
		$ally = mysql_result($res, $i, 'a');
		echo '<option value="'.$gal.'">'.$gal. ($ally ? ' - '.$ally : '').'</option>';
	}
?>
			</select>
		</td>
		<td align="center"><input type="button" value=">>" onclick="javascript:document.getElementById('galaxie').value += (document.getElementById('galselect').value + ';')"></td>
		<td>
			<textarea id="galaxie" name="galaxie"><?=$_GET['galaxie']?></textarea>
		</td>
		<td align="center"><input type="button" onclick="javascript:document.getElementById('galaxie').value=''" value="del"/></td>
	</tr>
	<tr class="datatablehead">
		<td colspan="4">&nbsp;Spieler&nbsp;</td>
	</tr>
	<tr class="fieldnormallight">
		<td>
			<input size="6" id="gal"/>:<input size="6" id="pla"/>
		</td>
		<td align="center"><input type="button" value=">>" onclick="javascript:document.getElementById('spieler').value += (document.getElementById('gal').value + ':' + document.getElementById('pla').value + ';')"></td>
		<td>
			<textarea id="spieler" name="koords"><?=$_GET['koords']?></textarea>
		</td>
		<td align="center"><input type="button" onclick="javascript:document.getElementById('spieler').value=''" value="del"/></td>
	</tr>
	<tr class="fieldnormaldark">
		<td colspan="3" align="right">
			<input type="checkbox" checked="checked" name="s"/> Sektoren
			<input type="checkbox" checked="checked" name="g"/> Gesch&uuml;tze
			<input type="checkbox" checked="checked" name="e"/> Einheiten
			<input type="checkbox" <?php echo isset($_GET['m']) ? 'checked="checked"' : '' ?> name="m"/> Milit&auml;r
			<input type="checkbox" <?php echo isset($_GET['n']) ? 'checked="checked"' : '' ?> name="n"/> News
			<input type="checkbox" checked="checked" name="b"/> Scanblocks
			<input type="checkbox" <?php echo isset($_GET['u']) ? 'checked="checked"' : '' ?>name="u"/> Urlaub  
			<input type="submit" name="export" value="Export"/>&nbsp;</td>
		<td align="right">&nbsp;<input type="submit" value="Scanliste"/>&nbsp;</td>
	</tr>	
</table>
</form>
<br/>
<?php

$where = '0';
foreach($to_be_scanned as $v) {
	$v = explode(':', $v);
	$where .= ' OR (s.spieler_galaxie = '.$v[0].' AND s.spieler_planet = '.$v[1].')';
}

if(isset($_GET['export'])) {
	if($Benutzer['rang'] >= $Rang_GC) {
		$sql = "SELECT
				s.meta,
				s.allianz_name ally,
				s.spieler_galaxie g,
				s.spieler_planet p,
				s.spieler_name name,
				s.spieler_urlaub urlaub
			FROM gn_spieler2 s
			WHERE (".$where.") AND s.spieler_urlaub IN (0, " . (isset($_GET['u']) ? 1 : 0) . ") ORDER BY s.meta, ally, g, p";
		//aprint($sql);
		$res = tic_mysql_query($sql, __FILE__, __LINE__);
		$num = mysql_num_rows($res);

		echo '<textarea style="width: 700px; height: 500px">';
		
		for($i = 0; $i < $num; $i++) {
			$scans = 0;
			list($meta, $ally, $gal, $pla, $name, $urlaub) = mysql_fetch_row($res);
			
			if($urlaub) {
				echo "UMODE!\n\n";
			}
			
			
			if(isset($_GET['b'])) {
				//blocks
				$sql = "SELECT t, svs, typ FROM gn4scanblock WHERE g = '" . $gal . "' AND p = '" . $pla . "' AND suspicious IS NULL ORDER BY svs DESC, t DESC LIMIT 3";
				//aprint($sql);
				$res2 = tic_mysql_query($sql, __FILE__, __LINE__);
				$num2 = mysql_num_rows($res2);
				for($j = 0; $j < $num2; $j++) {
					list($t, $svs, $typ) = mysql_fetch_row($res2);
					echo "SCANBLOCK ";
					switch($typ) {
						case 0: echo 'Sektor   '; break;
						case 1: echo 'Einheiten'; break;
						case 2: echo 'Militär  '; break;
						case 3: echo 'Geschütze'; break;
						case 4: echo 'News     '; break;
						default: echo 'unbekannt'; break;
					}
					echo " mit " . ZahlZuText($svs) . " SVS am " . date('d.m.Y H:i', $t) . "\n";
				}
				if($num2 > 0)
					echo "\n";
			}

			//scans
			$sql = "SELECT * FROM gn4scans WHERE rg='" . $gal . "' AND rp='" . $pla . "' ORDER BY rg, rp, `type`";
			$res2 = tic_mysql_query($sql, __FILE__, __LINE__);
			for($j = 0; $j < mysql_num_rows($res2); $j++) {
				list($id, $ticid, $zeit, $type, $g, $p, $rg, $rp, $gen, $pts, $s, $d, $me, $ke, $a,
					$sf0j, $sf0b, $sf0f, $sf0z, $sf0kr, $sf0sa, $sf0t, $sf0ko, $sf0ka, $sf0su, 
					$sf1j, $sf1b, $sf1f, $sf1z, $sf1kr, $sf1sa, $sf1t, $sf1ko, $sf1ka, $sf1su, $status1, $ziel1,
					$sf2j, $sf2b, $sf2f, $sf2z, $sf2kr, $sf2sa, $sf2t, $sf2ko, $sf2ka, $sf2su, $status2, $ziel2,
					$sfj, $sfb, $sff, $sfz, $sfkr, $sfsa, $sft, $sfko, $sfka, $sfsu, $glo, $glr, $gmr, $gsr, $ga, $gr) = mysql_fetch_row($res2);
				switch($type) {
					case 0:
						if(!isset($_GET['s']))
							continue;
						$align = 9;
						echo "SEKTOR " . $rg . ":" . $rp . " " . $name . " (" . $gen . "%, " . $zeit . ")\n";
						echo "Punkte:     " . nformat(round($pts, 0), $align) . "\n";
						echo "Schiffe:     " . nformat($s, $align) .             ";  Defensiv:          " . nformat($d, $align) . "\n";
						echo "Metall-Exen: " . nformat($me, $align) .            ";  Kristall-Exen:     " . nformat($ke, $align) . "\n";
						echo "Exen-Summe:  " . nformat($me+$ke, $align) .        ";  Exen nach 5 Ticks: " . nformat(round(($me+$ke)*pow(.9, 5), 0), $align) . "\n";
						echo "\n";
						$scans++;
						break;
					case 1:
						if(!isset($_GET['e']))
							continue;
						$align = 7;
						echo "EINHEITEN " . $rg . ":" . $rp . " " . $name . " (" . $gen . "%, " . $zeit . ")\n";
						echo "Jaeger:    " . nformat($sfj, $align) .  ";  Bomber:     " . nformat($sfb, $align) . "\n";
						echo "Fregatten: " . nformat($sff, $align) .  ";  Zerstoerer: " . nformat($sfz, $align) . "\n";
						echo "Kreuzer:   " . nformat($sfkr, $align) . ";  Schlachter: " . nformat($sfsa, $align) . "\n";
						echo "Traeger:   " . nformat($sft, $align) .  ";  Cancs:      " . nformat($sfka, $align) . "\n";
						echo "Cleps:     " . nformat($sfsu, $align) . "\n";
						echo "\n";
						$scans++;
						break;
					case 2:
						if(!isset($_GET['m']))
							continue;
						$align = 7;
						echo "MILITÄR " . $rg . ":" . $rp . " " . $name . " (" . $gen . "%, " . $zeit . ")\n";
						echo "              Orbit  Flotte1  Flotte2\n";
						echo "Jaeger:     " . nformat($sf0j, $align)  . "  " . nformat($sf1j, $align)   . "  " . nformat($sf2j, $align)  . "\n";
						echo "Bomber:     " . nformat($sf0b, $align)  . "  " . nformat($sf1b, $align)   . "  " . nformat($sf2b, $align)  . "\n";
						echo "Fregatten:  " . nformat($sf0f, $align)  . "  " . nformat($sf1f, $align)   . "  " . nformat($sf2f, $align)  . "\n";
						echo "Zerstoerer: " . nformat($sf0z, $align)  . "  " . nformat($sf1z, $align)   . "  " . nformat($sf2z, $align)  . "\n";
						echo "Kreuzer:    " . nformat($sf0kr, $align) . "  " . nformat($sf1kr, $align)  . "  " . nformat($sf2kr, $align)  . "\n";
						echo "Schlachter: " . nformat($sf0sa, $align) . "  " . nformat($sf1sa, $align)  . "  " . nformat($sf2sa, $align)  . "\n";
						echo "Traeger:    " . nformat($sf0t, $align)  . "  " . nformat($sf1t, $align)   . "  " . nformat($sf2t, $align)  . "\n";
						echo "Cleps:      " . nformat($sf0ka, $align) . "  " . nformat($sf1ka, $align)  . "  " . nformat($sf2ka, $align)  . "\n";
						echo "Cancs:      " . nformat($sf0su, $align) . "  " . nformat($sf1su, $align)  . "  " . nformat($sf2su, $align)  . "\n";
						echo "\n";
						$scans++;
						break;
					case 3:
						if(!isset($_GET['g']))
							continue;
						if(isset($_GET['s']) && ($ga + $glo + $glr + $gmr + $gsr) == 0)
							continue;
						$align = 7;
						$clepkill = floor($glo * 1.28 + $ga * .32);
						echo "GESCHÜTZE " . $rg . ":" . $rp . " " . $name . " (" . $gen . "%, " . $zeit . ")\n";
						echo "Abfangjaeger:           " . nformat($ga, $align) .  ";  Leichte Orbitalgeschuetze: " . nformat($glo, $align) . "\n";
						echo "Leichte Raumgeschuetze: " . nformat($glr, $align) . ";  Mittlere Raumgeschuetze:   " . nformat($gmr, $align) . "\n";
						echo "Schwere Raumgeschuetze: " . nformat($gsr, $align) . ";  Clepkill pro Tick:         " . nformat($clepkill, $align) . "\n";
						echo "\n";
						$scans++;
						break;
				}
			}//foreach players scans
			mysql_free_result($res2);

			if(isset($_GET['n'])) {
				$sql = "SELECT id, t, genauigkeit, erfasser_svs FROM gn4scans_news WHERE ziel_g = '".$rg."' AND ziel_p = '".$rp."' ORDER BY t DESC LIMIT 1";
				$res2 = tic_mysql_query($sql, __FILE__, __LINE__);
				if(mysql_num_rows($res2) == 1) {
					list($id, $t, $gen, $svs) = mysql_fetch_row($res2);
					mysql_free_result($res2);
					
					echo "NACHRICHTEN " . $rg . ":" . $rp . " " . $name . " (" . $gen . "%, " . date('H:i d.m.Y', $t) . ")\n";
					
					$sql = "SELECT t, typ, inhalt, inaccurate FROM gn4scans_news_entries WHERE typ NOT LIKE '%bericht%' AND typ NOT LIKE '%beschuss%' AND (typ LIKE '%Verteidigung%' OR typ LIKE '%Angriff%' OR typ LIKE '%ckzug%') AND news_id = '".$id."' ORDER BY id";
					$res2 = tic_mysql_query($sql, __FILE__, __LINE__);
					$num2 = mysql_num_rows($res2);
					
					if($num2 > 0) {
						for($j = 0; $j < $num2; $j++) {
							list($t, $typ, $inhalt, $inaccurate) = mysql_fetch_row($res2);
							$age = round((time() - $t)/60, 0);
							if($j > 0) echo "--\n";
							echo nformat($age, 6) . 'min ' . ($inaccurate ? ' (fehlerhaft) ' : '') . $typ . ":\n";
							echo              '          ' . str_replace("\n", " ", $inhalt) . "\n";
						}
						echo "\nENDE\n\n";
					} else {
						echo "ENDE\n\n";
					}
					
					$scans++;
				}
				
				mysql_free_result($res2);
			}
			
			if($scans == 0) {
				echo 'Keine Scaninformation zu ' . $gal . ':' . $pla . ' ' . $name . "\n\n";
			}
			echo "===\n\n";
		}
		
		echo '</textarea><br/><div style="text-align: right;"><a href="http://www.pastebin.com" target="_blank">&raquo; Pastebin</a>&nbsp;</div>';
	} else {
		echo 'Dieses Feature steht Dir leider nicht zur Verf&uuml;gung.';
	}
} else {
?>

<table width="100%">
	<tr class="datatablehead">
		<td align="center">&nbsp;Meta&nbsp;</td>
		<td align="center">&nbsp;Allianz&nbsp;</td>
		<td align="center">&nbsp;Galaxie&nbsp;</td>
		<td align="center">&nbsp;Planet&nbsp;</td>
		<td align="center">&nbsp;Spieler&nbsp;</td>
		<td align="center" colspan="2">&nbsp;Blocks&nbsp;</td>
		<td align="center" colspan="2">&nbsp;Sektor&nbsp;</td>
		<td align="center" colspan="3">&nbsp;Gesch&uuml;tze&nbsp;</td>
		<td align="center" colspan="2">&nbsp;Einheiten&nbsp;</td>
		<td align="center" colspan="2">&nbsp;Milit&auml;r&nbsp;</td>
		<td align="center" colspan="2">&nbsp;Nachrichten&nbsp;</td>
	</tr>
	<tr class="datatablehead">
		<td>&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;</td>
		<td>&nbsp;&nbsp;</td>
		<td align="center">&nbsp;SVS&nbsp;</td>
		<td align="center">&nbsp;Typ&nbsp;</td>
		<td align="center">&nbsp;Alter&nbsp;</td>
		<td align="center">&nbsp;&nbsp;</td>
		<td align="center">&nbsp;Alter&nbsp;</td>
		<td align="center">&nbsp;Deff <i title="Laut neuestem Sektor-/Gesch&uuml;tzscan.">(?)</i>&nbsp;</td>
		<td align="center">&nbsp;Link&nbsp;</td>
		<td align="center">&nbsp;Alter&nbsp;</td>
		<td align="center">&nbsp;Link&nbsp;</td>
		<td align="center">&nbsp;Alter&nbsp;</td>
		<td align="center">&nbsp;Link&nbsp;</td>
		<td align="center">&nbsp;Alter&nbsp;</td>
		<td align="center">&nbsp;Link&nbsp;</td>
	</tr>
<?php
	$sql = "SELECT
			s.meta,
			s.allianz_name ally,
			s.spieler_galaxie g,
			s.spieler_planet p,
			s.spieler_name name,
			s.spieler_urlaub,
			round((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(STR_TO_DATE(scans0.zeit, '%H:%i %d.%m.%Y')))/60, 0) t0,
			round((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(STR_TO_DATE(scans1.zeit, '%H:%i %d.%m.%Y')))/60, 0) t1,
			round((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(STR_TO_DATE(scans2.zeit, '%H:%i %d.%m.%Y')))/60, 0) t2,
			round((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(STR_TO_DATE(scans3.zeit, '%H:%i %d.%m.%Y')))/60, 0) t3,
			scans0.d AS DEFF,
			scans3.ga + scans3.glo + scans3.glr + scans3.gmr + scans3.gsr as DEFF2,
			round((UNIX_TIMESTAMP(NOW()) - scans4.t)/60, 0) t4,
			blocks.typ,
			blocks.svs
		FROM gn_spieler2 s
		LEFT JOIN gn4scans scans0 ON scans0.type = 0 AND scans0.rg = s.spieler_galaxie AND scans0.rp = s.spieler_planet
		LEFT JOIN gn4scans scans1 ON scans1.type = 1 AND scans1.rg = s.spieler_galaxie AND scans1.rp = s.spieler_planet
		LEFT JOIN gn4scans scans2 ON scans2.type = 2 AND scans2.rg = s.spieler_galaxie AND scans2.rp = s.spieler_planet
		LEFT JOIN gn4scans scans3 ON scans3.type = 3 AND scans3.rg = s.spieler_galaxie AND scans3.rp = s.spieler_planet
		LEFT JOIN (SELECT ziel_g, ziel_p, max(t) t FROM `gn4scans_news` group by ziel_g, ziel_p) scans4 ON scans4.ziel_g = s.spieler_galaxie AND scans4.ziel_p = s.spieler_planet
		LEFT JOIN (SELECT b1.g, b1.p, b1.svs, b1.typ
					FROM gn4scanblock b1
					WHERE b1.t = (SELECT MAX(b2.t)
								FROM gn4scanblock b2
								WHERE b2.g = b1.g AND b2.p = b1.p)
				) blocks ON blocks.g = s.spieler_galaxie AND blocks.p = s.spieler_planet
		WHERE (".$where.") ORDER BY s.meta, ally, g, p";
	//aprint($sql);

	$res = tic_mysql_query($sql) or tic_mysql_error(__FILE__, __LINE__);
	$num = mysql_num_rows($res);

	$color = true;
	for($i = 0; $i < $num; $i++) {
		$meta = mysql_result($res, $i, 'meta');
		$ally = mysql_result($res, $i, 'ally');
		$g = mysql_result($res, $i, 'g');
		$p = mysql_result($res, $i, 'p');
		$name = mysql_result($res, $i, 'name');
		$umode = mysql_result($res, $i, 'spieler_urlaub');

		$age_s = mysql_result($res, $i, 't0');
		$age_e = mysql_result($res, $i, 't1');
		$age_m = mysql_result($res, $i, 't2');
		$age_g = mysql_result($res, $i, 't3');
		$age_n = mysql_result($res, $i, 't4');
		
		$DEFF =  mysql_result($res, $i, 'DEFF');
		$DEFF2 =  mysql_result($res, $i, 'DEFF2');
		$deffstr = '-';
		if($age_g && $age_s) {
			$deffstr = ($age_g < $age_s) ? ZahlZuText($DEFF2) : ZahlZuText($DEFF);
		} else if($age_g) {
			$deffstr = ZahlZuText($DEFF2);
		} else if($age_s) {
			$deffstr = ZahlZuText($DEFF);
		}
		
		
		$block_svs = mysql_result($res, $i, 'svs');
		$block_typ = mysql_result($res, $i, 'typ');

		switch($block_typ) {
			case 0: $block_typ = 'S'; break;
			case 1: $block_typ = 'E'; break;
			case 2: $block_typ = 'M'; break;
			case 3: $block_typ = 'G'; break;
			case 4: $block_typ = 'N'; break;
			default:
				$block_typ = '<i>unknown</i>';
		}

		if(!$block_svs)
			$block_typ = '';

//<|S> | ';
//		$postdata .= '<http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $gal . '&c2=' . $plani . '&typ=einheit|E> ';
//		$postdata .= '<http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $gal . '&c2=' . $plani . '&typ=mili|M> | ';
//		$postdata .= '<http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $gal . '&c2=' . $plani . '&typ=gesch|G> ';
//		$postdata .= '<http://www.galaxy-network.net/#game/waves.php?action=Scannen&c1=' . $gal . '&c2=' . $plani . '&typ=news|N>

		if($umode)
			echo '<tr title="Urlaub" bgcolor="#ccaaaa">';
		else
			echo '<tr id="row'.$i.'" class="fieldnormal'.($color ? 'light' : 'dark').'">';
		echo '	<td>&nbsp;' . $meta . '&nbsp;</td>';
		echo '	<td>&nbsp;' . $ally . '&nbsp;</td>';
		echo '	<td align="right">&nbsp;<a href="main.php?modul=showgalascans&displaytype=1&xgala='.$g.'">&raquo; ' . $g . '</a>&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $p . '&nbsp;</td>';
		echo '	<td>&nbsp;<a href="main.php?modul=showgalascans&xgala='.$g.'&xplanet='.$p.'&displaytype=0">&raquo; ' . $name . '</a>&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . ($block_svs > 0 ? ZahlZuText($block_svs) : '-') . '&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $block_typ . '&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . ($age_s ? ZahlZuText($age_s) : '-') . '&nbsp;</td>';
		echo '	<td align="center">&nbsp;<a href="http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=sektor" target="_blank"><b>Scan S</b></a>&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . ($age_g ? ZahlZuText($age_g) : '-') . '&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $deffstr . '&nbsp;</td>';
		echo '	<td align="center">&nbsp;<a href="http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=gesch" target="_blank"><b>Scan G</b></a>&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . ($age_e ? ZahlZuText($age_e) : '-') . '&nbsp;</td>';
		echo '	<td align="center">&nbsp;<a href="http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=einheit" target="_blank"><b>Scan E</b></a>&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . ($age_m ? ZahlZuText($age_m) : '-') . '&nbsp;</td>';
		echo '	<td align="center">&nbsp;<a href="http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=mili" target="_blank"><b>Scan M</b></a>&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . ($age_n ? ZahlZuText($age_n) : '-') . '&nbsp;</td>';
		echo '	<td align="center">&nbsp;<a href="http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=news&news_kampf=1&news_scan=1&news_spenden=1&news_galaxy=1&news_allianz=1&news_tausch=1" target="_blank"><b>Scan N</b></a>&nbsp;</td>';
		echo '</tr>';

		$color = !$color;
	}
	
	if($num == 0) {
		echo '<tr class="fieldnormallight"><td colspan="18" align="center"><i>keine Eintr&auml;ge</i></td></tr>';
	}
?>
</table>
<?php
}
?>
</center>
