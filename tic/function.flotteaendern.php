<?PHP
// Flottenbewegung ändern
if ($_POST['action'] == 'flotteaendern') {
	if (!isset($_POST['flottenid'])) $_POST['flottenid'] = '';
	if (!isset($_POST['id'])) $_POST['$id'] = '';
	if (!isset($_POST['lst_Flotte'])) $_POST['lst_Flotte'] = 0;
	$tmp_modus = 0;
	$tsec = $Ticks['lange']*60;
	
	/*
	Berechne ankunftzeit, flugzeit_ende und ruckflug_ende
	*/
	$_time		= ((int)(time()/($tsec)))*($tsec);
	$_ankunft	= 0;
	$_flugzeit	= 0;
	$_ruckflug	= 0;
	
	if(isset($_POST['lst_ETA'])){
		$_ankunft  = $_time + ($_POST['lst_ETA'] * $tsec);
		$_flugzeit = $_ankunft + ($_POST['lst_Flugzeit'] * $tsec);
	}
	
	if(isset($_POST['lst_ETA0'])) $_ruckflug  = $_time + ($_POST['lst_ETA0'] * $tsec);


	if ($_POST['flottenid'] != '' && $_POST['id'] != '') {
		$fid = mysql_real_escape_string($_POST['flottenid']);
		$SQL_Result = tic_mysql_query('SELECT flugzeit_ende, ruckflug_ende, angreifer_galaxie, verteidiger_galaxie, modus FROM `gn4flottenbewegungen` WHERE id="'.$fid.'";', __FILE__, __LINE__);
		
		if (mysql_num_rows($SQL_Result) == 1) {
			$tmp_galaxie_angreifer = mysql_result($SQL_Result, 0, 'angreifer_galaxie');
			$tmp_galaxie_verteidiger = mysql_result($SQL_Result, 0, 'verteidiger_galaxie');
			$flugzeit = mysql_result($SQL_Result, 0, 'flugzeit_ende');
			$ruckflug = mysql_result($SQL_Result, 0, 'ruckflug_ende');
			$tmp_modus = mysql_result($SQL_Result, 0, 'modus');
			$_ruckflug = $_flugzeit + (eta($flugzeit,$ruckflug) * $tsec);
		}

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
			$logstr = 'Flotte ge&auml;ndert alt=('.$angreifer_gal.':'.$angreifer_pla.'-#'.$flotte.'->'.$verteidiger_gal.':'.$verteidiger_pla.' modus='.$modus.' eta='.$eta.')';
			LogAction($logstr, LOG_SETSAFE);
		}
		
		if ($_POST['optModus'] == 0 or $_POST['optModus'] == 3 or $_POST['optModus'] == 4 ) {
			switch( $tmp_modus ) {
				case 1:
				case 2:
					echo "<br>mode changed from ".$tmp_modus;
					$tmp_modus += 2;
					echo "to mode ".$tmp_modus."<br>";
					break;
				case 3:
				case 4:
					// do nothing
					break;
				case 0:
				default:    // besser als nix ...
					$tmp_modus = 3;
				break;
			}

			tic_mysql_query('UPDATE `gn4flottenbewegungen` SET modus="'.$tmp_modus.'", flugzeit="0", save="", ankunft="0", flugzeit_ende="0", eta="'.$_POST['lst_ETA0'].'", ruckflug_ende="'.$_ruckflug.'", flottennr="'.$_POST['lst_Flotte'].'" WHERE id="'.$fid.'";', __FILE__, __LINE__);
		} else {
			tic_mysql_query('UPDATE `gn4flottenbewegungen` SET modus="'.$_POST['optModus'].'", eta="'.$_POST['lst_ETA'].'", ankunft="'.$_ankunft.'", flugzeit_ende="'.$_flugzeit.'", ruckflug_ende="'.$_ruckflug.'", flugzeit="'.$_POST['lst_Flugzeit'].'", flottennr="'.$_POST['lst_Flotte'].'" WHERE id="'.$fid.'";', __FILE__, __LINE__);
		}
	}
}
?>
