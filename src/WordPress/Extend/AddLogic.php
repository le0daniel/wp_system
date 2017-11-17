<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 17.11.17
 * Time: 17:12
 */

namespace le0daniel\System\WordPress\Extend;

use Illuminate\Container\Container;
use le0daniel\System\Contracts\ShortCode;

class AddLogic {

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * Array containing shortcode Classes!
	 *
	 * @var array
	 */
	protected $shortcodes = [];

	/**
	 * WordPressExtender constructor.
	 *
	 * @param Container $container
	 */
	public function __construct(Container $container) {
		$this->container = $container;
	}

	/**
	 * Calls all the Register Hooks
	 */
	public function boot(){

		/* Add Actions */
		add_action('init',[$this,'registerShortCodes']);

	}

	/**
	 * Registers all ShortCodes!
	 */
	public function registerShortCodes(){

		/* Make the Shortcodes */
		array_walk($this->shortcodes,[$this,'makeShortCode']);

		array_walk($this->shortcodes,[$this,'registerShortCode']);
	}

	/**
	 * Casts the shortcut
	 *
	 * @param $abstract
	 *
	 * @throws \Exception
	 */
	protected function makeShortCode(&$abstract){

		/* Make object if needed */
		if( ! is_object($abstract) ){
			$abstract = $this->container->make($abstract);
		}

		if( ! $abstract instanceof ShortCode){
			throw new \Exception('Shortcode must Implement the Shortcode contract!');
		}
	}

	/**
	 * @param ShortCode $short_code
	 */
	protected function registerShortCode(ShortCode $short_code){
		list($name,$callable) = $short_code->toShortcode();
		add_shortcode($name,$callable);
	}

}