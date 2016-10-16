<h2>Scananfragen <?php

/*
echo ' - ';
if(postOrGet('mili')) {
	echo 'Todo Milit&auml;r';
} else if(postOrGet('gesch')) {
	echo ' Todo Gesch&uuml;tze';
} else {
	echo 'Deine!';
}*/

?></h2>

<a href="main.php?modul=scanrequest">&raquo; zu Deinen Scananfragen</a><br/>
<?php
if($Benutzer['scantyp'] == 0 || $Benutzer['scantyp'] == 1 || $Benutzer['scantyp'] == 2) {
	echo '<a href="main.php?modul=scanrequest&mili=1#mili">&raquo; zu den offenen MILI-Scananfragen</a><br/>';
}
if($Benutzer['scantyp'] == 0 || $Benutzer['scantyp'] == 3 || $Benutzer['scantyp'] == 4) {
	echo '<a href="main.php?modul=scanrequest&gesch=1#gesch">&raquo; zu den offenen GESCH-Scananfragen</a><br/>';
}
?>
<a href="main.php?modul=scanliste">&raquo; zur Scanliste</a><br/>
<br/>
<?php
if(postOrGet('allesbezahlt')) {
	$g = postOrGet('g');
	$p = postOrGet('p');

	$sql1 = "SET @rg = '" . mysql_real_escape_string($g) . "',
				@rp = '" . mysql_real_escape_string($p) . "',
				@g = '" . mysql_real_escape_string($Benutzer['galaxie']) . "',
				@p = '" . mysql_real_escape_string($Benutzer['planet']) . "';";
	$sql2 = "UPDATE gn4scanrequests r SET bezahlt = 1
			WHERE EXISTS (
				SELECT *
				FROM (
					SELECT UNIX_TIMESTAMP(STR_TO_DATE(zeit, '%H:%i %d.%m.%Y')) t, `type`, rg, rp, g, p, gen FROM gn4scans
					UNION
					SELECT t, `type`, rg, rp, g, p, gen FROM gn4scans_history
				) sc
				WHERE r.requester_g = @g AND r.requester_p = @p AND r.bezahlt = 0 AND sc.g = @rg AND sc.p = @rp
					AND sc.rg = r.ziel_g AND sc.rp = r.ziel_p AND sc.type = r.scantyp
					AND sc.t = (
						SELECT min(t) FROM (
							SELECT UNIX_TIMESTAMP(STR_TO_DATE(zeit, '%H:%i %d.%m.%Y')) t, `type`, rg, rp, g, p FROM gn4scans
							UNION
							SELECT t, `type`, rg, rp, g, p FROM gn4scans_history
						) tmp WHERE rg = r.ziel_g AND rp = r.ziel_p AND type = r.scantyp AND t > r.t
					)
			)";
	//aprint(join("\n\n", array($sql1, $sql2)));
	tic_mysql_query($sql1, __FILE__, __LINE__);
	tic_mysql_query($sql2, __FILE__, __LINE__);
}


if(postOrGet('bezahlt')) {
	$g = postOrGet('g');
	$p = postOrGet('p');
	$t = postOrGet('t');
	$tt = postOrGet('tt');

	$sql1 = "SET @g = '" . mysql_real_escape_string($g) . "',
				@p = '" . mysql_real_escape_string($p) . "',
				@t = '" . mysql_real_escape_string($t) . "',
				@tt = '" . mysql_real_escape_string($tt) . "';";
	$sql2 = "UPDATE gn4scanrequests SET bezahlt = 1 WHERE ziel_g = @g AND ziel_p = @p AND scantyp = @t AND t = @tt";
	//aprint(join("\n\n", array($sql1, $sql2)));
	tic_mysql_query($sql1, __FILE__, __LINE__);
	tic_mysql_query($sql2, __FILE__, __LINE__);
}


