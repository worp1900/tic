<!-- START: inc_allifleets -->
<?php
	//für farben.
	$hours = 36;

	$allianz = isset($_GET['allianz'])?$_GET['allianz']:$Benutzer['allianz'];
?>
<center>
	<h2>Allianz-Flotten&uuml;bersicht</h2>
<?php
	if (isset($_GET['metanr'])) $_SESSION['metanr'] = $_GET['metanr'];
	else if (!isset($_SESSION['metanr'])) $_SESSION['metanr'] = $Benutzer['ticid'];
	$SQL_Query = "SELECT * FROM gn4vars WHERE name='ticeb' ORDER BY value;";
	$SQL_Result_metas = tic_mysql_query($SQL_Query, $SQL_DBConn) or $error_code = 4;
	for ($m=0; $m<mysql_num_rows($SQL_Result_metas); $m++) {
		$MetaNummer = mysql_result($SQL_Result_metas, $m, 'ticid');
		$MetaName = mysql_result($SQL_Result_metas, $m, 'value');
		if ($MetaNummer == $_SESSION['metanr'])
			echo "<h2>".$MetaName."</h2>\n";
		else
			echo "<h2><a href=\"./main.php?modul=allifleets&metanr=".$MetaNummer."\">".$MetaName."</a></h2>\n";
	}
	mysql_free_result($SQL_Result_metas);
	foreach ($AllianzName as $AllianzNummer => $AllianzNummerName) {
		if ($AllianzInfo[$AllianzNummer]['meta'] == $_SESSION['metanr']) {
			if ($AllianzInfo[$allianz]['meta'] != $_SESSION['metanr']) $allianz = $AllianzNummer;
			if ($AllianzNummer == $allianz)
				echo "<h3>[ ".$AllianzInfo[$AllianzNummer]['tag']." ] ".$AllianzNummerName."</h3>\n";
			else
				echo "<h3><a href=\"./main.php?modul=allifleets&allianz=".$AllianzNummer."\">[ ".$AllianzTag[$AllianzNummer]." ]</a></h3>\n";
		}
	}
?>
	<table width="100%">
		<tr class="datatablehead">
			<th>&nbsp;Sektor&nbsp;</th>
			<th>&nbsp;Name&nbsp;</th>
			<th colspan="6">&nbsp;Defensiv&nbsp;</th>
			<th colspan="10">&nbsp;Offensiv&nbsp;</th>
			<th colspan="2">&nbsp;Clepdeff&nbsp;</th>
		</tr>
		<tr class="datatablehead">
			<th>&nbsp;</th>
			<th>&nbsp;</th>
			<th title="letzter Import in Minuten">&nbsp;Alter&nbsp;</th>
			<th title="Leichtes Orbitalgesch&uuml;tz">&nbsp;LO&nbsp;</th>
			<th title="Leichtes Raumgesch&uuml;tz">&nbsp;LR&nbsp;</th>
			<th title="Mittleres Raumgesch&uuml;tz">&nbsp;MR&nbsp;</th>
			<th title="Schweres Raumgesch&uuml;tz">&nbsp;SR&nbsp;</th>
			<th title="Abfangj&auml;ger">&nbsp;AJ&nbsp;</th>

			<th title="letzter Import in Minuten">&nbsp;Alter&nbsp;</th>
			<th title="J&auml;ger">&nbsp;J&auml;&nbsp;</th>
			<th title="Bomber">&nbsp;Bo&nbsp;</th>
			<th title="Fregatten">&nbsp;Fr&nbsp;</th>
			<th title="Zerst&ouml;rer">&nbsp;Ze&nbsp;</th>
			<th title="Kreuzer">&nbsp;Kr&nbsp;</th>
			<th title="Schlachtschiffe">&nbsp;SS&nbsp;</th>
			<th title="Tr&auml;ger">&nbsp;Tr&nbsp;</th>
			<th title="Kaperschiffe">&nbsp;Ka&nbsp;</th>
			<th title="Schildschiffe">&nbsp;Sch&nbsp;</th>
			<th>&nbsp;Schrott&nbsp;</th>
			<th>&nbsp;Gesamt&nbsp;</th>
		</tr>
