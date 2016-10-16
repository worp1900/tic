<!-- START: menu.inc.php -->
<div class="navi">
<table>
	<tr>
		<td class="menutop">Taktik</td>
	</tr>
	<tr><td>
		<table cellspacing="1" cellpadding="0" style="background:#000000;width:100%">
			<tr>
				<td class="menu"><a href="./main.php?modul=taktikbildschirm&amp;mode=1"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Incomings</a></td>
			</tr>
			<tr>
				<td class="menu"><a href="./main.php?modul=taktikbildschirm&amp;mode=2"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Flotten</a></td>
			</tr>
<?php
	if ($Benutzer['rang']>=$Rang_GC) {
		echo "			<tr>\n";
		echo "				<td class=\"menu\"><a href=\"./main.php?modul=taktikbildschirm&amp;mode=3\"><img src=\"bilder/skin/menu_item_icon.bmp\" alt=\"\" style=\"padding:0px 5px 0px 5px;\" />Alles</a></td>\n";
		echo "		</tr>\n";
	}
	echo "			<tr>\n";
	echo "				<td class=\"menu\"><a href=\"./main.php?modul=taktikbildschirm&amp;mode=4&amp;allianz=".$Benutzer['allianz']."\"><img src=\"bilder/skin/menu_item_icon.bmp\" alt=\"\" style=\"padding:0px 5px 0px 5px;\" />Ally</a></td>\n";
	echo "			</tr>\n";
?>
			<tr>
				<td class="menu"><a href="./main.php?modul=taktikbildschirm&amp;mode=5"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Galaxie <?=$Benutzer['galaxie']?></a></td>
			</tr>
			<tr>
				<td class="menu"><a href="./main.php?modul=statistikmod"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Statistiken</a></td>
			</tr>
		</table>
	</td></tr>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="menutop">Tic-Intern</td>
	</tr>
	<tr><td>
		<table cellspacing="1" style="width:100%;background:#000000;">
			<!--<tr>
				<td class="menu"><a href="./main.php?modul=scans"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" /><i>Scan-Erfassen</i></a></td>
			</tr>-->
			<tr>
				<td class="menu" title="beta, bitte testen!"><a href="./main.php?modul=scans2"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Scan-Erfassen 2.0</a></td>
			</tr>
			<tr>
				<td class="menu"><a href="./main.php?modul=showgalascans&amp;displaytype=0&amp;xgala=<?=$Benutzer['galaxie']?>&amp;xplanet=<?=$Benutzer['planet']?>"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Scan-Datenbank</a></td>
			</tr>
			<tr>
				<td class="menu"><a href="./main.php?modul=scanrequest"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Scan-Anfragen<?php


				if($Benutzer['scananfragen']) {
					$num = 0;

					if($Benutzer['scantyp'] == 0 || $Benutzer['scantyp'] == 3 || $Benutzer['scantyp'] == 4) {
						$sql = "SELECT count(*) FROM (SELECT DISTINCT s.meta, s.allianz_name, r.ziel_g, r.ziel_p, s.spieler_name, s.spieler_punkte, r.scantyp, blocks.svs, blocks.typ, r.t
								FROM gn4scanrequests r
								LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = r.ziel_g AND s.spieler_planet = r.ziel_p
								LEFT JOIN (SELECT b1.g, b1.p, b1.svs, b1.typ
											FROM gn4scanblock b1
											WHERE b1.svs = (SELECT MAX(b2.svs)
															FROM gn4scanblock b2
															WHERE b2.g = b1.g AND b2.p = b1.p)
											) blocks
									ON blocks.g = r.ziel_g AND blocks.p = r.ziel_p
								WHERE (r.scantyp IN (0, 3) AND NOT EXISTS(
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
								) tmp";
						$num += mysql_result(tic_mysql_query($sql, __FILE__, __LINE__), 0, 0);
					}

					if($Benutzer['scantyp'] == 0 || $Benutzer['scantyp'] == 1 || $Benutzer['scantyp'] == 2) {
						$sql = "SELECT count(*) FROM (SELECT DISTINCT s.meta, s.allianz_name, r.ziel_g, r.ziel_p, s.spieler_name, s.spieler_punkte, r.scantyp, blocks.svs, blocks.typ, r.t
								FROM gn4scanrequests r
								LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = r.ziel_g AND s.spieler_planet = r.ziel_p
								LEFT JOIN (SELECT b1.g, b1.p, b1.svs, b1.typ
											FROM gn4scanblock b1
											WHERE b1.svs = (SELECT MAX(b2.svs)
															FROM gn4scanblock b2
															WHERE b2.g = b1.g AND b2.p = b1.p)
											) blocks
									ON blocks.g = r.ziel_g AND blocks.p = r.ziel_p
								WHERE r.scantyp IN (0, 1, 2) AND NOT EXISTS(
										SELECT * FROM
											(SELECT UNIX_TIMESTAMP(STR_TO_DATE(zeit, '%H:%i %d.%m.%Y')) t, `type`, rg, rp, g, p FROM gn4scans
											UNION
											SELECT t, `type`, rg, rp, g, p FROM gn4scans_history) x
										 WHERE rg = r.ziel_g AND rp = r.ziel_p AND `type` = r.scantyp AND t > r.t - 15 * 60
									)
									AND s.spieler_name IS NOT NULL
									AND r.deleted = 0
								) tmp";
						$num += mysql_result(tic_mysql_query($sql, __FILE__, __LINE__), 0, 0);
					}

					echo ' (';
					if($num > 0) {
						echo '<span style="color: red;">' . $num . '</span>';
					} else {
						echo $num;
					}
					echo ')';
				}

				?></a></td>
			</tr>
			<!--
			<tr>
				<td class="menu"><a href="./main.php?modul=forum&amp;faction=show&amp;falli=0&amp;ftopic=0"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Forum</a></td>
			</tr>
			//-->
			<tr>
				<td class="menu"><a href="./main.php"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Nachrichten</a></td>
			</tr>
			<tr>
				<td class="menu"><a href="./main.php?modul=profil"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Mein Profil</a></td>
			</tr>
			<tr>
