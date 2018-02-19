<?php
/**
 * Created by PhpStorm.
 * User: kernbrand
 * Date: 19.02.18
 * Time: 13:53
 */

namespace le0daniel\System\WordPress;

use le0daniel\System\Contracts\JsonApiEndpoint as JsonApiEndpointContract;

class JsonApiEndpoint implements JsonApiEndpointContract {

	/**
	 * The Namespace of the API Endpoint
	 *
	 * @var string
	 */
	protected $namespace = '';

	/**
	 * Returns the namespace
	 */
	public function getNamespace():string{
		return $this->namespace;
	}

	/**
	 * The route of the API Endpoint
	 *
	 * @var string
	 */
	protected $route = '';

	/**
	 * @return string
	 */
	public function getRoute():string{
		return $this->route;
	}

	/**
	 * @var string
	 */
	protected $method = 'GET';

	/**
	 * Get the method
	 *
	 * @return string
	 */
	protected function getMethod():string{
		return strtoupper($this->method);
	}

	/**
	 * Return the needed args
	 *
	 * @return array
	 */
	public function getArgs():array{
		return [];
	}

	/**
	 * Append to the arguments
	 *
	 * @return array
	 */
	public function appendArguments():array{

	}

	/**
	 * Returns the JSON API Endpoint data
	 *
	 * @return array
	 */
	public function toJsonApiEndpoint():array{
		return [
			$this->getNamespace(),
			$this->getRoute(),
			array_merge(
				[
					'methods'=>$this->getMethod(),
					'callback'=>[$this,'handle'],
					'args'=>$this->getArgs(),
				],
				$this->appendArguments()
			),
		];
	}
}