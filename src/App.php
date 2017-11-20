<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 06.11.17
 * Time: 16:49
 */

namespace le0daniel\System;


use Carbon\Carbon;
use Illuminate\Container\Container;
use le0daniel\System\Contracts\ShortCode;
use le0daniel\System\Helpers\Path;
use le0daniel\System\WordPress\Context;
use le0daniel\System\WordPress\MetaField;
use le0daniel\System\WordPress\Page;
use le0daniel\System\WordPress\Post;
use le0daniel\System\WordPress\Site;
use le0daniel\System\WordPress\User;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use le0daniel\System\Contracts\Kernel;
use le0daniel\System\Http\Kernel as HttpKernel;
use le0daniel\System\Console\Kernel as ConsoleKernel;
use le0daniel\System\View\View;

/**
 * Class App
 * @package System
 *
 * It uses the the Laravel Container as IoC
 */
class App {

	/**
	 * @var string
	 */
	private static $name = 'le0daniel\\System';

	/**
	 * @var string
	 */
	private static $version = '1.0.0';

	/**
	 * @var App
	 */
	private static $instance;

	/**
	 * @var float
	 */
	private static $boot_time;

	/**
	 * @var string
	 */
	public static $root_dir;

	/**
	 * IoC Container (L5)
	 *
	 * @var Container
	 */
	protected $container;

	/**
	 * App constructor.
	 */
	private function __construct() {

		/* Set Boot time */
		self::$boot_time = microtime(true);

		/* Set Root Dir */
		if( ! isset(self::$root_dir) ){
			self::$root_dir = (isset($GLOBALS['root_dir']))? realpath($GLOBALS['root_dir']) : realpath(__DIR__.'/..');
		}

		/* Include the container */
		$this->container = require_once __DIR__ .'/bootstrap/ioc.php';

		/* Bind Kernel */
		$this->bindKernel();

		/* Register and boot! */
		$this->register();
		$this->boot();
	}

	/**
	 * @param string|null $root_dir
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public static function init(string $root_dir = null):bool{

		if( function_exists('app')){
			return false;
		}

		/* Set Dir */
		if( empty($root_dir) ){
			throw new \Exception('Root dir required to boot!');
		}

		require __DIR__.'/functions/paths.php';
		require __DIR__.'/functions/app.php';
		require __DIR__.'/functions/resolve.php';
		require __DIR__.'/functions/view.php';

		/**
		 * Set Statics
		 */
		self::$root_dir = realpath($root_dir);
		Path::$root_dir = self::$root_dir;

		/* Check dirs */
		Path::checkRequiredDirs();

		return true;
	}

	/**
	 * Singlton Pattern
	 *
	 * @return App
	 */
	public static function getInstance():App{
		if(!isset(self::$instance)){
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 *
	 */
	public static function checkRequiredDirs(){

		$required = [
			'cache',
		];

		foreach ($required as $dir){
			if(!file_exists(self::$root_dir.'/'.$dir)){
				mkdir(self::$root_dir.'/'.$dir,0777,true);
				file_put_contents(self::$root_dir.'/'.$dir.'/.htaccess','Deny from all');
			}
		}

	}

	/**
	 * Bind The Kernel Interface to an Kernel
	 */
	protected function bindKernel(){
		if(php_sapi_name() === 'cli'){
			$this->container->bind(Kernel::class,ConsoleKernel::class);
		}
		else{
			$this->container->bind(Kernel::class,HttpKernel::class);
		}
	}

	/**
	 * Register all the Bindings
	 */
	protected function register(){

		/* Register the View as a Singleton */
		$this->container->singleton(View::class);

		/* Monolog Logger */
		$this->container->singleton( Logger::class, function(Container $container):Logger{
			$log = new Logger(self::$name);
			$log->pushHandler(
				$container->make(StreamHandler::class,['stream'=>self::$root_dir.'/storage/log/' . Carbon::now()->toDateString() . '.log'])
			);
			return $log;
		});

		/* Setup the Stream Logger */
		$this->container->resolving(StreamHandler::class, function (StreamHandler $logger) {
			$logger->setLevel(Logger::DEBUG);
			$logger->setFormatter(new LineFormatter(null, null, false, true));
		});

	}

	/**
	 * Register Aliases
	 */
	protected function registerAliases(){

		/* Aliases */
		$this->container->alias(Context::class,     'wp.context');
		$this->container->alias(MetaField::class,   'wp.metafield');
		$this->container->alias(Page::class,        'wp.page');
		$this->container->alias(Post::class,        'wp.post');
		$this->container->alias(ShortCode::class,   'wp.shortcode');
		$this->container->alias(Site::class,        'wp.site');
		$this->container->alias(User::class,        'wp.user');
	}

	/**
	 * Boot
	 */
	protected function boot(){
		/* Boot */

		/* Run the kernel */
		$this->container->call('le0daniel\\System\\Contracts\\Kernel@boot');
	}

	/**
	 * Get the container instance!
	 *
	 * @return Container
	 */
	public function getContainer():Container{
		return $this->container;
	}

	/**
	 * Returns the current uptime of the App
	 *
	 * @return float
	 */
	public function getUpTime():float {
		return (float) microtime(true) - self::$boot_time;
	}

	/**
	 * Returns a logger class
	 *
	 * @return Logger
	 */
	public function log():Logger{
		return $this->container->get(Logger::class);
	}

	/**
	 * Run the App
	 */
	public function run(){
		return $this->container->call('le0daniel\\System\\Contracts\\Kernel@run');
	}

	/**
	 * Call on the container
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {

		/* Call the container if needed */
		if(method_exists($this->container,$name)){
			return call_user_func_array([ $this->container , $name ],$arguments);
		}

		/* Throw an error */
		throw new \BadMethodCallException(sprintf('Method %s not found',$name));
	}

}