-- ========================================================================
-- Copyright (C) 2011 -- Auguria	<contact@auguria.net>
--
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
-- along with this program. If not, see <http://www.gnu.org/licenses/>.
--
-- ========================================================================

create table llx_autoaddline
(  
  rowid					   integer AUTO_INCREMENT PRIMARY KEY,
  label                    varchar(255) NOT NULL,
  fk_product_base          integer,
  final_service_type       smallint DEFAULT 0,
  final_service_value      double DEFAULT 0  
)ENGINE=innodb;
