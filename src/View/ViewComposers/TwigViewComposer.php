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
use Timber\Timber;

class TwigViewComposer implements ViewComposer {

	/**
	 * @var Container
	 */
	private $container;

	/**
	 * @var string
	 */
	private $calling_script_path;

	/**
	 * @var array
	 */
	private $include_paths = [];

	/**
	 * TwigViewComposer constructor.
	 *
	 * @param Container $container
	 * @param string $calling_script_path
	 * @param array $include_paths
	 */
	public function __construct(
		Container $container,
		string $calling_script_path,
		array $include_paths = []
	) {
		$this->container = $container;

		/* Configure Timber */
		if(WP_DEBUG !== true){
			Timber::$twig_cache = true;
		}

		/* Escape HTML by default! */
		Timber::$autoescape = 'html';

		/* Make and bind timber */
		$timber = $this->container->make(Timber::class);
		$this->container->instance(Timber::class,$timber);

		/* Get Calling script */
		$this->calling_script_path = $calling_script_path;

		/* Set include paths */
		$this->include_paths = $include_paths;

		/* Register Timber Hooks */
		$this->registerFilters();
	}

	/**
	 * Add wordpress filters
	 */
	protected function registerFilters(){
		add_filter('timber/loader/loader',  [$this,'addPaths']);
		add_filter('timber/twig',           [$this,'registerTwigFilters']);
		add_filter('timber/cache/location', [$this,'twigCachePath']);
	}

	/**
	 * Overwrites the default cache path
	 *
	 * @param $default_path
	 *
	 * @return string
	 */
	public function twigCachePath($default_path = ''):string {

		$cache_path =Path::combine( $this->calling_script_path ,'cache');

		/* Make Sure the dir exists */
		if( ! file_exists($cache_path) || ! is_dir($cache_path) ){
			mkdir($cache_path,0777,true);
		}

		return $cache_path;
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
			elseif( file_exists($this->calling_script_path. '/' . $path) ){
				$filesystem_loader->addPath($this->calling_script_path. '/' . $path,$alias);
			}
			else{
				throw new FileNotFoundException(
					sprintf('Directory %s not found',$path)
				);
			}
		}

		return $filesystem_loader;
	}

	/**
	 * @param \Twig_Environment $twig
	 *
	 * @return \Twig_Environment
	 */
	public function registerTwigFilters(\Twig_Environment $twig){

		$twig->addFilter(
			new \Twig_Filter('theme_path','\\System\\Helpers\\TwigFilters::themePath')
		);

		$twig->addFilter(
			new \Twig_Filter('static_path','\\System\\Helpers\\TwigFilters::staticPath')
		);

		return $twig;
	}

	/**
	 * Get Context from Timber
	 *
	 * @return array
	 */
	protected function getWPContext():array{
		return (array) Timber::get_context();
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
		return (string) Timber::fetch( $filename, $this->constructData( $data ) );
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