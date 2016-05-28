<?php
	//neuen kommentar hinzufügen
	$ziel_g = isset($_POST['kommentar_g']) ? $_POST['kommentar_g'] : null;
	$ziel_p = isset($_POST['kommentar_p']) ? $_POST['kommentar_p'] : null;
	
	
	if(isset($_GET['del'])) {
		$sql = "SELECT erfasser_g, erfasser_p FROM gn4flottenbewegungen_kommentare WHERE id = '" . mysql_real_escape_string($_GET['del']) . "' AND erfasser_g = '" . $Benutzer['galaxie'] . "' AND erfasser_p = '" . $Benutzer['planet'] . "'";
		$num = mysql_num_rows(tic_mysql_query($sql, __FILE__, __LINE__));
 
		if($Benutzer['rang'] >= $Rang_GC  || $num > 0) {
			LogAction("Flottenkommentar: id='" . $_GET['del'] . "' gelÃ¶scht.");
			$sql = "DELETE FROM gn4flottenbewegungen_kommentare WHERE id = '" . mysql_real_escape_string($_GET['del']) . "'";
			//aprint($sql);
			tic_mysql_query($sql, __FILE__, __LINE__);
		} else {
			aprint('Keine Berechtigung.');
		}
	} else if(is_numeric($ziel_g) && is_numeric($ziel_p)) {
		$kommentar = mysql_real_escape_string($_POST['kommentar']);
		$sql = "INSERT INTO `gn`.`gn4flottenbewegungen_kommentare` (`id`, `g`, `p`, `erfasser_g`, `erfasser_p`, `kommentar`, `t`) VALUES (NULL, '" . mysql_real_escape_string($ziel_g) . "', '" . mysql_real_escape_string($ziel_p) . "', '" . $Benutzer['galaxie'] . "', '" . $Benutzer['planet'] . "', '" . $kommentar . "', UNIX_TIMESTAMP(NOW()));";
		tic_mysql_query($sql, __FILE__, __LINE__);
	}

?>