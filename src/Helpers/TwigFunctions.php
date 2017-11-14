<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 13.11.17
 * Time: 21:56
 */

namespace le0daniel\System\Helpers;


class TwigFunctions {

	/**
	 * @param $callable
	 * @param array[] ...$params
	 *
	 * @return string
	 */
	public static function captureCallableOutput($callable, array ...$params){
		ob_start();

		call_user_func_array($callable,$params);
		$content = ob_get_contents();

		ob_end_clean();

		return $content;
	}

}