if(!postOrGet('mili') && !postOrGet('gesch') || true) {
?>
	<table width="100%">
		<tr class="datatablehead">
			<td colspan="10">&nbsp;Deine offenen Scananfragen&nbsp;</td>
		</tr>
		<tr class="fieldnormaldark" style="font-weight: bold">
			<td>&nbsp;Zeit&nbsp;</td>
			<td>&nbsp;Meta&nbsp;</td>
			<td>&nbsp;Allianz&nbsp;</td>
			<td>&nbsp;Galaxie&nbsp;</td>
			<td>&nbsp;Planet&nbsp;</td>
			<td>&nbsp;Spieler&nbsp;</td>
			<td>&nbsp;Punkte&nbsp;</td>
			<td>&nbsp;Typ&nbsp;</td>
			<td>&nbsp;Block&nbsp;</td>
			<td>&nbsp;Scans&nbsp;</td>
		</tr>
<?php
		$sql1 = "SET @g = '" . $Benutzer['galaxie'] . "', @p = '" . $Benutzer['planet'] . "';";
		$sql2 = "SELECT DISTINCT s.meta, s.allianz_name, r.ziel_g, r.ziel_p, s.spieler_name, s.spieler_punkte, r.scantyp, blocks.svs, blocks.typ, r.t
					FROM gn4scanrequests r
					LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = r.ziel_g AND s.spieler_planet = r.ziel_p
					LEFT JOIN (SELECT b1.g, b1.p, b1.svs, b1.typ
								FROM gn4scanblock b1
								WHERE b1.svs = (SELECT MAX(b2.svs)
												FROM gn4scanblock b2
												WHERE b2.g = b1.g AND b2.p = b1.p)
								) blocks
						ON blocks.g = r.ziel_g AND blocks.p = r.ziel_p
					WHERE r.requester_g = @g AND r.requester_p = @p AND deleted = 0
						AND
						( r.scantyp IN (0, 1, 2, 3)
							AND
							NOT EXISTS(
								SELECT * FROM
									(SELECT UNIX_TIMESTAMP(STR_TO_DATE(zeit, '%H:%i %d.%m.%Y')) t, `type`, rg, rp, g, p FROM gn4scans
									UNION
									SELECT t, `type`, rg, rp, g, p FROM gn4scans_history) x
								WHERE rg = r.ziel_g AND rp = r.ziel_p AND `type` = r.scantyp AND t > r.t - 15 * 60
							)
							OR
							r.scantyp = 4
							AND NOT EXISTS(
								SELECT * FROM gn4scans_news WHERE ziel_g = r.ziel_g AND ziel_p = r.ziel_p AND t > r.t
							)
						)
						AND s.spieler_name IS NOT NULL
						AND r.deleted = 0
					ORDER BY r.t ASC";
		//aprint(join("\n\n", array($sql1, $sql2)));
		tic_mysql_query($sql1, __FILE__, __LINE__);
		$res = tic_mysql_query($sql2, __FILE__, __LINE__);
		$num = mysql_num_rows($res);
		$color = false;
		while(list($meta, $allianz, $galaxie, $planet, $spieler, $punkte, $typ, $block_svs, $block_typ, $t) = mysql_fetch_row($res)) {
			$color = !$color;
			$block = '-';
			if(!is_null($block_typ)) {
				$block = ZahlZuText($block_svs) . ' ' . scanTypeName($block_typ, true);
			}
			echo '<tr class="fieldnormal' . ($color ? 'light' :  'dark') . '">';
			echo '	<td align="left">&nbsp;' . date('Y-m-d H:i', $t) . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $meta . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $allianz . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . $galaxie . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . $planet . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $spieler . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . ZahlZuText($punkte) . '&nbsp;</td>';
			echo '	<td>&nbsp;' . scanTypeName($typ) . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $block . '&nbsp;</td>';
			echo '	<td>&nbsp;<a href="main.php?modul=showgalascans&displaytype=0&xgala='.$galaxie.'&xplanet='.$planet.'">&raquo; Scans</a>&nbsp;</td>';
			echo '</tr>';
		}

		if($num == 0) {
			echo '<tr class="fieldnormallight"><td colspan="10"><i>keine</i></td></tr>';
		}
?>
	</table>
	<br/>
	<table width="100%">
		<tr class="datatablehead">
			<td colspan="13">&nbsp;Deine bearbeiteten Scananfragen (bezahlt &lt24h)&nbsp;</td>
		</tr>
		<tr class="fieldnormaldark" style="font-weight: bold">
			<td>&nbsp;Zeit&nbsp;</td>
			<td>&nbsp;Meta&nbsp;</td>
			<td>&nbsp;Allianz&nbsp;</td>
			<td>&nbsp;Galaxie&nbsp;</td>
			<td>&nbsp;Planet&nbsp;</td>
			<td>&nbsp;Spieler&nbsp;</td>
			<td>&nbsp;Punkte&nbsp;</td>
			<td>&nbsp;Typ&nbsp;</td>
			<td>&nbsp;Genauigkeit&nbsp;</td>
			<td>&nbsp;Danke an&nbsp;</td>
			<td colspan="2">&nbsp;Bezahlt&nbsp;</td>
			<td>&nbsp;Abruf&nbsp;</td>
		</tr>
<?php
		$sql1 = "SET @g = '" . $Benutzer['galaxie'] . "', @p = '" . $Benutzer['planet'] . "';";
		$sql2 = "SELECT s.meta, s.allianz_name, r.ziel_g, r.ziel_p, s.spieler_name, s.spieler_punkte, r.scantyp, sc.g, sc.p, sc.gen, sc.t, s2.spieler_name, r.bezahlt, r.t
					FROM gn4scanrequests r
					LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = r.ziel_g AND s.spieler_planet = r.ziel_p
					LEFT JOIN (
						SELECT UNIX_TIMESTAMP(STR_TO_DATE(zeit, '%H:%i %d.%m.%Y')) t, `type`, rg, rp, g, p, gen FROM gn4scans
						UNION
						SELECT t, `type`, rg, rp, g, p, gen FROM gn4scans_history
					) sc
						ON sc.rg = r.ziel_g AND sc.rp = r.ziel_p AND sc.type = r.scantyp
							AND sc.t = (
								SELECT min(t) FROM (
									SELECT UNIX_TIMESTAMP(STR_TO_DATE(zeit, '%H:%i %d.%m.%Y')) t, `type`, rg, rp, g, p FROM gn4scans
									UNION
									SELECT t, `type`, rg, rp, g, p FROM gn4scans_history
								) tmp WHERE rg = r.ziel_g AND rp = r.ziel_p AND type = r.scantyp AND t > r.t - 15 * 60
							)
					LEFT JOIN gn_spieler2 s2 ON s2.spieler_galaxie = sc.g AND s2.spieler_planet = sc.p
					WHERE r.requester_g = @g AND r.requester_p = @p AND (r.bezahlt = 0 OR r.t > UNIX_TIMESTAMP(NOW()) - 24*60*60) AND deleted = 0
					AND (
						r.scantyp IN (0, 1, 2, 3)
						AND
						EXISTS(
							SELECT * FROM
									(SELECT UNIX_TIMESTAMP(STR_TO_DATE(zeit, '%H:%i %d.%m.%Y')) t, `type`, rg, rp, g, p FROM gn4scans
									UNION
									SELECT t, `type`, rg, rp, g, p FROM gn4scans_history) x
								 WHERE rg = r.ziel_g AND rp = r.ziel_p AND `type` = r.scantyp AND t > r.t - 15 * 60
						)
						OR
						r.scantyp = 4
						AND EXISTS(
							SELECT * FROM
									(SELECT UNIX_TIMESTAMP(STR_TO_DATE(zeit, '%H:%i %d.%m.%Y')) t, `type`, rg, rp, g, p FROM gn4scans
									UNION
									SELECT t, `type`, rg, rp, g, p FROM gn4scans_history) x
							 WHERE ziel_g = r.ziel_g AND ziel_p = r.ziel_p AND t > r.t
						)
					)
					AND s.spieler_name IS NOT NULL
					AND r.deleted = 0
					ORDER BY r.t ASC";
		//aprint(join("\n\n", array($sql1, $sql2)));
		tic_mysql_query($sql1, __FILE__, __LINE__);
		$res = tic_mysql_query($sql2, __FILE__, __LINE__);
		$num = mysql_num_rows($res);
		$color = false;
		while(list($meta, $allianz, $galaxie, $planet, $spieler, $punkte, $typ, $scanner_g, $scanner_p, $gen, $scan_t, $scanner, $bezahlt, $t) = mysql_fetch_row($res)) {
			$color = !$color;

			$bezahlung = 0;
			switch($typ) {
				case 0: $bezahlung = 2000; break;
				case 1:
				case 3: $bezahlung = 4000; break;
				case 2:
				case 4: $bezahlung = 8000; break;
			}
			$bezahllink = '<a  title="Bezahle ' . ZahlZuText(round($bezahlung * $scanbezahlungfaktor, 0)) . ' Kristall"href="http://www.galaxy-network.net/game/rohstoffe.php?transfer1=' . $scanner_g . '&transfer2=' . $scanner_p . '&summe=' . round($bezahlung * $scanbezahlungfaktor, 0) . '&transfer_typ=Kristall&spenden_grund=Scanbezahlung" target="_blank">&raquo; $ Nein</a>';

			echo '<tr class="fieldnormal' . ($color ? 'light' :  'dark') . '">';
			echo '	<td align="left">&nbsp;' . date('Y-m-d H:i', $t) . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $meta . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $allianz . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . $galaxie . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . $planet . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $spieler . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . ZahlZuText($punkte) . '&nbsp;</td>';
			echo '	<td>&nbsp;' . scanTypeName($typ) . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . $gen . '%&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $scanner_g . ':' . $scanner_p . ' ' . $scanner . '</a>&nbsp;</td>';
			if($bezahlt) {
				echo '	<td colspan="2">&nbsp;Ja&nbsp;</td>';
			} else {
				echo '	<td>&nbsp;' . $bezahllink . '&nbsp;</td>';
				echo '	<td>&nbsp;<a href="main.php?modul=scanrequest&bezahlt=1&g=' . $galaxie . '&p=' . $planet . '&t=' . $typ . '&tt=' . $t . '">&raquo; Jetzt ja</a>&nbsp;</td>';
			}
			echo '	<td>&nbsp;<a href="main.php?modul=showgalascans&displaytype=0&xgala='.$galaxie.'&xplanet='.$planet.'">&raquo; Scans</a>&nbsp;</td>';
			echo '</tr>';
		}

		if($num == 0) {
			echo '<tr class="fieldnormallight"><td colspan="13"><i>keine</i></td></tr>';
		}
?>
	</table>

	<br/>
	<table width="100%">
		<tr class="datatablehead">
			<td colspan="13">&nbsp;Deine Scanschulden&nbsp;</td>
		</tr>
		<tr class="fieldnormaldark" style="font-weight: bold">
			<td>&nbsp;Galaxie&nbsp;</td>
			<td>&nbsp;Planet&nbsp;</td>
			<td>&nbsp;Spieler&nbsp;</td>
			<td>&nbsp;Kosten&nbsp;</td>
			<td colspan="2">&nbsp;Alles bezahlt&nbsp;</td>
		</tr>
<?php
		$sql1 = "SET @g = '" . $Benutzer['galaxie'] . "', @p = '" . $Benutzer['planet'] . "', @faktor = '" . $scanbezahlungfaktor . "';";
		$sql2 = "SELECT sc.g, sc.p, s2.spieler_name,
					SUM(
					CASE r.scantyp
						WHEN 0 THEN 2000
						WHEN 1 THEN 4000
						WHEN 2 THEN 8000
						WHEN 3 THEN 4000
						WHEN 4 THEN 8000
						ELSE NULL
					END) * @faktor AS kosten
					FROM gn4scanrequests r
					LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = r.ziel_g AND s.spieler_planet = r.ziel_p
					LEFT JOIN (
						SELECT UNIX_TIMESTAMP(STR_TO_DATE(zeit, '%H:%i %d.%m.%Y')) t, `type`, rg, rp, g, p, gen FROM gn4scans
						UNION
						SELECT t, `type`, rg, rp, g, p, gen FROM gn4scans_history
					) sc
						ON sc.rg = r.ziel_g AND sc.rp = r.ziel_p AND sc.type = r.scantyp
							AND sc.t = (
								SELECT min(t) FROM (
									SELECT UNIX_TIMESTAMP(STR_TO_DATE(zeit, '%H:%i %d.%m.%Y')) t, `type`, rg, rp, g, p FROM gn4scans
									UNION
									SELECT t, `type`, rg, rp, g, p FROM gn4scans_history
								) tmp WHERE rg = r.ziel_g AND rp = r.ziel_p AND type = r.scantyp AND t > r.t
							)
					LEFT JOIN gn_spieler2 s2 ON s2.spieler_galaxie = sc.g AND s2.spieler_planet = sc.p
					WHERE r.requester_g = @g AND r.requester_p = @p AND r.bezahlt = 0 AND deleted = 0
					AND
					(
						r.scantyp IN (0, 1, 2, 3)
						AND
						EXISTS(
							SELECT * FROM
									(SELECT UNIX_TIMESTAMP(STR_TO_DATE(zeit, '%H:%i %d.%m.%Y')) t, `type`, rg, rp, g, p FROM gn4scans
									UNION
									SELECT t, `type`, rg, rp, g, p FROM gn4scans_history) x
							WHERE rg = r.ziel_g AND rp = r.ziel_p AND `type` = r.scantyp AND t > r.t
						)
						OR
						r.scantyp = 4
						AND EXISTS(
							SELECT * FROM gn4scans_news WHERE ziel_g = r.ziel_g AND ziel_p = r.ziel_p AND t > r.t - 15 * 60
						)
					)
					AND s.spieler_name IS NOT NULL
					GROUP BY sc.g, sc.p, s2.spieler_name
					ORDER BY sc.g, sc.p ASC";
		//aprint(join("\n\n", array($sql1, $sql2)));
		tic_mysql_query($sql1, __FILE__, __LINE__);
		$res = tic_mysql_query($sql2, __FILE__, __LINE__);
		$num = mysql_num_rows($res);
		$color = false;
		$sum = 0;
		while(list($g, $p, $spieler, $kosten) = mysql_fetch_row($res)) {
			$color = !$color;
			$sum += $kosten;

			echo '<tr class="fieldnormal' . ($color ? 'light' :  'dark') . '">';
			echo '	<td align="right">&nbsp;' . $g . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . $p . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $spieler . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . ZahlZuText($kosten) . ' K&nbsp;</td>';
			echo '	<td>&nbsp;<a  title="Bezahle ' . ZahlZuText($kosten) . ' Kristall"href="http://www.galaxy-network.net/game/rohstoffe.php?transfer1=' . $g . '&transfer2=' . $p . '&summe=' . $kosten . '&transfer_typ=Kristall&spenden_grund=Scanbezahlung" target="_blank">&raquo; $ Noch nicht</a>&nbsp;</td>';
			echo '	<td>&nbsp;<a href="main.php?modul=scanrequest&allesbezahlt=1&g=' . $g . '&p=' . $p . '">&raquo; Jetzt ja</a>&nbsp;</td>';
			echo '</tr>';
		}

		if($num == 0) {
			echo '<tr class="fieldnormallight"><td colspan="5"><i>keine</i></td></tr>';
		} else {
			echo '<tr class="fieldnormaldark" style="font-weight: bold;"><td colspan="3"></td><td align="right">&nbsp;' . ZahlZuText($sum) . ' K&nbsp;</td><td colspan="2"></td></tr>';
		}
?>
	</table>
	<br/>
	<hr/>
<?php
}

