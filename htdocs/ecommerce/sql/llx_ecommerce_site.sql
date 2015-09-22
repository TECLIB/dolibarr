-- ===================================================================
-- Copyright (C) 2010 Auguria <franck.charpentier@auguria.net>
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
--
-- ===================================================================

CREATE TABLE `llx_ecommerce_site` (
  `rowid` int(11) NOT NULL auto_increment PRIMARY KEY,
  `name` varchar(255) NOT NULL,
  `type` int(11) NOT NULL DEFAULT 1,
  `webservice_address` varchar(255) NOT NULL,
  `user_name` varchar(255) DEFAULT NULL,
  `user_password` varchar(255) DEFAULT NULL,
  `filter_label` varchar(255) DEFAULT NULL,
  `filter_value` varchar(255) DEFAULT NULL,
  `fk_cat_societe` int(11) NOT NULL,
  `fk_cat_product` int(11) NOT NULL,
  `last_update` datetime DEFAULT NULL
) ENGINE=InnoDB COMMENT='Liste des sites à synchroniser';
