<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 18.11.17
 * Time: 10:32
 */

namespace le0daniel\System\WordPress\VisualComposer;


class ParameterHelper {

	protected $params = [];

	/**
	 * @param string $parameter_name
	 *
	 * @return Parameter
	 */
	public function addTextField(string $parameter_name):Parameter{
		$this->params[$parameter_name] = new Parameter($parameter_name,'textfield');
		return $this->params[$parameter_name];
	}

	/**
	 * @param string $parameter_name
	 *
	 * @return Parameter
	 */
	public function addTextArea(string $parameter_name):Parameter{
		$this->params[$parameter_name] = new Parameter($parameter_name,'textarea');
		return $this->params[$parameter_name];
	}

	/**
	 * Adds the Text Area HTML, name always content_html
	 *
	 * @return Parameter
	 */
	public function addTextAreaHtml():Parameter{
		$this->params['content'] = new Parameter('content','textarea_html');
		return $this->params['content'];
	}

	/**
	 * @param string $parameter_name
	 *
	 * @return Parameter
	 */
	public function addTextAreaRaw(string $parameter_name):Parameter{
		$this->params[$parameter_name] = new Parameter($parameter_name,'textarea_raw_html');
		return $this->params[$parameter_name];
	}

	/**
	 * @param string $parameter_name
	 *
	 * @return Parameter
	 */
	public function addDropdown(string $parameter_name):Parameter{
		$this->params[$parameter_name] = new Parameter($parameter_name,'dropdown');
		return $this->params[$parameter_name];
	}

	/**
	 * @param string $parameter_name
	 *
	 * @return Parameter
	 */
	public function addAttachImage(string $parameter_name):Parameter{
		$this->params[$parameter_name] = new Parameter($parameter_name,'attach_image');
		return $this->params[$parameter_name];
	}

	/**
	 * @param string $parameter_name
	 *
	 * @return Parameter
	 */
	public function addAttachMultipleImages(string $parameter_name):Parameter{
		$this->params[$parameter_name] = new Parameter($parameter_name,'attach_images');
		return $this->params[$parameter_name];
	}

	/**
	 * @param string $parameter_name
	 *
	 * @return Parameter
	 */
	public function addPostTypes(string $parameter_name):Parameter{
		$this->params[$parameter_name] = new Parameter($parameter_name,'posttypes');
		return $this->params[$parameter_name];
	}

	/**
	 * @param string $parameter_name
	 *
	 * @return Parameter
	 */
	public function addColorPicker(string $parameter_name):Parameter{
		$this->params[$parameter_name] = new Parameter($parameter_name,'colorpicker');
		return $this->params[$parameter_name];
	}

	/**
	 * @param string $parameter_name
	 *
	 * @return Parameter
	 */
	public function addCheckBox(string $parameter_name):Parameter{
		$this->params[$parameter_name] = new Parameter($parameter_name,'checkbox');
		return $this->params[$parameter_name];
	}

	/**
	 * @param string $parameter_name
	 *
	 * @return Parameter
	 */
	public function addCSS(string $parameter_name):Parameter{
		$this->params[$parameter_name] = new Parameter($parameter_name,'css');
		return $this->params[$parameter_name];
	}

	/**
	 * @return array
	 */
	public function toArray():array{
		return array_map([$this,'serializePatameters'],array_values($this->params));
	}

	/**
	 * @param Parameter $param
	 *
	 * @return array
	 */
	protected function serializePatameters(Parameter $param){
		return $param->toArray();
	}
}