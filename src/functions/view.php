<?php

/**
 * @param null $template
 * @param array $data
 *
 * @return le0daniel\System\View\View|bool
 */

if( ! function_exists('view') ) {

	/**
	 * @param null $template
	 * @param array $data
	 *
	 * @return \le0daniel\System\View\View|mixed
	 */
	function view( $template = null, $data = [] ) {

		/** @var \le0daniel\System\View\View $view */
		$view = resolve( 'view' );

		if ( is_string( $template ) ) {
			/* Display and terminate! */
			$view->show( $template, $data, true);

			/* Should not be called */
			return true;
		}

		return $view;
	}
}