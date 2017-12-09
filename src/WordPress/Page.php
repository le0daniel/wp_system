<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 12.11.17
 * Time: 14:45
 */

namespace le0daniel\System\WordPress;


use le0daniel\System\Contracts\CastArray;
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
	 * If posts are loaded
	 *
	 * @var bool
	 */
	protected $_posts_loaded = false;
	protected $_posts_load_time = 0;

	/**
	 * Page constructor.
	 */
	public function __construct() {
		/* Set Body */
		$this->attributes['body']=[
			'class'=> get_body_class(),
		];

		$this->loadPosts();
		$this->_posts_loaded = true;
	}

	/**
	 * Load the posts
	 */
	protected function loadPosts(){
		$start = microtime(true);

		/* Load Posts */
		$this->attributes['posts'] = [];

		if(have_posts()) {
			while ( have_posts() ) {
				/* Increment counter! */
				the_post();
				global $post;

				/* Resolve Post through container */
				$this->attributes['posts'][] = resolve('wp.post',['post'=>$post]);
			}
			//$post = null;
			//wp_reset_query();
		}

		$this->_posts_loaded = true;
		$this->_posts_load_time = microtime(true) - $start;
	}

	/**
	 * @param $value
	 *
	 * @return array
	 */
	protected function getPostsAttribute($value){
		/* Make sur posts exists */
		if( empty($value) && ! $this->_posts_loaded ){
			app()->log('Post attributes not loaded!');
		}

		return $value;
	}

	/**
	 * Returns load time
	 */
	protected function getLoadtimeAttribute(){
		return $this->_posts_load_time;
	}

	/**
	 * @param $value
	 *
	 * @return string
	 */
	protected function getUrlAttribute($value=null):string{

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
	public function getContentAttribute(){

		if( count($this->posts) === 1){
			return $this->posts[0];
		}

		return false;
	}

}