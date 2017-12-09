<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 09.12.17
 * Time: 10:11
 */

namespace le0daniel\System\Contracts;


use Illuminate\Container\Container;
use le0daniel\System\App;

interface ServiceProvider {

	/**
	 * @param App $app
	 *
	 * @return mixed
	 */
	public function boot(App $app);

	/**
	 * @param Container $container
	 *
	 * @return void
	 */
	public function register(Container $container);

}