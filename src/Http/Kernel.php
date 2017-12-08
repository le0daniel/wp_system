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
use Monolog\Logger;
use Whoops\Handler\Handler;
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

	/**
	 * Boot
	 */
	public function boot() {

		/* Register the context as singleton */
		$this->container->singleton(Context::class);

		/* Create The error handler */
		$this->registerErrorHandler();
		
	}

	/**
	 * Register Error Handler for Debugging
	 */
	protected function registerErrorHandler(){


		if( WP_DEBUG ){
			$whoops = $this->container->make(Run::class);
			$whoops->pushHandler($this->container->make(PrettyPageHandler::class));
			$whoops->register();
			$this->container->instance('error.handler',$whoops);
			assert_options(ASSERT_ACTIVE, true);
		}

	}

	/**
	 * Run the Application
	 */
	public function run() {

		$this->container->make('wp.extend')->boot();

	}
}