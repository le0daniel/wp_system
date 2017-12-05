<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 06.11.17
 * Time: 16:46
 */

namespace le0daniel\System\Console;


use Illuminate\Container\Container;
use le0daniel\System\Console\Commands\ClearCacheVC;
use le0daniel\System\Console\Commands\MakePostType;
use le0daniel\System\Console\Commands\MakeShortCut;
use le0daniel\System\Console\Commands\TakeSiteOffline;
use le0daniel\System\Console\Commands\TakeSiteOnline;
use le0daniel\System\Contracts\Kernel as KernelContract;
use Symfony\Component\Console\Application;

class Kernel implements KernelContract {

	/**
	 * Array containing Command Classes!
	 *
	 * @var array
	 */
	private $commands = [
		MakeShortCut::class,
		ClearCacheVC::class,
		MakePostType::class,

		TakeSiteOffline::class,
		TakeSiteOnline::class,
	];

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * Kernel constructor.
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Register Bindings
	 */
	public function boot() {
		$this->container->singleton('console',Application::class);
	}

	/**
	 * Run
	 */
	public function run() {
		/** @var Application $app */
		$app = $this->container->make('console');

		/* Add commands */
		array_walk($this->commands,function(string $abstract)use($app){
			$app->add( $this->container->make($abstract) );
		});

		$app->run();
	}
}