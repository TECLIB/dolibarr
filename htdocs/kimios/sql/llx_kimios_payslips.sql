CREATE TABLE llx_kimios_payslips (
   rowid    INT( 11 ) NOT NULL AUTO_INCREMENT PRIMARY KEY,
   payslips_code VARCHAR ( 50 ) NOT NULL DEFAULT 0,
   doliuserid INT ( 11 ) NOT NULL DEFAULT 0,
   doliuseremail VARCHAR ( 255 ) NULL DEFAULT '',
   last_send DATETIME NULL DEFAULT NULL,
   last_sender INT ( 11 ) NULL DEFAULT 0,
   kimios_docuid VARCHAR ( 50 ) NULL DEFAULT '',
   kimios_docpath VARCHAR ( 255 ) NULL DEFAULT '',
   kimios_docmime VARCHAR ( 255 ) NULL DEFAULT '',
   kimios_docname VARCHAR ( 255 ) NULL DEFAULT ''
);