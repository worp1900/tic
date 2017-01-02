<h2>Danke f&uuml;r die Taktikupdates!</h2>

<?php

$sql = 'select gal, erfasser, count(1) from gn4galfleetupdated_hist group by gal, erfasser order by gal, count(1) desc';
$res = tic_mysql_query($sql);
$num = mysql_num_rows($res);
echo '<table style="text-align: left;">';

$oldgal = 0;
$color = false;
while(list($gal, $erfasser, $zahl) = mysql_fetch_row($res)) {
	if($gal != $oldgal) {
		$oldgal = $gal;
		echo '	<tr class="datatablehead">';
		echo '		<td colspan="2" style="text-align: center;">&nbsp;Galaxie '.$gal.'&nbsp;</td>';
		echo '	</tr>';
		
		$color = false;
	}
	$color = !$color;
	
	echo '	<tr class="fieldnormal'.($color ? 'light' : 'dark').'">';
	echo '		<td>&nbsp;' . $erfasser . '&nbsp;</td>';
	echo '		<td style="text-align: right;">&nbsp;' . ZahlZuText($zahl) . '&nbsp;</td>';
	echo '	</tr>';
}

echo '</table>';

?>