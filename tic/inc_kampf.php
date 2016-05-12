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

include("GNSimuclass.php");
$ticks = (isset($_POST['ticks']) ? $_POST['ticks'] : 5);

function aprint($val, $txt = null) {
	echo '<code style="text-align: left; font-size: 8pt;"><pre>';
	if($txt != null) echo '<b>' . $txt . ':</b> ';
	print_r($val);
	echo '</pre></code><br><hr>';
}
//aprint($_POST);

function postOrGet($name) {
	if(isset($_POST[$name]))
		return $_POST[$name];
	if(isset($_GET[$name]))
		return $_GET[$name];
	return null;
}

$num_flotten = postOrGet('num_flotten') ? postOrGet('num_flotten') : 1;
$d = postOrGet('d');
$aufenthalt = postOrGet('aufenthalt');
$ankunft = postOrGet('ankunft');
$typ = postOrGet('typ');
$f = postOrGet('f');
//aprint($d, 'd');
//aprint($aufenthalt, 'aufenthalt');
//aprint($ankunft, 'ankunft');
//aprint($typ, 'typ');

$p = postOrGet('p');
$g = postOrGet('g');
//aprint($g);
//aprint($p);


if(!$p[1] || !$g[1]) {
	$g[1] = $Benutzer['galaxie'];
	$p[1] = $Benutzer['planet'];
}

if(postOrGet('referenz')) {
	//process references
	for($i = 0; $i < min(count($p), count($g)); $i++) {
		if(!$g[$i] || !$p[$i]) {
			continue;
		}

                $mysql_senden[0] = 'SELECT id, me, ke FROM gn4scans WHERE rg="'.$g[$i].'" AND rp="'.$p[$i].'" AND type="0" LIMIT 1;';
                $mysql_senden[1] = 'SELECT id, sfj, sfb, sff, sfz, sfkr, sfsa, sft, sfka, sfsu FROM gn4scans WHERE rg="'.$g[$i].'" AND rp="'.$p[$i].'" AND type="1" LIMIT 1;';
                $mysql_senden[2] = 'SELECT id, glo, glr, gmr, gsr, ga FROM gn4scans WHERE rg="'.$g[$i].'" AND rp="'.$p[$i].'" AND type="3" LIMIT 1;';
                $mysql_senden[3] = 'SELECT id, sf0j, sf0b, sf0f, sf0z, sf0kr, sf0sa, sf0t, sf0ka, sf0su, sf1j, sf1b, sf1f, sf1z, sf1kr, sf1sa, sf1t, sf1ka, sf1su, sf2j, sf2b, sf2f, sf2z, sf2kr, sf2sa, sf2t, sf2ka, sf2su FROM gn4scans WHERE rg="'.$g[$i].'" AND rp="'.$p[$i].'" AND type="2" LIMIT 1;';
                $res = mysql_multi_query($mysql_senden, 1);

				//meta for deffer
                if ($res[0]['id'] != '' && $i == 0) {
                        $d[$i][14] = $res[0]['me'];
                        $d[$i][15] = $res[0]['ke'];
                }

               //deff only for deffer.
                if ($res[2]['id'] != '' && $i == 0) {
                        $d[$i][9] = $res[2]['glo'];
                        $d[$i][10] = $res[2]['glr'];
                        $d[$i][11] = $res[2]['gmr'];
                        $d[$i][12] = $res[2]['gsr'];
                        $d[$i][13] = $res[2]['ga'];
                }


		//use all
                if ($res[1]['id'] != '' && $f[$i] == 0) {
                        $d[$i][0] = $res[1]['sfj'];
                        $d[$i][1] = $res[1]['sfb'];
                        $d[$i][2] = $res[1]['sff'];
                        $d[$i][3] = $res[1]['sfz'];
                        $d[$i][4] = $res[1]['sfkr'];
                        $d[$i][5] = $res[1]['sfsa'];
                        $d[$i][6] = $res[1]['sft'];
                        $d[$i][7] = $res[1]['sfka'];
                        $d[$i][8] = $res[1]['sfsu'];
                }
	
		//deff only for deffer.
                if ($res[2]['id'] != '' && $i == 0) {
                        $d[$i][9] = $res[2]['glo'];
                        $d[$i][10] = $res[2]['glr'];
                        $d[$i][11] = $res[2]['gmr'];
                        $d[$i][12] = $res[2]['gsr'];
                        $d[$i][13] = $res[2]['ga'];
                }

		//use mili if any fleet selected or sum if newer
                if ($res[3]['id'] != '' && $f[$i] != 0) {
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
                }
	}
}
function createFleet($dataRow, $aufenthalt, $ankunft, $isAtt, $txt) {
	$fleet = new Fleet();
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
	//aprint($fleet);
	return $fleet;
}

