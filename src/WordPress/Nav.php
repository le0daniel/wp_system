<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 01.12.17
 * Time: 23:50
 */

namespace le0daniel\System\WordPress;


class Nav {

	/**
	 * @var
	 */
	protected $menu;

	/**
	 * @var
	 */
	protected $items;

	/**
	 * Nav constructor.
	 *
	 * @param string $nav_name
	 */
	public function __construct($nav_name) {

		//$start = microtime(true);
		$menu_locations = get_nav_menu_locations();
		if( ! isset($menu_locations[$nav_name]) ){
			return;
		}

		$this->menu = wp_get_nav_menu_items($menu_locations[$nav_name]);
		$this->nestItems();

		//$d = microtime(true) - $start;

		//dd($d);
	}

	/**
	 * Nest Items, Only support for 2 levels
	 *
	 * @param bool $nest
	 */
	public function nestItems(bool $nest = false){

		foreach ( $this->menu as $wp_item ) {

			$item = new NavItem($wp_item);

			/* ignore nested navs */
			if( ! $nest && $item->parent){
				continue;
			}

			/* TODO: Nest  */

			/* Add to item! */
			$this->items[$item->id] = $item;
		}


	}

	public function items(){
		if( ! $this->menu ){
			return false;
		}


	}

}