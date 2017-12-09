<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 16.11.17
 * Time: 20:48
 */

namespace le0daniel\System\WordPress;


use Carbon\Carbon;
use le0daniel\System\Contracts\Hashable;
use le0daniel\System\Helpers\TwigFunctions;
use le0daniel\System\Traits\isGettable;

/**
 * Class Post
 * @package le0daniel\System\WordPress
 *
 * @property int $id
 * @property string $title
 * @property string $content
 * @property array $class
 * @property array $links
 * @property array $actions
 */
class Post {
	use isGettable;

	/**
	 * Contains Post attributes
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Array with fields
	 *
	 * @var array
	 */
	protected $fields = [];

	/**
	 * @var int
	 */
	protected $id;

	/**
	 * @var array
	 */
	protected $_cached_meta;

	/**
	 * Post constructor.
	 *
	 * @param \WP_Post $post
	 */
	public function __construct(\WP_Post $post) {

		/* Set ID */
		$this->id = $post->ID;

		/* Set Attributes */
		$this->attributes = [
			'id'        =>$this->id,
			'title'     =>$post->post_title,
			'meta'      =>$this->id,

			/* Pass the content through filters */
			'content'   =>$post->post_content,

			/* For displaying */
			'class'=>   get_post_class(),

			'links'=>null,
			'actions'=>null,

			/* Continue */
			'author'    =>$post->post_author,
			'date'      =>Carbon::createFromFormat('Y-m-d H:i:s',$post->post_date),

			/* If editable by user */
			'has_actions'  =>(get_edit_post_link())?true:false,
		];

	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function getContentAttribute($value){
		return apply_filters('the_content',$value);
	}

	/**
	 * @param $value
	 *
	 * @return mixed
	 */
	protected function getMetaAttribute($value){

		/* Get cached Meta */
		if( ! isset($this->_cached_meta) ){
			$this->_cached_meta = get_post_meta($value);
		}

		return $this->_cached_meta;
	}

	/**
	 * @return array
	 */
	protected function getLinksAttribute():array {
		/* Return Array with links */
		return [
			'next'      =>get_next_posts_link(),
			'previous'  =>get_preview_post_link(),
		];
	}

	/**
	 * @return array
	 */
	protected function getActionsAttribute():array{
		return [
			'next'      =>get_next_posts_link(),
			'previous'  =>get_preview_post_link(),
		];
	}

	/**
	 * @param string $key
	 *
	 * @return bool|mixed
	 */
	public function field(string $key){

		/**
		 * Cache Field!
		 */
		if( ! array_key_exists($key,$this->fields) ){
			$this->fields[$key] = TwigFunctions::getField($key,$this->id);
		}

		return $this->fields[$key];
	}

	/**
	 * Alias for field!
	 * @param string $key
	 *
	 * @return bool|mixed
	 */
	public function meta(string $key){
		return $this->field($key);
	}

	/**
	 * Return the content!
	 */
	public function __toString() {
		return (string) $this->content;
	}
}