<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 29.12.17
 * Time: 08:37
 */

namespace le0daniel\System\ServiceProviders;

use le0daniel\System\Helpers\Path;
use le0daniel\System\RootServiceProvider;
use phpFastCache\Helper\Psr16Adapter;
use Psr\SimpleCache\CacheInterface;

class Cache extends RootServiceProvider {

	/**
	 * @var string
	 */
	protected $driver = 'files';

	/**
	 * @return void
	 */
	public function boot() {

	}

	/**
	 * @return void
	 */
	public function register() {

		$adapter = new Psr16Adapter($this->driver,['path'=>Path::cachePath('fastcache')]);

		$this->app->instance(CacheInterface::class,$adapter);

	}
}