<?php
$sqlquery['gn4accounts'] = "DROP TABLE IF EXISTS `gn4accounts`;
CREATE TABLE `gn4accounts` (
  `id` int(11) NOT NULL auto_increment,
  `ticid` varchar(5) NOT NULL default '',
  `name` varchar(50) NOT NULL default '',
  `passwort` varchar(50) NOT NULL default '',
  `session` varchar(50) default NULL,
  `pwdandern` int(11) NOT NULL default '1',
  `galaxie` int(11) NOT NULL default '0',
  `planet` int(11) NOT NULL default '0',
  `rang` int(11) NOT NULL default '0',
  `allianz` int(11) NOT NULL default '0',
  `authnick` varchar(20) NOT NULL default '',
  `scantyp` int(11) NOT NULL default '0',
  `svs` bigint(11) NOT NULL default '0',
  `sbs` bigint(20) NOT NULL default '0',
  `deff` int(11) NOT NULL default '0',
  `unreadnews` int(11) NOT NULL default '1',
  `lastlogin` varchar(20) NOT NULL default '',
  `lastlogin_time` int(11) NOT NULL default '0',
  `umod` varchar(21) NOT NULL default '',
  `scans` bigint(20) NOT NULL default '0',
  `spy` int(11) NOT NULL default '0',
  `pwdStore` varchar(50) NOT NULL default '',
  `handy` varchar(50) NOT NULL default '',
  `messangerID` varchar(100) NOT NULL default '',
  `infotext` varchar(50) NOT NULL default '',
  `ip` varchar(32) NOT NULL default '',
  `zeitformat` varchar(8) NOT NULL default 'hh:mm',
  `taktiksort` varchar(10) NOT NULL default '0 asc',
  `help` int(1) NOT NULL default '1',
  `tcausw` char(1) NOT NULL default '1',
  `versuche` int(1) NOT NULL default '0',
  `attplaner` tinyint(4) default '0',
  PRIMARY KEY  (`id`)
) ;";

$sqlquery['gn4allianzen'] = "DROP TABLE IF EXISTS `gn4allianzen`;
CREATE TABLE `gn4allianzen` (
  `id` int(11) NOT NULL auto_increment,
  `ticid` varchar(5) NOT NULL default '',
  `name` varchar(255) NOT NULL default '',
  `tag` varchar(10) NOT NULL default '',
  `info_bnds` text NOT NULL,
  `info_naps` text NOT NULL,
  `info_inoffizielle_naps` text NOT NULL,
  `info_kriege` text NOT NULL,
  `code` int(11) NOT NULL default '0',
  `blind` tinyint(4) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ;";

$sqlquery['gn4cron'] = "DROP TABLE IF EXISTS `gn4cron`;
CREATE TABLE `gn4cron` (
  `time` int(14) default NULL,
  `ticid` varchar(5) NOT NULL default '',
  `count` int(14) NOT NULL default '0'
) ;
INSERT INTO `gn4cron` VALUES (0, 1, 1);";

$sqlquery['gn4flottenbewegungen'] = "DROP TABLE IF EXISTS `gn4flottenbewegungen`;
CREATE TABLE `gn4flottenbewegungen` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
  `ticid` varchar(5) NOT NULL DEFAULT '',
  `modus` int(11) NOT NULL DEFAULT '0',
  `angreifer_galaxie` int(11) NOT NULL DEFAULT '0',
  `angreifer_planet` int(11) NOT NULL DEFAULT '0',
  `verteidiger_galaxie` int(11) NOT NULL DEFAULT '0',
  `verteidiger_planet` int(11) NOT NULL DEFAULT '0',
  `save` char(1) NOT NULL DEFAULT '',
  `eta` int(11) NOT NULL DEFAULT '0',
  `flugzeit` int(11) NOT NULL DEFAULT '0',
  `flottennr` int(11) NOT NULL DEFAULT '0',
  `ankunft` int(14) NOT NULL DEFAULT '0',
  `flugzeit_ende` int(14) NOT NULL DEFAULT '0',
  `ruckflug_ende` int(14) NOT NULL DEFAULT '0',
  `tparser` tinyint(4) NOT NULL DEFAULT '0',
  `erfasser` varchar(50) NOT NULL DEFAULT '',
  `erfasst_am` varchar(55) NOT NULL DEFAULT '0',
  `reported_to_slack` int(11) DEFAULT NULL,
  PRIMARY KEY  (`id`),
  INDEX	start_koords (`angreifer_galaxie`, `angreifer_planet`),
  INDEX	ziel_koords (`verteidiger_galaxie`, `verteidiger_planet`)
);";

