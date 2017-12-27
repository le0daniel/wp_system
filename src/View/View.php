<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 07.11.17
 * Time: 14:47
 */

namespace le0daniel\System\View;


use Illuminate\Container\Container;
use le0daniel\System\App;
use le0daniel\System\Contracts\CastArray;
use le0daniel\System\Helpers\Path;
use le0daniel\System\WordPress\Context;
use Monolog\Logger;

class View {

	protected $container;

	/**
	 * @var array
	 */
	private $twig_config = [
		'debug'=>false,
		'charset'=>'utf-8',
		'strict_variables'=>false,
		'autoescape'=>'html',
	];

	/**
	 * @var \Twig_Environment
	 */
	private $twig;

	/**
	 * If it should be cached in plain HTML
	 * This should not be used if you use lazy loading
	 * of context!
	 *
	 * @var bool
	 */
	private $plain_cache = false;

	/**
	 * To add params, associate with array
	 * string $name => [ callable $callable , array $options ]
	 *
	 * @var array
	 */
	protected $filters = [
		'theme_path'    =>'\\le0daniel\\System\\Helpers\\TwigFilters::themePath',
		'static_path'   =>'\\le0daniel\\System\\Helpers\\TwigFilters::staticPath',
		'mix'           =>'\\le0daniel\\System\\Helpers\\TwigFilters::mix',

		/* Translation filters */
		't'             =>'\\le0daniel\\System\\Helpers\\TwigFilters::translate',
		'translate'     =>'\\le0daniel\\System\\Helpers\\TwigFilters::translate',

		/* Text Eclipse */
		'eclipse'=>'\\le0daniel\\System\\Helpers\\TwigFilters::eclipse',
	];

	/**
	 * To add params, associate with array
	 * string $name => [ callable $callable , array $options ]
	 *
	 * @var array
	 */
	protected $functions = [
		'function'      =>'\\le0daniel\\System\\Helpers\\TwigFunctions::captureCallableOutput',
		'field'         =>'\\le0daniel\\System\\Helpers\\TwigFunctions::getFieldValue',
		'wp_nav'        =>'\\le0daniel\\System\\Helpers\\TwigFunctions::getWpNav'
	];

	/**
	 * Array containing the Data
	 *
	 * @var array
	 */
	protected $data = [
		'debug'=>       WP_DEBUG,
		'hot_reload'=>  HOT_RELOAD,
	];

	/**
	 * @var string|CastArray
	 */
	protected $context = 'wp.context';
	protected $_context_locked = false;

	/**
	 * The root dir for the view composer
	 *
	 * @var string
	 */
	protected $root_dir;

	/**
	 * View constructor.
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Set Plain Cache!
	 *
	 * @param bool $bool
	 *
	 * @return $this
	 */
	public function setPlainCache(bool $bool){
		$this->plain_cache = $bool;
		return $this;
	}

	/**
	 * Builds and returns Twig
	 *
	 * @return \Twig_Environment
	 */
	protected function getTwig(){
		if(!isset($this->twig)){
			$this->buildTwig();
		}
		return $this->twig;
	}

	/**
	 * Build Twig
	 */
	protected function buildTwig(){

		if( ! isset($this->root_dir)){
			throw new \Exception('View root is missing!');
		}

		/* Load Twig */
		$loader = new \Twig_Loader_Filesystem($this->root_dir);

		/* Set Cache */
		$this->twig_config['cache']=$this->twigCachePath();
		if(WP_DEBUG === true){
			$this->twig_config['cache']=false;
		}

		/* Init Twig */
		$this->twig = new \Twig_Environment($loader,$this->twig_config);

		/* Register Filters */
		$this->registerTwigFiltersAndFunctions();
	}

	/**
	 * @return string
	 */
	public function twigCachePath():string {
		return Path::cachePath('twig');
	}

	/**
	 * @return string
	 */
	public function compiledCachePath():string{
		return Path::cachePath('rendered');
	}

	/**
	 * @param string $path
	 *
	 * @return $this
	 */
	public function setRootDir(string $path){
		$this->root_dir = $path;
		return $this;
	}

	/**
	 *
	 * @return void
	 */
	public function registerTwigFiltersAndFunctions(){

		array_walk($this->filters,[$this,'parseAndAddFunctionOrFilter'],'filter');
		array_walk($this->functions,[$this,'parseAndAddFunctionOrFilter'],'function');

	}

	/**
	 * @param $abstract
	 * @param string $name
	 * @param string $type
	 */
	protected function parseAndAddFunctionOrFilter($abstract,string $name,string $type){

		$callable = $abstract;
		$params = [];

		/* Parse Input if needed */
		if( is_array($abstract) && ! is_callable($abstract) ){
			list($callable,$params) = $abstract;
		}

		/* add to twig */
		$this->{'add'.ucfirst($type)}($callable,$name,$params);
	}

	/**
	 * @param callable $callable
	 * @param string $name
	 * @param array $params
	 */
	public function addFilter(callable $callable,string $name,$params = []){
		$this->getTwig()->addFilter(
			new \Twig_Filter($name,$callable,$params)
		);
	}

	/**
	 * @param callable $callable
	 * @param string $name
	 * @param array $params
	 */
	public function addFunction(callable $callable,string $name,$params = []){
		$this->getTwig()->addFunction(
			new \Twig_Function($name,$callable,$params)
		);
	}

