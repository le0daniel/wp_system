<?php

if( !function_exists('webroot-path') ){

	/**
	 * Path relative to the web root
	 * @param string $path
	 *
	 * @return string
	 */
	function webroot_path(string $path = ''):string{
		return \le0daniel\System\Helpers\Path::webroot().$path;
	}
}

if( ! function_exists('activetheme_path') ){

	/**
	 * Returns path relative to the active theme path configured
	 * in config/application.php
	 *
	 * @param string $path
	 *
	 * @return string
	 */
	function activetheme_path(string $path):string{
		return \le0daniel\System\Helpers\Path::activeThemePath($path);
	}

}
