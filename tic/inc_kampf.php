<?

/*
########################################
#+------------------------------------+#
#| Editiert von Hugch (Pascal Gollor) |#
#|     und daishan (Andreas Hemel)    |#
#|      E-Mail: pascal@gollor1.de     |#
#|        IRC-Quakenet: #Hugch        |#
#+------------------------------------+#
########################################
*/

//error_reporting(E_ALL);

include("GNSimuclass.php");
$ticks = postOrGet('ticks') ? postOrGet('ticks') : 5;

//aprint($_POST);

$num_flotten = postOrGet('num_flotten') ? postOrGet('num_flotten') : 1;
$d = postOrGet('d');
$aufenthalt = postOrGet('aufenthalt');
$ankunft = postOrGet('ankunft');
$typ = postOrGet('typ');
$f = postOrGet('f');

/*
aprint($aufenthalt, 'aufenthalt');
aprint($ankunft, 'ankunft');
aprint($typ, 'typ');
aprint($f, 'f');
*/

$p = postOrGet('p');
$g = postOrGet('g');
//aprint($g);
//aprint($p);


if((!$p[1] || !$g[1]) && !postOrGet('referenz') && !postOrGet('compute')) {
	$g[1] = $Benutzer['galaxie'];
	$p[1] = $Benutzer['planet'];
}

