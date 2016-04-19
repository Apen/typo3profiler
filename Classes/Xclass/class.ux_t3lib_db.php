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

/**
 * XCLASS the database connection to log queries
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage typo3profiler
 */
class ux_t3lib_db extends t3lib_db
{
    protected $profiledQueries = array();

    public function exec_INSERTquery($table, $fields_values, $no_quote_fields = false)
    {
        $query = $this->INSERTquery($table, $fields_values, $no_quote_fields);
        $res = $this->execAndProfileQuery($query, 'INSERT');
        return $res;
    }

    public function exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields = false)
    {
        $query = $this->UPDATEquery($table, $where, $fields_values, $no_quote_fields);
        $res = $this->execAndProfileQuery($query, 'UPDATE');
        return $res;
    }

    public function exec_DELETEquery($table, $where)
    {
        $query = $this->DELETEquery($table, $where);
        $res = $this->execAndProfileQuery($query, 'DELETE');
        return $res;
    }

    public function exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '')
    {
        $query = $this->SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);
        $res = $this->execAndProfileQuery($query, 'SELECT');
        return $res;
    }

    public function sql_query($query)
    {
        preg_match('/\s*(\w+)\s/i', $query, $matches);
        $keyword = strtoupper(trim($matches[1]));
        $res = $this->execAndProfileQuery($query, $keyword);
        return $res;
    }

    public function init()
    {
        $GLOBALS['TYPO3_DB']->mysqlprofilerConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3profiler']);
        $GLOBALS['TYPO3_DB']->mysqlprofilerConf['includeTypes'] = ($GLOBALS['TYPO3_DB']->mysqlprofilerConf['includeTypes'] != '') ? t3lib_div::trimExplode(',',
            $GLOBALS['TYPO3_DB']->mysqlprofilerConf['includeTypes']
        ) : array('SELECT');
        $GLOBALS['TYPO3_DB']->mysqlprofilerConf['nbQueries'] = ($GLOBALS['TYPO3_DB']->mysqlprofilerConf['nbQueries'] != '') ? intval($GLOBALS['TYPO3_DB']->mysqlprofilerConf['nbQueries']) : 5;
        $GLOBALS['TYPO3_DB']->mysqlprofilerConf['maxQueries'] = ($GLOBALS['TYPO3_DB']->mysqlprofilerConf['maxQueries'] != '') ? intval($GLOBALS['TYPO3_DB']->mysqlprofilerConf['maxQueries']) : 100;
        if ($GLOBALS['TYPO3_DB']->mysqlprofilerConf['excludeTables'] != '') {
            $excludeTables = array();
            $ets = t3lib_div::trimExplode(',', $GLOBALS['TYPO3_DB']->mysqlprofilerConf['excludeTables']);
            foreach ($ets as $et) {
                $resEt = $GLOBALS['TYPO3_DB']->sql_query('SHOW TABLES LIKE "' . $et . '"');
                while ($rowEt = $GLOBALS['TYPO3_DB']->sql_fetch_row($resEt)) {
                    $excludeTables[] = $rowEt[0];
                }
                $GLOBALS['TYPO3_DB']->sql_free_result($resEt);
            }
            $GLOBALS['TYPO3_DB']->mysqlprofilerConf['excludeTables'] = $excludeTables;
        }
    }

    public function isProfiling($query, $type)
    {
        // dont log be queries
        if (TYPO3_MODE == 'BE') {
            return false;
        }
        // not authorized type
        if (!in_array($type, $GLOBALS['TYPO3_DB']->mysqlprofilerConf['includeTypes'])) {
            return false;
        }
        // not authorized table
        if (is_array($GLOBALS['TYPO3_DB']->mysqlprofilerConf['excludeTables'])) {
            foreach ($GLOBALS['TYPO3_DB']->mysqlprofilerConf['excludeTables'] as $excludeTable) {
                //t3lib_div::debug($query,$excludeTable);
                if (stripos($query, $excludeTable) !== false) {
                    return false;
                }
            }
        }
        // not authorized regexp
        if (!empty($GLOBALS['TYPO3_DB']->mysqlprofilerConf['excludeRegexp'])) {
            if (preg_match($GLOBALS['TYPO3_DB']->mysqlprofilerConf['excludeRegexp'], $query, $matches) > 0) {
                return false;
            }
        }
        return true;
    }

    public function execAndProfileQuery($query, $type)
    {
        if (empty($GLOBALS['TYPO3_DB']->mysqlprofilerConf['excludeTables'])) {
            $this->init();
        }

        $isProfiling = $this->isProfiling($query, $type);

        if ($isProfiling) {
            $begin = microtime(true);
        }

        // exec query
        if (Typo3profiler_Utility_Compatibility::intFromVer(TYPO3_version) > 6000000) {
            if (!$this->isConnected) {
                $this->connectDB();
            }
            $res = $this->link->query($query);
        } else {
            $res = mysql_query($query, $this->link);
        }

        if ($isProfiling) {
            $deltatime = round((microtime(true) - $begin) * 1000, 8);

            if ($GLOBALS['TSFE']->id == 0) {
                $debugFunc = $this->get_caller_method(3);
            } else {
                $debugFunc = $this->get_caller_method(2);
            }

            if (TYPO3_MODE == 'BE') {
                $debugFunc = $this->get_caller_method(3);
            }

            $debug = array(
                'type'      => $type,
                'query'     => $query,
                'time'      => $deltatime,
                'backtrace' => $debugFunc,
                'typo3mode' => TYPO3_MODE,
                'page'      => ($GLOBALS['TSFE']->id !== null) ? $GLOBALS['TSFE']->id : '',
            );

            if ($GLOBALS['TYPO3_DB']->mysqlprofilerConf['debugbarenabled'] == 1) {
                if (t3lib_div::cmpIP(t3lib_div::getIndpEnv('REMOTE_ADDR'), $GLOBALS['TYPO3_CONF_VARS']['SYS']['devIPmask'])) {
                    $GLOBALS['debugbar']['queries']->info('[' . $deltatime . '] ' . $query . ' --> ' . $debugFunc['file'] . ' @ ' . $debugFunc['line'] . ' : ' . $debugFunc['function']);
                }
            }

            $this->profiledQueries [] = $debug;

            if (TYPO3_MODE == 'BE') {
                $this->cleanSqlLog();
                $this->insertSqlLog($debug);
            }
        }

        return $res;
    }

    public function profiling()
    {
        $datas = $this->profiledQueries;
        if (empty($datas)) {
            return null;
        }
        $nbQueries = count($datas);
        usort($datas, array('ux_t3lib_db', 'sortByDuration'));
        $datas = array_slice($datas, 0, $GLOBALS['TYPO3_DB']->mysqlprofilerConf['nbQueries']);
        $this->cleanSqlLog();
        $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_typo3profiler_sql', 'page=' . intval($GLOBALS['TSFE']->id));
        foreach ($datas as $data) {
            $this->insertSqlLog($data);
        }
        return $nbQueries;
    }

    public function insertSqlLog($data)
    {
        $GLOBALS['TYPO3_DB']->exec_INSERTQuery(
            'tx_typo3profiler_sql',
            array(
                'pid'       => 0,
                'type'      => $data['type'],
                'query'     => $data['query'],
                'time'      => $data['time'],
                'backtrace' => 'file ' . $data['backtrace']['file'] . ' @ line ' . $data['backtrace']['line'] . ' : function ' . $data['backtrace']['function'],
                'typo3mode' => $data['typo3mode'],
                'page'      => $data['page'],
            )
        );
    }

    public function cleanSqlLog()
    {
        $query = 'SELECT uid FROM tx_typo3profiler_sql ORDER BY time DESC LIMIT ' . $GLOBALS['TYPO3_DB']->mysqlprofilerConf['maxQueries'];
        $res = $GLOBALS['TYPO3_DB']->sql_query($query);
        $listOfUids = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $listOfUids [] = $row['uid'];
        }
        $GLOBALS['TYPO3_DB']->sql_free_result($res);
        $GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_typo3profiler_sql', 'uid NOT IN (' . implode(',', $listOfUids) . ')');
    }

    public function sortByDuration($a, $b)
    {
        if ($a['time'] == $b['time']) {
            return 0;
        } else {
            return ($a['time'] < $b['time']) ? 1 : -1;
        }
    }

    public function get_caller_method($rank)
    {
        $traces = debug_backtrace();
        if (isset($traces[$rank])) {
            return array(
                'file'     => $traces[$rank]['file'],
                'line'     => $traces[$rank]['line'],
                'function' => $traces[$rank]['function']
            );
        }
        return null;
    }

}

?>