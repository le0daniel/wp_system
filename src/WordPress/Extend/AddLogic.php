<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 17.11.17
 * Time: 17:12
 */

namespace le0daniel\System\WordPress\Extend;

use Illuminate\Container\Container;
use le0daniel\System\Contracts\AddLogicToWordpress;
use le0daniel\System\Contracts\JsonApiEndpoint;
use le0daniel\System\Contracts\PostType;
use le0daniel\System\Contracts\ShortCode;
use le0daniel\System\Contracts\VisualComposerComponent;
use le0daniel\System\Helpers\Language;

class AddLogic implements AddLogicToWordpress {

	/**
	 * @var Container
	 */
	protected $container;

	/**
	 * Array containing Post Types
	 *
	 * @var array
	 */
	protected $post_types = [];

	/**
	 * Array containing Key values of menues
	 *
	 * @var array
	 */
	protected $navs = [];

	/**
	 * Array containing shortcode Classes!
	 *
	 * @var array
	 */
	protected $shortcodes = [];

	/**
	 * Array containg Json Endpoints
	 *
	 * @var array
	 */
	protected $json_endpoints = [];

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
		array_walk($this->post_types,[$this,'resolveIfNeeded']);

		/* Add Actions */
		add_action('init',[$this,'init']);

		/* Add Rest Init Hook */
		add_action('rest_api_init',[$this,'rest_init']);

		/* Register Possible VS Components */
		add_action('vc_before_init',[$this,'registerVisualComposerComponents']);
	}

	/**
	 * Is called after WP init
	 */
	public function init(){
		/* Make the Shortcodes */
		array_walk($this->shortcodes,[$this,'registerShortCode']);

		/* Register Post type */
		array_walk($this->post_types,[$this,'registerPostType']);

		/* Register Navs */
		array_walk($this->navs,[$this,'registerNav']);
	}

	/**
	 * Init Rest API
	 */
	public function rest_init(){
		/* Create Rest Endpoints */
		array_walk($this->json_endpoints,[$this,'registerEndpoints']);
	}

	/**
	 * @param $abstract
	 *
	 * @throws \Exception
	 */
	public function registerEndpoints($abstract){

		if( ! is_object($abstract) ){
			/** @var JsonApiEndpoint $abstract */
			$abstract = $this->container->make($abstract);
		}

		if( ! ( $abstract instanceof JsonApiEndpoint ) ){
			throw new \Exception('Endpoints must implement the JsonApiEndpoint Interface!');
		}

		/* Register The endpoint! */
		register_rest_route( ...$abstract->toJsonApiEndpoint() );
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
	 * @param $abstract
	 */
	protected function resolveIfNeeded(&$abstract){
		if( ! is_object($abstract) ){
			$abstract = $this->container->make($abstract);
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
	 * @param PostType $post_type
	 */
	protected function registerPostType(PostType $post_type){

		list($name,$args) = $post_type->toPostType();

		register_post_type(
			$name,
			$args
		);

		/**
		 * Call the Taxemonies hook!
		 */
		$post_type->taxonomies();

		/**
		 * Call the registered method!
		 */
		if( method_exists($post_type,'registered') ){
			$post_type->registered();
		}
	}

	/**
	 * @param string $readable_title
	 * @param $slug
	 */
	protected function registerNav(string $readable_title,string $slug){
		register_nav_menu($slug, Language::translate( $readable_title ) );
	}

	/**
	 * @param VisualComposerComponent $component
	 */
	protected function registerVisualComposerComponent(VisualComposerComponent $component){
		/* Add Component */
		vc_map($component->toVisualComposer());
	}

}