<?php
	$gja = 0;
	$gbo = 0;
	$gfr = 0;
	$gze = 0;
	$gkr = 0;
	$gsl = 0;
	$gtr = 0;
	$gka = 0;
	$gca = 0;
	$glo = 0;
	$gro = 0;
	$gmr = 0;
	$gsr = 0;
	$gaj = 0;
	$gschrott = 0;
	
	$sql = "	SELECT 
	a.id, a.name, a.galaxie, a.planet, 
	b.sfj, b.sfb, b.sff, b.sfz, b.sfkr, b.sfsa, b.sft, b.sfka, b.sfsu, 
	c.glo, c.glr, c.gmr, c.gsr, c.ga,
	UNIX_TIMESTAMP(STR_TO_DATE(b.zeit, '%H:%i %d.%m.%Y')) t1, UNIX_TIMESTAMP(STR_TO_DATE(c.zeit, '%H:%i %d.%m.%Y')) t2 
FROM `gn4accounts` AS a
LEFT JOIN `gn4scans` AS b 
	ON(a.galaxie = b.rg AND a.planet = b.rp AND b.type = 1) 
LEFT JOIN `gn4scans` AS c 
	ON(a.galaxie = c.rg AND a.planet = c.rp AND c.type = 3) WHERE a.allianz='".$allianz."' order by a.galaxie, a.planet";
	$SQL_Result2 = tic_mysql_query($sql);
	$color = 0;
	if(mysql_num_rows($SQL_Result2) > 0)
	{
		for ( $i=0; $i<mysql_num_rows($SQL_Result2); $i++ ) {
			$color = !$color;
			$ftype = "normal";
			$gala   = mysql_result($SQL_Result2, $i, 'galaxie');
			$planet = mysql_result($SQL_Result2, $i, 'planet');
			$name   = mysql_result($SQL_Result2, $i, 'name');
			
			
			$ja = mysql_result($SQL_Result2, $i, 'sfj' );
			$bo = mysql_result($SQL_Result2, $i, 'sfb' );
			
			$fr     = mysql_result($SQL_Result2, $i, 'sff' );
			$ze     = mysql_result($SQL_Result2, $i, 'sfz' );
			$kr     = mysql_result($SQL_Result2, $i, 'sfkr' );
			$sl     = mysql_result($SQL_Result2, $i, 'sfsa' );
			$tr     = mysql_result($SQL_Result2, $i, 'sft' );
			$ka     = mysql_result($SQL_Result2, $i, 'sfka' );
			$ca     = mysql_result($SQL_Result2, $i, 'sfsu' );
			$lo     = mysql_result($SQL_Result2, $i, 'glo' );
			$ro     = mysql_result($SQL_Result2, $i, 'glr' );
			$mr     = mysql_result($SQL_Result2, $i, 'gmr' );
			$sr     = mysql_result($SQL_Result2, $i, 'gsr' );
			$aj     = mysql_result($SQL_Result2, $i, 'ga' );
			$gja += $ja;
			$gbo += $bo;
			$gfr += $fr;
			$gze += $ze;
			$gkr += $kr;
			$gsl += $sl;
			$gtr += $tr;
			$gka += $ka;
			$gca += $ca;
			$glo += $lo;
			$gro += $ro;
			$gmr += $mr;
			$gsr += $sr;
			$gaj += $aj;
			
			//geschütze
			$t1 = mysql_result($SQL_Result2, $i, 't1' );
			$alter1 = round((time() - $t1) / 60, 0);
			$farbe1 = 255 - round((1 - $alter1 / 60 / $hours) * 255);
			//einheiten
			$t2 = mysql_result($SQL_Result2, $i, 't2' );
			$alter2 = round((time() - $t2) / 60, 0);
			$farbe2 = 255 - round((1 - $alter2 / 60 / $hours) * 255);

			
			echo "		<tr class=\"field".$ftype.($color ? "dark" : "light")."\">\n";
			echo "			<td align=\"center\">&nbsp;".$gala.":".$planet."&nbsp;</td>\n";
			echo "			<td>&nbsp;<a href='main.php?modul=showgalascans&xgala=".$gala."&xplanet=".$planet."&displaytype=0'>&raquo; ".$name."</a>&nbsp;</td>\n";
			echo "			<td align='right' style='color: white; background-color: rgb(".$farbe1.", 0, 0)'>&nbsp;".ZahlZuText($alter2)."&nbsp;</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($lo)."</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($ro)."</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($mr)."</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($sr)."</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($aj)."</td>\n";

			echo "			<td align='right' style='color: white; background-color: rgb(".$farbe2.", 0, 0)'>&nbsp;".ZahlZuText($alter1)."&nbsp;</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($ja)."</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($bo)."</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($fr)."</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($ze)."</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($kr)."</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($sl)."</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($tr)."</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($ka)."</td>\n";
			echo "			<td align=\"right\">".ZahlZuText($ca)."</td>\n";
			
			$schrott = $lo * 1.28 + $aj * 0.32 + $tr * 25;
			$gschrott += $schrott;
			echo '<td align="right">&nbsp;'.ZahlZuText($schrott).'&nbsp;</td>';
			echo '<td align="right">&nbsp;'.ZahlZuText($schrott + $ca).'&nbsp;</td>';
			echo "		</tr>\n";
		}
			
		$color = !$color;
		echo "		<tr class=\"fieldnormal".($color ? "dark" : "light")."\" style=\"font-weight:bold;\">\n";
		echo "			<td align=\"center\">Allianz</td>\n";
		echo "			<td>Gesammt</td>\n";
		echo '<td>&nbsp;</td>';
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($glo)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gro)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gmr)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gsr)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gaj)."&nbsp;</td>\n";

		echo '<td>&nbsp;</td>';
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gja)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gbo)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gfr)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gze)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gkr)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gsl)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gtr)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gka)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gca)."&nbsp;</td>\n";

		echo '<td align="right">&nbsp;'.ZahlZuText($gschrott).'&nbsp;</td>';
		echo '<td align="right">&nbsp;'.ZahlZuText($gschrott + $gca).'&nbsp;</td>';
		echo "		</tr>\n";
		
		$num = mysql_num_rows($SQL_Result2);
		$gschrott = round(($glo * 1.28 + $gaj * 0.32 + $gtr * 25)/$num, 0);
		$gja = round($gja/$num, 0);
		$gbo = round($gbo/$num, 0);
		$gfr = round($gfr/$num, 0);
		$gze = round($gze/$num, 0);
		$gkr = round($gkr/$num, 0);
		$gsl = round($gsl/$num, 0);
		$gtr = round($gtr/$num, 0);
		$gka = round($gka/$num, 0);
		$gca = round($gca/$num, 0);
		$glo = round($glo/$num, 0);
		$gro = round($gro/$num, 0);
		$gmr = round($gmr/$num, 0);
		$gsr = round($gsr/$num, 0);
		$gaj = round($gaj/$num, 0);
		
		$color = !$color;
		echo "		<tr class=\"fieldnormal".($color ? "dark" : "light")."\" style=\"font-weight:bold;\">\n";
		echo "			<td align=\"center\">Allianz</td>\n";
		echo "			<td>Durchschnitt</td>\n";
		echo '<td>&nbsp;</td>';
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($glo)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gro)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gmr)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gsr)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gaj)."&nbsp;</td>\n";

		echo '<td>&nbsp;</td>';
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gja)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gbo)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gfr)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gze)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gkr)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gsl)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gtr)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gka)."&nbsp;</td>\n";
		echo "			<td align=\"right\">&nbsp;".ZahlZuText($gca)."&nbsp;</td>\n";

		echo '<td align="right">&nbsp;'.ZahlZuText($gschrott).'&nbsp;</td>';
		echo '<td align="right">&nbsp;'.ZahlZuText($gschrott + $gca).'&nbsp;</td>';

		echo "		</tr>\n";
	}
	else
	{
		echo "<tr class=\"datatablefoot\" style=\"font-weight:bold;\"><td>&nbsp;Diese Allianz hat keine Mitglieder&nbsp;</td></tr>";
	}

?>
	</table>
</center>
<!-- ENDE: inc_allifleets -->
