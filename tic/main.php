<?PHP
/*
	##########################################################
	#                                                        #
	#  T.I.C. | Tactical Information Center                  #
	#                                                        #
	#  Allianzorganisationstool für Galaxy-Network           #
	#  von NataS alias Tobias Sarnowski                      #
	#  von Pomel alias Achim Pomorin                         #
	#  von Abrafax                                           #
	#  vom tic-entwickler.de Team                            #
	#  und mit bytehoppers                                   #
	#                                                        #
	##########################################################
*/
	//error_reporting(E_ALL); // zu testzwecken einschalten
	ob_start("ob_gzhandler");

	header("Content-Type: text/html;charset=UTF-8");
	
	include("sessionhelpers.inc.php");
	$_GET = injsafe($_GET);
	$_POST = injsafe($_POST);
	foreach ($_GET as $key => $val) { $$key = $val; }

	// Session-Registrieren
	session_start();
	if (!isset($_SESSION['is_auth']) || $_SESSION['is_auth']!=1) {
		if ($userid=check_user($_POST['username'], $_POST['userpass'])) {
			$_SESSION['is_auth'] = 1;
			$_SESSION['userid'] = $userid;
		} else {
			$_SESSION['is_auth'] = 0;
			$_SESSION['userid'] = -1;
			header('Location: .');
			die("Ihre Anmeldedaten waren nicht korrekt!<br/><a href='./' target='_self'>Zum Login</a>");
		}
	}



	$mtime = microtime();
	$mtime = explode(" ", $mtime);
	$mtime = $mtime[1] + $mtime[0];
	$start_time = $mtime;

	$version = "1.45.0dev";

	include("./accdata.php");
	include("./globalvars.php");
	include("./functions.php");

	// Kein Fehler zu Beginn ^^
	$error_code = 0;

	// HTML Style
	$htmlstyle['hell'] = 'eeeeee';
	$htmlstyle['dunkel'] = 'dddddd';
	$htmlstyle['hell_rot'] = 'ffaaaa';
	$htmlstyle['dunkel_rot'] = 'ff8888';
	$htmlstyle['hell_gruen'] = 'aaffaa';
	$htmlstyle['dunkel_gruen'] = '88ff88';
	$htmlstyle['hell_blau'] = 'aaaaff';
	$htmlstyle['dunkel_blau'] = '8888ff';

	$SQL_Result = tic_mysql_query("SELECT * FROM `gn4accounts` WHERE id='".$_SESSION['userid']."'") or die(tic_mysql_error(__FILE__,__LINE__));
	if (mysql_num_rows($SQL_Result) == 1)
	{
		// Nameinfos setzen
		$Benutzer['id'] = mysql_result($SQL_Result, 0, 'id');
		$Benutzer['ticid'] = mysql_result($SQL_Result, 0, 'ticid');
		$Benutzer['name'] = mysql_result($SQL_Result, 0, 'name');
		$Benutzer['galaxie'] = mysql_result($SQL_Result, 0, 'galaxie');
		$Benutzer['pwdandern'] = mysql_result($SQL_Result, 0, 'pwdandern');
		$Benutzer['planet'] = mysql_result($SQL_Result, 0, 'planet');
		$Benutzer['rang'] = mysql_result($SQL_Result, 0, 'rang');
		$Benutzer['allianz'] = mysql_result($SQL_Result, 0, 'allianz');
		$Benutzer['scantyp'] = mysql_result($SQL_Result, 0, 'scantyp');
		$Benutzer['zeitformat'] = mysql_result($SQL_Result, 0, 'zeitformat');
		$Benutzer['svs'] = mysql_result($SQL_Result, 0, 'svs');
		$Benutzer['sbs'] = mysql_result($SQL_Result, 0, 'sbs');
		$Benutzer['umod'] = mysql_result($SQL_Result, 0, 'umod');
		$Benutzer['spy'] = mysql_result($SQL_Result, 0, 'spy');
		$Benutzer['help'] = mysql_result($SQL_Result, 0, 'help');
		$Benutzer['tcausw'] = mysql_result($SQL_Result, 0, 'tcausw');
		$Benutzer['scananfragen'] = mysql_result($SQL_Result, 0, 'scananfragen');
		$Benutzer['offfleets'] = mysql_result($SQL_Result, 0, 'off_fleets');

// Erweiterung von Bytehoppers vom 20.07.05 für Attplaner2
		@$Benutzer['attplaner'] = mysql_result($SQL_Result, 0, 'attplaner');
	}
	else
	{
		die("<a href=\"index.php\" target=\"_top\">Neu Einloggen</a>");
	}


	// Variablen laden
	include("./vars.php");
	// Pseudo-Cron
	include("./cron.php");

	// Standardmodul wählen falls nicht angegeben
	if(isset($_POST['modul']) && $_POST['modul'] != "")
		$modul = $_POST['modul'];
	else if(isset($_GET['modul']) && $_GET['modul'] != "")
		$modul = $_GET['modul'];
	else
		$modul = "nachrichten";

	$SQL_Result2 = tic_mysql_query("SELECT pts, s, d, me, ke FROM `gn4scans` WHERE rg='".$Benutzer['galaxie']."' AND rp='".$Benutzer['planet']."' AND type='0'") or die(tic_mysql_error(__FILE__,__LINE__));
	if (mysql_num_rows($SQL_Result2) != 1)
	{
		$Benutzer['punkte'] = 0;
		$Benutzer['schiffe'] = 0;
		$Benutzer['defensiv'] = 0;
		$Benutzer['exen_m'] = 0;
		$Benutzer['exen_k'] = 0;
	}
	else
	{
		$Benutzer['punkte'] = mysql_result($SQL_Result2, 0, 'pts');
		$Benutzer['schiffe'] = mysql_result($SQL_Result2, 0, 's');
		$Benutzer['defensiv'] = mysql_result($SQL_Result2, 0, 'd');
		$Benutzer['exen_m'] = mysql_result($SQL_Result2, 0, 'me');
		$Benutzer['exen_k'] = mysql_result($SQL_Result2, 0, 'ke');
	}
	$SQL_Result2 = tic_mysql_query("SELECT blind FROM `gn4allianzen` WHERE id='".$Benutzer['allianz']."' AND ticid='".$Benutzer['ticid']."'") or die(tic_mysql_error(__FILE__,__LINE__));
	if (mysql_num_rows($SQL_Result2) != 1)
	{
		$Benutzer['blind'] = 1;
	}
	else
	{
		$Benutzer['blind'] = mysql_result($SQL_Result2, 0, 'blind');
	}

	//lastlogin setzen
	tic_mysql_query("UPDATE `gn4accounts` SET lastlogin='".time()."' WHERE id='".$Benutzer['id']."' AND ticid='".$Benutzer['ticid']."'") or die(tic_mysql_error(__FILE__,__LINE__));

	// Spion???
	if($Benutzer['spy'] != 0 && $Benutzer['rang'] != RANG_STECHNIKER)
	{

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">
	<head>
		<title>TIC wird gewartet</title>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
	</head>
	<body style="background-color:#000000">
		<table height="100%" width="100%">
			<tr height="80%"><td style="text-align:center">
				<font style="font: 36pt bold arial,sans-serif; color:#ffffff;text-align:center">
					Das "Tactical Information Center" ist<br />
					 wegen Wartungsarbeiten nicht erreichbar!<p><br /></p>
				</font>
			</td></tr>
		</table>
	</body>
</html>
<?php
		exit;
	}

	if(isset($_POST['action']) && $_POST['action'] != "")
		$action = $_POST['action'];
	else if(isset($_GET['action']) && $_GET['action'] != "")
		$action = $_GET['action'];
	else
		$action = "";

	// Incoming makieren
	if (isset($_GET['need_planet']) && isset($_GET['need_galaxie']))
	{
		LogAction($_GET['need_galaxie'].":".$_GET['need_planet']." -> Unsafe", LOG_SETSAFE);
		tic_mysql_query("UPDATE `gn4flottenbewegungen` SET save='0' WHERE verteidiger_galaxie='".$_GET['need_galaxie']."' AND verteidiger_planet='".$_GET['need_planet']."'") or die(tic_mysql_error(__FILE__,__LINE__));
	}
	if (isset($_GET['needno_planet']) && isset($_GET['needno_galaxie']))
	{
		LogAction($_GET['needno_galaxie'].":".$_GET['needno_planet']." -> Safe", LOG_SETSAFE);
		tic_mysql_query("UPDATE `gn4flottenbewegungen` SET save='1' WHERE verteidiger_galaxie='".$_GET['needno_galaxie']."' AND verteidiger_planet='".$_GET['needno_planet']."'") or die(tic_mysql_error(__FILE__,__LINE__));
	}

	// Funktion einbinden
	if ($action != "")
		include("./function.".$action.".php");

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">
	<head>
		<title>TIC - <?=$MetaInfo['name']?></title>
		<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
		<meta http-equiv="refresh" content="900; URL=./main.php?<?=(isset($_GET['auto']) ? "" : "auto").($_SERVER['QUERY_STRING'] != "" ? (isset($_GET['auto']) ? "" : "&amp;").str_replace("&", "&amp;", $_SERVER['QUERY_STRING']) : "")?>" />
		<link rel="stylesheet" href="./tic.css" type="text/css" />
