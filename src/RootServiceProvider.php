<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 10.12.17
 * Time: 09:43
 */

namespace le0daniel\System;


use le0daniel\System\Contracts\ServiceProvider;

abstract class RootServiceProvider implements ServiceProvider {

	/**
	 * @var App
	 */
	protected $app;

	/**
	 * RootServiceProvider constructor.
	 *
	 * @param App $app
	 */
	public function __construct(App $app) {
		$this->app = $app;
	}

}