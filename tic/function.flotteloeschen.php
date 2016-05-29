<?PHP
// Flottenbewegung löschen
if ($_POST['action'] == 'flotteloeschen') {
	if (!isset($_POST['flottenid'])) $_POST['flottenid'] = '';
	if ($_POST['flottenid'] != '') {
		$fid = mysql_real_escape_string($_POST['flottenid']);
		$res = tic_mysql_query('SELECT modus, angreifer_galaxie, angreifer_planet, verteidiger_galaxie, verteidiger_planet, flottennr, eta FROM `gn4flottenbewegungen` WHERE id="'.$fid.'"', __FILE__, __LINE__);
		$num = mysql_num_rows($res);
		if($num > 0) {
			$angreifer_gal = mysql_result($res, 0, 'angreifer_galaxie');
			$angreifer_pla = mysql_result($res, 0, 'angreifer_planet');
			$verteidiger_gal = mysql_result($res, 0, 'verteidiger_galaxie');
			$verteidiger_pla = mysql_result($res, 0, 'verteidiger_planet');
			$flotte = mysql_result($res, 0, 'flottennr');
			$eta = mysql_result($res, 0, 'eta');
			$modus = mysql_result($res, 0, 'modus');
			$logstr = 'Flotte gel&ouml;scht ('.$angreifer_gal.':'.$angreifer_pla.'-#'.$flotte.'->'.$verteidiger_gal.':'.$verteidiger_pla.' modus='.$modus.' eta='.$eta.')';
			LogAction($logstr, LOG_SETSAFE);
			
			tic_mysql_query('DELETE FROM `gn4flottenbewegungen` WHERE id="'.$fid.'"', __FILE__, __LINE__);
		}
	}
}
?>
