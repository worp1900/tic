<?php
$DEBUG = false;
$data = (isset($_POST['txtScan'])) ? $_POST['txtScan'] : null;
mb_regex_encoding("UTF-8");

function getsqlIncreaseScanCount($gal, $pla) {
	return "UPDATE gn4accounts SET scans=scans+1 WHERE galaxie='".$gal."' AND planet='".$pla."'";
}

function showImportError($input, $parsed, $pattern) {
	aprint(array(
		'input' => $input,
		'pattern' => $pattern,
		'parsed' => $parsed
	), 'IMPORT_ERROR');
}

function mysqlEscapeMatchResult(&$res) {
	if(is_array($res))
		foreach($res as $k => $v) {
			if(!is_string)
				$res[$k] = mysql_real_escape_string($v);
		}
}

function getScanType($typ) {
	if($typ == 'Sektorscan')
		return 0;
	if($typ == 'Einheitenscan')
		return 1;
	if($typ == 'Militärscan')
		return 2;
	if($typ == 'Geschützscan')
		return 3;
	if($typ == 'Nachrichtenscan')
		return 4;
	if($typ == 'Newsscan')
		return 4;
	return -1;
}

//convert matching output to same tick based time
function getTimeInTicks($data, $row) {
	if(strlen($data['ticks'][$row]) > 0) {
		//ticks
		return $data['ticks'][$row];

	} elseif(strlen($data['minuten'][$row]) > 0) {
		//Min
		return ceil($data['minuten'][$row] / 15.0);

	} elseif(strlen($data['xstunden'][$row]) > 0) {
		//HH:MM:SS
		return ceil(($data['xstunden'][$row] * 60 * 60 + $data['xminuten'][$row] * 60 + $data['xsekunden'][$row]) / 60.0 / 15.0);

	} elseif(strlen($data['ystunden'][$row]) > 0) {
		//HH:MM
		return ceil(($data['ystunden'][$row] * 60 + $data['yminuten'][$row]) / 15.0);

	} elseif(strlen($data['ystunden'][$row]) > 0) {
		//HH Std
		return ceil(($data['stunden'][$row] * 60) / 15.0);
	}
	return -1;
}


