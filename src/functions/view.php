<?php

/**
 * @param null $template
 * @param array $data
 *
 * @return le0daniel\System\View\View|string
 */
function view($template = null,$data = []){

	$view = resolve(\le0daniel\System\View\View::class);

	if(is_string($template)){
		return $view->render($template,$data);
	}

	return $view;
}