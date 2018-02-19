<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 01.12.17
 * Time: 19:37
 */

namespace le0daniel\System\Contracts;


interface PostType {

	public function toPostType():array;

	public function taxonomies();
}