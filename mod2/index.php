<?php
/**
 * Copyright notice
 *
 *    (c) 2011  <>
 *    All rights reserved
 *
 *    This script is part of the TYPO3 project. The TYPO3 project is
 *    free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    The GNU General Public License can be found at
 *    http://www.gnu.org/copyleft/gpl.html.
 *
 *    This script is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details.
 *
 *    This copyright notice MUST APPEAR in all copies of the script!
 */

$LANG->includeLLFile('EXT:typo3profiler/mod2/locallang.xml');
require_once(PATH_t3lib . 'class.t3lib_scbase.php');
require_once(PATH_typo3 . 'class.db_list.inc');
require_once(PATH_typo3 . 'class.db_list_extra.inc');
require_once(PATH_typo3 . 'sysext/cms/layout/class.tx_cms_layout.php');
$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]
/**
 * Module 'Données' for the 'typo3profiler' extension.
 *
 * @author <>
 * @package TYPO3
 * @subpackage tx_typo3profiler
 */
class tx_typo3profiler_module2 extends t3lib_SCbase
{
	public $pageinfo;
	public $items = array();
	protected $nbElementsPerPage = 10;
	protected $exportMode = 'all';

	/**
	 * Initializes the Module
	 *
	 * @return void
	 */

	function init()
	{
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		// Check nb per page
		$nbPerPage = t3lib_div::_GP('nbPerPage');
		if ($nbPerPage !== null) {
			$this->nbElementsPerPage = $nbPerPage;
		}
		$flush = t3lib_div::_GP('flush');
		if ($flush !== null) {
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_typo3profiler_sql', '');
		}
		parent::init();
	}

	/**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return [type]		...
	 */
	function main()
	{
		global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
		// Draw the header.
		$this->doc = t3lib_div::makeInstance('bigDoc');
		$this->doc->styleSheetFile2 = '../typo3conf/ext/typo3profiler/lib/module.css';
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form = '<form action="" method="post" enctype="multipart/form-data">';
		// JavaScript
		$this->doc->JScode = '
			<script language="javascript" type="text/javascript">
			script_ended = 0;
			function jumpToUrl(URL)	{
			document.location = URL;
			}
			</script>
		';

		$this->doc->postCode = '
			<script language="javascript" type="text/javascript">
			script_ended = 1;
			if (top.fsMod) top.fsMod.recentIds["web"] = 0;
			</script>
		';

		$this->content .= $this->doc->startPage($LANG->getLL('title'));

		$this->content .= '<table width="100%"><tr><td class="functitle" width="50%">' . $LANG->getLL('choose') . '</td><td align="right" width="50%"><input type="button" onclick="jumpToUrl(\'' . t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=txtypo3profilerM1_sql&flush=true\');" value="' . $GLOBALS['LANG']->getLL('flush') . '"/></td></tr></table>';

		$this->content .= $this->doc->divider(5);
		$this->moduleContent();
	}

	/**
	 * Prints out the module HTML
	 *
	 * @return void
	 */
	function printContent()
	{
		$this->content .= $this->doc->endPage();
		echo $this->content;
	}

	/**
	 * Generates the module content
	 *
	 * @return void
	 */
	function moduleContent()
	{
		$query = array();
		$query['SELECT'] = 'type,query,time,backtrace,page,typo3mode';
		$query['FROM'] = 'tx_typo3profiler_sql';
		$query['WHERE'] = '1=1';
		$query['GROUPBY'] = '';
		$query['ORDERBY'] = 'time DESC';
		$query['LIMIT'] = '';
		$content = $this->drawTable($query);
		$this->content .= $content;
	}

	function drawTable($query)
	{
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', $query['FROM'], $query['WHERE'], $query['GROUPBY'], $query['ORDERBY'], $query['LIMIT']);
		$listOfUids = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$listOfUids [] = $row['uid'];
		}

		// Page browser
		$pointer = t3lib_div::_GP('pointer');
		$limit = ($pointer !== null) ? $pointer . ',' . $this->nbElementsPerPage : '0,' . $this->nbElementsPerPage;
		$current = ($pointer !== null) ? intval($pointer) : 0;
		$pageBrowser = $this->renderListNavigation($GLOBALS['TYPO3_DB']->sql_num_rows($res), $this->nbElementsPerPage, $current);
		$query['WHERE'] .= ' AND uid IN (' . implode(',', $listOfUids) . ')';
		$query['LIMIT'] = $limit;

		if ($GLOBALS['TYPO3_DB']->sql_num_rows($res) > 0) {
			$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($query);
			$content = $pageBrowser;
			$content .= $this->formatAllResults($result, $query['FROM'], $GLOBALS['LANG']->getLL('title'));
			$GLOBALS['TYPO3_DB']->sql_free_result($result);
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		return $content;
	}

