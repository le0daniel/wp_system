<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 12.11.17
 * Time: 14:22
 */

namespace le0daniel\System\WordPress;


use le0daniel\System\Traits\isGettable;

/**
 * Class Site
 * @package le0daniel\System\WordPress
 *
 * @property string $name
 * @property string $description
 * @property string $url
 */
class Site {
	use isGettable;

	/**
	 * Accessible attributes
	 *
	 * @var array
	 */
	protected $attributes = [
		'name'              =>null,
		'description'       =>null,
		'wpurl'             =>null,
		'url'               =>null,
		'admin_email'       =>null,
		'charset'           =>null,
		'version'           =>null,
		'html_type'         =>null,
		'language'          =>null,
		//'stylesheet_url'    =>null,
		//'stylesheet_directory'=>null,
		'template_url'      =>null,
		'pingback_url'      =>null,
		'atom_url'          =>null,
		//'rdf_url'           =>null,
		//'rss_url'           =>null,
		//'rss2_url'          =>null,
		//'comments_atom_url' =>null,
		//'comments_rss2_url' =>null,
	];

	/**
	 * @var string
	 */
	protected $title_separator;

	/**
	 * Site constructor.
	 *
	 * @param string $title_separator
	 */
	public function __construct(string $title_separator = '|') {
		$this->title_separator = $title_separator;
	}
	
	/**
	 * Title Mutator
	 */
	public function getTitleAttribute(){
		/* Load title */
		$title = get_the_title();

		if($title){
			return sprintf('%s %s %s ',$title,$this->title_separator,$this->name);
		}

		return $this->name;
	}

	/**
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public function loadAndReturnValue( $name ){

		/* Sort out magic getter properties */
		if( ! array_key_exists($name,$this->attributes) ){
			return null;
		}

		/* Load if needed */
		if( is_null( $this->attributes[$name] ) ){
			$this->attributes[$name] = get_bloginfo($name);
		}

		/* Return */
		return $this->attributes[$name];
		
	}
	
	/**
	 * @param $name
	 *
	 * @return bool|mixed
	 */
	public function __get( $name ) {

		/* Getter method getNameAttribute() */
		$getter = $this->getterName($name);

		/**
		 * Call getter with value
		 */
		if( method_exists($this,$getter) ){
			return $this->{$getter}(
				$this->loadAndReturnValue($name)
			);
		}

		return $this->loadAndReturnValue($name);
	}

	/**
	 * Serialize
	 */
	public function toArray():array{

		$array = [];

		foreach ($this->attributes as $key=>$item){

			if( ! is_null($item) ){
				$array[$key] = $item;
			}

			$array[$key] = get_bloginfo($key);

		}
		return $array;
	}
}