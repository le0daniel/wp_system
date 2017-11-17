<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 14.11.17
 * Time: 21:48
 */

namespace le0daniel\System\WordPress\VisualComposer;


class Extender {

	protected $components = [];

	/**
	 * @param string $name
	 * @param $abstract
	 *
	 * @return Extender
	 */
	public function addComponent(string $name,$abstract):Extender{

		$this->components[camel_case($name)] = $abstract;

		return $this;
	}

	/**
	 * Register the components for Visual Composer
	 *
	 * @param array $names
	 *
	 * @return array
	 */
	public function register(array $names = []){

		$extensions = $this->components;

		/** Filter the extension */
		if( ! empty($names) ){
			$names = array_map('camel_case',$names);
			$extensions = array_filter($extensions,function($name)use($names){
				return in_array($name,$names);
			},ARRAY_FILTER_USE_KEY);
		}

		return $extensions;
	}

	/**
	 * @param $name
	 * @param $arguments
	 *
	 * @return string
	 * @throws \Exception
	 */
	public function __call( $name, $arguments ) {

		$prefix = 'getComponent';
		$name = lcfirst( substr( $name,strlen($prefix) ) );

		if( ! array_key_exists($name,$this->components)){
			throw new \Exception(sprintf('VC Component [%s] not found',$name));
		}

		/* Now Init with attributes */
		return $this->components[$name];
	}

}