<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 07.11.17
 * Time: 14:47
 */

namespace le0daniel\System\View;


use Dotenv\Exception\InvalidFileException;
use Illuminate\Container\Container;
use le0daniel\System\View\ViewComposers\TwigViewComposer;
use le0daniel\System\Contracts\ViewComposer;
use le0daniel\System\Helpers\Path;

class View {

	protected $container;

	/**
	 * List of composers
	 *
	 * @var array
	 */
	protected $composers = [];

	/**
	 * List of composers
	 *
	 * @var array
	 */
	protected $include_paths = [];

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
	 * @var string
	 */
	protected $prefix_filename;

	/**
	 * View constructor.
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;

		/* Add twig view composer */
		$this->registerViewComposer('twig',TwigViewComposer::class);
	}

	/**
	 * Register a custom view composer
	 *
	 * @param string $file_extension
	 * @param $viewComposer
	 *
	 * @return $this
	 */
	public function registerViewComposer(string $file_extension,$viewComposer){
		$this->composers[ trim( $file_extension,'.') ]= $viewComposer;
		return $this;
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
	 * @param string $prefix
	 */
	public function addFilenamePrefix(string $prefix){

		/* delete / */
		if(substr($prefix,-1) === '/'){
			$prefix = substr($prefix, 0, -1);
		}

		$this->prefix_filename = $prefix;
	}

	/**
	 * @param string $path
	 * @param string $alias
	 */
	public function addIncludePath(string $path,string $alias){
		$this->include_paths[$path]=$alias;
	}

	/**
	 * Renders a given Template with an extension
	 *
	 * @param string $filename
	 * @param array $data
	 *
	 * @return string
	 */
	public function render(string $filename,array $data=[]):string {

		/* Combine file name */
		if(isset($this->prefix_filename)){
			$filename = $this->prefix_filename . '/' . trim($filename,'/');
		}

		/* Get the view composer */
		$composer = $this->getViewComposerByFilename($filename);

		/* Let the view composer render the view! */
		return $composer->render($filename,$this->mergeData($data));
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

		/**
		 * Adds Additional Data to the data object
		 */
		$this->setAdditionalData();

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
	 * Adds additional Data to the data object
	 */
	protected function setAdditionalData(){

		$file_called = Path::getCallingScriptFile(1);

		/* Sets the WP data */
		$this->data['wp']=[

			/* Usefull for knowing which file was called by wordpress! */
			'file_called'       =>basename($file_called),
			'file_called_full'  =>$file_called,


		];
	}

	/**
	 * @param string $filename
	 *
	 * @throws InvalidFileException
	 * @return ViewComposer
	 */
	protected function getViewComposerByFilename(string $filename):ViewComposer{

		/* Escape */
		$mapped_ends = array_map(function($regex){
			return preg_quote($regex);
		},array_keys($this->composers));

		/* Build regex */
		$regex = '/^.*\.(' . implode('|',$mapped_ends) . ')$/';

		/* Match */
		preg_match($regex,$filename,$matches);

		/* Error, View composer not found */
		if( ! isset($matches[1]) ){
			throw new InvalidFileException(
				sprintf('No view composer found for %s',$filename)
			);
		}

		/**
		 * Build and return the Composer!
		 */
		return $this->container->make(
			$this->composers[ $matches[1] ],
			$this->getViewComposerContext()
		);
	}

	/**
	 * The available Variables passed to the View Composer
	 *
	 * @return array
	 */
	protected function getViewComposerContext():array {
		return [
			'calling_script_path'   =>Path::getCallingScriptDir(1),
			'include_paths'         =>$this->include_paths,
		];
	}

}