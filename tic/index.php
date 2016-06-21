<?php

@session_start();
// Session-Registrieren
if (isset($_SESSION['is_auth']) && $_SESSION['is_auth']==1) {
	header("Location: ./main.php");
	echo "<a href='main.php'>hier geht es weiter...</a>";

	exit();
}

//echo "<pre><code>";
//print_r($_SESSION);
//echo "</code></pre>";

?>
<html>
<head>
<title>Tactical Information Center</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">

<style type="text/css">
<!--
body,td,th {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	color: #000000;
	font-size: 11px;
}
body {
    background-color: #aaaaaa;
	margin-left: 10px;
	margin-top: 10px;
	margin-right: 10px;
	margin-bottom: 10px;
}

INPUT {
    background-color: #8E4243;
    font-size: 11px;
	color: #FFFFFF;
    BORDER: #000000 1px solid;
}

INPUT.BUTTON {
    background-color: #2B3762;
    font-size: 11px;
    color: #FFFFFF;
    BORDER-RIGHT: #6E7AA6  1px solid;
    BORDER-LEFT: #D6D9E1 1px solid;
    BORDER-TOP: #D6D9E1 1px solid;
    BORDER-BOTTOM: #6E7AA6 1px solid;
    height: 21px;
}
-->
</style>




</head>
<body vlink="#FF0000" alink="#FF0000" text="#000000">
<!-- ImageReady Slices (loginit.jpg) -->

<h4>Bei Problemen beim Login bitte an folgende Leute wenden:
<table border="0" bgcolor="black">
<?php
include("./accdata.php");
$DBConn = mysql_connect($db_info['host'], $db_info['user'], $db_info['password']) or die(mysql_errno() . ": " . mysql_error(). "\n");
mysql_select_db($db_info['dbname'], $DBConn) or die(mysql_errno() . ": " . mysql_error(). "\n");
	
$sql =  "SELECT gn4accounts.name username, galaxie, planet, rang, gn4allianzen.name allyname from gn4accounts, gn4allianzen where gn4allianzen.id = gn4accounts.allianz AND rang > 3"; 
$Result = mysql_query($sql, $DBConn);
while($row = mysql_fetch_object($Result)) {
	printf ("<tr><td><a href=\"http://www.galaxy-network.net/game/comsys.php?action=sendmsg&toid1=%d&toid2=%d\" target=\"_blank\"><font color=\"#32cd32\">%s (%d:%d)</font></a></td><td><font color=\"white\">%s</font></td><td><font color=\"white\">%s</font></td></tr>", $row->galaxie, $row->planet, $row -> username, $row->galaxie, $row->planet, $row->rang == 4 ? "TIC-Techniker" : "TIC-Administrator", $row->allyname);
}
mysql_close($DBConn);
?>
</table>
</h4>
<form method="post" action="main.php">

<table id="Table_01" border="0" cellpadding="2" cellspacing="0" align="center">
	<tr>
		<th>Login:</th>
	</tr>
	<tr>
		<td>
                       <INPUT TYPE="text" NAME="username" SIZE=20 MAXLENGTH=30>
                        </td>
	</tr>
	<tr>
		<td>
                        <INPUT TYPE="password" NAME="userpass" id="userpass" SIZE=20 MAXLENGTH=30>
			</td>
		<td>
                       <INPUT TYPE="submit" NAME="login" id="login" VALUE="Login">
                </td>
	</form>
</table>
</body>
</html>
