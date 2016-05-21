<?php
if($Benutzer['rang'] < $Rang_GC) {
	aprint('Keine Berechtigung.');
	die();
}

$hours = 36;
?>
<center>
<h2>Eingegangene Scanblocks (<?php echo $hours; ?>h)</h2>

<table width="100%">
	<tr class="datatablehead">
		<td colspan="5">&nbsp;Scanner&nbsp;</td>
		<td colspan="5">&nbsp;Ziel&nbsp;</td>
		<td></td>
		<td colspan="2"></td>
	</tr>
	<tr class="datatablehead">
		<td>&nbsp;Meta&nbsp;</td>
		<td>&nbsp;Allianz&nbsp;</td>
		<td>&nbsp;Galaxie&nbsp;</td>
		<td>&nbsp;Planet&nbsp;</td>
		<td>&nbsp;Spieler&nbsp;</td>
		<td>&nbsp;Meta&nbsp;</td>
		<td>&nbsp;Allianz&nbsp;</td>
		<td>&nbsp;Galaxie&nbsp;</td>
		<td>&nbsp;Planet&nbsp;</td>
		<td>&nbsp;Spieler&nbsp;</td>
		<td>&nbsp;Typ&nbsp;</td>
		<td>&nbsp;Datum&nbsp;</td>
		<td>&nbsp;Alter&nbsp;</td>
	</tr>
<?php
	$sql = "SELECT s1.meta meta1, s1.allianz_name ally1, s2.meta meta2, s2.allianz_name ally2, s1.spieler_name name1, s2.spieler_name name2, b.sg, b.sp, b.t, b.g, b.p, b.typ
			FROM gn4scanblock b 
			LEFT JOIN gn_spieler2 s1 ON s1.spieler_galaxie = b.sg AND s1.spieler_planet = b.sp
			LEFT JOIN gn_spieler2 s2 ON s2.spieler_galaxie = b.g AND s2.spieler_planet = b.p
			WHERE b.suspicious = 1 AND b.t > UNIX_TIMESTAMP(NOW()) - 60*60*".$hours." 
			ORDER BY s1.meta, s1.allianz_name, b.sg, b.sp, s2.meta, s2.allianz_name, b.g, b.p, b.typ, b.t DESC";
			
	//aprint($sql);
	$res = tic_mysql_query($sql) or tic_mysql_error(__FILE__, __LINE__);
	$num = mysql_num_rows($res);
	
	$color = true;
	for($i = 0; $i < $num; $i++) {
		$meta1 = mysql_result($res, $i, 'meta1');
		$meta2 = mysql_result($res, $i, 'meta2');
		$ally1 = mysql_result($res, $i, 'ally1');
		$ally2 = mysql_result($res, $i, 'ally2');
		$g1 = mysql_result($res, $i, 'sg');
		$g2 = mysql_result($res, $i, 'g');
		$p1 = mysql_result($res, $i, 'sp');
		$p2 = mysql_result($res, $i, 'p');
		$name1 = mysql_result($res, $i, 'name1');
		$name2 = mysql_result($res, $i, 'name2');
		$t = mysql_result($res, $i, 't');
		$typ;
		switch(mysql_result($res, $i, 'typ')) {
			case 0: $typ = 'S'; break;
			case 1: $typ = 'E'; break;
			case 2: $typ = 'M'; break;
			case 3: $typ = 'G'; break;
			case 4: $typ = 'N'; break;
			default: $typ = '<i>unknown</i>'; break;
		}
		$alter = round((time() - $t) / 60, 0);
		$farbe = round((1 - $alter / 60 / $hours) * 255);
		
		echo '<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
		echo '	<td>&nbsp;' . $meta1 . '&nbsp;</td>';
		echo '	<td>&nbsp;' . $ally1 . '&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $g1 . '&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $p1 . '&nbsp;</td>';
		echo '	<td>&nbsp;' . $name1 . '&nbsp;</td>';
		echo '	<td>&nbsp;' . $meta2 . '&nbsp;</td>';
		echo '	<td>&nbsp;' . $ally2 . '&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $g2 . '&nbsp;</td>';
		echo '	<td align="right">&nbsp;' . $p2 . '&nbsp;</td>';
		echo '	<td>&nbsp;' . $name2 . '&nbsp;</td>';
		echo '	<td>&nbsp;' . $typ . '&nbsp;</td>';
		echo '	<td>&nbsp;' . date('Y-m-d H:i', $t) . '&nbsp;</td>';
		echo '  <td align="right" style="color: white; background-color: rgb(' . $farbe . ', 0, 0);">&nbsp;' . ZahlZuText($alter) . '&nbsp;</td>';
		echo '</tr>';
		
		$color = !$color;
	}
?>
</table>
</center>
