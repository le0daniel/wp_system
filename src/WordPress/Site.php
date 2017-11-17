<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 12.11.17
 * Time: 14:22
 */

namespace le0daniel\System\WordPress;


use le0daniel\System\Traits\isGettable;

class Site {
	use isGettable;

	/**
	 * Accessible attributes
	 *
	 * @var array
	 */
	protected $attributes = [
		'name'          =>'',
		'description'   =>'',
		'wpurl'         =>'',
		'url'           =>'',
		'admin_email'   =>'',
		'charset'       =>'',
		'version'       =>'',
		'html_type'     =>'',
		'language'      =>'',
		'stylesheet_url'    =>'',
		'stylesheet_directory'=>'',
		'template_url'      =>'',
		'pingback_url'      =>'',
		'atom_url'          =>'',
		'rdf_url'           =>'',
		'rss_url'           =>'',
		'rss2_url'          =>'',
		'comments_atom_url' =>'',
		'comments_rss2_url' =>'',
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

		/**
		 * Load the content
		 */
		foreach(array_keys($this->attributes) as $key){
			$this->attributes[$key] = get_bloginfo($key);
		}
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

}