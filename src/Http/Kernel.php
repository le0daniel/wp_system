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
		$whoops = $this->registerErrorHandler();

		$this->container->instance('error.handler',$whoops);
	}

	protected function registerErrorHandler(){

		$whoops = $this->container->make(Run::class);
		if( WP_DEBUG ){

			$whoops->pushHandler($this->container->make(PrettyPageHandler::class));
			assert_options(ASSERT_ACTIVE, true);

		}
		else{
			$whoops->pushHandler(function(\Exception $e){
				/** @var Logger $logger */
				$logger = $this->container->get(Logger::class);
				$logger->emergency('Error: '.$e->getMessage(),$e->getTraceAsString());

				try{
					view('@pages/500.twig');
				}catch (\Exception $e){
					echo 'We encountered a Fatal Error!';
					die();
				}

			});
		}

		$whoops->register();
		return $whoops;
	}

	public function run() {



	}
}