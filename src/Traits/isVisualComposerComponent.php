<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 18.11.17
 * Time: 10:17
 */

namespace le0daniel\System\Traits;


use le0daniel\System\Helpers\Path;
use le0daniel\System\WordPress\VisualComposer\ParameterHelper;

trait isVisualComposerComponent {

	/**
	 * @return string
	 */
	protected function getFullCachePath():string{
		$cache_name = md5($this->name.$this->slug);
		$cache_extension = '.serialized.vc';
		$full_cache_path = Path::cachePath('vc/'.$cache_name.$cache_extension);
		return $full_cache_path;
	}

	/**
	 * @return array
	 */
	public function toVisualComposer():array{

		/* Get from cache! */
		if( ! WP_DEBUG && file_exists($this->getFullCachePath())){
			return unserialize( file_get_contents($this->getFullCachePath()) );
		}

		/* Init Parameter Helper */
		$parameter = new ParameterHelper();

		/* Get Paramters */
		if(method_exists($this,'createVisualComposerParams')){
			$this->createVisualComposerParams($parameter);
		}


		$data = [
			/* Human Readable Name */
			'name'=>$this->name,

			/* Shortcode Slug (ID) */
			'base'=>$this->slug,

			/* Element Description */
			'description'=>$this->description,

			/* Backery builder */
			'class'=>$this->slug,
			'show_settings_on_create'=>true,
			'category'=>$this->categorie,
			//'group'=>'',
			//'icon'=>'',
			//'custom_markup'=>'',

			/* Bind Parameters */
			'params'=> $parameter->toArray(),
		];

		/* Cache */
		if( ! WP_DEBUG ){
			file_put_contents( $this->getFullCachePath(), serialize($data) );
		}

		return $data;
	}

}