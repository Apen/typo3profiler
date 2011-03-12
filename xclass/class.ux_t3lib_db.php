<?php

class ux_t3lib_db extends t3lib_db
{
	protected $profiledQueries = array();
	protected $initMysqlprofilerConf = array();

	function exec_INSERTquery($table, $fields_values, $no_quote_fields = false)
	{
		$query = $this->INSERTquery($table, $fields_values, $no_quote_fields);
		$res = $this->execAndProfileQuery($query, 'INSERT');
		if ($this->debugOutput) $this->debug('exec_INSERTquery');
		return $res;
	}

	function exec_UPDATEquery($table, $where, $fields_values, $no_quote_fields = false)
	{
		$query = $this->UPDATEquery($table, $where, $fields_values, $no_quote_fields);
		$res = $this->execAndProfileQuery($query, 'UPDATE');
		if ($this->debugOutput) $this->debug('exec_UPDATEquery');
		return $res;
	}

	function exec_DELETEquery($table, $where)
	{
		$query = $this->DELETEquery($table, $where);
		$res = $this->execAndProfileQuery($query, 'DELETE');
		if ($this->debugOutput) $this->debug('exec_DELETEquery');
		return $res;
	}

	function exec_SELECTquery($select_fields, $from_table, $where_clause, $groupBy = '', $orderBy = '', $limit = '')
	{
		$query = $this->SELECTquery($select_fields, $from_table, $where_clause, $groupBy, $orderBy, $limit);
		$res = $this->execAndProfileQuery($query, 'SELECT');
		if ($this->debugOutput) {
			$this->debug('exec_SELECTquery');
		}
		if ($this->explainOutput) {
			$this->explain($query, $from_table, $this->sql_num_rows($res));
		}
		return $res;
	}

	function init()
	{
		$this->mysqlprofilerConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['typo3profiler']);
		$this->mysqlprofilerConf['includeTypes'] = ($this->mysqlprofilerConf['includeTypes'] != '') ? t3lib_div::trimExplode(',', $this->mysqlprofilerConf['includeTypes']) : array('SELECT');
		$this->mysqlprofilerConf['nbQueries'] = ($this->mysqlprofilerConf['nbQueries'] != '') ? intval($this->mysqlprofilerConf['nbQueries']) : 5;
		$this->mysqlprofilerConf['maxQueries'] = ($this->mysqlprofilerConf['maxQueries'] != '') ? intval($this->mysqlprofilerConf['maxQueries']) : 100;
		if ($this->mysqlprofilerConf['excludeTables'] != '') {
			$excludeTables = array();
			$ets = t3lib_div::trimExplode(',', $this->mysqlprofilerConf['excludeTables']);
			foreach ($ets as $et) {
				$resEt = $GLOBALS['TYPO3_DB']->sql_query('SHOW TABLES LIKE \'' . $et . '\'');

				while ($rowEt = $GLOBALS['TYPO3_DB']->sql_fetch_row($resEt)) {
					$excludeTables[] = $rowEt[0];
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($resEt);
			}
			$this->mysqlprofilerConf['excludeTables'] = $excludeTables;
		}
	}

	function isProfiling($query, $type)
	{
		if (!in_array($type, $this->mysqlprofilerConf['includeTypes'])) {
			return false;
		}
		if (is_array($this->mysqlprofilerConf['excludeTables'])) {
			foreach ($this->mysqlprofilerConf['excludeTables'] as $excludeTable) {
				if (strpos($query, $excludeTable) !== false) {
					return false;
				}
			}
		}
		return true;
	}

	function execAndProfileQuery($query, $type)
	{
		if (!$this->mysqlprofilerConf) {
			$this->init();
		}
		$isProfiling = $this->isProfiling($query, $type);

		if ($isProfiling) {
			$begin = microtime(true);
		}

		$res = mysql_query($query, $this->link);

		if ($isProfiling) {
			$deltatime = round((microtime(true) - $begin) * 1000, 8);

			if ($GLOBALS['TSFE']->id == 0) {
				$debugFunc = $this->get_caller_method(3);
			} else {
				$debugFunc = $this->get_caller_method(2);
			}

			$debug = array(
				'type' => $type,
				'query' => $query,
				'time' => $deltatime,
				'backtrace' => $debugFunc,
				'typo3mode' => TYPO3_MODE,
				'page' => ($GLOBALS['TSFE']->id !== null) ? $GLOBALS['TSFE']->id : '',
			);

			$this->profiledQueries [] = $debug;
		}

		return $res;
	}

	function profiling()
	{
		$datas = $this->profiledQueries;
		if (empty($datas)) {
			return;
		}
		$nbQueries = count($datas);
		usort($datas, array('ux_t3lib_db', 'sortByDuration'));
		$datas = array_slice($datas, 0, $this->mysqlprofilerConf['nbQueries']);

		if (TYPO3_MODE == 'FE') {
			$this->cleanSQLLog();
			$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_typo3profiler_sql', 'page=' . intval($GLOBALS['TSFE']->id));
			foreach ($datas as $data) {
				$GLOBALS['TYPO3_DB']->exec_INSERTQuery(
					'tx_typo3profiler_sql',
					array(
					     'pid' => 0,
					     'type' => $data['type'],
					     'query' => $data['query'],
					     'time' => $data['time'],
					     'backtrace' => 'file ' . $data['backtrace']['file'] . ' @ line ' . $data['backtrace']['line'] . ' : function ' . $data['backtrace']['function'],
					     'typo3mode' => $data['typo3mode'],
					     'page' => $data['page'],
					)
				);
			}
		}

		return $nbQueries;
	}

	function cleanSQLLog()
	{
		$query = 'SELECT uid FROM tx_typo3profiler_sql ORDER BY time DESC LIMIT ' . $this->mysqlprofilerConf['maxQueries'];
		$res = $GLOBALS['TYPO3_DB']->sql_query($query);
		$listOfUids = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$listOfUids [] = $row['uid'];
		}
		$GLOBALS['TYPO3_DB']->sql_free_result($res);
		$GLOBALS['TYPO3_DB']->exec_DELETEquery('tx_typo3profiler_sql', 'uid NOT IN (' . implode(',', $listOfUids) . ')');
	}

	function sortByDuration($a, $b)
	{
		if ($a['time'] == $b['time']) {
			return 0;
		} else {
			return ($a['time'] < $b['time']) ? 1 : -1;
		}
	}

	function get_caller_method($rank)
	{
		$traces = debug_backtrace();

		if (isset($traces[$rank])) {
			return array(
				'file' => $traces[$rank]['file'],
				'line' => $traces[$rank]['line'],
				'function' => $traces[$rank]['function']
			);
		}

		return null;
	}
}

?>