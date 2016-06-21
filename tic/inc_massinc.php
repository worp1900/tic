<h2>Angriffsplanung 2.0</h2>
<p style="border: 2px red solid; background-color: rgb(200, 200, 0)">Dieser Bereich befindet sich noch in Entwicklung. /dv</p>
<?php
$Benutzer['rang'] = 1;

$refresh = 20;

function getOnlineStatus($g, $p, $thresh = 300) {
	global $SQL_DBConn;
	$lastlogin = mysql_result(tic_mysql_query("SELECT lastlogin FROM gn4accounts WHERE galaxie = '".mysql_real_escape_string($g)."' AND planet = '".mysql_real_escape_string($p)."'", __FILE__, __LINE__), 0, 0);
	return time() - $lastlogin < $thresh;
}

function showError($msg) {
	echo '<p><b>Fehler: ' . $msg . '</b>. <a href="javascript:history.back();">&raquo; zur&uumlck</a></p>';
}




$SQL_DEBUG = false;
//general vars
$project = postOrGet('project');

//project mgmt
$proj_edit;
$proj_del;
$proj_neu;

//aprint(array('POST' => $_POST, 'GET' => $_GET,));

//for all.
if(postOrGet('wave_askonline')) {
	ob_end_clean();
	ob_start();
	$started_g = postOrGet('started_g');
	$started_p = postOrGet('started_p');
	$started = getOnlineStatus($started_g, $started_p);
	echo '<html><head><meta http-equiv="refresh" content="'.$refresh.'"/></head><body style=" margin-top: 0px; margin-bottom: 0px; margin-left: 0px; margin-right: 0px;
padding: 0; font-family: helvetica; font-size: 9pt; font-weight: bold; text-align: center; background-color: '.($started ? '55ff55' : '#ff5555').'">'.($started ? 'JA' : 'NEIN').'</body></html>';
	ob_flush();
	exit();
}
if(postOrGet('wave_askfleet')) {
	ob_end_clean();
	ob_start();

	$started_g = postOrGet('started_g');
	$started_p = postOrGet('started_p');
	$started_f = postOrGet('started_f');

	$sql = "SELECT count(b.id) = 1 AS ok FROM gn4massinc_zuweisung z
			LEFT JOIN gn4flottenbewegungen b
			ON b.angreifer_galaxie = z.atter_gal
				AND b.angreifer_planet = z.atter_pla
				AND b.verteidiger_galaxie = z.dest_gal
				AND b.verteidiger_planet = z.dest_pla
				AND b.modus = 1
				AND b.ankunft = (floor(z.welle / 15 / 60) + z.relative_starttick + 30) * 15 * 60
			WHERE
				z.project_fk = '".mysql_real_escape_string($project)."'
				AND z.welle = '".mysql_real_escape_string($wave)."'
				AND z.atter_gal = '".mysql_real_escape_string($started_g)."'
				AND z.atter_pla = '".mysql_real_escape_string($started_p)."'
				AND z.fleet_id = '".mysql_real_escape_string($started_f)."'
			GROUP BY z.project_fk";
	if($SQL_DEBUG) aprint($sql);
	list($started) = mysql_fetch_row(tic_mysql_query($sql, __FILE__, __LINE__));
	echo '<html><head><meta http-equiv="refresh" content="'.$refresh.'"/></head><body style="margin-top: 0px; margin-bottom: 0px; margin-left: 0px; margin-right: 0px;
padding: 0; font-family: helvetica; font-size: 9pt; font-weight: bold; text-align: center; background-color: '.($started ? '55ff55' : '#ff5555').'">'.($started ? 'JA' : 'NEIN').'</body></html>';
	ob_flush();
	exit();
}


if(postOrGet('start_f')) {
	$f = postOrGet('start_f');
	$sql1 = "SET @g = '".mysql_real_escape_string($Benutzer['galaxie'])."', @p = '".mysql_real_escape_string($Benutzer['planet'])."', @f = '".mysql_real_escape_string($f)."';";
	$sql2 = "SELECT atter_gal, atter_pla, dest_gal, dest_pla, fleet_id, welle, relative_starttick FROM gn4massinc_zuweisung WHERE atter_gal = @g AND atter_pla = @p AND fleet_id = @f";
	if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
	tic_mysql_query($sql1, __FILE__, __LINE__);
	$res = tic_mysql_query($sql2, __FILE__, __LINE__);
	$num = mysql_num_rows($res);

	if($num == 1) {
		list($atter_gal, $atter_pla, $dest_gal, $dest_pla, $fleet_id, $welle, $relative_starttick) = mysql_fetch_row($res);
		$welle = floor($welle / 60 / 15);
		//valid, now create fleet.
		$ankunft = ($welle + $relative_starttick + 30) * 15 * 60;
		$flugzeit_ende = $ankunft + 5 * 15 * 60;
		$rueckflug_ende = $flugzeit_ende + 30 * 15 * 60;
		$sql1 = "SET @atter_gal = ".$atter_gal.", @atter_pla = ".$atter_pla.", @dest_gal = ".$dest_gal.", @dest_pla = ".$dest_pla.", @fleet_id = ".$fleet_id.",
					@ticid = '".$Benutzer['ticid']."',
					@modus = 1,
					@eta = 30,
					@flugzeit = 5,
					@ankunft = '".$ankunft."',
					@flugzeit_ende = '".$flugzeit_ende."',
					@ruckflug_ende = '".$rueckflug_ende."',
					@erfasser = '".$Benutzer['name']."',
					@erfasst_am = '".date('H:i \U\h\r \a\m d.m.Y')."',
					@welle = '".$welle."';";
		$sql2 = "INSERT INTO gn4flottenbewegungen (
						ticid, modus, angreifer_galaxie, angreifer_planet, verteidiger_galaxie, verteidiger_planet, eta, flugzeit, flottennr, ankunft, flugzeit_ende, ruckflug_ende, erfasser, erfasst_am
					)
					SELECT * FROM ( SELECT
						@ticid, @modus, @atter_gal, @atter_pla, @dest_gal, @dest_pla, @eta, @flugzeit, @fleet_id, @ankunft, @flugzeit_ende, @ruckflug_ende, @erfasser, @erfasst_am
					) tmp
					WHERE NOT EXISTS(
						SELECT * FROM gn4flottenbewegungen WHERE
							modus = @modus
							AND angreifer_galaxie = @atter_gal
							AND angreifer_planet = @atter_pla
							AND verteidiger_galaxie = @dest_gal
							AND verteidiger_planet = @dest_pla
							AND flottennr = @fleet_id
							AND ankunft = '".$ankunft."'
					) LIMIT 1";
		if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
		tic_mysql_query($sql1, __FILE__, __LINE__);
		tic_mysql_query($sql2, __FILE__, __LINE__);
	} else {
		showError('keine passende Flotte gefunden');
	}
}

