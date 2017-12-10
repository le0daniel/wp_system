<?php

/**
 * Resolve or get the container!
 *
 * @param null $abstract
 * @param array ...$params
 *
 * @return mixed
 */
function resolve($abstract = null,array $params=[]){

	$app = \le0daniel\System\App::getInstance();

	/* Resolve */
	if(is_string($abstract)){
		return $app->make($abstract,$params);
	}

	/* Return Container Instance */
	return $app;
}