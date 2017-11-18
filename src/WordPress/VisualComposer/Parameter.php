<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 18.11.17
 * Time: 10:34
 */

namespace le0daniel\System\WordPress\VisualComposer;


use le0daniel\System\Helpers\Language;

class Parameter {

	/**
	 * @var bool
	 */
	protected $autotranslate = true;

	/**
	 * Contains all keys which are required!
	 *
	 * @var array
	 */
	protected $required = [
		'param_name',
		'type',
		'heading',
		'description'
	];

	/**
	 * @var array
	 */
	protected $attributes = [
		'param_name'=>null,
		'type'=>null,
		'heading'=>null,
		'description'=>null,
		'value'=>null,
		'dependency'=>null,
		'group'=>null,
	];

	/**
	 * @var array
	 */
	protected $types = [
		'textarea_html',
		'textfield',
		'textarea',
		'dropdown',
		'attach_image',
		'attach_images',
		'posttypes',
		'colorpicker',
		'exploded_textarea',
		'widgetised_sidebars',
		'textarea_raw_html',
		'vc_link',
		'checkbox',
		'loop',
		'css',
	];

	/**
	 * Disable Translation
	 *
	 * @return $this
	 */
	public function disableAutotranslate(){
		$this->autotranslate = false;
		return $this;
	}

	/**
	 * Parameter constructor.
	 *
	 * @param string $name
	 * @param string $type
	 *
	 * @throws \Exception
	 */
	public function __construct(string $name,string $type) {

		if( ! in_array($type,$this->types) ){
			throw new \Exception('Visual Composer Type ['.$type.'] not found!');
		}

		$this->attributes['param_name']=$name;
		$this->attributes['type'] = $type;
	}

	/**
	 * @param string $name
	 *
	 * @return $this
	 */
	public function setGroup(string $name){
		$this->attributes['group']=$name;
		return $this;
	}

	/**
	 * @param string $description
	 *
	 * @return $this
	 */
	public function addDescription(string $description){
		$this->attributes['description']= $description;
		return $this;
	}

	/**
	 * @param string $header
	 *
	 * @return $this
	 */
	public function addHeading(string $header){
		$this->attributes['heading']= $header;
		return $this;
	}

	/**
	 * @param $array_or_string
	 *
	 * @return $this
	 * @throws \Exception
	 */
	public function addValue($array_or_string){
		if( ! is_string($array_or_string) && !is_array($array_or_string) ){
			throw new \Exception('The default value must be of type string | array');
		}
		$this->attributes['value']= $array_or_string;
		return $this;
	}

	/**
	 * Deprecated! Do NOT US
	 *
	 * @param $value_DEPRECATED
	 *
	 * @return Parameter
	 */
	public function addDefaultValue($value_DEPRECATED){
		return $this->addValue($value_DEPRECATED);
	}

	/**
	 * @param string $param_name
	 * @param array $values
	 * @param bool $not_empty
	 * @param string $js_callback_name
	 *
	 * @return $this
	 */
	public function addDependency(string $param_name,array $values,bool $not_empty=false,string $js_callback_name=''){

		$this->attributes['dependency']=[
			'element'=>$param_name,
			'value'=>$values,
			'not_empty'=>$not_empty,
			'callback'=>$js_callback_name,
		];

		return $this;
	}


	/**
	 * @param $element
	 *
	 * @return bool
	 */
	protected function filterArray($element){
		return ! is_null($element);
	}

	/**
	 * Used to set a key value pair which is not yet available in the
	 * Builder!
	 *
	 * @param string $key
	 * @param $value
	 *
	 * @return $this
	 */
	public function set(string $key,$value){
		$this->attributes[$key]=$value;
		return $this;
	}

	/**
	 * Returns a filtered Attributes array!
	 *
	 * @return array
	 */
	public function toArray():array{
		/* Filter input */
		$filtered = array_filter($this->attributes,[$this,'filterArray']);

		$this->checkRequiredKeys($filtered);

		/* Translate */
		if($this->autotranslate){

			foreach(['heading','description'] as $key){
				$filtered[$key] = Language::translate($filtered[$key]);
			}

		}

		return $filtered;
	}

	/**
	 * @param array $array
	 *
	 * @throws \Exception
	 */
	protected function checkRequiredKeys(array $array){

		/* Check for required keys */
		foreach($this->required as $key){
			if( ! array_key_exists($key,$array) ){
				throw new \Exception('The parameter '.$key.' must be set!');
			}
		}

	}
}