//MGMT
if($Benutzer['rang'] >= $Rang_GC) {
	$proj_edit = postOrGet('proj_edit');
	$proj_edit_ack = postOrGet('proj_edit_ack');
	$proj_del = postOrGet('proj_del');
	$proj_neu = postOrGet('proj_neu');

	if($proj_del) {
		$sql = "DELETE FROM gn4massinc_projects WHERE project_id = '" . mysql_real_escape_string($proj_del) . "'";
		if($SQL_DEBUG) aprint($sql);
		tic_mysql_query($sql, __FILE__, __LINE__);
	}
	if($proj_neu) {
		$sql = "INSERT INTO gn4massinc_projects (erstellt_von) VALUES ('".mysql_real_escape_string($Benutzer['name'])."')";
		if($SQL_DEBUG) aprint($sql);
		tic_mysql_query($sql, __FILE__, __LINE__);
	}
	if($proj_edit && $proj_edit_ack) {
		$proj_edit_name = postOrGet('proj_edit_name');
		$proj_edit_freigabe = postOrGet('proj_edit_freigabe');
		$sql = "UPDATE gn4massinc_projects SET name = '".mysql_real_escape_string($proj_edit_name)."', freigegeben = '".mysql_real_escape_string($proj_edit_freigabe)."' WHERE project_id = '".mysql_real_escape_string($proj_edit)."'";
		if($SQL_DEBUG) aprint($sql);
		tic_mysql_query($sql, __FILE__, __LINE__);
		$proj_edit = null;
	}

	//towards project
	if($project) {
		$wave = postOrGet('wave');
		$wave_edit = postOrGet('wave_edit');
		$wave_edit_ack = postOrGet('wave_edit_ack');
		$wave_del = postOrGet('wave_del');
		$wave_neu = postOrGet('wave_neu');
		$tab_ziele = postOrGet('tab_ziele');

		if(!empty($wave_del)) {
			$sql = "DELETE FROM gn4massinc_wellen WHERE project_fk = '" . mysql_real_escape_string($project) . "' AND t = '".mysql_real_escape_string($wave_del)."'";
			if($SQL_DEBUG) aprint($sql);
			tic_mysql_query($sql, __FILE__, __LINE__);
		}
		if(!empty($wave_neu)) {
			$sql = "INSERT INTO gn4massinc_wellen (project_fk, t) VALUES ('".mysql_real_escape_string($project)."', UNIX_TIMESTAMP(NOW()))";
			if($SQL_DEBUG) aprint($sql);
			tic_mysql_query($sql, __FILE__, __LINE__);
		}
		if(!empty($wave_edit) && $wave_edit_ack) {
			$wave_edit_t = postOrGet('wave_edit_t');
			$wave_edit_old_t = postOrGet('wave_edit_old_t');
			$wave_edit_kommentar = postOrGet('wave_edit_kommentar');
			$sql = "UPDATE gn4massinc_wellen SET t = UNIX_TIMESTAMP(STR_TO_DATE('".mysql_real_escape_string($wave_edit_t)."', '%Y-%m-%d %H:%i')), kommentar = '".$wave_edit_kommentar."' WHERE project_fk = '".mysql_real_escape_string($project)."' AND t = '".$wave_edit_old_t."'";
			if($SQL_DEBUG) aprint($sql);
			tic_mysql_query($sql, __FILE__, __LINE__);
			$wave_edit = null;
		}

		if(postOrGet('edit_welle_user_willing_g') && postOrGet('edit_welle_user_willing_p')) {
			$t = postOrGet('edit_welle_user_willing_t');
			$state = postOrGet('edit_welle_user_willing');
			$g = postOrGet('edit_welle_user_willing_g');
			$p = postOrGet('edit_welle_user_willing_p');

			$freigabestatus = mysql_result(tic_mysql_query("SELECT freigegeben FROM gn4massinc_projects WHERE project_id = '".mysql_real_escape_string($project)."'", __FILE__, __LINE__), 0, 0);

			if($Benutzer['rang'] >= $Rang_GC || $g == $Benutzer['galaxie'] && $p == $Benutzer['planet'] && $freigabestatus == 1) {
				$sql1 = "SET @project = '".mysql_real_escape_string($project)."',
							@welle = '".mysql_real_escape_string($t)."',
							@atter_gal = '".mysql_real_escape_string($g)."',
							@atter_pla = '".mysql_real_escape_string($p)."'";
				if(!$state) {
					$sql2 = "INSERT INTO gn4massinc_atter_willing (project_fk, welle, atter_gal, atter_pla, willing)
							VALUES(@project, @welle, @atter_gal, @atter_pla, 0)
							ON DUPLICATE KEY UPDATE willing = 0";
				} else {
					$sql2 = "INSERT INTO gn4massinc_atter_willing (project_fk, welle, atter_gal, atter_pla, willing)
							VALUES(@project, @welle, @atter_gal, @atter_pla, 1)
							ON DUPLICATE KEY UPDATE willing = 1";
				}
				if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
				tic_mysql_query($sql1, __FILE__, __LINE__);
				tic_mysql_query($sql2, __FILE__, __LINE__);
			}
		}

		if(postOrGet('ziel_welle_update')) {
			$assignment = postOrGet('ziel_welle');
			$sql = "DELETE FROM gn4massinc_ziele_welle WHERE project_fk = '".mysql_real_escape_string($project)."'";
			if($SQL_DEBUG) aprint($sql);
			tic_mysql_query($sql, __FILE__, __LINE__);

			foreach($assignment as $gal=>$v) {
				foreach($v as $pla=>$v2) {
					foreach($v2 as $welle=>$v4) {
						$sql = "INSERT INTO gn4massinc_ziele_welle
								(project_fk, welle, ziel_gal, ziel_pla)
								VALUES ('".mysql_real_escape_string($project)."', '".mysql_real_escape_string($welle)."', '".mysql_real_escape_string($gal)."', '".mysql_real_escape_string($pla)."')";
						if($SQL_DEBUG) aprint($sql);
						tic_mysql_query($sql);
					}
				}
			}

		} else  //this else IS important!
		if(postOrGet('ziele_do_del')) {
			$ziele = postOrGet('ziele_del');
			foreach($ziele as $g=>$v) {
				foreach($v as $p=>$v2) {
					if(!empty(v2)) {
						$sql = "DELETE FROM gn4massinc_ziele WHERE project_fk = '".mysql_real_escape_string($project)."' AND gal = '".mysql_real_escape_string($g)."' AND pla = '".mysql_real_escape_string($p)."'";
						if($SQL_DEBUG) aprint($sql);
						tic_mysql_query($sql);
					}
				}
			}
		}
		if(postOrGet('ziele_add')) {
			$meta = explode(';', postOrGet('ziel_add_meta'));
			$allies = explode(';', postOrGet('ziel_add_allianz'));
			$galas = explode(';', postOrGet('ziel_add_galaxie'));

			$where = ' (0 ';
			foreach($meta as $v) {
				$v = trim($v);
				$v = str_replace('?', '_', $v);
				if(strlen($v) > 0) {
					$where .= ' OR meta LIKE "'.mysql_real_escape_string($v).'"';
				}
			}
			foreach($allies as $v) {
				$v = trim($v);
				$v = str_replace('?', '_', $v);
				if(strlen($v) > 0) {
					$where .= ' OR allianz_name LIKE "'.mysql_real_escape_string($v).'"';
				}
			}
			foreach($galas as $v) {
				$v = trim($v);
				if(strlen($v) > 0) {
					$where .= ' OR spieler_galaxie = "'.mysql_real_escape_string($v).'"';
				}
			}
			$where .= ')';

			$sql = "INSERT IGNORE gn4massinc_ziele SELECT '".mysql_real_escape_string($project)."', spieler_galaxie gal, spieler_planet pla FROM gn_spieler2 WHERE " . $where;
			if($SQL_DEBUG) aprint($sql);
			tic_mysql_query($sql, __FILE__, __LINE__);
		}
	}
}

