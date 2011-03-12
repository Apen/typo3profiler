<?php
if (!defined ('TYPO3_MODE')) {
    die ('Access denied.');
}
$TCA['tx_typo3profiler_sql'] = array (
    'ctrl' => array (
        'title'     => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_sql',
        'label'     => 'uid',
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY crdate',
        'delete' => 'deleted',
        'enablecolumns' => array (
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_typo3profiler_sql.gif',
    ),
);

$TCA['tx_typo3profiler_page'] = array (
    'ctrl' => array (
        'title'     => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_page',
        'label'     => 'uid',
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'default_sortby' => 'ORDER BY crdate',
        'delete' => 'deleted',
        'enablecolumns' => array (
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_typo3profiler_page.gif',
    ),
);

if (TYPO3_MODE == 'BE') {
	t3lib_extMgm::addModule('txtypo3profilerM1', '', '', t3lib_extMgm::extPath($_EXTKEY) . 'modmain/');
	t3lib_extMgm::addModule('txtypo3profilerM1', 'page', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
	t3lib_extMgm::addModule('txtypo3profilerM1', 'sql', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod2/');
}

?>