$usedscans = array();
if(postOrGet('referenz')) {
	//process references
	$koords_seen = array();
	for($i = 0; $i < min(count($p), count($g)); $i++) {
		if(!$g[$i] || !$p[$i]) {
			continue;
		}

		$mysql_senden[0] = 'SELECT id, gen, unix_timestamp(STR_TO_DATE(zeit,  \'%H:%i %d.%m.%Y\' )) as t, me, ke FROM gn4scans WHERE rg="'.$g[$i].'" AND rp="'.$p[$i].'" AND type="0" LIMIT 1;';
		$mysql_senden[1] = 'SELECT id, gen, unix_timestamp(STR_TO_DATE(zeit,  \'%H:%i %d.%m.%Y\' )) as t, sfj, sfb, sff, sfz, sfkr, sfsa, sft, sfka, sfsu FROM gn4scans WHERE rg="'.$g[$i].'" AND rp="'.$p[$i].'" AND type="1" LIMIT 1;';
		$mysql_senden[2] = 'SELECT id, gen, unix_timestamp(STR_TO_DATE(zeit,  \'%H:%i %d.%m.%Y\' )) as t, glo, glr, gmr, gsr, ga FROM gn4scans WHERE rg="'.$g[$i].'" AND rp="'.$p[$i].'" AND type="3" LIMIT 1;';
		$mysql_senden[3] = 'SELECT id, gen, unix_timestamp(STR_TO_DATE(zeit,  \'%H:%i %d.%m.%Y\' )) as t, sf0j, sf0b, sf0f, sf0z, sf0kr, sf0sa, sf0t, sf0ka, sf0su, sf1j, sf1b, sf1f, sf1z, sf1kr, sf1sa, sf1t, sf1ka, sf1su, sf2j, sf2b, sf2f, sf2z, sf2kr, sf2sa, sf2t, sf2ka, sf2su FROM gn4scans WHERE rg="'.$g[$i].'" AND rp="'.$p[$i].'" AND type="2" LIMIT 1;';
		$mysql_senden[4] = "SELECT name FROM gn4gnuser WHERE gala = '".$g[$i]."' AND planet = '".$p[$i]."' LIMIT 1";
		$res = mysql_multi_query($mysql_senden, 1);
//aprint($res);

		if(!isset($res[4]['name'])) {
			$res[4]['name'] = '<i>unknown</i>';
		}

		//meta for deffer
		$koord = $g[$i].':'.$p[$i];
		if(($i == 0 || $typ[$i] == 'a') && !in_array($koord, $koords_seen)) {
			$koords_seen[] = $koord;
			if ($res[0]['id'] != '') {
				$d[$i][14] = $res[0]['me'];
				$d[$i][15] = $res[0]['ke'];
				$usedscans[] = array(
					'g' => $g[$i],
					'p' => $p[$i],
					'typ' => 'Sektor',
					'missing' => false,
					'gen' => $res[0]['gen'],
					't' => $res[0]['t'],
					'name' => $res[4]['name']
				);
			} else {
				$usedscans[] = array(
					'g' => $g[$i],
					'p' => $p[$i],
					'typ' => 'Sektor',
					'missing' => true,
					'name' => $res[4]['name']
				);
			}
		} else {
			$d[$i][14] = 0;
			$d[$i][15] = 0;
		}

		//deff only for deffer.
		if($i == 0) {
			if ($res[2]['id'] != '') {
				$d[$i][9] = $res[2]['glo'];
				$d[$i][10] = $res[2]['glr'];
				$d[$i][11] = $res[2]['gmr'];
				$d[$i][12] = $res[2]['gsr'];
				$d[$i][13] = $res[2]['ga'];
				$usedscans[] = array(
					'g' => $g[$i],
					'p' => $p[$i],
					'typ' => 'Gesch&uuml;tze',
					'missing' => false,
					'gen' => $res[2]['gen'],
					't' => $res[2]['t'],
					'name' => $res[4]['name']
				);
			} else {
				$usedscans[] = array(
					'g' => $g[$i],
					'p' => $p[$i],
					'typ' => 'Gesch&uuml;tze',
					'missing' => true,
					'name' => $res[4]['name']
				);
			}
		}

		//use all
		if($f[$i] == 0) {
			if ($res[1]['id'] != '') {
				$d[$i][0] = $res[1]['sfj'];
				$d[$i][1] = $res[1]['sfb'];
				$d[$i][2] = $res[1]['sff'];
				$d[$i][3] = $res[1]['sfz'];
				$d[$i][4] = $res[1]['sfkr'];
				$d[$i][5] = $res[1]['sfsa'];
				$d[$i][6] = $res[1]['sft'];
				$d[$i][7] = $res[1]['sfka'];
				$d[$i][8] = $res[1]['sfsu'];
				$usedscans[] = array(
					'g' => $g[$i],
					'p' => $p[$i],
					'typ' => 'Einheiten',
					'missing' => false,
					'gen' => $res[1]['gen'],
					't' => $res[1]['t'],
					'name' => $res[4]['name']
				);
			} else {
				$usedscans[] = array(
					'g' => $g[$i],
					'p' => $p[$i],
					'typ' => 'Einheiten',
					'missing' => true,
					'name' => $res[4]['name']
				);
			}
		}

		//use mili if any fleet selected or sum if newer
		if($f[$i] != 0) {
			if ($res[3]['id'] != '') {
				$zusatz = '';
				if($f[$i] == 1) {
					$zusatz = '1';
				} else if($f[$i] == 2) {
					$zusatz = '2';
				} else if($f[$i] == 3) {
					$zusatz = '0';
				}
				$d[$i][0] = $res[3]['sf'.$zusatz.'j'];
				$d[$i][1] = $res[3]['sf'.$zusatz.'b'];
				$d[$i][2] = $res[3]['sf'.$zusatz.'f'];
				$d[$i][3] = $res[3]['sf'.$zusatz.'z'];
				$d[$i][4] = $res[3]['sf'.$zusatz.'kr'];
				$d[$i][5] = $res[3]['sf'.$zusatz.'sa'];
				$d[$i][6] = $res[3]['sf'.$zusatz.'t'];
				$d[$i][7] = $res[3]['sf'.$zusatz.'ka'];
				$d[$i][8] = $res[3]['sf'.$zusatz.'su'];
				$usedscans[] = array(
					'g' => $g[$i],
					'p' => $p[$i],
					'typ' => 'Milit&auml;r',
					'missing' => false,
					'gen' => $res[1]['gen'],
					't' => $res[1]['t'],
					'name' => $res[4]['name']
				);
			} else {
				$usedscans[] = array(
					'g' => $g[$i],
					'p' => $p[$i],
					'typ' => 'Milit&auml;r',
					'missing' => true,
					'name' => $res[4]['name']
				);
			}
		}
	}

}
function createFleet($dataRow, $aufenthalt, $ankunft, $isAtt, $txt, $g, $p, $fno) {
	$fleet = new Fleet();

	$fleet->g = $g;
	$fleet->p = $p;
	$fleet->fleet = $fno;

	$fleet->Ships = array();
	for($i = 0; $i < 14; $i++) {
		if(isset($dataRow[$i]) && is_numeric($dataRow[$i])) {
			$fleet->Ships[] = floor($dataRow[$i]);
		} else {
			$fleet->Ships[] = 0;
		}
	}

	$fleet->TicksToWait = (is_numeric($ankunft) && $ankunft > 0) ? $ankunft-1 : 0;
	$fleet->TicksToStay = (is_numeric($aufenthalt) && $aufenthalt > 0) ? $aufenthalt : ($isAtt ? 5 : 99-$fleet->TicksToWait);

	$fleet->text = $txt;

	$fleet->atter_exenM = $dataRow[14] ? $dataRow[14] : 0;
	$fleet->atter_exenK = $dataRow[15] ? $dataRow[15] : 0;

	//aprint($fleet);
	return $fleet;
}

