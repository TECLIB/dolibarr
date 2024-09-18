-- Copyright (C) ---Put here your own copyright and developer email---
--
-- This program is free software: you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation, either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program.  If not, see https://www.gnu.org/licenses/.


CREATE TABLE llx_justificativedocuments_justificativedocument(
	-- BEGIN MODULEBUILDER FIELDS
	rowid integer AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	entity integer DEFAULT 1 NOT NULL, 
	ref varchar(128) DEFAULT '(PROV)' NOT NULL, 
	fk_type integer NOT NULL, 
	date_start date, 
	date_end date, 
	amount double, 
	percent_reimbursed double(24,8), 
	fk_user integer NOT NULL, 
	note_public text, 
	note_private text, 
	date_creation datetime NOT NULL, 
	tms timestamp, 
	date_validation datetime, 
	fk_user_creat integer NOT NULL, 
	fk_user_modif integer, 
	fk_user_valid integer, 
	description text, 
	import_key varchar(14), 
	status integer NOT NULL
	-- END MODULEBUILDER FIELDS
) ENGINE=innodb;
