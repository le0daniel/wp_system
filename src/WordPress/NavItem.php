<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 02.12.17
 * Time: 00:31
 */

namespace le0daniel\System\WordPress;


use le0daniel\System\Traits\isGettable;

class NavItem {
	use isGettable;

	/**
	 * @var \WP_Post
	 */
	protected $item;

	/**
	 * @var array
	 */
	protected $attributes = [
		'id'=>null,
		'children'=>[],
		'parent'=>false,
		'url'=>'',
		'title'=>'',
	];

	/**
	 * NavItem constructor.
	 *
	 * @param \WP_Post $item
	 */
	public function __construct(\WP_Post $item) {
		$this->item = $item;
		$this->attributes['id']= $item->ID;
		$this->attributes['url']= $item->url;
		$this->attributes['title']=$item->title;
		$this->attributes['parent']= ($item->menu_item_parent > 0) ? $item->menu_item_parent : false;
	}

	/**
	 * Check if has children
	 */
	public function getHasChildrenAttribute():bool{
		return (count($this->children) > 0);
	}

	/**
	 * @param NavItem $item
	 */
	public function addChildren(NavItem $item){
		$this->attributes['children'][$item->id] = $item;
	}

}