if(postOrGet('compute')) {
	$gnsimu_m = new GNSimu_Multi();

	for($i = 0; $i < max(count($d), count($g), count($p), count($typ)); $i++) {
		$isAtt = $typ[$i] === "a" ? true : false;

		if($g[$i] && $p[$i]) {
			$txt = $g[$i].':'.$p[$i].' ';
			if($f[$i] == 0)
				$txt .= 'Alles';
			else if($f[$i] == 1 || $f[$i] == 2)
				$txt .= '#' . $f[$i];
			else
				$txt .= 'Orbit';
		} else
			$txt = 'Flotte #' . $i;

		$fleet[$i] = createFleet($d[$i], $aufenthalt[$i], $ankunft[$i], $isAtt, $txt, $g[$i], $p[$i], $typ[$i]);

		if($isAtt) {
			$gnsimu_m->AddAttFleet($fleet[$i]);
		} else {
			$gnsimu_m->AddDeffFleet($fleet[$i]);
		}
	}

	$gnsimu_m->Exen_M = $d[0][14];
	$gnsimu_m->Exen_K = $d[0][15];
}


echo '<a name="oben"></a><center>';
echo '<h2>GN-Kampfsimulator v1.3</h2>';
echo '<form action="./main.php?modul=kampf" method="post">';
echo '<input type="hidden" name="modul" value="kampf"/>';
echo 'Anzahl Flotten: <input tabindex="1" type="text" size="4" maxlength="3" name="num_flotten" value="'.$num_flotten.'" /> <input tabindex="2" type="submit" value="W&auml;hlen" /><br />';
if(postOrGet('compute')) {
	echo '<div style="text-align: right"><a href="#overview">&raquo; zum Ergebnis</a></div><br/>';
}
echo '<br />';
echo '</center>';


if(count($usedscans) > 0) {
	$age_thresh = 12*60;
	echo '<table class="datatable" align="center">';
	echo '<tr class="datatablehead"><td colspan="7">Genutzte Scans</td></tr>';
	echo '<tr class="fieldnormaldark" style="font-weight: bold;"><td>&nbsp;Galaxie&nbsp;</td><td>&nbsp;Planet&nbsp;</td><td>&nbsp;Spieler&nbsp;</td><td>&nbsp;Typ&nbsp;</td><td>&nbsp;Genauigkeit&nbsp;</td><td>&nbsp;Datum&nbsp;</td><td>&nbsp;Alter [min]&nbsp;</td></tr>';
	$color = false;
	for($i = 0; $i < count($usedscans); $i++) {
		$date = date('Y.m.d H:i', $usedscans[$i]['t']);
		$age = round((time() - $usedscans[$i]['t']) / 60, 0);
		echo '<tr class="fieldnormal' . ($color ? 'dark' : 'light') . '"'.(($usedscans[$i]['missing'] || $age > $age_thresh) ? ' style="color: red;"' : '').'>';
		if($usedscans[$i]['missing']) {
			echo '<td>'.$usedscans[$i]['g'].'</td><td>'.$usedscans[$i]['p'].'</td><td>'.$usedscans[$i]['name'].'</td><td>'.$usedscans[$i]['typ'].'</td><td>-</td><td>-</td><td>-</td>';
		} else {
			echo '<td>'.$usedscans[$i]['g'].'</td><td>'.$usedscans[$i]['p'].'</td><td>'.$usedscans[$i]['name'].'</td><td>'.$usedscans[$i]['typ'].'</td><td>'.$usedscans[$i]['gen'].'%</td><td>'.$date.'</td><td>'.ZahlZuText($age).'</td>';
		}
		echo '</tr>';
		$color = !$color;
	}
	echo '</table><br/>';
}