<!--<script>
(function(e,c,a,g,f){function d(){var b=c.createElement("script");b.async=!0;
b.src="//radar.cedexis.com/1/21643/radar.js";c.body.appendChild(b)}
(function(){for(var b=[/\bMSIE (5|6)/i],a=b.length;a--;)if(b[a]
.test(navigator.userAgent))return!1;return!0})()
&&("complete"!==c.readyState?(a=e[a])?a(f,d,!1):(a=e[g])&&a("on"+f,d):d())})
(window,document,"addEventListener","attachEvent","load");
</script>-->
		<script language="javascript" type="text/javascript">
		<!--
			function NeuFenster( link ) {
				MeinFenster = window.open( link, "Artikel", "width=800,height=300,scrollbars=yes,resizable=yes");
				MeinFenster.focus();
			}

//			if ( top.frames.length < 2) {
//				window.open("./frameset.html","_top");
//			}
		//-->
		</script>
		<script type="text/javascript" src="./overlib/overlib.js"><!-- overLIB (c) Erik Bosrup --></script>
	</head>
	<body>
<script src="https://cdn.rawgit.com/zenorocha/clipboard.js/v1.5.10/dist/clipboard.min.js"></script>
<script>
	var clipboard = new Clipboard('.btn');
	clipboard.on('success', function(e) {
		e.clearSelection();
	});

	clipboard.on('error', function(e) {
		console.error('Action:', e.action);
		console.error('Trigger:', e.trigger);
	});
