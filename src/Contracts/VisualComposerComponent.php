<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 17.11.17
 * Time: 17:16
 */

namespace le0daniel\System\Contracts;


use le0daniel\System\WordPress\VisualComposer\ParameterHelper;

interface VisualComposerComponent {

	/**
	 * @return array
	 */
	public function toVisualComposer():array;
	public function createVisualComposerParams(ParameterHelper $param);

}