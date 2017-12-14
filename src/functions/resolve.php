<?php

/**
 * Resolve or get the container!
 *
 * @param null $abstract
 * @param array ...$params
 *
 * @return mixed
 */

if( ! function_exists('resolve') ) {
	/**
	 * @param string $abstract
	 * @param array $params
	 *
	 * @return mixed
	 */
	function resolve(string $abstract, array $params = [] ) {
		return app()->make( $abstract, $params );
	}
}