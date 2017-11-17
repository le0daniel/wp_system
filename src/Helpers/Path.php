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
	 * @var string
	 */
	public static $root_dir = '';

	/**
	 * @var array
	 */
	public static $required = [
		'cache',
		'cache/rendered',
		'cache/twig',
		'storage',
		'storage/log'
	];

	/**
	 * Check and create required dirs
	 */
	public static function checkRequiredDirs(){
		foreach (self::$required as $dir){
			if(!file_exists(self::$root_dir.'/'.$dir)){
				mkdir(self::$root_dir.'/'.$dir,0777,true);
				file_put_contents(self::$root_dir.'/'.$dir.'/.htaccess','Deny from all');
			}
		}
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public static function cachePath($path=''):string{
		return rtrim(self::$root_dir.'/cache/'.$path,'/');
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public static function themesPath($path=''):string{
		return rtrim(self::$root_dir.'/web/app/themes/'.$path,'/');
	}

	/**
	 * Returns all available themes
	 *
	 * @return array
	 */
	public static function getAvailableThemes():array{

		$themes = [];

		$dirs = new \DirectoryIterator(self::themesPath());
		foreach ($dirs as $dir) {
			if ($dir->isDir() && ! $dir->isDot()) {
				$themes[] = $dir->getFilename();
			}
		}

		return $themes;
	}

	/**
	 * @param string $path
	 *
	 * @return string
	 */
	public static function storagePath($path=''):string{
		return rtrim( self::$root_dir.'/storage/'.$path ,'/');
	}

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