</script>	
		<div id="overDiv" style="position:absolute; visibility:hidden; z-index:1000;"></div>
		<div style="position:absolute; z-index:10;width:100%">
		<!-- <div align="center" style="width:100%"><img src="bilder/skin/banner.jpg" alt="" align="middle" /></div> --> <!-- Banner -->
<?php
	include("./menu.inc.php");
?>
		<div style="position:relative; margin-left:200px; margin-right:30px;">
			<div class="info" align="center">
			<!--<font size="5"><b>T.I.C. | Tactical Information Center der Meta <?=$MetaInfo['name']?></b></font>//-->
<?php
	if ($error_code != 0)
		include("./inc_errors.php");
	else
	{
		include("./inc_accinfo.php");
		echo "			</div>\n";
		$mtime = microtime();
		$mtime = explode(" ", $mtime);
		$mtime = $mtime[1] + $mtime[0];
		$mid_time = $mtime;
		if (isset($_GET['auto']))
			echo "Auto-Refresh...";
		echo "<div class=\"main\" align=\"center\">";
		if ($Benutzer['pwdandern'] != 1) {
			include("./inc_".$modul.".php");
		} else
			include("./inc_pwdandern.php");

		if ($error_code != 0)
			include("./inc_errors.php");
	}
?>
		<div style="position:relative; width:100%; margin-top:10px;">
			<hr />
			<table width="100%"><tr>
				<td align="left" valign="top" style="font-size: 6pt; text-align: right">
					<a href="https://github.com/tuedelue/ticenter_tic" style="color: #555555;" target="_blank">T.I.C. v<?=$version?></a>
				</td>
<!--
				<td align="center" style="white-space:nowrap;">
					erstellt in
<?php
	$mtime = microtime();
	$mtime = explode(" ", $mtime);
	$mtime = $mtime[1] + $mtime[0];
	$end_time = $mtime;
	echo sprintf("%01.3f", $end_time - $start_time)."s";
	if (isset($mid_time) && $mid_time != 0)
	{
		echo " (".sprintf("%01.3f", $mid_time - $start_time)."s)\n";
	}
	echo "<br />".count_querys(false)." Datenbankabfragen\n";
?>
				</td>
			</tr></table>
//-->
		</div></div></div></div>
	</body>
</html>
