<?php
/**
 * Created by PhpStorm.
 * User: kernbrand
 * Date: 19.02.18
 * Time: 13:53
 */

namespace le0daniel\System\Contracts;


interface JsonApiEndpoint {

	/**
	 * Returns the JSON API Endpoint data
	 *
	 * @return mixed
	 */
	public function toJsonApiEndpoint():array;

	/**
	 * @param \WP_REST_Request $request
	 *
	 * @return mixed
	 */
	public function handle(\WP_REST_Request $request);

}