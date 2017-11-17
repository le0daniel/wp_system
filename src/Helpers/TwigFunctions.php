<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 13.11.17
 * Time: 21:56
 */

namespace le0daniel\System\Helpers;


use le0daniel\System\WordPress\MetaField;

class TwigFunctions {

	/**
	 * @param $callable
	 * @param array[] ...$params
	 *
	 * @return string
	 */
	public static function captureCallableOutput($callable,...$params){
		ob_start();

		$result = call_user_func_array($callable,$params);
		$content = ob_get_contents();

		ob_end_clean();

		/* Return result */
		if(empty($content)){
			return $result;
		}

		return $content;
	}

	/**
	 * @param string $key
	 * @param bool|int $id
	 *
	 * @return bool|mixed
	 */
	public static function getFieldValue(string $key,$id = false){
		return get_field($key,$id);
	}

	/**
	 * Gets a meta field but as object/array
	 *
	 * @param string $key
	 * @param bool $id
	 *
	 * @return MetaField
	 */
	public static function getField(string $key,$id = false){
		return new MetaField($key,$id);
	}

	/**
	 * @param string $key
	 * @param bool $id
	 *
	 * @return bool|mixed
	 */
	public static function hasField(string $key,$id = false){
		return (self::getFieldValue($key,$id));
	}

}