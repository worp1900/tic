<h2>Angriffsplanung 2.0</h2>
<!--<p style="background-color: rgb(200, 200, 0)">Dieser Bereich befindet sich noch in Entwicklung. /dv</p>-->
<?php
//$Benutzer['rang'] = 0;

function createSimuLink($project, $wave, $gal, $pla, $linkName) {
	global $SQL_DBConn;
	
	$link = '';
	//deffer
	$link .= '&g[0]='.$gal.'&p[0]='.$pla;
	$sql1 = "SET @project = '".$project."',
					@welle = '".$wave."',
					@gal = '".mysql_real_escape_string($koords_g)."',
					@pla = '".mysql_real_escape_string($koords_p)."'";
	$sql2 = "SELECT 
			s0.me, s0.ke,
			s3.ga, s3.glo, s3.glr, s3.gmr, s3.gsr,
			s1.sfj, s1.sfb, s1.sff, s1.sfz, s1.sfkr, s1.sfsa, s1.sft, s1.sfka, s1.sfsu
		FROM gn4massinc_ziele_welle zw
		LEFT JOIN gn4scans s0 ON s0.rg = zw.ziel_gal AND s0.rp = zw.ziel_pla AND s0.type = 0
		LEFT JOIN gn4scans s1 ON s1.rg = zw.ziel_gal AND s1.rp = zw.ziel_pla AND s1.type = 1
		LEFT JOIN gn4scans s3 ON s3.rg = zw.ziel_gal AND s3.rp = zw.ziel_pla AND s3.type = 3";
	if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)), "victum info");
	tic_mysql_query($sql1, __FILE__, __LINE__);
	list($me, $ke,
		$gaj, $glo, $glr, $gmr, $gsr, 
		$fja, $fbo, $ffr, $fze, $fkr, $fsc, $ftr, $fcl, $fca) = mysql_fetch_row(tic_mysql_query($sql2, __FILE__, __LINE__));
	$link .= '&d[0][0]=' . $fja . '&d[0][1]=' . $fbo . '&d[0][2]=' . $ffr . '&d[0][3]=' . $fze . '&d[0][4]=' . $fkr . '&d[0][5]=' . $fsc . '&d[0][6]=' . $ftr . '&d[0][7]=' . $fcl . '&d[0][8]=' . $fca;
	$link .= '&d[0][9]=' . $gaj . '&d[0][10]=' . $glo . '&d[0][11]=' . $glr . '&d[0][12]=' . $gmr . '&d[0][13]=' . $gsr;
	$link .= '&d[0][14]=' . $me . '&d[0][15]=' . $ke;
	
	//atter
	$sql1 = "SET @project = '".$project."',
				@welle = '".$wave."',
				@gal = '".$gal."',
				@pla = '".$pla."'";
	$sql2 = "SELECT z.atter_gal, z.atter_pla, z.fleet_id, z.relative_starttick,
				f.ja, f.bo, f.fr, f.ze, f.kr, f.sc, f.tr, f.cl, f.ca,
				i.me, i.ke
			FROM gn4massinc_zuweisung z
			JOIN gn4massinc_fleets f ON f.project_fk = z.project_fk AND f.atter_gal = z.atter_gal AND f.atter_pla = z.atter_pla AND z.fleet_id = f.fleet
			LEFT JOIN gn4scans i ON i.rg = z.atter_gal AND i.rp = z.atter_pla AND i.type = 0
			WHERE z.project_fk = @project AND z.welle = @welle AND z.dest_gal = @gal AND z.dest_pla = @pla
			ORDER BY z.atter_gal, z.atter_pla, z.relative_starttick, z.fleet_id";
	if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)));
	tic_mysql_query($sql1, __FILE__, __LINE__);
	$res = tic_mysql_query($sql2, __FILE__, __LINE__);
	$num = mysql_num_rows($res);
	

	$offset = 1;
	//atter link
	$max_starttick = 1;
	while(list($g, $p, $fleetid, $relative_starttick, $fja, $fbo, $bfr, $fzr, $fkr, $fsc, $ftr, $fcl, $fca, $me, $ke) = mysql_fetch_row($res)) {
		$relative_starttick++; //the simulator starts at 1
		$link .= '&g['.($i+$offset).']='.$g.'&p['.($i+$offset).']='.$p.'&typ['.($i+$offset).']=a&f['.($i+$offset).']='.$fleetid.'&ankunft['.($i+$offset).']='.$relative_starttick.'&aufenthalt['.($i+$offset).']=5';
		$link .= '&d['.($i+$offset).'][0]=' . $fja . '&d['.($i+$offset).'][1]=' . $fbo . '&d['.($i+$offset).'][2]=' . $ffr . '&d['.($i+$offset).'][3]=' . $fze . '&d['.($i+$offset).'][4]=' . $fkr . '&d['.($i+$offset).'][5]=' . $fsc . '&d['.($i+$offset).'][6]=' . $ftr . '&d['.($i+$offset).'][7]=' . $fcl . '&d['.($i+$offset).'][8]=' . $fca;
		$link .= '&d['.($i+$offset).'][14]=' . $me . '&d['.($i+$offset).'][15]=' . $ke;
		$i++;
		$max_starttick = ($relative_starttick > $max_starttick) ? $relative_starttick : $max_starttick;
	}

	return '<a href="main.php?modul=kampf&compute=Berechnen&preticks=1&ticks='.($max_starttick - 1 + 5).'&num_flotten='.($num + $offset - 1).$link.'#overview" target="_blank">'.$linkName.'</a>';
}


//$Benutzer['rang'] = 1;

$refresh = 20;

function getOnlineStatus($g, $p, $thresh = 300) {
	global $SQL_DBConn;
	$lastlogin = mysql_result(tic_mysql_query("SELECT lastlogin FROM gn4accounts WHERE galaxie = '".mysql_real_escape_string($g)."' AND planet = '".mysql_real_escape_string($p)."'", __FILE__, __LINE__), 0, 0);
	return time() - $lastlogin < $thresh;
}

function showError($msg) {
	echo '<p><b>Fehler: ' . $msg . '</b>. <a href="javascript:history.back();">&raquo; zur&uuml;ck</a></p>';
}




$SQL_DEBUG = false;
//general vars
$project = postOrGet('project');

function isUserCreator($project, $id) {
	global $SQL_DBConn;
	$sql = 'SELECT * FROM gn4massinc_projects WHERE project_id = "'.mysql_real_escape_string($project).'" AND erstellt_von = "'.mysql_real_escape_string($id).'"';
	$res = tic_mysql_query($sql);
	return mysql_num_rows($res);
}

//project mgmt
$proj_edit;
$proj_del;
$proj_neu;

//aprint(array('POST' => $_POST, 'GET' => $_GET,));

//for all.
if(postOrGet('getical')) {
	ob_end_clean();
	ob_start();
	
	$project = postOrGet('project');
	$wave = postOrGet('wave');
	
	$sql1 = 'SET @proj = "'.mysql_real_escape_string($project).'", @welle = "'.mysql_real_escape_string($wave).'", @refgal = "'.$Benutzer['galaxie'].'", @refpla="'.$Benutzer['planet'].'";';
	tic_mysql_query($sql1, __FILE__, __LINE__);
	$sql2 = 'SELECT w.t, z.dest_gal, z.dest_pla, z.fleet_id, z.kommentar, z.relative_starttick, u.spieler_name, u.spieler_punkte, u.allianz_name
			FROM gn4massinc_zuweisung z
			LEFT JOIN gn4massinc_wellen w ON w.project_fk = z.project_fk AND z.welle = w.id
			LEFT JOIN gn4massinc_fleets f ON f.project_fk = z.project_fk AND z.fleet_id = f.fleet AND z.atter_gal = f.atter_gal AND z.atter_pla = f.atter_pla
			LEFT JOIN gn_spieler2 u ON u.spieler_galaxie = z.dest_gal AND u.spieler_planet = z.dest_pla
			LEFT JOIN gn4scans s ON s.rg = @refgal AND s.rp = @refpla AND s.type = 2
			WHERE z.project_fk = @proj AND z.welle = @welle AND z.atter_gal = @refgal AND z.atter_pla = @refpla ORDER BY z.welle + z.relative_starttick * 15 * 60';
	if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
	$res = tic_mysql_query($sql2, __FILE__, __LINE__);
	$color = true;

	$fleets = null;
	
	//header
	header('Content-Description: File Transfer');
	header('Content-Disposition: attachment; filename="GN_att'.time().'.ics"');
	header('Content-Type: text/calendar');
	
	echo 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:http://gntic.de
X-WR-CALNAME:Galaxy Network
BEGIN:VTIMEZONE
TZID:Europe/Berlin
X-LIC-LOCATION:Europe/Berlin
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
END:STANDARD
END:VTIMEZONE'."\r\n";
	
	while(list($t, $dest_g, $dest_p, $fleetid, $kommentar, $relative_start, $name, $pkt, $ally
				) = mysql_fetch_row($res)) {
		echo 'BEGIN:VEVENT
LOCATION:https://gntic.de/tic/main.php?modul=massinc&project='.$project.'&wave='.$wave.'&besoffski=2
SUMMARY:[GN] Angriff Fleet #'.$fleetid.' auf '.$dest_g.':'.$dest_p.' '.$name.' ('.$kommentar.')
DESCRIPTION:Zielinfo: https://gntic.de/tic/main.php?modul=massinc&project='.$project.'&wave='.$wave.'&tab_ziele=1
CLASS:PUBLIC
DTSTART;TZID=Europe/Berlin:'.date("Ymd\THis", $t + $relative_start * 15 * 60).'
DTEND;TZID=Europe/Berlin:'.date("Ymd\THis", $t + $relative_start * 15 * 60 + 7 * 60).'
DTSTAMP:'.date("Ymd\THis").'
BEGIN:VALARM
TRIGGER:-PT15M
REPEAT:1
DURATION:PT15M
ACTION:DISPLAY
DESCRIPTION:Reminder
END:VALARM
END:VEVENT'."\r\n";
	}
	
	echo 'END:VCALENDAR'."\r\n".'';
	
	ob_flush();
	exit();
}

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

	$sql = "SELECT COALESCE(b.ankunft = (floor(w.t / 15 / 60) + z.relative_starttick + 30) * 15 * 60, -1) AS ok
			FROM gn4massinc_zuweisung z
			JOIN gn4massinc_wellen w ON w.id = z.welle AND w.project_fk = z.project_fk
			LEFT JOIN gn4flottenbewegungen b
			ON b.angreifer_galaxie = z.atter_gal
				AND b.angreifer_planet = z.atter_pla
				AND b.verteidiger_galaxie = z.dest_gal
				AND b.verteidiger_planet = z.dest_pla
				AND b.modus = 1
				AND b.flottennr = z.fleet_id
				AND ABS(b.ankunft - (floor(w.t / 15 / 60) + z.relative_starttick + 30) * 15 * 60) < 2 * 60 * 15
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
padding: 0; font-family: helvetica; font-size: 9pt; font-weight: bold; text-align: center; background-color: '.($started == 1 ? '55ff55' : ($started == -1 ? '#ff5555' : '#ffff55')).'">'.($started == 1 ? 'JA' : ($started == -1 ? 'NEIN' : 'Jein')).'</body></html>';
	ob_flush();
	exit();
}


if(postOrGet('start_f')) {
	$f = postOrGet('start_f');
	$sql1 = "SET @g = '".mysql_real_escape_string($Benutzer['galaxie'])."', @p = '".mysql_real_escape_string($Benutzer['planet'])."', @f = '".mysql_real_escape_string($f)."';";
	$sql2 = "SELECT zw.atter_gal, zw.atter_pla, zw.dest_gal, zw.dest_pla, zw.fleet_id, zw.welle, w.t, zw.relative_starttick
			FROM gn4massinc_zuweisung zw
			JOIN gn4massinc_wellen w ON w.project_fk = zw.project_fk AND w.id = zw.welle
			WHERE atter_gal = @g AND atter_pla = @p AND fleet_id = @f";
	if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
	tic_mysql_query($sql1, __FILE__, __LINE__);
	$res = tic_mysql_query($sql2, __FILE__, __LINE__);
	$num = mysql_num_rows($res);

	if($num == 1) {
		list($atter_gal, $atter_pla, $dest_gal, $dest_pla, $fleet_id, $welle, $t, $relative_starttick) = mysql_fetch_row($res);
		$t = floor($t / 60 / 15);
		//valid, now create fleet.
		$ankunft = ($t + $relative_starttick + 30) * 15 * 60;
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

if(postOrGet('edit_welle_user_willing_g') && postOrGet('edit_welle_user_willing_p') && postOrGet('edit_welle_user_willing_id')) {
	$id = postOrGet('edit_welle_user_willing_id');
	$state = postOrGet('edit_welle_user_willing');
	$g = postOrGet('edit_welle_user_willing_g');
	$p = postOrGet('edit_welle_user_willing_p');

	$freigabestatus = mysql_result(tic_mysql_query("SELECT freigegeben FROM gn4massinc_projects WHERE project_id = '".mysql_real_escape_string($project)."'", __FILE__, __LINE__), 0, 0);

	if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id']) || $g == $Benutzer['galaxie'] && $p == $Benutzer['planet'] && $freigabestatus == 1) {
		$sql1 = "SET @project = '".mysql_real_escape_string($project)."',
					@welle = '".mysql_real_escape_string($id)."',
					@atter_gal = '".mysql_real_escape_string($g)."',
					@atter_pla = '".mysql_real_escape_string($p)."',
					@atter_fleets = '".mysql_real_escape_string($Benutzer['offfleets'])."'";
		if(!$state) {
			$sql2 = "INSERT INTO gn4massinc_atter_willing (project_fk, welle, atter_gal, atter_pla, willing)
					VALUES(@project, @welle, @atter_gal, @atter_pla, 0)
					ON DUPLICATE KEY UPDATE willing = 0";
		} else {
			$sql2 = "INSERT INTO gn4massinc_atter_willing (project_fk, welle, atter_gal, atter_pla, willing)
					VALUES(@project, @welle, @atter_gal, @atter_pla, 1)
					ON DUPLICATE KEY UPDATE willing = 1";
		}
		$sql3 = "INSERT INTO gn4massinc_atter (project_fk, gal, pla, off_fleets) VALUES (@project, @atter_gal, @atter_pla, @atter_fleets)
			 ON DUPLICATE KEY UPDATE off_fleets=@atter_fleets;";
		
		if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2, $sql2)));
		tic_mysql_query($sql1, __FILE__, __LINE__);
		tic_mysql_query($sql2, __FILE__, __LINE__);
		tic_mysql_query($sql3, __FILE__, __LINE__);
	}
}

