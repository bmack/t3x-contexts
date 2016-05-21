<?php
defined('TYPO3_MODE') || die();

$GLOBALS['TYPO3_CONF_VARS']['FE']['addRootLineFields'] .= ',tx_contexts_enable,tx_contexts_disable';

//hook into record saving
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass']['contexts']
    = \Bmack\Contexts\Service\DataHandlerService::class;

//override enableFields
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['addEnableColumns']['contexts']
    = \Bmack\Contexts\Service\PageService::class . '->enableFields';

//override page access control
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_page.php']['getPage'][]
    = \Bmack\Contexts\Service\PageService::class;

//override page menu visibility
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/tslib/class.tslib_menu.php']['filterMenuPages'][]
    = \Bmack\Contexts\Service\PageService::class;

//override page hash generation
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['createHashBase'][]
    = \Bmack\Contexts\Service\PageService::class . '->createHashBase';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkAlternativeIdMethods-PostProc']['contexts']
    = \Bmack\Contexts\Service\FrontendControllerService::class . '->initFEuser';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['typo3/mod/tools/em/index.php']['checkDBupdates']['contexts']
    = 'EXT:contexts/Classes/Service/Install.php:Tx_Contexts_Service_Install';


// Add tree icons
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_iconworks.php']['overrideIconOverlay'][]
    = \Bmack\Contexts\Service\IconService::class;

// add some hooks
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['hook_checkEnableFields']['contexts']
    = \Bmack\Contexts\Service\FrontendControllerService::class . '->checkEnableFields';


// load the custom typoscript condition here
if (TYPO3_MODE == 'FE') {
    require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('contexts', 'Resources/Private/PHP/TypoScriptConditionMatcher.php');
}
