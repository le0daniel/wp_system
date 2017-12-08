<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 16.11.17
 * Time: 21:16
 */

namespace le0daniel\System\Traits;


trait isGettable {

	/**
	 * getNameAttribute
	 *
	 * @param string $attribute_name
	 *
	 * @return string
	 */
	protected function getterName(string $attribute_name = ''){
		return camel_case('get '.$attribute_name. ' attribute');
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
				array_key_exists($name,$this->attributes)?$this->attributes[$name]:null
			);
		}

		/**
		 * Return Value
		 */
		if( array_key_exists($name,$this->attributes) ){
			return $this->attributes[$name];
		}

		return false;
	}

	/**
	 * @param $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {

		if( ! is_array($this->attributes) ){
			return false;
		}

		return array_key_exists($name,$this->attributes) || method_exists($this,$this->getterName($name));
	}

	/**
	 * @return array
	 */
	public function toArray() {
		return $this->attributes;
	}

}