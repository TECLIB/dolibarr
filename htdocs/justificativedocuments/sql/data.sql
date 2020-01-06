-- Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2003      Jean-Louis Bergamo   <jlb@j1b.org>
-- Copyright (C) 2004-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
-- Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
-- Copyright (C) 2004      Guillaume Delecourt  <guillaume.delecourt@opensides.be>
-- Copyright (C) 2005-2009 Regis Houssin        <regis.houssin@inodbox.com>
-- Copyright (C) 2007 	   Patrick Raguin       <patrick.raguin@gmail.com>
-- Copyright (C) 2014 	   Alexandre Spangaro   <aspangaro@open-dsi.fr>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 3 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program. If not, see <https://www.gnu.org/licenses/>.
--
-- Do not add comment at end of line. This file is parsed by install and -- are removed


-- delete from llx_c_justificative_type;
INSERT INTO llx_c_justificative_type (code,label,active) VALUES ('TRAINSUBSCRIPTION',   'Train subscrption-',   1);
INSERT INTO llx_c_justificative_type (code,label,active) VALUES ('HIGHWAYSUBSCRIPTION', 'Highway subscription', 1);


-- new types of automatic events to record in agenda
-- 'code' must be a value matching 'MYOBJECT_ACTION'
-- 'elementtype' must be value 'mymodule' ('myobject@mymodule' may be possible but should not be required)
insert into llx_c_action_trigger (code,label,description,elementtype,rang) values ('JUSTIFICATIVEDOCUMENT_VALIDATE','Supporting document validated','Executed when a Supporting document is validated', 'justificativedocuments', 1000);
insert into llx_c_action_trigger (code,label,description,elementtype,rang) values ('JUSTIFICATIVEDOCUMENT_UNVALIDATE','Supporting document unvalidated','Executed when a Supporting document is unvalidated', 'justificativedocuments', 1001);
insert into llx_c_action_trigger (code,label,description,elementtype,rang) values ('JUSTIFICATIVEDOCUMENT_APPROVE','Supporting document approved','Executed when a Supporting document is approved', 'justificativedocuments', 1002);
insert into llx_c_action_trigger (code,label,description,elementtype,rang) values ('JUSTIFICATIVEDOCUMENT_DELETE','Supporting document deleted','Executed when a Supporting document deleted', 'justificativedocuments', 1004);

