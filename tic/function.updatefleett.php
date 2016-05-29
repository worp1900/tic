<?php
	//update timestamp for gal-fleetimport
	tic_mysql_query("INSERT INTO gn4galfleetupdated (gal, t, erfasser) VALUES (" . $Benutzer['galaxie'] . ", unix_timestamp(now()), '" . $Benutzer['name'] . "')
ON DUPLICATE KEY UPDATE t=unix_timestamp(now()), erfasser='" . $Benutzer['name'] . "';", __FILE__, __LINE__);

if(!isset($doNotRelocate)) {
	header('Location: ' . $_SERVER['HTTP_REFERER']);
}

?>