<?php
	$sql1 = "SET @gal = '".$Benutzer['galaxie']."', @pla = '".$Benutzer['planet']."'";
	$sql2 = "SELECT	(SELECT COUNT(*) FROM gn4massinc_wellen w
						WHERE NOT EXISTS(SELECT * FROM gn4massinc_atter_willing aw WHERE aw.project_fk = w.project_fk AND aw.welle = w.id AND aw.atter_gal = @gal AND aw.atter_pla = @pla)
						AND (SELECT freigegeben FROM gn4massinc_projects WHERE project_id = w.project_fk) > 0) open,
					(SELECT COUNT(*) FROM gn4massinc_atter_willing aw
						WHERE aw.atter_gal = @gal AND aw.atter_pla = @pla AND aw.willing = 1 AND NOT EXISTS(SELECT * FROM gn4massinc_zuweisung zw WHERE aw.project_fk = zw.project_fk AND aw.welle = zw.welle AND zw.atter_gal = @gal AND zw.atter_pla = @pla)
						) decided,
					(SELECT COUNT(*) FROM gn4massinc_zuweisung zw WHERE zw.atter_gal = @gal AND zw.atter_pla = @pla) assigned";
	//aprint(join(";\n\n", array($sql1, $sql2)));
	tic_mysql_query($sql1, __FILE__, __LINE__);
	$res = tic_mysql_query($sql2, __FILE__, __LINE__);
	list($open, $decided, $assigned) = mysql_fetch_row($res);
?>
				<td class="menu"><a href="./main.php?modul=massinc"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Att-Planung <?php
	echo '(<span title="offen"'.($open > 0 ? ' style="color: red"' : '').'>';
	echo $open;
	echo '</span>/<span title="Flotten-Checkin"'.($decided > 0 ? ' style="color: green"' : '').'>';
	echo $decided;
	echo '</span>/<span title="zugewiesen"'.($assigned > 0 ? ' style="color: yellow"' : '').'>';
	echo $assigned;
	echo '</span>)';
				
				?></a></td>
			</tr>
			<tr>
				<td class="menu"><a href="./main.php?modul=scanliste"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Scannerliste</a></td>
			</tr>
			<tr>
				<td class="menu"><a href="./main.php?modul=NWshow"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Nachtwache</a></td>
			</tr>
			<tr>
				<td class="menu"><a href="help/help.html" target="tic-hilfe"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Hilfe</a></td>
			</tr>
			<tr>
				<td class="menu"><a href="./main.php"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />News</a></td>
			</tr>
		</table>
	</td></tr>
