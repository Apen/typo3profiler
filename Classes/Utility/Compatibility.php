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

/**
 * Compatibility class
 *
 * @author     Yohann CERDAN <cerdanyohann@yahoo.fr>
 * @package    TYPO3
 * @subpackage typo3profiler
 */
class Typo3profiler_Utility_Compatibility {

	/**
	 * Returns an integer from a three part version number, eg '4.12.3' -> 4012003
	 *
	 * @param    string $verNumberStr number on format x.x.x
	 * @return   integer   Integer version of version number (where each part can count to 999)
	 */
	public static function intFromVer($verNumberStr) {
		$verParts = explode('.', $verNumberStr);
		return intval((int)$verParts[0] . str_pad((int)$verParts[1], 3, '0', STR_PAD_LEFT) . str_pad((int)$verParts[2], 3, '0', STR_PAD_LEFT));
	}

	/**
	 * Explodes a string and trims all values for whitespace in the ends.
	 * If $onlyNonEmptyValues is set, then all blank ('') values are removed.
	 * Usage: 256
	 *
	 * @param    string         Delimiter string to explode with
	 * @param    string         The string to explode
	 * @param    boolean        If set, all empty values will be removed in output
	 * @param    integer        If positive, the result will contain a maximum of
	 *                          $limit elements, if negative, all components except
	 *                          the last -$limit are returned, if zero (default),
	 *                          the result is not limited at all. Attention though
	 *                          that the use of this parameter can slow down this
	 *                          function.
	 * @return    array        Exploded values
	 */
	public static function trimExplode($delim, $string, $removeEmptyValues = FALSE, $limit = 0) {
		$explodedValues = explode($delim, $string);

		$result = array_map('trim', $explodedValues);

		if ($removeEmptyValues) {
			$temp = array();
			foreach ($result as $value) {
				if ($value !== '') {
					$temp[] = $value;
				}
			}
			$result = $temp;
		}

		if ($limit != 0) {
			if ($limit < 0) {
				$result = array_slice($result, 0, $limit);
			} elseif (count($result) > $limit) {
				$lastElements = array_slice($result, $limit - 1);
				$result = array_slice($result, 0, $limit - 1);
				$result[] = implode($delim, $lastElements);
			}
		}

		return $result;
	}

	/**
	 * Print a debug of an array
	 *
	 * @param array $arrayIn
	 * @return string
	 */
	public static function viewArray($arrayIn) {
		if (is_array($arrayIn)) {
			$result = '<table class="debug" border="1" cellpadding="0" cellspacing="0" bgcolor="white" width="100%">';
			if (count($arrayIn) == 0) {
				$result .= '<tr><td><strong>EMPTY!</strong></td></tr>';
			} else {
				foreach ($arrayIn as $key => $val) {
					$result .= '<tr><td>' . htmlspecialchars((string)$key) . '</td><td class="debugvar">';
					if (is_array($val)) {
						$result .= self::viewArray($val);
					} elseif (is_object($val)) {
						$string = get_class($val);
						if (method_exists($val, '__toString')) {
							$string .= ': ' . (string)$val;
						}
						$result .= nl2br(htmlspecialchars($string)) . '<br />';
					} else {
						if (gettype($val) == 'object') {
							$string = 'Unknown object';
						} else {
							$string = (string)$val;
						}
						$result .= nl2br(htmlspecialchars($string)) . '<br />';
					}
					$result .= '</td></tr>';
				}
			}
			$result .= '</table>';
		} else {
			$result = '<table class="debug" border="0" cellpadding="0" cellspacing="0" bgcolor="white">';
			$result .= '<tr><td class="debugvar">' . nl2br(htmlspecialchars((string)$arrayIn)) . '</td></tr></table>';
		}
		return $result;
	}

}