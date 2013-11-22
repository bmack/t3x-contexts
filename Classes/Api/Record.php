<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2013 Netresearch GmbH & Co. KG <typo3-2013@netresearch.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

/**
 * API with methods to retrieve context information for records
 *
 * @package    Contexts
 * @subpackage Api
 * @author     Christian Opitz <christian.opitz@netresearch.de>
 * @license    http://opensource.org/licenses/gpl-license GPLv2 or later
 */
class Tx_Contexts_Api_Record
{
    /**
     * Determines if the specified record is enabled or disabled by the current
     * contexts (means that the records is disabled if one of the enableSettings
     * are disabled for one of the current contexts)
     *
     * @param string        $table Table name
     * @param array|integer $row   Record array or an uid
     *
     * @return boolean
     */
    public static function isEnabled($table, $row)
    {
        global $TCA;
        $enableSettings = Tx_Contexts_Api_Configuration::getEnableSettings($table);
        if (!$enableSettings) {
            return true;
        }
        foreach ($enableSettings as $setting) {
            if (!self::isSettingEnabled($table, $setting, $row)) {
                return false;
            }
        }
        return true;
    }

    /**
     * Determines if a setting is enabled or disabled by the current contexts
     * (returns false if the setting is disabled for one of the contexts)
     *
     * @param string        $tableName   Table name
     * @param string        $settingName Setting name
     * @param array|integer $row     Record array or an uid
     *
     * @return boolean
     */
    public static function isSettingEnabled($tableName, $settingName, $row)
    {
        if (is_array($row)) {
            $flatColumnContents = self::getFlatColumnContents($tableName, $settingName, $row);

            if ($flatColumnContents !== null) {
                return self::evaluateFlatColumnContents($flatColumnContents);
            }

            if (!isset($row['uid'])) {
                t3lib_div::devLog(
                    'Missing uid field in row',
                    'tx_contexts',
                    t3lib_div::SYSLOG_SEVERITY_WARNING,
                    array('table' => $tableName, 'row' => $row)
                );
                return false;
            }

            $uid = (int) $row['uid'];
        } else {
            $uid = (int) $row;
        }

        $flatColumnContents = array(0 => array(), 1 => array());
        
        /* @var $context Tx_Contexts_Context_Abstract */
        foreach (Tx_Contexts_Context_Container::get() as $context) {
            $setting = $context->getSetting($tableName, $settingName, $uid);
            if ($setting) {
                $flatColumnContents[$setting->getEnabled() ? 1 : 0][] = (string) $context->getUid();
            }
        }

        return self::evaluateFlatColumnContents($flatColumnContents);
    }

    /**
     * Get the flat column contents from the record if possible
     *
     * @param string $table   Table name
     * @param string $setting Setting name
     * @param array  $row     Record array
     *
     * @return null|boolean NULL when table has no flat settings or the record
     *                      doesn't contain the appropriate flat columns
     *                      boolean otherwise
     */
    protected static function getFlatColumnContents($table, $setting, array $row)
    {
        $flatColumns = Tx_Contexts_Api_Configuration::getFlatColumns($table, $setting);

        if (!$flatColumns) {
            return null;
        }

        $rowValid           = true;
        $flatColumnContents = array();

        foreach ($flatColumns as $i => $flatColumn) {
            if (!array_key_exists($flatColumn, $row)) {
                t3lib_div::devLog(
                    'Missing flat field "' . $flatColumn . '"',
                    'tx_contexts',
                    t3lib_div::SYSLOG_SEVERITY_WARNING,
                    array('table' => $table, 'row' => $row)
                );
                $rowValid = false;
            } elseif ($row[$flatColumn] !== '') {
                $flatColumnContents[$i] = explode(',', $row[$flatColumn]);
            } else {
                $flatColumnContents[$i] = array();
            }
        }

        if (!$rowValid) {
            return null;
        }
        
        return $flatColumnContents;
    }
    
    /**
     * Evaluate the flat columns contents with the current active contexts
     * Same logic as the SQL conditions in {@see Tx_Contexts_Service_Page::getFilterSql()}
     * 
     * @param array $flatColumnContents
     * @return boolean
     */
    protected static function evaluateFlatColumnContents($flatColumnContents) {
        $enableChecks = array();
        $disableChecks = array();
        $voidableDisableChecks = array();

        foreach (Tx_Contexts_Context_Container::get() as $context) {
            /* @var $context Tx_Contexts_Context_Abstract */
            $id = (string) $context->getUid();
            if ($context->getNoIsVoidable()) {
                $voidableDisableChecks[] = $id;
            } else {
                $enableChecks[] = $id;
                $disableChecks[] = $id;
            }
        }

        $enableCheckRes = true;
        if (count($voidableDisableChecks)) {
            if (count($enableChecks)) {
                $enableCheckRes = 
                    !array_intersect($voidableDisableChecks, $flatColumnContents[0]) ||
                    array_intersect($enableChecks, $flatColumnContents[1]);
            } else {
                $disableChecks = array_merge($disableChecks, $voidableDisableChecks);
            }
        }
        
        $disableCheckRes = !array_intersect($disableChecks, $flatColumnContents[0]);

        return $enableCheckRes && $disableCheckRes;
    }
}
?>