	/**
	 * Share data with all views
	 *
	 * @param string $key
	 * @param $value
	 * @return View
	 */
	public function share(string $key,$value):View{
		$this->data[$key]=$this->getValue($value);

		return $this;
	}

	/**
	 * @param $context
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function addContext($context){

		if( $this->_context_locked ){
			throw new \Exception('Context can only be set once!');
		}

		$this->context         = $context;
		$this->_context_locked = true;

		return $this;
	}

	/**
	 * Evaluates Closure if given and returns result
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function getValue($value){
		if( $value instanceof \Closure){
			return $value();
		}
		return $value;
	}

	/**
	 * @param string $path
	 * @param string $alias
	 *
	 * @return $this
	 */
	public function addIncludePath(string $path,string $alias){

		/** @var \Twig_Loader_Filesystem $loader */
		$loader = $this->getTwig()->getLoader();
		$loader->addPath($path,$alias);

		return $this;
	}

	/**
	 * Renders a given Template
	 *
	 * @param string $filename
	 * @param array $data
	 * @param bool $with_context
	 * @param bool $force_plain_cache
	 * @param int $cache_duration
	 *
	 * @return string
	 */
	public function render(string $filename,array $data=[], bool $with_context = true, bool $force_plain_cache = false, int $cache_duration = -1):string {

		$start = microtime(true);
		$data_to_render = $this->mergeData($data);

		/* Add context! */
		if($with_context){
			$data_to_render = $this->addContextToData($data_to_render);
		}

		/* Add debug info */
		if( WP_DEBUG ){
			$data_to_render['debug']=[
				'template'=>$filename,
			];
		}

		/* Check if full HTML Cache is enabled! */
		if( ($this->plain_cache || $force_plain_cache) && ! $with_context ){

			$plain_cache_path = $this->getPlainCachePath($filename,$data_to_render);

			if( $this->cacheFileIsValid($plain_cache_path,$cache_duration)  ){
				return (string) file_get_contents($plain_cache_path);
			}

		}

		/* Render */
		$html = (string) $this->getTwig()->render(
			$filename,
			$data_to_render
		);

		/* Cache */
		if( isset($plain_cache_path) ){
			file_put_contents($plain_cache_path,$html);
		}

		/* Check duration */
		$duration = microtime(true) - $start;

		if( $duration > 2 ){
			app()->log()->warning('Render time for '.$filename.' over 2s!',['Server'=>$_SERVER]);
		}

		return $html;

	}

	/**
	 * @param string $filename
	 */
	protected function setDebugHeaders(string $filename = 'Not Set'){
		if(WP_DEBUG){
			header('X-Plain-Cache: '. ( ($this->plain_cache )?'true':'false' ) );
			header('X-Twig-Cache: '. ( ($this->twig_config['cache'])?$this->twig_config['cache']:'false' ) );
			header('X-Up-Time: '.app()->getUpTime());
			header('X-Twig-Template: '.$filename);
		}
	}

	/**
	 * @param string $template
	 * @param array $data
	 *
	 * @return string
	 */
	protected function getPlainCachePath(string $template,array $data):string {
		$plain_cache_name = md5($template.serialize($data)).'.plain.html';
		$cache_path = Path::cachePath('rendered/'.$plain_cache_name);
		return $cache_path;
	}

	/**
	 * @param string $filename
	 * @param int $max_age
	 *
	 * @return bool
	 */
	protected function cacheFileIsValid(string $filename,int $max_age = -1):bool{

		/* File does not exist */
		if( ! file_exists($filename) ){
			return false;
		}

		/* No max age defined */
		if( $max_age <= 0){
			return true;
		}

		/* Check file time */
		$touched = filemtime($filename);
		if( (time() - $touched) > ($max_age * 60) ){
			unlink($filename);
			return false;
		}

		return true;

	}

	/**
	 * Render and show a page!
	 *
	 * @param string $filename
	 * @param array $data
	 * @param bool $terminate
	 */
	public function show(string $filename,array $data=[],$terminate = true){

		/* Add Debug headers */
		$this->setDebugHeaders($filename);

		/* Render and Output */
		$html = $this->render($filename,$data);

		/* Expose duration before outputing anything */
		if( $this->shouldShowDurationHeader() ){
			header('X-Duration: '.app()->getUpTime());
		}

		/* Output */
		echo $html;

		/* Terminate */
		if($terminate){
			die();
		}
	}

	/**
	 * Check if should show the duration
	 * From App::init() -> Now
	 *
	 * @return bool
	 */
	protected function shouldShowDurationHeader():bool{

		/* Check if Expose Duration is set to true */
		if( defined('EXPOSE_DURATION') && EXPOSE_DURATION ){
			return true;
		}

		/* Check if the force duration header is set to true */
		$header_name = 'HTTP_FORCE_EXPOSE_DURATION';
		$unsafe_header = isset( $_SERVER[$header_name] )? $_SERVER[$header_name] : false;

		if($unsafe_header === 'true'){
			return true;
		}

		return false;
	}

	/**
	 * Merges the Data arrays and informs of collisions!
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function mergeData(array $data):array {
		return array_merge($data,$this->data);
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	protected function addContextToData(array $data):array{
		return array_merge( $data , $this->getContext() );
	}

	/**
	 * Constructs the context
	 *
	 * @return array
	 */
	protected function getContext():array {
		if( ! is_object($this->context) ){
			$this->context = $this->container->make($this->context);
		}

		return $this->context->toArray();
	}
}