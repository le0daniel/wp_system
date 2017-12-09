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
	 * @var array
	 */
	protected $only = [];

	/**
	 * Should the WP Context Be included to render the Shortcut
	 *
	 * @var bool
	 */
	protected $render_with_context = false;

	/**
	 * Cache file in plain HTML
	 *
	 * @var bool
	 */
	protected $cache = true;

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
	protected function getTemplatePath():string{
		return sprintf('%s/%s.%s',$this->namespace,$this->slug,$this->extension);
	}

	/**
	 * Returns the Tag name of the shortcode
	 *
	 * @return string
	 */
	protected function getTagName(): string {
		return $this->slug;
	}

	/**
	 * Checks if it should cache the config
	 *
	 * @return bool
	 */
	protected function shouldCache(): bool {
		return ($this->cache && WP_DEBUG );
	}

	/**
	 * @param $key
	 *
	 * @return bool
	 */
	protected function filterKeys($key):bool{
		return (in_array($key,$this->only));
	}

	/**
	 * Returns an array to construct a shortcode
	 *
	 * @return array
	 */
	public function toShortcode(): array {
		return [
			$this->getTagName(),
			[$this,'render']
		];
	}

	/**
	 * @param $attributes
	 * @param $content
	 *
	 * @return array
	 */
	protected function prepareAttributes($attributes,$content):array{

		/* Cast as Array */
		if( ! is_array($attributes)){
			$attributes = ['attribute'=>$attributes];
		}

		/* Filter Attributes if needed */
		if( ! empty($this->only) ){
			$attributes = array_filter($attributes,[$this,'filterKeys'],ARRAY_FILTER_USE_KEY);
		}

		/* Always overwrite the content */
		if( ! empty($content)){
			$attributes['content']= do_shortcode( $content );
		}
		else{
			$attributes['content']=null;
		}

		return $attributes;
	}

	/**
	 * @param array $raw_attributes
	 * @param null $raw_content
	 *
	 * @return string
	 */
	final public function render($raw_attributes = [],$raw_content = null): string {

		/** @var array $attributes */
		$attributes = $this->prepareAttributes($raw_attributes,$raw_content);

		/* Cache in plain HTML if it's without context */
		$plain_cache = true;
		if( $this->render_with_context || ! $this->shouldCache() ){
			$plain_cache = false;
		}

		/**
		 * IMPORTANT:
		 * A shortcode does not have access to the context
		 * by default, and should normally not have access
		 * to it!
		 */
		return view()->render( $this->getTemplatePath(), $attributes, $this->render_with_context, $plain_cache);
	}
}