?>
<script> //thx @ http://stackoverflow.com/questions/20618355/the-simplest-possible-javascript-countdown-timer
function startTimer(timestamp, display) {
	var start = Date.now(),
		diff,
		minutes,
		seconds;
	function timer() {
		// get the number of seconds that have elapsed since
		// startTimer() was called
		diff = timestamp - Date.now() / 1000;

		// does the same job as parseInt truncates the float
		minutes = (diff / 60) | 0;
		seconds = (diff % 60) | 0;

		minutes = minutes < 10 ? "0" + minutes : minutes;
		seconds = seconds < 10 ? "0" + seconds : seconds;

		if (diff <= 0) {
			display.textContent = "bereits vergangen";
		} else {
			display.textContent = minutes + ":" + seconds;
		}
	};
	// we don't want to wait a full second before the timer starts
	timer();
	setInterval(timer, 1000);
}
</script>
<?php
//DATA DISPLAY
if(empty($project)) {
	//show projects
	echo '<table class="datatable" align="center">';
	echo '<tr class="datatablehead">';
	if($Benutzer['rang'] >= $Rang_GC) {
		echo '	<td colspan="8">Projekte</td>';
	} else {
		echo '	<td colspan="6">Projekte</td>';
	}
	echo '</tr>';
	echo '<tr class="fieldnormaldark" style="font-weight: bold">';
	echo '	<td>&nbsp;ID&nbsp;</td>';
	echo '	<td>&nbsp;Name&nbsp;</td>';
	echo '	<td>&nbsp;Ersteller&nbsp;</td>';
	echo '	<td>&nbsp;Datum&nbsp;</td>';
	echo '	<td>&nbsp;Freigabe&nbsp;</td>';
	if($Benutzer['rang'] >= $Rang_GC) {
		echo '	<td>&nbsp;&nbsp;</td>';
	}
	echo '	<td>&nbsp;&nbsp;</td>';
	echo '</tr>';

	$sql = "SELECT project_id, name, freigegeben, erstellt_von, erstellt_am FROM gn4massinc_projects ORDER BY project_id";
	if($SQL_DEBUG) aprint($sql);
	$res = tic_mysql_query($sql, __FILE__, __LINE__);
	$num = mysql_num_rows($res);

	$color = false;
	while(list($project_id, $name, $freigegeben, $erstellt_von, $erstellt_am) = mysql_fetch_row($res)) {
		$color = !$color;
		if($freigegeben > 0 || $Benutzer['rang'] >= $Rang_GC) {
			if($proj_edit == $project_id && $Benutzer['rang'] >= $Rang_GC) {
				echo '<form method="post" action="main.php?modul=massinc"><input type="hidden" name="proj_edit" value="'.$proj_edit.'"/>';
				echo '<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
				echo '	<td>&nbsp;'.$project_id.'&nbsp;</td>';
				echo '	<td>&nbsp;<input type="text" name="proj_edit_name" value="'.$name.'"/>&nbsp;</td>';
				echo '	<td>&nbsp;'.$erstellt_von.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$erstellt_am.'&nbsp;</td>';
				echo '	<td>&nbsp;<select name="proj_edit_freigabe">';
				echo '			<option value="0"'.($freigegeben == 0 ? ' selected="selected"' : '').'>nicht freigegeben</option>';
				echo '			<option value="1"'.($freigegeben == 1 ? ' selected="selected"' : '').'>Flotten-Checkin</option>';
				echo '			<option value="2"'.($freigegeben == 2 ? ' selected="selected"' : '').'>Freigegeben</option>';
				echo '		</select>&nbsp;</td>';
				echo '	<td colspan="3">&nbsp;<input type="submit" name="proj_edit_ack" value="absenden"/>&nbsp;</td>';
				echo '</tr>';
				echo '</form>';
			} else {
				echo '<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
				echo '	<td>&nbsp;'.$project_id.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$name.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$erstellt_von.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$erstellt_am.'&nbsp;</td>';
				echo '	<td>&nbsp;';
				switch($freigegeben) {
					case 0: echo 'nicht freigegeben'; break;
					case 1: echo 'Flotten-Checkin'; break;
					case 2: echo 'freigegeben'; break;
				}
				echo '&nbsp;</td>';
				if($Benutzer['rang'] >= $Rang_GC) {
					echo '	<td>&nbsp;<a href="main.php?modul=massinc&proj_edit='.$project_id.'">&raquo; editieren</a>&nbsp;<br/>';
					echo '	&nbsp;<a href="main.php?modul=massinc&proj_del='.$project_id.'">&raquo; l&ouml;schen</a>&nbsp;</td>';
				}
				echo '	<td>&nbsp;<a href="main.php?modul=massinc&project='.$project_id.'">&raquo; ausw&auml;hlen</a>&nbsp;</td>';
				echo '</tr>';
			}
		}

	}
	if($Benutzer['rang'] >= $Rang_GC) {
		echo '<tr class="fieldnormaldark" style="font-weight: bold;">';
			echo '<td colspan="7" align="right"><a href="main.php?modul=massinc&proj_neu=1">&raquo; neu erstellen</a></td>';
		echo '</tr>';
	}
	echo '</table>';


	//show waves without confirmation
	echo '<br/><table class="datatable" align="center" width="100%">';
	echo '<tr class="datatablehead">';
	echo '	<td colspan="3">M&ouml;chtest Du mitmachen?</td>';
	echo '</tr">';
	echo '<tr class="fieldnormaldark" style="font-weight: bold">';
	echo '	<td>&nbsp;Projekt&nbsp;</td>';
	echo '	<td>&nbsp;Welle&nbsp;</td>';
	echo '	<td>&nbsp;&nbsp;</td>';
	echo '</tr>';
	$sql1 = "SET @g = '".mysql_real_escape_string($Benutzer['galaxie'])."', @p = '".mysql_real_escape_string($Benutzer['planet'])."'";
	$sql2 = "SELECT w.project_fk, p.name, w.t
				FROM gn4massinc_wellen w
				LEFT JOIN gn4massinc_projects p ON p.project_id = w.project_fk
				WHERE NOT EXISTS(
					SELECT * FROM gn4massinc_atter_willing a
					WHERE a.project_fk = w.project_fk AND a.welle = w.t AND a.atter_gal = @g AND a.atter_pla = @p
				) ORDER BY w.t";
	if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
	tic_mysql_query($sql1, __FILE__, __LINE__);
	$res = tic_mysql_query($sql2, __FILE__, __LINE__);
	$color = true;
	$i = 0;
	while(list($project_id, $project_name, $welle) = mysql_fetch_row($res)) {
		$i++;
		$color = !$color;
		echo '<tr class="fieldnormal'.($color ? 'dark' : 'light').'">';
		echo '	<td>&nbsp;'.$project_name.'&nbsp;</td>';
		echo '	<td>&nbsp;'.date('Y-m-d H:i', $welle).'&nbsp;</td>';
		echo '	<td>&nbsp;<a href="main.php?modul=massinc&project='.$project_id.'">&raquo; hier w&auml;hlen</a>&nbsp;</td>';
		echo '</tr>';
	}
	if($i == 0) {
		echo '<tr class="fieldnormallight"><td colspan="3">&nbsp;Es liegen keine weiteren Entscheidungen an.&nbsp;</td></tr>';
	}
	echo '</table>';


	//show waves without confirmation
	echo '<br/><table class="datatable" align="center" width="100%">';
	echo '<tr class="datatablehead">';
	echo '	<td colspan="4">Deine Aktionen</td>';
	echo '</tr">';
	echo '<tr class="fieldnormaldark" style="font-weight: bold">';
	echo '	<td>&nbsp;Projekt&nbsp;</td>';
	echo '	<td>&nbsp;Welle&nbsp;</td>';
	echo '	<td>&nbsp;Minuten bis Start&nbsp;</td>';
	echo '	<td>&nbsp;&nbsp;</td>';
	echo '</tr>';
	$sql1 = "SET @g = '".mysql_real_escape_string($Benutzer['galaxie'])."', @p = '".mysql_real_escape_string($Benutzer['planet'])."'";
	$sql2 = "SELECT DISTINCT z.project_fk, p.name, z.welle
				FROM gn4massinc_zuweisung z
				LEFT JOIN gn4massinc_projects p ON p.project_id = z.project_fk
				WHERE z.atter_gal = @g AND z.atter_pla = @p
				ORDER BY z.welle";
	if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
	tic_mysql_query($sql1, __FILE__, __LINE__);
	$res = tic_mysql_query($sql2, __FILE__, __LINE__);
	$color = false;
	$i = 0;
	while(list($project_id, $project_name, $welle) = mysql_fetch_row($res)) {
		$i++;
		$color = !$color;
		echo '<tr bgcolor="#'.($color ? 'ffffaa' : 'dddd99').'">';
		echo '	<td>&nbsp;'.$project_name.'&nbsp;</td>';
		echo '	<td>&nbsp;'.date('Y-m-d H:i', $welle).'&nbsp;</td>';
		echo '	<td>&nbsp;'.ZahlZuText(($welle - time()) / 60).'&nbsp;</td>';
		echo '	<td>&nbsp;<a href="main.php?modul=massinc&project='.$project_id.'&wave='.$welle.'">&raquo; zum Cockpit</a>&nbsp;</td>';
		echo '</tr>';
	}
	if($i == 0) {
		echo '<tr class="fieldnormallight"><td colspan="3">&nbsp;Es liegen keine weiteren Entscheidungen an.&nbsp;</td></tr>';
	}
	echo '</table>';

} else {
	//show project info
	$sql = 'SELECT project_id, name, freigegeben, erstellt_von, erstellt_am FROM gn4massinc_projects WHERE project_id = "'.mysql_real_escape_string($project).'"';
	if($SQL_DEBUG) aprint($sql);
	$res = tic_mysql_query($sql, __FILE__, __LINE__);
	$num = mysql_num_rows($res);

	if($num == 0) {
		showError('Projekt nicht gefunden.');
	} else {
		//proceed with project
		list($project_id, $name, $freigegeben, $erstellt_von, $erstellt_am) = mysql_fetch_row($res);

		echo '<table>';
		echo '<tr class="datatablehead">';
		echo '	<td>&nbsp;<a href="main.php?modul=massinc">&laquo</a>&nbsp;</td>';
		echo '	<td>&nbsp;Projekt: '.$name.'&nbsp;</td>';
		echo '</tr>';
		echo '<tr>';
		echo '	<td class="fieldnormaldark" valign="top">';

		//projects
		/*
		$sql = "SELECT project_id, name FROM gn4massinc_projects ORDER BY project_id";
		$res = tic_mysql_query($sql, __FILE__, __LINE__);
		while(list($id, $name) = mysql_fetch_row($res)) {
			echo '&nbsp;<a href="main.php?modul=massinc&project='.$id.'">&raquo; '.$name.'</a>&nbsp;<br>';
		}*/
		echo '	</td>';

		echo '	<td>';

		$donotshowdestinations = false;

		//show waves
		if(empty($wave)) {
			echo '<table class="datatable" align="center" width="100%">';
			echo '<tr class="datatablehead">';
			if($Benutzer['rang'] >= $Rang_GC) {
				echo '	<td colspan="9">Wellen</td>';
			} else {
				echo '	<td colspan="6">Wellen</td>';
			}
			echo '</tr>';
			echo '<tr class="fieldnormaldark" style="font-weight: bold">';
			echo '	<td>&nbsp;#&nbsp;</td>';
			echo '	<td>&nbsp;Sart&nbsp;</td>';
			echo '	<td>&nbsp;Kommentar&nbsp;</td>';
			if($Benutzer['rang'] >= $Rang_GC) {
				echo '	<td>&nbsp;&nbsp;</td>';
			}
			echo '	<td>&nbsp;Habe Zeit&nbsp;</td>';
			echo '	<td>&nbsp;Zugeteilt&nbsp;</td>';
			if($Benutzer['rang'] >= $Rang_GC) {
				echo '	<td>&nbsp;#Zeit Spieler&nbsp;<br/>&nbsp;(Flotten)&nbsp;</td>';
				echo '	<td>&nbsp;#zugewiesen Spieler&nbsp;<br/>&nbsp;(Flotten)&nbsp;</td>';
			}
			echo '	<td>&nbsp;&nbsp;</td>';
			echo '</tr>';

			$sql1 = 'SET @project = "'.$project.'"';
			$sql2 = "SELECT
						w.t,
						w.kommentar,
						COUNT(z.project_fk) num_flotten_zugewiesen,
						(SELECT COUNT(DISTINCT(CONCAT_WS(':', atter_gal, atter_pla)))
							FROM gn4massinc_zuweisung x
							WHERE x.project_fk = @project AND x.welle = w.t
							) num_spieler_zugewiesen,
						(SELECT SUM(s.off_fleets)
							FROM gn4massinc_atter_willing a
							LEFT JOIN gn4massinc_atter s
							ON s.gal = a.atter_gal AND s.pla = a.atter_pla
							WHERE a.project_fk = @project AND a.welle = w.t AND a.willing = 1) zeit_fleets,
						(SELECT COUNT(a.project_fk)
                         	FROM gn4massinc_atter_willing a
							LEFT JOIN gn4massinc_atter s
							ON s.gal = a.atter_gal AND s.pla = a.atter_pla
							WHERE a.project_fk = @project AND a.welle = w.t AND a.willing = 1) zeit_spieler,
						(SELECT COUNT(DISTINCT(CONCAT_WS(';', a.atter_gal, a.atter_pla)))
							FROM gn4massinc_atter_willing a
							WHERE a.project_fk = @project) distinct_spieler,
						(SELECT sum(x.off_fleets)
							FROM (
								SELECT DISTINCT(CONCAT_WS(':', w.atter_gal, w.atter_pla)), a.off_fleets
								FROM gn4massinc_atter_willing w
								LEFT JOIN gn4massinc_atter a ON w.atter_gal = a.gal AND w.atter_pla = a.pla
								WHERE w.project_fk = @project
							) x) distinct_fleets
					FROM gn4massinc_wellen w
					LEFT JOIN gn4massinc_zuweisung z ON z.project_fk = w.project_fk AND z.welle = w.t
					WHERE w.project_fk = @project
					GROUP BY w.t, w.kommentar
					ORDER BY w.t ASC";
			if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
			tic_mysql_query($sql1, __FILE__, __LINE__);
			$res = tic_mysql_query($sql2, __FILE__, __LINE__);
			$num = mysql_num_rows($res);

			$color = false;
			$i = 0;
			$distinct_spieler = 0;
			$distinct_fleets = 0;
			while(list($t, $kommentar, $fleets_zugewiesen, $spieler_zugewiesen, $zeit_fleets, $zeit_spieler, $d_spieler, $d_fleets) = mysql_fetch_row($res)) {
				$distinct_spieler = $d_spieler;
				$distinct_fleets = $d_fleets;
				$i++;
				$color = !$color;
				if($freigegeben > 0 || $Benutzer['rang'] >= $Rang_GC) {
					if($wave_edit == $t && $Benutzer['rang'] >= $Rang_GC) {
						echo '<form method="post" action="main.php?modul=massinc&project='.$project.'">';
						echo '<input type="hidden" name="wave_edit_old_t" value="'.$t.'"/><input type="hidden" name="wave_edit" value="'.$wave_edit.'"/>';
						echo '<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
						echo '	<td valign="top">&nbsp;'.$i.'&nbsp;</td>';
						echo '	<td valign="top">&nbsp;<input type="text" name="wave_edit_t" value="'.date('Y-m-d H:i', $t).'"/>&nbsp;</td>';
						echo '	<td valign="top" colspan="4">&nbsp;<textarea name="wave_edit_kommentar">'.$kommentar.'</textarea>&nbsp;</td>';
						echo '	<td valign="top" colspan="3">&nbsp;<input type="submit" name="wave_edit_ack" value="absenden"/>&nbsp;</td>';
						echo '</tr>';
						echo '</form>';
					} else {
						echo '<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
						echo '	<td>&nbsp;'.$i.'&nbsp;</td>';
						echo '	<td>&nbsp;'.date('Y-m-d H:i', $t).'&nbsp;</td>';
						echo '	<td>&nbsp;'.$kommentar.'&nbsp;</td>';
						if($Benutzer['rang'] >= $Rang_GC) {
							echo '	<td>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave_edit='.$t.'">&raquo; editieren</a>&nbsp;<br/>';
							echo '	&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave_del='.$t.'">&raquo; l&ouml;schen</a>&nbsp;</td>';
						}

						$sql1 = "SET @project = '".mysql_real_escape_string($project)."',
									@welle = '".mysql_real_escape_string($t)."',
									@atter_gal = '".mysql_real_escape_string($Benutzer['galaxie'])."',
									@atter_pla = '".mysql_real_escape_string($Benutzer['planet'])."'";
						$sql2 = "SELECT willing FROM gn4massinc_atter_willing w WHERE w.project_fk=@project AND w.welle=@welle AND w.atter_gal=@atter_gal AND w.atter_pla=@atter_pla";
						if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$res2 = tic_mysql_query($sql2, __FILE__, __LINE__);
						$habeZeit = null;
						if(mysql_num_rows($res2) > 0) {
							$habeZeit = mysql_result($res2, 0, 'willing');
						}

						if(is_null($habeZeit)) {
							echo '	<td bgcolor="#ffffaa">&nbsp;<b>Bitte eintragen!</b><br/>';
						} else if($habeZeit == 0) {
							echo '	<td>&nbsp;NEIN<br/>';
						} else {
							echo '	<td>&nbsp;JA<br/>';
						}
						if($freigegeben == 1) {
							echo '<a href="main.php?modul=massinc&project='.$project.'&edit_welle_user_willing_t='.$t.'&edit_welle_user_willing='.($habeZeit ? 0 : 1).'&edit_welle_user_willing_g='.$Benutzer['galaxie'].'&edit_welle_user_willing_p='.$Benutzer['planet'].'">&raquo; &auml;ndern</a>';
						}
						echo '</td>';

						$sql = "SELECT count(*) > 0 FROM gn4massinc_zuweisung WHERE project_fk='".mysql_real_escape_string($project)."' AND welle='".mysql_real_escape_string($t)."' AND atter_gal='".mysql_real_escape_string($Benutzer['galaxie'])."' AND atter_pla='".mysql_real_escape_string($Benutzer['planet'])."'";
						if($SQL_DEBUG) aprint($sql);
						$zugeteilt = mysql_result(tic_mysql_query($sql, __FILE__, __LINE__), 0, 0);
						if($freigegeben == 2) {
							echo '	<td>&nbsp;'.($zugeteilt ? '<b>JA</b>' : 'NEIN').'&nbsp;</td>';
						} else {
							echo '	<td>&nbsp;<i>noch keine Freigabe</i>'.($zugeteilt ? '<br/>(JA)' : '').'&nbsp;</td>';
						}

						if($Benutzer['rang'] >= $Rang_GC) {
							echo '	<td>&nbsp;'.$zeit_spieler .'&nbsp;<br/>&nbsp;('.$zeit_fleets.')&nbsp;</td>';
							echo '	<td>&nbsp;'.$spieler_zugewiesen.'&nbsp;<br/>&nbsp;('.$fleets_zugewiesen.')&nbsp;</td>';
						}

						echo '	<td>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$t.'">&raquo; Zuweisung</a>&nbsp;</td>';
						echo '</tr>';
					}
				}

			}
			if($Benutzer['rang'] >= $Rang_GC) {
				echo '<tr class="fieldnormaldark" style="font-weight: bold;">';
				echo '	<td colspan="6"></td>';
				echo '	<td colspan="2">Spieler (Flotten): '.$distinct_spieler.' ('.$distinct_fleets.')</td>';
				echo '	<td align="right"><a href="main.php?modul=massinc&project='.$project.'&wave_neu=1">&raquo; neu erstellen</a></td>';
				echo '</tr>';
			}
			echo '</table>';
		} else {
			//show wave info
			$sql = 'SELECT project_fk, t, kommentar FROM gn4massinc_wellen WHERE project_fk = "'.mysql_real_escape_string($project).'" AND t = "'.mysql_real_escape_string($wave).'"';
			$res = tic_mysql_query($sql, __FILE__, __LINE__);
			$num = mysql_num_rows($res);
			list($project, $t, $kommentar) = mysql_fetch_row($res);

			if($num == 0) {
				showError('Welle nicht gefunden.');
			} else {
				echo '<table>';
				echo '<tr class="datatablehead">';
				echo '	<td width="150">&nbsp;<a href="main.php?modul=massinc&project='.$project.'">&laquo; Zum Projekt</a>&nbsp;</td>';
				echo '	<td colspan="2">&nbsp;Welle: '.date('Y-m-d H:i', $wave).'&nbsp;</td>';
				echo '<td><a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.($tab_ziele ? '&tab_ziele=1' : '').'">&raquo; Refresh</a></td>';
				echo '</tr>';
				echo '<tr>';
				echo '	<td align="left" class="fieldnormaldark" valign="top" width="155">';

				//waves
				$sql1 = "SET @project = '".mysql_real_escape_string($project)."', @g = '".$Benutzer['galaxie']."', @p = '".$Benutzer['planet']."';";
				$sql2 = "SELECT w.t, count(z.project_fk) flotten
							FROM gn4massinc_wellen w
							LEFT JOIN gn4massinc_zuweisung z ON z.welle = w.t AND z.project_fk = z.project_fk AND z.atter_gal = @g AND z.atter_pla = @p
							WHERE w.project_fk = @project
							GROUP BY w.t
							ORDER BY w.t ASC";
				//if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
				tic_mysql_query($sql1, __FILE__, __LINE__);
				$res = tic_mysql_query($sql2, __FILE__, __LINE__);
				while(list($t, $flotten) = mysql_fetch_row($res)) {
					if($flotten > 0) {
						echo '<b>';
					}

					echo '&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$t.'">&raquo; '.date('Y-m-d H:i', $t).'</a>&nbsp;<br>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$t.'">&raquo; Cockpit ('.$flotten.')</a><br/>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$t.'&tab_ziele=1">&raquo; Ziele</a><br/>';

					if($flotten > 0) {
						echo '</b>';
					}
					echo '<br/>';
				}

				echo '	</td>';
				echo '	<td colspan="3">';
				echo '<table width="100%"><tr class="datatablehead" style="font-weight: bold"><td>Zielsetzung &amp; Beschreibung</td></tr><tr class="fieldnormallight"><td align="left">'.nl2br($kommentar).'</td></tr></table>';

				if($tab_ziele) {

					//TARGET INFO
					echo '<br/><table>';
					$sql1 = "SET @g = '".$Benutzer['galaxie']."', @p = '".$Benutzer['planet']."', @w = '".mysql_real_escape_string($wave)."';";
					$sql2 = "SELECT DISTINCT z.dest_gal, z.dest_pla, s.spieler_name, s.meta, s.allianz_name,
								s0.pts, s0.s, s0.d, s0.me, s0.ke,
								s1.sfj, s1.sfb, s1.sff, s1.sfz, s1.sfkr, s1.sfsa, s1.sft, s1.sfka, s1.sfsu,
								s3.glo, s3.glr, s3.gmr, s3.gsr, s3.ga,
								blocks.typ, blocks.svs
							FROM gn4massinc_zuweisung z
							LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = z.dest_gal AND s.spieler_planet = z.dest_pla
							LEFT JOIN gn4scans s0 ON s0.rg = z.dest_gal AND s0.rp = z.dest_pla AND s0.type = 0
							LEFT JOIN gn4scans s1 ON s1.rg = z.dest_gal AND s1.rp = z.dest_pla AND s1.type = 1
							LEFT JOIN gn4scans s3 ON s3.rg = z.dest_gal AND s3.rp = z.dest_pla AND s3.type = 3
							LEFT JOIN (SELECT b1.g, b1.p, b1.svs, b1.typ
										FROM gn4scanblock b1
										WHERE b1.svs = (SELECT MAX(b2.svs)
														FROM gn4scanblock b2
														WHERE b2.g = b1.g AND b2.p = b1.p)
										LIMIT 1				
										) blocks ON blocks.g = z.dest_gal AND blocks.p = z.dest_pla
							WHERE z.atter_gal = @g AND z.atter_pla = @p ORDER BY z.welle + z.relative_starttick*15*60";
					if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
					tic_mysql_query($sql1, __FILE__, __LINE__);
					$res = tic_mysql_query($sql2, __FILE__, __LINE__);
					while(list($g, $p, $name, $meta, $allianz,
							$pts, $s, $d, $me, $ke,
							$sfja, $sfbo, $sffr, $sfze, $sfkr, $sfsc, $sftr, $sfcl, $sfca,
							$glo, $glr, $gmr, $gsr, $ga,
							$btyp, $bsvs) = mysql_fetch_row($res)) {
						echo '	<tr class="datatablehead">';
						echo '		<td colspan="7" width="500">&nbsp;Deine Ziele&nbsp;</td>';
						echo '		<td rowspan="11" bgcolor="white" style="width: 5px"></td>';
						echo '		<td width="600">&nbsp;Latest News&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr>';
						echo '		<td class="fieldnormaldark">&nbsp;<b>Meta</b>&nbsp;</td>';
						echo '		<td class="fieldnormallight" colspan="2">&nbsp;'.(!empty($meta) ? $meta : '-').'&nbsp;</td>';
						echo '		<td class="fieldnormaldark">&nbsp;<b>Allianz&nbsp;</b></td>';
						echo '		<td class="fieldnormallight"colspan="2">&nbsp;'.(!empty($allianz) ? $allianz : '-').'&nbsp;</td>';
						echo '		<td class="fieldnormallight"rowspan="2">&nbsp;<a href="main.php?modul=showgalascans&xgala='.$g.'&xplanet='.$p.'&displaytype=0">&raquo; Scans</a>&nbsp;<br/>&nbsp;<a href="https://gntic.de/x/player.php?name='.$name.'" target="_blank">&raquo; Pkt-Hist</a>&nbsp;</td>';
						echo '		<td rowspan="10" valign="top">';

						echo '<table width="100%">';
						echo '<tr class="fieldnormaldark" style="font-weight: bold;">';
						$sql1 = "SET @gal = '".mysql_real_escape_string($g)."', @pla = '".mysql_real_escape_string($p)."';";
						$sql2 = "SELECT id, t, genauigkeit FROM gn4scans_news WHERE ziel_g = @gal AND ziel_p = @pla ORDER BY t DESC LIMIT 1";
						if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$res2 = tic_mysql_query($sql2, __FILE__, __LINE__);
						$num2 = mysql_num_rows($res2);
						if($num2 == 0) {
							echo '<td class="fieldnormallight" style="font-weight: normal">&nbsp;keine Eintr&auml;ge&nbsp;</td>';
						} else {
							$news_id = mysql_result($res2, 0, 'id');
							echo '<td width="155">&nbsp;'.date('Y-m-d H:i', mysql_result($res2, 0, 't')).'&nbsp;</td>';
							echo '<td>&nbsp;'.mysql_result($res2, 0, 'genauigkeit').'%&nbsp;</td>';
							echo '<td align="right">&nbsp;<a href="'.makeRequestScanLink($g, $p, 4, 'modul=massinc&project='.$project.'&wave='.$wave.'&tab_ziele=1').'">&raquo; request News</a> &nbsp; <a href="main.php?modul=showgalascans&xgala='.$g.'&xplanet='.$p.'&displaytype=news&newsid='.$news_id.'">&raquo; mehr</a>&nbsp;</td></tr>';

							$sql3 = "SET @newsid = " . $news_id;
							$sql4 = "SELECT t, typ, inhalt, inaccurate
									FROM gn4scans_news_entries
									WHERE news_id = @newsid
									LIMIT 10";
							if($SQL_DEBUG) aprint(join("\n\n", array($sql3, $sql4)));
							tic_mysql_query($sql3, __FILE__, __LINE__);
							$res3 = tic_mysql_query($sql4, __FILE__, __LINE__);
							$color = false;
							while(list($nt, $ntyp, $ncontent, $ninaccurate) = mysql_fetch_row($res3)) {
								$color = !$color;
								$age = round((time() - $nt) / 60, 0);
								if(in_array_contains(array('Angriffsbericht', 'Verteidigungsbericht', 'Artilleriebeschuss', 'Artilleriesysteme', 'Galaxie-Abgabensatz'), $ntyp)) {
									$ncontent = '<i>*snip*</i>';
								}
								echo '<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
								echo '	<td>&nbsp;'.ZahlZuText($age).'min&nbsp;</td>';
								echo '	<td align="left">&nbsp;'.$ntyp.'&nbsp;</td>';
								echo '	<td align="left">&nbsp;'.makeNewsPrettier($ncontent).'&nbsp;</td>';
								echo '</tr>';
							}//while news entries
						}//if num news > 0
						echo '</table>';

						echo 		'</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormaldark">';
						echo '		<td bgcolor="#ddddfd" colspan="6" style="font-weight: bold;">&nbsp;'.$g.':'.$p.' - '.$name.'&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormaldark" style="font-weight: bold">';
						echo '		<td colspan="2">&nbsp;Punkte&nbsp;</td>';
						echo '		<td>&nbsp;Schiffe&nbsp;</td>';
						echo '		<td>&nbsp;Defensiv&nbsp;</td>';
						echo '		<td>&nbsp;Exen K&nbsp;</td>';
						echo '		<td>&nbsp;Exen M&nbsp;</td>';
						echo '		<td title="Fordere Scans per Slack an." width="100" rowspan="2" style="font-weight: normal">&nbsp;<b>Scan-Req:</b>&nbsp;<br/>
									<a href="'.makeRequestScanLink($g, $p, 0, 'modul=massinc&project='.$project.'&wave='.$wave.'&tab_ziele=1').'">&raquo; Sektor</a>&nbsp;<br/>
									<a href="'.makeRequestScanLink($g, $p, 3, 'modul=massinc&project='.$project.'&wave='.$wave.'&tab_ziele=1').'">&raquo; Gesch&uuml;tze</a>&nbsp;<br/>
									<a href="'.makeRequestScanLink($g, $p, 1, 'modul=massinc&project='.$project.'&wave='.$wave.'&tab_ziele=1').'">&raquo; Einheiten</a>&nbsp;<br/>
									<a href="'.makeRequestScanLink($g, $p, 2, 'modul=massinc&project='.$project.'&wave='.$wave.'&tab_ziele=1').'">&raquo; Milit&auml;r</a>&nbsp;<br/>
									<a href="'.makeRequestScanLink($g, $p, 4, 'modul=massinc&project='.$project.'&wave='.$wave.'&tab_ziele=1').'">&raquo; News</a>&nbsp;<br/>
							</td>';
						
						echo '	</tr>';
						echo '	<tr class="fieldnormallight">';
						echo '		<td colspan="2">&nbsp;'.ZahlZuText($pts).'&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($s).'&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($d).'&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($me).'&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($ke).'&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormaldark" style="font-weight: bold">';
						echo '		<td bgcolor="#ddddfd">&nbsp;AJ&nbsp;</td>';
						echo '		<td bgcolor="#ddddfd">&nbsp;LO&nbsp;</td>';
						echo '		<td bgcolor="#ddddfd">&nbsp;LR&nbsp;</td>';
						echo '		<td bgcolor="#ddddfd">&nbsp;MR&nbsp;</td>';
						echo '		<td bgcolor="#ddddfd">&nbsp;SR&nbsp;</td>';
						echo '		<td colspan="2">&nbsp;Scanblock&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormallight">';
						echo '		<td>&nbsp;'.ZahlZuText($ga).'&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($glo).'&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($glr).'&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($gmr).'&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($gsr).'&nbsp;</td>';
						if($btyp)
							echo '		<td colspan="2">&nbsp;'.ZahlZuText($bsvs).' SVS '.scanTypeName($btyp).'&nbsp;</td>';
						else
							echo '		<td colspan="2">&nbsp;-&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormaldark"><td colspan="7" style="height: 5px"></td></tr>';
						echo '	<tr class="fieldnormallight">';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Ja&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfja).'&nbsp;</td>';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Bo&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfbo).'&nbsp;</td>';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Fr&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sffr).'&nbsp;</td>';
						echo '		<td rowspan="3" class="fieldnormaldark" title="Simulieren Deinen Angriff samt Mitstreitern und ggf. gescannten Deffern.">&nbsp;<a href="#">&raquo; Simu</a></td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormallight">';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Ze&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfze).'&nbsp;</td>';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Kr&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfkr).'&nbsp;</td>';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Sc&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfsc).'&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormallight">';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Tr&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sftr).'&nbsp;</td>';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Cl&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfcl).'&nbsp;</td>';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Ca&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfca).'&nbsp;</td>';
						echo '	</tr>';
						echo '<tr class="fieldnormaldark" style="height: 5px"><td colspan="9"></td></tr>';
					}//while ziele
					echo '</table>';

				} else {

					//FLEET INFO
					echo '<br/><table>';
					echo '	<tr class="datatablehead">';
					echo '		<td colspan="8">&nbsp;Deine Flotten&nbsp;</td>';
					echo '		<td>&nbsp;Online&nbsp;</td>';
					echo '		<td>&nbsp;Gestartet&nbsp;</td>';
					echo '		<td>&nbsp;</td>';
					echo '	</tr>';
					echo '	<tr class="fieldnormaldark" style="font-weight: bold">';
					echo '		<td>&nbsp;Startfenster&nbsp;</td>';
					echo '		<td>&nbsp;Galaxie&nbsp;</td>';
					echo '		<td>&nbsp;Planet&nbsp;</td>';
					echo '		<td>&nbsp;Flotten-Nr.&nbsp;</td>';
					echo '		<td>&nbsp;&nbsp;</td>';
					echo '		<td>&nbsp;Galaxie&nbsp;</td>';
					echo '		<td>&nbsp;Planet&nbsp;</td>';
					echo '		<td>&nbsp;Spieler&nbsp;</td>';
					echo '		<td>&nbsp;TIC&nbsp;</td>';
					echo '		<td>&nbsp;&nbsp;</td>';
					echo '		<td>&nbsp;&nbsp;</td>';
					echo '	</tr>';

					$timer1 = 0;
					$timer2 = 0;
					$sql1 = 'SET @proj = "'.mysql_real_escape_string($project).'", @welle = "'.mysql_real_escape_string($wave).'", @refgal = "'.$Benutzer['galaxie'].'", @refpla="'.$Benutzer['planet'].'";';
					tic_mysql_query($sql1, __FILE__, __LINE__);
					$sql2 = 'SELECT z.welle, z.atter_gal, z.atter_pla, z.dest_gal, z.dest_pla, z.fleet_id, z.kommentar, z.relative_starttick, u.name,
								f.ja, f.bo, f.fr, f.ze, f.kr, f.sc, f.tr, f.cl, f.ca,
								s.sf1j, s.sf1b, s.sf1f, s.sf1z, s.sf1kr, s.sf1sa, s.sf1t, s.sf1ka, s.sf1su,
								s.sf2j, s.sf2b, s.sf2f, s.sf2z, s.sf2kr, s.sf2sa, s.sf2t, s.sf2ka, s.sf2su
							FROM gn4massinc_zuweisung z
							LEFT JOIN gn4massinc_fleets f ON f.project_fk = z.project_fk AND z.fleet_id = f.fleet AND z.atter_gal = f.atter_gal AND z.atter_pla = f.atter_pla
							LEFT JOIN gn4gnuser u ON u.gala = z.dest_gal AND u.planet = z.dest_pla
							LEFT JOIN gn4scans s ON s.rg = @refgal AND s.rp = @refpla AND s.type = 2
							WHERE z.project_fk = @proj AND z.welle = @welle AND z.atter_gal = @refgal AND z.atter_pla = @refpla ORDER BY z.welle + z.relative_starttick * 15 * 60';
					if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
					$res = tic_mysql_query($sql2, __FILE__, __LINE__);
					$color = true;
					while(list($t, $atter_g, $atter_p, $dest_g, $dest_p, $fleetid, $kommentar, $relative_start, $name,
								$fja, $fbo, $ffr, $fze, $fkr, $fsc, $ftr, $fcl, $fca,
								$sja[1], $sbo[1], $sfr[1], $sze[1], $skr[1], $ssc[1], $str[1], $scl[1], $sca[1],
								$sja[2], $sbo[2], $sfr[2], $sze[2], $skr[2], $ssc[2], $str[2], $scl[2], $sca[2]
								) = mysql_fetch_row($res)) {
						$color = !$color;
						$tickstart = (floor($t / 60 / 15) + $relative_start) * 60 * 15;
						$startstr = date('H:i:s', $tickstart + 15) . '&nbsp;<br/>&nbsp;' . date('H:i:s', $tickstart + 15 * 60 - 1);

						if($fleetid == 1) {
							$timer1 = $tickstart + 7.5*60;
						} else {
							$timer2 = $tickstart + 7.5*60;
						}
						echo '<tr bgcolor="#'.($color ? 'dddd99' : 'cccc88').'">';
						echo '	<td>&nbsp;'.$startstr.'&nbsp;</td>';
						echo '	<td>&nbsp;'.$atter_g.'&nbsp;</td>';
						echo '	<td>&nbsp;'.$atter_p.'&nbsp;</td>';
						echo '	<td>&nbsp;#'.$fleetid.'&nbsp;</td>';
						echo '	<td>&nbsp;&nbsp;&nbsp;<b>&gt;&gt;</b>&nbsp;&nbsp;&nbsp;</td>';
						echo '	<td>&nbsp;'.$dest_g.'&nbsp;</td>';
						echo '	<td>&nbsp;'.$dest_p.'&nbsp;</td>';
						echo '	<td>&nbsp;'.$name.'&nbsp;</td>';
						echo '	<td>&nbsp;<iframe src="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&wave_askonline=1&started_g='.$atter_g.'&started_p='.$atter_p.'" style="height: 16px; width: 30px; overflow:hidden; border: 1px solid darkgray" scrolling="no"></iframe>&nbsp;</td>';
						echo '	<td>&nbsp;<iframe src="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&wave_askfleet=1&started_g='.$atter_g.'&started_p='.$atter_p.'&started_f='.$fleetid.'" style="height: 16px; width: 30px; overflow:hidden; border: 1px solid darkgray" scrolling="no"></iframe>&nbsp;</td>';
						echo '	<td rowspan="3">&nbsp;<span id="timer'.$fleetid.'" style="font-size: 12pt; font-weight: bold"><br/>';
						if($fleetid == 1 && $timer1 < 0) echo 'bereits vergangen';
						if($fleetid == 2 && $timer2 < 0) echo 'bereits vergangen';
						echo '</span>&nbsp;<br/><br/>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&start_f='.$fleetid.'" title="Du kannst Deinen Flottenstart hier ins TIC eintragen.">&raquo; Exakt gestartet &gt;&gt; TIC</a>&nbsp;</td>';
						echo '</tr>';
						echo '<tr class="fieldnormaldark" style="font-weight: bold">';
						echo '	<td bgcolor="white">&nbsp;</td>';
						echo '	<td>&nbsp;Ja&nbsp;</td>';
						echo '	<td>&nbsp;Bo&nbsp;</td>';
						echo '	<td>&nbsp;Fr&nbsp;</td>';
						echo '	<td>&nbsp;Ze&nbsp;</td>';
						echo '	<td>&nbsp;Kr&nbsp;</td>';
						echo '	<td>&nbsp;Sc&nbsp;</td>';
						echo '	<td>&nbsp;Tr&nbsp;</td>';
						echo '	<td>&nbsp;Cl&nbsp;</td>';
						echo '	<td>&nbsp;Ca&nbsp;</td>';
						echo '</tr>';
						echo '<tr class="fieldnormallight">';
						echo '	<td bgcolor="white" align="right" style="font-weight: normal">&nbsp;Vorschlag:&nbsp;</td>';
						echo '	<td>&nbsp;'.($fja > 0 ? ZahlZuText($ja) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($fbo > 0 ? ZahlZuText($fbo) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($ffr > 0 ? ZahlZuText($ffr) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($fze > 0 ? ZahlZuText($fze) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($fkr > 0 ? ZahlZuText($fkr) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($fsc > 0 ? ZahlZuText($fsc) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($ftr > 0 ? ZahlZuText($ftr) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($fcl > 0 ? ZahlZuText($fcl) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($fca > 0 ? ZahlZuText($fca) : '-').'&nbsp;</td>';
						echo '</tr>';
						echo '<tr class="fieldnormallight">';
						echo '	<td bgcolor="white" align="right" style="font-weight: normal">&nbsp;Flotte:&nbsp;</td>';
						echo '	<td>&nbsp;'.($sja[$fleetid] > 0 ? ZahlZuText($sja[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($sbo[$fleetid] > 0 ? ZahlZuText($sbo[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($sfr[$fleetid] > 0 ? ZahlZuText($sfr[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($sze[$fleetid] > 0 ? ZahlZuText($sze[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($skr[$fleetid] > 0 ? ZahlZuText($skr[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($ssc[$fleetid] > 0 ? ZahlZuText($ssc[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($str[$fleetid] > 0 ? ZahlZuText($str[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($scl[$fleetid] > 0 ? ZahlZuText($scl[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($sca[$fleetid] > 0 ? ZahlZuText($sca[$fleetid]) : '-').'&nbsp;</td>';
						echo '</tr>';
					}

					echo '	<tr class="datatablehead">';
					echo '		<td colspan="8">&nbsp;Mitstreiter&nbsp;</td>';
					echo '		<td>&nbsp;Online&nbsp;</td>';
					echo '		<td>&nbsp;Gestartet&nbsp;</td>';
					echo '	</tr>';
					$color = false;
					echo '	<tr class="fieldnormaldark" style="font-weight: bold">';
					echo '		<td>&nbsp;Startfenster&nbsp;</td>';
					echo '		<td>&nbsp;Galaxie&nbsp;</td>';
					echo '		<td>&nbsp;Planet&nbsp;</td>';
					echo '		<td>&nbsp;Flotten-Nr.&nbsp;</td>';
					echo '		<td>&nbsp;&nbsp;</td>';
					echo '		<td>&nbsp;Galaxie&nbsp;</td>';
					echo '		<td>&nbsp;Planet&nbsp;</td>';
					echo '		<td>&nbsp;Spieler&nbsp;</td>';
					echo '		<td>&nbsp;TIC&nbsp;</td>';
					echo '		<td>&nbsp;&nbsp;</td>';
					echo '	</tr>';

					$sql1 = 'SET @proj = "'.mysql_real_escape_string($project).'", @welle = "'.mysql_real_escape_string($wave).'", @refgal = "'.$Benutzer['galaxie'].'", @refpla="'.$Benutzer['planet'].'";';
					tic_mysql_query($sql1, __FILE__, __LINE__);
					$sql2 = 'SELECT z.welle, z.atter_gal, z.atter_pla, z.dest_gal, z.dest_pla, z.fleet_id, z.kommentar, z.relative_starttick, u.name, u2.name,
								f.ja, f.bo, f.fr, f.ze, f.kr, f.sc, f.tr, f.cl, f.ca,
								s.sf1j, s.sf1b, s.sf1f, s.sf1z, s.sf1kr, s.sf1sa, s.sf1t, s.sf1ka, s.sf1su,
								s.sf2j, s.sf2b, s.sf2f, s.sf2z, s.sf2kr, s.sf2sa, s.sf2t, s.sf2ka, s.sf2su
								FROM gn4massinc_zuweisung z
								LEFT JOIN gn4massinc_fleets f ON f.project_fk = z.project_fk AND z.fleet_id = f.fleet AND z.atter_gal = f.atter_gal AND z.atter_pla = f.atter_pla
								LEFT JOIN gn4gnuser u ON u.gala = z.dest_gal AND u.planet = z.dest_pla
								LEFT JOIN gn4gnuser u2 ON u.gala = z.atter_gal AND u.planet = z.atter_pla
								LEFT JOIN gn4scans s ON s.rg = @refgal AND s.rp = @refpla AND s.type = 2
								WHERE z.project_fk = @proj AND z.welle = @welle AND CONCAT_WS(":", z.atter_gal, z.atter_pla) NOT LIKE CONCAT_WS(":", @refgal, @refpla)
									AND EXISTS(
										SELECT * FROM gn4massinc_zuweisung y WHERE y.project_fk = @proj AND y.atter_gal = @refgal AND y.atter_pla = @refpla AND y.dest_gal = z.dest_gal AND y.dest_pla = z.dest_pla)
								 ORDER BY z.welle + z.relative_starttick * 15 * 60, z.atter_gal, z.atter_pla, z.fleet_id';
					if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
					$res = tic_mysql_query($sql2, __FILE__, __LINE__);
					$color = true;
					while(list($t, $atter_g, $atter_p, $dest_g, $dest_p, $fleetid, $kommentar, $relative_start, $name, $name2,
								$fja, $fbo, $ffr, $fze, $fkr, $fsc, $ftr, $fcl, $fca,
								$sja[1], $sbo[1], $sfr[1], $sze[1], $skr[1], $ssc[1], $str[1], $scl[1], $sca[1],
								$sja[2], $sbo[2], $sfr[2], $sze[2], $skr[2], $ssc[2], $str[2], $scl[2], $sca[2]
								) = mysql_fetch_row($res)) {
						$color = !$color;
						$tickstart = (floor($t / 60 / 15) + $relative_start) * 60 * 15;
						$startstr = date('H:i:s', $tickstart + 15) . '&nbsp;<br/>&nbsp;' . date('H:i:s', $tickstart + 15 * 60 - 1);

						echo '<tr class="fieldnormallight">';
						echo '	<td>&nbsp;'.$startstr.'&nbsp;</td>';
						echo '	<td>&nbsp;'.$atter_g.'&nbsp;</td>';
						echo '	<td>&nbsp;'.$atter_p.'&nbsp;</td>';
						echo '	<td>&nbsp;#'.$fleetid.'&nbsp;</td>';
						echo '	<td>&nbsp;<b>&gt;&gt;</b>&nbsp;</td>';
						echo '	<td>&nbsp;'.$dest_g.'&nbsp;</td>';
						echo '	<td>&nbsp;'.$dest_p.'&nbsp;</td>';
						echo '	<td>&nbsp;'.$name.'&nbsp;</td>';
						echo '	<td>&nbsp;<iframe src="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&wave_askonline=1&started_g='.$atter_g.'&started_p='.$atter_p.'" style="height: 16px; width: 30px; overflow:hidden; border: 1px solid darkgray" scrolling="no"></iframe>&nbsp;</td>';
						echo '	<td>&nbsp;<iframe src="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&wave_askfleet=1&started_g='.$atter_g.'&started_p='.$atter_p.'&started_f='.$fleetid.'" style="height: 16px; width: 30px; overflow:hidden; border: 1px solid darkgray" scrolling="no"></iframe>&nbsp;</td>';
						echo '</tr>';
						echo '<tr class="fieldnormaldark" style="font-weight: bold">';
						echo '	<td bgcolor="white">&nbsp;'.$name2.'&nbsp;</td>';
						echo '	<td>&nbsp;Ja&nbsp;</td>';
						echo '	<td>&nbsp;Bo&nbsp;</td>';
						echo '	<td>&nbsp;Fr&nbsp;</td>';
						echo '	<td>&nbsp;Ze&nbsp;</td>';
						echo '	<td>&nbsp;Kr&nbsp;</td>';
						echo '	<td>&nbsp;Sc&nbsp;</td>';
						echo '	<td>&nbsp;Tr&nbsp;</td>';
						echo '	<td>&nbsp;Cl&nbsp;</td>';
						echo '	<td>&nbsp;Ca&nbsp;</td>';
						echo '</tr>';
						echo '<tr class="fieldnormallight">';
						echo '	<td bgcolor="white" align="right">&nbsp;Vorschlag:&nbsp;</td>';
						echo '	<td>&nbsp;'.($fja > 0 ? ZahlZuText($ja) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($fbo > 0 ? ZahlZuText($fbo) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($ffr > 0 ? ZahlZuText($ffr) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($fze > 0 ? ZahlZuText($fze) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($fkr > 0 ? ZahlZuText($fkr) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($fsc > 0 ? ZahlZuText($fsc) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($ftr > 0 ? ZahlZuText($ftr) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($fcl > 0 ? ZahlZuText($fcl) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($fca > 0 ? ZahlZuText($fca) : '-').'&nbsp;</td>';
						echo '</tr>';
						echo '<tr class="fieldnormallight">';
						echo '	<td bgcolor="white" align="right" style="font-weight: normal">&nbsp;Flotte:&nbsp;</td>';
						echo '	<td>&nbsp;'.($sja[$fleetid] > 0 ? ZahlZuText($sja[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($sbo[$fleetid] > 0 ? ZahlZuText($sbo[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($sfr[$fleetid] > 0 ? ZahlZuText($sfr[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($sze[$fleetid] > 0 ? ZahlZuText($sze[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($skr[$fleetid] > 0 ? ZahlZuText($skr[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($ssc[$fleetid] > 0 ? ZahlZuText($ssc[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($str[$fleetid] > 0 ? ZahlZuText($str[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($scl[$fleetid] > 0 ? ZahlZuText($scl[$fleetid]) : '-').'&nbsp;</td>';
						echo '	<td>&nbsp;'.($sca[$fleetid] > 0 ? ZahlZuText($sca[$fleetid]) : '-').'&nbsp;</td>';
						echo '</tr>';
						echo '<tr class="fieldnormaldark"><td colspan="10" style="height: 5px"></td></tr>';
					}
					echo '</table>';

					//timer
					//aprint(array($timer1, $timer2), "timer");
					echo "<script>
						window.onload = function () {
							display1 = document.querySelector('#timer1');
							display2 = document.querySelector('#timer2');";
					if($timer1 > 0) {
						echo "startTimer(" .$timer1. ", display1);";
					}
					if($timer2 > 0) {
						echo "startTimer(" .$timer2. ", display2);";
					}
					echo "};
						</script>";

					//ADMIN
					if($Benutzer['rang'] >= $Rang_GC) {
					}

					echo '</td></tr></table>';
				}//tab
			}//num wave > 0

			$donotshowdestinations = true;
		}//!empty wave

		echo '<br/>';

		if($Benutzer['rang'] >= $Rang_GC && !$donotshowdestinations) {
			//show destinations
			echo '<table class="datatable" align="center">';
			echo '<tr class="datatablehead">';
			echo '	<td colspan="11">Ziele</td>';
			echo '</tr>';

			//add dialog
			echo '<form method="post" action="main.php?modul=massinc&project='.$project.'">';
			echo '	<tr class="fieldnormaldark" style="font-weight:bold;"><td colspan="11">&nbsp;Meta hinzuf&uuml;gen&nbsp;</td></tr>';
			echo '	<tr class="fieldnormallight"><td align="left" colspan="4"><select id="metaselect">';
			$sql = "SELECT DISTINCT meta m FROM gn_spieler2 ORDER BY m";
			$res = tic_mysql_query($sql) or tic_mysql_error(__FILE__, __LINE__);
			$num = mysql_num_rows($res);
			for($i = 0; $i < $num; $i++) {
				$meta = mysql_result($res, $i, 'm');
				echo '<option value="'.$meta.'">'.$meta.'</option>';
			}
			echo '			</select></td>';
			echo '		<td align="center"><input type="button" value=">>" onclick="javascript:document.getElementById(\'meta\').value += (document.getElementById(\'metaselect\').value + \';\')"></td>';
			echo '		<td colspan="5"><textarea id="meta" name="ziel_add_meta"></textarea></td>';
			echo '		<td align="center"><input type="button" onclick="javascript:document.getElementById(\'meta\').value=\'\'" value="del"/></td>';
			echo '	</tr>';

			echo '	<tr class="fieldnormaldark" style="font-weight:bold;"><td colspan="11">&nbsp;Allianz hinzuf&uuml;gen&nbsp;</td></tr>';
			echo '	<tr class="fieldnormallight"><td align="left" colspan="4"><select id="allyselect">';
			$sql = "SELECT DISTINCT allianz_name a, meta m FROM gn_spieler2 ORDER BY a";
			$res = tic_mysql_query($sql) or tic_mysql_error(__FILE__, __LINE__);
			$num = mysql_num_rows($res);
			for($i = 0; $i < $num; $i++) {
				$meta = mysql_result($res, $i, 'm');
				$ally = mysql_result($res, $i, 'a');
				echo '<option value="'.$ally.'">'.$ally.($meta ? ' - '.$meta : '').'</option>';
			}
			echo '			</select></td>';
			echo '		<td align="center"><input type="button" value=">>" onclick="javascript:document.getElementById(\'allianz\').value += (document.getElementById(\'allyselect\').value + \';\')"></td>';
			echo '		<td colspan="5"><textarea id="allianz" name="ziel_add_allianz"></textarea></td>';
			echo '		<td align="center"><input type="button" onclick="javascript:document.getElementById(\'allianz\').value=\'\'" value="del"/></td>';
			echo '	</tr>';

			echo '	<tr class="fieldnormaldark" style="font-weight:bold;"><td colspan="11">&nbsp;Galaxie hinzuf&uuml;gen&nbsp;</td></tr>';
			echo '	<tr class="fieldnormallight"><td align="left" colspan="4"><select id="galselect">';
			$sql = "SELECT DISTINCT spieler_galaxie g, allianz_name a FROM gn_spieler2 ORDER BY g";
			$res = tic_mysql_query($sql) or tic_mysql_error(__FILE__, __LINE__);
			$num = mysql_num_rows($res);
			for($i = 0; $i < $num; $i++) {
				$gal = mysql_result($res, $i, 'g');
				$ally = mysql_result($res, $i, 'a');
				echo '<option value="'.$gal.'">'.$gal. ($ally ? ' - '.$ally : '').'</option>';
			}
			echo '			</select></td>';
			echo '		<td align="center"><input type="button" value=">>" onclick="javascript:document.getElementById(\'galaxie\').value += (document.getElementById(\'galselect\').value + \';\')"></td>';
			echo '		<td colspan="5"><textarea id="galaxie" name="ziel_add_galaxie"></textarea></td>';
			echo '		<td align="center"><input type="button" onclick="javascript:document.getElementById(\'galaxie\').value=\'\'" value="del"/></td>';
			echo '	</tr>';
			echo '	<tr class="fieldnormaldark"><td colspan="11" align="right">&nbsp;<input type="submit" name="ziele_add" value="best&auml;tigen">&nbsp;</td></tr>';
			echo '</form>';

			//VIEW
			echo '<form method="post" action="main.php?modul=massinc&project='.$project.'"><input type="hidden" name="ziele_do_del" value="1">';
			echo '<tr class="datatablehead">';
			echo '	<td>&nbsp;Meta&nbsp;</td>';
			echo '	<td>&nbsp;Ally&nbsp;</td>';
			echo '	<td>&nbsp;Galaxie&nbsp;</td>';
			echo '	<td>&nbsp;Planet&nbsp;</td>';
			echo '	<td>&nbsp;Spieler&nbsp;</td>';
			echo '	<td>&nbsp;Punkte&nbsp;</td>';
			echo '	<td>&nbsp;Exen&nbsp;</td>';
			echo '	<td>&nbsp;Schiffe&nbsp;</td>';
			echo '	<td>&nbsp;Deff&nbsp;</td>';
			echo '	<td>&nbsp;Scanblock&nbsp;</td>';
			echo '	<td>&nbsp;Scans&nbsp;</td>';
			echo '	<td>&nbsp;del&nbsp;</td>';

			$sql = "SELECT t FROM gn4massinc_wellen WHERE project_fk = '".$project."' ORDER BY t ASC";
			$res = tic_mysql_query($sql, __FILE__, __LINE__);
			$num_wellen = 0;
			$i = 0;
			while(list($t) = mysql_fetch_row($res)) {
				$i++;
				echo '	<td title="'.date('Y-m-d H:i', $t).'">&nbsp;#'.$i.'&nbsp;</td>';
				$num_wellen++;
			}

			echo '</tr>';

			$sql = "SELECT z.gal, z.pla, s.meta, s.allianz_name, s.spieler_name, s.spieler_punkte, s.spieler_urlaub, i.ke + i.me as exen, i.s, i.d
					FROM gn4massinc_ziele z
					LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = z.gal AND s.spieler_planet = z.pla
					LEFT JOIN gn4scans i ON i.rg = z.gal AND i.rp = z.pla AND i.type = 0
					WHERE z.project_fk = '".$project."' ORDER BY s.meta, s.allianz_name, z.gal, z.pla";
			if($SQL_DEBUG) aprint($sql);
			$res = tic_mysql_query($sql, __FILE__, __LINE__);
			$color = false;
			$scanlistparams = '';
			while(list($g, $p, $meta, $ally, $name, $pkt, $umode, $exen, $s, $d) = mysql_fetch_row($res)) {
				$color = !$color;
				$scanlistparams .= $g . ':' . $p . ';';
				if($umode)
					echo '<tr bgcolor="#999999" title="Urlaub">';
				else
					echo '<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
				echo '	<td>&nbsp;'.$meta.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$ally.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$g.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$p.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$name.'&nbsp;</td>';
				echo '	<td>&nbsp;'.ZahlZuText($pkt).'&nbsp;</td>';
				echo '	<td>&nbsp;'.ZahlZuText($exen).'&nbsp;</td>';
				echo '	<td>&nbsp;'.ZahlZuText($s).'&nbsp;</td>';
				echo '	<td>&nbsp;'.ZahlZuText($d).'&nbsp;</td>';
				$sql = "SELECT svs, typ FROM gn4scanblock WHERE g = '".$g."' AND p = '".$p."' AND suspicious IS NULL ORDER BY svs DESC LIMIT 1";
				$tmp = tic_mysql_query($sql, __FILE__, __LINE__);
				if(mysql_num_rows($tmp) > 0) {
					echo '<td>&nbsp;'.ZahlZuText(mysql_result($tmp, 0, 'svs')) . ' ' . scanTypeName(mysql_result($tmp, 0, 'typ'), true).'&nbsp;</td>';
				} else {
					echo '	<td>&nbsp;&nbsp;</td>';
				}
				echo '<td>&nbsp;' . Get_Scan4($SQL_DBConn, $g, $p, null, null, null) . '&nbsp;</td>';
				echo '	<td><input type="checkbox" name="ziele_del['.$g.']['.$p.']"/></td>';

				//wellen
				$sql = "SELECT z.gal, z.pla, w.t, zw.id FROM gn4massinc_ziele z
						JOIN gn4massinc_wellen w ON w.project_fk = '".$project."'
						LEFT JOIN gn4massinc_ziele_welle zw ON zw.ziel_gal = z.gal AND zw.ziel_pla = z.pla AND zw.welle = w.t
						WHERE z.gal = '".$g."' AND z.pla = '".$p."'";
				if($SQL_DEBUG) aprint($sql);
				$res2 = tic_mysql_query($sql, __FILE__, __LINE__);
				while(list($g, $p, $t, $id) = mysql_fetch_row($res2)) {
					echo '<td>&nbsp;<input type="checkbox" name="ziel_welle['.$g.']['.$p.']['.$t.']" '.($id ? ' checked="checked"' : '').'/>&nbsp;</td>';
				}


				echo '</tr>';
			}

 			$short = addShortUrl('main.php?modul=scanliste&koords=' . $scanlistparams);
			echo '<tr class="fieldnormaldark"><td colspan="11" align="right">&nbsp;' . createCopyLink('&raquo; Scanlistenlink kopieren', $short) . ' | <a href="' . $short . '">&raquo; Scanliste</a>&nbsp;</td><td>&nbsp;<input type="submit" value="del">&nbsp;</td>';
			echo '<td colspan="'.$num_wellen.'">&nbsp;<input type="submit" name="ziel_welle_update" value="speichern"/>&nbsp;</td>';
			echo '</tr>';
			echo '</table>';
			echo '</form>';
		}//if GC show destinations

		echo '</td></tr></table>';
	}//num project > 0
}//!empty project

?>
