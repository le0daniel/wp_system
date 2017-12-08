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
	public $site = 'wp.site';

	/**
	 * @var Page|string
	 */
	public $page = 'wp.page';

	/**
	 * @var User|string
	 */
	public $user = 'wp.user';

	/**
	 * Context constructor.
	 */
	public function __construct() {
		/* Resolve */
		$this->site = resolve($this->site);
		$this->page = resolve($this->page);
		$this->user = resolve($this->user);
	}

	/**
	 * @return array
	 */
	protected function castBase():array{
		return [
			'site'=>$this->site,
			'page'=>$this->page,
			'user'=>$this->user,
		];
	}

	/**
	 * Do additional stuff before returning as array!
	 *
	 * @return array
	 */
	public function toArray():array
	{
		return $this->castBase();
	}

}