?>
<table align="center" class="datatable">
<tr class="datatablehead">
	<td>Schiffstyp</td>
	<td>Orbit</td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td>Flotte ' . $i . '</td>';
	}
?>
</tr>
<tr class="fieldnormallight">
	<td><i>Referenz:</i></td>
<?php
	for($i = 0; $i <= $num_flotten; $i++) {
		echo '<td>';
		echo '<input tabindex="'.(10*($i+1)+1).'" type="text" size="2" maxlength="6" name="g['.$i.']" value="'.$g[$i].'" />:<input tabindex="'.(10*($i+1)+2).'"type="text" size="2" maxlength="3" name="p['.$i.']" value="'.$p[$i].'" />';
		echo '<br/>Flotte: <select tabindex="'.(10*($i+1)+3).'" name="f['.$i.']">';
		echo '<option value="0"'.($f[$i] == 0 ? ' selected="selected"' : '').'>Alles</option>';
		echo '<option value="1"'.($f[$i] == 1 ? ' selected="selected"' : '').'>#1</option>';
		echo '<option value="2"'.($f[$i] == 2 ? ' selected="selected"' : '').'>#2</option>';
		echo '<option value="3"'.($f[$i] == 3 ? ' selected="selected"' : '').'>Orbit</option>';
		echo '</select>';
		echo '</td>';
	}
?>
<td><input tabindex="400" type="submit" name="referenz" value="eintragen"/></td>
</tr>
<tr class="fieldnormaldark">
	<td><i>Typ:</i></td>
	<td>Verteidigung<input type="hidden" name="typ[0]" value="d"/></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '	<td>
		<select tabindex="'.(500+$i).'" name="typ['.$i.']">
			<option value="a"'.($typ[$i] == 'a' ? ' selected="selected"' : '').'>Angriff</option>
			<option value="d"'.($typ[$i] == 'd' ? ' selected="selected"' : '').'>Verteidigung</option>
		</select>
		</td>';
	}
?>
</tr>
<tr class="fieldnormallight">
	<td><i>Ankunftstick:</i></td>
	<td>&infin;<input type="hidden" name="ankunft[0]" value="-2"/></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '	<td>
		<select tabindex="'.(600+$i).'" name="ankunft['.$i.']">';
		for($j = 1; $j <= 50; $j++) {
			echo '<option value="'.$j.'"'.((isset($ankunft[$i]) && $ankunft[$i] == $j) ? ' selected="selected"' : '').'>'.$j.'</option>';
		}
		echo '</select>
	</td>';
	}
?>
</tr>
<tr class="fieldnormaldark">
	<td><i>Aufenthaltsdauer:</i></td>
	<td>&infin;<input type="hidden" name="aufenthalt[0]" value="99"/></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '	<td><select tabindex="'.(700+$i).'" name="aufenthalt['.$i.']">';

		for($j = 1; $j <= 20; $j++) {
			if(!isset($aufenthalt[$i]) || $aufenthalt[$i] == 0) {
				$aufenthalt[$i] = (!$typ[$j] && $j > 0 || $typ[$j] == 'a') ? 5 : 20;
			}
			echo '<option value="'.$j.'"'.(($aufenthalt[$i] == $j) ? ' selected="selected"' : '').'>'.$j.'</option>';
		}

		echo '</select></td>';
	}
?>
</tr>
<tr class="fieldnormallight">
	<td>J&auml;ger:</td>
	<td><input size="6" maxlength="6" tabindex="1000" type="text" name="d[0][0]" value="<?=$d[0][0]; ?>" /></td>

