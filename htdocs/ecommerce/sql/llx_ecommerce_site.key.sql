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

ALTER TABLE `llx_ecommerce_site` ADD `timeout` int(11) unsigned NOT NULL DEFAULT 300;
ALTER TABLE `llx_ecommerce_site` ADD `magento_use_special_price` INT( 1 ) NOT NULL DEFAULT '0' AFTER `timeout` ;
ALTER TABLE `llx_ecommerce_site` ADD `magento_price_type` VARCHAR(3) NOT NULL DEFAULT 'HT' AFTER `magento_use_special_price` ;
