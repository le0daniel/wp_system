<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 17.11.17
 * Time: 17:38
 */

namespace le0daniel\System\Contracts;


interface ShortCode {

	public function toShortcode():array;
	public function render($attributes = [],$content = null):string;
}