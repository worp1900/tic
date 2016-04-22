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
    background-color: #FFFFFF;
	background-image: url(bilder/login/bgjuhe.jpg);
	background-repeat: repeat-x;
	margin-left: 10px;
	margin-top: 10px;
	margin-right: 10px;
	margin-bottom: 10px;
	scrollbar-track-color: #F0F0F0;
    scrollbar-face-color: #3366CC;
    scrollbar-base-color: #3366CC;
    scrollbar-arrow-color: #FFFFFF;
}

.heading {
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-weight: bold;
	color: #741415;
}
.menu {
	cursor: hand;
	font-family: Verdana, Arial, Helvetica, sans-serif;
	font-size: 11px;
	font-weight: bold;
	color: #FFFFFF;
	text-decoration: none;
}
.table_h {
	background-color: #741415;
	font-weight: bold;
	color: #FFFFFF;
}
.table_2 {
	background-color: #D1D4DE;
}
.table_1 {
	background-color: #A6ACBE;
}




.table_h a:link {
	color: #FFA000;
	text-decoration: NONE;
	font-weight:bold;
	font-family: Verdana;
}

.table_h a:hover {
	color: #FFA000;
	text-decoration:underlined;
	font-weight:bold;
	font-family: Verdana;
}

.table_h a:visited {
	color: #FFA000;
	text-decoration:none;
	font-weight:bold;
	font-family: Verdana;
}

.table_h a:visited:hover {
	color: #FFA000;
	text-decoration:underlined;
	font-weight:bold;
	font-family: Verdana;
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

textarea {
    background-color: #8E4243;
    font-size: 11px;
    color: #FFFFFF;
    BORDER: #000000 1px solid;
}

select {
    background-color:  #8E4243;
    BORDER: #000000 1px solid;
    font-size: 11px;
    color: #FFFFFF;
}
-->
</style>




</head>
<body vlink="#FF0000" alink="#FF0000" text="#000000">
<!-- ImageReady Slices (loginit.jpg) -->

<h4><font color="white">Bei Problemen beim Login bitte an folgende Leute wenden:
<table border="0" bgcolor="black">
<?php
include("./accdata.php");
$DBConn = mysql_connect($db_info['host'], $db_info['user'], $db_info['password']) or die(mysql_errno() . ": " . mysql_error(). "\n");
mysql_select_db($db_info['dbname'], $DBConn) or die(mysql_errno() . ": " . mysql_error(). "\n");
	
$sql =  "SELECT gn4accounts.name username, galaxie, planet, rang, gn4allianzen.name allyname from gn4accounts, gn4allianzen where gn4allianzen.id = gn4accounts.allianz AND rang > 3"; 
$Result = mysql_query($sql, $DBConn);
while($row = mysql_fetch_object($Result)) {
	printf ("<tr><td><a href=\"http://www.galaxy-network.de/game/comsys.php?action=sendmsg&toid1=%d&toid2=%d\" target=\"_blank\"><font color=\"#32cd32\">%s (%d:%d)</font></a></td><td><font color=\"white\">%s</font></td><td><font color=\"white\">%s</font></td></tr>", $row->galaxie, $row->planet, $row -> username, $row->galaxie, $row->planet, $row->rang == 4 ? "TIC-Techniker" : "TIC-Administrator", $row->allyname);
}
mysql_close($DBConn);
?>
</table>
</font></h4>
<form method="post" action="main.php">

<table id="Table_01" width="951" height="575" border="0" cellpadding="0" cellspacing="0" align="center">
	<tr>
		<td rowspan="7">
			<img src="bilder/login/logbg_01.jpg" width="250" height="575" alt=""></td>
		<td colspan="4">
			<img src="bilder/login/logbg_02.jpg" width="700" height="183" alt=""></td>
		<td>
			<img src="bilder/login/spacer.gif" width="1" height="183" alt=""></td>
	</tr>
	<tr>
		<td rowspan="4">
			<img src="bilder/login/logbg_03.jpg" width="85" height="126" alt=""></td>
		<td colspan="2">
			<img src="bilder/login/logbg_04.jpg" width="132" height="44" alt=""></td>
		<td rowspan="6">
			<img src="bilder/login/logbg_05.jpg" width="483" height="392" alt=""></td>
		<td>
			<img src="bilder/login/spacer.gif" width="1" height="44" alt=""></td>
	</tr>
	<tr>
		<td colspan="2" background="bilder/login/logbg_06.jpg" width="132" height="37" alt="">

                       <INPUT TYPE="text" NAME="username" SIZE=20 MAXLENGTH=30>


                        </td>
		<td>
			<img src="bilder/login/spacer.gif" width="1" height="37" alt=""></td>
	</tr>
	<tr>
		<td colspan="2" width="132" height="37" background="bilder/login/logbg_07.jpg">

                        <INPUT TYPE="password" NAME="userpass" id="userpass" SIZE=20 MAXLENGTH=30>

			</td>
		<td>
			<img src="bilder/login/spacer.gif" width="1" height="37" alt=""></td>
	</tr>
	<tr>
		<td rowspan="2">
			<img src="bilder/login/logbg_08.jpg" width="66" height="23" alt=""></td>


		<td rowspan="2" background="bilder/login/logbg_09.jpg" width="66" height="23" alt="">


                       <INPUT TYPE="submit" NAME="login" id="login" VALUE="Login">
                </td>



		<td>
			<img src="bilder/login/spacer.gif" width="1" height="8" alt=""></td>
	</tr>
	<tr>
		<td>
			<img src="bilder/login/logbg_10.jpg" width="85" height="15" alt=""></td>
		<td>
			<img src="bilder/login/spacer.gif" width="1" height="15" alt=""></td>
	</tr>
	<tr>
		<td colspan="3">
			<img src="bilder/login/logbg_11.jpg" width="217" height="251" alt=""></td>
		<td>
			<img src="bilder/login/spacer.gif" width="1" height="251" alt=""></td>
	</tr>
	</form>
</table>
</body>
</html>