<?php
	if ($Benutzer['rang'] >= $Rang_GC) {
		echo "	<tr>\n";
		echo "		<td>&nbsp;</td>\n";
		echo "	</tr>\n";
		echo "	<tr>\n";
		echo "		<td class=\"menutop\">Admin</td>\n";
		echo "	</tr>\n";
		echo "	<tr><td>\n";
		echo "		<table cellspacing=\"1\" style=\"width:100%;background:#000000;\">\n";
		echo "			<tr>\n";
		echo "				<td class=\"menu\"><a href=\"./main.php?modul=management_alli\"><img src=\"bilder/skin/menu_item_icon.bmp\" alt=\"\" style=\"padding:0px 5px 0px 5px;\" />Allianz-Management</a></td>\n";
		echo "			</tr>\n";
		echo "			<tr>\n";
		echo "				<td class=\"menu\"><a href=\"./main.php?modul=management_meta\"><img src=\"bilder/skin/menu_item_icon.bmp\" alt=\"\" style=\"padding:0px 5px 0px 5px;\" />Meta-Management</a></td>\n";
		echo "			</tr>\n";
		/*
		if ($Benutzer['rang'] >= $Rang_Techniker) {
			echo "			<tr>\n";
			echo "				<td class=\"menu\"><a href=\"./main.php?modul=management_channels\"><img src=\"bilder/skin/menu_item_icon.bmp\" alt=\"\" style=\"padding:0px 5px 0px 5px;\" />Channel-Management</a></td>\n";
			echo "			</tr>\n";
		}
		*/
		echo "			<tr>\n";
		echo "				<td class=\"menu\"><a href=\"./main.php?modul=userman\"><img src=\"bilder/skin/menu_item_icon.bmp\" alt=\"\" style=\"padding:0px 5px 0px 5px;\" />Benutzerverwaltung</a></td>\n";
		echo "			</tr>\n";
		if ($Benutzer['rang'] > $Rang_GC) {
			echo "			<tr>\n";
			echo "				<td class=\"menu\"><a href=\"./main.php?modul=nachrichtschreiben\"><img src=\"bilder/skin/menu_item_icon.bmp\" alt=\"\" style=\"padding:0px 5px 0px 5px;\" />Nachricht</a></td>\n";
			echo "			</tr>\n";
		}
		if ($Benutzer['rang'] >= $Rang_Techniker) {
			echo "			<tr>\n";
			echo "				<td class=\"menu\"><a href=\"./main.php?modul=log\"><img src=\"bilder/skin/menu_item_icon.bmp\" alt=\"\" style=\"padding:0px 5px 0px 5px;\" />Log</a></td>\n";
			echo "			</tr>\n";
		}
		echo "		</table>\n";
		echo "	</td></tr>";
	}
?>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="menutop">Sonstiges</td>
	</tr>
	<tr><td>
		<table cellspacing="1" style="width:100%;background:#000000;">
			<tr>
				<td class="menu"><a href="./main.php?modul=kampf"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Kampf-Simu</a></td>
			</tr>
			<tr>
				<td class="menu"><a href="./main.php?modul=vag-rechner"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Vag-Rechner</a></td>
			</tr>
			<tr>
				<td class="menu"><a href="./main.php?modul=statistic"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />TIC Statistik</a></td>
			</tr>
			<tr>
				<td class="menu"><a href="./main.php?modul=listen"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Listen</a></td>
			</tr>
			<tr>
				<td class="menu"><a href="./main.php?modul=impressum"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Impressum</a></td>
			</tr>
			<tr>
				<td class="menu"><a href="./main.php?modul=logout"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Logout</a></td>
			</tr>
			<tr>
				<td class="menu"><a href="http://home.in.tum.de/~steilm/forschungsplan.htm" target="_blank"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Bauplan</a></td>
			</tr>
		</table>
	</td></tr>
<?php
if($Benutzer['rang'] >= $Rang_GC) {
?>
	<tr>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class="menutop">Eingefangene Scanblocks</td>
	</tr>
<?php
	$time_h = 36;
	$sql = "SELECT s.allianz_name, COUNT(*) blocks
FROM gn4scanblock sb
LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = sb.sg
AND s.spieler_planet = sb.sp
WHERE sb.suspicious = 1 AND sb.t > UNIX_TIMESTAMP(NOW()) - (".$time_h."*60*60)
GROUP BY s.allianz_name
ORDER BY s.allianz_name";
	$res = tic_mysql_query($sql);
	$num = mysql_num_rows($res);

	echo '<td><table title="Anzahl erfafasster Scanblocks der letzten '.$time_h.'h." cellspacing="1" style="width:100%;background:#000000;">';
	if($num > 0) {
		for($i = 0; $i < $num; $i++) {
			/*$t = mysql_result($res, $i, 't');
			$g = mysql_result($res, $i, 'g');
			$p = mysql_result($res, $i, 'p');
			$sg = mysql_result($res, $i, 'sg');
			$sp = mysql_result($res, $i, 'sp');
			$typ = mysql_result($res, $i, 'typ');

			echo '<tr><td>'.date('Y-m-d H:i', $t).'</td><td>'.$sg.':'.$sp.'</td><td>'.$g.':'.$p.'</td><td>'.$typ.'</td></tr>';*/
			$ally = mysql_result($res, $i, 'allianz_name');
			$tmp = strlen($ally);
			$ally = substr($ally, 0, 20);
			if($tmp > 20)
				$ally .= '...';
			$blocks = mysql_result($res, $i, 'blocks');

			echo '<tr><td bgcolor="#aaaaaa">'.$ally.'</td><td bgcolor="#aaaaaa" align="right">&nbsp;'.$blocks.'&nbsp;</td></tr>';
		}
	}
	echo '<tr><td class="menu" colspan="2"><a href="main.php?modul=scanblocks"><img src="bilder/skin/menu_item_icon.bmp" alt="" style="padding:0px 5px 0px 5px;" />Details</a></td></tr>';
	echo '</table><td>';
}
?>
</table>
</div>
<!-- END: menu.inc.php -->
