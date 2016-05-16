<script>
  function copyToClipboard(text) {
    window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
  }
</script>
<?php
	$limit = 20;

	function xformat($number) {
		if(is_numeric($number))
			return number_format($number);
		return $number;
	}

	function nformat($number, $totalLen) {
		$out = $number = xformat($number);
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
?>
	<h2>Scanarchiv</h2>
	<table width="100%" cellspacing="0" cellpadding="0">
		<tr><td class="datatablehead">Scanarchiv</td></tr>
		<tr class="fieldnormallight">
			<td valign="top" width="30%">
				<form method="get" action="./main.php">
					<input type="hidden" name="modul" value="scanarchiv" />
					<table width="100%" cellspacing="0" cellpadding="3">
						<tr><td class="datatablehead">Spezieller Planet</td></tr>
						<tr><td>Einzelner Planet</td></tr>
						<tr><td>Gala:Planet <input type="text" name="xgala" size="4" value="<?=(isset($xgala) ? $xgala : "")?>" /> : <input type="text" name="xplanet" size="2" value="<?=(isset($xplanet) ? $xplanet : "")?>" /></td></tr>
						<tr><td align="right"><input type="submit" value="Anzeigen" /></td></tr>
					</table>
				</form>
			</td>
		</tr>
	</table>
	<form action="./main.php?modul=scans" method="post">
		<input type="hidden" name="txtScanGalaxie" value="<?= (isset($xgala) ? $xgala : "") ?>" />
		<input type="hidden" name="txtScanPlanet" value="<?= (isset($xplanet) ? $xplanet : "") ?>" />
		<input type="submit" value="Zur Datenerfassung" />
	</form>
	<br />
<?php
if(!$xgala || !$xplanet) {
	echo '<font color="#800000" size="-1"><b>Keine Koordinaten ausgew&auml;hlt.</b></font>';
} else {
	$sql_name = "SELECT name FROM gn4gnuser WHERE gala = '".mysql_real_escape_string($xgala)."' AND planet = '".mysql_real_escape_string($xplanet)."' LIMIT 1";
	$res_name = tic_mysql_query($sql_name) or die(tic_mysql_error(__FILE__,__LINE__));
	$name = @mysql_result($res_name, 0, 'name');

	$playerStr = $xgala . ':' . $xplanet . ' ' . $name;
	

	?>
	<p><center><b><?php echo $playerStr; ?></b> - <a href="#sek">&raquo; Sektor</a> <a href="#deff">&raquo; Gesch&uuml;tze</a> <a href="#unit">&raquo; Einheiten</a> <a href="#mili">&raquo; Milit&auml;r</a> <a href="#news">&raquo; Nachrichten</a> <a href="javascript:history.back();"><b>zur&uuml;ck</b></a></center></p>
			<a name="sek"></a>
			<table width="100%">
				<tr>
					<td colspan="15" class="datatablehead">Sektorscans</td>
				</tr>
				<tr>
					<td class="fieldnormaldark"><b>Datum</b></td>
					<td class="fieldnormaldark"><b>Genuaigkeit</b></td>
					<td class="fieldnormaldark"><b>SVS</b></td>
					<td class="fieldnormaldark"><b>Punkte</b></td>
					<td class="fieldnormaldark"><b>MetExen</b></td>
					<td class="fieldnormaldark"><b>KrisExen</b></td>
					<td class="fieldnormaldark"><b>Schiffe</b></td>
					<td class="fieldnormaldark"><b>Defensiv</b></td>
				</tr>
<?php
	$sql_s = "SELECT t, gen, erfasser_svs, pts, me, ke, s, d FROM gn4scans_history WHERE rg = '".mysql_real_escape_string($xgala)."' AND rp = '".mysql_real_escape_string($xplanet)."' AND type = 0 ORDER BY t DESC LIMIT " . $limit;

	$res_s = tic_mysql_query($sql_s) or die(tic_mysql_error(__FILE__,__LINE__));
	$num_s = mysql_num_rows($res_s);
	
	$color = true;
	for($i = 0; $i < $num_s; $i++) {
		$row = mysql_fetch_row($res_s);
		$color = !$color;
		
		echo '<tr class="fieldnormal'.($color ? 'dark' : 'light').'">';
		echo '	<td>'.date('Y-m-d H:i:s', $row[0]).'</td>';
		echo '	<td>'.$row[1].'%</td>';
		echo '	<td>'.ZahlZuText($row[2]).'</td>';
		echo '	<td>'.ZahlZuText($row[3]).'</td>';
		echo '	<td>'.ZahlZuText($row[4]).'</td>';
		echo '	<td>'.ZahlZuText($row[5]).'</td>';
		echo '	<td>'.ZahlZuText($row[6]).'</td>';
		echo '	<td>'.ZahlZuText($row[7]).'</td>';
		echo '</tr>';
	}
	mysql_free_result($res_s);
?>
			</table><br/>

			<a name="deff"></a>
			<table width="100%">
				<tr>
					<td colspan="15" class="datatablehead">Gesch&uuml;tzscans</td>
				</tr>
				<tr>
					<td class="fieldnormaldark"><b>Datum</b></td>
					<td class="fieldnormaldark"><b>Genuaigkeit</b></td>
					<td class="fieldnormaldark"><b>SVS</b></td>
					<td class="fieldnormaldark"><b>AJ</b></td>
					<td class="fieldnormaldark"><b>LO</b></td>
					<td class="fieldnormaldark"><b>LR</b></td>
					<td class="fieldnormaldark"><b>MR</b></td>
					<td class="fieldnormaldark"><b>SR</b></td>
				</tr>
<?php
	$sql_g = "SELECT t, gen, erfasser_svs, ga, glo, glr, gmr, gsr FROM gn4scans_history WHERE rg = '".mysql_real_escape_string($xgala)."' AND rp = '".mysql_real_escape_string($xplanet)."' AND type = 3 ORDER BY t DESC LIMIT " . $limit;

	$res_g = tic_mysql_query($sql_g) or die(tic_mysql_error(__FILE__,__LINE__));
	$num_g = mysql_num_rows($res_g);
	
	$color = true;
	for($i = 0; $i < $num_g; $i++) {
		$row = mysql_fetch_row($res_g);
		$color = !$color;
		
		echo '<tr class="fieldnormal'.($color ? 'dark' : 'light').'">';
		echo '	<td>'.date('Y-m-d H:i:s', $row[0]).'</td>';
		echo '	<td>'.$row[1].'%</td>';
		echo '	<td>'.ZahlZuText($row[2]).'</td>';
		echo '	<td>'.ZahlZuText($row[3]).'</td>';
		echo '	<td>'.ZahlZuText($row[4]).'</td>';
		echo '	<td>'.ZahlZuText($row[5]).'</td>';
		echo '	<td>'.ZahlZuText($row[6]).'</td>';
		echo '	<td>'.ZahlZuText($row[7]).'</td>';
		echo '</tr>';
	}
	mysql_free_result($res_g);
?>
			</table><br/>

			<a name="unit"></a>
			<table width="100%">
				<tr>
					<td colspan="15" class="datatablehead">Einheitenscans</td>
				</tr>
				<tr>
					<td class="fieldnormaldark"><b>Datum</b></td>
					<td class="fieldnormaldark"><b>Genuaigkeit</b></td>
					<td class="fieldnormaldark"><b>SVS</b></td>
					<td class="fieldnormaldark"><b>Ja</b></td>
					<td class="fieldnormaldark"><b>Bo</b></td>
					<td class="fieldnormaldark"><b>Fr</b></td>
					<td class="fieldnormaldark"><b>Ze</b></td>
					<td class="fieldnormaldark"><b>Kr</b></td>
					<td class="fieldnormaldark"><b>Sc</b></td>
					<td class="fieldnormaldark"><b>Tr</b></td>
					<td class="fieldnormaldark"><b>Cl</b></td>
					<td class="fieldnormaldark"><b>Ca</b></td>
				</tr>
<?php
	$sql_e = "SELECT t, gen, erfasser_svs, sfj, sfb, sff, sfz, sfkr, sfsa, sft, sfka, sfsu FROM gn4scans_history WHERE rg = '".mysql_real_escape_string($xgala)."' AND rp = '".mysql_real_escape_string($xplanet)."' AND type = 1 ORDER BY t DESC LIMIT " . $limit;

	$res_e = tic_mysql_query($sql_e) or die(tic_mysql_error(__FILE__,__LINE__));
	$num_e = mysql_num_rows($res_e);
	
	$color = true;
	for($i = 0; $i < $num_g; $i++) {
		$row = mysql_fetch_row($res_e);
		$color = !$color;
		
		echo '<tr class="fieldnormal'.($color ? 'dark' : 'light').'">';
		echo '	<td>'.date('Y-m-d H:i:s', $row[0]).'</td>';
		echo '	<td>'.$row[1].'%</td>';
		echo '	<td>'.ZahlZuText($row[2]).'</td>';
		echo '	<td>'.ZahlZuText($row[3]).'</td>';
		echo '	<td>'.ZahlZuText($row[4]).'</td>';
		echo '	<td>'.ZahlZuText($row[5]).'</td>';
		echo '	<td>'.ZahlZuText($row[6]).'</td>';
		echo '	<td>'.ZahlZuText($row[7]).'</td>';
		echo '	<td>'.ZahlZuText($row[8]).'</td>';
		echo '	<td>'.ZahlZuText($row[9]).'</td>';
		echo '	<td>'.ZahlZuText($row[10]).'</td>';
		echo '	<td>'.ZahlZuText($row[11]).'</td>';
		echo '</tr>';
	}
	mysql_free_result($res_e);
?>
			</table><br/>
			<a name="mili"></a>
			<table width="100%">
				<tr>
					<td colspan="15" class="datatablehead">Milit&auml;rscans</td>
				</tr>
				<tr>
					<td class="fieldnormaldark"><b>Datum</b></td>
					<td class="fieldnormaldark"><b>Genuaigkeit</b></td>
					<td class="fieldnormaldark"><b>SVS</b></td>
					<td class="fieldnormaldark"><b>Flotte</b></td>
					<td class="fieldnormaldark"><b>Ja</b></td>
					<td class="fieldnormaldark"><b>Bo</b></td>
					<td class="fieldnormaldark"><b>Fr</b></td>
					<td class="fieldnormaldark"><b>Ze</b></td>
					<td class="fieldnormaldark"><b>Kr</b></td>
					<td class="fieldnormaldark"><b>Sc</b></td>
					<td class="fieldnormaldark"><b>Tr</b></td>
					<td class="fieldnormaldark"><b>Cl</b></td>
					<td class="fieldnormaldark"><b>Ca</b></td>
				</tr>
<?php
	$sql_m = "SELECT t, gen, erfasser_svs, sf0j, sf0b, sf0f, sf0z, sf0kr, sf0sa, sf0t, sf0ka, sf0su, sf1j, sf1b, sf1f, sf1z, sf1kr, sf1sa, sf1t, sf1ka, sf1su, sf2j, sf2b, sf2f, sf2z, sf2kr, sf2sa, sf2t, sf2ka, sf2su FROM gn4scans_history WHERE rg = '".mysql_real_escape_string($xgala)."' AND rp = '".mysql_real_escape_string($xplanet)."' AND type = 2 ORDER BY t DESC LIMIT " . $limit;

	$res_m = tic_mysql_query($sql_m) or die(tic_mysql_error(__FILE__,__LINE__));
	$num_m = mysql_num_rows($res_m);
	
	$color = true;
	for($i = 0; $i < $num_m; $i++) {
		$row = mysql_fetch_row($res_m);
		$color = !$color;
		
		echo '<tr class="fieldnormal'.($color ? 'dark' : 'light').'">';
		echo '	<td rowspan="3">'.date('Y-m-d H:i:s', $row[0]).'</td>';
		echo '	<td rowspan="3">'.$row[1].'%</td>';
		echo '	<td rowspan="3">'.ZahlZuText($row[2]).'</td>';
		echo '	<td>0</td>';
		echo '	<td>'.ZahlZuText($row[3]).'</td>';
		echo '	<td>'.ZahlZuText($row[4]).'</td>';
		echo '	<td>'.ZahlZuText($row[5]).'</td>';
		echo '	<td>'.ZahlZuText($row[6]).'</td>';
		echo '	<td>'.ZahlZuText($row[7]).'</td>';
		echo '	<td>'.ZahlZuText($row[8]).'</td>';
		echo '	<td>'.ZahlZuText($row[9]).'</td>';
		echo '	<td>'.ZahlZuText($row[10]).'</td>';
		echo '	<td>'.ZahlZuText($row[11]).'</td>';
		echo '</tr>';
		$color = !$color;
		echo '<tr class="fieldnormal'.($color ? 'dark' : 'light').'">';
		echo '	<td>1</td>';
		echo '	<td>'.ZahlZuText($row[12]).'</td>';
		echo '	<td>'.ZahlZuText($row[13]).'</td>';
		echo '	<td>'.ZahlZuText($row[14]).'</td>';
		echo '	<td>'.ZahlZuText($row[15]).'</td>';
		echo '	<td>'.ZahlZuText($row[16]).'</td>';
		echo '	<td>'.ZahlZuText($row[17]).'</td>';
		echo '	<td>'.ZahlZuText($row[18]).'</td>';
		echo '	<td>'.ZahlZuText($row[19]).'</td>';
		echo '	<td>'.ZahlZuText($row[20]).'</td>';
		echo '</tr>';
		$color = !$color;
		echo '<tr class="fieldnormal'.($color ? 'dark' : 'light').'">';
		echo '	<td>2</td>';
		echo '	<td>'.ZahlZuText($row[21]).'</td>';
		echo '	<td>'.ZahlZuText($row[22]).'</td>';
		echo '	<td>'.ZahlZuText($row[23]).'</td>';
		echo '	<td>'.ZahlZuText($row[24]).'</td>';
		echo '	<td>'.ZahlZuText($row[25]).'</td>';
		echo '	<td>'.ZahlZuText($row[26]).'</td>';
		echo '	<td>'.ZahlZuText($row[27]).'</td>';
		echo '	<td>'.ZahlZuText($row[28]).'</td>';
		echo '	<td>'.ZahlZuText($row[29]).'</td>';
		echo '</tr>';
	}
	mysql_free_result($res_m);
?>
			</table><br/>

			<a name="news"></a>
			<table width="100%">
				<tr>
					<td colspan="15" class="datatablehead">Nachrichten</td>
				</tr>
				<tr>
					<td class="fieldnormaldark"><b>Datum</b></td>
					<td class="fieldnormaldark"><b>Genuaigkeit</b></td>
					<td class="fieldnormaldark"><b>SVS</b></td>
					<td class="fieldnormaldark"><b>Details</b></td>
				</tr>
<?php
	$sql_n = "select t, genauigkeit, erfasser_svs, id  from gn4scans_news where ziel_g = '" . mysql_real_escape_string($xgala) . "' and ziel_p = '" . mysql_real_escape_string($xplanet) . "' ORDER BY t DESC LIMIT " . $limit;

	$res_n = tic_mysql_query($sql_n) or die(tic_mysql_error(__FILE__,__LINE__));
	$num_n = mysql_num_rows($res_n);
	
	$color = true;
	for($i = 0; $i < $num_n; $i++) {
		$row = mysql_fetch_row($res_n);
		$color = !$color;
		
		echo '<tr class="fieldnormal'.($color ? 'dark' : 'light').'">';
		echo '	<td>'.date('Y-m-d H:i:s', $row[0]).'</td>';
		echo '	<td>'.$row[1].'%</td>';
		echo '	<td>'.ZahlZuText($row[2]).'</td>';
		echo '	<td><a href="https://gntic.de/tic/main.php?modul=showgalascans&xgala='.mysql_real_escape_string($xgala).'&xplanet='.mysql_real_escape_string($xplanet).'&displaytype=news&newsid='.ZahlZuText($row[3]).'">x</a></td>';
		echo '</tr>';
	}
	mysql_free_result($res_n);
?>
			</table><br/>
<?php
}
?>