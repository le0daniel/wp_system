<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 06.11.17
 * Time: 16:46
 */

namespace le0daniel\System\Http;


use Illuminate\Container\Container;
use le0daniel\System\App;
use le0daniel\System\Contracts\AddLogicToWordpress;
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
	protected $app;

	/**
	 * Kernel constructor.
	 *
	 * @param Container $app
	 */
	public function __construct( App $app ) {
		$this->app = $app;
	}

	/**
	 * Boot
	 */
	public function boot() {

		/* Register the context as singleton */
		$this->app->singleton(Context::class);

		/* Create The error handler */
		$this->registerErrorHandler();
		
	}

	/**
	 * Register Error Handler for Debugging
	 */
	protected function registerErrorHandler(){


		if( WP_DEBUG ){
			$whoops = $this->app->make(Run::class);
			$whoops->pushHandler($this->app->make(PrettyPageHandler::class));
			$whoops->register();
			$this->app->instance('error.handler',$whoops);
			assert_options(ASSERT_ACTIVE, true);
		}

	}

	/**
	 * Run the Application
	 */
	public function run() {

		/* Run extender if bound! */
		if( $this->app->bound(AddLogicToWordpress::class) ){
			$this->app->make('wp.extend')->boot();
		}


	}
}