<?php
/**
 * Created by PhpStorm.
 * User: leodaniel
 * Date: 12.11.17
 * Time: 14:49
 */

namespace le0daniel\System\WordPress;

use Timber\User as TimberUser;

class User extends TimberUser{

	/**
	 * User constructor.
	 */
	public function __construct() {
		parent::__construct();
	}

}