if(isset($_POST['compute'])) {
	$gnsimu = new GNSimu();
	$gnsimu_m = new GNSimu_Multi();

	for($i = 0; $i < count($d); $i++) {
		$isAtt = $typ[$i] === "a" ? true : false;

		if($g[$i] && $p[$i]) {
			$txt = $g[$i].':'.$p[$i].' ';
			if($typ[$i] == 0)
				$txt .= 'all';
			else if($typ[$i] == 1 || $typ[$i] == 2)
				$txt .= '#' . $typ[$i];
			else
				$txt .= 'Orbit';
		} else
			$txt = 'Flotte #' . $i;

		$fleet[$i] = createFleet($d[$i], $aufenthalt[$i], $ankunft[$i], $isAtt, $txt);

		if($isAtt) {
			$gnsimu_m->AddAttFleet($fleet[$i]);
		} else {
			$gnsimu_m->AddDeffFleet($fleet[$i]);
		}
	}
	
	//$gnsimu->mexen = $d[0][14];
	//$gnsimu->kexen = $d[0][15];
	$gnsimu_m->Exen_M = $d[0][14];
	$gnsimu_m->Exen_K = $d[0][15];
/*	
	aprint($gnsimu_m);
	$gnsimu_m->Tick(false);
	aprint($gnsimu_m);
	$gnsimu_m->Tick(false);
	aprint($gnsimu_m);
*/
}

echo '<center>';
echo '<h2>GN-Kampfsimulator v1.3</h2><p>Bitte beachtet, dass die Gesch&uuml;tz-Vorticks noch nicht implementiert sind./dv</p>';
echo '<form action="./main.php?modul=kampf" method="post">';
echo '<input type="hidden" name="modul" value="kampf"/>';
echo 'Anzahl Flotten: <input tabindex="1" type="text" size="4" maxlength="4" name="num_flotten" value="'.$num_flotten.'" /> <input tabindex="2" type="submit" value="W&auml;hlen" /><br /><br />';
echo '</center>';

?>
<table align="center" class="datatable">
<tr class="datatablehead">
	<td>Schiffstyp</td>
	<td>Verteidigender Orbit</td>
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
		echo '<input tabindex="'.(10*($i+1)+1).'" type="text" size="4" maxlength="4" name="g['.$i.']" value="'.$g[$i].'" />:<input tabindex="'.(10*($i+1)+2).'"type="text" size="2" maxlength="2" name="p['.$i.']" value="'.$p[$i].'" />';
		echo '<select tabindex="'.(10*($i+1)+3).'" name="f['.$i.']">';
		echo '<option value="0"'.($f[$i] == 0 ? ' selected="selected"' : '').'>all</option>';
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
	<td>1<input type="hidden" name="ankunft[0]" value="1"/></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '	<td>
		<select tabindex="'.(600+$i).'" name="ankunft['.$i.']">
			<option value="1"'.((isset($ankunft[$i]) && $ankunft[$i] == 1) ? ' selected="selected"' : '').'>1</option>
			<option value="2"'.((isset($ankunft[$i]) && $ankunft[$i] == 2) ? ' selected="selected"' : '').'>2</option>
			<option value="3"'.((isset($ankunft[$i]) && $ankunft[$i] == 3) ? ' selected="selected"' : '').'>3</option>
			<option value="4"'.((isset($ankunft[$i]) && $ankunft[$i] == 4) ? ' selected="selected"' : '').'>4</option>
			<option value="5"'.((isset($ankunft[$i]) && $ankunft[$i] == 5) ? ' selected="selected"' : '').'>5</option>
			<option value="6"'.((isset($ankunft[$i]) && $ankunft[$i] == 6) ? ' selected="selected"' : '').'>6</option>
			<option value="7"'.((isset($ankunft[$i]) && $ankunft[$i] == 7) ? ' selected="selected"' : '').'>7</option>
			<option value="8"'.((isset($ankunft[$i]) && $ankunft[$i] == 8) ? ' selected="selected"' : '').'>8</option>
			<option value="9"'.((isset($ankunft[$i]) && $ankunft[$i] == 9) ? ' selected="selected"' : '').'>9</option>
			<option value="10"'.((isset($ankunft[$i]) && $ankunft[$i] == 10) ? ' selected="selected"' : '').'>10</option>
		</select>
	</td>';
	}
