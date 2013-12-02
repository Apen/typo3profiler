<?php

if (!defined('TYPO3_MODE')) die ('Access denied.');

$extensionPath = t3lib_extMgm::extPath('typo3profiler');
require_once($extensionPath . 'Classes/Utility/Compatibility.php');
require_once($extensionPath . 'Classes/Utility/Debugbar.php');

$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3profiler']);

if ($conf['enabled'] == 1) {
	// For profiling rendering
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['hook_eofe']['typo3profiler'] = 'EXT:typo3profiler/Classes/Hook/class.user_typo3profiler_hooks.php:&user_typo3profiler->contentPostProc';
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['postInit']['typo3profiler'] = 'EXT:typo3profiler/Classes/Hook/class.user_typo3profiler_hooks.php:&user_typo3profiler';

	if (Typo3profiler_Utility_Compatibility::intFromVer(TYPO3_version) > 6000000) {
		require_once($extensionPath . 'Classes/Xclass/DatabaseConnection.php');
		$GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects']['TYPO3\\CMS\\Core\\Database\\DatabaseConnection'] = array('className' => 'Typo3profiler_Xclass_DatabaseConnection');
	} else {
		// For profiling mysql
		$TYPO3_CONF_VARS['FE']['XCLASS']['t3lib/class.t3lib_db.php'] = t3lib_extMgm::extPath('typo3profiler') . 'Classes/Xclass/class.ux_t3lib_db.php';
		$TYPO3_CONF_VARS['BE']['XCLASS']['t3lib/class.t3lib_db.php'] = t3lib_extMgm::extPath('typo3profiler') . 'Classes/Xclass/class.ux_t3lib_db.php';
	}
}

if ($conf['debugbarenabled'] == 1) {
	Typo3profiler_Utility_Debugbar::init();
}

?>