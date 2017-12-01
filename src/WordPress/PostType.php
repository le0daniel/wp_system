<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 01.12.17
 * Time: 19:40
 */

namespace le0daniel\System\WordPress;


use \le0daniel\System\Contracts\PostType as PostTypeContract;
use le0daniel\System\Helpers\Language;

class PostType implements PostTypeContract {

	protected $prefix = '';
	protected $slug;
	protected $description = '';

	protected $name = '';
	protected $singular_name = '';
	protected $args = [];

	public function __construct() {

		/* Create Slug */
		if(! isset($this->slug) ){
			$this->slug = $this->getSlugFromName();
		}

		/* Create Singular name */
		if( ! isset($this->singular_name) ){
			$this->singular_name = $this->name;
		}

	}

	/**
	 * @return string
	 */
	protected function getSlugFromName():string{
		return snake_case( $this->name );
	}

	/**
	 * @return string
	 */
	protected function getPostTypeIdentifier():string{
		return substr($this->prefix.$this->slug,0,20);
	}

	/**
	 * @return array
	 */
	public function toPostType(): array {
		return [
			$this->getPostTypeIdentifier(),
			$this->getArgs(),
		];
	}

	/**
	 * Returns all args
	 *
	 * @return array
	 */
	protected function getArgs():array{

		$data = $this->args;

		/* Overwrite Manditory */
		$data['labels']['name']=            Language::translate( $this->name );
		$data['labels']['singular_name']=   Language::translate($this->singular_name);
		$data['description']=               Language::translate($this->description);
		$data['rewrite']['slug']=           $this->slug;
		$data['public']=true;

		return $data;
	}
}