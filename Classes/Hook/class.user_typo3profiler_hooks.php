<?php

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2013 CERDAN Yohann <cerdanyohann@yahoo.fr>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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
class user_typo3profiler implements \TYPO3\CMS\Frontend\ContentObject\ContentObjectPostInitHookInterface
{
    public function contentPostProc($_funcRef, $_params)
    {
        $nbQueries = $GLOBALS['TYPO3_DB']->profiling();
        $conf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3profiler']);
        $logTS = $GLOBALS['TT']->printTSlog();
        $logTS = preg_replace('/src="typo3/', 'src="/typo3', $logTS);
        $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_typo3profiler_page', 'page=' . intval($GLOBALS['TSFE']->id));
        $GLOBALS['TYPO3_DB']->exec_INSERTQuery(
            'tx_typo3profiler_page',
            array(
                'pid'       => 0,
                'parsetime' => $GLOBALS['TSFE']->scriptParseTime,
                'page'      => $GLOBALS['TSFE']->id,
                'logts'     => $logTS,
                'size'      => \TYPO3\CMS\Core\Utility\GeneralUtility::formatSize(strlen($GLOBALS['TSFE']->content)),
                'nocache'   => $GLOBALS['TSFE']->no_cache ? 1 : 0,
                'userint'   => count($GLOBALS['TSFE']->config['INTincScript']),
                'nbqueries' => $nbQueries,
            )
        );
        if ($conf['debugbarenabled'] == 1) {
            Typo3profiler_Utility_Debugbar::render();
        }
    }

    public function postProcessContentObjectInitialization(\TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer &$parentObject)
    {
        if (TYPO3_MODE == 'BE') {
            return null;
        }
        $GLOBALS['TT']->LR = 1;
        $GLOBALS['TSFE']->forceTemplateParsing;
        $GLOBALS['TT']->printConf['flag_tree'] = 1;
        $GLOBALS['TT']->printConf['allTime'] = 1;
        $GLOBALS['TT']->printConf['flag_messages'] = 0;
        $GLOBALS['TT']->printConf['flag_content'] = 0;
        $GLOBALS['TT']->printConf['flag_queries'] = 0;
        $GLOBALS['TT']->printConf['keyLgd'] = 100;
    }
}

?>