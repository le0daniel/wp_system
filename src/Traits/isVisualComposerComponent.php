<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 18.11.17
 * Time: 10:17
 */

namespace le0daniel\System\Traits;


use le0daniel\System\Helpers\Language;
use le0daniel\System\Helpers\Path;
use le0daniel\System\Helpers\TwigFilters;
use le0daniel\System\WordPress\VisualComposer\ParameterHelper;
use Psr\SimpleCache\CacheInterface;

trait isVisualComposerComponent {
	/**
	 * Gets the cache path
	 *
	 * @return string
	 */
	protected function vcCacheName():string{
		$namespace = 'vc_';
		$cache_name = md5($this->name.$this->slug);

		return $namespace.$cache_name;
	}

	/**
	 * Checks if file is cached
	 *
	 * @return bool
	 */
	protected function isCached(): bool {
		return ( cache()->has($this->vcCacheName()) );
	}

	/**
	 * @return array
	 */
	public function toVisualComposer():array{

		/** @var CacheInterface $manager */
		$manager = cache();

		/* Get from cache! */
		if( $this->shouldCache() && $manager->has($this->vcCacheName()) ){
			return $manager->get($this->vcCacheName());
		}

		/* Init Parameter Helper */
		$parameter = new ParameterHelper();

		/* Get Paramters */
		if(method_exists($this,'createVisualComposerParams')){
			$this->createVisualComposerParams($parameter);
		}

		$data = [
			/* Human Readable Name */
			'name'=>                    Language::translate( $this->name ),

			/* Shortcode Tag Name: default is slug */
			'base'=>                    $this->getTagName(),

			/* Element Description */
			'description'=>             Language::translate( $this->description ),
			'category'=>                Language::translate( $this->category ),

			/* Backery builder */
			'class'=>                   $this->getTagName(),

			/* Not Manditory */
			'show_settings_on_create'=> true,
			'group'=>                   $this->get('group'),
			'icon'=>                    $this->get('icon'),
			'weight'=>                  $this->get('weight'),

			/* Bind Parameters */
			'params'=>                  $parameter->toArray(),
		];

		/* Merge with additional params */
		if(isset($this->vc_params)){
			$data = array_merge($this->vc_params,$data);
		}

		/* Filter empty values! */
		$data = array_filter($data,[$this,'filterDataArray']);

		/* Cache */
		if( $this->shouldCache() ){
			$manager->set($this->vcCacheName(),$data);
		}

		return $data;
	}

	/**
	 * Filters the data array
	 *
	 * @param $item
	 *
	 * @return bool
	 */
	protected function filterDataArray($item):bool{
		return ( ! is_null($item) );
	}

	/**
	 * @param $key
	 *
	 * @return null
	 */
	protected function get($key){
		return (isset($this->$key))?$this->$key:null;
	}

}