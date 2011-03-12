<?php

if (!defined('TYPO3_MODE')) die ('Access denied.');

$conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3profiler']);

if ($conf['enabled'] == 1) {
	// For profiling rendering
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['hook_eofe']['typo3profiler'] = 'EXT:typo3profiler/hooks/class.user_typo3profiler_hooks.php:&user_typo3profiler->contentPostProc';
	$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_content.php']['postInit']['typo3profiler'] = 'EXT:typo3profiler/hooks/class.user_typo3profiler_hooks.php:&user_typo3profiler';

	// For profiling mysql
	$TYPO3_CONF_VARS['FE']['XCLASS']['t3lib/class.t3lib_db.php'] = t3lib_extMgm::extPath('typo3profiler') . 'xclass/class.ux_t3lib_db.php';
}

?>