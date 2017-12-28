<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 12.11.17
 * Time: 14:27
 */

namespace le0daniel\System\WordPress;

use le0daniel\System\Contracts\CastArray;
use le0daniel\System\Contracts\Hashable;
use Timber\Request;

/**
 * Class Context
 * @package le0daniel\System\WordPress
 *
 * Containing the full wordpress context
 */
class Context implements CastArray, Hashable {

	/**
	 * @var array
	 */
	protected $cast = [
		'user'=>'wp.user',
		'page'=>'wp.page',
		'site'=>'wp.site',
	];

	/**
	 * @var bool
	 */
	protected $_is_resolved = false;

	/**
	 * Resolve the cast array!
	 */
	protected function resolve(){
		$this->cast = array_map('resolve',$this->cast);
	}

	/**
	 * Do additional stuff before returning as array!
	 *
	 * @return array
	 */
	public function toArray():array
	{
		if( ! $this->_is_resolved ){
			$this->resolve();
			$this->_is_resolved = true;
		}

		return $this->castBase();
	}

	/**
	 * Return Hash, used for caching!
	 *
	 * @return string
	 */
	public function getHash(): string {

		$hash = 'qwefgsgwgfewe';

		return md5($hash);
	}
}