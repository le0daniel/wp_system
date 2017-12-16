<?php

if( ! function_exists('config') ){

	/**
	 * Returns config option
	 *
	 * @param string $key
	 * @param $default
	 *
	 * @return mixed|null
	 */
	function config(string $key, $default = null){
		return app()->config($key,$default);
	}
}