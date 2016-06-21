<?
$tsec = $Ticks['lange']*60;

// Diverenzberechnung ohne Cron-JOB!!!
// Dazu gefgt von Mojah 2004
$res = tic_mysql_query('SELECT time, count FROM `gn4cron` ;', $SQL_DBConn) or die(mysql_errno()." - ".mysql_error());

$null_ticks = (int)(time() / ($tsec));

$alt_ticks = 0;
if(mysql_affected_rows()==0)
{
	tic_mysql_query("INSERT INTO gn4cron (time,count) VALUES (".$null_ticks.",0);", $SQL_DBConn) or die(mysql_errno()." - ".mysql_error());
	$alt_ticks = $null_ticks;
}
else
{
	$alt_ticks = mysql_result($res,0);
}

$alt_count = mt_rand();

if ($alt_ticks< $null_ticks)
{
	tic_mysql_query("UPDATE gn4cron set time=$null_ticks, count=$alt_count ;", $SQL_DBConn) or die(mysql_errno()." - ".mysql_error());
}

$div_ticks = $null_ticks - $alt_ticks;

// cron Berechnungen
if($div_ticks > 0)
{
	//delete scanrequests not successful and old:
	$days = 2;
	$sql = "DELETE FROM gn4scanrequests
				WHERE
				(
					(
						scantyp IN (0, 1, 2, 3)
						AND
						NOT EXISTS(
							SELECT * FROM gn4scans s WHERE s.rg = ziel_g AND s.rp = ziel_p AND s.type = scantyp AND UNIX_TIMESTAMP(STR_TO_DATE(s.zeit, '%H:%i %d.%m.%Y')) > t
						)
					)
					OR
					(
						scantyp = 4
						AND
						NOT EXISTS(
							SELECT * FROM gn4scans_news n WHERE n.ziel_g = ziel_g AND n.ziel_p = ziel_p AND n.t > t
						)
					)
				)
				AND
				t < UNIX_TIMESTAMP(NOW()) - ".$days."*24*60*60";
	tic_mysql_query($sql, __FILE__, __LINE__);

	//30days
	tic_mysql_query('DELETE FROM gn4shorturls WHERE t < UNIX_TIMESTAMP(NOW()) - 60*60*24*30');


    $SQL_Result = tic_mysql_query('SELECT * FROM `gn4flottenbewegungen`  ORDER BY id;', $SQL_DBConn) or die(mysql_errno()." - ".mysql_error());
    $SQL_Num = mysql_num_rows($SQL_Result);

	if ($SQL_Num != 0)
	{

		for ($n = 0; $n < $SQL_Num; $n++) {
			$eintrag_id = mysql_result($SQL_Result, $n, 'id');
			$eintrag_modus = mysql_result($SQL_Result, $n, 'modus');
			$eintrag_angreifer_galaxie = mysql_result($SQL_Result, $n, 'angreifer_galaxie');
			$eintrag_angreifer_planet = mysql_result($SQL_Result, $n, 'angreifer_planet');
			$eintrag_verteidiger_galaxie = mysql_result($SQL_Result, $n, 'verteidiger_galaxie');
			$eintrag_verteidiger_planet = mysql_result($SQL_Result, $n, 'verteidiger_planet');
			$eintrag_eta = mysql_result($SQL_Result, $n, 'eta');
			$eintrag_flugzeit = mysql_result($SQL_Result, $n, 'flugzeit');

			$ankunft = mysql_result($SQL_Result, $n, 'ankunft');
			$flugzeit_ende = mysql_result($SQL_Result, $n, 'flugzeit_ende');
			$ruckflug_ende = mysql_result($SQL_Result, $n, 'ruckflug_ende');

			if ($ruckflug_ende == 0) {
				echo "Alte Daten! Cronjob ausgeführt<br />";
				continue;
			}

			// Debug Infos!
			//echo "$eintrag_angreifer_name => $eintrag_verteidiger_name<br />";
			//echo "ank=".date("d.m.y H:i",$ankunft)."; fz=".date("d.m.y H:i",$flugzeit_ende)."; rfz=".date("d.m.y H:i",$ruckflug_ende)."<br />";


			$akt_time = ((int)(time()/($tsec)))*($tsec);

			// Noch auf Hinflug???
			if($ankunft > $akt_time)
			{
				$eintrag_eta = (int)(($ankunft - $akt_time)/($tsec));
				$SQL_Result2 = tic_mysql_query('UPDATE `gn4flottenbewegungen` SET eta="'.$eintrag_eta.'" WHERE id="'.$eintrag_id.'" ;', $SQL_DBConn) or die(mysql_errno()." - ".mysql_error());
				//echo "Hin: UPDATE `gn4flottenbewegungen` SET eta='$eintrag_eta' WHERE id='$eintrag_id';<br />";
			}
			// Angriff oder Verteidigung ??
			elseif($flugzeit_ende > $akt_time)
			{
				$eintrag_flugzeit = (int)(($flugzeit_ende - $akt_time)/($tsec));
				$SQL_Result2 = tic_mysql_query('UPDATE `gn4flottenbewegungen` SET flugzeit="'.$eintrag_flugzeit.'", eta=0 WHERE id="'.$eintrag_id.'" ;', $SQL_DBConn) or die(mysql_errno()." - ".mysql_error());
				//echo "Ang/Vert: UPDATE `gn4flottenbewegungen` SET flugzeit='$eintrag_flugzeit', eta=0 WHERE id='.$eintrag_id.';'<br />";
			}
			// Schon Zurück??
			elseif($ruckflug_ende <= $akt_time)
			{
				$SQL_Result2 = tic_mysql_query('DELETE FROM `gn4flottenbewegungen` WHERE id='.$eintrag_id, $SQL_DBConn) or die(mysql_errno()." - ".mysql_error());
				//echo "ENDE: DELETE FROM `gn4flottenbewegungen` WHERE id=$eintrag_id<br />";
			}
			// Auf Rückflug ??
			elseif($ruckflug_ende > $akt_time)
			{
				$eintrag_eta = (int)(($ruckflug_ende - $akt_time)/($tsec));
				if($eintrag_modus==1) {
					$SQL_Result2 = tic_mysql_query('UPDATE `gn4flottenbewegungen` SET modus="3", flugzeit="0", eta="'.$eintrag_eta.'" WHERE id="'.$eintrag_id.'";', $SQL_DBConn) or die(mysql_errno()." - ".mysql_error());
					//echo "Rück: UPDATE `gn4flottenbewegungen` SET modus='0', flugzeit='0', eta='$eintrag_eta' WHERE id='$eintrag_id';'<br />";
				}
				if($eintrag_modus==2) {
					$SQL_Result2 = tic_mysql_query('UPDATE `gn4flottenbewegungen` SET modus="4", flugzeit="0", eta="'.$eintrag_eta.'" WHERE id="'.$eintrag_id.'";', $SQL_DBConn) or die(mysql_errno()." - ".mysql_error());
					//echo "Rück: UPDATE `gn4flottenbewegungen` SET modus='0', flugzeit='0', eta='$eintrag_eta' WHERE id='$eintrag_id';'<br />";
				}
				tic_mysql_query('UPDATE `gn4flottenbewegungen` SET flugzeit="0", eta="'.$eintrag_eta.'" WHERE id="'.$eintrag_id.'";', __FILE__, __LINE__);
			}
		}//for rows
	}

	//update timestamp
	$time = $null_ticks * ($tsec);
	$SQL_Result = tic_mysql_query('UPDATE `gn4vars` SET value="'.date("H:i:s", $time).'" WHERE name="lasttick";', $SQL_DBConn) or die(mysql_errno()." - ".mysql_error());

	include "cleanscans.php";
}//div ticks > 0
?>
