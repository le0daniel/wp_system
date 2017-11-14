<?php

/**
 * @param null $template
 * @param array $data
 *
 * @return le0daniel\System\View\View|bool
 */
function view($template = null,$data = []){

	$view = resolve(\le0daniel\System\View\View::class);

	if(is_string($template)){
		$view->show($template,$data);
		return true;
	}

	return $view;
}