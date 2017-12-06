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
		$this->container = $this->createContainer();

		/* Bind Kernel */
		$this->bindKernel();

		/* Register and boot! */
		$this->register();
		$this->boot();
	}

	/**
	 * Creates the Laravel 5 Container
	 *
	 * @return Container
	 */
	private function createContainer():Container{
		$container = new Container();

		/* Bind container itself */
		$container->instance(Container::class, $container);
		$container->bind(\Illuminate\Contracts\Container\Container::class,Container::class);

		/**
		 * Initial Bindings
		 */
		$container->singleton( HttpKernel::class );
		$container->singleton( ConsoleKernel::class);

		/**
		 * Return the Instance of the container
		 */
		return $container;
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

		/* Make Sure Expose PHP is disabled */
		if( self::isDefinedOrDefine('WP_DEBUG',false) ){
			header('X-Debug-Mode: true');
		}
		else{
			self::removeHeaders();
			self::setSecurityHeaders();
		}

		require __DIR__.'/functions/app.php';
		require __DIR__.'/functions/resolve.php';
		require __DIR__.'/functions/view.php';

		/**
		 * Set Statics
		 */
		self::$root_dir = realpath($root_dir);
		Path::$root_dir = self::$root_dir;

		/**
		 * Check dirs by default! Can be disabled in production!
		 */
		if( self::isDefinedOrDefine('DIR_CHECK',true) ){
			Path::checkRequiredDirs();
		}

		return true;
	}

	/**
	 * Defines or returns the value of a CONSTANT
	 *
	 * @param string $name
	 * @param bool $default
	 *
	 * @return mixed
	 */
	public static function isDefinedOrDefine(string $name,bool $default = false){

		/* Check if defined */
		if( ! defined($name) ){
			define($name,$default);
		}

		return constant($name);
	}

	/**
	 * Removes all headers which could expose information
	 */
	public static function removeHeaders(){

		/* Do not expose too much */
		header_remove('X-Powered-By');
		header_remove('Server');

	}

	/**
	 * Set basic security headers
	 */
	public static function setSecurityHeaders(){

		if( php_sapi_name() === 'cli' ){
			return;
		}

		/* No iframe */
		header('X-Frame-Options','SAMEORIGIN');

		/* Currently disabled */
		//header('Strict-Transport-Security','');
		//header('Content-Security-Policy','script-src \'self\'') //https://scotthelme.co.uk/content-security-policy-an-introduction/;
		//header('X-XSS-Protection','1; mode=block');
		//header('X-Content-Type-Options','X-Content-Type-Options: nosniff');
		//header('Referrer-Policy','no-referrer');
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

		/* Register all Aliases */
		$this->registerAliases();
	}

	/**
	 * Register Aliases
	 */
	protected function registerAliases(){

		/* WP Aliases */
		$this->container->alias(Context::class,     'wp.context');
		$this->container->alias(MetaField::class,   'wp.metafield');
		$this->container->alias(Page::class,        'wp.page');
		$this->container->alias(Post::class,        'wp.post');
		$this->container->alias(ShortCode::class,   'wp.shortcode');
		$this->container->alias(Site::class,        'wp.site');
		$this->container->alias(User::class,        'wp.user');

		/* Tools */
		$this->container->alias(View::class,        'view');

		/* System Aliases */
		$this->container->alias(Kernel::class,      'system.kernel');
		$this->container->alias(Logger::class,      'system.log');

	}

	/**
	 * Boot
	 */
	protected function boot(){
		/**
		 * Handle global stuff below, all the
		 * rest should be handled by the dedicated
		 * Kernel (Http/Console)
		 */


		/* Boot */
		$this->container->call('system.kernel@boot');
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
		return $this->container->get('system.log');
	}

	/**
	 * Run the App
	 */
	public function run(){
		return $this->container->call('system.kernel@run');
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