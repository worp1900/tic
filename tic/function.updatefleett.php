<?php
	//update timestamp for gal-fleetimport
	$sql = "INSERT INTO gn4galfleetupdated (gal, t, erfasser) VALUES (" . $Benutzer['galaxie'] . ", unix_timestamp(now()), '" . $Benutzer['name'] . "')
ON DUPLICATE KEY UPDATE t=unix_timestamp(now()), erfasser='" . $Benutzer['name'] . "';";
	tic_mysql_query($sql, $SQL_DBConn);
?>