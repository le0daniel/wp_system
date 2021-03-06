<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 06.11.17
 * Time: 16:46
 */

namespace le0daniel\System\Console;


use Illuminate\Container\Container;
use le0daniel\System\App;
use le0daniel\System\Console\Commands\ClearCacheInteractive;
use le0daniel\System\Console\Commands\ClearCacheVC;
use le0daniel\System\Console\Commands\GenereatePotFile;
use le0daniel\System\Console\Commands\MakePostType;
use le0daniel\System\Console\Commands\MakeShortCut;
use le0daniel\System\Console\Commands\TakeSiteOffline;
use le0daniel\System\Console\Commands\TakeSiteOnline;
use le0daniel\System\Console\Commands\TestDatabase;
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
		ClearCacheInteractive::class,
		MakePostType::class,
		TakeSiteOffline::class,
		TakeSiteOnline::class,
		TestDatabase::class,
		GenereatePotFile::class,
	];

	/**
	 * @var App
	 */
	private $app;

	/**
	 * Kernel constructor.
	 *
	 * @param App $app
	 */
	public function __construct(App $app) {
		$this->app = $app;
	}

	/**
	 * Register Bindings
	 */
	public function boot() {
		$this->app->singleton('system.console',Application::class);
	}

	/**
	 * Run
	 */
	public function run() {

		/* Don't run if on WP CLI */
		if( $this->app->isRunningInWpCliMode() ){
			$this->runWpCli();
			return;
		}

		/** @var Application $console */
		$console = $this->app->make('system.console');
		$commands = $this->commands;

		/* Add custom commands */
		if( ! empty( $this->app->config('commands',[]) ) ){
			$commands = array_merge($this->app->config('commands',[]),$commands);
		}

		array_walk($commands,function(string $abstract)use($console){
			$console->add( $this->app->make($abstract) );
		});

		$console->run();
	}

	/**
	 * Run when in WP mode
	 */
	protected function runWpCli(){
		/* Configure WP-CLI */

		return;
	}
}