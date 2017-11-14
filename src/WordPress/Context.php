<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 12.11.17
 * Time: 14:27
 */

namespace le0daniel\System\WordPress;

use Timber\FunctionWrapper;
use Timber\Request;

/**
 * Class Context
 * @package le0daniel\System\WordPress
 *
 * Containing the full wordpress context
 */
class Context {

	public $site;
	public $page;
	public $user;
	public $wp_title;
	public $request;

	/**
	 * Deprecated!
	 * @var
	 */
	public $body_class;

	/**
	 * @var \ReflectionClass
	 */
	private $reflection;

	/**
	 * @var \ReflectionProperty[]
	 */
	private $properties;

	/**
	 * @var array
	 */
	private $cache;

	/**
	 * Context constructor.
	 */
	public function __construct() {
		$this->reflection = new \ReflectionClass($this);
		$this->properties = $this->reflection->getProperties(\ReflectionProperty::IS_PUBLIC);
		$this->build();
	}

	/**
	 * Build the Context
	 */
	protected function build(){

		/* Resolve */
		$this->site = resolve(Site::class);
		$this->page = resolve(Page::class);

		$user = resolve(User::class);
		$this->user = ($user->ID)?$user:false;

		/* Set the title */
		$this->wp_title = '';
		$this->request = new Request();

		/* Deprecated, will be removed! */
		$this->body_class = $this->page->body['class'];

	}

	/**
	 * @return array
	 */
	public function toArray():array
	{
		/**
		 * Return Cache
		 */
		if( isset($this->cache) ){
			return $this->cache;
		}

		/* Init Cache as Array */
		$this->cache = [];

		array_walk($this->properties, function($public_var){
			$this->cache[$public_var->name] = $this->{$public_var->name};
		});

		return $this->cache;
	}

}