<?php

namespace Sng\Typo3profiler\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2015 CERDAN Yohann <cerdanyohann@yahoo.fr>
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

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

class SqlController extends ActionController
{

    /**
     * action index
     *
     * @return void
     */
    public function indexAction()
    {
        $query = array();
        $query['SELECT'] = 'uid,type,query,time,backtrace,page,typo3mode';
        $query['FROM'] = 'tx_typo3profiler_sql';
        $query['WHERE'] = '1=1';
        $query['GROUPBY'] = '';
        $query['ORDERBY'] = 'time DESC';
        $query['LIMIT'] = '';
        $this->view->assign('query', $query);
    }

    /**
     * action show
     *
     * @param int $uid
     *
     * @return void
     */
    public function showAction($uid)
    {
        $query['SELECT'] = 'type,query,time,backtrace,page,typo3mode';
        $query['FROM'] = 'tx_typo3profiler_sql';
        $query['WHERE'] = 'uid=' . intval($uid);
        $res = $this->getDatabaseConnection()->exec_SELECT_queryArray($query);
        $row = $this->getDatabaseConnection()->sql_fetch_assoc($res);
        $this->getDatabaseConnection()->sql_free_result($res);
        $res = $this->getDatabaseConnection()->sql_query('EXPLAIN ' . $row['query']);
        $explain = array();
        $explainHeader = array();
        while ($rowExplain = $this->getDatabaseConnection()->sql_fetch_assoc($res)) {
            $explain[] = $rowExplain;
            $explainHeader = array_keys($rowExplain);
        }
        $this->getDatabaseConnection()->sql_free_result($res);
        $this->view->assign('item', $row);
        $this->view->assign('explain', $explain);
        $this->view->assign('explainHeader', $explainHeader);
    }

    /**
     * action flush
     *
     * @return void
     */
    public function flushAction()
    {
        $this->getDatabaseConnection()->exec_DELETEquery('tx_typo3profiler_sql', '');
        $this->redirect('index');
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
