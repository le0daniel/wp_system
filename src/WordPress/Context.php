<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 12.11.17
 * Time: 14:27
 */

namespace le0daniel\System\WordPress;

use le0daniel\System\Contracts\CastArray;
use Timber\Request;

/**
 * Class Context
 * @package le0daniel\System\WordPress
 *
 * Containing the full wordpress context
 */
class Context implements CastArray {

	/**
	 * @var Site|string
	 */
	public $site = Site::class;

	/**
	 * @var Page|string
	 */
	public $page = Page::class;

	/**
	 * Context constructor.
	 */
	public function __construct() {
		/* Resolve */
		$this->site = resolve($this->site);
		$this->page = resolve($this->page);
	}

	/**
	 * @return array
	 */
	public function toArray():array
	{
		return [
			'site'=>$this->site,
			'page'=>$this->page,
		];
	}

}