?>
</tr>
<tr class="fieldnormaldark">
	<td><i>Aufenthaltsdauer:</i></td>
<?php
	for($i = 0; $i <= $num_flotten; $i++) {
		echo '	<td>
		<select tabindex="'.(700+$i).'" name="aufenthalt['.$i.']">
			<option value="1"'.((isset($_POST['compute']) && isset($aufenthalt[$i]) && $aufenthalt[$i] == 1) ? ' selected="selected"' : '').'>1</option>
			<option value="2"'.((isset($_POST['compute']) && isset($aufenthalt[$i]) && $aufenthalt[$i] == 2) ? ' selected="selected"' : '').'>2</option>
			<option value="3"'.((isset($_POST['compute']) && isset($aufenthalt[$i]) && $aufenthalt[$i] == 3) ? ' selected="selected"' : '').'>3</option>
			<option value="4"'.((isset($_POST['compute']) && isset($aufenthalt[$i]) && $aufenthalt[$i] == 4) ? ' selected="selected"' : '').'>4</option>';
			
			if(isset($_POST['compute'])) {
				echo '<option value="5"'.((isset($aufenthalt[$i]) && $aufenthalt[$i] == 5) ? ' selected="selected"' : '').'>5</option>';
				echo '<option value="10"'.((isset($aufenthalt[$i]) && $aufenthalt[$i] == 10) ? ' selected="selected"' : '').'>10</option>';
				echo '<option value="99"'.((isset($aufenthalt[$i]) && $aufenthalt[$i] == 99) ? ' selected="selected"' : '').'>99</option>';
			} else {
				echo '<option value="5"'.(($i != 0) ? ' selected="selected"' : '').'>5</option>';
				echo '<option value="10">10</option>';
				echo '<option value="99"'.(($i == 0) ? ' selected="selected"' : '').'>99</option>';
			}
		echo '</select>
	</td>';
	}
?>
</tr>
<tr class="fieldnormallight">
	<td>J&auml;ger - Leo:</td>
	<td><input tabindex="1000" type="text" name="d[0][0]" value="<?=$d[0][0]; ?>" /></td>

<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input tabindex="'.(1000*($i+1)+0).'" type="text" name="d['.$i.'][0]" value="'.$d[$i][0].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormaldark">
	<td>Bomber - Aquilae:</td>
	<td><input tabindex="1010" type="text" name="d[0][1]" value="<?=$d[0][1]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input tabindex="'.(1000*($i+1)+1).'" type="text" name="d['.$i.'][1]" value="'.$d[$i][1].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormallight">
	<td>Fregatte - Fronax:</td>
	<td><input tabindex="1020" type="text" name="d[0][2]" value="<?=$d[0][2]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input tabindex="'.(1000*($i+1)+2).'" type="text" name="d['.$i.'][2]" value="'.$d[$i][2].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormaldark">
	<td>Zerst&ouml;rer - Draco:</td>
	<td><input tabindex="1030" type="text" name="d[0][3]" value="<?=$d[0][3]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input tabindex="'.(1000*($i+1)+3).'" type="text" name="d['.$i.'][3]" value="'.$d[$i][3].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormallight">
	<td>Kreuzer - Goron:</td>
	<td><input tabindex="1040" type="text" name="d[0][4]" value="<?=$d[0][4]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input tabindex="'.(1000*($i+1)+4).'" type="text" name="d['.$i.'][4]" value="'.$d[$i][4].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormaldark">
	<td>Schlachtschiff - Pentalin:</td>
	<td><input tabindex="1050" type="text" name="d[0][5]" value="<?=$d[0][5]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input tabindex="'.(1000*($i+1)+5).'" type="text" name="d['.$i.'][5]" value="'.$d[$i][5].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormallight">
	<td>Tr&auml;gerschiff - Zenit:</td>
	<td><input tabindex="1060" type="text" name="d[0][6]" value="<?=$d[0][6]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input tabindex="'.(1000*($i+1)+6).'" type="text" name="d['.$i.'][6]" value="'.$d[$i][6].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormaldark">
	<td>Kaperschiff - Cleptor:</td>
	<td><input tabindex="1070" type="text" name="d[0][7]" value="<?=$d[0][7]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input tabindex="'.(1000*($i+1)+7).'" type="text" name="d['.$i.'][7]" value="'.$d[$i][7].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormallight">
	<td>Schutzschiff - Cancri:</td>
	<td><input tabindex="1080" type="text" name="d[0][8]" value="<?=$d[0][8]; ?>" /></td>