<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input size="6" maxlength="6" tabindex="'.(1000*($i+1)+0).'" type="text" name="d['.$i.'][0]" value="'.$d[$i][0].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormaldark">
	<td>Bomber:</td>
	<td><input size="6" maxlength="6" tabindex="1010" type="text" name="d[0][1]" value="<?=$d[0][1]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input size="6" maxlength="6" tabindex="'.(1000*($i+1)+1).'" type="text" name="d['.$i.'][1]" value="'.$d[$i][1].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormallight">
	<td>Fregatte:</td>
	<td><input size="6" maxlength="6" tabindex="1020" type="text" name="d[0][2]" value="<?=$d[0][2]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input size="6" maxlength="6" tabindex="'.(1000*($i+1)+2).'" type="text" name="d['.$i.'][2]" value="'.$d[$i][2].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormaldark">
	<td>Zerst&ouml;rer:</td>
	<td><input size="6" maxlength="6" tabindex="1030" type="text" name="d[0][3]" value="<?=$d[0][3]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input size="6" maxlength="6" tabindex="'.(1000*($i+1)+3).'" type="text" name="d['.$i.'][3]" value="'.$d[$i][3].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormallight">
	<td>Kreuzer:</td>
	<td><input size="6" maxlength="6" tabindex="1040" type="text" name="d[0][4]" value="<?=$d[0][4]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input size="6" maxlength="6" tabindex="'.(1000*($i+1)+4).'" type="text" name="d['.$i.'][4]" value="'.$d[$i][4].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormaldark">
	<td>Schlachtschiff:</td>
	<td><input size="6" maxlength="6" tabindex="1050" type="text" name="d[0][5]" value="<?=$d[0][5]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input size="6" maxlength="6" tabindex="'.(1000*($i+1)+5).'" type="text" name="d['.$i.'][5]" value="'.$d[$i][5].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormallight">
	<td>Tr&auml;gerschiff:</td>
	<td><input size="6" maxlength="6" tabindex="1060" type="text" name="d[0][6]" value="<?=$d[0][6]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input size="6" maxlength="6" tabindex="'.(1000*($i+1)+6).'" type="text" name="d['.$i.'][6]" value="'.$d[$i][6].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormaldark">
	<td>Kaperschiff:</td>
	<td><input size="6" maxlength="6" tabindex="1070" type="text" name="d[0][7]" value="<?=$d[0][7]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input size="6" maxlength="6" tabindex="'.(1000*($i+1)+7).'" type="text" name="d['.$i.'][7]" value="'.$d[$i][7].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormallight">
	<td>Schutzschiff:</td>
	<td><input size="6" maxlength="6" tabindex="1080" type="text" name="d[0][8]" value="<?=$d[0][8]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input size="6" maxlength="6" tabindex="'.(1000*($i+1)+8).'" type="text" name="d['.$i.'][8]" value="'.$d[$i][8].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormaldark">
	<td>Leichtes Orbitalgesch&uuml;tz:</td>
	<td><input size="6" maxlength="6" tabindex="1090" type="text" name="d[0][9]" value="<?=$d[0][9]; ?>" /></td>
</tr>
<tr class="fieldnormallight">
	<td>Leichtes Raumgesch&uuml;tz:</td>
	<td><input size="6" maxlength="6" tabindex="1100" type="text" name="d[0][10]" value="<?=$d[0][10]; ?>" /></td>
</tr>
<tr class="fieldnormaldark">
	<td>Mittlers Raumgesch&uuml;tz:</td>
	<td><input size="6" maxlength="6" tabindex="1110" type="text" name="d[0][11]" value="<?=$d[0][11]; ?>" /></td>
</tr>
<tr class="fieldnormallight">
	<td>Schweres Raumgesch&uuml;tz:</td>
	<td><input size="6" maxlength="6" tabindex="1120" type="text" name="d[0][12]" value="<?=$d[0][12]; ?>" /></td>
</tr>
<tr class="fieldnormaldark">
	<td>Abfangj&auml;ger:</td>
	<td><input size="6" maxlength="6" tabindex="1130" type="text" name="d[0][13]" value="<?=$d[0][13]; ?>" /></td>
</tr>
<tr class="fieldnormallight">
	<td>Metalextraktoren:</td>
	<td><input size="6" maxlength="6" tabindex="1200" type="text" name="d[0][14]" value="<?=$d[0][14]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input size="6" maxlength="6" id="d'.$i.'_14" tabindex="'.(1000*($i+1)+14).'" type="text" name="d['.$i.'][14]" value="'.($d[$i][14] > 0 ? $d[$i][14] : '').'" /></td>';
	}
?>
</tr>
<tr class="fieldnormaldark">
	<td>Kristalextraktoren:</td>
	<td><input size="6" maxlength="6" tabindex="1300" type="text" name="d[0][15]" value="<?=$d[0][15]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input size="6" maxlength="6" id="d'.$i.'_15" tabindex="'.(1000*($i+1)+15).'" type="text" name="d['.$i.'][15]" value="'.($d[$i][15] > 0 ? $d[$i][15] : '').'" /></td>';
	}
?>
</tr>
<tr>
	<td colspan="<?=($num_flotten+2);?>">Ticks: <select name="ticks" tabindex="10000">';
