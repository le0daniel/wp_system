<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 15.11.17
 * Time: 11:05
 */

namespace le0daniel\System\WordPress;

use le0daniel\System\Contracts\ShortCode as ShortCodeContract;
use le0daniel\System\Helpers\Language;
use le0daniel\System\WordPress\VisualComposer\ParameterHelper;

class ShortCode implements ShortCodeContract{

	/**
	 * The full Human readable name,
	 * The Template name is generated from it
	 * My View => my_view
	 * +--> Done using snake_case($name)
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $slug;

	/**
	 * @var string
	 */
	protected $namespace = '@shorts';

	/**
	 * @var string
	 */
	protected $extension = 'twig';

	/**
	 * @var bool
	 */
	protected $autotranslate = true;

	/**
	 * Should the WP Context Be included to render the Shortcut
	 *
	 * @var bool
	 */
	protected $render_with_context = false;

	/**
	 * ShortCode constructor.
	 *
	 * @param string $name
	 *
	 * @throws \Exception
	 */
	public function __construct(string $name = '') {

		if(empty($this->name) && empty($name)){
			throw new \Exception('A component name must be set!');
		}

		/* Overwrite name */
		if( !  empty($name) ){
			$this->name = $name;
		}

		/* Generate slug if needed */
		if( ! isset($this->slug) ){
			$this->slug = snake_case($this->name);
		}
	}

	/**
	 * Returns the Filename
	 *
	 * @return string
	 */
	protected function getTemplateName():string{
		return sprintf('%s/%s.%s',$this->namespace,$this->slug,$this->extension);
	}

	/**
	 * Returns an array to construct a shortcode
	 *
	 * @return array
	 */
	public function toShortcode():array{
		// Array [ name, callable ]
		return [$this->slug,[$this,'render']];
	}

	/**
	 * @param array $attributes
	 * @param null $content What was between the Brackets!
	 *
	 * @return string
	 */
	public function render($attributes = [],$content = null):string{

		if(!is_array($attributes)){
			$attributes = ['given'=>$attributes];
		}

		/* Always overwrite the content */
		$attributes['content']=$content;

		/* IMPORTANT: A shortcode has Access to the View WP Context! */
		return view()->render( $this->getTemplateName(), $attributes, $this->render_with_context);
	}

	/**
	 * @param $key
	 *
	 * @return string
	 * @throws \Exception
	 */
	protected function translate($key){

		/* Check if isset */
		if(!isset($this->$key)){
			/* Throw exception if there was no found key for this value! */
			throw new \Exception('Parameter ['.$key.'] not found in '.get_called_class().'!');
		}

		/* If no translation */
		if( ! $this->autotranslate ){
			return $this->$key;
		}

		return Language::translate($this->$key);
	}
}