<?php
	for($i = 1; $i <= $num_flotten; $i++) {
		echo '<td><input tabindex="'.(1000*($i+1)+8).'" type="text" name="d['.$i.'][8]" value="'.$d[$i][8].'" /></td>';
	}
?>
</tr>
<tr class="fieldnormaldark">
	<td>Leichtes Orbitalgesch&uuml;tz - Rubium:</td>
	<td><input tabindex="1090" type="text" name="d[0][9]" value="<?=$d[0][9]; ?>" /></td>
</tr>
<tr class="fieldnormallight">
	<td>Leichtes Raumgesch&uuml;tz - Pulsar:</td>
	<td><input tabindex="1100" type="text" name="d[0][10]" value="<?=$d[0][10]; ?>" /></td>
</tr>
<tr class="fieldnormaldark">
	<td>Mittlers Raumgesch&uuml;tz - Coon:</td>
	<td><input tabindex="1110" type="text" name="d[0][11]" value="<?=$d[0][11]; ?>" /></td>
</tr>
<tr class="fieldnormallight">
	<td>Schweres Raumgesch&uuml;tz - Centurion:</td>
	<td><input tabindex="1120" type="text" name="d[0][12]" value="<?=$d[0][12]; ?>" /></td>
</tr>
<tr class="fieldnormaldark">
	<td>Abfangj&auml;ger - Horus:</td>
	<td><input tabindex="1130" type="text" name="d[0][13]" value="<?=$d[0][13]; ?>" /></td>
</tr>
<tr class="fieldnormallight">
	<td>Metalextraktoren:</td>
	<td><input tabindex="1200" type="text" name="d[0][14]" value="<?=$d[0][14]; ?>" /></td>
</tr>
<tr class="fieldnormaldark">
	<td>Kristalextraktoren:</td>
	<td><input tabindex="1300" type="text" name="d[0][15]" value="<?=$d[0][15]; ?>" /></td>
</tr>
<tr>
	<td colspan="<?=($num_flotten+2);?>">Ticks: <select name="ticks" tabindex="10000">';
<?php
for($i=1;$i<15;$i++) {
	if($i==$ticks)
		echo '<option value="'.$i.'" selected="selected">'.$i.'</option>';
	else
		echo '<option value="'.$i.'">'.$i.'</option>';
}

echo '</select><input tabindex="10100" type="checkbox" name="preticks"';
if(isset($_POST['preticks']) || !isset($_POST['ticks'])) {
	echo ' checked="checked"';
}
echo ' />Feuerkraft der Gesch&uuml;tze vor Ankunft der Flotte berechnen</td></tr>';
echo '<tr><td colspan="'.($num_flotten+2).'" align="center"><input tabindex="15000" type="submit" name="compute" value="Berechnen" /></td></tr></table></form>';
if($ticks<1)
	$ticks=1;

if(isset($_POST['compute'])) {
	if(isset($_POST['preticks'])) {
		echo '<br/><br/><b>Gesch&uuml;tzfeuer Tick -2:</b>';
		$gnsimu_m->prefire(2);
		$gnsimu_m->PrintStates();
		echo '<br/><br/><b>Gesch&uuml;tzfeuer Tick -1:</b>';
		$gnsimu_m->prefire(1);
		$gnsimu_m->PrintStates();
	}

	//aprint($gnsimu_m, 'start');
	for($i=0;$i<$ticks;$i++) {
        $gnsimu_m->Tick(false);
		//aprint($gnsimu_m, 'after tick ' . ($i+1));
		echo '<br/><br/><b>Tick ' . ($i+1) . ':</b>';
		$gnsimu_m->PrintStates();
    }

    $gnsimu_m->PrintOverView();
}

?>