<?php
for($i=1;$i<=99;$i++) {
	if($i==$ticks)
		echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
	else
		echo '<option value="'.$i.'">'.$i.'</option>';
}

echo '</select><input tabindex="10100" type="checkbox" name="preticks"';
if(postOrGet('preticks') || !postOrGet('ticks')) {
	echo ' checked="checked"';
}
echo ' />Feuerkraft der Gesch&uuml;tze vor Ankunft der Flotte berechnen</td></tr>';
echo '<tr><td colspan="'.($num_flotten+2).'" align="center"><input tabindex="15000" type="submit" name="compute" value="Berechnen" /></td></tr></table></form>';
if($ticks<1)
	$ticks=1;


if(postOrGet('compute')) {
	//sort gnsimu-
	$gnsimu_m->sortFleets();

	if(postOrGet('preticks')) {
		for($i = 0; $i < 2; $i++) {
			//aprint('', 'before pre gunticks');
			$gnsimu_m->prefire(2);
			$gnsimu_m->PrintStatesGun(2);
			//aprint('', 'pre gunticks 2 done');
			$gnsimu_m->prefire(1);
			$gnsimu_m->PrintStatesGun(1);
			//aprint('', 'pre gunticks 1 done');
			$gnsimu_m->currentTick++;
		}
	} else {
		$gnsimu_m->currentTick++;
		$gnsimu_m->currentTick++;
	}

	/*
	aprint(array(
		'Deff' => $gnsimu_m->DeffFleets,
		'Att' => $gnsimu_m->AttFleets,
	), 'start');
	*/
	for($i=0;$i<$ticks;$i++) {
		$gnsimu_m->Tick(false);
		/*aprint(array(
			'Deff' => $gnsimu_m->DeffFleets,
			'Att' => $gnsimu_m->AttFleets,
		), 'after tick ' . ($i+1));*/
		$gnsimu_m->PrintStates();
		//aprint($gnsimu_m);
		if(postOrGet('preticks')) {
			$gnsimu_m->prefire(2);
			$gnsimu_m->PrintStatesGun(2);
			//aprint('', 'tick '.$i.': gunticks 2 done');
			$gnsimu_m->prefire(1);
			$gnsimu_m->PrintStatesGun(1);
			//aprint('', 'tick '.$i.': gunticks 1 done');
		}
		$gnsimu_m->currentTick++;
	}

    $gnsimu_m->PrintOverView();

	echo '<div style="text-align: right;"><a href="#oben">&raquo; nach oben</a></div>';

$tmpstr = '';
if(is_array(postOrGet('g')))
	foreach(postOrGet('g') as $k=>$v) {
		$tmpstr .= '&g['.$k.']='.$v;
	}
if(is_array(postOrGet('p')))
	foreach(postOrGet('p') as $k=>$v) {
		$tmpstr .= '&p['.$k.']='.$v;
	}
if(is_array(postOrGet('d')))
	foreach(postOrGet('d') as $k=>$v) {
		foreach($v as $k2 => $v2) {
			$tmpstr .= '&d['.$k.']['.$k2.']='.$v2;
		}
	}
if(is_array(postOrGet('aufenthalt')))
	foreach(postOrGet('aufenthalt') as $k=>$v) {
		$tmpstr .= '&aufenthalt['.$k.']='.$v;
	}
if(is_array(postOrGet('ankunft')))
	foreach(postOrGet('ankunft') as $k=>$v) {
		$tmpstr .= '&ankunft['.$k.']='.$v;
	}
if(is_array(postOrGet('typ')))
	foreach(postOrGet('typ') as $k=>$v) {
		$tmpstr .= '&typ['.$k.']='.$v;
	}
if(is_array(postOrGet('f')))
	foreach(postOrGet('f') as $k=>$v) {
		$tmpstr .= '&f['.$k.']='.$v;
	}

$short = addShortUrl('main.php?modul=kampf&preticks='.postOrGet('preticks').'&ticks='.postOrGet('ticks').'&num_flotten='.postOrGet('num_flotten').'&compute=doIt'.$tmpstr);
echo '<div style="text-align: right;">'.createCopyLink('&raquo; Link zu dieser Simulation kopieren', $short).' | <a href="' . $short . '">&raquo; Link zu dieser Simulation</a></div>';
}

?>
