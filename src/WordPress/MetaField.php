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
	 * @var string
	 */
	protected $_value = '';

	/**
	 * @var
	 */
	//protected $field;

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

		/* Load the field */
		$this->attributes =  get_field_object($key,$id);


		if( isset($this->attributes['value']) ){
			$this->_value = $this->attributes['value'];
			unset($this->attributes['value']);
		}

	}

	/**
	 * Value magic getter
	 */
	protected function getValueAttribute(){
		return $this->_value;
	}

	/**
	 * @return string
	 */
	public function __toString():string {

		if(! is_string($this->_value)){
			return sprintf('Metafield value must be a string! %s given!',gettype($this->_value));
		}

		return $this->_value;
	}

}