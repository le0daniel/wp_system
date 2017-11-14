<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 07.11.17
 * Time: 14:26
 */

namespace le0daniel\System\View\ViewComposers;


use Illuminate\Container\Container;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use le0daniel\System\Contracts\ViewComposer;
use le0daniel\System\Helpers\Path;
use le0daniel\System\WordPress\Context;
use Timber\Timber;

class TwigViewComposer implements ViewComposer {

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
	 * @var Container
	 */
	private $container;

	/**
	 * @var string
	 */
	private $root_dir;

	/**
	 * @var array
	 */
	private $include_paths = [];

	/**
	 * TwigViewComposer constructor.
	 *
	 * @param Container $container
	 * @param string $root_dir
	 * @param array $include_paths
	 */
	public function __construct(
		Container $container,
		string $root_dir,
		array $include_paths = []
	) {
		$this->container = $container;

		/* Get Calling script */
		$this->root_dir = $root_dir;

		/* Set include paths */
		$this->include_paths = $include_paths;

		/* Init Twig */
		$this->buildTwig();
	}

	/**
	 * Build Twig
	 */
	protected function buildTwig(){

		/* Load Twig */
		$loader = new \Twig_Loader_Filesystem($this->root_dir);

		/* Add Additional paths */
		$this->addPaths($loader);

		/* Get config */
		$config = $this->twig_config;

		/* Set Cache */
		if(WP_DEBUG !== true){
			$config['cache']=$this->twigCachePath();
		}
		else{
			$config['cache']=false;
		}

		/* Init Twig */
		$this->twig = new \Twig_Environment($loader,$config);

		/* Register Filters */
		$this->registerTwigFiltersAndFunctions($this->twig);
	}

	/**
	 * Overwrites the default cache path
	 *
	 * @return string
	 */
	public function twigCachePath():string {
		return Path::cachePath();
	}

	/**
	 * @param \Twig_Loader_Filesystem $filesystem_loader
	 *
	 * @return \Twig_Loader_Filesystem
	 * @throws FileNotFoundException
	 */
	public function addPaths(\Twig_Loader_Filesystem $filesystem_loader){

		/* Add aliases! */
		foreach ($this->include_paths as $path => $alias){

			if( Path::isAbsolute($path) && file_exists($path) ){
				$filesystem_loader->addPath($path,$alias);
			}
			elseif( file_exists( $this->root_dir . '/' . $path) ){
				$filesystem_loader->addPath( $this->root_dir . '/' . $path,$alias);
			}
			else{
				throw new FileNotFoundException(
					sprintf('Directory %s not found',$path)
				);
			}
		}
	}

	/**
	 * @param \Twig_Environment $twig
	 *
	 * @return \Twig_Environment
	 */
	public function registerTwigFiltersAndFunctions(\Twig_Environment $twig){

		$twig->addFilter(
			new \Twig_Filter('theme_path','\\le0daniel\\System\\Helpers\\TwigFilters::themePath')
		);

		$twig->addFilter(
			new \Twig_Filter('static_path','\\le0daniel\\System\\Helpers\\TwigFilters::staticPath')
		);

		$twig->addFunction(
			new \Twig_Function('function','\\le0daniel\\System\\Helpers\\TwigFunctions::captureCallableOutput')
		);

		return $twig;
	}

	/**
	 * Get Context from Timber
	 *
	 * @return array
	 */
	protected function getWPContext():array{
		return resolve('wp.context')->toArray();
	}

	/**
	 * Render A view!
	 *
	 * @param string $filename
	 * @param array $data
	 *
	 * @return string
	 */
	public function render( string $filename, array $data ): string {
		return (string) $this->twig->render($filename,$this->constructData($data));
	}

	/**
	 * Constructs the Data
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function constructData( array $data ):array{
		return array_merge(
			$this->getWPContext(),
			$data
		);
	}
}