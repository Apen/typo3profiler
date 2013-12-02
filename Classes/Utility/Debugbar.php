<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 Yohann CERDAN <cerdanyohann@yahoo.fr>
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use DebugBar\DebugBar;
use DebugBar\DataCollector\MessagesCollector;
use DebugBar\DataCollector\ConfigCollector;
use DebugBar\DataCollector\RequestDataCollector;

/**
 * Debug bar
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage typo3profiler
 */
class Typo3profiler_Utility_Debugbar {

	/**
	 * Init the debug bar
	 */
	public static function init() {
		if (t3lib_div::cmpIP(t3lib_div::getIndpEnv('REMOTE_ADDR'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'])) {
			require_once(PATH_typo3conf . 'ext/typo3profiler/Classes/Lib/DebugBar/vendor/autoload.php');
			$GLOBALS['debugbar'] = new DebugBar();
			$GLOBALS['debugbar']->addCollector(new RequestDataCollector());
			$GLOBALS['debugbar']->addCollector(new ConfigCollector(array(), 'PHP'));
			$GLOBALS['debugbar']->addCollector(new ConfigCollector(array(), 'page'));
			$GLOBALS['debugbar']->addCollector(new ConfigCollector(array(), 'typoscript'));
			$GLOBALS['debugbar']->addCollector(new MessagesCollector('queries'));
			//$GLOBALS['debugbar']->addCollector(new MessagesCollector('contents'));
		}
	}

	/**
	 * Render the debug bar
	 */
	public static function render() {
		if (t3lib_div::cmpIP(t3lib_div::getIndpEnv('REMOTE_ADDR'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'])) {
			$debugbarRenderer = $GLOBALS['debugbar']->getJavascriptRenderer();
			$debugbarRenderer->setBaseUrl('typo3conf/ext/typo3profiler/Classes/Lib/DebugBar/vendor/maximebf/debugbar/src/DebugBar/Resources')->setEnableJqueryNoConflict(FALSE);
			self::renderPhp();
			self::renderPage();
			self::renderTyposcript();
			//self::renderContents();
			$GLOBALS['TSFE']->content = str_replace('</head>', $debugbarRenderer->renderHead() . '</head>', $GLOBALS['TSFE']->content);
			$GLOBALS['TSFE']->content = str_replace('</body>', $debugbarRenderer->render() . '</body>', $GLOBALS['TSFE']->content);
		}
	}

	public static function renderPhp() {
		$GLOBALS['debugbar']['PHP']->setData(self::phpinfoArray());
	}

	function phpinfoArray() {
		ob_start();
		phpinfo();
		$infoArr = array();
		$infoArr['General']['version'] = phpversion();
		$infoLines = explode("\n", strip_tags(ob_get_clean(), "<tr><td><h2>"));
		$cat = "General";
		foreach ($infoLines as $line) {
			// new cat?
			preg_match("~<h2>(.*)</h2>~", $line, $title) ? $cat = $title[1] : NULL;
			if (preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)) {
				$infoArr[$cat][trim($val[1])] = $val[2];
			} elseif (preg_match("~<tr><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td><td[^>]+>([^<]*)</td></tr>~", $line, $val)) {
				$infoArr[$cat][trim($val[1])] = array("local" => $val[2], "master" => $val[3]);
			}
		}
		return $infoArr;
	}

	public static function renderPage() {
		$GLOBALS['debugbar']['page']->setData($GLOBALS['TSFE']->page);
	}

	public static function renderTyposcript() {
		$typoscript = array();
		foreach ($GLOBALS['TSFE']->tmpl->setup as $setupKey => $setup) {
			$GLOBALS['TSFE']->tmpl->setup[$setupKey] = str_replace(array("\n", "\r", "\r\n"), array('', '', ''), Typo3profiler_Utility_Compatibility::viewArray($setup));
		}
		$GLOBALS['debugbar']['typoscript']->setData(self::mdArrayMap('utf8_encode', $GLOBALS['TSFE']->tmpl->setup));
	}

	public static function renderContents() {
		/*$apiObj = t3lib_div::makeInstance('tx_templavoila_api', 'pages');
		$rootElementRecord = t3lib_BEfunc::getRecordWSOL('pages', $GLOBALS['TSFE']->id, '*');
		$contentTreeData = $apiObj->getContentTree('pages', $rootElementRecord);
		$usedUids = array_keys($contentTreeData['contentElementUsage']);
		$GLOBALS['debugbar']['contents']->info($rootElementRecord);
		$GLOBALS['debugbar']['contents']->info($contentTreeData);
		$GLOBALS['debugbar']['contents']->info($GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,pid,CType,list_type,header', 'tt_content', 'uid IN (' . implode(',', $usedUids) . ')'));*/
	}

	public static function mdArrayMap($func, $arr) {
		$ret = array();
		foreach ($arr as $key => $val) {
			$ret[$key] = (is_array($val) ? self::mdArrayMap($func, $val) : $func($val));
		}
		return $ret;
	}
}