if((postOrGet('mili') || true) && ($Benutzer['scantyp'] == 0 || $Benutzer['scantyp'] == 1 || $Benutzer['scantyp'] == 2)) {
	?>	<br/><a name="mili"></a><table width="100%">
		<tr class="datatablehead">
			<td colspan="10">&nbsp;Offene MILI Scananfragen&nbsp;</td>
			<td>&nbsp;<a href="main.php?modul=scanrequest&mili=1#mili">&raquo; Refresh</a>&nbsp;</td>
		</tr>
		<tr class="fieldnormaldark" style="font-weight: bold">
			<td>&nbsp;Zeit&nbsp;</td>
			<td>&nbsp;Meta&nbsp;</td>
			<td>&nbsp;Allianz&nbsp;</td>
			<td>&nbsp;Galaxie&nbsp;</td>
			<td>&nbsp;Planet&nbsp;</td>
			<td>&nbsp;Spieler&nbsp;</td>
			<td>&nbsp;Punkte&nbsp;</td>
			<td>&nbsp;Typ&nbsp;</td>
			<td>&nbsp;Block&nbsp;</td>
			<td>&nbsp;Scans&nbsp;</td>
			<td>&nbsp;Scanlink&nbsp;</td>
		</tr>
<?php
		$sql = "SELECT DISTINCT s.meta, s.allianz_name, r.ziel_g, r.ziel_p, s.spieler_name, s.spieler_punkte, r.scantyp, blocks.svs, blocks.typ, r.t
					FROM gn4scanrequests r
					LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = r.ziel_g AND s.spieler_planet = r.ziel_p
					LEFT JOIN (SELECT b1.g, b1.p, b1.svs, b1.typ
								FROM gn4scanblock b1
								WHERE b1.svs = (SELECT MAX(b2.svs)
												FROM gn4scanblock b2
												WHERE b2.g = b1.g AND b2.p = b1.p)
								) blocks
						ON blocks.g = r.ziel_g AND blocks.p = r.ziel_p
					WHERE r.scantyp IN (0, 1, 2) AND deleted = 0 AND NOT EXISTS(
							SELECT * FROM
									(SELECT UNIX_TIMESTAMP(STR_TO_DATE(zeit, '%H:%i %d.%m.%Y')) t, `type`, rg, rp, g, p FROM gn4scans
									UNION
									SELECT t, `type`, rg, rp, g, p FROM gn4scans_history) x
							WHERE rg = r.ziel_g AND rp = r.ziel_p AND `type` = r.scantyp AND t > r.t - 15 * 60
						)
						AND s.spieler_name IS NOT NULL
						AND r.deleted = 0
					ORDER BY r.t ASC";
		//aprint($sql);
		$res = tic_mysql_query($sql, __FILE__, __LINE__);
		$num = mysql_num_rows($res);
		$color = false;
		while(list($meta, $allianz, $g, $p, $spieler, $punkte, $typ, $block_svs, $block_typ, $t) = mysql_fetch_row($res)) {
			$color = !$color;
			$block = '-';
			if(!is_null($block_typ)) {
				$block = ZahlZuText($block_svs) . ' ' . scanTypeName($block_typ, true);
			}

			$url = '';
			switch($typ) {
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
			}


			echo '<tr class="fieldnormal' . ($color ? 'light' :  'dark') . '">';
			echo '	<td align="left">&nbsp;' . date('Y-m-d H:i', $t) . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $meta . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $allianz . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . $g . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . $p . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $spieler . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . ZahlZuText($punkte) . '&nbsp;</td>';
			echo '	<td>&nbsp;' . scanTypeName($typ) . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $block . '&nbsp;</td>';
			echo '	<td>&nbsp;<a href="main.php?modul=showgalascans&displaytype=0&xgala='.$g.'&xplanet='.$p.'">&raquo; Scans</a>&nbsp;</td>';
			echo '	<td>&nbsp;<a href="' . $url . '" target="_blank">&raquo; ' . scanTypeName($typ, true) . ' scannen</a>&nbsp;</td>';
			echo '</tr>';
		}

		if($num == 0) {
			echo '<tr class="fieldnormallight"><td colspan="11"><i>keine</i></td></tr>';
		}
?>
	</table>
	<?php
}

