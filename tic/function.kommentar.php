<?php
	//neuen kommentar hinzufügen
	$ziel_g = mysql_real_escape_string($_POST['kommentar_g']);
	$ziel_p = mysql_real_escape_string($_POST['kommentar_p']);
	$kommentar = mysql_real_escape_string($_POST['kommentar']);
	$sql = "INSERT INTO `gn`.`gn4flottenbewegungen_kommentare` (`id`, `g`, `p`, `erfasser_g`, `erfasser_p`, `kommentar`, `t`) VALUES (NULL, '" . $ziel_g . "', '" . $ziel_p . "', '" . $Benutzer['galaxie'] . "', '" . $Benutzer['planet'] . "', '" . $kommentar . "', UNIX_TIMESTAMP(NOW()));";

	tic_mysql_query($sql);
?>