<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 12.11.17
 * Time: 14:45
 */

namespace le0daniel\System\WordPress;


use Carbon\Carbon;
use le0daniel\System\Helpers\TwigFunctions;

class Page {

	/**
	 * Accessible through magic method
	 *
	 * @var array
	 */
	protected $attributes = [
		'body'=>null,
		'posts'=>null,
	];

	/**
	 * Page constructor.
	 */
	public function __construct() {
	}

	/**
	 * Get Posts
	 *
	 * @return array
	 */
	protected function loadPosts():array{

		$content = [];

		while( have_posts() ){
			/* Increment counter! */
			the_post();
			global $post;

			$content[] = new Post($post);
		}

		return $content;
	}

	/**
	 * Get The Body Context
	 */
	protected function loadBody():array{
		return [
			'class'=> get_body_class(),
		];
	}

	/**
	 * Returns the content if there is only one Post!
	 *
	 * @return bool|Post
	 */
	public function content(){

		if(count($this->posts) === 1){
			return $this->posts[0];
		}

		return false;
	}

	/**
	 * Add getter support
	 *
	 * @param string $name
	 *
	 * @return bool|mixed
	 */
	public function __get( string $name ) {
		if( ! array_key_exists($name,$this->attributes) ){
			return false;
		}

		$loader =  camel_case('load '.$name);

		if( is_null($this->attributes[$name]) && method_exists($this,$loader) ){
			$this->attributes[$name] = $this->{ $loader }();
		}

		return $this->attributes[$name];
	}

	/**
	 * @param $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return array_key_exists($name,$this->attributes);
	}

}