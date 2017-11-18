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
use le0daniel\System\WordPress\Context;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

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

	public function boot() {

		/* Register the context as singleton */
		$this->container->singleton(Context::class);
		$this->container->alias(Context::class,'wp.context');

		/* Create The error handler */
		$whoops = $this->container->make(Run::class);
		$this->container->instance('error.handler',$whoops);

	}
	public function run() {

		/** @var Run $whoops */
		$whoops = $this->container->get('error.handler');
		$whoops->pushHandler($this->container->make(PrettyPageHandler::class));
		$whoops->register();
	}
}