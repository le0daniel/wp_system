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
use le0daniel\System\Contracts\VisualComposerComponent;

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
	 * @var array
	 */
	protected $vc_components = [];

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

		/* Init all shortcodes */
		array_walk($this->shortcodes,[$this,'makeShortCode']);

		/* Add Actions */
		add_action('init',[$this,'registerShortCodes']);

		/* Register Possible VS Components */
		add_action( 'vc_before_init',[$this,'registerVisualComposerComponents']);
	}

	/**
	 * Registers all ShortCodes!
	 */
	public function registerShortCodes(){

		/* Make the Shortcodes */
		array_walk($this->shortcodes,[$this,'registerShortCode']);
	}

	/**
	 * Register all Possible VC Components!
	 */
	public function registerVisualComposerComponents(){
		array_walk($this->vc_components,[$this,'registerVisualComposerComponent']);
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

		/* Add Visual Composer component */
		if( $abstract instanceof VisualComposerComponent ){
			$this->vc_components[] = $abstract;
		}
	}

	/**
	 * @param ShortCode $short_code
	 */
	protected function registerShortCode(ShortCode $short_code){
		list($name,$callable) = $short_code->toShortcode();
		add_shortcode($name,$callable);
	}

	/**
	 * @param VisualComposerComponent $component
	 */
	protected function registerVisualComposerComponent(VisualComposerComponent $component){
		/* Add Component */
		vc_map($component->toVisualComposer());
	}

}