$sqlquery['gn4forum'] = "DROP TABLE IF EXISTS `gn4forum`;
CREATE TABLE `gn4forum` (
  `id` int(11) NOT NULL auto_increment,
  `ticid` varchar(5) NOT NULL default '',
  `autorid` int(11) NOT NULL default '0',
  `zeit` varchar(20) NOT NULL default '',
  `belongsto` int(11) NOT NULL default '0',
  `topic` varchar(50) NOT NULL default '',
  `text` text NOT NULL,
  `allianz` int(11) NOT NULL default '0',
  `priority` bigint(20) NOT NULL default '0',
  `wichtig` int(11) NOT NULL default '0',
  `lastpost` int(11) NOT NULL default '0',
  `views` bigint(20) NOT NULL default '0',
  `geandert` int(11) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ;";

$sqlquery['gn4incplanets'] = "DROP TABLE IF EXISTS `gn4incplanets`;
CREATE TABLE `gn4incplanets` (
  `ticid` varchar(5) NOT NULL default '',
  `planet` smallint(6) NOT NULL default '0',
  `gala` smallint(6) NOT NULL default '0',
  `bestaetigt` varchar(200) NOT NULL default '',
  `vorgemerkt` varchar(200) NOT NULL default '',
  `frei` tinyint(4) NOT NULL default '1'
) ;";

$sqlquery['gn4log'] = "DROP TABLE IF EXISTS `gn4log`;
CREATE TABLE `gn4log` (
  `id` int(11) NOT NULL auto_increment,
  `ticid` varchar(5) NOT NULL default '',
  `name` varchar(50) NOT NULL default '',
  `accid` int(11) NOT NULL default '0',
  `rang` int(11) NOT NULL default '0',
  `allianz` int(11) NOT NULL default '0',
  `zeit` varchar(20) NOT NULL default '',
  `aktion` text NOT NULL default '',
  `type` tinyint(1) NOT NULL default '0',
  `ip` varchar(15) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ;";

$sqlquery['gn4nachrichten'] = "DROP TABLE IF EXISTS `gn4nachrichten`;
CREATE TABLE `gn4nachrichten` (
  `id` int(11) NOT NULL auto_increment,
  `ticid` varchar(5) NOT NULL default '',
  `name` varchar(60) NOT NULL default '0',
  `titel` varchar(50) NOT NULL default '',
  `zeit` varchar(20) NOT NULL default '',
  `text` text NOT NULL,
  PRIMARY KEY  (`id`)
) ;";

$sqlquery['gn4nachtwache'] = "DROP TABLE IF EXISTS `gn4nachtwache`;
CREATE TABLE `gn4nachtwache` (
  `time` int(11) NOT NULL default '0',
  `ticid` tinyint(4) NOT NULL default '0',
  `gala` int(11) NOT NULL default '0',
  `planet1` tinyint(2) NOT NULL default '0',
  `done1` enum('0','1') NOT NULL default '0',
  `planet2` tinyint(2) NOT NULL default '0',
  `done2` enum('0','1') NOT NULL default '0',
  `planet3` tinyint(2) NOT NULL default '0',
  `done3` enum('0','1') NOT NULL default '0',
  `planet4` tinyint(2) NOT NULL default '0',
  `done4` enum('0','1') NOT NULL default '0',
  `planet5` tinyint(2) NOT NULL default '0',
  `done5` enum('0','1') NOT NULL default '0',
  `planet6` tinyint(2) NOT NULL default '0',
  `done6` enum('0','1') NOT NULL default '0',
  `planet7` tinyint(2) NOT NULL default '0',
  `done7` enum('0','1') NOT NULL default '0',
  PRIMARY KEY  (`time`,`gala`)
) ;";

$sqlquery['gn4scans'] = "DROP TABLE IF EXISTS `gn4scans`;
CREATE TABLE `gn4scans` (
  `id` bigint(20) NOT NULL auto_increment,
  `ticid` varchar(5) NOT NULL default '',
  `zeit` varchar(20) NOT NULL default '',
  `type` int(11) NOT NULL default '0',
  `g` int(11) NOT NULL default '0',
  `p` int(11) NOT NULL default '0',
  `rg` int(11) NOT NULL default '0',
  `rp` int(11) NOT NULL default '0',
  `gen` int(11) NOT NULL default '0',
  `pts` decimal(10,0) NOT NULL default '0',
  `s` int(11) NOT NULL default '0',
  `d` int(11) NOT NULL default '0',
  `me` int(11) NOT NULL default '0',
  `ke` int(11) NOT NULL default '0',
  `a` int(11) NOT NULL default '0',
  `sf0j` bigint(20) NOT NULL default '0',
  `sf0b` bigint(20) NOT NULL default '0',
  `sf0f` bigint(20) NOT NULL default '0',
  `sf0z` bigint(20) NOT NULL default '0',
  `sf0kr` bigint(20) NOT NULL default '0',
  `sf0sa` bigint(20) NOT NULL default '0',
  `sf0t` bigint(20) NOT NULL default '0',
  `sf0ko` bigint(20) NOT NULL default '0',
  `sf0ka` bigint(20) NOT NULL default '0',
  `sf0su` bigint(20) NOT NULL default '0',
  `sf1j` bigint(20) NOT NULL default '0',
  `sf1b` bigint(20) NOT NULL default '0',
  `sf1f` bigint(20) NOT NULL default '0',
  `sf1z` bigint(20) NOT NULL default '0',
  `sf1kr` bigint(20) NOT NULL default '0',
  `sf1sa` bigint(20) NOT NULL default '0',
  `sf1t` bigint(20) NOT NULL default '0',
  `sf1ko` bigint(20) NOT NULL default '0',
  `sf1ka` bigint(20) NOT NULL default '0',
  `sf1su` bigint(20) NOT NULL default '0',
  `status1` int(11) NOT NULL default '0',
  `ziel1` varchar(20) NOT NULL default '',
  `sf2j` bigint(20) NOT NULL default '0',
  `sf2b` bigint(20) NOT NULL default '0',
  `sf2f` bigint(20) NOT NULL default '0',
  `sf2z` bigint(20) NOT NULL default '0',
  `sf2kr` bigint(20) NOT NULL default '0',
  `sf2sa` bigint(20) NOT NULL default '0',
  `sf2t` bigint(20) NOT NULL default '0',
  `sf2ko` bigint(20) NOT NULL default '0',
  `sf2ka` bigint(20) NOT NULL default '0',
  `sf2su` bigint(20) NOT NULL default '0',
  `status2` int(11) NOT NULL default '0',
  `ziel2` varchar(20) NOT NULL default '',
  `sfj` bigint(20) NOT NULL default '0',
  `sfb` bigint(20) NOT NULL default '0',
  `sff` bigint(20) NOT NULL default '0',
  `sfz` bigint(20) NOT NULL default '0',
  `sfkr` bigint(20) NOT NULL default '0',
  `sfsa` bigint(20) NOT NULL default '0',
  `sft` bigint(20) NOT NULL default '0',
  `sfko` bigint(20) NOT NULL default '0',
  `sfka` bigint(20) NOT NULL default '0',
  `sfsu` bigint(20) NOT NULL default '0',
  `glo` bigint(20) NOT NULL default '0',
  `glr` bigint(20) NOT NULL default '0',
  `gmr` bigint(20) NOT NULL default '0',
  `gsr` bigint(20) NOT NULL default '0',
  `ga` bigint(20) NOT NULL default '0',
  `gr` bigint(20) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  INDEX	scan_koords (`rg`, `rp`)
) ;";

$sqlquery['gn4gnuser'] = "DROP TABLE IF EXISTS `gn4gnuser`;
CREATE TABLE `gn4gnuser` (
  `id` int(12) NOT NULL auto_increment,
  `ticid` varchar(5) NOT NULL default '',
  `gala` int(12) NOT NULL default '0',
  `planet` int(12) NOT NULL default '0',
  `name` varchar(50) NOT NULL default '',
  `kommentare` varchar(50) NOT NULL default '',
  `erfasst` varchar(32) NOT NULL default '',
  PRIMARY KEY  (`id`),
  INDEX	user_koords (`gala`, `planet`)
);";

$sqlquery['gn4vars'] = "
DROP TABLE IF EXISTS `gn4vars`;
CREATE TABLE `gn4vars` (
  `id` int(11) NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `value` text NOT NULL,
  `ticid` varchar(5) NOT NULL default '',
  PRIMARY KEY  (`id`)
) ;";

$sqlquery['gn4meta'] = "
DROP TABLE IF EXISTS `gn4meta`;
CREATE TABLE `gn4meta` (
  `id` tinyint(3) unsigned NOT NULL auto_increment,
  `name` varchar(20) NOT NULL default '',
  `sysmsg` text NOT NULL,
  `bnds` varchar(255) NOT NULL default '',
  `naps` varchar(255) NOT NULL default '',
  `wars` varchar(255) NOT NULL default '',
  `duell` varchar(20) default NULL,
  PRIMARY KEY  (`id`)
) ;";

$sqlquery['gn4attplanung'] = "DROP TABLE IF EXISTS `gn4attplanung`;
CREATE TABLE `gn4attplanung` (
  `lfd` int(11) NOT NULL auto_increment,
  `id` int(11) default NULL,
  `galaxie` int(11) default NULL,
  `planet` int(11) default NULL,
  `attdatum` date default NULL,
  `attzeit` time default NULL,
  `attstatus` int(11) default '0',
  `freigabe` tinyint(4) default '0',
  `info` varchar(255) default NULL,
  `forall` tinyint(4) default '0',
  `formeta` int(11) default '0',
  `forallianz` int(11) default '0',
  PRIMARY KEY  (`lfd`),
  UNIQUE KEY `lfd` (`lfd`)
) ;";

$sqlquery['gn4attflotten'] = "DROP TABLE IF EXISTS `gn4attflotten`;
CREATE TABLE `gn4attflotten` (
  `lfd` int(11) default NULL,
  `id` int(11) default NULL,
  `flottenr` tinyint(4) default '1'
) ;";

$sqlquery['gn4channels'] = "DROP TABLE IF EXISTS gn4channels;
CREATE TABLE `gn4channels` (
  `id` mediumint(9) NOT NULL auto_increment,
  `channame` varchar(63) NOT NULL default '',
  `joincommand` varchar(127) NOT NULL default '',
  `pass` varchar(63) NOT NULL default '',
  `ally` mediumint(9) NOT NULL default '0',
  `metachan` tinyint(4) NOT NULL default '0',
  `guard` tinyint(4) NOT NULL default '0',
  `answer` tinyint(4) NOT NULL default '0',
  `voicerang` tinyint(4) NOT NULL default '-1',
  `oprang` tinyint(4) NOT NULL default '2',
  `accessrang` tinyint(4) NOT NULL default '0',
  `inviterang` tinyint(4) NOT NULL default '0',
  `opcontrol` tinyint(4) NOT NULL default '1',
  PRIMARY KEY  (`id`)
) ;"

$sqlquery['gn4galfleetupdated'] = "DROP TABLE IF EXISTS `gn4galfleetupdated`;
CREATE TABLE `gn4galfleetupdated` (
  `gal` int(9) NOT NULL,
  `t` int(14) NOT NULL,
  `erfasser` varchar(255) COLLATE utf8_bin NOT NULL,
  PRIMARY KEY (`gal`)
);";

$sqlquery['gn4scanblock'] = "DROP TABLE IF EXISTS `gn4scanblock`;
CREATE TABLE `gn4scanblock` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `g` int(5) DEFAULT NULL,
  `p` int(5) DEFAULT NULL,
  `t` int(11) DEFAULT NULL,
  `svs` int(6) DEFAULT NULL,
  `sg` int(5) DEFAULT NULL,
  `sp` int(5) DEFAULT NULL,
  `sname` varchar(255) DEFAULT NULL,
  `typ` int(2) DEFAULT NULL,
  PRIMARY KEY (`id`)
);";

$sqlquery['gn4scans_history'] = "DROP TABLE IF EXISTS `gn4scans_history`;
CREATE TABLE `gn4scans_history` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `ticid` varchar(5) NOT NULL DEFAULT '',
  `zeit` varchar(20) NOT NULL DEFAULT '',
  `type` int(11) NOT NULL DEFAULT '0',
  `g` int(11) NOT NULL DEFAULT '0',
  `p` int(11) NOT NULL DEFAULT '0',
  `rg` int(11) NOT NULL DEFAULT '0',
  `rp` int(11) NOT NULL DEFAULT '0',
  `gen` int(11) NOT NULL DEFAULT '0',
  `pts` decimal(10,0) NOT NULL DEFAULT '0',
  `s` int(11) NOT NULL DEFAULT '0',
  `d` int(11) NOT NULL DEFAULT '0',
  `me` int(11) NOT NULL DEFAULT '0',
  `ke` int(11) NOT NULL DEFAULT '0',
  `a` int(11) NOT NULL DEFAULT '0',
  `sf0j` bigint(20) NOT NULL DEFAULT '0',
  `sf0b` bigint(20) NOT NULL DEFAULT '0',
  `sf0f` bigint(20) NOT NULL DEFAULT '0',
  `sf0z` bigint(20) NOT NULL DEFAULT '0',
  `sf0kr` bigint(20) NOT NULL DEFAULT '0',
  `sf0sa` bigint(20) NOT NULL DEFAULT '0',
  `sf0t` bigint(20) NOT NULL DEFAULT '0',
  `sf0ko` bigint(20) NOT NULL DEFAULT '0',
  `sf0ka` bigint(20) NOT NULL DEFAULT '0',
  `sf0su` bigint(20) NOT NULL DEFAULT '0',
  `sf1j` bigint(20) NOT NULL DEFAULT '0',
  `sf1b` bigint(20) NOT NULL DEFAULT '0',
  `sf1f` bigint(20) NOT NULL DEFAULT '0',
  `sf1z` bigint(20) NOT NULL DEFAULT '0',
  `sf1kr` bigint(20) NOT NULL DEFAULT '0',
  `sf1sa` bigint(20) NOT NULL DEFAULT '0',
  `sf1t` bigint(20) NOT NULL DEFAULT '0',
  `sf1ko` bigint(20) NOT NULL DEFAULT '0',
  `sf1ka` bigint(20) NOT NULL DEFAULT '0',
  `sf1su` bigint(20) NOT NULL DEFAULT '0',
  `status1` int(11) NOT NULL DEFAULT '0',
  `ziel1` varchar(20) NOT NULL DEFAULT '',
  `sf2j` bigint(20) NOT NULL DEFAULT '0',
  `sf2b` bigint(20) NOT NULL DEFAULT '0',
  `sf2f` bigint(20) NOT NULL DEFAULT '0',
  `sf2z` bigint(20) NOT NULL DEFAULT '0',
  `sf2kr` bigint(20) NOT NULL DEFAULT '0',
  `sf2sa` bigint(20) NOT NULL DEFAULT '0',
  `sf2t` bigint(20) NOT NULL DEFAULT '0',
  `sf2ko` bigint(20) NOT NULL DEFAULT '0',
  `sf2ka` bigint(20) NOT NULL DEFAULT '0',
  `sf2su` bigint(20) NOT NULL DEFAULT '0',
  `status2` int(11) NOT NULL DEFAULT '0',
  `ziel2` varchar(20) NOT NULL DEFAULT '',
  `sfj` bigint(20) NOT NULL DEFAULT '0',
  `sfb` bigint(20) NOT NULL DEFAULT '0',
  `sff` bigint(20) NOT NULL DEFAULT '0',
  `sfz` bigint(20) NOT NULL DEFAULT '0',
  `sfkr` bigint(20) NOT NULL DEFAULT '0',
  `sfsa` bigint(20) NOT NULL DEFAULT '0',
  `sft` bigint(20) NOT NULL DEFAULT '0',
  `sfko` bigint(20) NOT NULL DEFAULT '0',
  `sfka` bigint(20) NOT NULL DEFAULT '0',
  `sfsu` bigint(20) NOT NULL DEFAULT '0',
  `glo` bigint(20) NOT NULL DEFAULT '0',
  `glr` bigint(20) NOT NULL DEFAULT '0',
  `gmr` bigint(20) NOT NULL DEFAULT '0',
  `gsr` bigint(20) NOT NULL DEFAULT '0',
  `ga` bigint(20) NOT NULL DEFAULT '0',
  `gr` bigint(20) NOT NULL DEFAULT '0',
  `erfasser_svs` int(11) DEFAULT NULL,
  `erfasser` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scan_koords` (`rg`,`rp`)
)";


$sqlquery['trigger_scan_hist_i'] = "DROP TRIGGER IF EXISTS `history_i`;
CREATE TRIGGER `history_i` AFTER INSERT ON `gn4scans`
 FOR EACH ROW insert into gn4scans_history (
	ticid,
	zeit,
	type,
	g,
	p,
	rg,
	rp,
	gen,
	pts,
	s,
	d,
	me,
	ke,
	a,
	sf0j,
	sf0b,
	sf0f,
	sf0z,
	sf0kr,
	sf0sa,
	sf0t,
	sf0ko,
	sf0ka,
	sf0su,
	sf1j,
	sf1b,
	sf1f,
	sf1z,
	sf1kr,
	sf1sa,
	sf1t,
	sf1ko,
	sf1ka,
	sf1su,
	status1,
	ziel1,
	sf2j,
	sf2b,
	sf2f,
	sf2z,
	sf2kr,
	sf2sa,
	sf2t,
	sf2ko,
	sf2ka,
	sf2su,
	status2,
	ziel2,
	sfj,
	sfb,
	sff,
	sfz,
	sfkr,
	sfsa,
	sft,
	sfko,
	sfka,
	sfsu,
	glo,
	glr,
	gmr,
	gsr,
	ga,
	gr,
	erfasser_svs,
	erfasser)
VALUES(
	NEW.ticid,
	NEW.zeit,
	NEW.type,
	NEW.g,
	NEW.p,
	NEW.rg,
	NEW.rp,
	NEW.gen,
	NEW.pts,
	NEW.s,
	NEW.d,
	NEW.me,
	NEW.ke,
	NEW.a,
	NEW.sf0j,
	NEW.sf0b,
	NEW.sf0f,
	NEW.sf0z,
	NEW.sf0kr,
	NEW.sf0sa,
	NEW.sf0t,
	NEW.sf0ko,
	NEW.sf0ka,
	NEW.sf0su,
	NEW.sf1j,
	NEW.sf1b,
	NEW.sf1f,
	NEW.sf1z,
	NEW.sf1kr,
	NEW.sf1sa,
	NEW.sf1t,
	NEW.sf1ko,
	NEW.sf1ka,
	NEW.sf1su,
	NEW.status1,
	NEW.ziel1,
	NEW.sf2j,
	NEW.sf2b,
	NEW.sf2f,
	NEW.sf2z,
	NEW.sf2kr,
	NEW.sf2sa,
	NEW.sf2t,
	NEW.sf2ko,
	NEW.sf2ka,
	NEW.sf2su,
	NEW.status2,
	NEW.ziel2,
	NEW.sfj,
	NEW.sfb,
	NEW.sff,
	NEW.sfz,
	NEW.sfkr,
	NEW.sfsa,
	NEW.sft,
	NEW.sfko,
	NEW.sfka,
	NEW.sfsu,
	NEW.glo,
	NEW.glr,
	NEW.gmr,
	NEW.gsr,
	NEW.ga,
	NEW.gr,
	NEW.erfasser_svs,
	NEW.erfasser
	)";
$sqlquery['trigger_scan_hist_u'] = "DROP TRIGGER IF EXISTS `history_u`;
CREATE TRIGGER `history_u` AFTER UPDATE ON `gn4scans`
 FOR EACH ROW insert into gn4scans_history (
	ticid,
	zeit,
	type,
	g,
	p,
	rg,
	rp,
	gen,
	pts,
	s,
	d,
	me,
	ke,
	a,
	sf0j,
	sf0b,
	sf0f,
	sf0z,
	sf0kr,
	sf0sa,
	sf0t,
	sf0ko,
	sf0ka,
	sf0su,
	sf1j,
	sf1b,
	sf1f,
	sf1z,
	sf1kr,
	sf1sa,
	sf1t,
	sf1ko,
	sf1ka,
	sf1su,
	status1,
	ziel1,
	sf2j,
	sf2b,
	sf2f,
	sf2z,
	sf2kr,
	sf2sa,
	sf2t,
	sf2ko,
	sf2ka,
	sf2su,
	status2,
	ziel2,
	sfj,
	sfb,
	sff,
	sfz,
	sfkr,
	sfsa,
	sft,
	sfko,
	sfka,
	sfsu,
	glo,
	glr,
	gmr,
	gsr,
	ga,
	gr,
	erfasser_svs,
	erfasser)
VALUES(
	NEW.ticid,
	NEW.zeit,
	NEW.type,
	NEW.g,
	NEW.p,
	NEW.rg,
	NEW.rp,
	NEW.gen,
	NEW.pts,
	NEW.s,
	NEW.d,
	NEW.me,
	NEW.ke,
	NEW.a,
	NEW.sf0j,
	NEW.sf0b,
	NEW.sf0f,
	NEW.sf0z,
	NEW.sf0kr,
	NEW.sf0sa,
	NEW.sf0t,
	NEW.sf0ko,
	NEW.sf0ka,
	NEW.sf0su,
	NEW.sf1j,
	NEW.sf1b,
	NEW.sf1f,
	NEW.sf1z,
	NEW.sf1kr,
	NEW.sf1sa,
	NEW.sf1t,
	NEW.sf1ko,
	NEW.sf1ka,
	NEW.sf1su,
	NEW.status1,
	NEW.ziel1,
	NEW.sf2j,
	NEW.sf2b,
	NEW.sf2f,
	NEW.sf2z,
	NEW.sf2kr,
	NEW.sf2sa,
	NEW.sf2t,
	NEW.sf2ko,
	NEW.sf2ka,
	NEW.sf2su,
	NEW.status2,
	NEW.ziel2,
	NEW.sfj,
	NEW.sfb,
	NEW.sff,
	NEW.sfz,
	NEW.sfkr,
	NEW.sfsa,
	NEW.sft,
	NEW.sfko,
	NEW.sfka,
	NEW.sfsu,
	NEW.glo,
	NEW.glr,
	NEW.gmr,
	NEW.gsr,
	NEW.ga,
	NEW.gr,
	NEW.erfasser_svs,
	NEW.erfasser
	)";

$sqlquery['trigger_scan_svs_i'] = "DROP TRIGGER IF EXISTS `scans_svs_i`;
CREATE TRIGGER `scans_svs_i` BEFORE INSERT ON `gn4scans`
 FOR EACH ROW SET NEW.erfasser_svs = IF(NEW.erfasser_svs IS NULL, (SELECT svs FROM gn4accounts WHERE galaxie = NEW.g AND planet = NEW.p), NEW.erfasser_svs)";

$sqlquery['trigger_scan_svs_u'] = "DROP TRIGGER IF EXISTS `scans_svs_u`;
CREATE TRIGGER `scans_svs_u` BEFORE UPDATE ON `gn4scans`
 FOR EACH ROW SET NEW.erfasser_svs = IF(NEW.erfasser_svs IS NULL, (SELECT svs FROM gn4accounts WHERE galaxie = NEW.g AND planet = NEW.p), NEW.erfasser_svs)"
?>
