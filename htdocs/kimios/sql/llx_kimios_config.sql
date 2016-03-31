CREATE TABLE llx_kimios_config (
	rowid    INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	url     VARCHAR( 255 ) NOT NULL DEFAULT "",
	userName     VARCHAR( 255 ) NOT NULL DEFAULT "",
	password     VARCHAR( 255 ) NOT NULL DEFAULT "",
	userSource     VARCHAR( 255 ) NOT NULL DEFAULT "",
	initialPath     VARCHAR( 255 ) NOT NULL DEFAULT ""
);