if($data) {
	$importlog = array();
	$sql = array();

	$split = array(
		'Sektorscan Ergebnis',
		'Militärcan Ergebnis',
		'Geschützscan Ergebnis',
		'Einheitenscan Ergebnis',
		'Newsscan Ergebnis',
		'Scan Block',
		'Flottenzusammensetzung',
		'Galaxiemitglieder',
		'Flottenbewegungen',
	);

	$pattern = '/(' . join('|', $split) . ')/';
	if(strpos($data, 'Newsscan Ergebnis') === false) {
		$data = explode('###', preg_replace($pattern, '###${1}', trim($data)));
	} else {
		$data = array(trim($data));
	}

	if($DEBUG) 	aprint(array(
			'pattern' => $pattern,
			'result' => $data
		), 'split');

	foreach($data as $v) {
		$v = trim($v);
		$v = str_replace("\r", "", $v);
		if(strlen($v) > 0) {
			$matches = array();

			if(strpos($v, 'Newsscan Ergebnis') !== false) {
				$p = "/^Newsscan von .+?vom.+?(?P<d2>\\d+).+?(?P<m2>\\d+).+?(?P<y2>\\d+).+?(?P<h2>\\d+).+?(?P<i2>\\d+)|^Newsscan Ergebnis.+?(?P<gen>\\d+)%.+\\n\\w.+?(?P<name>[\\wäöü:()\\.]+)\\n.+?(?P<gal>\\d+):(?P<pla>\\d+)|^\\[(?P<d>\\d+).(?P<m>\\d+).(?P<y>\\d+).(?P<h>\\d+).(?P<i>\\d+).(?P<s>\\d+)\\].(?P<typ>.+)\\n(?P<inhalt>[\\s\\S]+?)\\n\\n(?=\\[|#|[^\\w])|^#.+?(?P<fehler>Daten zu fehlerhaft)|^[^N#\\[\\n].+?\\n(?P<inhalt2>[\\s\\S]+?)\\s(?=ENDE|#|\\[)/m";

				preg_match_all($p, $v, $matches);
				if($DEBUG) aprint($matches);
				mysqlEscapeMatchResult($matches);
				if(count($matches) > 0) {
					//anchor
					$t = "UNIX_TIMESTAMP('" . date('Y-m-d H:i:00') . "')";
					if(join('', $matches['y2'])) {
						$y = join('', $matches['y2']);
						$m = join('', $matches['m2']);
						$d = join('', $matches['d2']);
						$h = join('', $matches['h2']);
						$i = join('', $matches['i2']);
						$t = "UNIX_TIMESTAMP('" . $y . "-" . $m . "-" . $d . " " . $h . ":" . $i . ":00')";
					}

					$sql[] = getsqlIncreaseScanCount($Benutzer['galaxie'], $Benutzer['planet']);
					$sql[] = "INSERT INTO gn4scans_news (
									t, genauigkeit, ziel_g, ziel_p, erfasser_g, erfasser_p, erfasser_name, erfasser_svs
								) VALUES (
									" . $t . ",
									" . join('', $matches['gen']) . ",
									" . join('', $matches['gal']) . ",
									" . join('', $matches['pla']) . ",
									" . $Benutzer['galaxie'] . ",
									" . $Benutzer['planet'] . ",
									'" . $Benutzer['name'] . "',
									" . $Benutzer['svs'] . "

								)";
					$sql[] = 'SET @newsid = LAST_INSERT_ID();';

					$importlog[] = 'NEWS t=' . $t . ' gen=' . join('', $matches['gen']) . '% gal=' . join('', $matches['gal']) . ' pla=' . join('', $matches['pla']);

					//entries
					for($i = 0; $i < count($matches[0]); $i++) {
						if(strlen($matches['d'][$i]) > 0 || $matches['fehler'][$i] || $matches['inhalt2'][$i]) {
							$y = $matches['y'][$i];
							$m = $matches['m'][$i];
							$d = $matches['d'][$i];
							$h = $matches['h'][$i];
							$min = $matches['i'][$i];
							$s = $matches['s'][$i];
							$t = "UNIX_TIMESTAMP('" . $y . "-" . $m . "-" . $d . " " . $h . ":" . $min . ":" . $s . "')";
							$inaccurate = ($matches['fehler'][$i] || $matches['inhalt2'][$i]) ? '1' : 'NULL';
							$type = ($matches['fehler'][$i] || $matches['inhalt2'][$i]) ? 'Daten unvollständig' : $matches['typ'][$i];
							$content = ($matches['fehler'][$i] || $matches['inhalt2'][$i]) ? $matches['inhalt2'][$i] : $matches['inhalt'][$i];
							$content = str_replace("\n", '\n', $content);
							$content = str_replace("\t", '\t', $content);
							//aprint($content);
							$sql[] = "INSERT INTO gn4scans_news_entries (
										news_id, t, typ, inhalt, inaccurate
									) VALUES (
										@newsid, " . $t . ", '" . $type . "', '" . $content . "', " . $inaccurate . "
									)";
						}
					}
				}
				if($DEBUG) aprint(array(
						'pattern' => htmlentities($p),
						//'parsed' => $matches,
					), 'Nachrichtenscan');
			} else if(strpos($v, 'Sektorscan Ergebnis') !== false) {
				$p = "/.+?(?P<gen>\\d+)%.+\\n.+?(?P<name>[\\w\\.\\-äöü:()]+)\\n.+?(?P<gal>\\d+):(?P<pla>\\d+)\\n.+?(?P<pkt>(?:\\d+.)+\\d+)\\n.+?(?P<s>\\d+)\\n.+?(?P<d>\\d+)\\n.+?(?P<me>\\d+)\\n.+?(?P<ke>\\d+)\\n.+?(?P<a>\\d+)/";
				preg_match($p, $v, $matches);
				mysqlEscapeMatchResult($matches);
				if(count($matches) > 0) {
					$t = date('H:i d.m.Y');
					$sql[] = "DELETE FROM gn4scans WHERE rg='" . $matches['gal'] . "' AND rp='" . $matches['pla'] . "' AND `type`=0";
					$sql[] = "INSERT INTO gn4scans (
								zeit, `type`, g, p, rg, rp, gen,
								pts, s, d, me, ke, a
							) VALUES (
								'" . $t . "',
								0,
								'" . $Benutzer['galaxie'] . "',
								'" . $Benutzer['planet'] . "',
								'" . $matches['gal'] . "',
								'" . $matches['pla'] . "',
								'" . $matches['gen'] . "',
								'" . str_replace('.', '', $matches['pkt']) . "',
								'" . $matches['s'] . "',
								'" . $matches['d'] . "',
								'" . $matches['me'] . "',
								'" . $matches['ke'] . "',
								'" . $matches['a'] . "'
							)";
					$sql[] = getsqlIncreaseScanCount($Benutzer['galaxie'], $Benutzer['planet']);
					$importlog[] = 'SEKTOR t=' . $t . ' gen=' . $matches['gen'] . '% gal=' . $matches['gal'] . ' pla=' . $matches['pla'];
					if($DEBUG) aprint(array(
							'pattern' => htmlentities($p),
							'parsed' => $matches,
						), 'Sektorscan');
				} else {
					showImportError($v, $matches, $p);
				}
			} else if(strpos($v, 'Militärscan Ergebnis') !== false) {
				$p = "/.+?(?P<gen>\\d+)%.+\\n\\w.+?(?P<name>[\\w\\.\\-äöü:()]+)\\n.+?(?P<gal>\\d+):(?P<pla>\\d+)\\n.+\\n.+?(?:\\s(?P<s0j>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s1j>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s2j>(?:\\d+\\.)*\\d+)\\s*?)?\\n.+?(?:\\s(?P<s0b>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s1b>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s2b>(?:\\d+\\.)*\\d+)\\s*?)?\\n.+?(?:\\s(?P<s0f>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s1f>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s2f>(?:\\d+\\.)*\\d+)\\s*?)?\\n.+?(?:\\s(?P<s0z>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s1z>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s2z>(?:\\d+\\.)*\\d+)\\s*?)?\\n.+?(?:\\s(?P<s0k>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s1k>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s2k>(?:\\d+\\.)*\\d+)\\s*?)?\\n.+?(?:\\s(?P<s0s>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s1s>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s2s>(?:\\d+\\.)*\\d+)\\s*?)?\\n.+?(?:\\s(?P<s0t>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s1t>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s2t>(?:\\d+\\.)*\\d+)\\s*?)?\\n.+?(?:\\s(?P<s0cl>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s1cl>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s2cl>(?:\\d+\\.)*\\d+)\\s*?)?\\n.+?(?:\\s(?P<s0ca>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s1ca>(?:\\d+\\.)*\\d+)\\s*?)(?:\\s(?P<s2ca>(?:\\d+\\.)*\\d+)\\s*?)?\\nPosition(?:[\\w ]+):\\s+[\\w \\(\\)]+(?:\\s)(?P<ziel1>[\\w\\.\\-äöü\\(\\) ]+)(?:\\s*)?(?P<ziel2>[\\w\\.\\-äöü\\(\\) ]+)?/m";
				preg_match($p, $v, $matches);
				mysqlEscapeMatchResult($matches);
				if(count($matches) > 0) {
					$ziel1 = '';
					$status1 = 0;
					$ziel2 = '';
					$status2 = 0;
					$t = date('H:i d.m.Y');

					$sql[] = getsqlIncreaseScanCount($Benutzer['galaxie'], $Benutzer['planet']);
					$sql[] = "DELETE FROM gn4scans WHERE rg='" . $matches['gal'] . "' AND rp='" . $matches['pla'] . "' AND `type`=2";
					$sql[] = "INSERT INTO gn4scans (
								zeit, `type`, g, p, rg, rp, gen,
								sf0j, sf0b, sf0f, sf0z, sf0kr, sf0sa, sf0t, sf0ka, sf0su,
								sf1j, sf1b, sf1f, sf1z, sf1kr, sf1sa, sf1t, sf1ka, sf1su, ziel1, status1,
								sf2j, sf2b, sf2f, sf2z, sf2kr, sf2sa, sf2t, sf2ka, sf2su, ziel2, status2
							) VALUES (
								'" . $t . "',
								2,
								'" . $Benutzer['galaxie'] . "',
								'" . $Benutzer['planet'] . "',
								'" . $matches['gal'] . "',
								'" . $matches['pla'] . "',
								'" . $matches['gen'] . "',

								'" . str_replace('.', '', $matches['s0j']) . "',
								'" . str_replace('.', '', $matches['s0b']) . "',
								'" . str_replace('.', '', $matches['s0f']) . "',
								'" . str_replace('.', '', $matches['s0z']) . "',
								'" . str_replace('.', '', $matches['s0k']) . "',
								'" . str_replace('.', '', $matches['s0s']) . "',
								'" . str_replace('.', '', $matches['s0t']) . "',
								'" . str_replace('.', '', $matches['s0cl']) . "',
								'" . str_replace('.', '', $matches['s0ca']) . "',

								'" . str_replace('.', '', $matches['s1j']) . "',
								'" . str_replace('.', '', $matches['s1b']) . "',
								'" . str_replace('.', '', $matches['s1f']) . "',
								'" . str_replace('.', '', $matches['s1z']) . "',
								'" . str_replace('.', '', $matches['s1k']) . "',
								'" . str_replace('.', '', $matches['s1s']) . "',
								'" . str_replace('.', '', $matches['s1t']) . "',
								'" . str_replace('.', '', $matches['s1cl']) . "',
								'" . str_replace('.', '', $matches['s1ca']) . "',
								'" . $ziel1 . "',
								'" . $status1 . "',

								'" . str_replace('.', '', $matches['s2j']) . "',
								'" . str_replace('.', '', $matches['s2b']) . "',
								'" . str_replace('.', '', $matches['s2f']) . "',
								'" . str_replace('.', '', $matches['s2z']) . "',
								'" . str_replace('.', '', $matches['s2k']) . "',
								'" . str_replace('.', '', $matches['s2s']) . "',
								'" . str_replace('.', '', $matches['s2t']) . "',
								'" . str_replace('.', '', $matches['s2cl']) . "',
								'" . str_replace('.', '', $matches['s2ca']) . "',
								'" . $ziel2 . "',
								'" . $status2 . "'
							)";
					$sql[] = "DELETE FROM gn4scans WHERE rg='" . $matches['gal'] . "' AND rp='" . $matches['pla'] . "' AND `type`=1";
					$sql[] = "INSERT INTO gn4scans (
								zeit, `type`, g, p, rg, rp, gen,
								sfj, sfb, sff, sfz, sfkr, sfsa, sft, sfka, sfsu
							) VALUES (
								'" . $t . "',
								1,
								'" . $Benutzer['galaxie'] . "',
								'" . $Benutzer['planet'] . "',
								'" . $matches['gal'] . "',
								'" . $matches['pla'] . "',
								'" . $matches['gen'] . "',

								'" . (str_replace('.', '', $matches['s0j']) + str_replace('.', '', $matches['s1j']) + str_replace('.', '', $matches['s2j'])). "',
								'" . (str_replace('.', '', $matches['s0b']) + str_replace('.', '', $matches['s1b']) + str_replace('.', '', $matches['s2b'])) . "',
								'" . (str_replace('.', '', $matches['s0f']) + str_replace('.', '', $matches['s1f']) + str_replace('.', '', $matches['s2f'])) . "',
								'" . (str_replace('.', '', $matches['s0z']) + str_replace('.', '', $matches['s1z']) + str_replace('.', '', $matches['s2z'])) . "',
								'" . (str_replace('.', '', $matches['s0k']) + str_replace('.', '', $matches['s1k']) + str_replace('.', '', $matches['s2k'])) . "',
								'" . (str_replace('.', '', $matches['s0s']) + str_replace('.', '', $matches['s1s']) + str_replace('.', '', $matches['s2s'])) . "',
								'" . (str_replace('.', '', $matches['s0t']) + str_replace('.', '', $matches['s1t']) + str_replace('.', '', $matches['s2t'])) . "',
								'" . (str_replace('.', '', $matches['s0cl']) + str_replace('.', '', $matches['s1cl']) + str_replace('.', '', $matches['s2cl'])) . "',
								'" . (str_replace('.', '', $matches['s0ca']) + str_replace('.', '', $matches['s1ca']) + str_replace('.', '', $matches['s2ca'])) . "'
							)";
					$importlog[] = 'MILI t=' . $t . ' gen=' . $matches['gen'] . '% gal=' . $matches['gal'] . ' pla=' . $matches['pla'];
					if($DEBUG) aprint(array(
							'pattern' => htmlentities($p),
							'parsed' => $matches,
						), 'Mili');
				} else {
					showImportError($v, $matches, $p);
				}
			} else if(strpos($v, 'Geschützscan Ergebnis') !== false) {
				$p = "/.+?(?P<gen>\\d+)%.+\\n\\w+.+?(?P<name>[\\w\\.\\-äöü:()]+)\\n.+?(?P<gal>\\d+):(?P<pla>\\d+)\\n.+?(?P<glo>(?:\\d+\\.)*\\d+)\\n.+?(?P<glr>(?:\\d+\\.)*\\d+)\\n.+?(?P<gmr>(?:\\d+\\.)*\\d+)\\n.+?(?P<gsr>(?:\\d+\\.)*\\d+)\\n.+?(?P<gaj>(?:\\d+\\.)*\\d+)/m";
				preg_match($p, $v, $matches);
				mysqlEscapeMatchResult($matches);
				if(count($matches) > 0) {
					$ziel1 = '';
					$status1 = 0;
					$ziel2 = '';
					$status2 = 0;
					$t = date('H:i d.m.Y');

					$sql[] = getsqlIncreaseScanCount($Benutzer['galaxie'], $Benutzer['planet']);
					$sql[] = "DELETE FROM gn4scans WHERE rg='" . $matches['gal'] . "' AND rp='" . $matches['pla'] . "' AND `type`=3";
					$sql[] = "INSERT INTO gn4scans (
								zeit, type, g, p, rg, rp, gen,
								glo, glr, gmr, gsr, ga
							) VALUES (
								'" . $t . "',
								3,
								'" . $Benutzer['galaxie'] . "',
								'" . $Benutzer['planet'] . "',
								'" . $matches['gal'] . "',
								'" . $matches['pla'] . "',
								'" . $matches['gen'] . "',

								'" . str_replace('.', '', $matches['glo']) . "',
								'" . str_replace('.', '', $matches['glr']) . "',
								'" . str_replace('.', '', $matches['gmr']) . "',
								'" . str_replace('.', '', $matches['gsr']) . "',
								'" . str_replace('.', '', $matches['gaj']) . "'
							)";
					$importlog[] = 'GESCH t=' . $t . ' gen=' . $matches['gen'] . '% gal=' . $matches['gal'] . ' pla=' . $matches['pla'];
					if($DEBUG) aprint(array(
							'pattern' => htmlentities($p),
							'parsed' => $matches,
						), 'Geschütze');
				} else {
					showImportError($v, $matches, $p);
				}
			} else if(strpos($v, 'Einheitenscan Ergebnis') !== false) {
				$p = "/.+?(?P<gen>\\d+)%.+\\n\\w+.+?(?P<name>[\\w\\.\\-äöü:()]+)\\n.+?(?P<gal>\\d+):(?P<pla>\\d+)\\n.+?(?P<sfja>\\d+)\\n.+?(?P<sfbo>\\d+)\\n.+?(?P<sffr>\\d+)\\n.+?(?P<sfze>\\d+)\\n.+?(?P<sfkr>\\d+)\\n.+?(?P<sfsc>\\d+)\\n.+?(?P<sftr>\\d+)\\n.+?(?P<sfcl>\\d+)\\n.+?(?P<sfca>\\d+)/";
				preg_match($p, $v, $matches);
				mysqlEscapeMatchResult($matches);
				if(count($matches) > 0) {
					$ziel1 = '';
					$status1 = 0;
					$ziel2 = '';
					$status2 = 0;
					$t = date('H:i d.m.Y');
					$sql[] = getsqlIncreaseScanCount($Benutzer['galaxie'], $Benutzer['planet']);
					$sql[] = "DELETE FROM gn4scans WHERE rg='" . $matches['gal'] . "' AND rp='" . $matches['pla'] . "' AND `type`=1";
					$sql[] = "INSERT INTO gn4scans (
								zeit, `type`, g, p, rg, rp, gen,
								sfj, sfb, sff, sfz, sfkr, sfsa, sft, sfka, sfsu
							) VALUES (
								'" . $t . "',
								1,
								'" . $Benutzer['galaxie'] . "',
								'" . $Benutzer['planet'] . "',
								'" . $matches['gal'] . "',
								'" . $matches['pla'] . "',
								'" . $matches['gen'] . "',

								'" . str_replace('.', '', $matches['sfja']) . "',
								'" . str_replace('.', '', $matches['sfbo']) . "',
								'" . str_replace('.', '', $matches['sffr']) . "',
								'" . str_replace('.', '', $matches['sfze']) . "',
								'" . str_replace('.', '', $matches['sfkr']) . "',
								'" . str_replace('.', '', $matches['sfsc']) . "',
								'" . str_replace('.', '', $matches['sftr']) . "',
								'" . str_replace('.', '', $matches['sfcl']) . "',
								'" . str_replace('.', '', $matches['sfca']) . "'
							)";
					$importlog[] = 'EINH t=' . $t . ' gen=' . $matches['gen'] . '% gal=' . $matches['gal'] . ' pla=' . $matches['pla'];
					if($DEBUG) aprint(array(
							'pattern' => htmlentities($p),
							'parsed' => $matches,
						), 'Einheiten');
				} else {
					showImportError($v, $matches, $p);
				}
			} else if(strpos($v, 'Scan Block') !== false) {
				$p = "/Scan Block vom (?P<d>\\d+).+?(?P<m>\\d+).+?(?P<y>\\d+).+?(?P<h>\\d+).+?(?P<i>\\d+).*\\n^(?P<gal>\\d+):(?P<pla>\\d+).+hat einen (?P<typ>.+) versucht!/m";
				preg_match($p, $v, $matches);
				mysqlEscapeMatchResult($matches);
				if(count($matches) > 0) {
					$scantyp = getScanType($matches['typ']);
					$t = "UNIX_TIMESTAMP('" . $matches['y'] . "-" . $matches['m'] . "-" . $matches['d'] . " " . $matches['h'] . ":" . $matches['i'] . ":00')";
					$sql[] = "INSERT  INTO gn4scanblock (g, p, t, sg, sp, sname, typ, suspicious)
							SELECT '".$Benutzer['galaxie']."', '".$Benutzer['planet']."', ".$t.", '".$matches['gal']."', '".$matches['pla']."', '".$Benutzer['name']."', '".$typ."', 1
							FROM DUAL
							WHERE NOT EXISTS (
								SELECT * FROM gn4scanblock WHERE g = '".$Benutzer['galaxie']."' AND p = '".$Benutzer['planet']."' AND sg = '".$matches['gal']."' AND sp = '".$matches['pla']."' AND t = ".$t." AND typ = '".$typ."'
							) LIMIT 1";
					$importlog[] = 'SCANBLOCK t=' . $t . ' suspicous=1 typ=' . $matches['typ'] . '('.$scantyp.') scanner_gal=' . $matches['gal'] . ' scanner_pla=' . $matches['pla'];
					if($DEBUG) aprint(array(
							'pattern' => htmlentities($p),
							'parsed' => $matches,
						), 'block');
				} else {
					showImportError($v, $matches, $p);
				}
			} else if(strpos($v, 'Flottenzusammensetzung') !== false) {
				$p = "/^[\\w\\.\\-äöü]+:.+?(?P<s0>\\d+).+?(?P<s1><?\\d+).+?(?P<s2>\\d+)?|Flottenzusammensetzung.+?(?P<gal>\\d+):(?P<pla>\\d+)|^.+?\".+?\":.+?(?P<d>\\d+)/m";
				preg_match_all($p, $v, $matches);
				mysqlEscapeMatchResult($matches);
				if(count($matches)> 0 && (count($matches[0]) == 10 || count($matches[0]) == 15)) {
					$t = date('H:i d.m.Y');
					$sql[] = "DELETE FROM gn4scans WHERE rg='" . $matches['gal'][0] . "' AND rp='" . $matches['pla'][0] . "' AND `type`=2";
					$sql[] = "INSERT INTO gn4scans (
								zeit, `type`, g, p, rg, rp, gen,
								sf0j, sf0b, sf0f, sf0z, sf0kr, sf0sa, sf0t, sf0ka, sf0su,
								sf1j, sf1b, sf1f, sf1z, sf1kr, sf1sa, sf1t, sf1ka, sf1su, ziel1, status1,
								sf2j, sf2b, sf2f, sf2z, sf2kr, sf2sa, sf2t, sf2ka, sf2su, ziel2, status2
							) VALUES (
								'" . $t . "',
								2,
								'" . $Benutzer['galaxie'] . "',
								'" . $Benutzer['planet'] . "',
								'" . $matches['gal'][0]  . "',
								'" . $matches['pla'][0] . "',
								99,

								'" . str_replace('.', '', $matches['s0'][1]) . "',
								'" . str_replace('.', '', $matches['s0'][2]) . "',
								'" . str_replace('.', '', $matches['s0'][3]) . "',
								'" . str_replace('.', '', $matches['s0'][4]) . "',
								'" . str_replace('.', '', $matches['s0'][5]) . "',
								'" . str_replace('.', '', $matches['s0'][6]) . "',
								'" . str_replace('.', '', $matches['s0'][7]) . "',
								'" . str_replace('.', '', $matches['s0'][8]) . "',
								'" . str_replace('.', '', $matches['s0'][9]) . "',

								'" . str_replace('.', '', $matches['s1'][1]) . "',
								'" . str_replace('.', '', $matches['s1'][2]) . "',
								'" . str_replace('.', '', $matches['s1'][3]) . "',
								'" . str_replace('.', '', $matches['s1'][4]) . "',
								'" . str_replace('.', '', $matches['s1'][5]) . "',
								'" . str_replace('.', '', $matches['s1'][6]) . "',
								'" . str_replace('.', '', $matches['s1'][7]) . "',
								'" . str_replace('.', '', $matches['s1'][8]) . "',
								'" . str_replace('.', '', $matches['s1'][9]) . "',
								'" . $ziel1 . "',
								'" . $status1 . "',

								'" . str_replace('.', '', $matches['s2'][1]) . "',
								'" . str_replace('.', '', $matches['s2'][2]) . "',
								'" . str_replace('.', '', $matches['s2'][3]) . "',
								'" . str_replace('.', '', $matches['s2'][4]) . "',
								'" . str_replace('.', '', $matches['s2'][5]) . "',
								'" . str_replace('.', '', $matches['s2'][6]) . "',
								'" . str_replace('.', '', $matches['s2'][7]) . "',
								'" . str_replace('.', '', $matches['s2'][8]) . "',
								'" . str_replace('.', '', $matches['s2'][9]) . "',
								'" . $ziel2 . "',
								'" . $status2 . "'
							)";
					$sql[] = "DELETE FROM gn4scans WHERE rg='" . $matches['gal'][0] . "' AND rp='" . $matches['pla'][0] . "' AND `type`=1";
					$sql[] = "INSERT INTO gn4scans (
								zeit, `type`, g, p, rg, rp, gen,
								sfj, sfb, sff, sfz, sfkr, sfsa, sft, sfka, sfsu
							) VALUES (
								'" . $t . "',
								1,
								'" . $Benutzer['galaxie'] . "',
								'" . $Benutzer['planet'] . "',
								'" . $matches['gal'][0] . "',
								'" . $matches['pla'][0] . "',
								99,

								'" . (str_replace('.', '', $matches['s0'][1]) + str_replace('.', '', $matches['s1'][1]) + str_replace('.', '', $matches['s2'][1])). "',
								'" . (str_replace('.', '', $matches['s0'][2]) + str_replace('.', '', $matches['s1'][2]) + str_replace('.', '', $matches['s2'][2])) . "',
								'" . (str_replace('.', '', $matches['s0'][3]) + str_replace('.', '', $matches['s1'][3]) + str_replace('.', '', $matches['s2'][3])) . "',
								'" . (str_replace('.', '', $matches['s0'][4]) + str_replace('.', '', $matches['s1'][4]) + str_replace('.', '', $matches['s2'][4])) . "',
								'" . (str_replace('.', '', $matches['s0'][5]) + str_replace('.', '', $matches['s1'][5]) + str_replace('.', '', $matches['s2'][5])) . "',
								'" . (str_replace('.', '', $matches['s0'][6]) + str_replace('.', '', $matches['s1'][6]) + str_replace('.', '', $matches['s2'][6])) . "',
								'" . (str_replace('.', '', $matches['s0'][7]) + str_replace('.', '', $matches['s1'][7]) + str_replace('.', '', $matches['s2'][7])) . "',
								'" . (str_replace('.', '', $matches['s0'][8]) + str_replace('.', '', $matches['s1'][8]) + str_replace('.', '', $matches['s2'][8])) . "',
								'" . (str_replace('.', '', $matches['s0'][9]) + str_replace('.', '', $matches['s1'][9]) + str_replace('.', '', $matches['s2'][9])) . "'
							)";
					$importlog[] = 'MILI t=' . $t . ' gen=99% gal=' . $matches['gal'][0] . ' pla=' . $matches['pla'][0];
					if(count($matches[0]) == 15) {
						$sql[] = "DELETE FROM gn4scans WHERE rg='" . $matches['gal'][0] . "' AND rp='" . $matches['pla'][0] . "' AND `type`=3";
						$sql[] = "INSERT INTO gn4scans (
									zeit, type, g, p, rg, rp, gen,
									glo, glr, gmr, gsr, ga
								) VALUES (
									'" . date('H:i d.m.Y') . "',
									3,
									'" . $Benutzer['galaxie'] . "',
									'" . $Benutzer['planet'] . "',
									'" . $matches['gal'][0] . "',
									'" . $matches['pla'][0] . "',
									99,

									'" . str_replace('.', '', $matches['d'][10]) . "',
									'" . str_replace('.', '', $matches['d'][11]) . "',
									'" . str_replace('.', '', $matches['d'][12]) . "',
									'" . str_replace('.', '', $matches['d'][13]) . "',
									'" . str_replace('.', '', $matches['d'][14]) . "'
								)";
						$importlog[] = 'GESCH t=' . $t . ' gen=99% gal=' . $matches['gal'][0] . ' pla=' . $matches['pla'][0];
					}
					if($DEBUG)
						aprint(array(
							'pattern' => htmlentities($p),
							'parsed' => $matches,
						), 'Flottenzusammensetzung');
				}
			} else if(strpos($v, 'Galaxiemitglieder') !== false) {
				$p = "/^(?P<gal>\\d+):(?P<pla>\\d+).(?P<name>[\\w\\.\\-äöü:()]+).+?(?P<pkt>(?:\\d+\\.)*\\d+).+?(?P<s>\\d+).+?(?P<d>\\d+).+?(?P<me>\\d+).\\/.(?P<ke>\\d+).+?(?P<a>\\d+)/m";
				preg_match_all($p, $v, $matches);
				mysqlEscapeMatchResult($matches);
				if(count($matches) > 0) {
					$num = count($matches['gal']);
					$t = date('H:i d.m.Y');
					for($i = 0; $i < $num; $i++) {
						$sql[] = "DELETE FROM gn4scans WHERE rg='" . $matches['gal'][$i] . "' AND rp='" . $matches['pla'][$i] . "' AND `type`=0";
						$sql[] = "INSERT INTO gn4scans (
									zeit, type, g, p, rg, rp, gen,
									pts, s, d, me, ke, a
								) VALUES (
									'" . $t . "',
									0,
									'" . $Benutzer['galaxie'] . "',
									'" . $Benutzer['planet'] . "',
									'" . $matches['gal'][$i] . "',
									'" . $matches['pla'][$i] . "',
									99,
									'" . str_replace('.', '', $matches['pkt'][$i]) . "',
									'" . $matches['s'][$i] . "',
									'" . $matches['d'][$i] . "',
									'" . $matches['me'][$i] . "',
									'" . $matches['ke'][$i] . "',
									'" . $matches['a'][$i] . "'
								)";
						$importlog[] = 'SEKTOR t=' . $t . ' gen=99% gal=' . $matches['gal'][$i] . ' pla=' . $matches['pla'][$i];
					}
					if($DEBUG)
						aprint(array(
							'pattern' => htmlentities($p),
							'parsed' => $matches,
						), 'Galaxiemitglieder');
				} else {
					showImportError($v, $matches, $p);
				}
			} else if(strpos($v, 'Flottenbewegungen') !== false) {
				$doNotRelocate = true;
				include('function.updatefleett.php');
				//.*?(?P<gal>\d+):(?P<pla>\d+).+?(?P<name>[\w\.\-äöü]+)(?: \*)?\t(?P<greift_an>[^\t]*)\t(?P<greift_an_t>[^\t]*)\t(?P<verteidigt>[^\t]*)\t(?P<verteidigt_t>[^\t]*)\t(?P<angegriffen_von>[^\t]*)\t(?P<angegriffen_von_t>[^\t]*)\t(?P<verteidigt_von>[^\t]*)\t(?P<verteidigt_von_t>[\s\S]*?^(?=\d+:\d+.+\w))?
				$p = "/.*?(?P<gal>\\d+):(?P<pla>\\d+).+?(?P<name>[\\w\\.\\-äöü:()]+)(?: \\*)?\\t(?P<greift_an>[^\\t]*)\\t(?P<greift_an_t>[^\\t]*)\\t(?P<verteidigt>[^\\t]*)\\t(?P<verteidigt_t>[^\\t]*)\\t(?P<angegriffen_von>[^\\t]*)\\t(?P<angegriffen_von_t>[^\\t]*)\\t(?P<verteidigt_von>[^\\t]*)\\t(?P<verteidigt_von_t>[\\s\\S]*?^(?=\\d+:\\d+.+\\w))?/m";
				preg_match_all($p, $v, $matches);

				//aprint($matches);

				mysqlEscapeMatchResult($matches);

				$pattern_fleets = "/^Rückflug\\s+\\((?P<rfgal>\\d+).(?P<rfpla>\\d+).+?(?<rfname>[\\w\\.\\äöü:()]+)\\)|^(?P<gal>\\d+).(?P<pla>\\d+).+?(?P<name>[\\w\\.ä�:()]+)/m";
				$pattern_times ="/^(?P<minuten>\\d+).Min|^(?P<stunden>\\d+).Std|^(?P<xstunden>\\d+):(?P<xminuten>\\d+):(?P<xsekunden>\\d+)|^(?P<ystunden>\\d+):(?P<yminuten>\\d+)|^(?P<ticks>\\d+)/m";
				$fleets = array();

				$tickNow = floor(time() / 15 / 60) * 15 * 60;

				//for each player
				for($i = 0; $i < count($matches['gal']); $i++) {
					//modus
					//1 "angreifen";
					//2 "verteidigen";
					//3 "rueckflug_angreifen";
					//4 "rueckflug_verteidigen";
					$atter = 0;
					$deffer = 0;
					$modus = -1;
					$timeInTicks = -1;

					$types = array('greift_an', 'angegriffen_von', 'verteidigt', 'verteidigt_von');
					foreach($types as $t) {
						if(isset($matches[$t][$i])) {
							preg_match_all($pattern_fleets, $matches[$t][$i], $tmp1);
							preg_match_all($pattern_times, $matches[$t.'_t'][$i], $tmp2);
							$thisgal = array($matches['gal'][$i], $matches['pla'][$i]);
							for($j = 0; $j < count($tmp1[0]); $j++) {
								if(strlen($tmp1['rfgal'][$j]) > 0) {
									$othergal = array($tmp1['rfgal'][$j], $tmp1['rfpla'][$j]);
									$modus = (strpos($t, 'verteidigt') !== false) ? 4 : 3;
								} else {
									$othergal = array($tmp1['gal'][$j], $tmp1['pla'][$j]);
									$modus = (strpos($t, 'verteidigt') !== false) ? 2 : 1;
								}

								//definition where the fleet has its origin
								$atter = array($thisgal[0], $thisgal[1]);
								$deffer = array($othergal[0], $othergal[1]);
								
								if($t == 'angegriffen_von' || $t == 'verteidigt_von') {
									//skip fleet if same gala - already recognized before while atting or deffing.
									if($thisgal[0] == $othergal[0]) {
										continue;
									}
									
									$atter = array($othergal[0], $othergal[1]);
									$deffer = array($thisgal[0], $thisgal[1]);
								}

								$timeInTicks = getTimeInTicks($tmp2, $j);
								$fleets[] = array(
									'atter' => $atter,
									'deffer' => $deffer,
									'modus' => $modus,
									'eta' => $timeInTicks,
									'ankunft' => $tickNow + $timeInTicks * 60 * 15,
									'flottennr' => 0
								);

								if($j > 0) {
									$indexNow = count($fleets)-1;
									$indexBefore = $indexNow-1;
									if(
										$fleets[$indexNow]['atter'][0] == $fleets[$indexBefore]['atter'][0]
										&& $fleets[$indexNow]['atter'][1] == $fleets[$indexBefore]['atter'][1]
										&& (
											$fleets[$indexNow]['modus'] == $fleets[$indexBefore]['modus']
											|| $fleets[$indexNow]['modus']+2 == $fleets[$indexBefore]['modus']
											|| $fleets[$indexNow]['modus'] == $fleets[$indexBefore]['modus']+2
										)
									) {
										$fleets[$indexBefore]['flottennr'] = 1;
										$fleets[$indexNow]['flottennr'] = 2;
										//aprint(array($fleets[$indexBefore], $fleets[$indexNow]));
									}
								}
							} //for fleets of this flight type
						}//is array
					}//type
				} //for player
				if($DEBUG) aprint($fleets, 'fleets from import');

				$gal = $matches['gal'][0];
				//get fleet data
				$sql2 = 'SELECT id, modus, angreifer_galaxie, angreifer_planet, verteidiger_galaxie, verteidiger_planet, save, eta, flugzeit, flottennr, ankunft, flugzeit_ende, ruckflug_ende, reported_to_slack FROM gn4flottenbewegungen WHERE angreifer_galaxie = "'.$gal.'" OR verteidiger_galaxie = "'.$gal.'"';
				if($DEBUG) aprint($sql2);
				$res = tic_mysql_query($sql2, __FILE__, __LINE__);
				$num = mysql_num_rows($res);

				$fleetupdate_sql = [];

				//foreach fleet in database
				for($i = 0; $i < $num; $i++) {
					$fag = mysql_result($res, $i, 'angreifer_galaxie');
					$fap = mysql_result($res, $i, 'angreifer_planet');
					$fvg = mysql_result($res, $i, 'verteidiger_galaxie');
					$fvp = mysql_result($res, $i, 'verteidiger_planet');
					$fm = mysql_result($res, $i, 'modus');
					$fid = mysql_result($res, $i, 'id');
					$feta = mysql_result($res, $i, 'eta');
					$fnr = mysql_result($res, $i, 'flottennr');


					if($DEBUG) aprint(array(
							'atter' => array($fag, $fap),
							'deffer' => array($fvg, $fvp),
							'modus' => $fm,
							'eta' => $feta,
							'flottennr' => $fnr
						), 'fleet id='.$fid);


					$valid = false;
					//check if we have a corresponding one
					$found = false;
					foreach($fleets as $k => $v) {
						if($fleets[$k]['atter'][0] == $fag && $fleets[$k]['atter'][1] == $fap && $fleets[$k]['deffer'][0] == $fvg && $fleets[$k]['deffer'][1] == $fvp) {
							if($DEBUG) aprint('koords match fid=' . $fid . ' vs key='.$k);
							if($fm == $fleets[$k]['modus'] && ($feta == $fleets[$k]['eta'] || $feta == 1 && $fleets[$k]['eta'] == 0)) {
								//ugly corner case - galaxy network already shows eta0 - however, the tic then has eta 1.
								$found = true;
								if($DEBUG) aprint(array($k => $v), 'keep fleet fid=' . $fid);
							} else if($fleets[$k]['modus'] == 3 && $fm == 1 ||  $fleets[$k]['modus'] == 4 && $fm == 2) {
								$found = true;
						$sql[] = "UPDATE gn4flottenbewegungen SET modus = '" . $fleets[$k]['modus'] . "', flugzeit=0, eta='" . $fleets[$k]['eta'] . "', ankunft=0, flugzeit_ende=0, erfasser='".$Benutzer['name']."', erfasst_am='".date('H:i \U\h\r \a\m d.m.Y')."', ruckflug_ende='".($tickNow + $fleets[$k]['eta'] * 15 * 60) ."' WHERE id = '" . $fid . "'";
								$importlog[] = 'FLEET UPDATE RETURN atter_gal=' . $fleets[$k]['atter'][0] . ' atter_pla=' . $fleets[$k]['atter'][1] . ' deffer_gal=' . $fleets[$k]['deffer'][0] . ' deffer_pla=' . $fleets[$k]['deffer'][1] . ' eta=' . $fleets[$k]['eta'];
								if($DEBUG) aprint(array($k => $v), 'switch mode to RF fid=' . $fid);

								$logstr = 'Flotte ge&auml;ndert alt=('.$fag.':'.$fap.'-#'.$fid.'->'.$fvg.':'.$fvp.' modus='.$fm.' eta='.$feta.')';
								LogAction($logstr, LOG_SETSAFE);
							}

							if($found) {
								//aprint(array($fleets[$k], $fnr));
								if($fleets[$k]['flottennr'] && $fnr == 0) {
									$sql[] = "UPDATE gn4flottenbewegungen SET flottennr = '" . $fleets[$k]['flottennr'] . "' WHERE id = '" . $fid . "'";
									$importlog[] = 'FLEET UPDATE FLEET_NO atter_gal=' . $fleets[$k]['atter'][0] . ' atter_pla=' . $fleets[$k]['atter'][1] . ' deffer_gal=' . $fleets[$k]['deffer'][0] . ' deffer_pla=' . $fleets[$k]['deffer'][1] . ' eta=' . $fleets[$k]['eta'] . ' fleetnr=' . $fleets[$k]['flottennr'];
									if($DEBUG) aprint(array($k => $v), 'update fleet number fid=' . $fid);

									$logstr = 'Flotte ge&auml;ndert alt=('.$fag.':'.$fap.'-#'.$fid.'->'.$fvg.':'.$fvp.' modus='.$fm.' eta='.$feta.')';
									LogAction($logstr, LOG_SETSAFE);
								}
								//remove from list to be processed.
								$valid = true;
								unset($fleets[$k]);
								break;
							}
						}
					}//foreach fleet in import

					if(!$valid) {
					$sql[] = 'DELETE FROM gn4flottenbewegungen WHERE id =' . $fid;
						$importlog[] = 'FLEET DELETE id=' . $fid;
						if($DEBUG) aprint('delete fleet id=' . $fid);
						$logstr = 'Flotte gel&ouml;scht ('.$fag.':'.$fap.'-#'.$fid.'->'.$fvg.':'.$fvp.' modus='.$fm.' eta='.$feta.')';
						LogAction($logstr, LOG_SETSAFE);
					}
				}//for each fleet in db

				//now add the remaining fleets
				foreach($fleets as $k=>$v) {
					//- Angriff: 450 Minuten (7,5 Stunden) beträgt die Flugzeit bei Angriffsflügen zwischen Galaxien. Die Operationszeit im Zielsektor beträgt demnach maximal 75 Minuten (1,25 Stunden)
					//- Verteidigung: Die geringste Zeit braucht ein Verteidigungsflug innerhalb der eigenen Galaxie oder Allianz, dort schlägt sie mit 270 Minuten (4,5 Stunden) zu Buche. Zusätzlich gibt es noch einen Verteidigungszeitbonus für alle Spieler, die miteinander verbündet sind. Hier beträgt die Flugzeit 300 Minuten (5 Stunden). Die Operationszeiten für die Galaxie-, Allianz- und Bündnisverteidigungsflüge betragen 300 Minuten (5 Stunden). Alle anderen Verteidigungsflüge dauern 405 Minuten (6,75 Stunden), die verbleibende Operationszeit beträgt hier 165 Minuten (2,75 Stunden).
					$flugzeit = 5;
					$flugdauer = 450 / 15;
					if($v['modus'] == 2 || $v['modus'] == 4) {
						$res = tic_mysql_query("SELECT
												ac1.allianz = ac2.allianz as same_ally,
												al1.ticid = al2.ticid as same_meta
											FROM
												gn4accounts ac1
											LEFT JOIN
												gn4accounts ac2 ON ac2.galaxie = '" . $v['atter'][0] . "'
											LEFT JOIN
												gn4allianzen al1 ON al1.id = ac1.allianz
											LEFT JOIN
												gn4allianzen al2 ON al2.id = ac2.allianz
											WHERE
												ac1.galaxie = '" . $v['deffer'][0] . "'
											GROUP BY ac1.ticid, ac2.ticid, ac1.allianz, ac2.allianz
											LIMIT 1", __FILE__, __LINE__);

						$same_ally = 0;
						$same_meta = 0;
						if(mysql_num_rows($res) > 0) {
							$same_ally = mysql_result($res, 0, 'same_ally');
							$same_meta = mysql_result($res, 0, 'same_meta');
						}

						//extern
						$flugzeit = 165 / 15;
						$flugdauer = 405 / 15;
						if($same_meta) {
							//meta
							$flugzeit = 300 / 15;
							$flugdauer = 300 / 15;
							if($same_ally) {
								//ally
								$flugzeit = 300 / 15;
								$flugdauer = 270 / 15;
							}
						}
					}//modus == 2 || modus == 4

					//calc timestamps
					$ankunft = $v['ankunft'];
					$flugzeit_ende = $v['ankunft'] + $flugzeit * 15 * 60;
					$rueckflug_ende = $flugzeit_ende + $flugdauer * 15 * 60;

					if($v['modus'] == 3 || $v['modus'] == 4) {
						$ankunft = 0;
						$flugzeit_ende = 0;
						$rueckflug_ende = $v['ankunft'];
					}

					//query
					$sql[] = 'INSERT INTO gn4flottenbewegungen (
											ticid,
											modus,
											angreifer_galaxie,
											angreifer_planet,
											verteidiger_galaxie,
											verteidiger_planet,
											save,
											eta,
											flugzeit,
											flottennr,
											ankunft,
											flugzeit_ende,
											ruckflug_ende,
											erfasser,
											erfasst_am,
											reported_to_slack
										) VALUES (
											(SELECT a.ticid FROM gn4accounts ac INNER JOIN gn4allianzen a ON a.id = ac.allianz WHERE ac.galaxie = "' . $gal . '" LIMIT 1),
											"' . $v['modus'] . '",
											"' . $v['atter'][0] . '",
											"' . $v['atter'][1] . '",
											"' . $v['deffer'][0] . '",
											"' . $v['deffer'][1] . '",
											/*important, 1 == unsafe!*/ "1",
											"' . $v['eta'] . '",
											"' . $flugzeit . '",
											"' . $v['flottennr'] . '",
											"' . $ankunft . '",
											"' . $flugzeit_ende . '",
											"' . $rueckflug_ende . '",
											"' . $Benutzer['name'] . '",
											"' . date('H:i \U\h\r \a\m d.m.Y') . '",
											NULL
										)';
					$importlog[] = 'FLEET ADD atter_gal=' . $fleets[$k]['atter'][0] . ' atter_pla=' . $fleets[$k]['atter'][1] . ' deffer_gal=' . $fleets[$k]['deffer'][0] . ' deffer_pla=' . $fleets[$k]['deffer'][1] . ' eta=' . $fleets[$k]['eta'] . ' fleetnr=' . $fleets[$k]['flottennr'] . ' mode=' . $v['modus'];
					if($DEBUG) aprint(array($k => $v), 'add fleet');

					$logstr = 'Flotte hinzugef&uuml;gt ('.$v['atter'][0].':'.$v['atter'][1].'-#'.$v['flottennr'].'->'.$v['deffer'][0].':'.$v['deffer'][1].' modus='.$v['modus'].' eta='.$v['eta'].')';
					LogAction($logstr, LOG_SETSAFE);

				}//foreach fleets

				$importlog[] = 'TAKTIK importiert';
			}//flottenbewegungen
		}//import item strlen > 0
	}//foreach import item

	if($DEBUG) aprint($sql, 'DB SQL UPDATES');
	if(count($sql) > 0 && is_array($sql)) {
		foreach($sql as $v) {
			tic_mysql_query($v, __FILE__, __LINE__);
		}
	}
}//date available

//return to import.
$modul = 'scans2';
?>
