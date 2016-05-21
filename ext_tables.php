<?php
defined('TYPO3_MODE') || die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr(
    'tx_contexts_contexts.type_conf.combination',
    'EXT:contexts/Resources/Private/csh/Combination.xml'
);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_contexts_contexts');

$icons = array(
    'status-overlay-contexts' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath() . 'overlay-contexts.png',
);
\TYPO3\CMS\Backend\Sprite\SpriteManager::addSingleIcons($icons, 'contexts');
$GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayPriorities'][150] = 'contexts';
$GLOBALS['TBE_STYLES']['spriteIconApi']['spriteIconRecordOverlayNames']['contexts']
    = 'extensions-contexts-status-overlay-contexts';
