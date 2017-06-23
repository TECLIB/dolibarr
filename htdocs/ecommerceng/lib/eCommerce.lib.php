<?php
/* Copyright (C) 2017 Open-DSI                     <support@open-dsi.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */


dol_include_once('/ecommerceng/class/data/eCommerceProduct.class.php');
dol_include_once('/ecommerceng/class/business/eCommerceSynchro.class.php');

/**
 * Update the price for all product in the ecommerce product category for this site price level
 * @param eCommerceSite  $siteDb    Object eCommerceSite
 *
 * @return int                      <0 if KO, >0 if OK
 */
function updatePriceLevel($siteDb)
{
    global $db, $conf;

    if (!empty($conf->global->PRODUIT_MULTIPRICES) && $siteDb->price_level > 0 && $siteDb->price_level <= intval($conf->global->PRODUIT_MULTIPRICES_LIMIT)) {
        $sql = 'SELECT p.rowid';
        $sql.= ' FROM ' . MAIN_DB_PREFIX . 'product as p';
        $sql.= ' LEFT JOIN ' . MAIN_DB_PREFIX . "categorie_product as cp ON p.rowid = cp.fk_product";
        $sql.= ' WHERE p.entity IN (' . getEntity('product', 1) . ')';
        $sql.= ' AND cp.fk_categorie = ' . $siteDb->fk_cat_product;
        $sql.= ' GROUP BY p.rowid';

        $db->begin();

        dol_syslog("updatePriceLevel sql=" . $sql);
        $resql = $db->query($sql);
        if ($resql) {
            $product = new Product($db);
            $eCommerceProduct = new eCommerceProduct($db);

            while ($obj = $db->fetch_object($resql)) {
                $product->fetch($obj->rowid);
                $eCommerceProduct->fetchByProductId($obj->rowid, $siteDb->id);

                if ($eCommerceProduct->remote_id > 0) {
                    $eCommerceSynchro = new eCommerceSynchro($db, $siteDb);
                    $eCommerceSynchro->connect();
                    if (count($eCommerceSynchro->errors)) {
                        dol_syslog("updatePriceLevel eCommerceSynchro->connect() ".$eCommerceSynchro->error, LOG_ERR);
                        setEventMessages($eCommerceSynchro->error, $eCommerceSynchro->errors, 'errors');

                        $db->rollback();
                        return -1;
                    }

                    $product->price = $product->multiprices[$siteDb->price_level];

                    $result = $eCommerceSynchro->eCommerceRemoteAccess->updateRemoteProduct($eCommerceProduct->remote_id, $product);
                    if (!$result) {
                        dol_syslog("updatePriceLevel eCommerceSynchro->eCommerceRemoteAccess->updateRemoteProduct() ".$eCommerceSynchro->eCommerceRemoteAccess->error, LOG_ERR);
                        setEventMessages($eCommerceSynchro->eCommerceRemoteAccess->error, $eCommerceSynchro->eCommerceRemoteAccess->errors, 'errors');

                        $db->rollback();
                        return -2;
                    }
                } else {
                    dol_syslog("updatePriceLevel Product with id " . $product->id . " is not linked to an ecommerce record but has category flag to push on eCommerce. So we push it");
                    // TODO
                    //$result = $eCommerceSynchro->eCommerceRemoteAccess->updateRemoteProduct($eCommerceProduct->remote_id);
                }
            }
        }

        $db->commit();
    }

    return 1;
}

