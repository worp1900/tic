<?php
$slack_nickname = mysql_real_escape_string(htmlentities($_POST['slack_nickname']));
$handy = mysql_real_escape_string(htmlentities($_POST['handy']));
$messangerID = mysql_real_escape_string(htmlentities($_POST['icq']));
$ticscreen = mysql_real_escape_string(htmlentities($_POST['ticscreen']));
$infotext = mysql_real_escape_string(htmlentities($_POST['infotext']));
$authnick = mysql_real_escape_string(htmlentities($_POST['authnick']));
$incfreigabe=mysql_real_escape_string(htmlentities($_POST['check']));
$lstZeitformat = mysql_real_escape_string(htmlentities($_POST['lstZeitformat']));
if ( $incfreigabe=='' ) {
	$incfreigabe = 0;
} else {
	$incfreigabe = 1;
}

$sql = "Update gn4accounts set handy='$handy', infotext='$infotext', authnick='$authnick', tcausw='$ticscreen', zeitformat='$lstZeitformat', messangerID='$messangerID', help='$incfreigabe', slack_nickname='$slack_nickname' where id=".$Benutzer["id"].";";
//echo $sql;
$SQL_Result = tic_mysql_query($sql);
$Benutzer["zeitformat"]=$lstZeitformat;
$Benutzer["help"]= $incfreigabe;
$Benutzer['tcausw']= $ticscreen;
$Benutzer['slack_nickname']= $slack_nickname;
$Benutzer['scananfragen'] = $scananfragen;
//echo $sql;
?>
