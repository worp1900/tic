<?php
$id = $_GET['id'];

if($id) {
	$sql = "SELECT url FROM gn4shorturls WHERE uuid = '" . mysql_real_escape_string($id) . "' LIMIT 1";
	$res = tic_mysql_query($sql, __FILE__, __LINE__);
	if(mysql_num_rows($res) == 1) {
		$url = mysql_result($res, 0, 'url');
		header('Location: ' . $url);
		echo '<a href="'.$url.'">&raquo; weiter</a>';
	}
}
?>