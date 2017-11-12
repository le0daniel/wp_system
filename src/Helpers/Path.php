<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 08.11.17
 * Time: 15:28
 */

namespace le0daniel\System\Helpers;


class Path {

	/**
	 * @param array ...$strings
	 *
	 * @return string
	 */
	public static function combine(...$strings):string {

		$trimmed = [];

		foreach($strings as $index => $string){

			if($index > 0 ){
				$trimmed[] = trim( rtrim($string,'/') , '/');
			}
			else{
				$trimmed[] = rtrim($string,'/');
			}

		}

		/* Return */
		return implode('/',$trimmed);
	}

	/**
	 * Check if is absolute path, only works on Linux filesystems
	 *
	 * @param string $path
	 *
	 * @return bool
	 */
	public static function isAbsolute(string $path):bool{
		return ( substr($path,0,1) === '/' );
	}

	/**
	 * @param int $offset
	 *
	 * @return string
	 */
	public static function getCallingScriptFile(int $offset = 0 ):string {
		$callers = [];
		$backtrace = debug_backtrace();
		foreach ( $backtrace as $trace ) {
			if ( array_key_exists('file', $trace) && $trace['file'] != __FILE__ ) {
				$callers[] = $trace['file'];
			}
		}
		$callers = array_unique($callers);
		$callers = array_values($callers);
		return (string) $callers[$offset];
	}

	/**
	 * @param int $offset
	 *
	 * @return string
	 */
	public static function getCallingScriptDir(int $offset = 0 ): string {
		$caller = self::getCallingScriptFile($offset);
		$pathinfo = pathinfo($caller);
		$dir = $pathinfo['dirname'];
		return (string) $dir;
	}

}