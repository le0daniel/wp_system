<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 07.11.17
 * Time: 14:47
 */

namespace le0daniel\System\View;


use Illuminate\Container\Container;
use le0daniel\System\Contracts\CastArray;
use le0daniel\System\Helpers\Path;

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

		/* Translation filters */
		't'             =>'\\le0daniel\\System\\Helpers\\TwigFilters::translate',
		'translate'     =>'\\le0daniel\\System\\Helpers\\TwigFilters::translate',

		/* Shortcodes, may be vulnerable to xss, developer should handle that! */
		'shortcode'     =>[
			'\\le0daniel\\System\\Helpers\\TwigFilters::shortcode',
			['is_safe'=>['html']]
		],
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
	 * @var array
	 */
	protected $context = [];

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

		/* Set WP Data */
		$this->setAdditionalData();
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

		/* Get config */
		$config = $this->twig_config;

		/* Set Cache */
		$config['cache']=$this->twigCachePath();
		if(WP_DEBUG === true){
			$config['cache']=false;
		}

		/* Init Twig */
		$this->twig = new \Twig_Environment($loader,$config);

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
	 * @param CastArray $context
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function addContext(CastArray $context){

		if( ! empty($this->context) ){
			throw new \Exception('Context can only be set once!');
		}

		$this->context = $context->toArray();

		return $this;
	}

	/**
	 * Checks if a given shared value key exists!
	 * @param string $key
	 *
	 * @return bool
	 */
	public function hasSharedKey(string $key):bool {
		return array_key_exists($key,$this->data);
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
	 *
	 * @return string
	 */
	public function render(string $filename,array $data=[], bool $with_context = true):string {

		$data_to_render = $this->mergeData($data);

		/* Add context! */
		if($with_context){
			$data_to_render = $this->addContextToData($data);
		}

		return (string) $this->getTwig()->render(
			$filename,
			$data_to_render
		);
	}

	/**
	 * Render and show a page!
	 *
	 * @param string $filename
	 * @param array $data
	 */
	public function show(string $filename,array $data=[]){
		echo $this->render($filename,$data);
	}

	/**
	 * Merges the Data arrays and informs of collisions!
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	protected function mergeData(array $data):array {

		$intersect = array_intersect_key($this->data,$data);

		/* Check if there is a collision! */
		if( ! empty($intersect) ){
			app()
				->log()
				->warning('Duplicated keys in data! Following keys where overwritten by the shared data: '. implode(', ', array_keys($intersect)));
		}

		/* Merge the arrays, always in favor of shared data! */
		return array_merge($data,$this->data);
	}

	/**
	 * @param array $data
	 *
	 * @return array
	 */
	protected function addContextToData(array $data):array{
		return array_merge($data,$this->context);
	}

	/**
	 * Adds additional Data to the data object
	 */
	protected function setAdditionalData(){

		/* Sets the WP data */
		$this->data['wp']=[
			/* Root dir, to look for templates */
			'root_dir'          =>$this->root_dir,
		];
	}

}