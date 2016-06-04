<?php
//LINK
$link = '';

//GET RAW DATA
$in = '0';

if(isset($_POST['news']) && is_array($_POST['news'])) {
	foreach($_POST['news'] as $k => $v) {
		$in .= ', ' . mysql_real_escape_string($k);
	}
	$sql = "SELECT t, typ, inhalt FROM gn4scans_news_entries WHERE id IN (" . $in . ") ORDER BY t ASC";
	$res = tic_mysql_query($sql, __FILE__, __LINE__);
	$num = mysql_num_rows($res);

	if($num > 0) {
		//FLEETS: .*(?P<gal>\d+):(?P<pla>\d+)\s+(?P<name>[\w\.-äöü]+).+?Flotte (?P<fnr>\d)?.*?(?:(?P<RF>zurück)|(?P<deff>zur Seite)|(?P<att>an))
		$p1 = "@.*?(?P<gal>\\d+):(?P<pla>\\d+)\\s+(?P<name>[\\w\\.-äöü]+).+?Flotte (?P<fnr>\\d)?.*?(?:(?P<RF>zurück)|(?P<deff>zur Seite)|(?P<att>an))@m";

		//TIMES:  \d+:\d+.+wird in.+?(?:(?P<minuten>\d+).Min|^(?P<stunden>\d+).Std|(?P<xstunden>\d+):(?P<xminuten>\d+):(?P<xsekunden>\d+)|(?P<ystunden>\d+):(?P<yminuten>\d+)|(?P<ticks>\d+))
		$p2 = "@\\d+:\\d+.+wird in.+?(?:(?P<minuten>\\d+).Min|^(?P<stunden>\\d+).Std|(?P<xstunden>\\d+):(?P<xminuten>\\d+):(?P<xsekunden>\\d+)|(?P<ystunden>\\d+):(?P<yminuten>\\d+)|(?P<ticks>\\d+))@m";

		while(list($t, $typ, $inhalt) = mysql_fetch_row($res)) {
			$age = ceil((time()-$t)/60);
			preg_match($p1, $inhalt, $fleet_d);
			preg_match($p2, $inhalt, $fleet_t);
			$tmp = getTimeInTicksSimple($fleet_t);
			$ticks = -floor($age / 15.0) + $tmp;

			/*
			aprint(array(
				'input' => array($typ, $inhalt),
				'parsed' => $fleet_d,
				't' => array(
						't' => $t,
						'age' => $age,
						'ticks' =>$ticks,
						'time in ticks until arrival' => $tmp)
				));
			*/

			$key = $fleet_d['gal'] . ':' . $fleet_d['pla'];
			//with the sort order, we ensure first adding fleets - thus we can remove them easily.
			if($fleet_d['RF']) {
				if(count($fleets[$key]) == 0) {
					//nothing.
					unset($fleets[$key]);
					continue;
				}
				if(count($fleets[$key]) == 1) {
					//remove
					unset($fleets[$key][0]);
					unset($fleets[$key]);
				}
				if(count($fleets[$key]) == 2) {
					//ambiguous, remove second fleet.
					unset($fleets[$key][1]);
				}
			} else {
				$fleet_type = $fleet_d['att'] ? 'a' : 'd';
				$fleets[$key][] = array('fleetnr' => $fleet_d['fnr'], 'typ' => $fleet_type, 'arrivaltick' => $ticks, 'news_t' => $t);
			}

		}

		//min arrival + num ticks
		$min_arrival = null;
		$num_fleets = 0;
		$latest_arrival_atter = null;
		foreach($fleets as $v) {
			foreach($v as $v2) {
				if($min_arrival == null || $v2['arrivaltick'] < $min_arrival) {
					$min_arrival = $v2['arrivaltick'];
				}
				if($v2['typ'] == 'a' && ($latest_arrival_atter == null || $latest_arrival_atter > $v2['arrivaltick'])) {
					$latest_arrival_atter = $v2['arrivaltick'];
				}

				$num_fleets++;
			}
		}
		$num_ticks = $latest_arrival_atter - $min_arrival + 5;
		$num_ticks = ($num_ticks < 0) ? 5 : $num_ticks;

		//link
		$link .= 'main.php?modul=kampf&referenz=eintragen&compute=Berechnen&preticks=on&ticks='.$num_ticks.'&num_flotten='.$num_fleets.'&g[0]='.$_POST['rg'].'&p[0]='.$_POST['rp'].'&f[0]=0';
		$i = 0;
		foreach($fleets as $k=>$v) {
			foreach($v as $v2) {
				$i++;
				$k2 = explode(':', $k);
				$a = $v2['arrivaltick'] - $min_arrival;

				$link .= '&g['.$i.']='.$k2[0].'&p['.$i.']='.$k2[1].'&typ['.$i.']='.$v2['typ'].'&f['.$i.']='.$v2['fleetnr'].'&ankunft['.$i.']='.($a).'&aufenthalt['.$i.']='.($v2['typ'] == 'a' ? 5 : 20);
			}
		}
		$link .= '#oben';


		aprint(array(
			'links' => $link,
			'min_arrival' => $min_arrival,
			'fleets' => $fleets
			), 'participating fleets');

		echo '<a href="' . $link . '">&raquo; weiter</a>';
	}

	//RELOCATE
	if($link) {
		header('Location: ' . $link);
		echo '<a href="' . $link . '">&raquo; weiter</a>';
	}
}
$modul = 'showgalascans';

?>
