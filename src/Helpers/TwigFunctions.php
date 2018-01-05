<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 13.11.17
 * Time: 21:56
 */

namespace le0daniel\System\Helpers;


use le0daniel\System\WordPress\MetaField;
use Psr\SimpleCache\CacheInterface;

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
	 * @param string $callable
	 * @param array ...$params
	 *
	 * @return mixed
	 */
	public static function captureAndCacheCallableOutput(string $callable,...$params){
		/* generate key in namespace callable */
		$key = 'callable_'. md5( $callable ); //.'_'.md5(serialize($params));

		/** @var CacheInterface $cache */
		$cache = resolve(CacheInterface::class);

		/* Get from cache */
		if($cache->has($key)){
			return $cache->get($key);
		}

		/* Resolve */
		$output = self::captureCallableOutput($callable,...$params);

		/* Cache */
		$cache->set($key,$output);

		/* return */
		return $output;
	}

	/**
	 * @param string $name
	 *
	 * @return false|string
	 */
	public static function getWpNav(string $name){
		return wp_nav_menu([
			'echo'=>false,
		]);
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
		return  resolve('wp.metafield',['key'=>$key,'id'=>$id]);
		//new MetaField($key,$id);
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