<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 16.11.17
 * Time: 21:10
 */

namespace le0daniel\System\WordPress;


use le0daniel\System\Traits\isGettable;

class MetaField {

	use isGettable;

	/**
	 * @var array
	 */
	protected $attributes;

	/**
	 * @var
	 */
	protected $field;

	/**
	 * MetaField constructor.
	 *
	 * @param string $key
	 * @param bool $id
	 */
	public function __construct(string $key,$id = false) {

		/**
		 * Custom Field not installed
		 */
		if( ! function_exists('get_field_object')){
			$this->attributes = [];
			return;
		}

		$field =  get_field_object($key,$id);

		$this->attributes = $field['value'];
		unset($field['value']);

		$this->field = $field;

		//$this->attributes = get_field_object($key,$id) ;
	}

	/**
	 * @return string
	 */
	public function __toString():string {

		if ( is_string($this->attributes) ){
			return $this->attributes;
		}

		return json_encode($this->attributes);
	}

}