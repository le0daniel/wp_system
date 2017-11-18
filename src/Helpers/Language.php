<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 18.11.17
 * Time: 16:03
 */

namespace le0daniel\System\Helpers;


class Language {

	/**
	 * Holds the Translation context
	 *
	 * @var string
	 */
	public static $translation_context = '';

	/**
	 * @param string $key
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function translate(string $key):string{

		if(empty(self::$translation_context)){
			throw new \Exception('Translation context not set!');
		}

		return __($key,self::$translation_context);
	}

}