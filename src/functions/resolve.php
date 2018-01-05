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

if( ! function_exists('cache') ) {

	/**
	 * @param null $key
	 * @param null $value
	 *
	 * @return \Psr\SimpleCache\CacheInterface
	 */
	function cache($key = null, $value = null ) {

		/** @var \Psr\SimpleCache\CacheInterface $manager */
		$manager = resolve(\Psr\SimpleCache\CacheInterface::class);

		return $manager;
	}
}