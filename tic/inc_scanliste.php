<?php
$hours = 36;
?>
<center>
<h2>Scanliste</h2>

<?php
	$to_be_scanned = array();
	$to_be_scanned_meta = array();

	$where = ' (0 ';
	
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
		<td colspan="4">Metas</td>
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
		<td colspan="4">Allianzen</td>
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
		echo '<option value="'.$ally.'">'.$ally.' - '.$meta.'</option>';
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
		<td colspan="4">Galaxien</td>
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
		echo '<option value="'.$gal.'">'.$gal.' - '.$ally.'</option>';
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
	<tr class="fieldnormaldark">
		<td colspan="4" align="right"><input type="submit" value="Liste erstellen"/></td>
	</tr>
</table>
</form>
<br/>
<table width="100%">
	<tr class="datatablehead">
		<td>&nbsp;Meta&nbsp;</td>
		<td>&nbsp;Allianz&nbsp;</td>
		<td>&nbsp;Galaxie&nbsp;</td>
		<td>&nbsp;Planet&nbsp;</td>
		<td>&nbsp;Spieler&nbsp;</td>
		<td colspan="2">&nbsp;Blocks&nbsp;</td>
		<td colspan="2">&nbsp;Sektor&nbsp;</td>
		<td colspan="2">&nbsp;Gesch&uuml;tze&nbsp;</td>
		<td colspan="2">&nbsp;Einheiten&nbsp;</td>
		<td colspan="2">&nbsp;Milit&auml;r&nbsp;</td>
		<td colspan="2">&nbsp;Nachrichten&nbsp;</td>
		<td>&nbsp;Scandb&nbsp;</td>
	</tr>
<?php

	$where = '0';
	foreach($to_be_scanned as $v) {
		$v = explode(':', $v);
		$where .= ' OR (s.spieler_galaxie = '.$v[0].' AND s.spieler_planet = '.$v[1].')';
	}
	$sql = "SELECT 
			s.meta, 
			s.allianz_name ally, 
			s.spieler_galaxie g, 
			s.spieler_planet p, 
			s.spieler_name name, 
			s.spieler_urlaub,
			round((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(STR_TO_DATE(scans0.zeit, '%H:%i %d.%m.%Y')))/-60, 0) t0,
			round((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(STR_TO_DATE(scans1.zeit, '%H:%i %d.%m.%Y')))/-60, 0) t1,
			round((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(STR_TO_DATE(scans2.zeit, '%H:%i %d.%m.%Y')))/-60, 0) t2,
			round((UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(STR_TO_DATE(scans3.zeit, '%H:%i %d.%m.%Y')))/-60, 0) t3,
			round((UNIX_TIMESTAMP(NOW()) - scans4.t)/-60, 0) t4,
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
		echo '	<td align="right">&nbsp;' . $g . '&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $p . '&nbsp;</td>';
		echo '	<td>&nbsp;' . $name . '&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . ZahlZuText($block_svs) . '&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $block_typ . '&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $age_s . '&nbsp;</td>';
		echo '	<td align="center">&nbsp;<a href="http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=sektor" target="_blank"><b>Scan S</b></a>&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $age_g . '&nbsp;</td>';
		echo '	<td align="center">&nbsp;<a href="http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=gesch" target="_blank"><b>Scan G</b></a>&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $age_e . '&nbsp;</td>';
		echo '	<td align="center">&nbsp;<a href="http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=einheit" target="_blank"><b>Scan E</b></a>&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $age_m . '&nbsp;</td>';
		echo '	<td align="center">&nbsp;<a href="http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=mili" target="_blank"><b>Scan M</b></a>&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $age_n . '&nbsp;</td>';
		echo '	<td align="center">&nbsp;<a href="http://www.galaxy-network.net/game/waves.php?action=Scannen&c1=' . $g . '&c2=' . $p . '&typ=news" target="_blank"><b>Scan N</b></a>&nbsp;</td>';
		echo '	<td align="center"><a href="main.php?modul=showgalascans&xgala='.$g.'&xplanet='.$p.'&displaytype=0">Details</a></td>';
		echo '</tr>';
		
		$color = !$color;
	}
?>
</table>
</center>