if((postOrGet('gesch') || true) && ($Benutzer['scantyp'] == 0 || $Benutzer['scantyp'] == 3 || $Benutzer['scantyp'] == 4)) {
	?>	<br/><a name="gesch"></a><table width="100%">
		<tr class="datatablehead">
			<td colspan="10">&nbsp;Offene GESCH Scananfragen&nbsp;</td>
			<td>&nbsp;<a href="main.php?modul=scanrequest&mili=1#gesch">&raquo; Refresh</a>&nbsp;</td>
		</tr>
		<tr class="fieldnormaldark" style="font-weight: bold">
			<td>&nbsp;Zeit&nbsp;</td>
			<td>&nbsp;Meta&nbsp;</td>
			<td>&nbsp;Allianz&nbsp;</td>
			<td>&nbsp;Galaxie&nbsp;</td>
			<td>&nbsp;Planet&nbsp;</td>
			<td>&nbsp;Spieler&nbsp;</td>
			<td>&nbsp;Punkte&nbsp;</td>
			<td>&nbsp;Typ&nbsp;</td>
			<td>&nbsp;Block&nbsp;</td>
			<td>&nbsp;Scans&nbsp;</td>
			<td>&nbsp;Scanlink&nbsp;</td>
		</tr>
<?php
		$sql = "SELECT DISTINCT s.meta, s.allianz_name, r.ziel_g, r.ziel_p, s.spieler_name, s.spieler_punkte, r.scantyp, blocks.svs, blocks.typ, r.t
					FROM gn4scanrequests r
					LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = r.ziel_g AND s.spieler_planet = r.ziel_p
					LEFT JOIN (SELECT b1.g, b1.p, b1.svs, b1.typ
								FROM gn4scanblock b1
								WHERE b1.svs = (SELECT MAX(b2.svs)
												FROM gn4scanblock b2
												WHERE b2.g = b1.g AND b2.p = b1.p)
								) blocks
						ON blocks.g = r.ziel_g AND blocks.p = r.ziel_p
					WHERE (r.scantyp IN (0, 3) AND deleted = 0 AND NOT EXISTS(
							SELECT * FROM
									(SELECT UNIX_TIMESTAMP(STR_TO_DATE(zeit, '%H:%i %d.%m.%Y')) t, `type`, rg, rp, g, p FROM gn4scans
									UNION
									SELECT t, `type`, rg, rp, g, p FROM gn4scans_history) x
							WHERE rg = r.ziel_g AND rp = r.ziel_p AND `type` = r.scantyp AND t > r.t - 15 * 60
						)
						OR r.scantyp = 4 AND NOT EXISTS(
							SELECT * FROM gn4scans_news WHERE ziel_g = r.ziel_g AND ziel_p = r.ziel_p AND t > r.t
						)
						)
						AND s.spieler_name IS NOT NULL
						AND r.deleted = 0
					ORDER BY r.t ASC";
		//aprint($sql);
		$res = tic_mysql_query($sql, __FILE__, __LINE__);
		$num = mysql_num_rows($res);
		$color = false;
		while(list($meta, $allianz, $g, $p, $spieler, $punkte, $typ, $block_svs, $block_typ, $t) = mysql_fetch_row($res)) {
			$color = !$color;
			$block = '-';
			if(!is_null($block_typ)) {
				$block = ZahlZuText($block_svs) . ' ' . scanTypeName($block_typ, true);
			}

			$url = '';
			switch($typ) {
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
			}


			echo '<tr class="fieldnormal' . ($color ? 'light' :  'dark') . '">';
			echo '	<td align="left">&nbsp;' . date('Y-m-d H:i', $t) . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $meta . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $allianz . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . $g . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . $p . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $spieler . '&nbsp;</td>';
			echo '	<td align="right">&nbsp;' . ZahlZuText($punkte) . '&nbsp;</td>';
			echo '	<td>&nbsp;' . scanTypeName($typ) . '&nbsp;</td>';
			echo '	<td align="left">&nbsp;' . $block . '&nbsp;</td>';
			echo '	<td>&nbsp;<a href="main.php?modul=showgalascans&displaytype=0&xgala='.$g.'&xplanet='.$p.'">&raquo; Scans</a>&nbsp;</td>';
			echo '	<td>&nbsp;<a href="' . $url . '" target="_blank">&raquo; ' . scanTypeName($typ, true) . ' scannen</a>&nbsp;</td>';
			echo '</tr>';
		}

		if($num == 0) {
			echo '<tr class="fieldnormallight"><td colspan="11"><i>keine</i></td></tr>';
		}
?>
	</table>
<?php
}
?>
