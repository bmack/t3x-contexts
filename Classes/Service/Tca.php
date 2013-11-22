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
 * USER functions to render the defaults and record settings fields
 *
 * @author Christian Opitz <christian.opitz@netresearch.de>
 */
class Tx_Contexts_Service_Tca
{
    /**
     * Render the context settings field for a certain table
     *
     * @param array $params
     * @param t3lib_TCEforms $fobj
     * @return string
     */
    public function renderRecordSettingsField($params, $fobj)
    {
        global $TCA;
        $table = $params['table'];

        $fobj->addStyleSheet(
            'tx_contexts_bestyles',
            t3lib_extMgm::extRelPath('contexts') . 'Resources/Public/StyleSheet/be.css'
        );

        $contexts = new Tx_Contexts_Context_Container();
        $contexts->initAll();

        $namePre = str_replace('[' . $params['field'] . '_', '[' . $params['field'] . '][', $params['itemFormElName']);

        $settings = $params['fieldConf']['config']['settings'];

        $content = '<br/><table class="tx_contexts_table_settings">' .
            '<tr><th class="tx_contexts_context">' .
            $fobj->sL('LLL:' . Tx_Contexts_Api_Configuration::LANG_FILE . ':tx_contexts_contexts') .
            '</th>';
        foreach ($settings as $settingName => $config) {
            $content .= '<th class="tx_contexts_setting">' . $fobj->sL($config['label']) . '</th>';
        }
        $content .= '</tr>';

        $uid = (int) $params['row']['uid'];

        foreach ($contexts as $context) {
            if ($context->getDisabled()) {
                continue;
            }

            /* @var $context Tx_Contexts_Context_Abstract */
            $contSettings = '';
            $bHasSetting = false;
            foreach ($settings as $settingName => $config) {
                $setting = $uid ? $context->getSetting($table, $settingName, $uid) : null;
                $bHasSetting = $bHasSetting || (bool) $setting;
                $contSettings .= '<td class="tx_contexts_setting">'
                    . '<select name="' . $namePre . '[' . $context->getUid() . '][' . $settingName . ']">'
                    . '<option value="">n/a</option>'
                    . '<option value="1"' . ($setting && $setting->getEnabled() ? ' selected="selected"' : '') . '>Yes</option>'
                    . '<option value="0"' . ($setting && !$setting->getEnabled() ? ' selected="selected"' : '') . '>No</option>'
                    . '</select></td>';
            }

            $content .= '<tr>'
                . '<td class="tx_contexts_context">'
                . '<span class="context-' . ($bHasSetting ? 'active' : 'inactive') . ' nobr">'
                . ($context->getUid() ? $this->getRecordPreview($context) : $this->getDefaultPreview($context))
                . '</span>'
                . '</td>'
                . $contSettings
                . '</tr>';
        }
        $content .= '</table>';

        return $content;
    }

    /**
     * Get the standard view for contexts without records
     *
     * @param Tx_Contexts_Context_Abstract $context
     * @return string
     */
    protected function getDefaultPreview($context)
    {
        $row = array(
            'uid'   => $context->getUid(),
            'pid'   => 0,
        );

        return t3lib_iconWorks::getSpriteIconForRecord(
                'tx_contexts_contexts',
                $row,
                array(
                    'style' => 'vertical-align:top',
                    'class' => 'context-default-icon'
                )
            ) .
            '&nbsp;' .
            htmlspecialchars($context->getTitle());
    }

    /**
     * Get the standard record view for context records
     *
     * @param Tx_Contexts_Context_Abstract $context
     * @return string
     */
    protected function getRecordPreview($context)
    {
        $row = array(
            'uid'   => $context->getUid(),
            'pid'   => 0,
            'type'  => $context->getType(),
            'alias' => $context->getAlias()
        );

        return $this->getClickMenu(
                t3lib_iconWorks::getSpriteIconForRecord(
                    'tx_contexts_contexts',
                    $row,
                    array(
                        'style' => 'vertical-align:top',
                        'title' => htmlspecialchars(
                            $context->getTitle() .
                            ' [UID: ' . $row['uid'] . ']')
                    )
                ),
                'tx_contexts_contexts',
                $row['uid']
            ) .
            '&nbsp;' .
            htmlspecialchars($context->getTitle()) .
            ' <span class="typo3-dimmed"><em>[' . $row['uid'] . ']</em></span>';
    }

    /**
     * Wraps the icon of a relation item (database record or file) in a link
     * opening the context menu for the item.
     *
     * Copied from class.t3lib_befunc.php
     *
     * @param string  $str   The icon HTML to wrap
     * @param string  $table Table name (eg. "pages" or "tt_content") OR the
     *                       absolute path to the file
     * @param integer $uid   The uid of the record OR if file, just blank value.
     * @return string HTML
     */
    protected function getClickMenu($str, $table, $uid = '')
    {
        $onClick = htmlspecialchars($GLOBALS['SOBE']->doc->wrapClickMenuOnIcon(
            $str, $table, $uid, 1, '', '+info,edit,view,new', TRUE
        ));
        return
        '<a href="#" onclick="' . $onClick . '" onrightclick="' . $onClick . '">' . $str . '</a>';
    }


    /**
     * Render a checkbox for the default settings of records in
     * this table
     *
     * @param array $params
     * @param t3lib_TCEforms $fobj
     * @return string
     */
    public function renderDefaultSettingsField($params, $fobj)
    {
        global $TCA;
        $table = $params['fieldConf']['config']['table'];
        t3lib_div::loadTCA($table);

        $content = '';

        $namePre = str_replace('[default_settings_', '[default_settings][', $params['itemFormElName']);

        /* @var $context Tx_Contexts_Context_Abstract */
        $uid = (int) $params['row']['uid'];
        $context = $uid
            ? Tx_Contexts_Context_Container::get()->initAll()->find($uid)
            : null;

        foreach ($params['fieldConf']['config']['settings'] as $setting => $config) {
            $id = $params['itemFormElID'] . '-' . $setting;
            $name = $namePre . '[' . $setting . ']';
            $content .= '<input type="hidden" name="' . $name . '" value="0"/>';
            $content .= '<input class="checkbox" type="checkbox" name="' . $name . '" ';
            if (
                !$context ||
                !$context->hasSetting($table, $setting, 0) ||
                $context->getSetting($table, $setting, 0)->getEnabled()
            ) {
                $content .= 'checked="checked" ';
            }
            $content .= 'value="1" id="' . $id . '" /> ';
            $content .= '<label for="' . $id . '">';
            $content .= $fobj->sL($config['label']);
            $content .= '</label><br/>';
        }

        return $content;
    }

}
?>