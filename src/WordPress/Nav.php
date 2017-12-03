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
		7/$start = microtime(true);
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
	 */
	public function nestItems(){

		foreach ( $this->menu as $wp_item ) {

			$item = new NavItem($wp_item);

			if($item->parent && array_key_exists($item->parent,$this->items)){
				$this->items[$item->parent]->addChildren($item);
				continue;
			}

			$this->items[$item->id] = $item;
		}


	}

	public function items(){
		if( ! $this->menu ){
			return false;
		}


	}

}