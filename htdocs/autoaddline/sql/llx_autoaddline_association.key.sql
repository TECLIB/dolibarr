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

ALTER TABLE llx_autoaddline_association ADD UNIQUE uk_autoaddline_association_base_target ( fk_product_base , fk_product_target );
ALTER TABLE llx_autoaddline_association ADD CONSTRAINT fk_base_fk_product_base FOREIGN KEY (fk_product_base) REFERENCES llx_autoaddline (fk_product_base) ON DELETE CASCADE;
ALTER TABLE llx_autoaddline_association ADD CONSTRAINT fk_product_fk_product_target FOREIGN KEY (fk_product_target) REFERENCES llx_product (rowid) ON DELETE CASCADE;

