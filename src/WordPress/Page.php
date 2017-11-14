<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 12.11.17
 * Time: 14:45
 */

namespace le0daniel\System\WordPress;


class Page {

	/**
	 * @var array
	 */
	public $body;

	/**
	 * Page constructor.
	 */
	public function __construct() {

		$this->getBodyContext();

	}

	protected function getBodyContext(){
		$this->body = [
			'class'=> implode(' ' , get_body_class()),
		];
	}


}