if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
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
		$sql = "INSERT INTO gn4massinc_projects (erstellt_von) VALUES ('".mysql_real_escape_string($Benutzer['id'])."')";
		if($SQL_DEBUG) aprint($sql);
		tic_mysql_query($sql, __FILE__, __LINE__);
	}
	if($proj_edit && $proj_edit_ack) {
		$proj_edit_name = postOrGet('proj_edit_name');
		$proj_edit_freigabe = postOrGet('proj_edit_freigabe');
		$proj_edit_zielwahl = postOrGet('proj_edit_zielwahl');
		$sql = "UPDATE gn4massinc_projects SET name = '".mysql_real_escape_string($proj_edit_name)."', freigegeben = '".mysql_real_escape_string($proj_edit_freigabe)."', freie_zielwahl = '".$proj_edit_zielwahl."' WHERE project_id = '".mysql_real_escape_string($proj_edit)."'";
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
			$sql = "DELETE FROM gn4massinc_wellen WHERE project_fk = '" . mysql_real_escape_string($project) . "' AND id = '".mysql_real_escape_string($wave_del)."'";
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
			$wave_edit_kommentar = postOrGet('wave_edit_kommentar');
			$sql = "UPDATE gn4massinc_wellen SET t = UNIX_TIMESTAMP(STR_TO_DATE('".mysql_real_escape_string($wave_edit_t)."', '%Y-%m-%d %H:%i')), kommentar = '".$wave_edit_kommentar."' WHERE project_fk = '".mysql_real_escape_string($project)."' AND id = '".$wave_edit."'";
			if($SQL_DEBUG) aprint($sql);
			tic_mysql_query($sql, __FILE__, __LINE__);
			$wave_edit = null;
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
			display.textContent = "-";
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
	if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
		echo '	<td colspan="9">Projekte</td>';
	} else {
		echo '	<td colspan="7">Projekte</td>';
	}
	echo '</tr>';
	echo '<tr class="fieldnormaldark" style="font-weight: bold">';
	echo '	<td>&nbsp;ID&nbsp;</td>';
	echo '	<td>&nbsp;Name&nbsp;</td>';
	echo '	<td>&nbsp;Ersteller&nbsp;</td>';
	echo '	<td>&nbsp;Datum&nbsp;</td>';
	echo '	<td>&nbsp;Freigabe&nbsp;</td>';
	echo '	<td>&nbsp;Zielauswahl&nbsp;</td>';
	if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
		echo '	<td>&nbsp;&nbsp;</td>';
	}
	echo '	<td>&nbsp;&nbsp;</td>';
	echo '</tr>';

	$sql = "SELECT p.project_id, p.name, p.freigegeben, u.name, p.erstellt_am, p.freie_zielwahl FROM gn4massinc_projects p LEFT JOIN gn4accounts u ON u.id = p.erstellt_von ORDER BY p.project_id";
	if($SQL_DEBUG) aprint($sql);
	$res = tic_mysql_query($sql, __FILE__, __LINE__);
	$num = mysql_num_rows($res);

	$color = false;
	$i = 0;
	while(list($project_id, $name, $freigegeben, $erstellt_von, $erstellt_am, $wahl) = mysql_fetch_row($res)) {
		$color = !$color;
		if($freigegeben > 0 || $Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
			$i++;
			if($proj_edit == $project_id && ($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id']))) {
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
				echo '			<option value="-1"'.($freigegeben == -1 ? ' selected="selected"' : '').'>ausgeblendet</option>';
				echo '		</select>&nbsp;</td>';
				echo '	<td>&nbsp;<select name="proj_edit_zielwahl">';
				echo '			<option value="'.(!$wahl ? ' selected="selected"' : '').'">Organisator</option>';
				echo '			<option value="1"'.($wahl ? ' selected="selected"' : '').'>frei</option>';
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
					case 0: echo '<span title="Nicht sichtbar f&uuml;r regul&auml;re Mitglieder">nicht freigegeben</span>'; break;
					case 1: echo '<span title="F&uuml;r alle sichtbar, Spieler tragen ein, ob Sie mitmachen m&ouml;chten">Flotten-Checkin</span>'; break;
					case 2: echo '<span title="Planung abgeschlossen">freigegeben</span>'; break;
					case -1: echo '<span title="Beendet">ausgeblendet</span>'; break;
				}
				echo '&nbsp;</td>';
				echo '	<td>&nbsp;'.($wahl ? 'frei' : 'Organisator').'&nbsp;</td>';
				if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
					echo '	<td>&nbsp;<a href="main.php?modul=massinc&proj_edit='.$project_id.'">&raquo; editieren</a>&nbsp;<br/>';
					echo '	&nbsp;<a href="main.php?modul=massinc&proj_del='.$project_id.'" onclick="return confirm(\'Bist Du Dir sicher?\')">&raquo; l&ouml;schen</a>&nbsp;</td>';
				}
				echo '	<td>&nbsp;<a href="main.php?modul=massinc&project='.$project_id.'">&raquo; ausw&auml;hlen</a>&nbsp;</td>';
				echo '</tr>';
			}
		}
	}

	if($i == 0) {
		echo '<tr class="fieldnormallight"><td colspan="8" align="center">Keine Eintr&auml;ge vorhanden.</td></tr>';
	}

	if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
		echo '<tr class="fieldnormaldark" style="font-weight: bold;">';
			echo '<td colspan="8" align="right"><a href="main.php?modul=massinc&proj_neu=1">&raquo; neu erstellen</a></td>';
		echo '</tr>';
	}
	
	echo '</table>';


	//show waves without confirmation
	echo '<br/><table class="datatable" align="center" width="100%">';
	echo '<tr class="datatablehead">';
	echo '	<td colspan="4">M&ouml;chtest Du mitmachen?</td>';
	echo '</tr>';
	echo '<tr class="fieldnormaldark" style="font-weight: bold">';
	echo '	<td>&nbsp;Projekt&nbsp;</td>';
	echo '	<td>&nbsp;Welle&nbsp;</td>';
	echo '	<td>&nbsp;Entscheidung&nbsp;</td>';
	echo '	<td>&nbsp;&nbsp;</td>';
	echo '</tr>';
	$sql1 = "SET @g = '".mysql_real_escape_string($Benutzer['galaxie'])."', @p = '".mysql_real_escape_string($Benutzer['planet'])."'";
	$sql2 = "SELECT w.project_fk, p.name, w.t, a.willing, w.id, p.freie_zielwahl
				FROM gn4massinc_wellen w
				LEFT JOIN gn4massinc_projects p ON p.project_id = w.project_fk
				LEFT JOIN gn4massinc_atter_willing a ON a.project_fk = w.project_fk AND a.welle = w.id AND a.atter_gal = @g AND a.atter_pla = @p
				WHERE (SELECT freigegeben FROM gn4massinc_projects WHERE project_id = w.project_fk) > 0
				AND w.t > UNIX_TIMESTAMP()
				ORDER BY w.t";
	if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
	tic_mysql_query($sql1, __FILE__, __LINE__);
	$res = tic_mysql_query($sql2, __FILE__, __LINE__);
	$color = true;
	$i = 0;
	while(list($project_id, $project_name, $welle, $willing, $welle_id, $freie_zielwahl) = mysql_fetch_row($res)) {
		$i++;
		$color = !$color;
		echo '<tr class="fieldnormal'.($color ? 'dark' : 'light').'">';
		echo '	<td>&nbsp;'.$project_name.'&nbsp;</td>';
		echo '	<td>&nbsp;'.date('Y-m-d H:i', $welle).'&nbsp;</td>';
		echo '	<td'.(is_null($willing) ? ' bgcolor="#ffffaa"' : '').'>&nbsp;';
		if(is_null($willing)) {
			echo 'bitte eintragen:&nbsp;<br/>';
			echo '&nbsp;<a href="main.php?modul=massinc&project='.$project_id.'&edit_welle_user_willing_id='.$welle_id.'&edit_welle_user_willing=1&edit_welle_user_willing_g='.$Benutzer['galaxie'].'&edit_welle_user_willing_p='.$Benutzer['planet'].'">JA</a>';
			echo ' / ';
			echo '<a href="main.php?modul=massinc&project='.$project_id.'&edit_welle_user_willing_id='.$welle_id.'&edit_welle_user_willing=0&edit_welle_user_willing_g='.$Benutzer['galaxie'].'&edit_welle_user_willing_p='.$Benutzer['planet'].'">Nein</a>';
		} else if($willing) {
			echo 'JA&nbsp;';
			echo '<br>';
			echo '&nbsp;<a title="Bitte melde Dich bei dem Organisator, falls Du doch nicht teilnehmen kannst." onclick="return confirm(\'Beachte: Bestehende Flottenzuweisungen werden nicht gel&ouml;scht. Bitte kontaktiere den Organisator!\')" href="main.php?modul=massinc&project='.$project_id.'&edit_welle_user_willing_id='.$welle_id.'&edit_welle_user_willing=0&edit_welle_user_willing_g='.$Benutzer['galaxie'].'&edit_welle_user_willing_p='.$Benutzer['planet'].'">Doch nicht</a>';
			if($freie_zielwahl == 1) echo '&nbsp;<br/>&nbsp;<a href="main.php?modul=massinc&project='.$project_id.'&wave='.$welle_id.'&zuweisung=2" style="font-weight: bold;">&raquo; Ziel w&auml;hlen</a>';
		} else {
			echo 'Nein&nbsp;';
			echo '<br>';
			echo '&nbsp;<a href="main.php?modul=massinc&project='.$project_id.'&edit_welle_user_willing_id='.$welle_id.'&edit_welle_user_willing=1&edit_welle_user_willing_g='.$Benutzer['galaxie'].'&edit_welle_user_willing_p='.$Benutzer['planet'].'">Doch!</a>';
		}
		echo '&nbsp;</td>';
		echo '	<td>&nbsp;<a href="main.php?modul=massinc&project='.$project_id.'">&raquo; hier w&auml;hlen</a>&nbsp;</td>';
		echo '</tr>';
	}
	if($i == 0) {
		echo '<tr class="fieldnormallight"><td colspan="4">&nbsp;Es liegen keine weiteren Entscheidungen an.&nbsp;</td></tr>';
	}
	echo '</table>';


	//show waves without confirmation
	echo '<br/><table class="datatable" align="center" width="100%">';
	echo '<tr class="datatablehead">';
	echo '	<td colspan="4">Deine zugeteilten Aktionen</td>';
	echo '</tr">';
	echo '<tr class="fieldnormaldark" style="font-weight: bold">';
	echo '	<td>&nbsp;Projekt&nbsp;</td>';
	echo '	<td>&nbsp;Welle&nbsp;</td>';
	echo '	<td>&nbsp;Minuten bis Start&nbsp;</td>';
	echo '	<td>&nbsp;&nbsp;</td>';
	echo '</tr>';
	$sql1 = "SET @g = '".mysql_real_escape_string($Benutzer['galaxie'])."', @p = '".mysql_real_escape_string($Benutzer['planet'])."'";
	$sql2 = "SELECT DISTINCT z.project_fk, p.name, z.welle, w.t, p.freigegeben
				FROM gn4massinc_zuweisung z
				LEFT JOIN gn4massinc_projects p ON p.project_id = z.project_fk
				LEFT JOIN gn4massinc_wellen w ON w.project_fk = z.project_fk AND w.id = z.welle
				WHERE z.atter_gal = @g AND z.atter_pla = @p AND p.freigegeben > 0
				ORDER BY z.welle";
	if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)));
	tic_mysql_query($sql1, __FILE__, __LINE__);
	$res = tic_mysql_query($sql2, __FILE__, __LINE__);
	$color = false;
	$i = 0;
	while(list($project_id, $project_name, $welle, $t, $freigabe) = mysql_fetch_row($res)) {
		$i++;
		$color = !$color;
		echo '<tr bgcolor="#'.($color ? 'ffffaa' : 'dddd99').'" '.($freigabe < 2 ? ' style="font-style: italic;" title="Beachte: Die Zuweisung kann sich noch &auml;ndern, da das Projekt nocht nicht freigegeben wurde!"' : '').'>';
		echo '	<td>&nbsp;'.$project_name.'&nbsp;</td>';
		echo '	<td>&nbsp;'.date('Y-m-d H:i', $t).'&nbsp;</td>';
		echo '	<td>&nbsp;'.ZahlZuText(($t - time()) / 60).'&nbsp;</td>';
		echo '	<td style="background-color: red; font-weight: bold;">&nbsp;<a href="main.php?modul=massinc&project='.$project_id.'&wave='.$welle.'&besoffski=2" style="color: white; text-decoration: underline;">&raquo; zum Cockpit</a>&nbsp;</td>';
		echo '</tr>';
	}
	if($i == 0) {
		echo '<tr class="fieldnormallight"><td colspan="4">&nbsp;Es liegen keine weiteren Entscheidungen an.&nbsp;</td></tr>';
	}
	echo '</table>';

} else {
	if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
		$status = postOrGet('status');
		if(!empty($status) && ($status == 0 ||$status == 1 || $status == 2 || $status == -1)) {
			$sql1 = 'SET @project = "'.$project.'", @status = "'.$status.'";';
			$sql2 = "UPDATE gn4massinc_projects SET freigegeben = @status WHERE project_id = @project";
			if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
			tic_mysql_query($sql1, __FILE__, __LINE__);
			tic_mysql_query($sql2, __FILE__, __LINE__);
		}
	}
	
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
		echo '	<td>&nbsp;<a href="main.php?modul=massinc">&laquo; zur&uuml;ck</a>&nbsp;</td>';
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
			if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
				echo '<table class="datatable" align="center" width="100%">';
				echo '<tr class="datatablehead">';
				echo '<td'.($freigegeben == 0 ? ' bgcolor="red"' : '').'>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&status=0" target="_self">&raquo; nicht freigegeben</a>&nbsp;</td>';
				echo '<td'.($freigegeben == 1 ? ' bgcolor="red"' : '').'>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&status=1" target="_self">&raquo; Flotten Checkin</a>&nbsp;</td>';
				echo '<td'.($freigegeben == 2 ? ' bgcolor="red"' : '').'>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&status=2" target="_self">&raquo; freigegeben</a>&nbsp;</td>';
				echo '<td'.($freigegeben == -1 ? ' bgcolor="red"' : '').'>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&status=-1" target="_self">&raquo; ausgeblendet</a>&nbsp;</td>';
				echo '</tr>';
				echo '</table><br/>';
			}
			echo '<table class="datatable" align="center" width="100%">';
			echo '<tr class="datatablehead">';
			if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
				echo '	<td colspan="9">Wellen</td>';
			} else {
				echo '	<td colspan="6">Wellen</td>';
			}
			echo '</tr>';
			echo '<tr class="fieldnormaldark" style="font-weight: bold">';
			echo '	<td>&nbsp;#&nbsp;</td>';
			echo '	<td>&nbsp;Sart&nbsp;</td>';
			echo '	<td>&nbsp;Kommentar&nbsp;</td>';
			if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
				echo '	<td>&nbsp;&nbsp;</td>';
			}
			echo '	<td>&nbsp;Habe Zeit&nbsp;</td>';
			echo '	<td>&nbsp;Zugeteilt&nbsp;</td>';
			if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
				echo '	<td>&nbsp;#Zeit Spieler&nbsp;<br/>&nbsp;(Flotten)&nbsp;</td>';
				echo '	<td>&nbsp;#zugewiesen Spieler&nbsp;<br/>&nbsp;(Flotten)&nbsp;</td>';
			}
			echo '	<td>&nbsp;&nbsp;</td>';
			echo '</tr>';

			$sql1 = 'SET @project = "'.$project.'"';
			$sql2 = "SELECT
						w.id,
						w.t,
						w.kommentar,
						COUNT(z.project_fk) num_flotten_zugewiesen,
						(SELECT COUNT(DISTINCT(CONCAT_WS(':', atter_gal, atter_pla)))
							FROM gn4massinc_zuweisung x
							WHERE x.project_fk = @project AND x.welle = w.id
							) num_spieler_zugewiesen,
						(SELECT COALESCE(SUM(s.off_fleets), 0)
							FROM gn4massinc_atter_willing a
							LEFT JOIN gn4massinc_atter s
							ON s.gal = a.atter_gal AND s.pla = a.atter_pla
							WHERE a.willing = 1 AND a.project_fk = @project AND a.welle = w.id AND a.willing = 1) zeit_fleets,
						(SELECT COUNT(a.project_fk)
                         	FROM gn4massinc_atter_willing a
							LEFT JOIN gn4massinc_atter s
							ON s.gal = a.atter_gal AND s.pla = a.atter_pla
							WHERE a.willing = 1 AND a.project_fk = @project AND a.welle = w.id AND a.willing = 1) zeit_spieler,
						(SELECT COUNT(DISTINCT(CONCAT_WS(';', a.atter_gal, a.atter_pla)))
							FROM gn4massinc_atter_willing a
							WHERE a.willing = 1 AND a.project_fk = @project) distinct_spieler,
						(SELECT sum(x.off_fleets)
							FROM (
								SELECT DISTINCT(CONCAT_WS(':', w.atter_gal, w.atter_pla)), a.off_fleets
								FROM gn4massinc_atter_willing w
								LEFT JOIN gn4massinc_atter a ON w.atter_gal = a.gal AND w.atter_pla = a.pla
								WHERE w.willing = 1 AND w.project_fk = @project
							) x) distinct_fleets
					FROM gn4massinc_wellen w
					LEFT JOIN gn4massinc_zuweisung z ON z.project_fk = w.project_fk AND z.welle = w.id
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
			while(list($id, $t, $kommentar, $fleets_zugewiesen, $spieler_zugewiesen, $zeit_fleets, $zeit_spieler, $d_spieler, $d_fleets) = mysql_fetch_row($res)) {
				$distinct_spieler = $d_spieler;
				$distinct_fleets = $d_fleets;
				$i++;
				$color = !$color;
				if($freigegeben > 0 || $Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
					if($wave_edit == $id && ($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id']))) {
						echo '<form method="post" action="main.php?modul=massinc&project='.$project.'">';
						echo '<input type="hidden" name="wave_edit" value="'.$id.'"/>';
						echo '<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
						echo '	<td valign="top">&nbsp;'.$i.'&nbsp;</td>';
						echo '	<td valign="top">&nbsp;<input type="text" name="wave_edit_t" value="'.date('Y-m-d H:i', $t).'"/> <span title="Beachte: Datum/Zeit nach Freigabe &auml;ndern wird im Allgemeinen &Auml;rger geben.">(!)</span>&nbsp;</td>';
						echo '	<td valign="top" colspan="4">&nbsp;<textarea name="wave_edit_kommentar">'.$kommentar.'</textarea>&nbsp;</td>';
						echo '	<td valign="top" colspan="3">&nbsp;<input type="submit" name="wave_edit_ack" value="absenden"/>&nbsp;</td>';
						echo '</tr>';
						echo '</form>';
					} else {
						echo '<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
						echo '	<td>&nbsp;'.$i.'&nbsp;</td>';
						echo '	<td>&nbsp;'.date('Y-m-d H:i', $t).'&nbsp;</td>';
						echo '	<td>&nbsp;'.$kommentar.'&nbsp;</td>';
						if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
							echo '	<td>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave_edit='.$id.'">&raquo; editieren</a>&nbsp;<br/>';
							echo '	&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave_del='.$id.'" onclick="return confirm(\'Bist Du Dir sicher?\')">&raquo; l&ouml;schen</a>&nbsp;</td>';
						}

						$sql1 = "SET @project = '".mysql_real_escape_string($project)."',
									@welle = '".mysql_real_escape_string($id)."',
									@atter_gal = '".mysql_real_escape_string($Benutzer['galaxie'])."',
									@atter_pla = '".mysql_real_escape_string($Benutzer['planet'])."';";
						$sql2 = "SELECT w.willing, (NOT zw.project_fk IS NULL) AND w.willing = 0 as error, zw.project_fk > 0 as zugewiesen
								FROM gn4massinc_atter_willing w 
								LEFT JOIN gn4massinc_zuweisung zw ON zw.project_fk = w.project_fk AND zw.welle = w.welle AND zw.atter_gal = @atter_gal AND zw.atter_pla = @pla
								WHERE w.project_fk=@project AND w.welle=@welle AND w.atter_gal=@atter_gal AND w.atter_pla=@atter_pla;";
						if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$res2 = tic_mysql_query($sql2, __FILE__, __LINE__);
						$habeZeit = null;
						$error = null;
						$zugewiesen = false;
						if(mysql_num_rows($res2) > 0) {
							$habeZeit = mysql_result($res2, 0, 'willing');
							$error = mysql_result($res2, 0, 'error');
							$zugewiesen = mysql_result($res2, 0, 'zugewiesen');
						}

						if(is_null($habeZeit)) {
							echo '	<td bgcolor="#ffffaa">&nbsp;<b>Bitte eintragen!</b><br/>';
						} else if($habeZeit == 0) {
							echo '	<td'.($error ? ' bgcolor="red" title="Du wurdest bereits zugewiesen. Bitte benachrichtige den Organisator!"' : '').'>&nbsp;NEIN<br/>';
						} else {
							echo '	<td>&nbsp;JA<br/>';
						}
						if($freigegeben == 1) {
							echo '<a '.($error ? 'style="background-color: lightred" title="Du wurdest bereits zugewiesen!"' : '').' href="main.php?modul=massinc&project='.$project.'&edit_welle_user_willing_id='.$id.'&edit_welle_user_willing='.($habeZeit ? 0 : 1).'&edit_welle_user_willing_g='.$Benutzer['galaxie'].'&edit_welle_user_willing_p='.$Benutzer['planet'].'"'.($zugewiesen ? ' onclick="return confirm(\'Du wurdest bereits zugewiesen; Bist Du Dir sicher? - Benachrichte ggf. den Organisator!\')"' : '').'>&raquo; &auml;ndern</a>';
						}
						echo '</td>';

						$sql = "SELECT count(*) > 0 FROM gn4massinc_zuweisung WHERE project_fk='".mysql_real_escape_string($project)."' AND welle='".mysql_real_escape_string($id)."' AND atter_gal='".mysql_real_escape_string($Benutzer['galaxie'])."' AND atter_pla='".mysql_real_escape_string($Benutzer['planet'])."'";
						if($SQL_DEBUG) aprint($sql);
						$zugeteilt = mysql_result(tic_mysql_query($sql, __FILE__, __LINE__), 0, 0);
						if($freigegeben == 2) {
							echo '	<td>&nbsp;'.($zugeteilt ? '<b>JA</b>' : 'NEIN').'&nbsp;</td>';
						} else {
							echo '	<td>&nbsp;<i>noch keine Freigabe</i>'.($zugeteilt ? '<br/>(JA)' : '').'&nbsp;</td>';
						}

						if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
							echo '	<td>&nbsp;'.$zeit_spieler .'&nbsp;<br/>&nbsp;('.$zeit_fleets.')&nbsp;</td>';
							echo '	<td>&nbsp;'.$spieler_zugewiesen.'&nbsp;<br/>&nbsp;('.$fleets_zugewiesen.')&nbsp;</td>';
						}

						echo '	<td>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$id.'&zuweisung=1">'.(($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) ? '&raquo; Zuweisung' : '&raquo; Cockpit').'</a>&nbsp;</td>';
						echo '</tr>';
					}
				}

			}
			if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
				echo '<tr class="fieldnormaldark" style="font-weight: bold;">';
				echo '	<td colspan="6">&nbsp;&nbsp;</td>';
				echo '	<td colspan="2">&nbsp;Spieler (Flotten): '.$distinct_spieler.' ('.$distinct_fleets.')&nbsp;</td>';
				echo '	<td align="right">&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave_neu=1">&raquo; neu erstellen</a>&nbsp;</td>';
				echo '</tr>';
				echo '<tr class="fieldnormaldark"><td colspan="9" align="right" style="font-weight: bold;">';
				echo '&nbsp;<a href="main.php?modul=massinc&project='.$project.'&scansbeantragen1=1" onclick="return confirm(\'Du beantragst SEG Scans von allen Zielen. Bist Du Dir wirklich sicher?\')">&raquo SEG Scans beantragen</a>&nbsp;<br/>';
				echo '&nbsp;<a href="main.php?modul=massinc&project='.$project.'&scansbeantragen2=1" onclick="return confirm(\'Du beantragst N Scans von allen Zielen. Bist Du Dir wirklich sicher?\')">&raquo N Scans beantragen</a>&nbsp;<br/>';
				echo '</td></tr>';
			}
			echo '</table>';
		} else if(postOrGet('zuweisung') == 2 
					&& mysql_result(tic_mysql_query('SELECT freie_zielwahl FROM gn4massinc_projects WHERE project_id = "'.mysql_real_escape_string($project).'"'), 0, 'freie_zielwahl') == 1) {
			$detail = postOrGet('koord');
			if($detail) $detail = explode(':', $detail);
			
			//dummy.
			//echo 'alle so: yeah.</br>';
			$c_blue = 'rgb(150,150,255)';
			$c_yellow = 'rgb(255,255,150)';
			$c_red = 'rgb(255,150,150)';
			
			$sql1 = "SET @project = '".mysql_real_escape_string($project)."',
						@welle = '".mysql_real_escape_string($wave)."'";
			$sql2 = "SELECT zw.ziel_gal, zw.ziel_pla, 
							s.spieler_name, s.spieler_punkte,
							s0.me + s0.ke, 
							s3.ga, s3.glo, s3.glr, s3.gmr, s3.gsr,
							s1.sfj, s1.sfb, s1.sff, s1.sfz, s1.sfkr, s1.sfsa, s1.sft, s1.sfka, s1.sfsu,
							(	SELECT count(distinct(concat_ws(':', zws.atter_gal, zws.atter_pla))) FROM gn4massinc_zuweisung zws WHERE zws.project_fk = @project AND zws.welle = @welle AND zws.dest_gal = zw.ziel_gal AND zws.dest_pla = zw.ziel_pla
							) fleets
						FROM gn4massinc_ziele_welle zw
						LEFT JOIN gn4scans s0 ON s0.rg = zw.ziel_gal AND s0.rp = ziel_pla AND s0.type = 0
						LEFT JOIN gn4scans s1 ON s1.rg = zw.ziel_gal AND s1.rp = ziel_pla AND s1.type = 1
						LEFT JOIN gn4scans s3 ON s3.rg = zw.ziel_gal AND s3.rp = ziel_pla AND s3.type = 3
						LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = zw.ziel_gal AND s.spieler_planet = zw.ziel_pla
						WHERE zw.project_fk = @project AND zw.welle = @welle
						ORDER BY s.spieler_punkte DESC, zw.ziel_gal, zw.ziel_pla";
			tic_mysql_query($sql1, __FILE__, __LINE__);
			$res2 = tic_mysql_query($sql2, __FILE__, __LINE__);
			
			$num = mysql_num_rows($res2);
			if($num == 0) {
				echo 'Keine Eintr&auml;ge';
			}
			echo '<table style="border-spacing: 10px;"><tr><td></td><td rowspan="'.($num+1).'" style="padding-left: 40px; padding-top: 20px; vertical-align: top;">';
			if(count($detail) > 1) {
				echo 'Detailinformation:';
				
				if(postOrGet('koord')) {
					$tmp = explode(':', postOrGet('koord'));
					if(count($tmp) == 2) {

						$sql3 = "SET @project = '".mysql_real_escape_string($project)."',
									@welle = '".mysql_real_escape_string($wave)."',
									@gal = '".mysql_real_escape_string($tmp[0])."',
									@pla = '".mysql_real_escape_string($tmp[1])."'";
						$sql4 = "SELECT s.spieler_name, s.spieler_punkte,
										s0.me,
										s0.ke, 
										s3.ga, s3.glo, s3.glr, s3.gmr, s3.gsr,
										s1.sfj, s1.sfb, s1.sff, s1.sfz, s1.sfkr, s1.sfsa, s1.sft, s1.sfka, s1.sfsu
									FROM gn4massinc_ziele_welle zw
									LEFT JOIN gn4scans s0 ON s0.rg = zw.ziel_gal AND s0.rp = ziel_pla AND s0.type = 0
									LEFT JOIN gn4scans s1 ON s1.rg = zw.ziel_gal AND s1.rp = ziel_pla AND s1.type = 1
									LEFT JOIN gn4scans s3 ON s3.rg = zw.ziel_gal AND s3.rp = ziel_pla AND s3.type = 3
									LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = zw.ziel_gal AND s.spieler_planet = zw.ziel_pla
									WHERE zw.project_fk = @project AND zw.welle = @welle AND zw.ziel_gal = @gal AND zw.ziel_pla = @pla
									ORDER BY s.spieler_punkte DESC, zw.ziel_gal, zw.ziel_pla";
						if($SQL_DEBUG) aprint(join(';', array($sql3, $sql4)));
						tic_mysql_query($sql3, __FILE__, __LINE__);
						$res3 = tic_mysql_query($sql4, __FILE__, __LINE__);
						$num2 = mysql_num_rows($res3);
						if($num2 == 1) {
							$res3 = mysql_fetch_array($res3);
							
							echo '<table>';
							echo '  <tr class="datatablehead">';
							echo '    <td colspan="5">'.$tmp[0].':'.$tmp[1].' '.$res3['spieler_name'].'</td>';
							echo '  </tr>';
							echo '  <tr class="fieldnormaldark" style="font-weight: bold;">';
							echo '    <td>Punkte</td>';
							echo '    <td>MetExen</td>';
							echo '    <td>KrisExen</td>';
							echo '    <td>Schiffe</td>';
							echo '    <td>Defensiv</td>';
							echo '  </tr>';
							echo '  <tr class="fieldnormallight">';
							echo '    <td>'.($res3['spieler_punkte'] ? ZahlZuText($res3['spieler_punkte']) : '-').'</td>';
							echo '    <td>'.($res3['me'] ? ZahlZuText($res3['me']) : '-').'</td>';
							echo '    <td>'.($res3['ke'] ? ZahlZuText($res3['ke']) : '-').'</td>';
							echo '    <td><i>tba</i></td>';
							echo '    <td><i>tba</i></td>';
							echo '  <tr class="fieldnormaldark" style="font-weight: bold;">';
							echo '    <td>LO</td>';
							echo '    <td>LR</td>';
							echo '    <td>MR</td>';
							echo '    <td>SR</td>';
							echo '    <td>AJ</td>';
							echo '  </tr>';
							echo '  <tr class="fieldnormallight">';
							echo '    <td>'.($res3['glo'] ? ZahlZuText($res3['glo']) : '-').'</td>';
							echo '    <td>'.($res3['glr'] ? ZahlZuText($res3['glr']) : '-').'</td>';
							echo '    <td>'.($res3['gmr'] ? ZahlZuText($res3['gmr']) : '-').'</td>';
							echo '    <td>'.($res3['gsr'] ? ZahlZuText($res3['gsr']) : '-').'</td>';
							echo '    <td>'.($res3['ga'] ? ZahlZuText($res3['ga']) : '-').'</td>';
							echo '  </tr>';
							echo '  <tr>';
							echo '    <td class="fieldnormaldark" style="font-weight: bold;">J&auml;ger</td>';
							echo '    <td class="fieldnormallight">'.($res3['sfj'] ? ZahlZuText($res3['sfj']) : '-').'</td>';
							echo '    <td class="fieldnormaldark" style="font-weight: bold;">Bomber</td>';
							echo '    <td class="fieldnormallight">'.($res3['sfb'] ? ZahlZuText($res3['sfb']) : '-').'</td>';
							echo '  </tr>';
							echo '  <tr>';
							echo '    <td class="fieldnormaldark" style="font-weight: bold;">Fregatten</td>';
							echo '    <td class="fieldnormallight">'.($res3['sff'] ? ZahlZuText($res3['sff']) : '-').'</td>';
							echo '    <td class="fieldnormaldark" style="font-weight: bold;">Zerst&ouml;rer</td>';
							echo '    <td class="fieldnormallight">'.($res3['sfz'] ? ZahlZuText($res3['sfz']) : '-').'</td>';
							echo '  </tr>';
							echo '  <tr>';
							echo '    <td class="fieldnormaldark" style="font-weight: bold;">Kreuzer</td>';
							echo '    <td class="fieldnormallight">'.($res3['sfkr'] ? ZahlZuText($res3['sfkr']) : '-').'</td>';
							echo '    <td class="fieldnormaldark" style="font-weight: bold;">Schlachschiffe</td>';
							echo '    <td class="fieldnormallight">'.($res3['sfsa'] ? ZahlZuText($res3['sfsa']) : '-').'</td>';
							echo '  </tr>';
							echo '  <tr>';
							echo '    <td class="fieldnormaldark" style="font-weight: bold;">Tr&auml;ger</td>';
							echo '    <td class="fieldnormallight">'.($res3['sft'] ? ZahlZuText($res3['sft']) : '-').'</td>';
							echo '  </tr>';
							echo '  <tr>';
							echo '    <td class="fieldnormaldark" style="font-weight: bold;">Cleps</td>';
							echo '    <td class="fieldnormallight">'.($res3['sfka'] ? ZahlZuText($res3['sfka']) : '-').'</td>';
							echo '    <td class="fieldnormaldark" style="font-weight: bold;">Cancs</td>';
							echo '    <td class="fieldnormallight">'.($res3['sfsu'] ? ZahlZuText($res3['sfsu']) : '-').'</td>';
							echo '  </tr>';
							echo '</table>';
						} else {
							showError('Interner Fehler.');
						}
					}//is valid koord
				}//koord
			} else {
				echo '<i>Bitte w&auml;hle ein Ziel, um Details einzusehen.</i>';
			}
			echo '</td></tr>';
			
			while(list($zg, $zp, $zs, $zpkt, $zexen, $zgaj, $zglo, $zglr, $zgmr, $zgsr, $zfja, $zfbo, $zffr, $zfze, $zfkr, $zfsc, $zftr, $zfcl, $zfca, $fleets) = mysql_fetch_row($res2)) {
				//aprint(array($zg, $zp, $zs, $zpkt, $zexen, $zgaj, $zglo, $zglr, $zgmr, $zgsr, $zfja, $zfbo, $zffr, $zfze, $zfkr, $zfsc, $zftr, $zfcl, $zfca, $fleets));
				$color = $c_blue;
				if($fleets > 0) $color = $c_yellow;
				if($fleets > 1) $color = $c_red;
				echo '<tr>';
				echo '	<td style="'.((count($detail) > 1 && $detail[0] == $zg && $detail[1] == $zp) ? 'border: 4px green solid; ' : '').'border-radius: 50%; width: 250px; height: 150px; background-color: '.$color.'; vertical-align: middle; align: center;"><p style="font-size: 16px; font-weight: bold;"><a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&zuweisung=2&koord='.$zg.':'.$zp.'" title="Details">&raquo; '.$zg.':'.$zp.' '.$zs.'</a></p>'.ZahlZuText($zpkt).' Punkte<br/>'.ZahlZuText($zexen).' Exen</td>';
				echo '</tr>';
			}
			echo '</table>';
			
			//disable destination selection.
			$donotshowdestinations = 1;
		} else if(postOrGet('zuweisung') == 1 
						AND ($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id']) || 
							false //mysql_result(tic_mysql_query('SELECT freie_zielwahl FROM gn4massinc_projects WHERE project_id = "'.mysql_real_escape_string($project).'"'), 0, 'freie_zielwahl') == 1
						)
		) {
			$koords_g = explode(':', postOrGet('koord'))[0];
			$koords_p = explode(':', postOrGet('koord'))[1];
			
			//mgmgt
			//UPDATE
			$edit_gal = postOrGet('edit_gal');
			$edit_pla = postOrGet('edit_pla');
			$edit_fleet = postOrGet('edit_fleet');
			$edit_dest_gal = postOrGet('edit_dest_gal');
			$edit_dest_pla = postOrGet('edit_dest_pla');
			if(postOrGet('edit') && !empty($edit_dest_gal) && !empty($edit_dest_pla) && !empty($edit_gal) && !empty($edit_pla) && !empty($edit_fleet)) {
				$data = postOrGet('edit_f');
				$edit_relative_start = postOrGet('edit_relative_start');
				$edit_kommentar = postOrGet('edit_kommentar');
				$sql1 = "SET @project = '".$project."',
								@welle = '".$wave."',
								@edit_gal = '".mysql_real_escape_string($edit_gal)."',
								@edit_pla = '".mysql_real_escape_string($edit_pla)."',
								@edit_dest_gal = '".mysql_real_escape_string($edit_dest_gal)."',
								@edit_dest_pla = '".mysql_real_escape_string($edit_dest_pla)."',
								@edit_fleet = '".mysql_real_escape_string($edit_fleet)."',
								@edit_start = '".mysql_real_escape_string($edit_relative_start)."',
								@edit_kommentar = '".mysql_real_escape_string($edit_kommentar)."',
								@f0 = '".mysql_real_escape_string($data[0])."',
								@f1 = '".mysql_real_escape_string($data[1])."',
								@f2 = '".mysql_real_escape_string($data[2])."',
								@f3 = '".mysql_real_escape_string($data[3])."',
								@f4 = '".mysql_real_escape_string($data[4])."',
								@f5 = '".mysql_real_escape_string($data[5])."',
								@f6 = '".mysql_real_escape_string($data[6])."',
								@f7 = '".mysql_real_escape_string($data[7])."',
								@f8 = '".mysql_real_escape_string($data[8])."'";
				$sql2 = "UPDATE gn4massinc_fleets SET ja = @f0, bo = @f1, fr = @f2, ze = @f3, kr = @f4, sc = @f5, tr = @f6, cl = @f7, ca = @f8
						WHERE project_fk = @project AND atter_gal = @edit_gal AND atter_pla = @edit_pla AND fleet = @edit_fleet";
				$sql3 = "UPDATE gn4massinc_zuweisung SET kommentar = @edit_kommentar, relative_starttick = @edit_start
						WHERE project_fk = @project AND atter_gal = @edit_gal AND atter_pla = @edit_pla AND dest_gal = @edit_dest_gal AND dest_pla = @edit_dest_pla AND fleet_id = @edit_fleet";
				if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2, $sql3)));
				tic_mysql_query($sql1, __FILE__, __LINE__);
				tic_mysql_query($sql2, __FILE__, __LINE__);
				tic_mysql_query($sql3, __FILE__, __LINE__);
				
				$edit_gal = NULL;
				$edit_pla = NULL;
				$edit_fleet = NULL;
			}

			//DELETE
			$del_gal = postOrGet('del_gal');
			$del_pla = postOrGet('del_pla');
			$del_fleet = postOrGet('del_fleet');
			if(!empty($del_gal) && !empty($del_pla) && !empty($del_fleet)) {
				$sql1 = "SET @project = '".$project."',
								@welle = '".$wave."',
								@del_gal = '".mysql_real_escape_string($del_gal)."',
								@del_pla = '".mysql_real_escape_string($del_pla)."',
								@del_fleet = '".mysql_real_escape_string($del_fleet)."'";
				$sql2 = "DELETE FROM gn4massinc_zuweisung WHERE project_fk = @project AND welle = @welle AND atter_gal = @del_gal AND atter_pla = @del_pla AND fleet_id = @del_fleet";
				if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)), 'delete asdsignment');
				tic_mysql_query($sql1, __FILE__, __LINE__);
				tic_mysql_query($sql2, __FILE__, __LINE__);
			}
			
			//ADD
			$add_gal = postOrGet('add_gal');
			$add_pla = postOrGet('add_pla');
			if(!empty($add_gal) && !empty($add_pla)) {
				//determine fleetid
				$fleetid = 0;
				$sql1 = "SET @project = '".$project."',
								@add_gal = '".mysql_real_escape_string($add_gal)."',
								@add_pla = '".mysql_real_escape_string($add_pla)."'";
				$sql2 = "SELECT fleet_id FROM gn4massinc_zuweisung WHERE project_fk = @project AND atter_gal = @add_gal AND atter_pla = @add_pla ORDER BY fleet_id";
				if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)), 'used fleets');
				tic_mysql_query($sql1, __FILE__, __LINE__);
				$tmp = tic_mysql_query($sql2, __FILE__, __LINE__);
				$tmp_num = mysql_num_rows($tmp);
				if($tmp_num == 0) {
					$fleetid = 1;
				} else if($tmp_num > 1) {
					$fleetid = 0;
				} else {
					list($used) = mysql_fetch_row($tmp);
					$fleetid = $used % 2 + 1;
					//aprint($fleetid, 'fleetid');
				}
				
				if($fleetid) {
					$sql1 = "SET @project = '".$project."',
									@welle = '".$wave."',
									@add_gal = '".mysql_real_escape_string($add_gal)."',
									@add_pla = '".mysql_real_escape_string($add_pla)."',
									@add_fleet = '".$fleetid."',
									@dest_gal = '".mysql_real_escape_string($koords_g)."',
									@dest_pla = '".mysql_real_escape_string($koords_p)."'";
					//$sql2 = "INSERT IGNORE INTO gn4massinc_fleets (project_fk, atter_gal, atter_pla, fleet) VALUES (@project, @add_gal, @add_pla, @add_fleet)";
					$sql2 = "INSERT IGNORE INTO gn4massinc_fleets 
								(project_fk, atter_gal, atter_pla, fleet, ja, bo, fr, ze, kr, sc, tr, cl) 
								SELECT @project, @add_gal, 	@add_pla, @add_fleet,
									COALESCE(s1.sfj, 0) - COALESCE(f.ja, 0), 
									COALESCE(s1.sfb, 0) - COALESCE(f.bo, 0), 
									COALESCE(s1.sff, 0) - COALESCE(f.fr, 0), 
									COALESCE(s1.sfz, 0) - COALESCE(f.ze, 0), 
									COALESCE(s1.sfkr, 0) - COALESCE(f.kr, 0), 
									COALESCE(s1.sfsa, 0) - COALESCE(f.sc, 0), 
									COALESCE(s1.sft, 0) - COALESCE(f.tr, 0), 
									COALESCE(s1.sfka, 0) - COALESCE(f.cl, 0)
							FROM gn4massinc_atter_willing aw
							JOIN gn4massinc_atter a ON a.project_fk = aw.project_fk AND a.gal = aw.atter_gal AND a.pla = aw.atter_pla
							LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = aw.atter_gal AND s.spieler_planet = aw.atter_pla
							LEFT JOIN gn4scans s1 ON s1.rg = aw.atter_gal AND s1.rp = aw.atter_pla AND s1.type = 1
							LEFT JOIN gn4massinc_zuweisung xz ON xz.project_fk = aw.project_fk AND xz.atter_gal = aw.atter_gal AND xz.atter_pla = aw.atter_pla
							LEFT JOIN gn4massinc_fleets f ON f.project_fk = aw.project_fk AND f.atter_gal = aw.atter_gal AND f.atter_pla = aw.atter_pla AND xz.fleet_id = f.fleet
							WHERE aw.willing = 1 AND aw.project_fk = @project AND aw.welle = @welle AND aw.atter_gal = @add_gal AND aw.atter_pla = @add_pla LIMIT 1";
					$sql3 = "INSERT INTO gn4massinc_zuweisung (project_fk, welle, atter_gal, atter_pla, fleet_id, dest_gal, dest_pla) VALUES (@project, @welle, @add_gal, @add_pla, @add_fleet, @dest_gal, @dest_pla)";
					if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2, $sql3)), 'add assignment');
					tic_mysql_query($sql1, __FILE__, __LINE__);
					tic_mysql_query($sql2, __FILE__, __LINE__);
					tic_mysql_query($sql3, __FILE__, __LINE__);
					
					$edit_gal = $add_gal;
					$edit_pla = $add_pla;
					$edit_fleet = $fleetid;
				}
			}
			
			//zuweisungen löschen
			if(postOrGet('zuweisungenloeschen') == 1 || postOrGet('zuweisungauto') == 1) {
				$sql1 = "SET @project = '".mysql_real_escape_string($project)."', @wave = '".mysql_real_escape_string($wave)."'";
				$sql2 = "DELETE FROM gn4massinc_zuweisung WHERE project_fk = @project AND welle = @wave";
				tic_mysql_query($sql1, __FILE__, __LINE__);
				tic_mysql_query($sql2, __FILE__, __LINE__);
				if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)));
			}

			//auto
			if(postOrGet('zuweisungenauto') == 1) {
				showError('AUTO nicht implementiert.');
			}
			
			//display
			$sql = "SELECT project_fk, id, t, kommentar FROM gn4massinc_wellen WHERE id = '".mysql_real_escape_string($wave)."'";
			list($project, $wave, $t, $kommentar) = mysql_fetch_row(tic_mysql_query($sql, __FILE__, __LINE__));
			
			echo '<table>';
			echo '	<tr class="datatablehead">';
			echo '		<td>&nbsp;<a href="main.php?modul=massinc&project='.$project.'">&laquo; zur&uuml;ck</a>&nbsp;</td>';
			echo '		<td>&nbsp;Status&nbsp;</td>';
			echo '		<td colspan="3">&nbsp;Ziel&nbsp;</td>';
			echo '		<td>&nbsp;Zuweisung Welle #'. $wave .' - ' . date('Y-m-d H:i', $t) . '&nbsp;</td>';
			echo '	</tr>';
			
			$sql1 = "SET @welle = '".$wave."',
						@project = '".$project."'";
			$sql2 = "SELECT z.gal, z.pla, s.spieler_name, s.spieler_urlaub,
						(SELECT COUNT(*) FROM gn4massinc_zuweisung x WHERE x.project_fk = @project AND x.welle = @welle AND x.dest_gal = z.gal AND x.dest_pla = z.pla) flotten_zugewiesen, s.spieler_punkte
					FROM gn4massinc_ziele z 
					JOIN gn4massinc_ziele_welle zw ON zw.welle = @welle AND zw.project_fk = @project AND ziel_gal = z.gal AND ziel_pla = z.pla
					LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = z.gal AND s.spieler_planet = z.pla";
			if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)));
			tic_mysql_query($sql1, __FILE__, __LINE__);
			$res = tic_mysql_query($sql2, __FILE__, __LINE__);
			$num = mysql_num_rows($res);

			//MAIN LOOP FOR PLAYERS
			$color = true;
			$first = true;
			
			if($num == 0) {
				echo '<tr class="fieldnormallight"><td align="center" colspan="6">Es sind noch keine Ziele ausgew&auml;hlt. <a href="main.php?modul=massinc&project='.$project.'">&raquo; Hier geht es weiter</a></td></tr>';
			}
			
			$color = false;
			while(list($g, $p, $name, $urlaub, $flotten_zugewiesen, $pkt) = mysql_fetch_row($res)) {
				$color = !$color;
				
				echo '	<tr class="fieldnormal'.($color ? 'light' : 'dark').'"'.($g == $koords_g && $p == $koords_p ? ' style="background-color: #dddd99"' : '').'>';
				echo '		<td width="100">&nbsp;<a style="font-weight: bold; font-size: 15px;" href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&zuweisung=1&koord='.$g.':'.$p.'">&raquo; w&auml;hlen</a>&nbsp;</td>';
				echo '		<td>&nbsp;';
				if($urlaub) {
					echo '<span style="color: red">URLAUB!</span>&nbsp;<br/>&nbsp;';
				}
				echo '			'. $flotten_zugewiesen .' Flotten&nbsp;</td>';
				echo '		<td>&nbsp;'.$g.'&nbsp;</td>';
				echo '		<td>&nbsp;'.$p.'&nbsp;</td>';
				echo '		<td>&nbsp;'.$name.'&nbsp;<br/><i>'.ZahlZuText($pkt).' Pkt.</i></td>';

				//ASSIGNMENT
				if($first == true) {
					$first = false;
					
					if(empty($koords_g) && empty($koords_p)) {
						echo '		<td rowspan="'.($num).'" style="background-color: #dddd99" valign="top">';
						echo '			<p style="margin: 5px"><b>Willkommen.</b> Wähle im linken Men&uuml; Dein Ziel, um Flotten dort zuweisen zu k&ouml;nnen.</p><br/>';
						echo '<table align="center"><tr><td align="left">';
						
						echo '<b>Optionen:</b><br/>';
						echo '<a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&zuweisung=1&zuweisungenloeschen=1" onclick="return confirm(\'Bist Du Dir sicher?\')">&raquo; Zuweisungen l&ouml;schen</a><br/>';
						echo '<a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&zuweisung=1&&zuweisungenauto=1" onclick="return confirm(\'Bist Du Dir sicher?\')">&raquo; Zuweisungen l&ouml;schen und Vorschlag eintragen</a>';
						echo '</td></tr></table><br/>';

						//STATS
						/*
						//opfer
						$sql1 = "SET @project = '".mysql_real_escape_string($project)."',
									@welle = '".mysql_real_escape_string($wave)."'";
						$sql2 = "SELECT SUM(s.spieler_punkte), COUNT(*), SUM(s3.ga), SUM(s3.glo), SUM(s3.glr), SUM(s3.gmr), SUM(s3.gsr),
										SUM(s1.sfj), SUM(s1.sfb), SUM(s1.sff), SUM(s1.sfz), SUM(s1.sfkr), SUM(s1.sfsa), SUM(s1.sft), SUM(s1.sfka), SUM(s1.sfsu)
									FROM gn4massinc_ziele_welle zw
									LEFT JOIN gn4scans s0 ON s0.rg = zw.ziel_gal AND s0.rp = ziel_pla AND s0.type = 0
									LEFT JOIN gn4scans s1 ON s1.rg = zw.ziel_gal AND s1.rp = ziel_pla AND s1.type = 1
									LEFT JOIN gn4scans s3 ON s3.rg = zw.ziel_gal AND s3.rp = ziel_pla AND s3.type = 3
									LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = zw.ziel_gal AND s.spieler_planet = zw.ziel_pla
									WHERE zw.project_fk = @project AND zw.welle = @welle";
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$res2 = tic_mysql_query($sql2, __FILE__, __LINE__);
						list($zpkt, $znum, $zgaj, $zglo, $zglr, $zgmr, $zgsr, $zfja, $zfbo, $zffr, $zfze, $zfkr, $zfsc, $zftr, $zfcl, $zfca) = mysql_fetch_row($res2);
						//wir
						$sql2 = "SELECT SUM(s.spieler_punkte), COUNT(*), SUM(s1.sfj), SUM(s1.sfb), SUM(s1.sff), SUM(s1.sfz), SUM(s1.sfkr), SUM(s1.sfsa), SUM(s1.sft), SUM(s1.sfka), SUM(s1.sfsu)
									FROM gn4massinc_atter_willing aw
									LEFT JOIN gn4scans s0 ON s0.rg = aw.atter_gal AND s0.rp = aw.atter_pla AND s0.type = 0
									LEFT JOIN gn4scans s1 ON s1.rg = aw.atter_gal AND s1.rp = aw.atter_pla AND s1.type = 1
									LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = aw.atter_gal AND s.spieler_planet = aw.atter_pla
									WHERE aw.project_fk = @project AND aw.welle = @welle AND aw.willing = 1
									ORDER BY aw.atter_gal, aw.atter_pla";
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$res2 = tic_mysql_query($sql2, __FILE__, __LINE__);
						list($wpkt, $wnum, $wfja, $wfbo, $wffr, $wfze, $wfkr, $wfsc, $wftr, $wfcl, $wfca) = mysql_fetch_row($res2);
						
						echo '	<table bgcolor="white" style="width: calc(100% - 10px); margin: 5px" align="center">';
						echo '		<tr class="datatablehead">';
						echo '			<td colspan="6">&nbsp;Statistiken (verf&uuml;gbar)&nbsp;</td>';
						echo '		</tr>';
						echo '		<tr class="fieldnormaldark" style="font-weight: bold">';
						echo '			<td>&nbsp;&nbsp;</td>';
						echo '			<td colspan="3">&nbsp;WIR&nbsp;</td>';
						echo '			<td>&nbsp;vs.&nbsp;</td>';
						echo '			<td>&nbsp;Gegner&nbsp;</td>';
						echo '		</tr>';
						echo '		<tr class="fieldnormallight">';
						echo '			<td>&nbsp;Punkte&nbsp;</td>';
						echo '			<td colspan="3">&nbsp;'.ZahlZuText($wpkt).'&nbsp;</td>';
						echo '			<td>&nbsp;'.round($wpkt / $zpkt, 2).'&nbsp;</td>';
						echo '			<td>&nbsp;'.ZahlZuText($zpkt).'&nbsp;</td>';
						echo '		</tr>';
						echo '		<tr class="fieldnormaldark">';
						echo '			<td>&nbsp;Spieler&nbsp;</td>';
						echo '			<td colspan="3">&nbsp;'.ZahlZuText($wnum).'&nbsp;</td>';
						echo '			<td>&nbsp;'.round($wnum / $znum, 2).'&nbsp;</td>';
						echo '			<td>&nbsp;'.ZahlZuText($znum).'&nbsp;</td>';
						echo '		</tr>';
						echo '		<tr class="fieldnormaldark" style="height: 5px">';
						echo '			<td colspan="6"></td>';
						echo '		</tr>';
						echo '	</table><br/>';
						*/
						
						//ZIELE
						echo '			<table bgcolor="white" style="width: calc(100% - 10px); margin: 5px" align="center">';
						echo '				<tr class="datatablehead">';
						echo '					<td colspan="19">&nbsp;Total Ziele&nbsp;</td>';
						echo '				</tr>';
						echo '				<tr class="fieldnormaldark" style="font-weight: bold">';
						echo '					<td colspan="5">&nbsp;Ziel&nbsp;</td>';
						echo '					<td colspan="5">&nbsp;Deff&nbsp;</td>';
						echo '					<td colspan="9">&nbsp;Schiffe&nbsp;</td>';
						echo '				</tr>';
						echo '				<tr class="fieldnormaldark" style="font-weight: bold">';
						echo '					<td>&nbsp;Gal&nbsp;</td>';
						echo '					<td>&nbsp;Pla&nbsp;</td>';
						echo '					<td>&nbsp;Spieler&nbsp;</td>';
						echo '					<td>&nbsp;Punkte&nbsp;</td>';
						echo '					<td>&nbsp;Exen&nbsp;</td>';
						echo '					<td>&nbsp;AJ&nbsp;</td>';
						echo '					<td>&nbsp;LO&nbsp;</td>';
						echo '					<td>&nbsp;LR&nbsp;</td>';
						echo '					<td>&nbsp;MR&nbsp;</td>';
						echo '					<td>&nbsp;SR&nbsp;</td>';
						echo '					<td>&nbsp;Ja&nbsp;</td>';
						echo '					<td>&nbsp;Bo&nbsp;</td>';
						echo '					<td>&nbsp;Fr&nbsp;</td>';
						echo '					<td>&nbsp;Ze&nbsp;</td>';
						echo '					<td>&nbsp;Kr&nbsp;</td>';
						echo '					<td>&nbsp;Sc&nbsp;</td>';
						echo '					<td>&nbsp;Tr&nbsp;</td>';
						echo '					<td>&nbsp;Cl&nbsp;</td>';
						echo '					<td>&nbsp;Ca&nbsp;</td>';
						echo '				</tr>';
						
						$sql1 = "SET @project = '".mysql_real_escape_string($project)."',
									@welle = '".mysql_real_escape_string($wave)."'";
						$sql2 = "SELECT zw.ziel_gal, zw.ziel_pla, 
										s.spieler_name, s.spieler_punkte,
										s0.me + s0.ke, 
										s3.ga, s3.glo, s3.glr, s3.gmr, s3.gsr,
										s1.sfj, s1.sfb, s1.sff, s1.sfz, s1.sfkr, s1.sfsa, s1.sft, s1.sfka, s1.sfsu
									FROM gn4massinc_ziele_welle zw
									LEFT JOIN gn4scans s0 ON s0.rg = zw.ziel_gal AND s0.rp = ziel_pla AND s0.type = 0
									LEFT JOIN gn4scans s1 ON s1.rg = zw.ziel_gal AND s1.rp = ziel_pla AND s1.type = 1
									LEFT JOIN gn4scans s3 ON s3.rg = zw.ziel_gal AND s3.rp = ziel_pla AND s3.type = 3
									LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = zw.ziel_gal AND s.spieler_planet = zw.ziel_pla
									WHERE zw.project_fk = @project AND zw.welle = @welle
									ORDER BY zw.ziel_gal, zw.ziel_pla";
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$res2 = tic_mysql_query($sql2, __FILE__, __LINE__);
						
						if(mysql_num_rows($res2) == 0) {
							echo '<tr class="fieldnormallight"><td colspan="19" align="center">&nbsp;Keine Eintr&auml;ge.&nbsp;</td></tr>';
						}
						$color = false;
						$szpkt = $szexen = $szgaj = $szglo = $szglr = $szgmr = $szgsr = $szfja = $szfbo = $szffr = $szfze = $szfkr = $szfsc = $szftr = $szfcl = $szfca = 0;
						while(list($zg, $zp, $zs, $zpkt, $zexen, $zgaj, $zglo, $zglr, $zgmr, $zgsr, $zfja, $zfbo, $zffr, $zfze, $zfkr, $zfsc, $zftr, $zfcl, $zfca) = mysql_fetch_row($res2)) {
							$szpkt += $zpkt;
							$szexen += $zexen;
							$szgaj += $zgaj;
							$szglo += $zglo;
							$szglr += $zglr;
							$szgmr += $zgmr;
							$szgsr += $zgsr;
							$szfja += $zfja;
							$szfbo += $zfbo;
							$szffr += $zffr;
							$szfze += $zfze;
							$szfkr += $zfkr;
							$szfsc += $zfsc;
							$szftr += $zftr;
							$szfcl += $zfcl;
							$szfca += $zfca;
							
							$color = !$color;
							echo '				<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
							echo '					<td>&nbsp;'.$zg.'&nbsp;</td>';
							echo '					<td>&nbsp;'.$zp.'&nbsp;</td>';
							echo '					<td>&nbsp;'.$zs.'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zpkt).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zexen).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zgaj).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zglo).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zglr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zgmr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zgsr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfja).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfbo).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zffr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfze).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfkr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfsc).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zftr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfcl).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfca).'&nbsp;</td>';
							echo '				</tr>';
						}
						echo '				<tr class="fieldnormaldark" style="font-weight: bold">';
						echo '					<td colspan="3">&nbsp;&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szpkt).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szexen).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szgaj).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szglo).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szglr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szgmr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szgsr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfja).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfbo).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szffr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfze).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfkr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfsc).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szftr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfcl).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfca).'&nbsp;</td>';
						echo '				</tr>';
						echo '			</table>';

						//übersicht verfügbar
						echo '			<br/>';
						echo '			<table bgcolor="white" style="width: calc(100% - 10px); margin: 5px" align="center">';
						echo '				<tr class="datatablehead">';
						echo '					<td colspan="14">&nbsp;Total verf&uuml;gbar&nbsp;</td>';
						echo '				</tr>';
						echo '				<tr class="fieldnormaldark" style="font-weight: bold">';
						echo '					<td colspan="5">&nbsp;Ziel&nbsp;</td>';
						echo '					<td colspan="9">&nbsp;Schiffe&nbsp;</td>';
						echo '				</tr>';
						echo '				<tr class="fieldnormaldark" style="font-weight: bold">';
						echo '					<td>&nbsp;Gal&nbsp;</td>';
						echo '					<td>&nbsp;Pla&nbsp;</td>';
						echo '					<td>&nbsp;Spieler&nbsp;</td>';
						echo '					<td>&nbsp;Punkte&nbsp;</td>';
						echo '					<td>&nbsp;Exen&nbsp;</td>';
						echo '					<td>&nbsp;Ja&nbsp;</td>';
						echo '					<td>&nbsp;Bo&nbsp;</td>';
						echo '					<td>&nbsp;Fr&nbsp;</td>';
						echo '					<td>&nbsp;Ze&nbsp;</td>';
						echo '					<td>&nbsp;Kr&nbsp;</td>';
						echo '					<td>&nbsp;Sc&nbsp;</td>';
						echo '					<td>&nbsp;Tr&nbsp;</td>';
						echo '					<td>&nbsp;Cl&nbsp;</td>';
						echo '					<td>&nbsp;Ca&nbsp;</td>';
						echo '				</tr>';
						
						$sql2 = "SELECT aw.atter_gal, aw.atter_pla, 
										s.spieler_name, s.spieler_punkte,
										s0.me + s0.ke, 
										s1.sfj, s1.sfb, s1.sff, s1.sfz, s1.sfkr, s1.sfsa, s1.sft, s1.sfka, s1.sfsu
									FROM gn4massinc_atter_willing aw
									LEFT JOIN gn4scans s0 ON s0.rg = aw.atter_gal AND s0.rp = aw.atter_pla AND s0.type = 0
									LEFT JOIN gn4scans s1 ON s1.rg = aw.atter_gal AND s1.rp = aw.atter_pla AND s1.type = 1
									LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = aw.atter_gal AND s.spieler_planet = aw.atter_pla
									WHERE aw.project_fk = @project AND aw.welle = @welle AND aw.willing = 1
									ORDER BY aw.atter_gal, aw.atter_pla";
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$res2 = tic_mysql_query($sql2, __FILE__, __LINE__);
						
						if(mysql_num_rows($res2) == 0) {
							echo '<tr class="fieldnormallight"><td colspan="14" align="center">&nbsp;Keine Eintr&auml;ge.&nbsp;</td></tr>';
						}
						$color = false;
						$szpkt = $szexen = $szgaj = $szglo = $szglr = $szgmr = $szgsr = $szfja = $szfbo = $szffr = $szfze = $szfkr = $szfsc = $szftr = $szfcl = $szfca = 0;
						while(list($zg, $zp, $zs, $zpkt, $zexen, $zfja, $zfbo, $zffr, $zfze, $zfkr, $zfsc, $zftr, $zfcl, $zfca) = mysql_fetch_row($res2)) {
							$szpkt += $zpkt;
							$szexen += $zexen;
							$szfja += $zfja;
							$szfbo += $zfbo;
							$szffr += $zffr;
							$szfze += $zfze;
							$szfkr += $zfkr;
							$szfsc += $zfsc;
							$szftr += $zftr;
							$szfcl += $zfcl;
							$szfca += $zfca;
							
							$color = !$color;
							echo '				<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
							echo '					<td>&nbsp;'.$zg.'&nbsp;</td>';
							echo '					<td>&nbsp;'.$zp.'&nbsp;</td>';
							echo '					<td>&nbsp;'.$zs.'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zpkt).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zexen).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfja).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfbo).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zffr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfze).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfkr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfsc).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zftr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfcl).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zfca).'&nbsp;</td>';
							echo '				</tr>';
						}
						echo '				<tr class="fieldnormaldark" style="font-weight: bold">';
						echo '					<td colspan="3">&nbsp;&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szpkt).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szexen).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfja).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfbo).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szffr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfze).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfkr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfsc).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szftr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfcl).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($szfca).'&nbsp;</td>';
						echo '				</tr>';
						echo '			</table>';
						echo '		</td>';
					} else {
						//player info
						$sql1 = "SET @project = '".$project."',
										@welle = '".$wave."',
										@gal = '".mysql_real_escape_string($koords_g)."',
										@pla = '".mysql_real_escape_string($koords_p)."'";
						$sql2 = "SELECT 
								s.spieler_name, s.spieler_punkte, s.spieler_urlaub, UNIX_TIMESTAMP(STR_TO_DATE(s0.zeit, '%H:%i %d.%m.%Y')) szeit, s0.me, s0.ke,
								s3.id, UNIX_TIMESTAMP(STR_TO_DATE(s3.zeit, '%H:%i %d.%m.%Y')) gzeit, s3.ga, s3.glo, s3.glr, s3.gmr, s3.gsr,
								s1.id, UNIX_TIMESTAMP(STR_TO_DATE(s1.zeit, '%H:%i %d.%m.%Y')) ezeit, s1.sfj, s1.sfb, s1.sff, s1.sfz, s1.sfkr, s1.sfsa, s1.sft, s1.sfka, s1.sfsu
							FROM gn4massinc_ziele_welle zw
							LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = zw.ziel_gal AND s.spieler_planet = zw.ziel_pla
							LEFT JOIN gn4scans s0 ON s0.rg = zw.ziel_gal AND s0.rp = zw.ziel_pla AND s0.type = 0
							LEFT JOIN gn4scans s1 ON s1.rg = zw.ziel_gal AND s1.rp = zw.ziel_pla AND s1.type = 1
							LEFT JOIN gn4scans s3 ON s3.rg = zw.ziel_gal AND s3.rp = zw.ziel_pla AND s3.type = 3
							WHERE zw.ziel_gal = @gal AND zw.ziel_pla = @pla AND zw.project_fk = @project AND zw.welle = @welle";
						if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)), "victum info");
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$player = tic_mysql_query($sql2, __FILE__, __LINE__);
						$num2 = mysql_num_rows($player);
						if($num2 != 1) {
							showError('Fehler, Spielerdaten nicht gefunden.');
						}
						list($s_name, $pkt, $s_urlaub, $s_t, $s_me, $s_ke,
							$g_id, $g_t, $g_aj, $g_lo, $g_lr, $g_mr, $g_sr, 
							$f_id, $f_t, $f_ja, $f_bo, $f_fr, $f_ze, $f_kr, $f_sc, $f_tr, $f_cl, $f_ca) = mysql_fetch_row($player);
						
						echo '		<td rowspan="'.($num).'" style="background-color: #dddd99">';
						echo '			<table bgcolor="white" style="margin: 5px; padding: 5px">';
						echo '				<tr>';
						echo '					<td colspan="16" class="datatablehead">&nbsp;Gegnerflotte ' . $s_name . '&nbsp;</td>';
						echo '					<td colspan="2">&nbsp;&nbsp;</td>';
						echo '					<td rowspan="16" valign="top">';
						echo '						<table style="margin-left: 10px">';
						echo '							<tr class="datatablehead">';
						echo '								<td colspan="2">&nbsp;Zusammenfassung&nbsp;</td>';
						echo '							</tr>';
						echo '							<tr class="fieldnormaldark" style="font-weight: bold;">';
						echo '								<td>&nbsp;Punkte&nbsp;</td>';
						echo '								<td>&nbsp;Flotten&nbsp;<br/>&nbsp;/ (Spieler)&nbsp;</td>';
						echo '							</tr>';
						echo '							<tr class="fieldnormallight">';
						echo '								<td>&nbsp;'.ZahlZuText($pkt).'&nbsp;</td>';
						echo '								<td>&nbsp;-&nbsp;</td>';
						echo '							</tr>';
						echo '							<tr class="fieldnormaldark">';
						echo '								<td colspan="2" height="5"></td>';
						echo '							</tr>';
						echo '							<tr class="fieldnormallight">';

						//total atter points
						$sql2 = "SELECT COALESCE(SUM(g.spieler_punkte), 0) total_pkt 
								FROM (
									SELECT DISTINCT atter_gal, atter_pla 
									FROM gn4massinc_zuweisung 
									WHERE project_fk = @project AND welle = @welle AND dest_gal = @gal AND dest_pla = @pla
									) z
									LEFT JOIN gn_spieler2 g ON g.spieler_galaxie = z.atter_gal AND g.spieler_planet = z.atter_pla";
						if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)), "total att pkt");
						tic_mysql_query($sql1, __FILE__, __LINE__);
						list($fleetpkt) = mysql_fetch_row(tic_mysql_query($sql2, __FILE__, __LINE__));

						//atter num per ally
						$sql2 = "SELECT COUNT(DISTINCT(CONCAT_WS(':', atter_gal, atter_pla))) num, g.allianz_name FROM gn4massinc_zuweisung z
									LEFT JOIN gn_spieler2 g ON g.spieler_galaxie = atter_gal AND g.spieler_planet = atter_pla
									WHERE z.project_fk = @project AND z.welle = @welle AND z.dest_gal = @gal AND z.dest_pla = @pla
									GROUP BY g.allianz_name
									ORDER BY num DESC";
						if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)), "num per ally");
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$tmp = tic_mysql_query($sql2, __FILE__, __LINE__);
						$spieler_punkte_str = "";
						$total_num = 0;
						$error = false;
						while(list($num, $ally) = mysql_fetch_row($tmp)) {
							if($total_num > 0) {
								$spieler_punkte_str .= ", ";
							}
							$spieler_punkte_str .= '<span title="'.$ally.'">' . $num . '</span>';
							$total_num++;
							if($num > 8)
								$error = true;
						}
						
						if($total_num == 0) {
							$spieler_punkte_str = '-';
						}
						
						echo '								<td'.($fleetpkt > 6 * $pkt ? ' bgcolor="#ffaaaa"' : '').'>&nbsp;'.ZahlZuText($fleetpkt).'&nbsp;<br/>&nbsp;/ '.ZahlZuText(6 * $pkt).'&nbsp;</td>';
						echo '								<td'.($error ? ' bgcolor="#ffaaaa"' : '').'>&nbsp;'.$flotten_zugewiesen.'&nbsp;<br/>&nbsp;/ ('.$spieler_punkte_str.')&nbsp;</td>';
						echo '							</tr>';
						echo '							<tr class="fieldnormallight">';
						echo '								<td><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"/ height="10" width="'.($pkt > 0 ? min(round($fleetpkt / (6 * $pkt) * 100, 0), 100) : '0').'%" style="background-color: darkgray"><img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"/ height="10" width="'.($pkt > 0 ? max(100 - round($fleetpkt / (6 * $pkt) * 100, 0), 0) : '100').'%" style="background-color: lightgreen"></td>';
						echo '								<td title="ohne Kriegszustand">&nbsp;max. 8 pro Ally&nbsp;</td>';
						echo '							</tr>';
						echo '							<tr class="fieldnormaldark">';
						echo '								<td colspan="2" height="5"></td>';
						echo '							</tr>';
						echo '							<tr class="fieldnormallight" align="left"><td>&nbsp;Extraktoren&nbsp;</td><td align="center">&nbsp;'.(is_null($s_me) ? '-' : ZahlZuText($s_me + $s_ke) . ' (M ' . ZahlZuText($s_me) . ', K ' . ZahlZuText($s_ke) . ')').'&nbsp;</td></tr>';
						echo '							<tr class="fieldnormallight" align="left"><td>&nbsp;5 Tick Roid&nbsp;</td><td align="center">&nbsp;'.(is_null($s_me) ? '-' : ZahlZuText($s_me + $s_ke - ($s_me + $s_ke) * pow(0.9, 5))).'&nbsp;</td></tr>';
						echo '							<tr class="fieldnormaldark">';
						echo '								<td colspan="2" height="5"></td>';
						echo '							</tr>';
						echo '							<tr class="fieldnormallight" align="left"><td>&nbsp;Sektorscan&nbsp;</td><td>&nbsp;'.(is_null($s_t) ? '-' : date('Y-m-d H:i', $s_t)).'&nbsp;</td></tr>';
						echo '							<tr class="fieldnormallight" align="left"><td>&nbsp;Gesch&uuml;tzscan&nbsp;</td><td>&nbsp;'.(is_null($g_t) ? '-' : date('Y-m-d H:i', $g_t)).'&nbsp;</td></tr>';
						echo '							<tr class="fieldnormallight" align="left"><td>&nbsp;Einheitenscan&nbsp;</td><td>&nbsp;'.(is_null($f_t) ? '-' : date('Y-m-d H:i', $f_t)).'&nbsp;</td></tr>';
						echo '							<tr class="fieldnormaldark">';
						echo '								<td colspan="2" height="5"></td>';
						echo '							</tr>';
						echo '							<tr class="fieldnormallight" align="right"><td bgcolor="white"></td><td>&nbsp;<a href="main.php?modul=showgalascans&displaytype=0&xgala='.$koords_g.'&xplanet='.$koords_p.'">&raquo; Scans</a>&nbsp;</td></tr>';
						echo '							<tr class="fieldnormaldark">';
						echo '								<td bgcolor="white"></td><td height="5"></td>';
						echo '							</tr>';
						echo '							<tr class="fieldnormallight" align="right"><td bgcolor="white"></td><td>&nbsp;<b>'.createSimuLink($project, $wave, $koords_g, $koords_p, '&raquo; Simulation').'</b>&nbsp;</td></tr>';
						if($s_urlaub)
							echo '						<tr><td colspan="2">URLAUB!</td></tr>';
						echo '						</table>';
						echo '					</td>';
						echo '				</tr>';

						//ziel info
						echo '				<tr style="font-weight: bold;" class="fieldnormaldark">';
						echo '					<td>&nbsp;AJ&nbsp;</td>';
						echo '					<td>&nbsp;LO&nbsp;</td>';
						echo '					<td>&nbsp;LR&nbsp;</td>';
						echo '					<td>&nbsp;MR&nbsp;</td>';
						echo '					<td>&nbsp;SR&nbsp;</td>';
						echo '					<td rowspan="2" colspan="2" bgcolor="white">&nbsp;&nbsp;</td>';
						echo '					<td>&nbsp;Ja&nbsp;</td>';
						echo '					<td>&nbsp;Bo&nbsp;</td>';
						echo '					<td>&nbsp;Fr&nbsp;</td>';
						echo '					<td>&nbsp;Ze&nbsp;</td>';
						echo '					<td>&nbsp;Kr&nbsp;</td>';
						echo '					<td>&nbsp;Sc&nbsp;</td>';
						echo '					<td>&nbsp;Tr&nbsp;</td>';
						echo '					<td>&nbsp;Cl&nbsp;</td>';
						echo '					<td>&nbsp;Ca&nbsp;</td>';
						echo '				</tr>';
						echo '				<tr class="fieldnormallight">';
						echo '					<td>&nbsp;'.ZahlZuText($g_aj).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($g_lo).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($g_lr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($g_mr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($g_sr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($f_ja).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($f_bo).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($f_fr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($f_ze).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($f_kr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($f_sc).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($f_tr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($f_cl).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($f_ca).'&nbsp;</td>';
						echo '				</tr>';

						echo '				</tr><tr style="font-weight: bold; background-color: white"><td colspan="7"></td><td colspan="9" bgcolor="#ffaaaa" style="font-size: 6pt">vs<td></td></tr>';

						//zugewiesene flotten
						$sql1 = "SET @project = '".$project."',
										@welle = '".$wave."',
										@gal = '".mysql_real_escape_string($koords_g)."',
										@pla = '".mysql_real_escape_string($koords_p)."'";
						$sql2 = "SELECT 
								SUM(f.ja), SUM(f.bo), SUM(f.fr), SUM(f.ze), SUM(f.kr), SUM(f.sc), SUM(f.tr), SUM(f.cl), SUM(f.ca)
							FROM gn4massinc_zuweisung zw
							JOIN gn4massinc_fleets f ON f.project_fk = zw.project_fk AND f.atter_gal = zw.atter_gal AND f.atter_pla = zw.atter_pla AND f.fleet = zw.fleet_id
							LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = zw.dest_gal AND s.spieler_planet = zw.dest_pla
							WHERE zw.dest_gal = @gal AND zw.dest_pla = @pla AND zw.project_fk = @project AND zw.welle = @welle";
						if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)), "zugewiesen");
						tic_mysql_query($sql1, __FILE__, __LINE__);
						list($sum_ja, $sum_bo, $sum_fr, $sum_ze, $sum_kr, $sum_sc, $sum_tr, $sum_cl, $sum_ca) = mysql_fetch_row(tic_mysql_query($sql2, __FILE__, __LINE__));
						echo '				<tr>';
						echo '					<td colspan="16" class="datatablehead">&nbsp;Hier zugewiesen&nbsp;</td>';
						echo '				</tr>';
						echo '				<tr style="font-weight: bold;" class="fieldnormaldark">';
						echo '					<td rowspan="2" colspan="7" bgcolor="white">&nbsp;'.createSimuLink($project, $wave, $koords_g, $koords_p, '&raquo; zur Simulation').'&nbsp;</td>';
						echo '					<td>&nbsp;Ja&nbsp;</td>';
						echo '					<td>&nbsp;Bo&nbsp;</td>';
						echo '					<td>&nbsp;Fr&nbsp;</td>';
						echo '					<td>&nbsp;Ze&nbsp;</td>';
						echo '					<td>&nbsp;Kr&nbsp;</td>';
						echo '					<td>&nbsp;Sc&nbsp;</td>';
						echo '					<td>&nbsp;Tr&nbsp;</td>';
						echo '					<td>&nbsp;Cl&nbsp;</td>';
						echo '					<td>&nbsp;Ca&nbsp;</td>';
						echo '				</tr>';
						echo '				<tr style="font-weight: bold;" class="fieldnormallight">';
						echo '					<td>&nbsp;'.ZahlZuText($sum_ja).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_bo).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_fr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_ze).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_kr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_sc).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_tr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_cl).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_ca).'&nbsp;</td>';
						echo '				</tr><tr style="font-weight: bold;" class="fieldnormaldark"><td colspan="16" height="5"></td></tr>';
						$sql2 = "SELECT 
								s.spieler_name, s.spieler_punkte, zw.atter_gal, zw.atter_pla, zw.fleet_id, zw.relative_starttick, zw.kommentar,
								f.ja, f.bo, f.fr, f.ze, f.kr, f.sc, f.tr, f.cl, f.ca
							FROM gn4massinc_zuweisung zw
							JOIN gn4massinc_fleets f ON f.project_fk = zw.project_fk AND f.atter_gal = zw.atter_gal AND f.atter_pla = zw.atter_pla AND f.fleet = zw.fleet_id
							LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = zw.atter_gal AND s.spieler_planet = zw.atter_pla
							WHERE zw.dest_gal = @gal AND zw.dest_pla = @pla AND zw.project_fk = @project AND zw.welle = @welle
							ORDER BY zw.relative_starttick, zw.atter_gal, zw.atter_pla, zw.fleet_id";
						if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)), "zugewiesen");
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$zugewiesen = tic_mysql_query($sql2, __FILE__, __LINE__);
						if(mysql_num_rows($zugewiesen) == 0) {
							echo '<tr class="fieldnormallight"><td colspan="16">&nbsp;<i>keine</i></td></tr>';
						}
						$color2 = false;
						while(list($zw_name, $zw_pkt, $zw_atter_gal, $zw_atter_pla, $zw_fleet_id, $zw_relative_starttick, $zw_kommentar, $zw_fja, $zw_fbo, $zw_ffr, $zw_fze, $zw_fkr, $zw_fsc, $zw_ftr, $zw_fcl, $zw_fca) = mysql_fetch_row($zugewiesen))
						{
							$color2 = !$color2;

							if($edit_gal == $zw_atter_gal && $edit_pla == $zw_atter_pla && $edit_fleet == $zw_fleet_id) {
								$sql1 = "SET @project = '".$project."',
												@welle = '".$wave."',
												@igal = '".mysql_real_escape_string($edit_gal)."',
												@ipla = '".mysql_real_escape_string($edit_pla)."',
												@ifleet = '".mysql_real_escape_string($edit_fleet)."'";
								$sql2 = "SELECT	COALESCE(s1.sfj, 0) - COALESCE(f.ja, 0),
												COALESCE(s1.sfb, 0) - COALESCE(f.bo, 0), 
												COALESCE(s1.sff, 0) - COALESCE(f.fr, 0), 
												COALESCE(s1.sfz, 0) - COALESCE(f.ze, 0), 
												COALESCE(s1.sfkr, 0) - COALESCE(f.kr, 0), 
												COALESCE(s1.sfsa, 0) - COALESCE(f.sc, 0), 
												COALESCE(s1.sft, 0) - COALESCE(f.tr, 0), 
												COALESCE(s1.sfka, 0) - COALESCE(f.cl, 0), 
												COALESCE(s1.sfsu, 0) - COALESCE(f.ca, 0)
								FROM gn4scans s1
								LEFT JOIN gn4massinc_zuweisung zw ON zw.project_fk = @project AND zw.atter_gal = @igal AND zw.atter_pla = @ipla AND fleet_id <> @ifleet
								LEFT JOIN gn4massinc_fleets f ON f.project_fk = zw.project_fk AND f.atter_gal = zw.atter_gal AND f.atter_pla = zw.atter_pla AND f.fleet = zw.fleet_id
								WHERE s1.rg = @igal AND s1.rp = @ipla AND s1.type = 1";
								if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)), 'verfuegbare flotte');
								tic_mysql_query($sql1, __FILE__, __LINE__);
								$tmp = tic_mysql_query($sql2, __FILE__, __LINE__);
								$fleet_ja = $fleet_bo = $fleet_fr = $fleet_ze = $fleet_kr = $fleet_sc = $fleet_tr = $fleet_cl = $fleet_ca = 0;
								if(mysql_num_rows($tmp) > 0) {
									list($fleet_ja, $fleet_bo, $fleet_fr, $fleet_ze, $fleet_kr, $fleet_sc, $fleet_tr, $fleet_cl, $fleet_ca) = mysql_fetch_row($tmp);
								}
								
								//edit
								echo '<form action="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&zuweisung=1&koord='.postOrGet('koord').'" method="post">';
								echo '<input type="hidden" name="edit_gal" value="'.$zw_atter_gal.'"/>';
								echo '<input type="hidden" name="edit_pla" value="'.$zw_atter_pla.'"/>';
								echo '<input type="hidden" name="edit_dest_gal" value="'.$koords_g.'"/>';
								echo '<input type="hidden" name="edit_dest_pla" value="'.$koords_p.'"/>';
								echo '<input type="hidden" name="edit_fleet" value="'.$zw_fleet_id.'"/>';
								echo '				<tr class="fieldnormal'.($color2 ? 'light' : 'dark').'" style="background-color: #dddd99">';
								echo '					<td>&nbsp;'.$zw_atter_gal.':'.$zw_atter_pla.'&nbsp;</td>';
								echo '					<td colspan="4">&nbsp;'.$zw_name.'&nbsp;</td>';
								echo '					<td>&nbsp;#'.$zw_fleet_id.'&nbsp;</td>';
								echo '					<td>&nbsp;<select name="edit_relative_start">';
								for($i = 0; $i < 6; $i++) {
									echo '					<option value="'.$i.'"'.($zw_relative_starttick == $i ? ' selected="selected"' : '').'>+ '.($i*15).'min</option>';
								}
								echo '						</select>&nbsp;</td>';
								echo '					<td>&nbsp;'.(is_null($fleet_ja) ? '-' : ZahlZuText($fleet_ja)).'&nbsp;</td>';
								echo '					<td>&nbsp;'.(is_null($fleet_bo) ? '-' : ZahlZuText($fleet_bo)).'&nbsp;</td>';
								echo '					<td>&nbsp;'.(is_null($fleet_fr) ? '-' : ZahlZuText($fleet_fr)).'&nbsp;</td>';
								echo '					<td>&nbsp;'.(is_null($fleet_ze) ? '-' : ZahlZuText($fleet_ze)).'&nbsp;</td>';
								echo '					<td>&nbsp;'.(is_null($fleet_kr) ? '-' : ZahlZuText($fleet_kr)).'&nbsp;</td>';
								echo '					<td>&nbsp;'.(is_null($fleet_sc) ? '-' : ZahlZuText($fleet_sc)).'&nbsp;</td>';
								echo '					<td>&nbsp;'.(is_null($fleet_tr) ? '-' : ZahlZuText($fleet_tr)).'&nbsp;</td>';
								echo '					<td>&nbsp;'.(is_null($fleet_cl) ? '-' : ZahlZuText($fleet_cl)).'&nbsp;</td>';
								echo '					<td>&nbsp;'.(is_null($fleet_ca) ? '-' : ZahlZuText($fleet_ca)).'&nbsp;</td>';
								echo '					<td colspan="2">&nbsp;
															<a href="#" onclick="document.getElementById(\'e0\').value=\''.$fleet_ja.'\';
																document.getElementById(\'e1\').value=\''.$fleet_bo.'\';
																document.getElementById(\'e2\').value=\''.$fleet_fr.'\';
																document.getElementById(\'e3\').value=\''.$fleet_ze.'\';
																document.getElementById(\'e4\').value=\''.$fleet_kr.'\';
																document.getElementById(\'e5\').value=\''.$fleet_sc.'\';
																document.getElementById(\'e6\').value=\''.$fleet_tr.'\';
																document.getElementById(\'e7\').value=\''.$fleet_cl.'\';
																document.getElementById(\'e8\').value=\''.$fleet_ca.'\';">&raquo; alles</a>&nbsp;</td>';
								echo '				</tr>';
								echo '				<tr class="fieldnormal'.($color2 ? 'light' : 'dark').'" style="background-color: #dddd99">';
								echo '					<td rowspan="2" bgcolor="white"></td>';
								echo '					<td rowspan="2" colspan="6" style="font-style: italic">&nbsp;'.ZahlZuText($zw_pkt).' Pkt&nbsp;</td>';
								echo '					<td>&nbsp;<input type="text" size="6" id="e0" name="edit_f[0]" value="'.$zw_fja.'"/>&nbsp;</td>';
								echo '					<td>&nbsp;<input type="text" size="6" id="e1" name="edit_f[1]" value="'.$zw_fbo.'"/>&nbsp;</td>';
								echo '					<td>&nbsp;<input type="text" size="6" id="e2" name="edit_f[2]" value="'.$zw_ffr.'"/>&nbsp;</td>';
								echo '					<td>&nbsp;<input type="text" size="6" id="e3" name="edit_f[3]" value="'.$zw_fze.'"/>&nbsp;</td>';
								echo '					<td>&nbsp;<input type="text" size="6" id="e4" name="edit_f[4]" value="'.$zw_fkr.'"/>&nbsp;</td>';
								echo '					<td>&nbsp;<input type="text" size="6" id="e5" name="edit_f[5]" value="'.$zw_fsc.'"/>&nbsp;</td>';
								echo '					<td>&nbsp;<input type="text" size="6" id="e6" name="edit_f[6]" value="'.$zw_ftr.'"/>&nbsp;</td>';
								echo '					<td>&nbsp;<input type="text" size="6" id="e7" name="edit_f[7]" value="'.$zw_fcl.'"/>&nbsp;</td>';
								echo '					<td>&nbsp;<input type="text" size="6" id="e8" name="edit_f[8]" value="'.$zw_fca.'"/>&nbsp;</td>';
								echo '					<td colspan="2">&nbsp;
															<a href="#" onclick="document.getElementById(\'e0\').value=\'\';
																document.getElementById(\'e1\').value=\'\';
																document.getElementById(\'e2\').value=\'\';
																document.getElementById(\'e3\').value=\'\';
																document.getElementById(\'e4\').value=\'\';
																document.getElementById(\'e5\').value=\'\';
																document.getElementById(\'e6\').value=\'\';
																document.getElementById(\'e7\').value=\'\';
																document.getElementById(\'e8\').value=\'\';">&raquo; leeren</a>&nbsp;</td>';
								echo '				</tr>';
								echo '				<tr class="fieldnormal'.($color2 ? 'light' : 'dark').'" style="background-color: #dddd99">';
								echo '					<td colspan="9" align="left"><table border="0"><tr><td valign="top">&nbsp;Anmerkungen:&nbsp;</td><td><textarea style="font-family: courier new; font-size: 9pt" name="edit_kommentar" rows="1" cols="50">'.$zw_kommentar.'</textarea></td></tr></table></td>';
								echo '					<td colspan="2">&nbsp;<input type="submit" name="edit" value="speichern"/>&nbsp;</td>';
								echo '				</tr>';
								echo '</form>';
							} else {
								//display
								echo '				<tr class="fieldnormal'.($color2 ? 'light' : 'dark').'">';
								echo '					<td>&nbsp;'.$zw_atter_gal.':'.$zw_atter_pla.'&nbsp;</td>';
								echo '					<td colspan="4">&nbsp;'.$zw_name.'&nbsp;</td>';
								echo '					<td>&nbsp;#'.$zw_fleet_id.'&nbsp;</td>';
								echo '					<td>&nbsp;+'.($zw_relative_starttick * 15).'min&nbsp;</td>';
								echo '					<td>&nbsp;'.ZahlZuText($zw_fja).'&nbsp;</td>';
								echo '					<td>&nbsp;'.ZahlZuText($zw_fbo).'&nbsp;</td>';
								echo '					<td>&nbsp;'.ZahlZuText($zw_ffr).'&nbsp;</td>';
								echo '					<td>&nbsp;'.ZahlZuText($zw_fze).'&nbsp;</td>';
								echo '					<td>&nbsp;'.ZahlZuText($zw_fkr).'&nbsp;</td>';
								echo '					<td>&nbsp;'.ZahlZuText($zw_fsc).'&nbsp;</td>';
								echo '					<td>&nbsp;'.ZahlZuText($zw_ftr).'&nbsp;</td>';
								echo '					<td>&nbsp;'.ZahlZuText($zw_fcl).'&nbsp;</td>';
								echo '					<td>&nbsp;'.ZahlZuText($zw_fca).'&nbsp;</td>';
								echo '					<td rowspan="2" width="60">&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&zuweisung=1&koord='.postOrGet('koord').'&edit_gal='.$zw_atter_gal.'&edit_pla='.$zw_atter_pla.'&edit_fleet='.$zw_fleet_id.'" title="&auml;ndern">&raquo; edit</a>&nbsp;</td>';
								echo '					<td rowspan="2" width="70">&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&zuweisung=1&koord='.postOrGet('koord').'&del_gal='.$zw_atter_gal.'&del_pla='.$zw_atter_pla.'&del_fleet='.$zw_fleet_id.'" title="l&ouml;schen">&raquo; l&ouml;schen</a>&nbsp;</td>';
								echo '				</tr>';
								echo '				<tr class="fieldnormal'.($color2 ? 'light' : 'dark').'" style="font-style: italic">';
								echo '					<td bgcolor="white"></td><td colspan="6">&nbsp;'.ZahlZuText($zw_pkt).' Pkt&nbsp;</td>';
								echo '					<td colspan="9" align="left" style="font-family: courier new">&nbsp;'.htmlspecialchars($zw_kommentar).'&nbsp;</td>';
								echo '				</tr>';
							}
						}
						echo '				</tr><tr style="font-weight: bold; background-color: white"><td colspan="16" height="5"></td></tr>';

						//verfügbare flotten
						//SUMME
						$sql2 = "SELECT 
									SUM(COALESCE(s1.sfj, 0) - COALESCE(f.ja, 0)), 
									SUM(COALESCE(s1.sfb, 0) - COALESCE(f.bo, 0)), 
									SUM(COALESCE(s1.sff, 0) - COALESCE(f.fr, 0)), 
									SUM(COALESCE(s1.sfz, 0) - COALESCE(f.ze, 0)), 
									SUM(COALESCE(s1.sfkr, 0) - COALESCE(f.kr, 0)), 
									SUM(COALESCE(s1.sfsa, 0) - COALESCE(f.sc, 0)), 
									SUM(COALESCE(s1.sft, 0) - COALESCE(f.tr, 0)), 
									SUM(COALESCE(s1.sfka, 0) - COALESCE(f.cl, 0)), 
									SUM(COALESCE(s1.sfsu, 0) - COALESCE(f.ca, 0))
							FROM gn4massinc_atter_willing aw
							JOIN gn4massinc_atter a ON a.project_fk = aw.project_fk AND a.gal = aw.atter_gal AND a.pla = aw.atter_pla
							LEFT JOIN gn4massinc_fleets f 
								ON f.project_fk = aw.project_fk AND f.atter_gal = aw.atter_gal AND f.atter_pla = aw.atter_pla
									AND NOT EXISTS(
										SELECT * FROM gn4massinc_zuweisung zw WHERE zw.project_fk = @project AND zw.fleet_id = f.fleet AND zw.atter_gal = aw.atter_gal AND zw.atter_pla = aw.atter_pla
									)
							LEFT JOIN gn4scans s1 ON s1.rg = aw.atter_gal AND s1.rp = aw.atter_pla AND s1.type = 1
							WHERE aw.willing = 1 AND aw.project_fk = @project AND aw.welle = @welle AND 
								a.off_fleets > (
									SELECT COUNT(*) FROM gn4massinc_zuweisung zw WHERE zw.project_fk = @project AND zw.atter_gal = aw.atter_gal AND zw.atter_pla = aw.atter_pla
								)";
						if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)));
						tic_mysql_query($sql1, __FILE__, __LINE__);
						list($sum_ja, $sum_bo, $sum_fr, $sum_ze, $sum_kr, $sum_sc, $sum_tr, $sum_cl, $sum_ca) = mysql_fetch_row(tic_mysql_query($sql2, __FILE__, __LINE__));
						echo '				<tr>';
						echo '					<td colspan="16" class="datatablehead">&nbsp;Verf&uuml;gbar&nbsp;</td>';
						echo '				</tr>';
						echo '				<tr style="font-weight: bold;" class="fieldnormaldark">';
						echo '					<td rowspan="2" colspan="7" bgcolor="white">&nbsp;<!--7 Flotten&nbsp;<br/>&nbsp;4 Spieler-->&nbsp;</td>';
						echo '					<td>&nbsp;Ja&nbsp;</td>';
						echo '					<td>&nbsp;Bo&nbsp;</td>';
						echo '					<td>&nbsp;Fr&nbsp;</td>';
						echo '					<td>&nbsp;Ze&nbsp;</td>';
						echo '					<td>&nbsp;Kr&nbsp;</td>';
						echo '					<td>&nbsp;Sc&nbsp;</td>';
						echo '					<td>&nbsp;Tr&nbsp;</td>';
						echo '					<td>&nbsp;Cl&nbsp;</td>';
						echo '					<td>&nbsp;Ca&nbsp;</td>';
						echo '				</tr>';
						echo '				<tr style="font-weight: bold;" class="fieldnormallight">';
						echo '					<td>&nbsp;'.ZahlZuText($sum_ja).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_bo).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_fr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_ze).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_kr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_sc).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_tr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_cl).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_ca).'&nbsp;</td>';
						echo '				</tr><tr style="font-weight: bold;" class="fieldnormaldark"><td colspan="16" height="5"></td></tr>';
						
						//pro spieler
						$sql2 = "SELECT 
									s.spieler_name, 
									s.spieler_punkte, 
									aw.atter_gal, 
									aw.atter_pla, 
									a.off_fleets - (SELECT COUNT(*) FROM gn4massinc_zuweisung zw WHERE zw.project_fk = @project AND zw.atter_gal = aw.atter_gal AND zw.atter_pla = aw.atter_pla) fleets,
									s1.id, UNIX_TIMESTAMP(STR_TO_DATE(s1.zeit, '%H:%i %d.%m.%Y')) ezeit, 
									COALESCE(s1.sfj, 0) - COALESCE(f.ja, 0), 
									COALESCE(s1.sfb, 0) - COALESCE(f.bo, 0), 
									COALESCE(s1.sff, 0) - COALESCE(f.fr, 0), 
									COALESCE(s1.sfz, 0) - COALESCE(f.ze, 0), 
									COALESCE(s1.sfkr, 0) - COALESCE(f.kr, 0), 
									COALESCE(s1.sfsa, 0) - COALESCE(f.sc, 0), 
									COALESCE(s1.sft, 0) - COALESCE(f.tr, 0), 
									COALESCE(s1.sfka, 0) - COALESCE(f.cl, 0), 
									COALESCE(s1.sfsu, 0) - COALESCE(f.ca, 0)
							FROM gn4massinc_atter_willing aw
							JOIN gn4massinc_atter a ON a.project_fk = aw.project_fk AND a.gal = aw.atter_gal AND a.pla = aw.atter_pla
							LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = aw.atter_gal AND s.spieler_planet = aw.atter_pla
							LEFT JOIN gn4scans s1 ON s1.rg = aw.atter_gal AND s1.rp = aw.atter_pla AND s1.type = 1
							LEFT JOIN gn4massinc_zuweisung xz ON xz.project_fk = aw.project_fk AND xz.atter_gal = aw.atter_gal AND xz.atter_pla = aw.atter_pla
							LEFT JOIN gn4massinc_fleets f ON f.project_fk = aw.project_fk AND f.atter_gal = aw.atter_gal AND f.atter_pla = aw.atter_pla AND xz.fleet_id = f.fleet
							WHERE aw.willing = 1 AND aw.project_fk = @project AND aw.welle = @welle AND
								a.off_fleets > (SELECT COUNT(*) FROM gn4massinc_zuweisung zw WHERE zw.project_fk = @project AND zw.welle = @welle AND zw.atter_gal = aw.atter_gal AND zw.atter_pla = aw.atter_pla)
							HAVING fleets > 0
							ORDER BY aw.atter_gal, aw.atter_pla";
						if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)));
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$verfuegbar = tic_mysql_query($sql2, __FILE__, __LINE__);
						if(mysql_num_rows($verfuegbar) == 0) {
							echo '<tr class="fieldnormallight"><td colspan="16">&nbsp;<i>keine</i></td></tr>';
						}
						$color2 = false;
						while(list($zw_name, $zw_pkt, $zw_atter_gal, $zw_atter_pla, $zw_fleets, $zw_fid, $zw_ft, $zw_fja, $zw_fbo, $zw_ffr, $zw_fze, $zw_fkr, $zw_fsc, $zw_ftr, $zw_fcl, $zw_fca) = mysql_fetch_row($verfuegbar))
						{
							$color2 = !$color2;
							echo '				<tr class="fieldnormal'.($color2 ? 'light' : 'dark').'">';
							echo '					<td colspan="4" align="left" title="'.ZahlZuText($zw_pkt).' Pkt">&nbsp;'.$zw_atter_gal.':'.$zw_atter_pla.' '.$zw_name.'&nbsp;</td>';
							echo '					<td colspan="3">&nbsp;'.($zw_fleets - $zw_verplant).' Flotte(n) <a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&zuweisung=1&koord='.$koords_g.':'.$koords_p.'&editfleets=1&gal='.$zw_atter_gal.'&pla='.$zw_atter_pla.'" target="_self">&raquo; &auml;ndern</a>&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fja).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fbo).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_ffr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fze).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fkr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fsc).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_ftr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fcl).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fca).'&nbsp;</td>';
							echo '					<td colspan="2">&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&zuweisung=1&koord='.postOrGet('koord').'&add_gal='.$zw_atter_gal.'&add_pla='.$zw_atter_pla.'" title="zuweisen" '.($zw_pkt > 4*$pkt ? 'onclick="return confirm(\''.$zw_name.' hat mehr als die vierfache Punktezahl! Bist Du Dir sicher?\')"' : '').'>&raquo; hinzuf&uuml;gen</a>&nbsp;</td>';
							echo '				</tr>';
						}
						
						echo '				</tr><tr style="font-weight: bold; background-color: white"><td colspan="16" height="5"></td></tr>';
						
						//woanders zugewiesen
						$sql2 = "SELECT 
								SUM(f.ja), SUM(f.bo), SUM(f.fr), SUM(f.ze), SUM(f.kr), SUM(f.sc), SUM(f.tr), SUM(f.cl), SUM(f.ca)
							FROM gn4massinc_zuweisung zw
							JOIN gn4massinc_fleets f ON f.project_fk = zw.project_fk AND f.atter_gal = zw.atter_gal AND f.atter_pla = zw.atter_pla AND f.fleet = zw.fleet_id
							LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = zw.dest_gal AND s.spieler_planet = zw.dest_pla
							WHERE CONCAT_WS(':', zw.dest_gal, zw.dest_pla) <> CONCAT_WS(':', @gal, @pla) AND zw.project_fk = @project";
						if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)), "zugewiesen");
						tic_mysql_query($sql1, __FILE__, __LINE__);
						list($sum_ja, $sum_bo, $sum_fr, $sum_ze, $sum_kr, $sum_sc, $sum_tr, $sum_cl, $sum_ca) = mysql_fetch_row(tic_mysql_query($sql2, __FILE__, __LINE__));
						echo '				<tr>';
						echo '					<td colspan="16" class="datatablehead">&nbsp;Woanders zugewiesen&nbsp;</td>';
						echo '				</tr>';
						echo '				<tr style="font-weight: bold;" class="fieldnormaldark">';
						echo '					<td rowspan="2" colspan="7" bgcolor="white">&nbsp;<!--7 Flotten&nbsp;<br/>&nbsp;4 Spieler-->&nbsp;</td>';
						echo '					<td>&nbsp;Ja&nbsp;</td>';
						echo '					<td>&nbsp;Bo&nbsp;</td>';
						echo '					<td>&nbsp;Fr&nbsp;</td>';
						echo '					<td>&nbsp;Ze&nbsp;</td>';
						echo '					<td>&nbsp;Kr&nbsp;</td>';
						echo '					<td>&nbsp;Sc&nbsp;</td>';
						echo '					<td>&nbsp;Tr&nbsp;</td>';
						echo '					<td>&nbsp;Cl&nbsp;</td>';
						echo '					<td>&nbsp;Ca&nbsp;</td>';
						echo '				</tr>';
						echo '				<tr style="font-weight: bold;" class="fieldnormallight">';
						echo '					<td>&nbsp;'.ZahlZuText($sum_ja).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_bo).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_fr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_ze).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_kr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_sc).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_tr).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_cl).'&nbsp;</td>';
						echo '					<td>&nbsp;'.ZahlZuText($sum_ca).'&nbsp;</td>';
						echo '				</tr><tr style="font-weight: bold;" class="fieldnormaldark"><td colspan="16" height="5"></td></tr>';
						
						$sql2 = "SELECT 
								zw.welle, s.spieler_name, zw.atter_gal, zw.atter_pla, zw.dest_gal, zw.dest_pla, zw.fleet_id,
								f.ja, f.bo, f.fr, f.ze, f.kr, f.sc, f.tr, f.cl, f.ca
							FROM gn4massinc_zuweisung zw
							JOIN gn4massinc_fleets f ON f.project_fk = zw.project_fk AND f.atter_gal = zw.atter_gal AND f.atter_pla = zw.atter_pla AND f.fleet = zw.fleet_id
							LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = zw.atter_gal AND s.spieler_planet = zw.atter_pla
							WHERE CONCAT_WS(':', zw.dest_gal, zw.dest_pla) <> CONCAT_WS(':', @gal, @pla) AND zw.project_fk = @project
							ORDER BY zw.atter_gal, zw.atter_pla, zw.fleet_id";

						if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)));
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$woanders = tic_mysql_query($sql2, __FILE__, __LINE__);
						if(mysql_num_rows($woanders) == 0) {
							echo '<tr class="fieldnormallight"><td colspan="16">&nbsp;<i>keine</i></td></tr>';
						}
						$color2 = false;
						while(list($zw_welle, $zw_name, $zw_atter_gal, $zw_atter_pla, $zw_dest_gal, $zw_dest_pla, $zw_fleetid, $zw_fja, $zw_fbo, $zw_ffr, $zw_fze, $zw_fkr, $zw_fsc, $zw_ftr, $zw_fcl, $zw_fca) = mysql_fetch_row($woanders))
						{
							$color2 = !$color2;
							echo '				<tr class="fieldnormal'.($color2 ? 'light' : 'dark').'">';
							echo '					<td colspan="5" align="left" title="'.ZahlZuText($zw_pkt).' Pkt">&nbsp;'.$zw_atter_gal.':'.$zw_atter_pla.' '.$zw_name.'&nbsp;</td>';
							echo '					<td>&nbsp;Welle #'.$zw_welle.'&nbsp;</td>';
							echo '					<td>&nbsp;#'.$zw_fleetid.'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fja).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fbo).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_ffr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fze).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fkr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fsc).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_ftr).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fcl).'&nbsp;</td>';
							echo '					<td>&nbsp;'.ZahlZuText($zw_fca).'&nbsp;</td>';
							echo '					<td>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$zw_welle.'&zuweisung=1&koord='.$zw_dest_gal.':'.$zw_dest_pla.'" title="Info">&raquo; ' . $zw_dest_gal . ':' . $zw_dest_pla . '</a>&nbsp;</td>';
							echo '					<td>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&zuweisung=1&koord='.postOrGet('koord').'&del_gal='.$zw_atter_gal.'&del_pla='.$zw_atter_pla.'&del_fleet='.$zw_fleetid.'" title="l&ouml;schen">&raquo; l&ouml;schen</a>&nbsp;</td>';
							echo '				</tr>';
						}
						
						echo '			</table>';
						echo '		</td>';
						echo '	</tr>';
					}//spieler gewählt
				}//first
				
				echo '	</tr>';
			}
			
			echo '	<tr>';
			echo '		<td colspan="5">&nbsp;</td>';
			echo '	</tr>';
			echo '</table>';
			
			
			$donotshowdestinations = true;
		} else {
			//show wave info
			$sql = 'SELECT project_fk, t, kommentar, id FROM gn4massinc_wellen WHERE project_fk = "'.mysql_real_escape_string($project).'" AND id = "'.mysql_real_escape_string($wave).'"';
			$res = tic_mysql_query($sql, __FILE__, __LINE__);
			$num = mysql_num_rows($res);
			list($project, $t, $kommentar, $id) = mysql_fetch_row($res);

			if($num == 0) {
				showError('Welle nicht gefunden.');
			} else {
				echo '<table>';
				echo '<tr class="datatablehead">';
				echo '	<td width="160">&nbsp;<a href="main.php?modul=massinc&project='.$project.'">&laquo; Zum Projekt</a>&nbsp;</td>';
				echo '	<td colspan="2">&nbsp;Welle: #'.$id.' '.date('Y-m-d H:i', $t).'&nbsp;</td>';
				echo '<td>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.($tab_ziele ? '&tab_ziele=1' : '').'">&raquo; Refresh</a>&nbsp;</td>';
				echo '</tr>';
				echo '<tr>';
				echo '	<td align="left" class="fieldnormaldark" valign="top" width="200">';

				//waves
				$sql1 = "SET @project = '".mysql_real_escape_string($project)."', @g = '".$Benutzer['galaxie']."', @p = '".$Benutzer['planet']."';";
				$sql2 = "SELECT w.t, count(z.project_fk) flotten, id
							FROM gn4massinc_wellen w
							LEFT JOIN gn4massinc_zuweisung z ON z.welle = w.id AND z.project_fk = z.project_fk AND z.atter_gal = @g AND z.atter_pla = @p
							WHERE w.project_fk = @project
							GROUP BY w.t
							ORDER BY w.t ASC";
				//if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
				tic_mysql_query($sql1, __FILE__, __LINE__);
				$res = tic_mysql_query($sql2, __FILE__, __LINE__);
				while(list($t, $flotten, $id) = mysql_fetch_row($res)) {
					if($flotten > 0) {
						echo '<b>';
					}

					$mode = postOrGet('besoffski');

					if($id == $wave) echo '<table style="background-color: #cccc88; border: 1px dashed red"><tr><td width="200">';
					echo '&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$id.'">&raquo; '.date('Y-m-d H:i', $t).'</a>&nbsp;<br>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a '.($mode==2 ? 'style="color: red" ' : '').'href="main.php?modul=massinc&project='.$project.'&wave='.$id.'&besoffski=2">&raquo; Cockpit Besoffski</a><br/>';
					//echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a '.($mode==1 ? 'style="color: red" ' : '').'href="main.php?modul=massinc&project='.$project.'&wave='.$id.'&besoffski=1">&raquo; Cockpit</a><br/>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a '.((!$mode && !$tab_ziele) ? 'style="color: red" ' : '').'href="main.php?modul=massinc&project='.$project.'&wave='.$id.'">&raquo; Cockpit Details</a><br/>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a '.($tab_ziele ? 'style="color: red" ' : '').'href="main.php?modul=massinc&project='.$project.'&wave='.$id.'&tab_ziele=1">&raquo; Zielinformationen</a><br/>';
					echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$id.'&getical=1">&raquo; Kalendereintrag</a><br/>';
					
					if($id == $wave) echo '</td></tr></table>';
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
							WHERE z.atter_gal = @g AND z.atter_pla = @p AND z.welle = @w ORDER BY z.welle + z.relative_starttick*15*60";
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
						echo '		<td class="fieldnormallight"rowspan="2" width="120">&nbsp;<a href="main.php?modul=showgalascans&xgala='.$g.'&xplanet='.$p.'&displaytype=0">&raquo; Scans</a>&nbsp;<br/>&nbsp;<a href="https://gntic.de/x/player.php?name='.$name.'" target="_blank">&raquo; Punkteverlauf</a>&nbsp;</td>';
						echo '		<td rowspan="10" valign="top">';

						echo '<table width="100%">';
						echo '<tr class="fieldnormaldark" style="font-weight: bold;">';
						$sql1 = "SET @gal = '".mysql_real_escape_string($g)."', @pla = '".mysql_real_escape_string($p)."';";
						$sql2 = "SELECT id, t, genauigkeit FROM gn4scans_news WHERE ziel_g = @gal AND ziel_p = @pla ORDER BY t DESC LIMIT 1";
						if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$res2 = tic_mysql_query($sql2, __FILE__, __LINE__);
						$num2 = mysql_num_rows($res2);
						
						if(time() - mysql_result($res2, 0, 't') > 270*60 - 10*15*60) {
							echo '<td class="fieldnormallight">&nbsp;<a href="'.makeRequestScanLink($g, $p, 4, 'modul=massinc&project='.$project.'&wave='.$wave.'&tab_ziele=1').'" style="font-weight: bold;">&raquo; Jetzt News beantragen!</a>&nbsp;</td></tr><tr>';
						}
						
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
								if(in_array_contains(array('Spende', 'Tauschangebot', 'Angriffsbericht', 'Verteidigungsbericht', 'Artilleriebeschuss', 'Artilleriesysteme', 'Galaxie-Abgabensatz'), $ntyp)) {
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
						echo '		<td bgcolor="#ddddfd" colspan="6" style="font-weight: bold; color: red">&nbsp;'.$g.':'.$p.' - '.$name.'&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormaldark" style="font-weight: bold">';
						echo '		<td colspan="2">&nbsp;Punkte&nbsp;</td>';
						echo '		<td>&nbsp;Schiffe&nbsp;</td>';
						echo '		<td>&nbsp;Defensiv&nbsp;</td>';
						echo '		<td>&nbsp;Exen Kristall&nbsp;</td>';
						echo '		<td>&nbsp;Exen Metall&nbsp;</td>';
						echo '		<td title="Fordere Scans per Slack an." width="100" rowspan="2" style="font-weight: normal">&nbsp;<b>Scananfrage:</b>&nbsp;<br/>
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
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;J&auml;ger&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfja).'&nbsp;</td>';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Bomber&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfbo).'&nbsp;</td>';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Fregatte&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sffr).'&nbsp;</td>';
						echo '		<td rowspan="3" class="fieldnormaldark" title="Simuliere Deinen Angriff samt Mitstreitern und ggf. gescannten Deffern.">&nbsp;'.createSimuLink($project, $wave, $g, $p, '<span style="color: red; font-weight: bold;">&raquo; Simulieren</span>').'&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormallight">';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Zerst&ouml;rer&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfze).'&nbsp;</td>';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Kreuzer&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfkr).'&nbsp;</td>';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Schlachtschiff&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfsc).'&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormallight">';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Tr&auml;ger&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sftr).'&nbsp;</td>';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Cleptor&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfcl).'&nbsp;</td>';
						echo '		<td style="font-weight: bold" bgcolor="#ddddfd">&nbsp;Cancri&nbsp;</td>';
						echo '		<td>&nbsp;'.ZahlZuText($sfca).'&nbsp;</td>';
						echo '	</tr>';
						echo '<tr class="fieldnormaldark" style="height: 5px"><td colspan="9"></td></tr>';
					}//while ziele
					echo '</table>';

				} else {
					$mode = postOrGet('besoffski');
					
					if($mode == 2) {

						$sql1 = 'SET @proj = "'.mysql_real_escape_string($project).'", @welle = "'.mysql_real_escape_string($wave).'", @refgal = "'.$Benutzer['galaxie'].'", @refpla="'.$Benutzer['planet'].'";';
						tic_mysql_query($sql1, __FILE__, __LINE__);
						$sql2 = 'SELECT w.t, z.dest_gal, z.dest_pla, z.fleet_id, z.kommentar, z.relative_starttick, u.spieler_name, u.spieler_punkte, u.allianz_name
								FROM gn4massinc_zuweisung z
								LEFT JOIN gn4massinc_wellen w ON w.project_fk = z.project_fk AND z.welle = w.id
								LEFT JOIN gn4massinc_fleets f ON f.project_fk = z.project_fk AND z.fleet_id = f.fleet AND z.atter_gal = f.atter_gal AND z.atter_pla = f.atter_pla
								LEFT JOIN gn_spieler2 u ON u.spieler_galaxie = z.dest_gal AND u.spieler_planet = z.dest_pla
								LEFT JOIN gn4scans s ON s.rg = @refgal AND s.rp = @refpla AND s.type = 2
								WHERE z.project_fk = @proj AND z.welle = @welle AND z.atter_gal = @refgal AND z.atter_pla = @refpla ORDER BY z.welle + z.relative_starttick * 15 * 60';
						if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
						$res = tic_mysql_query($sql2, __FILE__, __LINE__);
						$color = true;

						$fleets = null;
						$tmp_t;
						while(list($t, $dest_g, $dest_p, $fleetid, $kommentar, $relative_start, $name, $pkt, $ally
									) = mysql_fetch_row($res)) {
								$tmp_t = $t;
								$fleets[$dest_g . ':' . $dest_p][] = array(
									't' => $t,
									'dest_g' => $dest_g,
									'dest_p' => $dest_p,
									'fleetid' => $fleetid,
									'comment' => $kommentar,
									'relative_start' => $relative_start,
									'ziel_name' => $name,
									'ziel_punkte' => $pkt,
									'ziel_ally' => $ally
									);
						}
						
						$gone = $tmp_t < time();
						if($gone) {
							echo '<p style="font-size: 15px">Der Start liegt in der Vergangenheit. <a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&tab_ziele=1" style="font-weight: bold;">&raquo; HIER</a> geht es zu den Zielinformationen (News).</p>';
						}
						
						echo '<div onclick="location.href=\'main.php?modul=massinc&project='.$project.'&wave='.$wave.'\'" style="'.($gone ? 'opacity: 0.5;' : '').'"><br/><br/>';
						if(count($fleets) == 0) {
							echo 'keine Starts geplant.';
						} else {
							$timer = array();
							$timer_num = 0;
							foreach($fleets as $ziel) {
								$circle_dimension = '300';
								//grafik
								echo '<div style="position: relative; left: 10px;">';
								echo '	<svg xmlns="http://www.w3.org/2000/svg" style="width: 900px; height: ' . ($circle_dimension+4) . 'px; z-index: 1; position: absolute; top: 0px; left: 0px">';
								echo '  <defs>';
								echo '    <linearGradient id="linear" x1="0%" y1="0%" x2="100%" y2="0%">';
								echo '      <stop offset="0%"   stop-color="rgb(255,50,50)"/>';
								echo '      <stop offset="100%" stop-color="rgb(255,255,100)"/>';
								echo '    </linearGradient>';
								echo '  </defs>';
								echo '		<rect x="'.($circle_dimension/2).'" y="20" width="600" height="'.($circle_dimension/2 - 40).'" stroke="red" fill="url(#linear)"/>';
								
								if(count($ziel) == 2) {
									echo '		<rect x="'.($circle_dimension/2).'" y="'.($circle_dimension/2+20).'" width="600" height="'.($circle_dimension/2 - 40).'" stroke="red" fill="url(#linear)"/>';
								}

								echo '		<circle cx="'.($circle_dimension/2+2).'" cy="'.($circle_dimension/2+2).'" r="'.($circle_dimension/2).'" fill="rgb(255,100,100)" stroke="red" />';
								echo '	</svg>';
								//ziel
								echo '	<div style="width: '.$circle_dimension.'px; text-align: center; z-index: 2; position: absolute; top: '.($circle_dimension/2 - 60).'px; left: 0px;">';
								echo '		<p style="font-size: 25px; font-weight: bold;">'.$ziel[0]['dest_g'].':'.$ziel[0]['dest_p'].'<br/>';
								echo '			' . $ziel[0]['ziel_name'];
								echo '		</p>';
								echo '		'.ZahlZuText($ziel[0]['ziel_punkte']).' Punkte<br/>';
								echo '		'.$ziel[0]['ziel_ally'].'<br/>';
								echo '	</div>';

								//flotten
								$tickstart = (floor($ziel[0]['t'] / 60 / 15) + $ziel[0]['relative_start']) * 60 * 15;
								$timer[$timer_num++] = $tickstart;

								echo '	<div style="width: 350px; text-align: left; z-index: 2; position: absolute; top: '.($circle_dimension/4 - 40).'px; left: '.($circle_dimension+20).'px;">';
								echo '		<p style="font-size: 20px; font-weight: bold;">';
								echo '			Flotte #'.$ziel[0]['fleetid'].' - ab '.date('H:i', $tickstart + 60).' Uhr';
								echo '		</p>';
								echo '		<b>'.$ziel[0]['comment'].'</b>';
								echo '	</div>';
								//counter
								echo '	<div style="width: 100px; text-align: right; z-index: 2; position: absolute; top: '.($circle_dimension/4 - 50).'px; left: '.($circle_dimension+320).'px;">';
								echo '		<p style="font-size: 30px; font-weight: bold;" id="timer'.($timer_num).'">';
								if($tickstart < 0) echo '			-';
								echo '		</p>';
								echo '	</div>';
								if(count($ziel) == 2) {
									$tickstart = (floor($ziel[1]['t'] / 60 / 15) + $ziel[1]['relative_start']) * 60 * 15;
									$timer[$timer_num++] = $tickstart;

									echo '	<div style="width: 350px; text-align: left; z-index: 2; position: absolute; top: '.($circle_dimension/4*3 - 40).'px; left: '.($circle_dimension+20).'px;">';
									echo '		<p style="font-size: 20px; font-weight: bold;">';
									echo '			Flotte #'.$ziel[1]['fleetid'].' - ab '.date('H:i', $tickstart + 60).' Uhr';
									echo '		</p>';
									echo '		<b>'.$ziel[1]['comment'].'</b>';
									echo '	</div>';
									//counter
									echo '	<div style="width: 100px; text-align: right; z-index: 2; position: absolute; top: '.($circle_dimension/4*3 - 50).'px; left: '.($circle_dimension+320).'px;">';
									echo '		<p style="font-size: 30px; font-weight: bold;" id="timer'.($timer_num).'">';
									if($tickstart < 0) echo '			-';
									echo '		</p>';
									echo '	</div>';
									echo '</div>';
								}
								
								echo '<div style="width: 770px; height: 320px">&nbsp;</div>';
							}//foreach

							//timer
							echo "<script>
								window.onload = function () {
									display1 = document.querySelector('#timer1');
									display2 = document.querySelector('#timer2');";
							if(count($timer) > 0) {
								echo "startTimer(" .($timer[0] + 7.5 * 60). ", display1);";
							}
							if(count($timer) > 1) {
								echo "startTimer(" .($timer[1] + 7.5 * 60). ", display2);";
							}
							echo "};";
							echo "	</script>";
						}//#ziele > 0
						echo '</div>';
					} else if($mode == 1) {
						//cockpit
						echo '<br/><table>';
						echo '	<tr class="datatablehead">';
						echo '		<td colspan="11">&nbsp;Angriffsflotten&nbsp;</td>';
						echo '		<td>&nbsp;Ziel&nbsp;</td>';
						echo '		<td>&nbsp;Abflug&nbsp;</td>';
						echo '		<td>&nbsp;Countdown&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormaldark" style="font-weight: bold;">';
						echo '		<td>&nbsp;Spieler&nbsp;</td>';
						echo '		<td>&nbsp;Flotte&nbsp;</td>';
						echo '		<td>&nbsp;Ja&nbsp;</td>';
						echo '		<td>&nbsp;Bo&nbsp;</td>';
						echo '		<td>&nbsp;Fr&nbsp;</td>';
						echo '		<td>&nbsp;Ze&nbsp;</td>';
						echo '		<td>&nbsp;Kr&nbsp;</td>';
						echo '		<td>&nbsp;Sc&nbsp;</td>';
						echo '		<td>&nbsp;Tr&nbsp;</td>';
						echo '		<td>&nbsp;Cl&nbsp;</td>';
						echo '		<td>&nbsp;Ca&nbsp;</td>';
						echo '		<td>&nbsp;</td>';
						echo '		<td>&nbsp;</td>';
						echo '		<td>&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormallight">';
						echo '		<td>&nbsp;41:3 - derVernichter&nbsp;</td>';
						echo '		<td>&nbsp;#1&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;23:1 - Dylan Hunt&nbsp;</td>';
						echo '		<td>&nbsp;23:45 - 23:59&nbsp;</td>';
						echo '		<td>&nbsp;Timer&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormaldark">';
						echo '		<td>&nbsp;41:3 - derVernichter&nbsp;</td>';
						echo '		<td>&nbsp;#1&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;23:1 - Dylan Hunt&nbsp;</td>';
						echo '		<td>&nbsp;23:45 - 23:59&nbsp;</td>';
						echo '		<td>&nbsp;Timer&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="datatablehead">';
						echo '		<td colspan="11">&nbsp;Angriffsflotten Deiner Mitstreiter&nbsp;</td>';
						echo '		<td>&nbsp;Ziel&nbsp;</td>';
						echo '		<td>&nbsp;Abflug&nbsp;</td>';
						echo '		<td>&nbsp;Countdown&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormaldark" style="font-weight: bold;">';
						echo '		<td>&nbsp;Spieler&nbsp;</td>';
						echo '		<td>&nbsp;Flotte&nbsp;</td>';
						echo '		<td>&nbsp;Ja&nbsp;</td>';
						echo '		<td>&nbsp;Bo&nbsp;</td>';
						echo '		<td>&nbsp;Fr&nbsp;</td>';
						echo '		<td>&nbsp;Ze&nbsp;</td>';
						echo '		<td>&nbsp;Kr&nbsp;</td>';
						echo '		<td>&nbsp;Sc&nbsp;</td>';
						echo '		<td>&nbsp;Tr&nbsp;</td>';
						echo '		<td>&nbsp;Cl&nbsp;</td>';
						echo '		<td>&nbsp;Ca&nbsp;</td>';
						echo '		<td>&nbsp;</td>';
						echo '		<td>&nbsp;</td>';
						echo '		<td>&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormallight">';
						echo '		<td>&nbsp;41:3 - derVernichter&nbsp;</td>';
						echo '		<td>&nbsp;#1&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;23:1 - Dylan Hunt&nbsp;</td>';
						echo '		<td>&nbsp;23:45 - 23:59&nbsp;</td>';
						echo '		<td>&nbsp;Timer&nbsp;</td>';
						echo '	</tr>';
						echo '	<tr class="fieldnormaldark">';
						echo '		<td>&nbsp;41:3 - derVernichter&nbsp;</td>';
						echo '		<td>&nbsp;#1&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;0&nbsp;</td>';
						echo '		<td>&nbsp;23:1 - Dylan Hunt&nbsp;</td>';
						echo '		<td>&nbsp;23:45 - 23:59&nbsp;</td>';
						echo '		<td>&nbsp;Timer&nbsp;</td>';
						echo '	</tr>';
						echo '</table>';
					} else {
						//detailansicht
						echo '<br/><table>';
						echo '	<tr class="datatablehead">';
						echo '		<td colspan="4">&nbsp;Deine Flotten&nbsp;</td>';
						echo '		<td>&nbsp;</td>';
						echo '		<td colspan="3">&nbsp;Ziel&nbsp;</td>';
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
						$sql2 = 'SELECT w.t, z.atter_gal, z.atter_pla, z.dest_gal, z.dest_pla, z.fleet_id, z.kommentar, z.relative_starttick, u.name,
									f.ja, f.bo, f.fr, f.ze, f.kr, f.sc, f.tr, f.cl, f.ca,
									s.sf1j, s.sf1b, s.sf1f, s.sf1z, s.sf1kr, s.sf1sa, s.sf1t, s.sf1ka, s.sf1su,
									s.sf2j, s.sf2b, s.sf2f, s.sf2z, s.sf2kr, s.sf2sa, s.sf2t, s.sf2ka, s.sf2su
								FROM gn4massinc_zuweisung z
								LEFT JOIN gn4massinc_wellen w ON w.project_fk = z.project_fk AND z.welle = w.id
								LEFT JOIN gn4massinc_fleets f ON f.project_fk = z.project_fk AND z.fleet_id = f.fleet AND z.atter_gal = f.atter_gal AND z.atter_pla = f.atter_pla
								LEFT JOIN gn4gnuser u ON u.gala = z.dest_gal AND u.planet = z.dest_pla
								LEFT JOIN gn4scans s ON s.rg = @refgal AND s.rp = @refpla AND s.type = 2
								WHERE z.project_fk = @proj AND z.welle = @welle AND z.atter_gal = @refgal AND z.atter_pla = @refpla ORDER BY z.welle + z.relative_starttick * 15 * 60';
						if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
						$res = tic_mysql_query($sql2, __FILE__, __LINE__);
						$color = true;
						if(mysql_num_rows($res) == 0) {
							echo '<tr class="fieldnormallight"><td colspan="11">keine</td></tr>';
						}
						while(list($t, $atter_g, $atter_p, $dest_g, $dest_p, $fleetid, $kommentar, $relative_start, $name,
									$fja, $fbo, $ffr, $fze, $fkr, $fsc, $ftr, $fcl, $fca,
									$sja[1], $sbo[1], $sfr[1], $sze[1], $skr[1], $ssc[1], $str[1], $scl[1], $sca[1],
									$sja[2], $sbo[2], $sfr[2], $sze[2], $skr[2], $ssc[2], $str[2], $scl[2], $sca[2]
									) = mysql_fetch_row($res)) {
							$color = !$color;
							$tickstart = (floor($t / 60 / 15) + $relative_start) * 60 * 15;
							$startstr = date('H:i:s', $tickstart + 30) . '&nbsp;<br/>&nbsp;' . date('H:i:s', $tickstart + 30 * 60 - 1);

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
							echo '	<td style="color: red; font-weight: bold;">&nbsp;'.$dest_g.'&nbsp;</td>';
							echo '	<td style="color: red; font-weight: bold;">&nbsp;'.$dest_p.'&nbsp;</td>';
							echo '	<td style="color: red; font-weight: bold;">&nbsp;'.$name.'&nbsp;</td>';
							echo '	<td>&nbsp;<iframe src="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&wave_askonline=1&started_g='.$atter_g.'&started_p='.$atter_p.'" style="height: 16px; width: 30px; overflow:hidden; border: 1px solid darkgray" scrolling="no"></iframe>&nbsp;</td>';
							echo '	<td>&nbsp;<iframe src="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&wave_askfleet=1&started_g='.$atter_g.'&started_p='.$atter_p.'&started_f='.$fleetid.'" style="height: 16px; width: 30px; overflow:hidden; border: 1px solid darkgray" scrolling="no"></iframe>&nbsp;</td>';
							echo '	<td rowspan="3">&nbsp;<span id="timer'.$fleetid.'" style="font-size: 12pt; font-weight: bold"><br/>';
							if($fleetid == 1 && $timer1 < 0) echo 'bereits vergangen';
							if($fleetid == 2 && $timer2 < 0) echo 'bereits vergangen';
							echo '</span>&nbsp;<br/><br/>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&start_f='.$fleetid.'" title="Du kannst Deinen Flottenstart hier ins TIC eintragen.">&raquo; Korrekten Start im TIC melden</a>&nbsp;</td>';
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
							echo '	<td bgcolor="white" align="right" style="font-weight: normal">&nbsp;Flottenvorschlag:&nbsp;</td>';
							echo '	<td>&nbsp;'.($fja > 0 ? ZahlZuText($fja) : '-').'&nbsp;</td>';
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
							
							if(strlen($kommentar) > 0) {
								echo '<tr class="fieldnormaldark">';
								echo '	<td bgcolor="white">&nbsp;Anmerkung:&nbsp;</td>';
								echo '	<td colspan="9" style="font-family: Courier New" align="left">'.$kommentar.'</td>';
								echo '</tr>';
							}
						}

						echo '	<tr class="datatablehead">';
						echo '		<td colspan="4">&nbsp;Mitstreiter&nbsp;</td>';
						echo '		<td>&nbsp;</td>';
						echo '		<td colspan="3">&nbsp;Ziel&nbsp;</td>';
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
						$sql2 = 'SELECT w.t, z.atter_gal, z.atter_pla, z.dest_gal, z.dest_pla, z.fleet_id, z.kommentar, z.relative_starttick, u.name, u2.name,
									f.ja, f.bo, f.fr, f.ze, f.kr, f.sc, f.tr, f.cl, f.ca,
									s.sf1j, s.sf1b, s.sf1f, s.sf1z, s.sf1kr, s.sf1sa, s.sf1t, s.sf1ka, s.sf1su,
									s.sf2j, s.sf2b, s.sf2f, s.sf2z, s.sf2kr, s.sf2sa, s.sf2t, s.sf2ka, s.sf2su
									FROM gn4massinc_zuweisung z
									LEFT JOIN gn4massinc_fleets f ON f.project_fk = z.project_fk AND z.fleet_id = f.fleet AND z.atter_gal = f.atter_gal AND z.atter_pla = f.atter_pla
									LEFT JOIN gn4massinc_wellen w ON w.id = z.welle AND w.project_fk = z.project_fk
									LEFT JOIN gn4gnuser u ON u.gala = z.dest_gal AND u.planet = z.dest_pla
									LEFT JOIN gn4gnuser u2 ON u2.gala = z.atter_gal AND u2.planet = z.atter_pla
									LEFT JOIN gn4scans s ON s.rg = z.atter_gal AND s.rp = z.atter_pla AND s.type = 2
									WHERE z.project_fk = @proj AND z.welle = @welle AND CONCAT_WS(":", z.atter_gal, z.atter_pla) NOT LIKE CONCAT_WS(":", @refgal, @refpla)
										AND EXISTS(
											SELECT * FROM gn4massinc_zuweisung y WHERE y.project_fk = @proj AND y.atter_gal = @refgal AND y.atter_pla = @refpla AND y.dest_gal = z.dest_gal AND y.dest_pla = z.dest_pla)
									 ORDER BY z.welle + z.relative_starttick * 15 * 60, z.atter_gal, z.atter_pla, z.fleet_id';
						if($SQL_DEBUG) aprint(join("\n\n", array($sql1, $sql2)));
						$res = tic_mysql_query($sql2, __FILE__, __LINE__);
						if(mysql_num_rows($res) == 0) {
							echo '<tr class="fieldnormallight"><td colspan="10">&nbsp;keine&nbsp;</td></tr>';
						}
						$color = true;
						$num2 = mysql_num_rows($res);
						while(list($t, $atter_g, $atter_p, $dest_g, $dest_p, $fleetid, $kommentar, $relative_start, $name, $name2,
									$fja, $fbo, $ffr, $fze, $fkr, $fsc, $ftr, $fcl, $fca,
									$sja[1], $sbo[1], $sfr[1], $sze[1], $skr[1], $ssc[1], $str[1], $scl[1], $sca[1],
									$sja[2], $sbo[2], $sfr[2], $sze[2], $skr[2], $ssc[2], $str[2], $scl[2], $sca[2]
									) = mysql_fetch_row($res)) {
							$color = !$color;
							$tickstart = (floor($t / 60 / 15) + $relative_start) * 60 * 15;
							$startstr = date('H:i:s', $tickstart + 30) . '&nbsp;<br/>&nbsp;' . date('H:i:s', $tickstart + 30 * 60 - 1);

							echo '<tr class="fieldnormallight">';
							echo '	<td>&nbsp;'.$startstr.'&nbsp;</td>';
							echo '	<td>&nbsp;'.$atter_g.'&nbsp;</td>';
							echo '	<td>&nbsp;'.$atter_p.'&nbsp;</td>';
							echo '	<td>&nbsp;#'.$fleetid.'&nbsp;</td>';
							echo '	<td>&nbsp;<b>&gt;&gt;</b>&nbsp;</td>';
							echo '	<td style="color: red;">&nbsp;'.$dest_g.'&nbsp;</td>';
							echo '	<td style="color: red;">&nbsp;'.$dest_p.'&nbsp;</td>';
							echo '	<td style="color: red;">&nbsp;'.$name.'&nbsp;</td>';
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
							echo '	<td bgcolor="white" align="right">&nbsp;Flottenvorschlag:&nbsp;</td>';
							echo '	<td>&nbsp;'.($fja > 0 ? ZahlZuText($fja) : '-').'&nbsp;</td>';
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
						if($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) {
						}

						echo '</td></tr></table>';
					}
				}//tab
			}//num wave > 0

			$donotshowdestinations = true;
		}//!empty wave

		echo '<br/>';

		//edit number of off fleets for external atter
		if(($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) && postOrGet('editfleets') == 1) {
			$gal = postOrGet('gal');
			$pla = postOrGet('pla');
			
			$sql1 = "SET @project = '".mysql_real_escape_string($project)."',
							@gal = '".mysql_real_escape_string($gal)."',
							@pla = '".mysql_real_escape_string($pla)."'";
			$sql2 = "UPDATE gn4massinc_atter SET off_fleets = MOD(off_fleets, 2) + 1 WHERE project_fk = @project AND gal = @gal AND pla = @pla";
			tic_mysql_query($sql1, __FILE__, __LINE__);
			tic_mysql_query($sql2, __FILE__, __LINE__);
		}
		
		if(($Benutzer['rang'] >= $Rang_GC  || isUserCreator($project, $Benutzer['id'])) && !$donotshowdestinations) {
			//mgmt
			
			//wellen management
			if(postOrGet('atter_wellen')) {
				$data = postOrGet('atter_welle');
				/*tic_mysql_query("DELETE FROM gn4massinc_atter_willing aw
								WHERE aw.project_fk = '".mysql_real_escape_string($project)."'
									AND EXISTS(
										SELECT * FROM gn4massinc_atter WHERE a.project_fk = aw.project_fk AND a.gal = aw.atter_gal AND a.pla = aw.atter_pla AND a.external = 1
									)", __FILE__, __LINE__);
				*/
				
				foreach($data as $gal=>$v1) {
					foreach($v1 as $pla=>$v2) {
						foreach($v2 as $welle=>$v3) {
							tic_mysql_query("INSERT INTO gn4massinc_atter_willing (
												project_fk, welle, atter_gal, atter_pla, willing
											) VALUES (
												'".mysql_real_escape_string($project)."',
												'".mysql_real_escape_string($welle)."',
												'".mysql_real_escape_string($gal)."',
												'".mysql_real_escape_string($pla)."',
												'".($v3 == "on")."'
											)
											ON DUPLICATE KEY UPDATE willing = '".($v3 == "on")."'
											", __FILE__, __LINE__);
							
						}
					}
				}
			}
			
			//del external atter
			if(strlen(postOrGet('del')) > 0) {
				$todel = postOrGet('atter_del');
				foreach($todel as $gal=>$v) {
					foreach($v as $pla=>$v2) {
						if($v2) {
							tic_mysql_query("DELETE FROM gn4massinc_atter WHERE project_fk = '".mysql_real_escape_string($project)."' AND gal = '".mysql_real_escape_string($gal)."' AND pla = '".mysql_real_escape_string($pla)."'", __FILE__, __LINE__);
						}

					}
				}
			}
			
			//add internal atter
			if(postOrGet('interne_atter_add')) {
				$re = "/(?P<g>\\d+):(?P<p>\\d+)/";
				$num = preg_match_all($re, postOrGet('x2'), $atter);
				
				for($i = 0; $i < $num; $i++) {
					$sql1 = "SET @project = '".mysql_real_escape_string($project)."',
						@gal = '".mysql_real_escape_string($atter['g'][$i])."',
						@pla = '".mysql_real_escape_string($atter['p'][$i])."'";
					$sql2 = "INSERT IGNORE INTO gn4massinc_atter (
									project_fk, gal, pla, off_fleets, external
								) VALUES (
									@project, @gal, @pla, (SELECT off_fleets FROM gn4accounts WHERE galaxie = @gal AND planet = @pla), NOT(EXISTS(SELECT * FROM gn4accounts WHERE galaxie = @gal AND planet = @pla))
								)";
					if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)));
					tic_mysql_query($sql1, __FILE__, __LINE__);
					tic_mysql_query($sql2, __FILE__, __LINE__);
				}
			}
			
			//add external atter
			if(strlen(postOrGet('external')) > 0) {
				$data = postOrGet('data');
				$data = explode("\n", $data);
				$toInsert = array();
				
				//gal:pla
				$re_koords = "/.*\\((?P<gal>\\d+):(?P<pla>\\d+)\\)/"; 
				$re_pkt = "/.* (?P<num>\\d+(?:.\\d+)?)(?P<num_modifier>[k])?.?(?:punkte|pts|pkt)/";
				$re_ja = "/.* (?P<num>\\d+(?:.\\d+)?)(?P<num_modifier>[k])?.?(?:j)/"; 
				$re_bo = "/.* (?P<num>\\d+(?:.\\d+)?)(?P<num_modifier>[k])?.?(?:bo|b)/"; 
				$re_fr = "/.* (?P<num>\\d+(?:.\\d+)?)(?P<num_modifier>[k])?.?(?:fr|f)/"; 
				$re_ze = "/.* (?P<num>\\d+(?:.\\d+)?)(?P<num_modifier>[k])?.?(?:ze|z)/"; 
				$re_kr = "/.* (?P<num>\\d+(?:.\\d+)?)(?P<num_modifier>[k])?.?(?:kr)/"; 
				$re_sc = "/.* (?P<num>\\d+(?:.\\d+)?)(?P<num_modifier>[k])?.?(?:s|sc|ss|sa)/"; 
				$re_tr = "/.* (?P<num>\\d+(?:.\\d+)?)(?P<num_modifier>[k])?.?(?:t|tr)/"; 
				$re_cl = "/.* (?P<num>\\d+(?:.\\d+)?)(?P<num_modifier>[k])?.?(?:cl|ka)/"; 
				
				for($i = 0; $i < count($data); $i++) {
					if(strlen($data[$i]) == 0)
						continue;
					
					$insert = array();
					
					//koords
					preg_match($re_koords, $data[$i], $insert['koords']);
					//pkt
					preg_match($re_pkt, $data[$i], $insert['pkt']);
					//schiffe
					preg_match($re_ja, $data[$i], $insert['ja']);
					preg_match($re_bo, $data[$i], $insert['bo']);
					preg_match($re_fr, $data[$i], $insert['fr']);
					preg_match($re_ze, $data[$i], $insert['ze']);
					preg_match($re_kr, $data[$i], $insert['kr']);
					preg_match($re_sc, $data[$i], $insert['sc']);
					preg_match($re_tr, $data[$i], $insert['tr']);
					preg_match($re_cl, $data[$i], $insert['cl']);
					
					if(count($insert['koords']) == 0 || count($insert['pkt']) == 0)
						continue;
					
					$sql1 = "SET @project = '".mysql_real_escape_string($project)."',
						@gal = '".mysql_real_escape_string($insert['koords']['gal'])."',
						@pla = '".mysql_real_escape_string($insert['koords']['pla'])."',
						@pkt = '".mysql_real_escape_string($insert['pkt']['num'] * ($insert['pkt']['num_modifier'] == "k" ? 1000 : 1))."',
						@fja = '".mysql_real_escape_string($insert['ja']['num'] * ($insert['ja']['num_modifier'] == "k" ? 1000 : 1))."',
						@fbo = '".mysql_real_escape_string($insert['bo']['num'] * ($insert['bo']['num_modifier'] == "k" ? 1000 : 1))."',
						@ffr = '".mysql_real_escape_string($insert['fr']['num'] * ($insert['fr']['num_modifier'] == "k" ? 1000 : 1))."',
						@fze = '".mysql_real_escape_string($insert['ze']['num'] * ($insert['ze']['num_modifier'] == "k" ? 1000 : 1))."',
						@fkr = '".mysql_real_escape_string($insert['kr']['num'] * ($insert['kr']['num_modifier'] == "k" ? 1000 : 1))."',
						@fsc = '".mysql_real_escape_string($insert['sc']['num'] * ($insert['sc']['num_modifier'] == "k" ? 1000 : 1))."',
						@ftr = '".mysql_real_escape_string($insert['tr']['num'] * ($insert['tr']['num_modifier'] == "k" ? 1000 : 1))."',
						@fcl = '".mysql_real_escape_string($insert['cl']['num'] * ($insert['cl']['num_modifier'] == "k" ? 1000 : 1))."',
						@erfasser = '".mysql_real_escape_string($Benutzer['name'])."',
						@erfasser_g = '".mysql_real_escape_string($Benutzer['galaxie'])."',
						@erfasser_p = '".mysql_real_escape_string($Benutzer['planet'])."'
						";
					$sql2 = "DELETE FROM gn4scans WHERE rg = @gal AND rp = @pla AND `type` IN (0, 1)";
					$sql3 = "INSERT INTO gn4scans
								(
									zeit, type, g, p, rp, rg, gen, pts, erfasser
								) VALUES (
									DATE_FORMAT(NOW(), '%H:$i %d.%m.%Y'),
									0,
									@erfasser_g,
									@erfasser_p,
									@gal,
									@pla,
									99,
									@pkt,
									@erfasser
								)";
					$sql4 = "INSERT INTO gn4scans (
									zeit, type, g, p, rp, rg, gen, erfasser, sfj, sfb, sff, sfz, sfkr, sfsa, sft, sfka
								) VALUES (
									DATE_FORMAT(NOW(), '%H:%i %d.%m.%Y'),
									1,
									@erfasser_g,
									@erfasser_p,
									@gal,
									@pla,
									99,
									@erfasser,
									@fja,
									@fbo,
									@ffr,
									@fze,
									@fkr,
									@fsc,
									@ftr,
									@fcl
								)";
					$sql5 = "INSERT IGNORE INTO gn4massinc_atter (
									project_fk, gal, pla, off_fleets,  external
								) VALUES (
									@project, @gal, @pla, 2, NOT(EXISTS(SELECT * FROM gn4accounts WHERE galaxie = @gal AND planet = @pla))
								)";
					if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2, $sql3, $sql4, $sql5)));
					tic_mysql_query($sql1, __FILE__, __LINE__);
					tic_mysql_query($sql2, __FILE__, __LINE__);
					tic_mysql_query($sql3, __FILE__, __LINE__);
					tic_mysql_query($sql4, __FILE__, __LINE__);
					tic_mysql_query($sql5, __FILE__, __LINE__);
				}
			}
			
			//display internal atter
			echo '<table class="datatable" align="center" width="100%">';
			echo '	<tr class="datatablehead">';
			echo '		<td colspan="5">&nbsp;Interne Atter&nbsp;</td>';
			echo '	</tr>';
			echo '	<tr class="fieldnormaldark" style="font-weight: bold">';
			echo '		<td>&nbsp;Gal&nbsp;</td>';
			echo '		<td>&nbsp;Pla&nbsp;</td>';
			echo '		<td>&nbsp;Spieler&nbsp;</td>';
			echo '		<td>&nbsp;Offensive Flotten&nbsp;</td>';
			echo '		<td>&nbsp;del&nbsp;</td>';
			$sql = "SELECT t FROM gn4massinc_wellen WHERE project_fk = '".$project."' ORDER BY t ASC";
			$res = tic_mysql_query($sql, __FILE__, __LINE__);
			$num_wellen = 0;
			$i = 0;
			while(list($t) = mysql_fetch_row($res)) {
				$i++;
				echo '	<td title="'.date('Y-m-d H:i', $t).'">&nbsp;#'.$i.'&nbsp;</td>';
				$num_wellen++;
			}
			echo '	</tr>';
			
			$sql1 = "SET @project = '".mysql_real_escape_string($project)."'";
			$sql2 = "SELECT a.gal, a.pla, a.off_fleets, s.spieler_name
						FROM gn4massinc_atter a
						LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = a.gal AND s.spieler_planet = a.pla
						WHERE a.external = 0
						ORDER BY a.gal, a.pla";
			if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)));
			tic_mysql_query($sql1,  __FILE__, __LINE__);
			$res = tic_mysql_query($sql2,  __FILE__, __LINE__);
			$num = mysql_num_rows($res);
			$color = false;
			echo '<form action="main.php?modul=massinc&project='.$project.'" method="post">';
			
			while(list($eg, $ep, $efleets, $ename) = mysql_fetch_row($res)) {
				$color = !$color;
				echo '<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
				echo '	<td>&nbsp;'.$eg.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$ep.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$ename.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$efleets.' <a href="main.php?modul=massinc&project='.$project.'&editfleets=1&gal='.$eg.'&pla='.$ep.'">&raquo; &auml;ndern</a>&nbsp;</td>';
				echo '	<td><input type="checkbox" name="atter_del['.$eg.']['.$ep.']"/></td>';
				$sql1 = "SET @project = '".mysql_real_escape_string($project)."',
								@gal = '".mysql_real_escape_string($eg)."',
								@pla = '".mysql_real_escape_string($ep)."'";
				$sql2 = "SELECT DISTINCT(CONCAT_WS('.', w.id, aw.willing, (NOT zw.project_fk IS NULL) AND aw.willing = 0)) xx,
							w.id welle, 
							aw.willing, 
							(NOT zw.project_fk IS NULL) AND aw.willing = 0 as error,
							zw.dest_gal,
							zw.dest_pla
						FROM gn4massinc_wellen w
						LEFT JOIN gn4massinc_atter_willing aw ON aw.project_fk = w.project_fk AND aw.atter_gal = @gal AND aw.atter_pla = @pla AND aw.welle = w.id
						LEFT JOIN gn4massinc_zuweisung zw ON zw.project_fk = w.project_fk AND zw.welle = w.id AND zw .atter_gal = @gal AND zw.atter_pla = @pla
						WHERE w.project_fk = @project
						GROUP BY xx";
				if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)));
				tic_mysql_query($sql1, __FILE__, __LINE__);
				$res2 = tic_mysql_query($sql2, __FILE__, __LINE__);
				while(list($tmp, $wid, $id, $error, $dest_g, $dest_p) = mysql_fetch_row($res2)) {
					echo '<td'.($error ? ' title="FEHLER: Zugewiesen, hat aber keine Zeit!" bgcolor="red"' : '').'>&nbsp;<input type="hidden" name="atter_welle['.$eg.']['.$ep.']['.$wid.']" value="off" />';
					echo '<input type="checkbox" name="atter_welle['.$eg.']['.$ep.']['.$wid.']" '.($id ? ' checked="checked"' : '').'/>&nbsp;';
					if($error) {
						echo '<br>&nbsp;<a href="main.php?modul=massinc&project='.$project.'&wave='.$wid.'&zuweisung=1&koord='.$dest_g.':'.$dest_p.'">Reinschauen</a>&nbsp;';
					}
					echo '</td>';
				}
				
				echo '</tr>';
			}
			
			if($num == 0) {
				echo '<tr class="fieldnormallight"><td colspan="5" align="center">Keine Eintr&auml;ge</td></tr>';
			}

			echo '<tr class="fieldnormaldark" style="font-weight: bold">';
			echo '	<td colspan="4"></td>';
			echo '	<td><input type="submit" name="del" value="del" onclick="return confirm(\'Die Eintr&auml;ge werden unwiederruflich gel&ouml;scht. Bist Du Dir sicher?\')"/></td>';
			echo '	<td colspan="'.$num_wellen.'"><input type="submit" name="atter_wellen" value="speichern"/></td>';
			echo '</tr>';
			echo '</form>';

			echo '<tr class="fieldnormaldark" style="font-weight: bold" align="center">';
			echo '	<td colspan="5">&nbsp;Angreifer hinzuf&uuml;gen&nbsp;</td>';
			echo '</tr>';
			echo '<tr class="fieldnormallight">';
			echo '	<td colspan="2">';
			echo '		<select name="x" id="intern">';

			$lastally = null;
			$sql = "SELECT a.name, acc.galaxie, acc.planet, acc.name FROM gn4accounts acc
					JOIN gn4allianzen a ON a.id = acc.allianz
					ORDER BY a.name, acc.galaxie, acc.planet";
			$res = tic_mysql_query($sql, __FILE__, __LINE__);
			while(list($ally, $gal, $pla, $name) = mysql_fetch_row($res)) {
				if($ally != $lastally) echo '			<optgroup label="'.$ally.'">';
				echo '	<option value="'.$gal.':'.$pla.'">'.$gal.':'.$pla.' '.$name.'</option>';
				if($ally != $lastally) echo '			</optgroup">';
				$lastally = $ally;
			}

			echo '		</select>';
			echo '	</td>';
			echo '	<td><input type="button" value=">>" onclick="document.getElementById(\'interne_atter\').value += (document.getElementById(\'intern\').value + \';\');var tmp = document.getElementById(\'intern\').selectedIndex; document.getElementById(\'intern\').remove(document.getElementById(\'intern\').selectedIndex); document.getElementById(\'intern\').selectedIndex = tmp;"/></td>';
			echo '	<td><form action="main.php?modul=massinc&project='.$project.'&wave='.$wave.'&interne_atter_add=1" method="post"><textarea name="x2" id="interne_atter"></textarea></td>';
			echo '	<td><input type="submit" value="speichern"/></form></td>';
			echo '</tr>';
			
			//display external atter
			echo '	<tr class="datatablehead">';
			echo '		<td colspan="5">&nbsp;Externe Atter&nbsp;</td>';
			echo '	</tr>';
			echo '	<tr class="fieldnormaldark" style="font-weight: bold">';
			echo '		<td>&nbsp;Gal&nbsp;</td>';
			echo '		<td>&nbsp;Pla&nbsp;</td>';
			echo '		<td>&nbsp;Spieler&nbsp;</td>';
			echo '		<td>&nbsp;Offensive Flotten&nbsp;</td>';
			echo '		<td>&nbsp;del&nbsp;</td>';
			$sql = "SELECT t FROM gn4massinc_wellen WHERE project_fk = '".$project."' ORDER BY t ASC";
			$res = tic_mysql_query($sql, __FILE__, __LINE__);
			$num_wellen = 0;
			$i = 0;
			while(list($t) = mysql_fetch_row($res)) {
				$i++;
				echo '	<td title="'.date('Y-m-d H:i', $t).'">&nbsp;#'.$i.'&nbsp;</td>';
				$num_wellen++;
			}
			echo '	</tr>';
			
			$sql1 = "SET @project = '".mysql_real_escape_string($project)."'";
			$sql2 = "SELECT a.gal, a.pla, a.off_fleets, s.spieler_name
						FROM gn4massinc_atter a
						LEFT JOIN gn_spieler2 s ON s.spieler_galaxie = a.gal AND s.spieler_planet = a.pla
						WHERE a.external = 1
						ORDER BY a.gal, a.pla";
			tic_mysql_query($sql1,  __FILE__, __LINE__);
			$res = tic_mysql_query($sql2,  __FILE__, __LINE__);
			$num = mysql_num_rows($res);
			$color = false;
			echo '<form action="main.php?modul=massinc&project='.$project.'" method="post">';
			while(list($eg, $ep, $efleets, $ename) = mysql_fetch_row($res)) {
				$color = !$color;
				echo '<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
				echo '	<td>&nbsp;'.$eg.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$ep.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$ename.'&nbsp;</td>';
				echo '	<td>&nbsp;'.$efleets.' <a href="main.php?modul=massinc&project='.$project.'&editfleets=1&gal='.$eg.'&pla='.$ep.'">&raquo; &auml;ndern</a>&nbsp;</td>';
				echo '	<td><input type="checkbox" name="atter_del['.$eg.']['.$ep.']"/></td>';
				
				$sql1 = "SET @project = '".mysql_real_escape_string($project)."',
								@gal = '".mysql_real_escape_string($eg)."',
								@pla = '".mysql_real_escape_string($ep)."'";
				$sql2 = "SELECT w.id welle, aw.willing FROM gn4massinc_wellen w
						LEFT JOIN gn4massinc_atter_willing aw ON aw.project_fk = w.project_fk AND atter_gal = @gal AND atter_pla = @pla
						WHERE w.project_fk = @project";
				if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2)));
				tic_mysql_query($sql1, __FILE__, __LINE__);
				$res2 = tic_mysql_query($sql2, __FILE__, __LINE__);
				while(list($wid, $id) = mysql_fetch_row($res2)) {
					echo '<td>&nbsp;<input type="hidden" name="atter_welle['.$eg.']['.$ep.']['.$wid.']" value="off" />';
					echo '<input type="checkbox" name="atter_welle['.$eg.']['.$ep.']['.$wid.']" '.($id ? ' checked="checked"' : '').'/>&nbsp;</td>';
				}
				
				echo '</tr>';
			}
			
			if($num == 0) {
				echo '<tr class="fieldnormallight"><td colspan="5" align="center">Keine Eintr&auml;ge</td></tr>';
			}

			echo '<tr class="fieldnormaldark" style="font-weight: bold">';
			echo '	<td colspan="4" align="left">Hinzuf&uuml;gen (auch intern m&ouml;glich):</td>';
			echo '	<td><input type="submit" name="del" value="del"/></td>';
			echo '	<td colspan="'.$num_wellen.'"><input type="submit" name="atter_wellen" value="speichern"/></td>';
			echo '</tr>';
			echo '</form>';
			echo '<form method="post" action="main.php?modul=massinc&project='.$project.'">';
			echo '<tr class="fieldnormallight">';
			echo '	<td colspan="5" align="left">&nbsp;<b>Format:</b>&nbsp;<br/>';
			echo '<span style="font-family: Courier New">&nbsp;Name (galaxie:planet), 180k punkte, 2.1k jaeger, 1.5k bomber, 75 fregs, 400 zerris, 20 kreuzer, 110 schlachter, 50 träger, 15.5k cleps&nbsp;<br/>&nbsp;Name (galaxie:planet), 180k punkte, 2k jaeger, 1k bomber, 75 fregs, 400 zerris, 20 kreuzer, 110 schlachter, 50 träger, 15k cleps&nbsp;</span><br/>';
			echo '<textarea cols="80" rows="5" name="data"></textarea>&nbsp;<input type="submit" name="external" value="best&auml;tigen"/>&nbsp;</td>';
			echo '</tr>';
			echo '</form>';


			echo '<tr class="fieldnormaldark" style="font-weight: bold">';
			
			//scanlist extern
			$sql = "SELECT z.gal, z.pla
					FROM gn4massinc_ziele z
					WHERE z.project_fk = '".$project."' ORDER BY z.gal, z.pla";
			if($SQL_DEBUG) aprint($sql);
			$res = tic_mysql_query($sql, __FILE__, __LINE__);
			$scanlistparams = '';
			while(list($g, $p) = mysql_fetch_row($res)) {
				$scanlistparams .= $g . ':' . $p . ';';
			}
			echo '	<td colspan="5" align="left">&nbsp;<a href="main.php?modul=scanliste&koords=' . $scanlistparams . '&s=on&g=on&e=on&n=on&b=on&u=on&export=Export">&raquo; Scanexport f&uuml;r Externe</a>&nbsp;</td>';			
			echo '</tr>';
			echo '</table>';
			echo '<br/>';
		}
		
		if(($Benutzer['rang'] >= $Rang_GC || isUserCreator($project, $Benutzer['id'])) && !$donotshowdestinations) {
			if(postOrGet('scansbeantragen2')) {
				$sql1 = "INSERT INTO gn4scanrequests (requester_g, requester_p, ziel_g,  ziel_p, t, scantyp) 
						(
						SELECT '".$Benutzer['galaxie']."' requester_g, '".$Benutzer['planet']."' requester_p, z.gal ziel_g, z.pla ziel_p, UNIX_TIMESTAMP(NOW()), 4 FROM gn4massinc_ziele z
						WHERE z.project_fk = '".$project."' ORDER BY z.gal, z.pla
						)";
				if($SQL_DEBUG) aprint(join(";\n\n", array($sql1)));
				tic_mysql_query($sql1, __FILE__, __LINE__);
			}
			if(postOrGet('scansbeantragen1')) {
				$sql1 = "INSERT INTO gn4scanrequests (requester_g, requester_p, ziel_g,  ziel_p, t, scantyp) 
						(
						SELECT '".$Benutzer['galaxie']."' requester_g, '".$Benutzer['planet']."' requester_p, z.gal ziel_g, z.pla ziel_p, UNIX_TIMESTAMP(NOW()), 0 FROM gn4massinc_ziele z
						WHERE z.project_fk = '".$project."' ORDER BY z.gal, z.pla
						)";
				$sql2 = "INSERT INTO gn4scanrequests (requester_g, requester_p, ziel_g,  ziel_p, t, scantyp) 
						(
						SELECT '".$Benutzer['galaxie']."' requester_g, '".$Benutzer['planet']."' requester_p, z.gal ziel_g, z.pla ziel_p, UNIX_TIMESTAMP(NOW()), 1 FROM gn4massinc_ziele z
						WHERE z.project_fk = '".$project."' ORDER BY z.gal, z.pla
						)";
				$sql3 = "INSERT INTO gn4scanrequests (requester_g, requester_p, ziel_g,  ziel_p, t, scantyp) 
						(
						SELECT '".$Benutzer['galaxie']."' requester_g, '".$Benutzer['planet']."' requester_p, z.gal ziel_g, z.pla ziel_p, UNIX_TIMESTAMP(NOW()), 3 FROM gn4massinc_ziele z
						WHERE z.project_fk = '".$project."' ORDER BY z.gal, z.pla
						)";
				if($SQL_DEBUG) aprint(join(";\n\n", array($sql1, $sql2, $sql3)));
				tic_mysql_query($sql1, __FILE__, __LINE__);
				tic_mysql_query($sql2, __FILE__, __LINE__);
				tic_mysql_query($sql3, __FILE__, __LINE__);
			}
			
			//show destinations
			echo '<table class="datatable" align="center" width="100%">';
			echo '<tr class="datatablehead">';
			echo '	<td colspan="11">&nbsp;Ziele&nbsp;</td>';
			//echo '	<td>&nbsp;<a href="#" onclick="javascript:document.getElementById(\'addfields\').style.display=\'\'">&raquo; show</a>&nbsp;</td>';
			//echo '</tr><tbody style="display: none;" id="addfields">';
			echo '</tr><tbody id="addfields">';

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
				$sql = "SELECT z.gal, z.pla, w.id welle, zw.id FROM gn4massinc_ziele z
						JOIN gn4massinc_wellen w ON w.project_fk = '".$project."'
						LEFT JOIN gn4massinc_ziele_welle zw ON zw.ziel_gal = z.gal AND zw.ziel_pla = z.pla AND zw.welle = w.id
						WHERE z.gal = '".$g."' AND z.pla = '".$p."'";
				if($SQL_DEBUG) aprint($sql);
				$res2 = tic_mysql_query($sql, __FILE__, __LINE__);
				while(list($g, $p, $wid, $id) = mysql_fetch_row($res2)) {
					echo '<td>&nbsp;<input type="checkbox" name="ziel_welle['.$g.']['.$p.']['.$wid.']" '.($id ? ' checked="checked"' : '').'/>&nbsp;</td>';
				}


				echo '</tr>';
			}

 			$short = addShortUrl('main.php?modul=scanliste&koords=' . $scanlistparams);
			echo '<tr class="fieldnormaldark"><td colspan="11" align="right">&nbsp;' . createCopyLink('&raquo; Scanlistenlink kopieren', $short) . ' | <a href="' . $short . '">&raquo; Scanliste</a>&nbsp;</td><td>&nbsp;<input type="submit" value="del">&nbsp;</td>';
			echo '<td colspan="'.$num_wellen.'">&nbsp;<input type="submit" name="ziel_welle_update" value="speichern"/>&nbsp;</td>';
			echo '</tr></tbody>';
			echo '</table>';
			echo '</form>';
		}//if GC show destinations

		echo '</td></tr></table>';
	}//num project > 0
}//!empty project

?>
