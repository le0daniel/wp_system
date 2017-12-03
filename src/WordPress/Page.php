<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 12.11.17
 * Time: 14:45
 */

namespace le0daniel\System\WordPress;


use le0daniel\System\Traits\isGettable;

/**
 * Class Page
 * @package le0daniel\System\WordPress
 *
 * @property array $posts
 * @property array $body
 * @property string $url
 */
class Page {
	use isGettable;

	/**
	 * Accessible through magic method
	 *
	 * @var array
	 */
	protected $attributes = [
		'body'=>null,
		'posts'=>null,
		'url'=>null,
	];

	/**
	 * Page constructor.
	 */
	public function __construct() {
		/* Set Body */
		$this->attributes['body']=[
			'class'=> get_body_class(),
		];
	}

	/**
	 * Get Posts
	 *
	 * @return array
	 */
	protected function getPostsAttribute($value):array{

		/* Load */
		if(is_null($value)){

			$this->attributes['posts'] = [];

			while( have_posts() ){
				/* Increment counter! */
				the_post();
				global $post;

				$this->attributes['posts'][] = new Post($post);
			}

			return $this->attributes['posts'];
		}

		return $value;
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	protected function getUrlAttribute($value):string{

		if( $this->content() ){
			return (string) get_permalink();
		}

		global $wp;
		return home_url( $wp->request );
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

}