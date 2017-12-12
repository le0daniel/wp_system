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
use le0daniel\System\View\View;
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
	 * @param App $app
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

		/* Add Class Controllers */
		if( $this->app->config('map_controllers_to_classes',false) ){
			add_filter("template_include",[$this,'routeByFilename'],99);
		}


	}

	/**
	 * @param string $filename
	 *
	 * @throws \Exception
	 */
	public function routeByFilename(string $filename){

		/* Include File, should have a declared class */
		require_once $filename;

		$classes = (array) get_declared_classes();
		$abstract = end( $classes );

		/* Create the class */
		$object = $this->app->make($abstract);

		/* Check if render method exists */
		if( ! method_exists($object,'render') ){
			throw new \Exception('Render method on class ('. get_class($object) .') not found!');
		}

		list($template,$data) = $this->app->call([$object,'render']);

		/** @var View $view */
		$view = $this->app->get('view');
		$view->show($template,$data,true);
		die();
	}
}