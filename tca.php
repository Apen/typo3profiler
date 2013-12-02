<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

$TCA['tx_typo3profiler_sql'] = array(
	'ctrl' => $TCA['tx_typo3profiler_sql']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden,type,query,time,backtrace'
	),
	'feInterface' => $TCA['tx_typo3profiler_sql']['feInterface'],
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'default' => '0'
			)
		),
		'type' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_sql.type',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'query' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_sql.query',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'time' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_sql.time',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'page' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_sql.page',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'typo3mode' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_sql.typo3mode',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'backtrace' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_sql.backtrace',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'hidden;;1;;1-1-1, type, query, time, backtrace')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	)
);

$TCA['tx_typo3profiler_page'] = array(
	'ctrl' => $TCA['tx_typo3profiler_sql']['ctrl'],
	'interface' => array(
		'showRecordFieldList' => 'hidden,parsetime,page,logts,size,nocache,userint,nbqueries'
	),
	'feInterface' => $TCA['tx_typo3profiler_sql']['feInterface'],
	'columns' => array(
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config' => array(
				'type' => 'check',
				'default' => '0'
			)
		),
		'parsetime' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_page.parsetime',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'page' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_page.page',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'logts' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_page.logts',
			'config' => array(
				'type' => 'text',
				'cols' => '30',
				'rows' => '5',
			)
		),
		'size' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_page.size',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'nocache' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_page.nocache',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'userint' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_page.userint',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			)
		),
		'nbqueries' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:typo3profiler/locallang_db.xml:tx_typo3profiler_page.nbqueries',
			'config' => array(
				'type' => 'input',
				'size' => '30',
			)
		),
	),
	'types' => array(
		'0' => array('showitem' => 'hidden;;1;;1-1-1, parsetime,page,logts,size,nocache,userint,nbqueries')
	),
	'palettes' => array(
		'1' => array('showitem' => '')
	)
);
?>