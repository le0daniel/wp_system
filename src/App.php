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
use le0daniel\System\Contracts\AddLogicToWordpress;
use le0daniel\System\Contracts\ServiceProvider;
use le0daniel\System\Contracts\ShortCode;
use le0daniel\System\Helpers\Path;
use le0daniel\System\ServiceProviders\Log;
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
class App extends Container {

	/**
	 * @var string
	 */
	private static $name = 'le0daniel\\System';

	/**
	 * @var string
	 */
	private static $version = '1.0.0';

	/**
	 * @var string
	 */
	public static $config_dir = __DIR__.'/../config';

	/**
	 * @var float
	 */
	private static $boot_time;

	/**
	 * @var string
	 */
	public static $root_dir;

	/**
	 * @var array
	 */
	protected $config;

	/**
	 * @var array
	 */
	protected $service_providers = [];

	/**
	 * App constructor.
	 */
	private function __construct() {

		/* Set Root Dir */
		if( ! isset(self::$root_dir) ){
			self::$root_dir = (isset($GLOBALS['root_dir']))? realpath($GLOBALS['root_dir']) : realpath(__DIR__.'/..');
		}

		/* Set Instance */
		self::setInstance($this);

		/* Load Config */
		$this->loadConfig();

		/* Bind Kernel */
		$this->createImportantBindings();


		/* Register and boot! */
		$this->register();
		$this->boot();
	}

	/**
	 *
	 */
	protected function loadConfig(){

		/* Include Config file */
		$this->config = require self::$config_dir.'/app.php';

		/* Set Service Providers */
		$this->service_providers = $this->config['providers'];
	}

	/**
	 * @param string $key
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public function config(string $key,$default= null){

		if( ! array_has($this->config,$key) ){
			return $default;
		}

		return array_get($this->config,$key);
	}

	/**
	 * @param string|null $root_dir
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public static function init(string $root_dir = null):bool{

		/* Check if App function already exposed! */
		if( function_exists('app')){
			return false;
		}

		/* Set Boot time */
		self::$boot_time = microtime(true);

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
		require __DIR__.'/functions/config.php';

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

		if( self::isDefinedOrDefine('DISABLE_SECURITY_HEADERS',false) ){
			return;
		}

		/* No iframe */
		header('X-Frame-Options: SAMEORIGIN');

		/* Currently disabled */
		//header('Strict-Transport-Security: ');
		//header('Content-Security-Policy: script-src \'self\' ') //https://scotthelme.co.uk/content-security-policy-an-introduction/;
		//header('X-XSS-Protection: 1; mode=block');
		//header('X-Content-Type-Options: X-Content-Type-Options: nosniff');
		//header('Referrer-Policy: no-referrer');
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
	protected function createImportantBindings(){

		/* Bind container itself */
		$this->instance(Container::class, $this);
		$this->bind(App::class,Container::class);
		$this->bind(\Illuminate\Contracts\Container\Container::class,Container::class);

		/**
		 * Initial Bindings
		 */
		$this->singleton( HttpKernel::class );
		$this->singleton( ConsoleKernel::class);
		$this->singleton(View::class);

		if(php_sapi_name() === 'cli'){
			$this->bind(Kernel::class,ConsoleKernel::class);
		}
		else{
			$this->bind(Kernel::class,HttpKernel::class);
		}
	}

	/**
	 * Register all the Bindings
	 */
	protected function register(){

		/* Register all needed system Constants */
		$this->registerConstants();

		/* Register all Aliases */
		$this->registerAliases();

		/* Register Service Providers */
		$this->service_providers = array_map([$this,'initAndRegisterServiceProvider'],$this->service_providers);
	}

	/**
	 * @param $abstract
	 *
	 * @return mixed
	 */
	protected function initAndRegisterServiceProvider($abstract){
		$provider = $this->make($abstract);
		$provider->register();
		return $provider;
	}

	/**
	 * @param ServiceProvider $provider
	 */
	protected function bootServiceProvider(ServiceProvider $provider){
		$provider->boot();
	}

	/**
	 * Register Constants
	 */
	protected function registerConstants(){
		/* Register Root dir */
		$this->instance('system.root_dir',self::$root_dir);
	}

	/**
	 * Register Aliases
	 */
	protected function registerAliases(){

		/* WP Aliases */
		$this->alias(Context::class,             'wp.context');
		$this->alias(MetaField::class,           'wp.metafield');
		$this->alias(Page::class,                'wp.page');
		$this->alias(Post::class,                'wp.post');
		$this->alias(ShortCode::class,           'wp.shortcode');
		$this->alias(Site::class,                'wp.site');
		$this->alias(User::class,                'wp.user');
		$this->alias(AddLogicToWordpress::class, 'wp.extend');

		/* Tools */
		$this->alias(View::class,                'view');

		/* System Aliases */
		$this->alias(Kernel::class,              'system.kernel');
		$this->alias(Logger::class,              'system.log');
		$this->alias('system.root_dir',          'system.root');

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
		array_walk($this->service_providers,[$this,'bootServiceProvider']);

		/* Boot */
		$this->call('system.kernel@boot');
	}

	/**
	 * Get the container instance!
	 *
	 * @return Container
	 */
	public function getContainer():Container{
		return $this;
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
		return $this->get('system.log');
	}

	/**
	 * Run the App
	 */
	public function run(){
		return $this->call('system.kernel@run');
	}

	/**
	 * @return bool
	 */
	public function isRunningInHttpMode():bool{
		return ( php_sapi_name() !== 'cli' );
	}

	/**
	 * Check if called in command line
	 *
	 * @return bool
	 */
	public function isRunningInCliMode():bool{
		return ( php_sapi_name() === 'cli' );
	}

	/**
	 * Check if running in WP Cli Mode
	 *
	 * @return bool
	 */
	public function isRunningInWpCliMode():bool{
		return ( $this->isRunningInCliMode() && self::isDefinedOrDefine('WP_CLI',false) );
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
		/* Throw an error */
		throw new \BadMethodCallException(sprintf('Method %s not found',$name));
	}

}