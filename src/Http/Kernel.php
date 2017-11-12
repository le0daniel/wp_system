<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 06.11.17
 * Time: 16:46
 */

namespace le0daniel\System\Http;


use Illuminate\Container\Container;
use le0daniel\System\Contracts\Kernel as KernelContract;

class Kernel implements KernelContract {

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * Kernel constructor.
	 *
	 * @param Container $container
	 */
	public function __construct( Container $container ) {
		$this->container = $container;
	}

	public function boot() {}
	public function run() {}
}