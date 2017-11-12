<?php

/**
 * Resolve or get the container!
 *
 * @param null $abstract
 * @param array ...$params
 *
 * @return mixed
 */
function resolve($abstract = null,...$params){

	$container = app()->getContainer();

	/* Resolve */
	if(is_string($abstract)){
		return $container->make($abstract,$params);
	}

	/* Return Container Instance */
	return $container;
}