	function formatAllResults($res, $table, $title)
	{
		$content = '';

		$content .= '<table cellspacing="1" cellpadding="2" border="0" class="tx_sv_reportlist typo3-dblist">';
		$content .= '<tr class="t3-row-header"><td colspan="10">';
		$content .= $title;
		$content .= '</td></tr>';
		$content .= '<tr class="c-headLine">';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('type') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('query') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('parsetime') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('call') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('pageuid') . '</td>';
		$content .= '<td class="cell">' . $GLOBALS['LANG']->getLL('typo3mode') . '</td>';
		$content .= '</tr>';

		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$content .= '<tr class="db_list_normal">';
			$content .= '<td class="cell">' . $row['type'] . '</td>';
			$content .= '<td class="cell" title="' . htmlentities($row['query']) . '">' . htmlentities(t3lib_div::fixed_lgd($row['query'], 100)) . '</td>';
			$content .= '<td class="cell">' . $row['time'] . '</td>';
			$content .= '<td class="cell">' . $row['backtrace'] . '</td>';
			$content .= '<td class="cell">' . $row['page'] . '</td>';
			$content .= '<td class="cell">' . $row['typo3mode'] . '</td>';
			$content .= '</tr>';
		}
		$content .= '</table>';
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		return $content;
	}

	/**
	 * Creates a page browser for tables with many records
	 */

	function renderListNavigation($totalItems, $iLimit, $firstElementNumber, $renderPart = 'top')
	{
		$totalPages = ceil($totalItems / $iLimit);

		$content = '';
		$returnContent = '';
		// Show page selector if not all records fit into one page
		$first = $previous = $next = $last = $reload = '';
		$listURLOrig = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=txtypo3profilerM1_sql';
		$listURL = t3lib_div::getIndpEnv('TYPO3_REQUEST_DIR') . 'mod.php?M=txtypo3profilerM1_sql';
		$listURL .= '&nbPerPage=' . $this->nbElementsPerPage;
		$currentPage = floor(($firstElementNumber + 1) / $iLimit) + 1;
		// First
		if ($currentPage > 1) {
			$labelFirst = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:first');
			$first = '<a href="' . $listURL . '&pointer=0"><img width="16" height="16" title="' . $labelFirst . '" alt="' . $labelFirst . '" src="sysext/t3skin/icons/gfx/control_first.gif"></a>';
		} else {
			$first = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_first_disabled.gif">';
		}
		// Previous
		if (($currentPage - 1) > 0) {
			$labelPrevious = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:previous');
			$previous = '<a href="' . $listURL . '&pointer=' . (($currentPage - 2) * $iLimit) . '"><img width="16" height="16" title="' . $labelPrevious . '" alt="' . $labelPrevious . '" src="sysext/t3skin/icons/gfx/control_previous.gif"></a>';
		} else {
			$previous = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_previous_disabled.gif">';
		}
		// Next
		if (($currentPage + 1) <= $totalPages) {
			$labelNext = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:next');
			$next = '<a href="' . $listURL . '&pointer=' . (($currentPage) * $iLimit) . '"><img width="16" height="16" title="' . $labelNext . '" alt="' . $labelNext . '" src="sysext/t3skin/icons/gfx/control_next.gif"></a>';
		} else {
			$next = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_next_disabled.gif">';
		}
		// Last
		if ($currentPage != $totalPages) {
			$labelLast = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_common.xml:last');
			$last = '<a href="' . $listURL . '&pointer=' . (($totalPages - 1) * $iLimit) . '"><img width="16" height="16" title="' . $labelLast . '" alt="' . $labelLast . '" src="sysext/t3skin/icons/gfx/control_last.gif"></a>';
		} else {
			$last = '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/control_last_disabled.gif">';
		}

		$pageNumberInput = '<span>' . $currentPage . '</span>';
		$pageIndicator = '<span class="pageIndicator">'
		                 . sprintf($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:pageIndicator'), $pageNumberInput, $totalPages)
		                 . '</span>';

		if ($totalItems > ($firstElementNumber + $iLimit)) {
			$lastElementNumber = $firstElementNumber + $iLimit;
		} else {
			$lastElementNumber = $totalItems;
		}

		$rangeIndicator = '<span class="pageIndicator">'
		                  . sprintf($GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_list.xml:rangeIndicator'), $firstElementNumber + 1, $lastElementNumber)
		                  . '</span>';

		$reload = '<input type="text" name="nbPerPage" id="nbPerPage" size="5" value="' . $this->nbElementsPerPage . '"/> / page '
		          . '<a href="#"  onClick="jumpToUrl(\'' . $listURLOrig . '&nbPerPage=\'+document.getElementById(\'nbPerPage\').value);">'
		          . '<img width="16" height="16" title="" alt="" src="sysext/t3skin/icons/gfx/refresh_n.gif"></a>';

		$content .= '<div id="typo3-dblist-pagination">'
		            . $first . $previous
		            . '<span class="bar">&nbsp;</span>'
		            . $rangeIndicator . '<span class="bar">&nbsp;</span>'
		            . $pageIndicator . '<span class="bar">&nbsp;</span>'
		            . $next . $last . '<span class="bar">&nbsp;</span>'
		            . $reload
		            . '</div>';

		$returnContent = $content;

		return $returnContent;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3profiler/mod2/index.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/typo3profiler/mod2/index.php']);
}
// Make instance:
$SOBE = t3lib_div::makeInstance('tx_typo3profiler_module2');
$SOBE->init();
// Include files?
foreach ($SOBE->include_once as $INC_FILE) include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>