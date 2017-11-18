<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 18.11.17
 * Time: 10:17
 */

namespace le0daniel\System\Traits;


use le0daniel\System\WordPress\VisualComposer\ParameterHelper;

trait isVisualComposerComponent {

	/**
	 * @return array
	 */
	public function toVisualComposer():array{

		/* Init Parameter Helper */
		$parameter = new ParameterHelper();

		/* Get Paramters */
		if(method_exists($this,'createVisualComposerParams')){
			$this->createVisualComposerParams($parameter);
		}


		return [
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
	}

}